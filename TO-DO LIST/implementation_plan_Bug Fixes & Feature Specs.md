# Implementation Plan - Employee Portal major tweaks

This implementation plan outlines the fixes and workflow upgrades for the Employee Portal's evaluation module, covering live notifications, draft logic, approval workflow, and role-based review pages.

## User Review Required

> [!IMPORTANT]
> - **Immediate Head Definition:** We identify the "Immediate Head" of an employee by checking their direct `reports_to` reference first. If it is not assigned, the system queries the same branch for any active employee with the Supervisor rank (`rank_category_id = 4`). If none is found, it queries for an employee with the Manager rank (`rank_category_id = 3`). If neither exists, the employee is treated as a standalone employee, and the evaluation is forwarded directly to the HR Supervisor.
> - **Direct HR Supervisor Forwarding:** Under the new standard flow, once the Immediate Head reviews and approves an evaluation, it is routed directly to the HR Supervisor (status: `Pending HR Consolidation`), bypassing the `Pending Dept Manager` step unless a department manager review is explicitly required outside the branch chain.
> - **Draft Discarding:** We are introducing a "Discard Draft" action. Discarding a draft marks the evaluation record as deleted (`deleted_at = NOW()`), making the template immediately available again under "Available Templates".

## Open Questions

> [!NOTE]
> None at the moment. The requirements are clear and align directly with the database structure and logical flow of the system.

## Proposed Changes

### 1. Notification Center (BUG-001)

#### [NEW] [get-unread-notifications.php](file:///c:/xampp/htdocs/Raquel_HRD_System/includes/ajax/get-unread-notifications.php)
- Create a new AJAX endpoint that queries the database for the current user's unread notification counts and any new notifications.
- Support a `last_seen_id` parameter to only fetch notifications created after that ID to prevent duplicate popups.

#### [MODIFY] [main.js](file:///c:/xampp/htdocs/Raquel_HRD_System/assets/js/main.js)
- Implement a JavaScript interval (polling every 10 seconds) that calls the new AJAX endpoint.
- Add `showLiveToast(title, message, link)` to dynamically render sticky notification banners on the screen using the pre-existing `.flash-message-banner` styles.
- Add helper functions to dynamically update the desktop header notification badge, the mobile bottom-navigation badge, the header notification dropdown list, and the notification center page list (`notifications.php`) without requiring page refreshes.

---

### 2. Evaluation Status Timestamps (BUG-002)

#### [MODIFY] [completed-ratings.php](file:///c:/xampp/htdocs/Raquel_HRD_System/employee/completed-ratings.php)
- Change the "Last Updated" display logic to show `updated_at` directly (or prioritize it) instead of coalescing with `submitted_date` via `$eval['submitted_date'] ?? $eval['updated_at']`. This ensures changes made by supervisors or managers post-submission are correctly reflected in the timestamp.

---

### 3. Draft Duplication & Template Visibility (BUG-003, BUG-004)

#### [MODIFY] [self-rating.php](file:///c:/xampp/htdocs/Raquel_HRD_System/employee/self-rating.php)
- **Template Selection Filtering:** Update the SQL query for available templates to exclude templates that have an existing active evaluation in `'Draft'`, `'Returned'`, or `'Pending Self-Rating'` status.
- **Auto-redirect to Existing Draft:** In the template selection and form initialization logic, check if an evaluation draft already exists for the selected template. If it does, automatically redirect the user to continue from the existing draft instead of initializing a new form.
- **Draft Duplication Prevention on Post:** In the form handler, check if a draft already exists for the template before performing an insert. If a draft exists, update that draft instead of inserting a duplicate record.
- **"Continue Draft / In Progress" Section:** Display a prominent list of all in-progress evaluations (Draft, Returned, and Pending Self-Rating) on the default page view when no template is selected.
- **Discard Draft Action:** Add a POST and GET handler for discarding a draft. Setting `deleted_at = NOW()` soft-deletes the draft and frees up the template. Add a "Discard Draft" button in the form.

---

### 4. 360° Evaluation Flow & Immediate Head Review Page (Workflow Spec, Page Requirement)

#### [MODIFY] [functions.php](file:///c:/xampp/htdocs/Raquel_HRD_System/includes/functions.php)
- **Immediate Head Lookup:** Update `getEmployeeSupervisor($conn, $employee_id)` to:
  1. Check if the employee has a direct `reports_to` assigned.
  2. If not, check if there is an active employee in the same branch who is a Supervisor (`rank_category_id = 4` or has "Supervisor" in their title).
  3. If not, check if there is an active employee in the same branch who is a Manager (`rank_category_id = 3` or has "Manager" in their title).
  4. If none, return `null`.

#### [MODIFY] [confirm-rating.php](file:///c:/xampp/htdocs/Raquel_HRD_System/employee/confirm-rating.php)
- **Role-based Access Control:** Enforce strict access control at the top of the file. If `hasEmployeeSubordinates($conn, $supervisor_employee_id)` is false, redirect the user back to the dashboard with an "Access Denied" error message.
- **Forwarding Path:** Update the approval action to set the next status directly to `'Pending HR Consolidation'` (routing it to the HR Supervisor) and notify the HR team, skipping the intermediate department manager routing.
- **Traceability & Audit Logs:** Modify the rating confirmation process to loop through scores, log precisely which criteria were altered (with original vs. new ratings) in the audit logs, and display the evaluation's audit history at the bottom of the review page.

---

## Verification Plan

### Automated Tests
We will perform visual checks and database queries to verify:
1. No duplicate drafts can be created via POST or UI template selection.
2. Notifications are pushed in real-time to the screen when a subordinate submits an evaluation, or when it moves up the chain.

### Manual Verification
1. **Log in as Employee:**
   - Select a template and save it as a draft.
   - Verify that the template is hidden from "Available Templates" and is shown under the "Continue Draft / In Progress" section.
   - Try to access the template via URL parameter `?template=[id]` and verify it redirects to the draft.
   - Discard the draft and verify the template reappears in "Available Templates".
   - Reselect a template, fill ratings, and click "Submit".
2. **Log in as Immediate Head:**
   - Verify access is allowed, while regular employees are blocked and redirected.
   - Open the subordinate's evaluation, alter some ratings, and click "Confirm and Send".
   - Verify that the audit logs record the exact rating modifications.
   - Verify a live notification appears immediately on the HR Supervisor's screen without requiring a page refresh.
3. **Log in as HR Supervisor:**
   - Verify the evaluation is present in the queue under `'Pending HR Consolidation'`.
   - Lock and forward it.
4. **Log in as HR Manager:**
   - Verify the evaluation is present in the approvals queue, approve it, and mark it as completed.
