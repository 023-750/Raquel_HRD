# Design Document — Employee Portal Career Movements

## Overview

This feature extends the Raquel Pawnshop HRIS Employee Portal to support a four-step approval
chain for branch-level Transfer requests. Branch Supervisors submit Transfer-only requests
through the Employee Portal; those requests flow through Branch Manager → HR Supervisor →
HR Manager before becoming fully approved. All existing HR Portal flows (HR Supervisor and
HR Staff direct submissions) remain unchanged.

**Key constraints driving the design:**
- Plain PHP + MySQLi; no ORM or framework
- Schema migrations run through `ensureCareerProgressionMovements()` via `ALTER TABLE … ADD COLUMN IF NOT EXISTS` pattern
- Backward compatibility: HR Portal records (`portal_workflow_stage IS NULL`) must never be
  accidentally touched by Employee Portal logic
- Session-based auth; role+rank checks enforced at the top of every page

---

## Architecture

The feature touches four layers:

```
Browser (Bootstrap 5 forms)
        │ POST / GET
        ▼
PHP Pages  ─── /employee/career-movement-request.php   (modified)
           ─── /employee/branch-manager-approvals.php  (new)
           ─── /supervisor/career-movements.php         (modified)
           ─── /manager/career-movements.php            (modified)
        │
        ▼
includes/functions.php  (ensureCareerProgressionMovements, createNotification,
                         logAudit, getPreferredLinkedUserId, executeCareerMovementApplication,
                         applyDueCareerProgressionMovements)
        │
        ▼
MySQL — career_movements table (new columns added via ALTER TABLE)
      — employees, users, branches, notifications tables (existing)
```

### Workflow State Machine

```
[Branch Supervisor submits]
          │
          ▼
  Pending_Branch_Manager  ─── BM found? No ──▶  Pending_HR_Supervisor
          │                                              │
       BM approves                               HR Sup approves
          │                                              │
          ▼                                              ▼
  Pending_HR_Supervisor                        Pending_HR_Manager
          │                                              │
   HR Sup approves                              HR Mgr approves/rejects
          │                                              │
          ▼                                              ▼
  Pending_HR_Manager                      Approved / Rejected
          │
   HR Mgr approves/rejects
          │
          ▼
   Approved / Rejected

Any stage: BM Reject / HR Sup Reject → Rejected (terminal)
```

The `approval_status` column is not changed until the final decision:
- `Pending` throughout all intermediate stages
- `Approved` or `Rejected` only at terminal states

This preserves the existing HR Portal query pattern (`approval_status = 'Pending'`) unchanged.

---

## Components and Interfaces

### 1. `ensureCareerProgressionMovements()` — Schema Migration (modified)

Location: `includes/functions.php`

Adds seven new columns to `career_movements` if they do not already exist, using the existing
`$optional_columns` array pattern:

```php
$new_portal_columns = [
    'portal_workflow_stage' =>
        "ALTER TABLE career_movements ADD COLUMN portal_workflow_stage
         ENUM('Pending_Branch_Manager','Pending_HR_Supervisor','Pending_HR_Manager','Approved','Rejected')
         NULL DEFAULT NULL AFTER request_source",

    'branch_manager_approved_by' =>
        "ALTER TABLE career_movements ADD COLUMN branch_manager_approved_by INT NULL AFTER portal_workflow_stage",

    'branch_manager_decision_date' =>
        "ALTER TABLE career_movements ADD COLUMN branch_manager_decision_date DATETIME NULL AFTER branch_manager_approved_by",

    'branch_manager_comments' =>
        "ALTER TABLE career_movements ADD COLUMN branch_manager_comments TEXT NULL AFTER branch_manager_decision_date",

    'hr_supervisor_approved_by' =>
        "ALTER TABLE career_movements ADD COLUMN hr_supervisor_approved_by INT NULL AFTER branch_manager_comments",

    'hr_supervisor_decision_date' =>
        "ALTER TABLE career_movements ADD COLUMN hr_supervisor_decision_date DATETIME NULL AFTER hr_supervisor_approved_by",

    'hr_supervisor_comments' =>
        "ALTER TABLE career_movements ADD COLUMN hr_supervisor_comments TEXT NULL AFTER hr_supervisor_decision_date",
];
```

Each column is checked via `getCareerProgressionMovementColumns()` (already used for existing
optional columns) before the `ALTER TABLE` is executed. MySQL error 1060 (duplicate column) is
handled implicitly because the existence check prevents the ALTER when the column is already
present. Any other failure is caught by the existing `try/catch (mysqli_sql_exception)` block,
logged, and returns `false`.

### 2. `/employee/career-movement-request.php` — Modified

**Access guard changes:**
- Remove `hasEmployeeSubordinates()` check; replace with a combined check:
  - Employee must have `rank_category_id = 4` (Branch Supervisor) AND have at least one active
    subordinate via `hasEmployeeSubordinates()`.
  - The informational "access restricted" block is shown if either condition fails.

**Form changes:**
- Movement type dropdown is rendered with only `Transfer` as an option. The other three values
  (`Promotion`, `Demotion`, `Role Change`) are not rendered into the HTML.
- Employee dropdown is populated from **branch employees** (all active employees with the same
  `branch_id` as the current user), not just subordinates. The supervisor's own `employee_id`
  is excluded. Query:
  ```sql
  SELECT employee_id, first_name, last_name, job_title
  FROM employees
  WHERE branch_id = ? AND is_active = 1 AND employee_id != ?
  ORDER BY last_name, first_name
  ```
- `new_branch_id` is **required** for Transfers and must differ from the employee's current
  `branch_id`. The form displays a mandatory "Destination Branch" select (no "same branch" option).

**POST validation additions:**
1. `movement_type` must be `Transfer` (server-side); any other value → reject.
2. `employee_id` must belong to the supervisor's branch (not just subordinates).
3. `new_branch_id` must be present and differ from the employee's current branch.
4. No existing `Pending` Portal_Request for the same `employee_id` (duplicate guard).

**Insertion changes:**
- Insert with `portal_workflow_stage = 'Pending_Branch_Manager'` initially.
- Immediately after insert, perform Branch Manager lookup:
  ```sql
  SELECT e.employee_id FROM employees e
  WHERE e.branch_id = ? AND e.rank_category_id = 3 AND e.is_active = 1 LIMIT 1
  ```
  Then resolve Employee Portal user: `getPreferredLinkedUserId($conn, $bm_emp_id, 'employee_portal')`
- If resolved → send notification to BM's `user_id`, keep stage as `Pending_Branch_Manager`.
- If not resolved → update `portal_workflow_stage = 'Pending_HR_Supervisor'`, notify all active
  HR Supervisors.

**Status table changes:**
- Query changed to: `WHERE cm.request_source = 'Employee Portal' AND cm.logged_by = ? AND cm.portal_workflow_stage IS NOT NULL`
- Stage label map (PHP):
  ```php
  $stage_labels = [
      'Pending_Branch_Manager'  => 'Pending Branch Manager',
      'Pending_HR_Supervisor'   => 'Pending HR Supervisor',
      'Pending_HR_Manager'      => 'Pending HR Manager',
      'Approved'                => 'Approved',
      'Rejected'                => 'Rejected',
  ];
  ```
- Badge color: `bg-warning text-dark` for any stage starting with "Pending", `bg-success` for
  Approved, `bg-danger` for Rejected.
- If `portal_workflow_stage = 'Rejected'`, display rejection reason sourced from:
  - `branch_manager_comments` if `branch_manager_decision_date IS NOT NULL`
  - `hr_supervisor_comments` if `hr_supervisor_decision_date IS NOT NULL`
  - `manager_comments` otherwise
  - Falls back to "No reason provided" if the applicable field is NULL/empty.
- Columns shown: ref # (zero-padded), employee name, destination branch, effective date, submitted date, stage badge, rejection reason (if applicable).

### 3. `/employee/branch-manager-approvals.php` — New Page

**Access guard:**
```php
checkRole(['Employee']);
$bm_emp_id  = (int)($_SESSION['employee_id'] ?? 0);
$bm_branch_id = (int)($_SESSION['branch_id'] ?? 0);

$rank_stmt = $conn->prepare(
    "SELECT rank_category_id FROM employees WHERE employee_id = ? LIMIT 1"
);
$rank_stmt->bind_param("i", $bm_emp_id);
$rank_stmt->execute();
$rank_row = $rank_stmt->get_result()->fetch_assoc();
if (!$rank_row || (int)$rank_row['rank_category_id'] !== 3) {
    redirectWith(BASE_URL . '/employee/dashboard.php', 'danger',
        'Access restricted to Branch Managers.');
}
```

**Pending queue query:**
```sql
SELECT cm.*,
    CONCAT(e.first_name,' ',e.last_name) AS employee_name,
    e.job_title AS current_position,
    pb.branch_name AS current_branch_name,
    nb.branch_name AS destination_branch_name,
    u_sub.full_name AS submitted_by_name
FROM career_movements cm
JOIN employees e   ON cm.employee_id = e.employee_id
LEFT JOIN branches pb ON cm.previous_branch_id = pb.branch_id
LEFT JOIN branches nb ON cm.new_branch_id = nb.branch_id
LEFT JOIN users u_sub ON cm.logged_by = u_sub.user_id
WHERE cm.portal_workflow_stage = 'Pending_Branch_Manager'
  AND cm.previous_branch_id = ?
ORDER BY cm.created_at ASC
```

**POST — Approve action:**
```php
// Guard: stage must still be Pending_Branch_Manager for this branch
if ($movement['portal_workflow_stage'] !== 'Pending_Branch_Manager'
    || (int)$movement['previous_branch_id'] !== $bm_branch_id) {
    redirectWith(..., 'danger', 'Unauthorized action.');
}
// Self-approval block
if ((int)$movement['logged_by'] === $current_user_id) {
    redirectWith(..., 'danger', 'You cannot approve your own request.');
}

$upd = $conn->prepare("
    UPDATE career_movements
    SET portal_workflow_stage      = 'Pending_HR_Supervisor',
        branch_manager_approved_by = ?,
        branch_manager_decision_date = NOW()
    WHERE movement_id = ?
");
// Notify all active HR Supervisors
$hr_sups = $conn->query("SELECT user_id FROM users WHERE role='HR Supervisor' AND is_active=1");
while ($hs = $hr_sups->fetch_assoc()) {
    createNotification($conn, $hs['user_id'], 'Transfer Request Awaiting Review', ...);
}
logAudit($conn, $current_user_id, 'APPROVE', 'Career Movement', $movement_id, ...);
```

**POST — Reject action:**
```php
$upd = $conn->prepare("
    UPDATE career_movements
    SET portal_workflow_stage        = 'Rejected',
        approval_status              = 'Rejected',
        branch_manager_approved_by   = ?,
        branch_manager_decision_date = NOW(),
        branch_manager_comments      = ?
    WHERE movement_id = ?
");
// Notify submitting Branch Supervisor
$submitter_user = getPreferredLinkedUserId($conn, $logged_employee_id, 'employee_portal');
if ($submitter_user) {
    createNotification($conn, $submitter_user, 'Transfer Request Rejected',
        'Your request was rejected at the Branch Manager stage.', ...);
}
logAudit(...);
```

**Display columns:** Target employee name, current position, current branch, destination branch,
effective date, submitted by (name), reason, Approve/Reject action buttons.

### 4. `/supervisor/career-movements.php` — Modified

**Pending queue query change:**

The existing query fetches all `approval_status = 'Pending'` records. It must be extended to
also surface Portal_Requests at `Pending_HR_Supervisor`. The combined condition:

```sql
WHERE (
    (cm.approval_status = 'Pending' AND (cm.portal_workflow_stage IS NULL OR cm.request_source = 'HR Portal'))
    OR
    (cm.request_source = 'Employee Portal' AND cm.portal_workflow_stage = 'Pending_HR_Supervisor')
)
```

This ensures HR_Portal_Requests (`portal_workflow_stage IS NULL`) and Portal_Requests at
`Pending_HR_Supervisor` both appear in the queue. Records at other Portal stages
(`Pending_Branch_Manager`, `Pending_HR_Manager`, `Approved`, `Rejected`) are excluded.

**Approve/Reject action — differentiated logic:**

```php
$is_portal_request = ($movement['request_source'] === 'Employee Portal'
                      && $movement['portal_workflow_stage'] !== null);

if ($is_portal_request) {
    // Stage guard
    if ($movement['portal_workflow_stage'] !== 'Pending_HR_Supervisor') {
        redirectWith(..., 'danger', 'This request is not at the HR Supervisor stage.');
    }
    if ($action === 'Approve') {
        $upd = $conn->prepare("
            UPDATE career_movements
            SET portal_workflow_stage       = 'Pending_HR_Manager',
                hr_supervisor_approved_by   = ?,
                hr_supervisor_decision_date = NOW()
            WHERE movement_id = ?
        ");
        // Notify all active HR Managers
        $hr_mgrs = $conn->query("SELECT user_id FROM users WHERE role='HR Manager' AND is_active=1");
        while ($hm = $hr_mgrs->fetch_assoc()) { createNotification(...); }
    } else {
        // Validate comments present (1-1000 chars)
        $upd = $conn->prepare("
            UPDATE career_movements
            SET portal_workflow_stage       = 'Rejected',
                approval_status             = 'Rejected',
                hr_supervisor_approved_by   = ?,
                hr_supervisor_decision_date = NOW(),
                hr_supervisor_comments      = ?
            WHERE movement_id = ?
        ");
        // Notify submitter (Branch Supervisor)
        $submitter_user = getPreferredLinkedUserId($conn, $emp_id, 'employee_portal');
        if ($submitter_user) { createNotification(...); }
    }
} else {
    // Existing HR_Portal_Request single-step flow — unchanged
    // Sets approval_status = 'Approved'/'Rejected', notifies HR Managers
    // Does NOT touch portal_workflow_stage
}
```

**Source label in table:**
- `request_source = 'Employee Portal'` → badge: "Branch Head Requisition" (`bg-info text-dark`)
- HR Portal → existing badge ("HR Portal", `bg-secondary`) — no change

### 5. `/manager/career-movements.php` — Modified

**Pending queue query change:**

```sql
WHERE (
    (cm.approval_status = 'Pending' AND cm.portal_workflow_stage IS NULL)
    OR
    (cm.request_source = 'Employee Portal' AND cm.portal_workflow_stage = 'Pending_HR_Manager')
)
```

The first clause covers all HR_Portal_Requests. The second covers Portal_Requests that have
passed both Branch Manager and HR Supervisor stages.

**Approve/Reject action — differentiated logic:**

```php
$is_portal_request = ($movement['request_source'] === 'Employee Portal'
                      && $movement['portal_workflow_stage'] !== null);

if ($is_portal_request) {
    if ($movement['portal_workflow_stage'] !== 'Pending_HR_Manager') {
        redirectWith(..., 'danger', 'This request is not at the HR Manager stage.');
    }
    if ($action === 'Approve') {
        $upd = $conn->prepare("
            UPDATE career_movements
            SET portal_workflow_stage = 'Approved',
                approval_status       = 'Approved',
                approved_by           = ?,
                decision_date         = NOW(),
                manager_comments      = ?,
                is_applied            = 0
            WHERE movement_id = ?
        ");
        $upd->execute();
        // Apply immediately if effective_date <= today
        if ($movement['effective_date'] <= date('Y-m-d')) {
            executeCareerMovementApplication($conn, $movement, $movement_id);
        }
        // Notify submitter
        $submitter_user = getPreferredLinkedUserId($conn, $emp_id, 'employee_portal');
        if ($submitter_user) { createNotification(..., 'Your Transfer request has been fully approved.'); }
    } else {
        $upd = $conn->prepare("
            UPDATE career_movements
            SET portal_workflow_stage = 'Rejected',
                approval_status       = 'Rejected',
                approved_by           = ?,
                decision_date         = NOW(),
                manager_comments      = ?
            WHERE movement_id = ?
        ");
        // Notify submitter
    }
} else {
    // Existing HR_Portal_Request final-approval flow — unchanged
}
```

**Approval chain detail row:**  
When displaying a decided Portal_Request, show:
- Branch Manager: `branch_manager_approved_by` (name lookup) + `branch_manager_decision_date`, or
  "Branch Manager step bypassed" if `branch_manager_approved_by IS NULL`.
- HR Supervisor: `hr_supervisor_approved_by` (name lookup) + `hr_supervisor_decision_date`.


### 6. Notification Routing

All notifications use `createNotification($conn, $user_id, $title, $message, $link)`.

| Trigger | Recipients | Resolution |
|---|---|---|
| Stage = `Pending_Branch_Manager` | Branch Manager's Employee Portal user | `getPreferredLinkedUserId($conn, $bm_emp_id, 'employee_portal')` |
| Stage = `Pending_HR_Supervisor` | All `role='HR Supervisor' AND is_active=1` | Direct query on `users` |
| Stage = `Pending_HR_Manager` | All `role='HR Manager' AND is_active=1` | Direct query on `users` |
| Stage = `Approved` | Submitting Branch Supervisor | `getPreferredLinkedUserId($conn, $submitter_emp_id, 'employee_portal')` |
| Stage = `Rejected` (any stage) | Submitting Branch Supervisor | `getPreferredLinkedUserId($conn, $submitter_emp_id, 'employee_portal')` |

The `logged_by` field holds the submitting user's `user_id`. To resolve the submitter's
`employee_id` for `getPreferredLinkedUserId()`:
```sql
SELECT employee_id FROM users WHERE user_id = ? LIMIT 1
```

If any resolution returns `null`, the notification is silently skipped and a warning is written
to the PHP error log via `error_log("Career movement notification skipped: ...")`.

---

## Data Models

### `career_movements` — Extended Schema

Existing columns remain unchanged. New columns added via `ensureCareerProgressionMovements()`:

| Column | Type | Nullable | Default | Purpose |
|---|---|---|---|---|
| `portal_workflow_stage` | `ENUM('Pending_Branch_Manager','Pending_HR_Supervisor','Pending_HR_Manager','Approved','Rejected')` | YES | NULL | Current stage in Employee Portal chain; NULL for HR Portal records |
| `branch_manager_approved_by` | `INT` | YES | NULL | FK → `users.user_id`; BM who acted |
| `branch_manager_decision_date` | `DATETIME` | YES | NULL | Timestamp of BM action |
| `branch_manager_comments` | `TEXT` | YES | NULL | Optional BM rejection reason |
| `hr_supervisor_approved_by` | `INT` | YES | NULL | FK → `users.user_id`; HR Sup who acted |
| `hr_supervisor_decision_date` | `DATETIME` | YES | NULL | Timestamp of HR Sup action |
| `hr_supervisor_comments` | `TEXT` | YES | NULL | Required HR Sup rejection reason (1–1000 chars) |

**Invariants:**
- HR Portal records: `portal_workflow_stage IS NULL` always.
- Portal_Requests: `portal_workflow_stage IS NOT NULL` always.
- `approval_status = 'Pending'` at all intermediate stages (`Pending_Branch_Manager`,
  `Pending_HR_Supervisor`, `Pending_HR_Manager`).
- `approval_status = 'Approved'` iff `portal_workflow_stage = 'Approved'` (for Portal_Requests).
- `approval_status = 'Rejected'` iff `portal_workflow_stage = 'Rejected'` (for Portal_Requests).
- `branch_manager_decision_date IS NULL` iff `portal_workflow_stage = 'Pending_Branch_Manager'`
  (i.e., no BM decision yet), UNLESS the BM step was bypassed — in that case
  `branch_manager_approved_by IS NULL` AND `portal_workflow_stage != 'Pending_Branch_Manager'`.

### Key Session Variables Used

| Variable | Set by | Used in |
|---|---|---|
| `$_SESSION['user_id']` | Login | All pages — identifies the acting user |
| `$_SESSION['employee_id']` | Login | `career-movement-request.php`, `branch-manager-approvals.php` |
| `$_SESSION['branch_id']` | Login | `branch-manager-approvals.php` (BM branch scope) |
| `$_SESSION['full_name']` | Login | `career-movement-request.php` (initiated_by_name) |

### Existing Tables Referenced (read-only for this feature)

- `employees`: `employee_id`, `branch_id`, `rank_category_id`, `job_title`, `is_active`
- `users`: `user_id`, `employee_id`, `role`, `is_active`, `full_name`
- `branches`: `branch_id`, `branch_name`
- `notifications`: written via `createNotification()`


---

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system — essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Transfer-Only Form Restriction

*For any* user with `rank_category_id = 4` and at least one active subordinate, the set of movement types returned by the form-building logic should contain exactly one element: `"Transfer"`. No other movement type value should appear in that set.

**Validates: Requirements 1.1, 1.2**

---

### Property 2: Same-Branch Destination Rejected

*For any* active employee with `branch_id = B` and any submission where `new_branch_id` is null, empty, or equal to `B`, the server-side validation function should return a rejection result and the `career_movements` record count should remain unchanged.

**Validates: Requirements 1.3, 4.2**

---

### Property 3: Branch Employee Set Completeness and Exclusivity

*For any* branch with N active employees (where N ≥ 1), the set of employees returned for a Branch Supervisor's dropdown should contain exactly N − 1 employees, every returned employee should have the same `branch_id` as the supervisor, and the supervisor's own `employee_id` should not appear in the set.

**Validates: Requirements 2.1, 2.2**

---

### Property 4: Schema Migration Idempotency

*For any* initial state of the `career_movements` table (whether the seven new columns are present, partially present, or absent), calling `ensureCareerProgressionMovements()` two or more times should produce the same result as calling it once — all seven columns exist, no data is modified, and the function returns `true` each time.

**Validates: Requirements 3.8**

---

### Property 5: Portal Request Initial Stage

*For any* valid Transfer submission (correct `employee_id` in supervisor's branch, `movement_type = 'Transfer'`, `new_branch_id` different from current branch, no duplicate pending), the inserted `career_movements` record should have `request_source = 'Employee Portal'`, `approval_status = 'Pending'`, and `portal_workflow_stage` equal to either `'Pending_Branch_Manager'` (if a linked BM user was found) or `'Pending_HR_Supervisor'` (if no linked BM user exists).

**Validates: Requirements 4.1, 4.3, 4.5**

---

### Property 6: Valid Stage Transitions Only

*For any* Portal_Request record, the `portal_workflow_stage` value after any approve/reject action must follow the defined transition table:

- `Pending_Branch_Manager` + Approve → `Pending_HR_Supervisor`
- `Pending_Branch_Manager` + Reject  → `Rejected`
- `Pending_HR_Supervisor`  + Approve → `Pending_HR_Manager`
- `Pending_HR_Supervisor`  + Reject  → `Rejected`
- `Pending_HR_Manager`     + Approve → `Approved`
- `Pending_HR_Manager`     + Reject  → `Rejected`

No transition outside this table should ever occur. Specifically, no stage should be skipped (e.g., going from `Pending_Branch_Manager` directly to `Pending_HR_Manager`) and terminal states (`Approved`, `Rejected`) should never transition to any other state.

**Validates: Requirements 5.2, 5.3, 6.2, 6.3, 7.2, 7.4, 11.3, 11.4**

---

### Property 7: Authorization Guard — No Unauthorized Stage Actions

*For any* Portal_Request, an action submitted by an approver should be rejected (no record modification) if any of the following are true:
- The record's `portal_workflow_stage` does not match the stage this approver is authorized to act on.
- For Branch Manager actions: the record's `previous_branch_id` does not match the acting Branch Manager's own `branch_id`.
- The acting user's `user_id` equals the record's `logged_by` (self-approval attempt).

This property must hold for every approver role (Branch Manager, HR Supervisor, HR Manager) and for any combination of record and actor.

**Validates: Requirements 5.4, 11.1, 11.2, 11.3, 11.4, 11.5, 11.6**

---

### Property 8: HR Portal Records Unaffected by Portal Workflow Logic

*For any* `career_movements` record where `request_source = 'HR Portal'` or `portal_workflow_stage IS NULL`, no action taken through the Employee Portal approval chain (Branch Manager approval, HR Supervisor or HR Manager acting on a Portal_Request) should modify that record's `portal_workflow_stage`, nor should it cause that record to disappear from the HR Supervisor or HR Manager pending queue (queried by `approval_status = 'Pending'`).

**Validates: Requirements 6.4, 7.5, 10.1, 10.2, 10.3, 10.4**

---

### Property 9: Approval Status Consistency

*For any* Portal_Request record, the values of `approval_status` and `portal_workflow_stage` must always be consistent:
- If `portal_workflow_stage` is any "Pending_*" value, then `approval_status` must be `'Pending'`.
- If `portal_workflow_stage = 'Approved'`, then `approval_status` must be `'Approved'`.
- If `portal_workflow_stage = 'Rejected'`, then `approval_status` must be `'Rejected'`.

This invariant must hold after every state transition.

**Validates: Requirements 5.3, 6.3, 7.2, 7.4**

---

### Property 10: Stage Label Round-Trip

*For any* `portal_workflow_stage` ENUM value, the label-mapping function should return a non-empty human-readable string, and applying the mapping to the same input twice should produce the same output (the mapping is deterministic and total over the enum domain).

**Validates: Requirements 8.2, 8.3**

---

### Property 11: Due Movement Application Regardless of Portal Stage

*For any* `career_movements` record where `approval_status = 'Approved'`, `is_applied = 0`, and `effective_date <= CURDATE()`, calling `applyDueCareerProgressionMovements()` should apply the movement (update `employees` table and set `is_applied = 1`), regardless of whether `portal_workflow_stage` is `NULL`, `'Approved'`, or any other value.

**Validates: Requirements 10.5**

---

## Error Handling

### Schema Migration Failures (`ensureCareerProgressionMovements`)
- Each `ALTER TABLE` is guarded by an existence check via `getCareerProgressionMovementColumns()`.
- The existing `try/catch (mysqli_sql_exception)` block wraps all DDL. On any unexpected
  exception, `error_log()` records the message and the function returns `false`.
- Pages that depend on the migration check its return value. If `false`, they display an
  `alert-danger` banner and disable form actions (existing pattern, unchanged).

### Validation Errors (`career-movement-request.php`)
- Collect all errors into `$errors[]` array.
- On non-empty `$errors`, re-render the form with field values preserved (via `$_POST`).
- Do NOT insert any record if validation fails.
- Specific cases:
  - Non-Transfer `movement_type` → "Only Transfer requests may be submitted through the Employee Portal."
  - Same-branch or missing `new_branch_id` → "A different destination branch must be selected."
  - Invalid `employee_id` (not in branch, or self) → "The selected employee is not eligible."
  - Duplicate pending Portal_Request → "A pending Transfer request already exists for this employee."

### Authorization Failures (all approval pages)
- All unauthorized actions use `redirectWith(BASE_URL . '/employee/dashboard.php', 'danger', $msg)`.
- No partial writes occur: the guard checks are performed before any `UPDATE`.
- Self-approval attempt → "You cannot approve or reject a request you submitted."
- Wrong stage → "This request is not at the [stage name] stage."
- Branch mismatch → "You are not authorized to act on requests for this branch."

### Notification Failures
- `getPreferredLinkedUserId()` returning `null` → silently skip, log:
  `error_log("Career movement notification skipped for employee_id={$emp_id}: no linked portal user found.")`
- No active HR Supervisors/Managers found → silently skip, log:
  `error_log("Career movement notification skipped: no active [role] users found.")`
- Notification failures do **not** roll back the stage transition already committed.

### CSRF Protection
- All POST handlers call `verifyCsrfToken()` at the top (existing pattern).
- All forms include `<?php echo csrfField(); ?>`.

---

## Testing Strategy

### Dual Approach

**Unit/Example Tests** focus on specific scenarios with fixed data:
- Correct HTML output for the Transfer-only dropdown (one option rendered).
- Rejection of Promotion/Demotion/Role Change POST submissions.
- Empty employee list when supervisor is the only branch member.
- Notification skipped (no PHP error) when `getPreferredLinkedUserId()` returns null.
- `ensureCareerProgressionMovements()` returns false on a non-1060 DB error (mock).

**Property-Based Tests** validate universal invariants across generated inputs. The chosen PBT
library is **[QuickCheck for PHP](https://github.com/steos/php-quickcheck)** (or equivalent PHPUnit
data provider approach with Faker for random generation, configured to run ≥100 iterations per
property). Each test is tagged with a comment referencing the design property.

### Property Tests

Each property test must run a minimum of **100 iterations** with randomized inputs. The tag
format is: `// Feature: employee-portal-career-movements, Property N: <property text>`

| Property | Test Target | Input Generators | Assertion |
|---|---|---|---|
| P1: Transfer-Only | `buildAllowedMovementTypes($rank_category_id, $has_subordinates)` | Random rank (1–10), random bool | Result === `['Transfer']` when rank=4 + has subs; empty/restricted otherwise |
| P2: Same-Branch Rejection | `validateTransferSubmission(...)` | Random employee+branch pairs, `new_branch_id` = same | Always returns validation error |
| P3: Branch Employee Set | `getBranchEmployeesForDropdown($conn, $sup_id, $branch_id)` | Random branch sizes (1–50 employees) | Count = N-1, all same branch_id, no supervisor |
| P4: Migration Idempotency | `ensureCareerProgressionMovements()` called N times | Random number of calls (2–10) | Returns true each time; column count unchanged |
| P5: Initial Stage | `insertPortalRequest(...)` | Random valid submissions | `portal_workflow_stage` ∈ {Pending_Branch_Manager, Pending_HR_Supervisor} |
| P6: Valid Transitions | `applyStageTransition($stage, $action)` | All stage/action combos | Output ∈ defined transition table only |
| P7: Auth Guards | `checkApprovalAuthorization($movement, $actor)` | Random record/actor pairs with mismatches | Returns false for any mismatch |
| P8: HR Portal Unaffected | `getPortalWorkflowStage($movement_id)` after any Employee Portal action | HR Portal records + any Portal action | `portal_workflow_stage` remains NULL |
| P9: Approval Status Consistency | Inspect record after any transition | Random state sequences | `approval_status` ↔ `portal_workflow_stage` invariant holds |
| P10: Label Mapping | `getPortalStageLabel($stage)` | All 5 ENUM values + random strings | Non-empty string for all ENUM values; deterministic |
| P11: Apply Due Movements | `applyDueCareerProgressionMovements()` | Records with any `portal_workflow_stage` value | All qualifying records applied |

### Integration Tests

Thin integration tests (1–3 examples each) covering:
- Inserting a Portal_Request and verifying the row exists with correct column values.
- Running the full four-step approval chain end-to-end in a test DB and verifying `employees` table updated.
- Verifying `notifications` table receives the correct rows at each stage transition.

### Test Isolation

All property tests use a test database with transactions rolled back after each test. The
`ensureCareerProgressionMovements()` migration runs once in the test setup. No production data is
touched.

