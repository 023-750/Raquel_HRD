# Raquel Pawnshop HRIS — Codebase Overview

## Summary
A vanilla-PHP (XAMPP/LAMP) Human Resource Information System for **Raquel Pawnshop**, a Philippine multi-branch pawnshop chain. It manages the full employee lifecycle: Civil Service–compliant Personal Data Sheets (PDS), a multi-role RBAC portal system (Admin / HR Manager / HR Supervisor / HR Staff / Employee), a **360-degree performance evaluation workflow** (self-rating → immediate head confirmation → department manager endorsement → HR Supervisor consolidation → HR Manager approval), career movements/succession planning, notifications, audit logging, and scheduled database backups. The project targets the Philippine HR compliance domain (HRD Form-013.01 rating scale, PDS format, government IDs).

## Architecture

- **Pattern**: Table-driven server-rendered PHP pages (no framework, no MVC). Each portal (`admin/`, `manager/`, `supervisor/`, `staff/`, `employee/`) is a flat folder of PHP pages that directly mix SQL, business logic, HTML, inline CSS, and inline JS. Shared code lives in `includes/`. AJAX endpoints live in per-portal `ajax/` subfolders.
- **Major subsystems**:
  1. **Login & RBAC** — `index.php` (HRIS login) and `employee/index.php` (ESS login); `includes/session-check.php` validates session and `checkRole()` gates each page.
  2. **Employee 360 Evaluation Engine** — the largest and most complex subsystem, spanning `employee/self-rating.php`, `employee/confirm-rating.php`, `employee/dept-manager-review.php`, `supervisor/pending-endorsements.php`, `manager/pending-approvals.php`, plus `manager/ajax/*` and `supervisor/ajax/*`.
  3. **PDS / Employee Records** — `manager/add-employee.php`, `manager/edit-employee.php`, `manager/view-employee.php`, `staff/edit-employee.php`, `employee/pds-wizard.php` (legacy), plus ~25 employee sub-tables (education, gov IDs, family, SALN properties, etc.).
  4. **Career Movements & Succession** — `manager/career-movements.php`, `manager/succession-planning.php`, `employee/career-movement-request.php`; driven by `career_movements` table and `executeCareerMovementApplication()` in `includes/functions.php`.
  5. **Admin/System** — `admin/backup.php`, `admin/config.php`, `admin/audit-trail.php`, `includes/backup-engine.php`, `includes/auto-backup-check.php`.
  6. **Notifications** — `notifications` table, `createNotification()` in `includes/functions.php`, notification dropdown in `includes/header.php`, AJAX mark-read endpoints in `includes/ajax/`.
- **Tech stack**: PHP 8.x (uses `str_contains`, `str_starts_with`, arrow fns), MySQL 8 (`raquel_hris` database), Bootstrap 5.3 (CDN), Font Awesome 6 (CDN), Chart.js 4 (CDN), Tabler Icons (CDN), vanilla JS. No Composer, no npm build, no autoloader.
- **Execution start**: Browser hits `index.php` → session start → `config/database.php` (DB constants, mysqli, `BASE_URL` auto-derived from folder name) → `includes/functions.php` → login handling → role switch → redirect to the role dashboard. Every authenticated page begins with `require_once '../includes/session-check.php';` then `checkRole([...])` then `require_once '../includes/functions.php';` then `require_once '../includes/header.php';` and ends with `require_once '../includes/footer.php';`.

## Directory Structure

```
project-root/
├── index.php                  — HRIS login + role-based redirect
├── logout.php
├── config/database.php        — DB credentials, BASE_URL, mysqli connection
├── includes/
│   ├── functions.php          — THE shared library: CSRF, e(), getPerformanceLevel, calculateEvalTotal, supervisor-resolution functions, career-movement apply, auto-migrations
│   ├── session-check.php      — session guard + checkRole()
│   ├── header.php             — sidebar/navbar per role, notifications, avatars, flash messages, auto-backup trigger
│   ├── footer.php             — mobile bottom navs (employee + HR), shared modals, global CSRF fetch patch
│   ├── backup-engine.php      — mysqldump w/ PHP fallback backup generator
│   ├── auto-backup-check.php  — scheduled backup runner (triggered per Admin page load)
│   ├── employee-form-steps.php, profile-settings-shared.php
│   └── ajax/                  — notification & backup AJAX endpoints
├── admin/                     — Admin portal: users, employee accounts, members, audit trail, backup, config
├── manager/                   — HR Manager portal: employees, templates, pending approvals (evaluations + employee edit requests), careers, analytics, reports, PDS review
│   └── ajax/                  — save-rating, save-dev-plan, save-career-growth, report generators
├── supervisor/                — HR Supervisor portal: pending-endorsements, evaluation history, career progression, analytics, partials/pending-evaluation-row.php
│   └── ajax/                  — save-pending-rating, save-rating, save-dev-plan, save-career-growth
├── staff/                     — HR Staff portal: employee search/edit, evaluation history, templates (view-only), career movements
│   └── ajax/                  — search-employees-handler
├── employee/                  — Employee Self-Service portal (ESS): self-rating, confirm-rating, dept-manager-review, my-employment, my-performance, career-movement-request, profile settings
│   └── ajax/                  — save-pds-section, get-my-performance
├── assets/
│   ├── css/                   — style.css + employee-portal-*.css (10 separate files) + hr-department-mobile.css + login css
│   ├── js/                    — pjax.js, auto-save.js, form-validation.js, charts.js, main.js, etc.
│   └── img/logo|employees/
├── database/
│   ├── 1st_schema_tables.sql  — full schema: branches → employees → users → evaluations → career_movements → audit_logs etc.
│   ├── 2nd_seed_organization.sql
│   ├── 3rd_seed_HR_accounts_.sql — admin/HRD accounts & PDS seed
│   ├── {dept}_seed.sql        — per-department employees (AP, Audit, BD, Compliance, Finance, GS, HR, IT, Marketing, OP, Operations, Purchasing)
│   ├── xPortal_accounts.sql   — user accounts for portals
│   ├── zLAST_performance_indexes.sql
│   ├── testing_seed.sql       — evaluation-flow, career-movement, analytics test data (import LAST)
│   └── data/seed_templates.sql — 60 evaluation templates & criteria
├── backups/                   — generated .sql backups (PUBLICLY EXPOSED — see SWOT)
├── misc/                      — hash.php (live utility), sample_employees.csv, departments.txt
├── cloudflared.exe            — dev tunnel binary for exposing localhost
├── SWOT_ANALYSIS.md           — existing security/architecture review
└── found_bugs.txt
```

## Key Abstractions

### `config/database.php`
- **File**: `config/database.php`
- **Responsibility**: Global DB credentials, `DATE_DEFAULT_TIMEZONE('Asia/Manila')`, `BASE_URL` derived from the parent folder name (`/FINAL_RAQUEL_PAWNSHOP_HRD`), mysqli connection with `MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT` and `utf8mb4`.
- **Lifecycle**: Included at top of every entry point.
- **Note**: `DB_PASS = 'admin'` is hardcoded/committed; README instructs devs to change to `''`. The `$conn` variable is global.

### `includes/functions.php`
- **File**: `includes/functions.php` (~3000 lines)
- **Responsibility**: THE shared library. Security helpers (`e()`, `generateCsrfToken()`, `csrfField()`, `verifyCsrfToken()`), notification helpers (`createNotification`, `getUnreadNotificationCount`), audit helper (`logAudit`), evaluation math (`calculateEvalTotal`, `getPerformanceLevel`, `recalculateEvaluationScores`, `getOriginalSelfRatingScore`), org-graph resolution (`getEmployeeSupervisor`, `getDeptSupervisorOfEmployee`, `getDeptManagerOfEmployee`, `getDeptManagersOfEmployee`, `isSupervisorOfEmployee`, `isDeptManagerOfEmployee`, `hasSupervisorPrivileges`), and self-healing auto-migrations (`ensureEmployeeChangeRequests`, `ensureEvaluationWorkflowSchema`, `ensureEmployeesReportsTo`, `ensureCareerProgressionMovements`).
- **Key invariant**: `calculateEvalTotal($kra_subtotal, $behavior_average, $kra_weight=80, $behavior_weight=20)` = `kra_subtotal*kra% + behavior_avg*beh%`. KRA subtotal = Σ(weight/100 × rating); behavior average = plain mean. Performance bands: ≥3.60 Outstanding, ≥2.60 Exceeds, ≥2.00 Meets, else Needs Improvement.

### `users` table + role model
- **File**: `database/1st_schema_tables.sql`
- **Responsibility**: Login accounts. `role ENUM('Admin','HR Manager','HR Supervisor','HR Staff','Employee')`, each maps to a folder. `employee_id INT NULL` links to `employees` (null for the standalone Admin).
- **Quirk**: Two separate logins — `index.php` rejects role='Employee' accounts ("use the ESS portal"); `employee/index.php` only accepts role='Employee'. Remember-me uses separate cookies (`remember_username` vs `remember_employee_username`).

### `employees` table
- **File**: `database/1st_schema_tables.sql`
- **Responsibility**: Core record — identity, employment metadata (`job_title`, `job_title_id`, `department_id`, `rank_category_id`, `branch_id`, `employment_status`, `employment_type`), soft-delete (`deleted_at`), and `reports_to` (added at runtime by `ensureEmployeesReportsTo()`).
- **Rank categories**: 3=Manager, 4=Supervisor, 5=Rank & File (2= ? , 1=? — seeded in 2nd_seed). These drive the evaluation routing logic.

### `evaluations` table (core of the 360 workflow)
- **File**: `database/1st_schema_tables.sql` + `ensureEvaluationWorkflowSchema()` adds ~19 optional columns at runtime.
- **Status machine**: `Draft → Pending Self-Rating → Pending Dept Supervisor → Pending Dept Manager → Pending HR Consolidation → Pending Manager → Approved` with `Returned`/`Rejected` terminal-ish states (Returned reopens employee editing).
- **Key columns**: `submitted_by`, `assigned_by`, `dept_supervisor_confirmed_by/date`, `supervisor_confirmed_by`, `supervisor_altered_scores`, `dept_manager_endorsed_by/date`, `endorsed_by`, `approved_by`, `total_score`, `kra_subtotal`, `behavior_average`, `performance_level`. Note the schema does NOT include the `dept_supervisor_confirmed_*` or `Pending Dept Supervisor`/`Pending Dept Manager` statuses — they are injected at runtime by `ensureEvaluationWorkflowSchema()` (status ENUM gets MODIFYed).

### `evaluation_scores` table
- **File**: `database/1st_schema_tables.sql` + runtime ALTERs
- **Responsibility**: One row per criterion per evaluation. `score_value` = employee self-rating. Override columns encode the approval chain history WITHOUT destroying the original: `dept_manager_override_score/by/at` → `supervisor_override_score/by/at` → `manager_override_score/by/at`. `weighted_score` is recomputed per stage.
- **Deterministic precedence** (from `recalculateEvaluationScores()`): manager override > supervisor override > dept manager override > original `score_value`.

### Evaluation templates + criteria
- **File**: `evaluation_templates` / `evaluation_criteria` in schema; seeded in `database/data/seed_templates.sql` (60 templates)
- **Responsibility**: Templates define `kra_weight` (default 80) / `behavior_weight` (default 20), `target_department` filtering, `evaluation_type` (Initial/Final/Quarterly/Annual). Criteria have `section ENUM('KRA','Behavior')`, `weight`, `scoring_method` (default `Scale_1_4`).
- **Quirk**: Employees see a template in `self-rating.php` only if `target_department` matches their department OR is NULL/''/'All Departments', AND no existing non-Draft/Returned/Rejected evaluation exists for that template. One evaluation per template per employee.

## Data Flow

### Login flow
1. User submits `index.php` POST (CSRF verified) → `checkLoginBruteForce()` (5 attempts/username, 10/IP, 30s lockout) → username lookup via `BINARY username = ?`.
2. `password_verify()` on bcrypt hash → session vars set (`user_id`, `employee_id`, `role`, `branch_id`, etc.) → `clearLoginAttempts()` + `INSERT audit_logs` LOGIN + `createNotification()` to all Admins → role switch redirect to `{role}/dashboard.php`.
3. Employee accounts are rejected here and redirected through the ESS login (`employee/index.php`), which filters `role='Employee' AND employee_id IS NOT NULL`.

### Self-rating flow (the heart of the system)
1. `employee/self-rating.php` → employee picks an active template → KRA + Behavior 1–4 radio ratings (JS review modal, `auto-save.js` debounced AJAX draft saves).
2. POST (draft or submit) computes `kra_subtotal`, `behavior_average`, `total_score = calculateEvalTotal()`, `performance_level`, then writes/updates `evaluations` + `evaluation_scores`.
3. On **submit**, status routing logic in `self-rating.php` decides the next step:
   - HR Staff / HRD-office employees → `Pending Supervisor` (HRIS only)
   - HR Supervisor → `Pending Manager`
   - HR Manager → `Pending Supervisor`
   - Supervisor-level employee with dept manager → `Pending Dept Manager`; without → `Pending HR Consolidation`
   - Branch Manager (rank 3): if a genuine rank-4 supervisor exists → `Pending Dept Supervisor`, else `Pending HR Consolidation`
   - Rank & File with supervisor → `Pending Dept Supervisor`; with manager only → `Pending Dept Manager`; else → `Pending HR Consolidation`.
4. Notifications fan out via `notifySupervisorOfSelfRating()` / HR role-specific fan-out — branch-scoped first, then "all active HR Supervisors" fallback.

### Immediate-head confirmation flow
1. `employee/confirm-rating.php` (`hasSupervisorPrivileges()` gate) lists pending confirmations filtered by `reports_to` or same branch, then `isSupervisorOfEmployee()` per row.
2. Supervisor can override each score (1–4, 0.01 steps) → altered rows are flagged `score-changed`, totals recalc client-side AND server-side.
3. `confirm_and_send`: next status logic — HR Manager rates → `Approved` directly; otherwise if a separate dept manager exists → `Pending Dept Manager`, else → `Pending HR Consolidation`. Audit log records exact per-criterion diffs when altered.
4. **Reject** → status `Returned` + `supervisor_comments` required → employee re-edits/re-submits.

### Department Manager endorsement flow
1. `employee/dept-manager-review.php` gates on `isDeptManagerRole()` (reports_to chain OR rank 3). Lists `Pending Dept Manager` evals then filters by `isDeptManagerOfEmployee()`.
2. Score adjustments are stored in `dept_manager_override_*` columns (original preserved). `recalculateEvaluationScores()` recomputes with full override hierarchy. Endorse → `Pending HR Consolidation`; Return → `Returned` to employee.
3. Both confirm-rating and dept-manager-review pages run a **5-second polling lock** (`includes/ajax/check-evaluation-status.php`) that disables the form and reloads if another manager acts first (race-condition guard server-side too).

### HR Supervisor consolidation flow
1. `supervisor/pending-endorsements.php` shows `Pending Supervisor` + `Pending HR Consolidation` rows, grouped per employee, with filters (branch/dept/type/template/score/date/attention). Live-refresh every 60 s only when the supervisor is idle (not hovering/reading/modal-open).
2. Endorse → `Pending Manager` (or `Approved`, if the evaluee is an HR Manager — HR Supervisor acts as approver for HR Manager self-ratings); Return → `Returned`.

### HR Manager approval flow
1. `manager/pending-approvals.php` — two tabs: **Evaluation Approvals** and **Employee Edit Requests** (`employee_change_requests`, the HR Staff→Manager employee-record change diff pipeline).
2. Manager reviews a modal with the full audit trail, KRA/Behavior tables showing every override badge, career growth + dev plan in-place editors (via `manager/ajax/save-career-growth.php`, `save-dev-plan.php`, `save-rating.php`).
3. **Approve** (guard: `status='Pending Manager'` via affected_rows check) → `Approved`; **Reject** → `Rejected` (comments required); **Revision** → `Returned`. All scoped to `Pending Manager` by the UPDATE...WHERE clause.

### Score override hierarchy (manager AJAX)
`manager/ajax/save-rating.php` → writes `manager_override_score` + `manager_override_by` + `manager_override_at` → `recalculateEvaluationScores()` → transactions + audit log with per-criterion "Previous → Adjusted".

### Career movement application flow
`manager/career-movements.php` approve → `applyDueCareerProgressionMovements()` (in `functions.php`, called from header? actually from pages) → `executeCareerMovementApplication()` updates `employees.job_title/job_title_id/department_id/rank_category_id/branch_id` + `users.role/branch_id` (RBAC) via `resolveRoleFromJobTitle()` → ROLE_CHANGE audit + notification + marks `is_applied=1`.

### Auto-backup flow
`includes/header.php` (Admin only) → `auto-backup-check.php` → `ab_run_due_backup()` checks `system_settings` schedule vs `auto_backup_next_run` → `backup_create_database_snapshot()` tries `mysqldump.exe` (XAMPP path) then falls back to native PHP `SHOW CREATE TABLE` + `SELECT *` generator → cleanup keep-count → audit + toast.

## Non-Obvious Behaviors & Design Decisions

- **Runtime auto-migrations everywhere.** The schema import does NOT create all columns/statuses/tables the app needs. `ensureEvaluationWorkflowSchema()`, `ensureEmployeesReportsTo()`, `ensureCareerProgressionMovements()`, `ensureEmployeeChangeRequests()` run on page load and ALTER the live DB the first time a relevant page is hit. The `evaluations.status` ENUM is MODIFYed at runtime to inject `Pending Dept Supervisor` and `Pending Dept Manager`. This is how the codebase evolves schema without migration files — but it means fresh imports depend on these functions running correctly and can silently fail (returns false, swallowed).
- **Two status tracks for the same-looking statuses.** `Pending Dept Supervisor` vs `Pending Supervisor`, `Pending Dept Manager` vs `Pending Manager` look redundant. The distinction is: "Dept*" = branch-level org-chain step (handled in the Employee Portal by the rank-4 supervisor / rank-3 manager), bare "Pending Supervisor/Manager" = HRIS HRD-level step (handled on the HR Supervisor/Manager portal). Employee-portal `confirm-rating.php` and `dept-manager-review.php` operate only on the Dept* statuses; HR portals operate on the bare ones.
- **Sequential-step guard against direct-URL bypass.** In `confirm-rating.php`, a rank-3 Branch Manager is blocked from acting on `Pending Dept Supervisor` when a genuine rank-4 supervisor exists in the branch ("This evaluation is pending Branch Supervisor review"). The guard is applied both on page load and again on POST to defeat forged requests.
- **The supervisor-resolution functions are intentionally different.** `getEmployeeSupervisor()` (notification routing) falls back Supervisor→Manager; `getDeptSupervisorOfEmployee()` (status decisions) is rank-4 genuine-supervisor ONLY and returns null rather than falling back to a manager. Treating them interchangeably is a bug source.
- **Score edits never destroy the original self-rating.** The `*_override_*` column stack preserves audit history; `getEvaluationScoreCirclesHtml()` shows side-by-side Original vs Adjusted score bubbles whenever they differ.
- **Race-condition handling is explicit.** Both supervisor confirmation and manager review pages poll `check-evaluation-status.php` every 5s and hard-disable the UI if the status changed. Server-side, `UPDATE ... WHERE status='Pending X'` with `affected_rows===0` rejects double-actions.
- **CSRF is enforced on essentially every POST** via `verifyCsrfToken()` at the top of handlers, and `header.php` outputs a `<meta name="csrf-token">` that `footer.php`'s inline script patches into ALL `fetch()` and jQuery AJAX requests globally.
- **The DB password is hardcoded** (`config/database.php: define('DB_PASS','admin')`) and committed; the README tells new devs to manually edit it. Full PII exposure risk.
- **`backups/` is inside the web root** and publicly downloadable — flagged in SWOT.
- **Session timeout is configured but never enforced** — `session-check.php` only checks `isset($_SESSION['user_id'])`.
- **`misc/hash.php` is a live password-hashing utility reachable from the browser.**
- **The employee PDS wizard was effectively deprecated** — `employee/my-pds.php` simply redirects to the dashboard with "Personal Data Sheet is no longer part of the Employee Portal." The PDS data model (~25 sub-tables) and `manager/pds-submissions.php`, `manager/review-pds.php`, `employee/pds-wizard.php` remain, but the employee-facing PDS flow is disabled.
- **The HR validation queue on the employee dashboard is hard-disabled** (`$show_validation_queue = false; // Disabled on employee portal dashboard as HRD evaluations are handled on HRIS admin portal`) yet a full live-refresh implementation and 10-second fetch refresh remains in the same file.
- **PJAX + live queue auto-refresh**: `assets/js/pjax.js` and the dashboard/queue pages (employee dashboard validation queue, supervisor pending-endorsements, manager pending-approvals) self-refresh their own HTML fragments via `fetch(window.location.href)` + DOMParser + replaceWith, with activity-detection guards (supervisor refresh only when idle; manager refresh only when no modal open).
- **The employee bottom mobile nav recomputes badge counts in `footer.php`** independently of `header.php` — duplicated counting logic that can drift.
- **Zebra-stripping CSS overrides appear repeatedly**: a global zebra-stripe JS/CSS affects tables, and dark "final grade card" CSS blocks in `confirm-rating.php`/`dept-manager-review.php` contain extensive `!important` overrides to neutralize it.
- **Tagalog/Filippino comment mix**: code comments interleave English and Tagalog (e.g., "Nasa plugin/functions.php yong time duration for bruteforce", "Palagay ang Loob Ko!" = brand tagline).

## Module Reference

| File | Purpose |
|------|---------|
| `index.php` | HRIS login, session bootstrap, role-based redirect; brute-force + audit + admin notification on login |
| `employee/index.php` | ESS (Employee Self-Service) login — only role='Employee' accounts |
| `config/database.php` | DB credentials, BASE_URL, timezone, mysqli connection |
| `includes/session-check.php` | Session guard + `checkRole()` RBAC gate |
| `includes/functions.php` | All shared logic: CSRF, e(), eval math, org-graph resolvers, auto-migrators, career movement executor |
| `includes/header.php` | Per-role sidebar, navbar, notification bell, avatar resolution, auto-backup check, flash rendering |
| `includes/footer.php` | Mobile bottom navs (employee + HR), image modal, global CSRF fetch/jQuery patch, shared JS |
| `includes/backup-engine.php` | `backup_create_database_snapshot()` — mysqldump path with PHP-native fallback, cleanup |
| `includes/auto-backup-check.php` | Scheduled backup runner on Admin page loads; schedule stored in system_settings |
| `employee/self-rating.php` | The entire self-rating create/edit/submit engine (template selection, KRA/Behavior scoring, status routing, notification fan-out) |
| `employee/confirm-rating.php` | Immediate-head confirmation/rejection with score overrides + sequential-step guard + race lock |
| `employee/dept-manager-review.php` | Department-manager endorsement stage (override_* columns, recalc, return-to-employee) |
| `employee/dashboard.php` | Employee home: eval status + dynamic workflow progress bar, career timeline, employment snapshot |
| `supervisor/pending-endorsements.php` | HR Supervisor consolidation queue (grouped per employee, filters, idle-time refresh, modals with full override audit) |
| `manager/pending-approvals.php` | HR Manager queue — evaluation approvals + employee-edit-request approvals with in-modal rating/dev-plan/career editors |
| `manager/ajax/save-rating.php` | Manager score override endpoint → recalc → notify → audit (transactional) |
| `manager/ajax/save-dev-plan.php` | Dev-plan editor endpoint (delete-all + reinsert, Pending Manager only) |
| `includes/ajax/check-evaluation-status.php` | 5s race-poll endpoint used by confirmation/review pages |
| `employee/career-movement-request.php` | Employee-initiated career movement request |
| `manager/career-movements.php` | HR Manager career movement management + application |
| `manager/succession-planning.php` | Succession candidates (uses `manager/ajax/get-succession-candidates.php`) |
| `manager/templates.php`, `manager/create-template.php`, `manager/edit-template.php` | Evaluation template CRUD |
| `manager/analytics.php`, `manager/reports.php` | Chart.js dashboards + PDF/CSV export (`manager/ajax/generate-report.php`, `manager/export-report.php`) |
| `admin/backup.php` | Manual backup dashboard (full/schema/data), download/delete |
| `admin/config.php` | System settings incl. auto-backup schedule |
| `admin/audit-trail.php`, `manager/audit-trail.php`, `supervisor/audit-trail.php` | Audit log viewers per role |
| `database/1st_schema_tables.sql` | Canonical schema (dropped + recreated: `DROP DATABASE IF EXISTS raquel_hris`) |
| `database/3rd_seed_HR_accounts_.sql` | Admin + HRD team accounts + full PDS seed data |
| `database/data/seed_templates.sql` | 60 evaluation templates + criteria seed |
| `database/testing_seed.sql` | Evaluation-flow, career-movement, analytics test data — import LAST |
| `misc/hash.php` | Live bcrypt password-hash generator (security risk if exposed) |
| `SWOT_ANALYSIS.md` | Existing security/architecture review — read this before touching infra |

## Suggested Reading Order

1. `SWOT_ANALYSIS.md` — the 10-minute orientation: what the system does, its known weaknesses, and its threat model. Written by the same team; highly accurate.
2. `config/database.php` + `index.php` — the entry point, database bootstrap, login flow, role redirect. This is where a new dev should start tracing execution.
3. `database/1st_schema_tables.sql` — the canonical schema: 40+ tables. Pay special attention to `employees`, `users`, `evaluations`, `evaluation_scores`, `evaluation_templates/criteria`, `career_movements`, `audit_logs`. Remember: several columns/statuses are ADDED at runtime by functions.php.
4. `includes/functions.php` — the entire shared logic. Focus on `calculateEvalTotal()` (+ the docblock examples around line 755), `getPerformanceLevel()`, the supervisor-resolution family (`getEmployeeSupervisor`, `getDeptSupervisorOfEmployee`, `getDeptManagerOfEmployee`, `isSupervisorOfEmployee`, `isDeptManagerOfEmployee`, `hasSupervisorPrivileges`), `ensureEvaluationWorkflowSchema()`, and `executeCareerMovementApplication()`.
5. `employee/self-rating.php` — the largest single page and the entry into the 360 workflow; this teaches the status routing decision tree that every downstream page follows.
6. `supervisor/pending-endorsements.php` then `manager/pending-approvals.php` — the two HR-side review/approval hubs that consume the workflow, including the override stack and the AJAX save endpoints.
7. `includes/header.php` + `includes/footer.php` — the layout/plumbing layer: session flashes, notification isolation per context (employee vs hr), mobile navs, auto-backup trigger, and the global CSRF fetch patch.

## Known Issues / Gotchas (from code + SWOT)

- `config/database.php` has committed plaintext DB password (`admin`); README says change to empty string on setup.
- `backups/` publicly accessible; GET-based delete in `admin/backup.php?delete=X` has no CSRF protection (SWOT).
- Session timeout setting never enforced.
- `misc/hash.php` exposed.
- No rate limiting on non-login POST endpoints.
- Employee PDS self-service disabled (`my-pds.php` redirects); PDS wizard code remains but unused.
- HR validation queue on employee dashboard hard-disabled but fully built.
- Duplicated badge-count logic between `header.php` and `footer.php` for mobile navs can drift.
- `str_replace(['All Departments', 'Template'], ['All Depts', 'Temp'], ...)` in template dropdown label has a partial-string replacement quirk (would replace "Template" inside e.g. "Templates 2026").
- The schema's original `evaluations.status` ENUM lacks the Dept* statuses — they appear only after `ensureEvaluationWorkflowSchema()` runs; importing only the SQL files yields a schema that some queries (which reference `Pending Dept Supervisor`) will fail against until those functions execute.
