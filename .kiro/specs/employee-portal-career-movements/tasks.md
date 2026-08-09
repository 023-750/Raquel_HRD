# Implementation Plan: Employee Portal Career Movements

## Overview

Implement a four-step approval chain for Employee Portal Transfer requests in the Raquel Pawnshop PHP/MySQL HRIS. Branch Supervisors can submit Transfer-only requests; those requests flow through Branch Manager → HR Supervisor → HR Manager before final approval. All existing HR Portal flows remain unchanged. The implementation touches `includes/functions.php`, three existing PHP pages, and one new page.

## Tasks

- [ ] 1. Extend `ensureCareerProgressionMovements()` with schema migration
  - [ ] 1.1 Add seven new columns to the `$new_portal_columns` array inside `ensureCareerProgressionMovements()` in `includes/functions.php`
    - Add `portal_workflow_stage` ENUM column (NULL default) after `request_source`
    - Add `branch_manager_approved_by` INT NULL, `branch_manager_decision_date` DATETIME NULL, `branch_manager_comments` TEXT NULL
    - Add `hr_supervisor_approved_by` INT NULL, `hr_supervisor_decision_date` DATETIME NULL, `hr_supervisor_comments` TEXT NULL
    - Each column must be checked via `getCareerProgressionMovementColumns()` before executing `ALTER TABLE`
    - Wrap all DDL in the existing `try/catch (mysqli_sql_exception)` block; log failures and return `false`
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.8, 3.9_

  - [ ]* 1.2 Write property test for schema migration idempotency
    - **Property 4: Schema Migration Idempotency**
    - **Validates: Requirements 3.8**
    - Call `ensureCareerProgressionMovements()` 2–10 times with random call counts against a test DB
    - Assert the function returns `true` each time and the column count remains unchanged after the first call

- [ ] 2. Implement helper functions for portal workflow
  - [ ] 2.1 Add `buildAllowedMovementTypes($rank_category_id, $has_subordinates)` to `includes/functions.php`
    - Returns `['Transfer']` only when `$rank_category_id === 4` AND `$has_subordinates === true`
    - Returns empty array for all other combinations
    - _Requirements: 1.1, 1.2_

  - [ ]* 2.2 Write property test for transfer-only form restriction
    - **Property 1: Transfer-Only Form Restriction**
    - **Validates: Requirements 1.1, 1.2**
    - Generate random rank values (1–10) and random bool for `$has_subordinates`
    - Assert result equals `['Transfer']` only when rank=4 and has_subordinates=true; empty/restricted otherwise
    - Run ≥ 100 iterations; tag: `// Feature: employee-portal-career-movements, Property 1`

  - [ ] 2.3 Add `getBranchEmployeesForDropdown($conn, $sup_employee_id, $branch_id)` to `includes/functions.php`
    - Query: `SELECT employee_id, first_name, last_name, job_title FROM employees WHERE branch_id = ? AND is_active = 1 AND employee_id != ? ORDER BY last_name, first_name`
    - Returns array of employee rows excluding the supervisor themselves
    - _Requirements: 2.1, 2.2, 2.3_

  - [ ]* 2.4 Write property test for branch employee set completeness and exclusivity
    - **Property 3: Branch Employee Set Completeness and Exclusivity**
    - **Validates: Requirements 2.1, 2.2, 2.3**
    - Generate random branch sizes (1–50 employees) in a test DB, pick one as supervisor
    - Assert count = N − 1, all rows share the same `branch_id`, supervisor's `employee_id` absent
    - Run ≥ 100 iterations; tag: `// Feature: employee-portal-career-movements, Property 3`

  - [ ] 2.5 Add `validateTransferSubmission($conn, $submitter_branch_id, $submitter_emp_id, $employee_id, $new_branch_id)` to `includes/functions.php`
    - Returns validation error string if `movement_type !== 'Transfer'`, `$employee_id` not in submitter's branch, `$new_branch_id` is null/empty/same as employee's current branch, or a duplicate pending Portal_Request exists for `$employee_id`
    - Returns `null` on valid input
    - _Requirements: 1.2, 1.3, 2.2, 4.1_

  - [ ]* 2.6 Write property test for same-branch destination rejection
    - **Property 2: Same-Branch Destination Rejected**
    - **Validates: Requirements 1.3, 4.2**
    - Generate random employee+branch pairs where `new_branch_id` is null, empty, or equals the employee's current branch
    - Assert `validateTransferSubmission()` always returns a non-null error string
    - Run ≥ 100 iterations; tag: `// Feature: employee-portal-career-movements, Property 2`

  - [ ] 2.7 Add `getPortalStageLabel($stage)` to `includes/functions.php`
    - Map all five ENUM values to human-readable strings: `Pending_Branch_Manager` → "Pending Branch Manager", etc.
    - Returns the label string for valid ENUM values; returns empty string or safe fallback for unknown values
    - _Requirements: 8.2, 8.3_

  - [ ]* 2.8 Write property test for stage label round-trip
    - **Property 10: Stage Label Round-Trip**
    - **Validates: Requirements 8.2, 8.3**
    - Input all 5 ENUM values plus random strings
    - Assert non-empty string returned for all ENUM values; identical output on repeated calls (deterministic)
    - Run ≥ 100 iterations; tag: `// Feature: employee-portal-career-movements, Property 10`

  - [ ] 2.9 Add `applyStageTransition($current_stage, $action)` to `includes/functions.php`
    - Encodes valid transitions: BM Approve → `Pending_HR_Supervisor`, BM Reject → `Rejected`, HR Sup Approve → `Pending_HR_Manager`, HR Sup Reject → `Rejected`, HR Mgr Approve → `Approved`, HR Mgr Reject → `Rejected`
    - Returns the next stage string on valid input; returns `null` or throws on invalid/terminal-state transitions
    - _Requirements: 5.2, 5.3, 6.2, 6.3, 7.2, 7.3_

  - [ ]* 2.10 Write property test for valid stage transitions only
    - **Property 6: Valid Stage Transitions Only**
    - **Validates: Requirements 5.2, 5.3, 6.2, 6.3, 7.2, 7.4, 11.3, 11.4**
    - Generate all stage/action combinations including invalid ones
    - Assert output is always a member of the defined transition table; terminal states never transition; no stage is skipped
    - Run ≥ 100 iterations; tag: `// Feature: employee-portal-career-movements, Property 6`

  - [ ] 2.11 Add `checkApprovalAuthorization($movement, $actor_user_id, $actor_branch_id, $actor_role, $required_stage)` to `includes/functions.php`
    - Returns `false` if `portal_workflow_stage` does not match `$required_stage`
    - Returns `false` if Branch Manager action and `previous_branch_id !== $actor_branch_id`
    - Returns `false` if `$actor_user_id === $movement['logged_by']` (self-approval)
    - Returns `true` only when all checks pass
    - _Requirements: 5.4, 11.1, 11.2, 11.3, 11.4, 11.5_

  - [ ]* 2.12 Write property test for authorization guard
    - **Property 7: Authorization Guard — No Unauthorized Stage Actions**
    - **Validates: Requirements 5.4, 11.1, 11.2, 11.3, 11.4, 11.5, 11.6**
    - Generate random record/actor pairs with intentional mismatches (wrong stage, wrong branch, self-approval)
    - Assert `checkApprovalAuthorization()` returns `false` for every mismatch combination
    - Run ≥ 100 iterations; tag: `// Feature: employee-portal-career-movements, Property 7`

- [ ] 3. Checkpoint — Ensure all tests pass
  - Ensure all helper function tests pass, ask the user if questions arise.

- [ ] 4. Modify `/employee/career-movement-request.php` — Transfer form and status table
  - [ ] 4.1 Update access guard to require `rank_category_id = 4` AND at least one active branch employee
    - Replace or augment the existing `hasEmployeeSubordinates()` check with a branch-employee count check
    - Display the "access restricted" informational message if the user fails either condition
    - _Requirements: 1.4, 2.4_

  - [ ] 4.2 Restrict movement type dropdown to Transfer only
    - Render only `<option value="Transfer">Transfer</option>` in the movement type `<select>`
    - Remove Promotion, Demotion, Role Change options from the HTML
    - _Requirements: 1.1_

  - [ ] 4.3 Replace employee dropdown with branch-scoped query
    - Call `getBranchEmployeesForDropdown()` to populate the employee `<select>`
    - Show the "no eligible employees" informational message when the returned array is empty
    - _Requirements: 2.1, 2.3, 2.4_

  - [ ] 4.4 Add required "Destination Branch" field to the form
    - Add a mandatory `<select name="new_branch_id">` populated from the `branches` table (excluding the submitting employee's current branch)
    - Display a validation error if no destination branch is selected
    - _Requirements: 1.3_

  - [ ] 4.5 Add server-side POST validation using `validateTransferSubmission()`
    - Call `validateTransferSubmission()` on every POST; collect errors into `$errors[]`
    - Reject non-Transfer `movement_type` with message "Only Transfer requests may be submitted through the Employee Portal."
    - Reject out-of-branch `employee_id` with "The selected employee is not eligible."
    - Reject same/missing `new_branch_id` with "A different destination branch must be selected."
    - Reject duplicate pending Portal_Request with "A pending Transfer request already exists for this employee."
    - Re-render the form with POST values preserved on any error
    - _Requirements: 1.2, 2.2_

  - [ ] 4.6 Implement Portal_Request insertion with `portal_workflow_stage`
    - Insert with `request_source = 'Employee Portal'`, `approval_status = 'Pending'`, `portal_workflow_stage = 'Pending_Branch_Manager'`
    - Record `logged_by`, `initiated_by_name`, `initiated_by_role` from session
    - After insert, look up Branch Manager via branch employees query (`rank_category_id = 3`, `is_active = 1`)
    - Resolve BM's Employee Portal user via `getPreferredLinkedUserId($conn, $bm_emp_id, 'employee_portal')`
    - If BM user found: send notification to BM user_id with submitter name, target employee name, link to `/employee/branch-manager-approvals.php`
    - If BM user not found: UPDATE `portal_workflow_stage = 'Pending_HR_Supervisor'`, notify all active HR Supervisors
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 9.1, 9.2_

  - [ ]* 4.7 Write property test for portal request initial stage
    - **Property 5: Portal Request Initial Stage**
    - **Validates: Requirements 4.1, 4.3, 4.5**
    - Generate random valid Transfer submissions against test DB
    - Assert inserted record has `request_source = 'Employee Portal'`, `approval_status = 'Pending'`, and `portal_workflow_stage` ∈ `{Pending_Branch_Manager, Pending_HR_Supervisor}`
    - Run ≥ 100 iterations; tag: `// Feature: employee-portal-career-movements, Property 5`

  - [ ] 4.8 Implement status table for Branch Supervisor
    - Query: `WHERE cm.request_source = 'Employee Portal' AND cm.logged_by = ? AND cm.portal_workflow_stage IS NOT NULL ORDER BY cm.created_at DESC`
    - Display columns: ref # (zero-padded), target employee name, destination branch, effective date, submitted date, stage badge
    - Map stage labels using `getPortalStageLabel()`
    - Apply badge colors: `bg-warning text-dark` for any "Pending_*" stage, `bg-success` for Approved, `bg-danger` for Rejected
    - If `portal_workflow_stage = 'Rejected'`, display rejection reason from the first non-null of: `branch_manager_comments`, `hr_supervisor_comments`, `manager_comments`; fall back to "No reason provided"
    - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5_

- [ ] 5. Create `/employee/branch-manager-approvals.php` — New Branch Manager approval page
  - [ ] 5.1 Implement access guard and pending queue display
    - Guard: `checkRole(['Employee'])` + query `rank_category_id` from `employees`; redirect with danger message if not 3
    - Pending queue query: join `career_movements`, `employees`, `branches`, `users` filtering `portal_workflow_stage = 'Pending_Branch_Manager'` AND `previous_branch_id = $bm_branch_id`
    - Display columns: target employee name, current position, current branch, destination branch, effective date, submitted by name, reason/notes, Approve and Reject action buttons
    - Include CSRF field in all action forms
    - _Requirements: 5.1, 5.5, 11.1_

  - [ ] 5.2 Implement Approve POST handler
    - Call `checkApprovalAuthorization()` for stage + branch + self-approval guards; redirect on failure
    - UPDATE: `portal_workflow_stage = 'Pending_HR_Supervisor'`, `branch_manager_approved_by = $current_user_id`, `branch_manager_decision_date = NOW()`
    - Query all active HR Supervisors (`role = 'HR Supervisor' AND is_active = 1`) and call `createNotification()` for each
    - Call `logAudit()` with action `'APPROVE'` and entity `'Career Movement'`
    - _Requirements: 5.2, 9.2, 11.2_

  - [ ] 5.3 Implement Reject POST handler
    - Call `checkApprovalAuthorization()` guards; redirect on failure
    - Require non-empty `branch_manager_comments`
    - UPDATE: `portal_workflow_stage = 'Rejected'`, `approval_status = 'Rejected'`, `branch_manager_approved_by`, `branch_manager_decision_date = NOW()`, `branch_manager_comments`
    - Resolve submitter's Employee Portal user via `getPreferredLinkedUserId()`; send rejection notification; silently skip and log if null
    - Call `logAudit()`
    - _Requirements: 5.3, 9.5, 9.6, 11.2_

- [ ] 6. Checkpoint — Ensure all tests pass
  - Ensure all tests pass for the new branch-manager-approvals page, ask the user if questions arise.

- [ ] 7. Modify `/supervisor/career-movements.php` — HR Supervisor approval step
  - [ ] 7.1 Extend the pending queue query to include Portal_Requests at `Pending_HR_Supervisor`
    - Replace or extend the existing `approval_status = 'Pending'` WHERE clause with the combined condition:
      ```sql
      WHERE (
        (cm.approval_status = 'Pending' AND (cm.portal_workflow_stage IS NULL OR cm.request_source = 'HR Portal'))
        OR
        (cm.request_source = 'Employee Portal' AND cm.portal_workflow_stage = 'Pending_HR_Supervisor')
      )
      ```
    - Ensure HR_Portal_Requests still appear unchanged
    - _Requirements: 6.1, 10.1, 10.2_

  - [ ] 7.2 Add source badge distinguishing Portal_Requests from HR_Portal_Requests in the table
    - Render "Branch Head Requisition" badge (`bg-info text-dark`) for `request_source = 'Employee Portal'`
    - Keep existing badge for HR Portal records
    - _Requirements: 6.5_

  - [ ] 7.3 Implement differentiated Approve action for Portal_Requests
    - Detect `$is_portal_request` flag (`request_source = 'Employee Portal'` AND `portal_workflow_stage IS NOT NULL`)
    - For Portal_Requests: verify `portal_workflow_stage = 'Pending_HR_Supervisor'` via `checkApprovalAuthorization()`; redirect on mismatch
    - UPDATE: `portal_workflow_stage = 'Pending_HR_Manager'`, `hr_supervisor_approved_by`, `hr_supervisor_decision_date = NOW()`
    - Notify all active HR Managers via `createNotification()`
    - For HR_Portal_Requests: execute existing unchanged single-step flow
    - _Requirements: 6.2, 6.4, 9.3, 11.3_

  - [ ] 7.4 Implement differentiated Reject action for Portal_Requests
    - For Portal_Requests at `Pending_HR_Supervisor`: validate `hr_supervisor_comments` (1–1000 chars)
    - UPDATE: `portal_workflow_stage = 'Rejected'`, `approval_status = 'Rejected'`, `hr_supervisor_approved_by`, `hr_supervisor_decision_date = NOW()`, `hr_supervisor_comments`
    - Resolve submitter's Employee Portal user; notify; silently skip and log if null
    - For HR_Portal_Requests: existing flow unchanged
    - _Requirements: 6.3, 6.4, 9.5, 9.6, 11.3_

- [ ] 8. Modify `/manager/career-movements.php` — HR Manager final approval step
  - [ ] 8.1 Extend the pending queue query to include Portal_Requests at `Pending_HR_Manager`
    - Replace or extend WHERE clause:
      ```sql
      WHERE (
        (cm.approval_status = 'Pending' AND cm.portal_workflow_stage IS NULL)
        OR
        (cm.request_source = 'Employee Portal' AND cm.portal_workflow_stage = 'Pending_HR_Manager')
      )
      ```
    - Ensure HR_Portal_Requests (stage IS NULL) still appear
    - _Requirements: 7.1, 10.3_

  - [ ] 8.2 Implement differentiated Approve action for Portal_Requests
    - Detect `$is_portal_request` and verify `portal_workflow_stage = 'Pending_HR_Manager'` via `checkApprovalAuthorization()`
    - UPDATE: `portal_workflow_stage = 'Approved'`, `approval_status = 'Approved'`, `approved_by`, `decision_date = NOW()`, `manager_comments`, `is_applied = 0`
    - If `effective_date <= date('Y-m-d')`: call `executeCareerMovementApplication()`
    - Notify submitting Branch Supervisor ("Your Transfer request has been fully approved.")
    - For HR_Portal_Requests: existing flow unchanged
    - _Requirements: 7.2, 7.4, 9.4, 10.3, 10.5, 11.4_

  - [ ] 8.3 Implement differentiated Reject action for Portal_Requests
    - For Portal_Requests at `Pending_HR_Manager`: UPDATE `portal_workflow_stage = 'Rejected'`, `approval_status = 'Rejected'`, `approved_by`, `decision_date = NOW()`, `manager_comments`
    - Notify submitting Branch Supervisor of rejection
    - For HR_Portal_Requests: existing flow unchanged
    - _Requirements: 7.3, 9.5, 9.6, 11.4_

  - [ ] 8.4 Display approval chain history row for Portal_Requests
    - When showing a Portal_Request in the detail/modal view, display Branch Manager row: name (lookup from `branch_manager_approved_by`) + `branch_manager_decision_date`, or "Branch Manager step bypassed" if `branch_manager_approved_by IS NULL`
    - Display HR Supervisor row: name (lookup from `hr_supervisor_approved_by`) + `hr_supervisor_decision_date`
    - _Requirements: 7.5_

- [ ] 9. Checkpoint — Ensure all tests pass
  - Ensure all tests pass across all modified pages, ask the user if questions arise.

- [ ] 10. Write integration and property tests for end-to-end workflow
  - [ ]* 10.1 Write integration test: insert Portal_Request and verify row data
    - Insert a valid Portal_Request in a test DB transaction; verify `request_source`, `approval_status`, `portal_workflow_stage`, and all logged fields
    - Roll back after assertion
    - _Requirements: 4.1, 4.5_

  - [ ]* 10.2 Write integration test: full four-step approval chain
    - Step through all four approval actions (BM Approve → HR Sup Approve → HR Mgr Approve) in a test DB
    - After HR Manager Approve with `effective_date <= today`, verify `employees` table updated and `is_applied = 1`
    - _Requirements: 5.2, 6.2, 7.2, 10.5_

  - [ ]* 10.3 Write integration test: notifications at each stage transition
    - After each approval action, query the `notifications` table and assert the correct recipient(s) and message content
    - _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5_

  - [ ]* 10.4 Write property test for HR Portal records unaffected by portal workflow logic
    - **Property 8: HR Portal Records Unaffected by Portal Workflow Logic**
    - **Validates: Requirements 6.4, 7.5, 10.1, 10.2, 10.3, 10.4**
    - Generate HR Portal records (`request_source = 'HR Portal'`, `portal_workflow_stage IS NULL`); execute any Employee Portal approval action
    - Assert `portal_workflow_stage` remains NULL and the record still appears in HR Supervisor/Manager pending queue
    - Run ≥ 100 iterations; tag: `// Feature: employee-portal-career-movements, Property 8`

  - [ ]* 10.5 Write property test for approval status consistency
    - **Property 9: Approval Status Consistency**
    - **Validates: Requirements 5.3, 6.3, 7.2, 7.4**
    - Generate random state sequences of transitions on Portal_Request records
    - After each transition assert: Pending_* stage ↔ `approval_status = 'Pending'`; Approved stage ↔ `approval_status = 'Approved'`; Rejected stage ↔ `approval_status = 'Rejected'`
    - Run ≥ 100 iterations; tag: `// Feature: employee-portal-career-movements, Property 9`

  - [ ]* 10.6 Write property test for due movement application regardless of portal stage
    - **Property 11: Due Movement Application Regardless of Portal Stage**
    - **Validates: Requirements 10.5**
    - Generate records with `approval_status = 'Approved'`, `is_applied = 0`, `effective_date <= CURDATE()`, varying `portal_workflow_stage` values
    - Call `applyDueCareerProgressionMovements()` and assert all qualifying records are applied (employees table updated, `is_applied = 1`)
    - Run ≥ 100 iterations; tag: `// Feature: employee-portal-career-movements, Property 11`

- [ ] 11. Final checkpoint — Ensure all tests pass
  - Ensure all tests pass and ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- All property tests use a test database with transactions rolled back after each test; the `ensureCareerProgressionMovements()` migration runs once in the test setup
- The PBT library target is QuickCheck for PHP (or PHPUnit data providers with Faker for ≥ 100 random iterations per property)
- Each property test must include the tag comment: `// Feature: employee-portal-career-movements, Property N: <property text>`
- HR Portal records (`portal_workflow_stage IS NULL`) must never be accidentally touched — all queries that modify portal stage must include `AND request_source = 'Employee Portal'` or equivalent guard
- All POST handlers must call `verifyCsrfToken()` at the top and all forms must include `<?php echo csrfField(); ?>`
- Notification failures must never roll back an already-committed stage transition

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1.1"] },
    { "id": 1, "tasks": ["1.2", "2.1", "2.3", "2.5", "2.7", "2.9", "2.11"] },
    { "id": 2, "tasks": ["2.2", "2.4", "2.6", "2.8", "2.10", "2.12"] },
    { "id": 3, "tasks": ["4.1", "4.2", "4.3", "4.4", "4.5", "4.6", "4.8", "5.1"] },
    { "id": 4, "tasks": ["4.7", "5.2", "5.3", "7.1", "7.2", "8.1"] },
    { "id": 5, "tasks": ["7.3", "7.4", "8.2", "8.3", "8.4"] },
    { "id": 6, "tasks": ["10.1", "10.2", "10.3", "10.4", "10.5", "10.6"] }
  ]
}
```
