# Implementation Plan - Self-Rating Flow and Pagination Refactor

This plan addresses two main sets of issues:
1. **Self-Rating Approval Flow & Supervisor Authorization**: Restricts visibility of self-ratings to a single supervisor and disables actions for others. We will open visibility to all supervisors in the same branch/department, authorize them to perform actions, implement ownership assignment upon action, and update the notifications and status labels.
2. **Employee List Pagination & Performance**: All employee records are initially rendered before pagination is applied, causing lag. Also, the page numbers in the pagination bar are not collapsed, causing clutter. We will hide rows initially by default, implement a sliding window pagination bar showing the first/last pages with ellipses, and apply this across all employee and user list pages.

## Proposed Changes 

---

### Component: Approval Flow & Supervisor Actions

#### [MODIFY] [functions.php](file:///c:/xampp/htdocs/Raquel_HRD_System/includes/functions.php)
- Update `notifySupervisorOfSelfRating` to find and notify all active supervisors who have user accounts, either:
  1. The direct supervisor (`reports_to`), or
  2. Active supervisors (`rank_category_id = 4` or `job_title LIKE '%Supervisor%'`) in the same branch and department.
- Update `isSupervisorOfEmployee` to authorize the supervisor if they are:
  1. The direct supervisor, or
  2. An active supervisor in the same branch and department as the employee.

#### [MODIFY] [confirm-rating.php](file:///c:/xampp/htdocs/Raquel_HRD_System/employee/confirm-rating.php)
- Update the pending evaluations query (lines 423+) so it loads evaluations where `emp.reports_to = ?` OR (`emp.branch_id = ? AND emp.department_id = ?`).
- Remove the loop-based PHP filter that restricts pending evaluations to a single direct/fallback supervisor, allowing all branch/department supervisors to see them.
- Since `isSupervisorOfEmployee` will be updated to allow same-branch/department supervisors, authorization to perform actions (view details, edit values, reject, and confirm & send) will automatically be opened.
- Ensure that if supervisor B submits/rejects an evaluation that has already been confirmed/returned by supervisor A, they are redirected with a view-only message.

#### [MODIFY] [team-list.php](file:///c:/xampp/htdocs/Raquel_HRD_System/employee/team-list.php)
- Remove the `$is_direct` restriction from the "Review" button check so any supervisor in the same branch/department can click it.
- Fix the Review button link to pass `?evaluation_id=...` instead of just routing to `confirm-rating.php`.
- Query the latest evaluation ID (`latest_evaluation_id`) in the member fetching SQL to populate the link.

#### [MODIFY] [pending-evaluation-row.php](file:///c:/xampp/htdocs/Raquel_HRD_System/supervisor/partials/pending-evaluation-row.php)
- Under "Submitted By" and "Type & Progress" columns, check if the evaluation was endorsed by a department manager (e.g. `!empty($row['dept_manager_endorsed_by_name'])`). If yes, display "Submitted By Branch Manager" instead of "Submitted By Supervisor" or "Supervisor confirmed".

---

### Component: Pagination Performance & UI Clutter

#### [MODIFY] [employees.php](file:///c:/xampp/htdocs/Raquel_HRD_System/manager/employees.php)
- Add `style="display: none;"` to desktop table rows (`<tr>`) and mobile card items (`<div class="student-item">`) to prevent all records from rendering at once on initial page load before JS executes.
- Update Javascript `updatePaginationUI` to render collapsed page links (e.g. `1 | ... | 48 | 49 | 50 | 51 | 52 | ... | 200`) using the sliding window page logic.

#### [MODIFY] [employees.php](file:///c:/xampp/htdocs/Raquel_HRD_System/supervisor/employees.php)
- Add `style="display: none;"` to desktop table rows and mobile card items.
- Update JS pagination to collapse pages with ellipses, similar to the manager view.
- Update JS `renderTable` to paginate mobile card items (hiding them if they are outside the current page window).

#### [MODIFY] [members.php](file:///c:/xampp/htdocs/Raquel_HRD_System/admin/members.php)
- Update PHP pagination rendering code to use the sliding window algorithm and render ellipses (`...`) for collapsed pages.

#### [MODIFY] [employee-accounts.php](file:///c:/xampp/htdocs/Raquel_HRD_System/admin/employee-accounts.php)
- Update PHP pagination rendering code to use the sliding window algorithm.

#### [MODIFY] [users.php](file:///c:/xampp/htdocs/Raquel_HRD_System/admin/users.php)
- Update PHP pagination rendering code to use the sliding window algorithm.

---

## Verification Plan

### Automated/Manual Tests
- Log in as an employee in the `Acquired Properties` department (e.g. `AP Staff I`) at `Raquel Pawnshop Main Office` and submit a self-rating.
- Log in as different supervisors in the same department (e.g. `AP Supervisor I`, `AP Supervisor II`, `AP Supervisor III`).
  - Verify that ALL of them receive the self-rating in their notification panel.
  - Verify that ALL of them see the self-rating in their "Confirm Self-Rating" pending list.
  - Verify that any of them can view, edit rating values, reject, or confirm the rating.
  - Verify that once one supervisor processes it, it becomes view-only/processed for the others.
- Navigate to the **Employee List** pages under Admin, HR Supervisor, and HR Manager.
  - Verify that the pages load immediately without lagging or showing all 2,000 employees initially.
  - Verify that pagination buttons are collapsed correctly using ellipses (e.g. `1 | ... | 4 | 5 | 6 | ... | 200`) instead of rendering all numbers.
