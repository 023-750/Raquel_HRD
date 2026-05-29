# Multi-Tier Department-Level Evaluation Workflow Plan

Implement a full multi-tier department-level evaluation approval workflow for the **Raquel HRIS Employee Portal 360 Self-Evaluation system (Main Branch)**. 

Currently, all self-evaluations follow a hardcoded `Employee → HR Supervisor → HR Manager` flow, skipping the department-level hierarchy. The new dynamic workflow will enforce:
`Employee → Dept Supervisor (reports_to) → Dept Manager (supervisor's reports_to) → HR Supervisor → HR Manager`.

---

## User Review Required

> [!IMPORTANT]
> **No New User Roles:** This system relies completely on the dynamic `reports_to` chain within the `employees` table. It infers whether an `Employee` is a "Dept Supervisor" or "Dept Manager" based on their position in the reporting hierarchy.
> - **Dept Supervisor:** The immediate head whom the employee reports to.
> - **Dept Manager:** The supervisor whom the Dept Supervisor reports to.

> [!WARNING]
> **Backward Compatibility:** All existing evaluations with status `Pending Supervisor` or `Pending HR Consolidation` will be preserved. To support this, `Pending Supervisor` remains in the ENUM definition, but new self-ratings will flow through the new statuses.

---

## Open Questions

> [!NOTE]
> **Dynamic Fallbacks:**
> 1. If an employee has **no immediate supervisor** assigned: The evaluation skips department-level approvals and goes directly to `'Pending HR Consolidation'` (HR Supervisor's queue).
> 2. If an employee has a **Dept Supervisor but no Dept Manager** (e.g., the supervisor reports to the GM/CEO or nobody): After the Dept Supervisor confirms the rating, it bypasses the Dept Manager step and goes directly to `'Pending HR Consolidation'`.
> 
> *Are you aligned with these fallback behaviors?*

---

## Proposed Changes

### Database & Helpers Component

#### [MODIFY] [functions.php](file:///c:/xampp/htdocs/raquel-hris/includes/functions.php)
- Update `ensureEvaluationWorkflowSchema($conn)`:
  - Add new ENUM statuses to `evaluations.status`: `'Pending Dept Supervisor'`, `'Pending Dept Manager'`.
  - Add new columns to `evaluations` table:
    - `dept_supervisor_confirmed_by INT NULL` (User ID of the supervisor)
    - `dept_supervisor_confirmed_date DATETIME NULL`
    - `dept_manager_endorsed_by INT NULL` (User ID of the manager)
    - `dept_manager_endorsed_date DATETIME NULL`
    - `dept_manager_comments TEXT NULL`
- Update `getStatusBadgeClass()` to support the new statuses with appealing CSS badge styles.
- Add new helper functions:
  - `getDeptManagerOfEmployee($conn, $employee_id)`: Fetches the supervisor's manager via `reports_to` chain traversal.
  - `isDeptManagerOfEmployee($conn, $manager_user_id, $employee_id)`: Verifies if a user is the department manager of a subordinate.
  - `isDeptManagerRole($conn, $employee_id)`: Checks if this employee has subordinates who themselves have subordinates.

---

### Employee Portal Component

#### [MODIFY] [self-rating.php](file:///c:/xampp/htdocs/raquel-hris/employee/self-rating.php)
- On submission, check if employee has a supervisor.
  - If YES: set status to `'Pending Dept Supervisor'` and notify the supervisor.
  - If NO: set status to `'Pending HR Consolidation'` and notify HR.

#### [MODIFY] [confirm-rating.php](file:///c:/xampp/htdocs/raquel-hris/employee/confirm-rating.php)
- Change pending checks and filter queries to use `'Pending Dept Supervisor'` instead of the legacy `'Pending Supervisor'`.
- On `confirm_and_send` action:
  - Check if the employee has a department manager (supervisor of supervisor).
  - If YES: Update status to `'Pending Dept Manager'`, record `dept_supervisor_confirmed_by` details, and notify the Dept Manager.
  - If NO: Update status to `'Pending HR Consolidation'`, record confirmation details, and notify HR.

#### [NEW] [dept-manager-review.php](file:///c:/xampp/htdocs/raquel-hris/employee/dept-manager-review.php)
- Create a dedicated review dashboard for department managers (using `Employee` portal aesthetics).
- Display a list of pending ratings with status `'Pending Dept Manager'` that are in the manager's hierarchy.
- Provide a full review form displaying the self-rating and immediate supervisor's comments/scores.
- Actions:
  - **Endorse:** Set status to `'Pending HR Consolidation'`, save `dept_manager_comments`, `dept_manager_endorsed_by`, `dept_manager_endorsed_date`, and notify HR Supervisor.
  - **Return:** Set status to `'Returned'` with comments, notifying the supervisor or employee.

#### [MODIFY] [header.php](file:///c:/xampp/htdocs/raquel-hris/includes/header.php)
- Add a new "Dept Manager Review" sidebar navigation item under the Employee Portal section.
- Visually toggle its visibility dynamically using `isDeptManagerRole($conn, $employee_id)`.

#### [MODIFY] [dashboard.php](file:///c:/xampp/htdocs/raquel-hris/employee/dashboard.php)
- Update progress step timeline component and stat counters to accommodate the new department supervisor and department manager stages.

---

### HR Portal Component

#### [MODIFY] [pending-endorsements.php](file:///c:/xampp/htdocs/raquel-hris/supervisor/pending-endorsements.php)
- Update SQL queries to look specifically for status `'Pending HR Consolidation'` instead of `'Pending Supervisor'`. This ensures HR Supervisors only receive evaluations after all department-level reviews are fully complete.

---

## Verification Plan

### Automated/Manual Verification
1. **Database Update:** Run a script or visit any evaluation page to trigger `ensureEvaluationWorkflowSchema()` and verify the database columns and new enum options are correctly altered.
2. **Standard Workflow Test:**
   - Log in as a standard employee (e.g., Marketing Employee). Submit a self-rating. Verify status is `'Pending Dept Supervisor'` and immediate supervisor is notified.
   - Log in as the Dept Supervisor (e.g., Marketing Supervisor). Confirm the rating. Verify status changes to `'Pending Dept Manager'` and Dept Manager is notified.
   - Log in as the Dept Manager (e.g., Marketing Manager). Endorse the rating. Verify status changes to `'Pending HR Consolidation'` and HR Supervisor is notified.
   - Log in as HR Supervisor. Endorse it to `'Pending Manager'`.
   - Log in as HR Manager. Approve the evaluation.
3. **Fallback Workflow Test:**
   - Submit an evaluation for an employee with NO supervisor. Verify it directly goes to `'Pending HR Consolidation'`.
   - Submit an evaluation for an employee with a supervisor who has no manager. Confirm it as supervisor. Verify it bypasses manager and goes directly to `'Pending HR Consolidation'`.
