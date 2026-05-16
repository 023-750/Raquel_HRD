# HRIS System Test Plan

This document outlines the testing tasks to verify the core functionalities of the Raquel Pawnshop HRIS. Please follow these steps sequentially to ensure the system is working as expected.

> [!NOTE]
> Career Movement functionality is excluded from this plan as it is still under development.

---

## 1. System Administration (Admin Role)
**Goal:** Verify user management and system integrity.

- [ ] **User Management:** Create a new HR Staff user and ensure they can log in.
- [ ] **Audit Trail:** Perform an action (e.g., update a setting) and verify it appears in the Audit Trail.
- [ ] **System Config:** Update the "Company Name" and verify the change reflects in the sidebar and header.
- [ ] **System Backup:** Trigger a backup and check if a file is generated in the `backups/` directory.

---

## 2. Organization Setup (HR Manager Role)
**Goal:** Ensure the foundation of the organization is correctly configured.

- [ ] **Branches:** Add a new branch and verify it shows up in the list.
- [ ] **Departments:** Assign the new branch to a new department.
- [ ] **Positions:** Create a new job title (e.g., "Junior Clerk") and assign it to a department.
- [ ] **Operation Management:** Verify that departments and branches are correctly linked.

---

## 3. Employee Management (HR Manager/Supervisor)
**Goal:** Verify employee record handling and portal access.

- [ ] **Add Employee:** Manually add a new employee with all required details.
- [ ] **Employee List:** Search for the newly added employee using the search bar and filters.
- [ ] **Portal Accounts:** Create a portal account for the new employee and verify the "Active" status.
- [ ] **Employee Deactivation:** Deactivate an employee and provide a reason (e.g., "Resigned"). Verify they can no longer log in.

---

## 4. Performance Rating System (End-to-End Workflow)
**Goal:** Test the complete 7-step evaluation process.

- [ ] **Template Creation (HR Manager):** Create a new evaluation template with specific KRAs and KPIs.
- [ ] **Assign Evaluation (HR Supervisor):** Assign the template to an employee.
- [ ] **Self-Rating (Employee):** Log in as the employee and complete the self-evaluation.
- [ ] **Validation (HR Supervisor):** Log in as the supervisor, review the employee's self-rating, and provide the supervisor's rating.
- [ ] **Final Approval (HR Manager):** Review the completed evaluation and move it to "Finalized" status.
- [ ] **Evaluation History:** Verify the finalized evaluation appears in the history for both the employee and HR.

---

## 5. Employee Self-Service (Employee Role)
**Goal:** Verify personal information access and account security.

- [ ] **My Employment:** Check if the Rank, Job Title, and Department are correctly displayed.
- [ ] **Profile Settings:** Change the account password and verify the new password works.
- [ ] **Notifications:** Trigger a notification (e.g., by assigning an evaluation) and verify it appears in the employee's bell icon.

---

## 6. Dashboard & Analytics
**Goal:** Verify data visualization and reporting.

- [ ] **HR Dashboard:** Check if the employee count, department breakdown, and pending evaluations are accurate.
- [ ] **Reports:** Generate a PDF report (if implemented) for the employee list or evaluation results.
