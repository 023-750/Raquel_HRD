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

1. WHEN a Branch Supervisor accesses `/employee/career-movement-request.php`, THE Career_Movement_Form SHALL display only "Transfer" as a selectable movement type in the movement type dropdown.
2. WHEN a POST request is received at `/employee/career-movement-request.php` with a `movement_type` value other than `Transfer`, THE System SHALL reject the submission and return a validation error without creating a `career_movements` record.
3. THE Career_Movement_Form SHALL require selection of a destination branch (`new_branch_id`) and SHALL display a validation error if no destination branch is selected on a Transfer submission.
4. WHEN a user with `role = 'Employee'` and `rank_category_id` other than 4 (Branch Supervisor) accesses `/employee/career-movement-request.php`, THE System SHALL display an informational message indicating the feature is restricted to Branch Supervisors.

---

### Requirement 2: Branch-Scoped Employee Selection

**User Story:** As a Branch Supervisor, I want to select any employee from my own branch when submitting a Transfer request, so that I can initiate movements for any team member assigned to my branch, not just my direct reports.

#### Acceptance Criteria

1. WHEN a Branch Supervisor opens the Transfer request form, THE Career_Movement_Form SHALL populate the employee dropdown with all active employees (`is_active = 1`) whose `branch_id` matches the Branch Supervisor's own `branch_id`.
2. WHEN a POST request is received with an `employee_id` that does not belong to the submitting Branch Supervisor's branch, THE System SHALL reject the submission with a validation error and SHALL NOT create a `career_movements` record.
3. THE Career_Movement_Form SHALL exclude the submitting Branch Supervisor themselves from the employee dropdown.
4. WHILE no active employees are found in the Branch Supervisor's branch, THE Career_Movement_Form SHALL display an informational message indicating no eligible employees are available for transfer.

---

### Requirement 3: Multi-Step Approval Workflow Schema

**User Story:** As a system administrator, I want the database to support a four-step approval chain for Employee Portal Transfer requests, so that each approver's decision is recorded independently and the current stage is always queryable.

#### Acceptance Criteria

1. THE System SHALL add a `portal_workflow_stage` column to the `career_movements` table with values: `Pending_Branch_Manager`, `Pending_HR_Supervisor`, `Pending_HR_Manager`, `Approved`, `Rejected`, defaulting to `NULL` for HR Portal requests.
2. THE System SHALL add a `branch_manager_approved_by` column (INT, FK to `users.user_id`) to record the Branch Manager who approved or rejected a Portal_Request.
3. THE System SHALL add a `branch_manager_decision_date` column (DATETIME) to record when the Branch Manager acted on a Portal_Request.
4. THE System SHALL add a `branch_manager_comments` column (TEXT, nullable) to record optional Branch Manager remarks.
5. THE System SHALL add a `hr_supervisor_approved_by` column (INT, FK to `users.user_id`) to record the HR Supervisor who approved or rejected a Portal_Request at their stage.
6. THE System SHALL add a `hr_supervisor_decision_date` column (DATETIME) to record when the HR Supervisor acted on a Portal_Request.
7. THE System SHALL add a `hr_supervisor_comments` column (TEXT, nullable) to record optional HR Supervisor remarks.
8. WHEN `ensureCareerProgressionMovements()` is called and any of the columns defined in criteria 1–7 do not exist, THE System SHALL execute the corresponding `ALTER TABLE` statements to add the missing columns without modifying existing data.
9. IF any `ALTER TABLE` statement in criterion 8 fails, THEN THE System SHALL log the failure to the PHP error log and return `false` from `ensureCareerProgressionMovements()`.

---

### Requirement 4: Portal Request Submission and Initial Routing

**User Story:** As a Branch Supervisor, I want my Transfer request to enter the approval chain at the Branch Manager stage, so that my Branch Manager has the first opportunity to review and endorse or reject the request before it escalates.

#### Acceptance Criteria

1. WHEN a Branch Supervisor submits a valid Transfer request via `/employee/career-movement-request.php`, THE System SHALL insert a `career_movements` record with `request_source = 'Employee Portal'`, `approval_status = 'Pending'`, and `portal_workflow_stage = 'Pending_Branch_Manager'`.
2. WHEN a Portal_Request is inserted, THE System SHALL identify the Branch Manager for the submitting branch by querying `employees` for an active employee in the same `branch_id` with `rank_category_id = 3`, then resolving their linked Employee Portal `users` record.
3. WHEN a Branch Manager user account is found, THE System SHALL send a Notification to that Branch Manager containing the submitter's name, the target employee's name, and a link to the Branch Manager's approval page.
4. IF no active Branch Manager with `rank_category_id = 3` is found for the submitting branch, THEN THE System SHALL set `portal_workflow_stage = 'Pending_HR_Supervisor'` and notify all active HR Supervisors instead, bypassing the Branch Manager step.
5. THE System SHALL record the submitting user's `user_id` in `logged_by` and the submitting employee's name and job title in `initiated_by_name` and `initiated_by_role`.

---

### Requirement 5: Branch Manager Approval Step

**User Story:** As a Branch Manager, I want to review, approve, or reject Transfer requests submitted by Branch Supervisors in my branch from the Employee Portal, so that I can endorse or stop requests before they reach HR.

#### Acceptance Criteria

1. THE Employee_Portal SHALL provide a page accessible to users with `role = 'Employee'` and `rank_category_id = 3` to view Portal_Requests where `portal_workflow_stage = 'Pending_Branch_Manager'` and `previous_branch_id` matches the Branch Manager's own `branch_id`.
2. WHEN a Branch Manager submits an Approve action for a Portal_Request, THE System SHALL update `portal_workflow_stage = 'Pending_HR_Supervisor'`, set `branch_manager_approved_by` and `branch_manager_decision_date`, and send a Notification to all active HR Supervisors.
3. WHEN a Branch Manager submits a Reject action for a Portal_Request, THE System SHALL update `portal_workflow_stage = 'Rejected'`, `approval_status = 'Rejected'`, set `branch_manager_approved_by`, `branch_manager_decision_date`, and `branch_manager_comments`, and send a Notification to the submitting Branch Supervisor.
4. IF a Branch Manager attempts to approve or reject a Portal_Request whose `previous_branch_id` does not match their own `branch_id`, THEN THE System SHALL reject the action with an authorization error and SHALL NOT modify the record.
5. THE Branch_Manager_Approval_Page SHALL display the target employee's name, current position, current branch, requested destination branch, effective date, and the submitter's name and reason.

---

### Requirement 6: HR Supervisor Approval Step

**User Story:** As an HR Supervisor, I want to see Employee Portal Transfer requests that have been approved by the Branch Manager, so that I can review them and escalate to the HR Manager or reject them.

#### Acceptance Criteria

1. WHEN the HR Supervisor portal (`/supervisor/career-movements.php`) loads its pending movements list, THE System SHALL include Portal_Requests with `portal_workflow_stage = 'Pending_HR_Supervisor'` in the pending queue alongside HR_Portal_Requests with `approval_status = 'Pending'`.
2. WHEN an HR Supervisor approves a Portal_Request with `portal_workflow_stage = 'Pending_HR_Supervisor'`, THE System SHALL update `portal_workflow_stage = 'Pending_HR_Manager'`, set `hr_supervisor_approved_by` and `hr_supervisor_decision_date`, and send a Notification to all active HR Managers.
3. WHEN an HR Supervisor rejects a Portal_Request with `portal_workflow_stage = 'Pending_HR_Supervisor'`, THE System SHALL update `portal_workflow_stage = 'Rejected'`, `approval_status = 'Rejected'`, set `hr_supervisor_approved_by`, `hr_supervisor_decision_date`, and `hr_supervisor_comments`, and send a Notification to the submitting Branch Supervisor.
4. WHEN an HR Supervisor acts on an HR_Portal_Request (created by HR Supervisor via the HRIS form), THE System SHALL follow the existing single-step workflow unchanged: the action sets `approval_status` directly and notifies the HR Manager.
5. THE Supervisor_Portal SHALL visually distinguish Portal_Requests (source badge "Branch Head Requisition") from HR_Portal_Requests in the movements list.

---

### Requirement 7: HR Manager Final Approval Step

**User Story:** As an HR Manager, I want to see Transfer requests that have passed the Branch Manager and HR Supervisor stages, so that I can make the final approval decision and trigger the movement's application.

#### Acceptance Criteria

1. WHEN the HR Manager portal (`/manager/career-movements.php`) loads its pending movements list, THE System SHALL include Portal_Requests with `portal_workflow_stage = 'Pending_HR_Manager'` in the pending queue.
2. WHEN an HR Manager approves a Portal_Request with `portal_workflow_stage = 'Pending_HR_Manager'`, THE System SHALL update `portal_workflow_stage = 'Approved'`, `approval_status = 'Approved'`, set `approved_by` and `decision_date`, apply the movement if the effective date has passed, and send a Notification to the submitting Branch Supervisor.
3. WHEN an HR Manager rejects a Portal_Request with `portal_workflow_stage = 'Pending_HR_Manager'`, THE System SHALL update `portal_workflow_stage = 'Rejected'`, `approval_status = 'Rejected'`, set `approved_by`, `decision_date`, and `manager_comments`, and send a Notification to the submitting Branch Supervisor.
4. THE existing HR Manager approval logic for HR_Portal_Requests (where `request_source = 'HR Portal'`) SHALL remain unchanged.
5. WHEN the HR Manager portal displays a Portal_Request, THE System SHALL show the names of the Branch Manager and HR Supervisor who previously approved it, along with their decision dates.

---

### Requirement 8: Status Tracking for Branch Supervisors

**User Story:** As a Branch Supervisor, I want to see the current approval stage of each Transfer request I have submitted on the same page as the submission form, so that I can track progress without contacting HR.

#### Acceptance Criteria

1. WHEN a Branch Supervisor views `/employee/career-movement-request.php`, THE Page SHALL display a list of all Portal_Requests submitted by that Branch Supervisor, ordered by submission date descending.
2. THE Status_Display SHALL show a human-readable label for each `portal_workflow_stage` value: `Pending_Branch_Manager` as "Pending Branch Manager", `Pending_HR_Supervisor` as "Pending HR Supervisor", `Pending_HR_Manager` as "Pending HR Manager", `Approved` as "Approved", `Rejected` as "Rejected".
3. THE Status_Display SHALL use distinct visual badge colors for each stage: amber/yellow for any "Pending" stage, green for "Approved", and red for "Rejected".
4. WHEN a Portal_Request has `approval_status = 'Rejected'`, THE Status_Display SHALL show the rejection reason (from the rejecting approver's comments field) alongside the Rejected badge.
5. THE Status_Display SHALL show for each request: the request reference number, target employee name, destination branch, effective date, submission date, and current stage badge.

---

### Requirement 9: Notification Rules

**User Story:** As any participant in the approval chain, I want to receive an in-system notification when action is required from me or when a request I submitted has a final outcome, so that no approval step is missed or delayed.

#### Acceptance Criteria

1. WHEN a Portal_Request enters `portal_workflow_stage = 'Pending_Branch_Manager'`, THE Notification_System SHALL send a Notification to the Branch Manager of the submitting branch containing the submitter name, target employee name, movement type, and a link to the Branch Manager approval page.
2. WHEN a Portal_Request enters `portal_workflow_stage = 'Pending_HR_Supervisor'`, THE Notification_System SHALL send a Notification to all active HR Supervisors containing the submitter name, target employee name, movement type, and a link to `/supervisor/career-movements.php`.
3. WHEN a Portal_Request enters `portal_workflow_stage = 'Pending_HR_Manager'`, THE Notification_System SHALL send a Notification to all active HR Managers containing the submitter name, target employee name, movement type, and a link to `/manager/career-movements.php`.
4. WHEN a Portal_Request reaches `portal_workflow_stage = 'Approved'`, THE Notification_System SHALL send a Notification to the submitting Branch Supervisor's linked Employee Portal user account indicating final approval.
5. WHEN a Portal_Request reaches `portal_workflow_stage = 'Rejected'` at any stage, THE Notification_System SHALL send a Notification to the submitting Branch Supervisor's linked Employee Portal user account indicating rejection and the stage at which it was rejected.
6. IF the submitting Branch Supervisor's linked Employee Portal user account cannot be resolved, THEN THE Notification_System SHALL skip the submitter notification without causing a PHP error and SHALL log a warning to the PHP error log.

---

### Requirement 10: HR-Side Flow Preservation

**User Story:** As an HR Supervisor, I want my existing ability to directly create all four movement types (Promotion, Transfer, Demotion, Role Change) for any employee via the HRIS portal to remain fully functional, so that the new Employee Portal chain does not disrupt day-to-day HR operations.

#### Acceptance Criteria

1. THE HR_Supervisor_Portal SHALL continue to allow creation of Promotion, Transfer, Demotion, and Role Change movements with `request_source = 'HR Portal'` without any `portal_workflow_stage` value (NULL).
2. WHEN an HR Supervisor creates a movement via the HRIS form, THE System SHALL set `approval_status = 'Pending'` and notify the HR Manager exactly as the current code does, with no additional approval steps.
3. THE HR_Manager_Portal approval logic for HR_Portal_Requests SHALL use `approval_status = 'Pending'` as the filter criterion and SHALL NOT be affected by the introduction of `portal_workflow_stage`.
4. THE HR_Staff_Portal (`/staff/career-movements.php`) approval and creation logic for leaderless branches SHALL remain unchanged.
5. WHEN `applyDueCareerProgressionMovements()` is called, THE System SHALL apply movements where `approval_status = 'Approved'` and `effective_date <= CURDATE()` and `is_applied = 0`, regardless of whether `portal_workflow_stage` is set.

---

### Requirement 11: Access Control and Authorization

**User Story:** As a system administrator, I want each portal page to enforce role and rank-based access controls, so that only the correct approver can act on each stage of a Portal_Request.

#### Acceptance Criteria

1. THE Branch_Manager_Approval_Page SHALL be accessible only to users with `role = 'Employee'` and `rank_category_id = 3`; any other user attempting to access it SHALL be redirected with an authorization error.
2. WHEN a Branch Manager submits an approval or rejection action, THE System SHALL verify that the `previous_branch_id` of the targeted Portal_Request matches the acting Branch Manager's own `branch_id` before committing the update.
3. WHEN an HR Supervisor submits an approve or reject action on a Portal_Request via `/supervisor/career-movements.php`, THE System SHALL verify that `portal_workflow_stage = 'Pending_HR_Supervisor'` before committing the update.
4. WHEN an HR Manager submits an approve or reject action on a Portal_Request via `/manager/career-movements.php`, THE System SHALL verify that `portal_workflow_stage = 'Pending_HR_Manager'` before committing the update.
5. IF any stage-mismatch is detected (e.g., acting on a request already decided or at a different stage), THEN THE System SHALL redirect with a danger flash message and SHALL NOT modify the record.
