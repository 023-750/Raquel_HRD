Employee Portal – Evaluation Flow Issues and Required Changes
1. Evaluation Approval Flow Based on Employee Rank

The evaluation routing should be determined by the employee's position/rank.

Example: Supervisor-Level Employees

If an employee holds a Supervisor position, their self-rating evaluation must not be routed to another Supervisor for approval. Instead, it should be routed directly to their immediate higher-ranking manager.

Expected Flow:

Supervisor (Employee Self-Rating) → Branch/Department Manager → HR Supervisor → HR Manager (Final Approval)

2. Current Testing Scenario

Employee:

Sarah Miller
Marketing Supervisor I
Marketing Department
Raquel Pawnshop Main Office

Issue:
When Sarah Miller submits her self-rating evaluation, the system routes the form to a Branch Supervisor for approval.

Expected Behavior:
Since Sarah Miller is already a Supervisor, the evaluation should be routed directly to:

Marcus Reyes

Marketing Manager I
Marketing Department
Raquel Pawnshop Main Office

The approval flow should follow:

Sarah Miller (Self-Rating) → Marcus Reyes (Marketing Manager I) → HR Supervisor → HR Manager (Final Approval)

3. Human Resources Department Exception

Employees who belong exclusively to the Human Resources Department at the Main Branch/Main Office should be excluded from the standard branch approval hierarchy.

Their evaluation workflow should follow a separate HR-specific approval process as defined by management.

4. Evaluation Status Monitoring Issue

The Employee Portal → Evaluation Status page does not display evaluations that are currently in the Self-Rating stage.

This is a critical issue because employees and administrators cannot monitor evaluations that have been started but not yet submitted for approval.

Expected Behavior:
The Evaluation Status page should display all evaluation records, including those currently in:

Self-Rating
Pending Manager Approval
Pending HR Supervisor Approval
Pending HR Manager Approval
Approved/Completed

This will provide complete visibility and tracking of the evaluation process.

Summary of Required Fixes
Route evaluations based on employee rank/position hierarchy.
Supervisor-level employees should submit directly to their respective Manager, not another Supervisor.
Sarah Miller's evaluation should be routed to Marcus Reyes (Marketing Manager I).
Exclude Main Office Human Resources employees from the standard branch approval workflow.
Display Self-Rating evaluations in the Evaluation Status page for proper monitoring and tracking.