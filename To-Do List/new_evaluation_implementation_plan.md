# New Organization-Driven Evaluation Flow — Draft Implementation Plan

Status: Draft for review; no implementation has started.

## 1. Purpose

Replace the current hard-coded HR and branch evaluation routes with an organization-driven process. The route must follow the client-approved reporting hierarchy, support team-level consolidation, and complete automatically after Board approval.

## 2. Confirmed Direction

- The client’s approved organizational chart is the source of truth.
- Departments and positions must be manageable by an HR Manager in the system, not only through SQL seed files.
- Individual KRA scores remain specific to each employee.
- Core Behaviors & Values is consolidated into one shared team score for the evaluation package.
- A supervisor cannot submit a team package until every required member has submitted the assigned self-rating.
- Higher reviewers can review, revise, return, or submit the package through the approved reporting route.
- Board of Directors approval automatically locks, applies, records, and notifies; no manual HR release is required.
- Annual, Quarterly, Initial, and Final determine evaluation eligibility and scheduling; they do not change the approval route.

## 3. Current System Baseline

The current system already includes:

- Department, job-title, and employee records.
- A job-title `reports_to` relationship.
- An employee-level `reports_to` relationship for direct supervisors.
- Individual evaluations, self-ratings, score overrides, notifications, and audit logging.
- Department and Position Management screens for HR Managers.

The current system does not yet include:

- Cross-department position reporting in the Position Management UI.
- Team evaluation packages and package membership.
- A shared final Behavior score per team package.
- A complete-team submission gate.
- Dynamic multi-level routes through VPs, President, and Board.
- Governance approval entities for Board of Directors and Audit Committee.

## 4. Organizational Structure Work

### 4.1 Client confirmation required

Obtain a final signed-off list of:

1. Departments and divisions.
2. Official positions under each department.
3. Parent position for every position.
4. Direct supervisor for each active employee.
5. Governance approvers: Board of Directors and Audit Committee.
6. Which existing positions should be renamed, mapped, retired, or newly added.

### 4.2 Current chart-specific gaps to resolve

- Facilities Development Supervisor.
- Security Systems Supervisor.
- Logistics & Warehouse Supervisor.
- Area Supervisor (current system uses Area Coordinator and Focal Person).
- Strategy Management Supervisor.
- Clarification of Executive Assistant / Strategy Management Staff.
- Mapping of General Manager to existing GS Manager titles.
- Mapping of Procurement Supervisor to existing Purchasing Supervisor titles.

### 4.3 Organization administration enhancements

Enhance Department and Position Management so HR Managers can:

- Add, edit, activate, and deactivate departments and positions.
- Assign a position to a parent position in any department.
- Mark a position as a department/division head where appropriate.
- View the complete reporting path before saving.
- See employee assignment counts before renaming or deactivating a position.

Required safeguards:

- Reject self-reporting and circular position chains.
- Do not delete positions that have employee assignments or evaluation history; deactivate them instead.
- Preserve audit entries for all hierarchy changes.
- Require an explicit confirmation when changing a parent position with active downstream employees.

## 5. Target Evaluation Model

### 5.1 Two hierarchy layers

```text
Position hierarchy
Job Title -> Parent Job Title

Employee hierarchy
Employee -> Direct Supervisor Employee
```

The position hierarchy defines the organization. The employee hierarchy identifies the actual reviewer for a particular employee. Both must be configured before an evaluation cycle is opened.

### 5.2 Evaluation package

Create a team/department package for each department, evaluation period, and template.

```text
Evaluation cycle
└─ Team package
   ├─ Member evaluations (individual KRA and Behavior input)
   ├─ Shared Core Behaviors & Values result
   ├─ Frozen approval-route snapshot
   ├─ Review actions and comments
   └─ Immutable audit history
```

The route must be snapshotted at package creation. A later change to a job title, department, or employee supervisor must not change an in-progress or completed evaluation.

### 5.3 Proposed package states

```text
Draft
Pending Self-Ratings
Pending Supervisor Consolidation
Pending Manager Review
Pending Executive Review
Pending President Review
Pending Board Approval
Approved and Applied
Returned for Revision
Cancelled
```

The interface should display a human-readable waiting message, such as `Waiting for 3 of 12 AP team members to submit` or `Pending VP Acquired Properties review`.

## 6. Example Routes

### 6.1 Human Resources

```text
HR Team
-> HR Supervisor consolidation
-> HR Manager review
-> President review
-> Board of Directors approval
-> System automatically applies final results
```

### 6.2 Acquired Properties

```text
AP Team
-> AP Supervisor consolidation
-> AP Manager review
-> VP Acquired Properties review
-> President review
-> Board of Directors approval
-> System automatically applies final results
```

The same route-generation rule will be applied to all approved departments after the HRD and AP pilot.

## 7. Score and Consolidation Rules

1. Each employee completes their individual KRA ratings and Behavior input.
2. The team’s standing supervisor sees all required member submission statuses.
3. The supervisor cannot submit the package until all required self-ratings are complete.
4. The supervisor may revise individual KRA ratings and set/consolidate the team Behavior result.
5. The manager and every later reviewer may revise allowed scores, add comments, return the package, or submit it to the next step.
6. On final approval, every package member receives the final shared Behavior score; each retains their own final KRA score.
7. Every revision retains the previous score, reviewer, timestamp, and reason in the audit history.

## 8. UI/UX Specification

Use the project’s existing Bootstrap visual language and accessible form/table components. Avoid adding a second UI framework.

### 8.1 Organization Management

**Department Management**

- Department list with active/inactive state and position count.
- Add/edit form with department name and description.
- Deactivation confirmation showing affected positions and employees.

**Position Management**

- Searchable table: Position, Department, Rank, Reports To, Assigned Employees, Status.
- Add/edit modal or page: position name, department, rank, parent position, head flag, active state.
- Parent-position selector grouped by department and allowed to show all departments.
- Inline validation for empty fields, self-reporting, and circular reporting.
- An organization-path preview, for example: `IT Manager -> VP Operations -> President -> Board of Directors`.

### 8.2 Employee Self-Rating

- Clear period/template card and a progress timeline.
- Separate KRA and Behavior sections with save-draft support.
- Explicit status after submission: `Waiting for AP Supervisor consolidation`.
- Read-only display of the final shared Behavior score after it is applied.
- Accessible labels, visible error messages near the relevant field, keyboard-operable controls, and no icon-only controls without labels.

### 8.3 Supervisor Consolidation Workspace

- Header: team/department, template, period, reviewer, current package status.
- Completion indicator: submitted vs required members, with a visible blocked-submit reason.
- Member table: employee, job title, self-rating status, individual KRA, adjustment indicator, and review action.
- Shared Behavior panel: team score, reviewer comment, calculated impact, and change history.
- Submit/return controls only become available when validation passes.

### 8.4 Manager, VP, President, and Board Review Workspace

- Same package review layout with role-appropriate labels and no duplicated page patterns.
- Readable approval timeline showing previous actions, comments, and score changes.
- Side-by-side original versus current values for every changed score.
- Return action requires a reason and routes the package to the correct prior stage.
- Final Board approval uses a strong confirmation dialog explaining that it locks and applies results.

### 8.5 Employee Results and History

- Final score summary: KRA, shared Behavior score, total score, performance level, final date.
- Route timeline and read-only comments.
- Historical packages filtered by period and evaluation type.

## 9. Technical Implementation Sequence

1. Document and import the approved organization structure.
2. Enhance Department and Position Management for cross-department reporting and hierarchy validation.
3. Add a safe employee hierarchy assignment/validation workflow.
4. Introduce package, package member, route-step, and consolidated-score records.
5. Generate a frozen route when an evaluation package is created.
6. Update self-rating assignment and submission to join the appropriate package.
7. Build the Supervisor Consolidation Workspace.
8. Build the generic higher-level package review workspace for Manager, VP, President, and Board.
9. Implement Board approval automation, audit records, notifications, and result locking.
10. Implement results/history views and reporting updates.
11. Pilot with HRD and Acquired Properties.
12. Fix pilot issues, migrate remaining departments, and enable rollout controls.

## 10. Security, Reliability, and Operations

- Enforce server-side authorization at every route step; never trust only UI visibility.
- Reviewers may access only packages assigned to their frozen route step.
- Preserve audit history for every action, score change, hierarchy change, and automated application.
- Use database transactions for package submission, return, approval, and final application.
- Prevent duplicate self-ratings, duplicate package members, repeated approval actions, and concurrent score overwrites.
- Provide safe fallback/escalation handling for inactive, vacant, or removed reviewers; this requires a client-approved delegation policy.
- Keep personal performance data available only to authorized employees and reviewers.

## 11. Test and Acceptance Scenarios

- HR Staff and AP Staff complete self-ratings.
- A missing team member blocks supervisor consolidation.
- A supervisor adjusts one member’s KRA and sets the shared Behavior score.
- A manager returns the package with remarks.
- An executive reviewer is reassigned after package creation; the frozen route remains valid.
- A Board approval automatically applies scores once and locks the package.
- A user outside the route cannot view or act on the package.
- A position change cannot create a circular hierarchy.
- An employee with no configured direct supervisor is blocked or escalated according to policy.
- Annual, Quarterly, Initial, and Final assignments follow the confirmed eligibility rules.

## 12. Open Client Decisions

1. Confirm the official organization chart, including all missing/mapped positions.
2. Confirm whether Audit and Compliance follow Board-only routes as shown.
3. Confirm who can act for an unavailable reviewer and for how long.
4. Confirm whether the team Behavior score includes supervisors/managers or only their direct team members.
5. Confirm whether peer/subordinate 360 ratings are in scope; this plan currently covers self-rating plus hierarchical consolidation.
6. Confirm the evaluation-date reference for Annual, Quarterly, Initial, and Final types.
7. Confirm the transition rule from Probationary to Regular status.

## 13. Decision Log

| Decision | Rationale |
| --- | --- |
| Use the client chart as the target hierarchy | The current hard-coded routes do not match the required organization. |
| Make hierarchy configuration HR-managed | The client must be able to add and manage future positions without seed-file edits. |
| Keep KRA individual | KRA measures each employee’s individual work. |
| Consolidate Behavior at team-package level | This is the stated client requirement for Core Behaviors & Values. |
| Freeze approval routes at package creation | Protects in-progress evaluations from later organization changes. |
| Automate release after Board approval | Avoids manual HR release, including conflict for HRD evaluations. |
| Pilot with HRD and AP | These departments make the new hierarchy and consolidation rules easy to validate first. |

