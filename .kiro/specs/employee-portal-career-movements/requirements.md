# Requirements Document

## Introduction

This feature enhances the Employee Portal career movement workflow for Raquel Pawnshop's PHP/MySQL HRIS system. Currently, Branch Supervisors can submit all four movement types (Promotion, Transfer, Demotion, Role Change) through the Employee Portal and those requests route directly to HR Supervisors, bypassing the Branch Manager. This feature restricts Employee Portal submissions to Transfer-only, introduces a four-step approval chain (Branch Supervisor → Branch Manager → HR Supervisor → HR Manager), provides granular status tracking for submitters, and sends targeted notifications at each stage. The existing HR-side (HRIS) flow for direct HR Supervisor submissions remains completely unchanged.

---

## Glossary

- **Branch_Supervisor**: An employee in the Employee Portal (`role = 'Employee'`, `rank_category_id = 4`) who holds supervisory authority over a branch and may submit Transfer requests on behalf of employees within their branch.
- **Branch_Manager**: An employee in the Employee Portal (`role = 'Employee'`, `rank_category_id = 3`) who serves as the first approver for Transfer requests submitted by a Branch Supervisor.
- **HR_Supervisor**: An HRIS system user (`role = 'HR Supervisor'`) who acts as the second approver in the Employee Portal chain and can independently create all four movement types via the HR portal.
- **HR_Manager**: An HRIS system user (`role = 'HR Manager'`) who is the final approver for all career movements regardless of source.
- **Portal_Request**: A career movement record with `request_source = 'Employee Portal'` submitted by a Branch Supervisor through `/employee/career-movement-request.php`.
- **Portal_Workflow_Stage**: A column added to `career_movements` that tracks the current step in the four-step Employee Portal approval chain. Valid values: `Pending_Branch_Manager`, `Pending_HR_Supervisor`, `Pending_HR_Manager`, `Approved`, `Rejected`.
- **HR_Portal_Request**: A career movement record with `request_source = 'HR Portal'` created directly by HR Supervisor or HR Staff. These follow the existing single-step approval flow and are unaffected by this feature.
- **Notification**: An in-system notification created via the `createNotification()` function and stored in the `notifications` table.
- **Transfer**: The only movement type (`movement_type = 'Transfer'`) that Branch Supervisors are permitted to submit through the Employee Portal.
- **Branch_Employees**: All active employees (`is_active = 1`) assigned to the same `branch_id` as the submitting Branch Supervisor, regardless of reporting line.

---

## Requirements

### Requirement 1: Transfer-Only Restriction for Branch Supervisors

**User Story:** As a Branch Supervisor, I want to submit only Transfer requests through the Employee Portal, so that the scope of my self-service authority is clearly defined and Promotions, Demotions, and Role Changes remain under exclusive HR control.

#### Acceptance Criteria

1. WHEN a Branch Supervisor (a user with `role = 'Employee'`, `rank_category_id = 4`, and at least one active subordinate as determined by `hasEmployeeSubordinates()`) accesses `/employee/career-movement-request.php`, THE Career_Movement_Form SHALL render the movement type dropdown containing only the option "Transfer" — the options "Promotion", "Demotion", and "Role Change" SHALL NOT be present in the rendered HTML.
2. WHEN a POST request is received at `/employee/career-movement-request.php` with a `movement_type` value other than `Transfer`, THE System SHALL reject the submission, display a validation error message, re-render the form with the previously submitted field values preserved, and SHALL NOT create a `career_movements` record.
3. IF a Transfer submission is received with `new_branch_id` absent, empty, or equal to the employee's current `branch_id`, THEN THE System SHALL reject the submission with a validation error indicating a different destination branch must be selected, and SHALL NOT create a `career_movements` record.
4. IF a user with `role = 'Employee'` has `rank_category_id` other than 4, OR has `rank_category_id = 4` but no active subordinates, THEN THE System SHALL NOT render the Transfer request form and SHALL display an informational message indicating this feature is restricted to Branch Supervisors.

---

### Requirement 2: Branch-Scoped Employee Selection

**User Story:** As a Branch Supervisor, I want to select any employee from my own branch when submitting a Transfer request, so that I can initiate movements for any team member assigned to my branch, not just my direct reports.

#### Acceptance Criteria

1. WHEN a Branch Supervisor opens the Transfer request form, THE Career_Movement_Form SHALL populate the employee dropdown with all active employees (`is_active = 1`) whose `branch_id` matches the Branch Supervisor's own `branch_id`, excluding the Branch Supervisor themselves from the list.
2. IF a POST request is received with an `employee_id` that does not belong to the submitting Branch Supervisor's branch, OR whose `employee_id` equals the submitting Branch Supervisor's own `employee_id`, THEN THE System SHALL reject the submission with a validation error indicating the selected employee is not eligible, and SHALL NOT create a `career_movements` record.
3. WHILE no active employees (other than the Branch Supervisor themselves) are found in the Branch Supervisor's branch, THE Career_Movement_Form SHALL display an informational message indicating no eligible employees are available for transfer, and SHALL disable the employee dropdown and submit button.

---

### Requirement 3: Multi-Step Approval Workflow Schema

**User Story:** As a system administrator, I want the database to support a four-step approval chain for Employee Portal Transfer requests, so that each approver's decision is recorded independently and the current stage is always queryable.

#### Acceptance Criteria

1. THE System SHALL add a `portal_workflow_stage` column to the `career_movements` table as an ENUM with values: `Pending_Branch_Manager`, `Pending_HR_Supervisor`, `Pending_HR_Manager`, `Approved`, `Rejected`, nullable with a DEFAULT of NULL, so that HR Portal requests retain NULL and Employee Portal requests always have an explicit stage value.
2. THE System SHALL add a `branch_manager_approved_by` column (INT NULL, FK to `users.user_id`) to record the user ID of the Branch Manager who approved or rejected a Portal_Request; NULL when no Branch Manager action has been taken.
3. THE System SHALL add a `branch_manager_decision_date` column (DATETIME NULL) to record the timestamp when the Branch Manager acted on a Portal_Request.
4. THE System SHALL add a `branch_manager_comments` column (TEXT NULL) to record optional Branch Manager remarks on a Portal_Request.
5. THE System SHALL add a `hr_supervisor_approved_by` column (INT NULL, FK to `users.user_id`) to record the user ID of the HR Supervisor who approved or rejected a Portal_Request at their stage.
6. THE System SHALL add a `hr_supervisor_decision_date` column (DATETIME NULL) to record the timestamp when the HR Supervisor acted on a Portal_Request.
7. THE System SHALL add a `hr_supervisor_comments` column (TEXT NULL) to record optional HR Supervisor remarks on a Portal_Request.
8. WHEN `ensureCareerProgressionMovements()` is called, THE System SHALL check for the existence of each column defined in criteria 1–7 independently and execute the corresponding `ALTER TABLE ADD COLUMN` statement for each missing column without modifying existing rows or columns.
9. IF an `ALTER TABLE` statement fails for a reason other than MySQL error 1060 (duplicate column — indicating the column already exists), THEN THE System SHALL log the specific failure message to the PHP error log and return `false` from `ensureCareerProgressionMovements()`.

---

### Requirement 4: Portal Request Submission and Initial Routing

**User Story:** As a Branch Supervisor, I want my Transfer request to enter the approval chain at the Branch Manager stage, so that my Branch Manager has the first opportunity to review and endorse or reject the request before it escalates.

#### Acceptance Criteria

1. WHEN a Branch Supervisor submits a valid Transfer request (non-empty `employee_id` belonging to their branch, `movement_type = 'Transfer'`, `new_branch_id` different from current branch, no existing pending Portal_Request for the same target employee) via `/employee/career-movement-request.php`, THE System SHALL insert a `career_movements` record with `request_source = 'Employee Portal'`, `approval_status = 'Pending'`, and `portal_workflow_stage = 'Pending_Branch_Manager'`.
2. IF a submission fails validation (missing fields, invalid employee, duplicate pending request, or same-branch destination), THEN THE System SHALL display a user-facing error message and SHALL NOT insert a `career_movements` record.
3. WHEN a Portal_Request is inserted, THE System SHALL query `employees` for an active employee in the same `branch_id` as the submitting Branch Supervisor with `rank_category_id = 3` and then resolve their linked Employee Portal `users` record (`role = 'Employee'`, `is_active = 1`).
4. IF an active Branch Manager employee exists but has no linked Employee Portal `users` record, THEN THE System SHALL treat this as equivalent to no Branch Manager found and fall through to criterion 5.
5. IF no active Branch Manager with `rank_category_id = 3` is found for the submitting branch, OR no linked Employee Portal user account can be resolved, THEN THE System SHALL update the inserted record to `portal_workflow_stage = 'Pending_HR_Supervisor'` and notify all active HR Supervisors (`role = 'HR Supervisor'`, `is_active = 1`), bypassing the Branch Manager step.
6. WHEN a Branch Manager user account is found, THE System SHALL send a Notification to that Branch Manager's `user_id` containing the submitter's name, the target employee's name, and a link to `/employee/branch-manager-approvals.php`.
7. WHEN a Portal_Request is successfully inserted, THE System SHALL record the submitting user's `user_id` in `logged_by`, the submitting employee's full name in `initiated_by_name`, and their job title in `initiated_by_role`.

---

### Requirement 5: Branch Manager Approval Step

**User Story:** As a Branch Manager, I want to review, approve, or reject Transfer requests submitted by Branch Supervisors in my branch from the Employee Portal, so that I can endorse or stop requests before they reach HR.

#### Acceptance Criteria

1. IF a user has `role = 'Employee'` and `rank_category_id = 3`, THEN THE System SHALL grant access to `/employee/branch-manager-approvals.php` and display all Portal_Requests where `portal_workflow_stage = 'Pending_Branch_Manager'` and `previous_branch_id` matches the Branch Manager's own `branch_id`; otherwise THE System SHALL redirect with a danger flash message indicating insufficient permissions.
2. WHEN a Branch Manager submits an Approve action for a Portal_Request, THE System SHALL update `portal_workflow_stage = 'Pending_HR_Supervisor'`, set `branch_manager_approved_by` to the acting Branch Manager's `user_id` and `branch_manager_decision_date` to the current timestamp, and send a Notification to all users with `role = 'HR Supervisor'` and `is_active = 1`.
3. WHEN a Branch Manager submits a Reject action for a Portal_Request, THE System SHALL update `portal_workflow_stage = 'Rejected'` and `approval_status = 'Rejected'`, set `branch_manager_approved_by` to the acting Branch Manager's `user_id`, `branch_manager_decision_date` to the current timestamp, and `branch_manager_comments` to the value provided by the Branch Manager (if any), and send a Notification to the submitting Branch Supervisor's linked Employee Portal user account.
4. IF a Branch Manager attempts to approve or reject a Portal_Request whose `previous_branch_id` does not match the acting Branch Manager's own `branch_id`, THEN THE System SHALL redirect with a danger flash message indicating unauthorized action and SHALL NOT modify the record.
5. THE Branch_Manager_Approval_Page SHALL display for each pending request: the target employee's name, current position, current branch, requested destination branch, effective date, the submitter's name, and the submitter-provided reason for the Transfer.

---

### Requirement 6: HR Supervisor Approval Step

**User Story:** As an HR Supervisor, I want to see Employee Portal Transfer requests that have been approved by the Branch Manager, so that I can review them and escalate to the HR Manager or reject them.

#### Acceptance Criteria

1. WHEN the HR Supervisor portal (`/supervisor/career-movements.php`) loads its pending movements list, THE System SHALL include Portal_Requests with `portal_workflow_stage = 'Pending_HR_Supervisor'` in the pending queue alongside HR_Portal_Requests with `approval_status = 'Pending'` and `portal_workflow_stage IS NULL`.
2. WHEN an HR Supervisor approves a Portal_Request with `portal_workflow_stage = 'Pending_HR_Supervisor'`, THE System SHALL update `portal_workflow_stage = 'Pending_HR_Manager'`, set `hr_supervisor_approved_by` to the acting HR Supervisor's `user_id` and `hr_supervisor_decision_date` to the current timestamp, and send a Notification to all users with `role = 'HR Manager'` and `is_active = 1`.
3. WHEN an HR Supervisor rejects a Portal_Request with `portal_workflow_stage = 'Pending_HR_Supervisor'`, THE System SHALL update `portal_workflow_stage = 'Rejected'` and `approval_status = 'Rejected'`, set `hr_supervisor_approved_by`, `hr_supervisor_decision_date`, and `hr_supervisor_comments` (1–1000 characters, required on rejection), and send a Notification to the submitting Branch Supervisor's linked Employee Portal user account.
4. WHEN an HR Supervisor acts on an HR_Portal_Request (`request_source = 'HR Portal'`, `approval_status = 'Pending'`, `portal_workflow_stage IS NULL`) via the approve/reject action, THE System SHALL set `approval_status` to `'Approved'` or `'Rejected'` accordingly and notify all active HR Managers via `createNotification()`, with no change to `portal_workflow_stage`.
5. THE Supervisor_Portal SHALL display a visible source label per row — readable without opening the record — distinguishing Portal_Requests (label: "Branch Head Requisition") from HR_Portal_Requests (label: "HR Portal").

---

### Requirement 7: HR Manager Final Approval Step

**User Story:** As an HR Manager, I want to see Transfer requests that have passed the Branch Manager and HR Supervisor stages, so that I can make the final approval decision and trigger the movement's application.

#### Acceptance Criteria

1. WHEN the HR Manager portal (`/manager/career-movements.php`) loads its pending movements list, THE System SHALL include Portal_Requests with `portal_workflow_stage = 'Pending_HR_Manager'` in the pending queue alongside HR_Portal_Requests with `approval_status = 'Pending'` and `portal_workflow_stage IS NULL`.
2. WHEN an HR Manager approves a Portal_Request with `portal_workflow_stage = 'Pending_HR_Manager'` and `effective_date <= CURDATE()`, THE System SHALL update `portal_workflow_stage = 'Approved'`, `approval_status = 'Approved'`, set `approved_by` and `decision_date`, immediately apply the movement (update `employees.job_title` and `employees.branch_id`, set `is_applied = 1`), and send a Notification to the submitting Branch Supervisor's linked Employee Portal user account.
3. WHEN an HR Manager approves a Portal_Request with `portal_workflow_stage = 'Pending_HR_Manager'` and `effective_date > CURDATE()`, THE System SHALL update `portal_workflow_stage = 'Approved'`, `approval_status = 'Approved'`, set `approved_by` and `decision_date`, leave `is_applied = 0` for scheduled application, and send a Notification to the submitting Branch Supervisor's linked Employee Portal user account.
4. WHEN an HR Manager rejects a Portal_Request with `portal_workflow_stage = 'Pending_HR_Manager'`, THE System SHALL update `portal_workflow_stage = 'Rejected'`, `approval_status = 'Rejected'`, set `approved_by`, `decision_date`, and `manager_comments` (optional), and send a Notification to the submitting Branch Supervisor's linked Employee Portal user account.
5. THE existing HR Manager approval logic for HR_Portal_Requests (where `request_source = 'HR Portal'` and `portal_workflow_stage IS NULL`) SHALL remain unmodified — the query filter SHALL use `approval_status = 'Pending'` without a `portal_workflow_stage` condition.
6. WHEN the HR Manager portal displays a Portal_Request that passed through the Branch Manager step, THE System SHALL show the Branch Manager's name and `branch_manager_decision_date`; WHEN the HR Supervisor step was completed, THE System SHALL show the HR Supervisor's name and `hr_supervisor_decision_date`; IF the Branch Manager step was bypassed, THE System SHALL display "Branch Manager step bypassed" in place of those fields.

---

### Requirement 8: Status Tracking for Branch Supervisors

**User Story:** As a Branch Supervisor, I want to see the current approval stage of each Transfer request I have submitted on the same page as the submission form, so that I can track progress without contacting HR.

#### Acceptance Criteria

1. WHEN a Branch Supervisor views `/employee/career-movement-request.php`, THE System SHALL query and display all Portal_Requests where `request_source = 'Employee Portal'` and `logged_by` matches the current user's `user_id`, ordered by `created_at` descending; records where `portal_workflow_stage IS NULL` SHALL be excluded from this list.
2. THE Status_Display SHALL show a human-readable label for each `portal_workflow_stage` value: `Pending_Branch_Manager` → "Pending Branch Manager", `Pending_HR_Supervisor` → "Pending HR Supervisor", `Pending_HR_Manager` → "Pending HR Manager", `Approved` → "Approved", `Rejected` → "Rejected".
3. THE Status_Display SHALL render distinct visual badge colors: amber/yellow (`bg-warning`) for any stage beginning with "Pending", green (`bg-success`) for "Approved", and red (`bg-danger`) for "Rejected".
4. IF a Portal_Request has `portal_workflow_stage = 'Rejected'`, THEN THE Status_Display SHALL show the rejection reason alongside the Rejected badge, sourcing from `branch_manager_comments` when rejected at the Branch Manager stage, `hr_supervisor_comments` when rejected at the HR Supervisor stage, or `manager_comments` when rejected at the HR Manager stage; if the applicable comments field is NULL or empty, THE System SHALL display "No reason provided".
5. THE Status_Display SHALL show for each request: the zero-padded reference number (`movement_id`), target employee's full name, destination branch name, effective date, submission date (`created_at`), and the current stage badge.

---

### Requirement 9: Notification Rules

**User Story:** As any participant in the approval chain, I want to receive an in-system notification when action is required from me or when a request I submitted has a final outcome, so that no approval step is missed or delayed.

#### Acceptance Criteria

1. WHEN a Portal_Request enters `portal_workflow_stage = 'Pending_Branch_Manager'`, THE Notification_System SHALL send a Notification to the Branch Manager's linked Employee Portal `user_id` containing the submitter name, target employee name, movement type, and a link to `/employee/branch-manager-approvals.php`.
2. WHEN a Portal_Request enters `portal_workflow_stage = 'Pending_HR_Supervisor'`, THE Notification_System SHALL send a Notification to all users with `role = 'HR Supervisor'` and `is_active = 1` containing the submitter name, target employee name, movement type, and a link to `/supervisor/career-movements.php`.
3. WHEN a Portal_Request enters `portal_workflow_stage = 'Pending_HR_Manager'`, THE Notification_System SHALL send a Notification to all users with `role = 'HR Manager'` and `is_active = 1` containing the submitter name, target employee name, movement type, and a link to `/manager/career-movements.php`.
4. WHEN a Portal_Request reaches `portal_workflow_stage = 'Approved'`, THE Notification_System SHALL send a Notification to the submitting Branch Supervisor's linked Employee Portal `user_id` containing the target employee name, movement type, and the text "Your Transfer request has been fully approved."
5. WHEN a Portal_Request reaches `portal_workflow_stage = 'Rejected'` at any stage, THE Notification_System SHALL send a Notification to the submitting Branch Supervisor's linked Employee Portal `user_id` containing the target employee name, movement type, the stage at which it was rejected (e.g., "Rejected at Branch Manager stage"), and a link to `/employee/career-movement-request.php`.
6. IF the submitting Branch Supervisor's linked Employee Portal `user_id` cannot be resolved via `getPreferredLinkedUserId()`, THEN THE Notification_System SHALL skip the submitter notification without raising a PHP error and SHALL log a warning-level message to the PHP error log.
7. IF no Branch Manager user account is found (criterion 1), or no active HR Supervisors exist (criterion 2), or no active HR Managers exist (criterion 3), THEN THE Notification_System SHALL skip the respective notification silently and log a warning to the PHP error log without halting the workflow stage transition.

---

### Requirement 10: HR-Side Flow Preservation

**User Story:** As an HR Supervisor, I want my existing ability to directly create all four movement types (Promotion, Transfer, Demotion, Role Change) for any employee via the HRIS portal to remain fully functional, so that the new Employee Portal chain does not disrupt day-to-day HR operations.

#### Acceptance Criteria

1. THE HR_Supervisor_Portal SHALL continue to allow creation of Promotion, Transfer, Demotion, and Role Change movements with `request_source = 'HR Portal'` and `portal_workflow_stage = NULL` (no value written to the column).
2. WHEN an HR Supervisor creates a movement via the HRIS form, THE System SHALL insert the record with `approval_status = 'Pending'` and send a Notification via `createNotification()` to all users with `role = 'HR Manager'` and `is_active = 1`, containing the movement type and target employee name — with no additional approval steps or `portal_workflow_stage` logic applied.
3. WHEN the HR Manager portal queries for pending movements, THE System SHALL use a filter of `approval_status = 'Pending'` without any `portal_workflow_stage` condition, so that HR_Portal_Requests (where `portal_workflow_stage IS NULL`) continue to appear in the pending queue.
4. THE HR_Staff_Portal (`/staff/career-movements.php`) SHALL retain its existing behavior: applying the `isBranchLeaderless()` check before creation, filtering the approval queue on `approval_status = 'Pending'`, and sending notifications to users with `role IN ('HR Supervisor', 'HR Manager')` — with no changes introduced by this feature.
5. WHEN `applyDueCareerProgressionMovements()` is called, THE System SHALL apply all records where `approval_status = 'Approved'` AND `effective_date <= CURDATE()` AND `is_applied = 0`, regardless of whether `portal_workflow_stage` is NULL, `'Approved'`, or any other value.

---

### Requirement 11: Access Control and Authorization

**User Story:** As a system administrator, I want each portal page to enforce role and rank-based access controls, so that only the correct approver can act on each stage of a Portal_Request.

#### Acceptance Criteria

1. IF a user does not have `role = 'Employee'` and `rank_category_id = 3`, THEN THE System SHALL deny access to `/employee/branch-manager-approvals.php` and redirect to the Employee Portal login page with a danger flash message indicating insufficient permissions.
2. WHEN a Branch Manager submits an approval or rejection action on a Portal_Request, THE System SHALL verify that the `previous_branch_id` of the targeted record matches the acting Branch Manager's own `branch_id`; IF the branch does not match, THE System SHALL redirect to the Branch Manager's portal dashboard with a danger flash message and SHALL NOT modify the record.
3. WHEN an HR Supervisor submits an approve or reject action on a Portal_Request via `/supervisor/career-movements.php`, THE System SHALL verify that `portal_workflow_stage = 'Pending_HR_Supervisor'` on the targeted record; IF the stage does not match, THE System SHALL redirect with a danger flash message and SHALL NOT modify the record.
4. WHEN an HR Manager submits an approve or reject action on a Portal_Request via `/manager/career-movements.php`, THE System SHALL verify that `portal_workflow_stage = 'Pending_HR_Manager'` on the targeted record; IF the stage does not match, THE System SHALL redirect with a danger flash message and SHALL NOT modify the record.
5. IF any branch-mismatch or stage-mismatch is detected at any portal, THEN THE System SHALL redirect to the acting user's portal dashboard with a danger flash message and SHALL NOT modify any `career_movements` record.
6. IF the acting user's `user_id` matches the `logged_by` value of the targeted Portal_Request (self-approval attempt), THEN THE System SHALL reject the action with a danger flash message and SHALL NOT modify the record, regardless of the current `portal_workflow_stage`.
