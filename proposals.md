# 🚀 Raquel HRD System — Improvement Proposals

Comprehensive proposals organized by **impact area** and **role affected**. Each item includes context from the existing codebase.

---

## 🔐 Security & System Reliability

### 1. Re-enable Brute-Force Login Protection
> **Affects: All roles | Priority: 🔴 Critical**

The login protection was **commented out** in [`index.php`](file:///c:/xampp/htdocs/Raquel_HRD_System/index.php#L60-L63). The `login_attempts` table already exists in the schema — it just needs to be re-wired.

- Re-enable `checkLoginBruteForce()` after testing
- Add a visible "Too many attempts" countdown timer on the login page
- Add IP-based lockout notification to Admin's audit trail

---

### 2. Two-Factor Authentication (2FA) via Email OTP
> **Affects: Admin, HR Manager, HR Supervisor | Priority: 🔴 High**

High-privilege accounts should require a one-time PIN sent to their registered email on login.

**Schema addition needed:**
```sql
ALTER TABLE users ADD COLUMN otp_code VARCHAR(10) NULL;
ALTER TABLE users ADD COLUMN otp_expires_at DATETIME NULL;
```

---

### 3. Session Timeout Warning
> **Affects: All roles | Priority: 🟡 Medium**

Add a JavaScript countdown modal that warns the user 2 minutes before their session expires, giving them the option to "Stay Logged In" or logout gracefully.

---

### 4. Password Strength Policy on First Login
> **Affects: All roles | Priority: 🟡 Medium**

The `first_login_completed` flag already exists in `users`. Enforce a minimum password policy (length, uppercase, special char) with a real-time strength meter during the forced first-login password change.

---

## 👨‍💼 Admin Role Improvements

### 5. System Health Monitor Dashboard Widget
> **Affects: Admin | Priority: 🟡 Medium**

Add a "System Health" section to the Admin Dashboard:
- PHP version, MySQL version
- Database size
- Number of rows in large tables (audit_logs, evaluations)
- Last backup timestamp (link to existing `backup.php`)

---

### 6. Bulk User Account Actions
> **Affects: Admin | Priority: 🟡 Medium**

In [`users.php`](file:///c:/xampp/htdocs/Raquel_HRD_System/admin/users.php), allow selecting multiple users to:
- Bulk activate / deactivate
- Bulk reset passwords
- Bulk assign to branch

---

### 7. Scheduled Automated Database Backup
> **Affects: Admin | Priority: 🟡 Medium**

The manual backup page ([`backup.php`](file:///c:/xampp/htdocs/Raquel_HRD_System/admin/backup.php)) already exists. Add a **system_settings** entry to configure a daily/weekly auto-backup that saves to a local folder, with the Admin receiving a notification when it runs.

---

## 🏢 HR Manager Improvements

### 8. Evaluation Deadline & Reminder Engine
> **Affects: HR Manager, HR Supervisor, HR Staff, Employee | Priority: 🔴 High**

The evaluation workflow tracks statuses but has **no deadline enforcement**. Propose:
- Add `due_date` column to `evaluations` table
- Manager can set the deadline when assigning
- A cron-like check (run on page load or via AJAX ping) sends notifications when a rating is overdue
- A dashboard widget shows "X evaluations overdue" in red

```sql
ALTER TABLE evaluations ADD COLUMN due_date DATE NULL AFTER evaluation_period_end;
```

---

### 9. Department-Level Performance Heatmap
> **Affects: HR Manager | Priority: 🟢 Nice-to-have**

In [`analytics.php`](file:///c:/xampp/htdocs/Raquel_HRD_System/manager/analytics.php), add a color-coded heatmap table:
- Rows = Departments
- Columns = Evaluation periods (Q1/Q2/Q3/Q4 or Annual)
- Color = Average score (red → yellow → green)

This gives the manager an instant view of which departments are improving or declining.

---

### 10. Succession Planning Module
> **Affects: HR Manager | Priority: 🟢 Nice-to-have**

For each key position, allow the manager to mark 1–3 employees as "succession candidates." This would appear in the employee's career view and guide promotion decisions during career movement reviews.

```sql
CREATE TABLE succession_plans (
    plan_id INT AUTO_INCREMENT PRIMARY KEY,
    job_title_id INT NOT NULL,
    candidate_employee_id INT NOT NULL,
    priority_rank TINYINT DEFAULT 1, -- 1 = primary, 2 = secondary, 3 = backup
    readiness ENUM('Ready Now', '1 Year', '2+ Years') DEFAULT '1 Year',
    notes TEXT NULL,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_title_id) REFERENCES job_titles(job_title_id),
    FOREIGN KEY (candidate_employee_id) REFERENCES employees(employee_id),
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL
);
```

---

## 👔 HR Supervisor Improvements

### 11. Quick Score Override Justification
> **Affects: HR Supervisor | Priority: 🟡 Medium**

When a supervisor overrides a score (`supervisor_override_score` already in schema), require a mandatory **reason text field**. Currently the override is logged but the reason isn't captured.

```sql
ALTER TABLE evaluation_scores ADD COLUMN supervisor_override_reason TEXT NULL;
```

---

### 12. Team Performance Trend Chart
> **Affects: HR Supervisor | Priority: 🟡 Medium**

In the supervisor's [`analytics.php`](file:///c:/xampp/htdocs/Raquel_HRD_System/supervisor/analytics.php), add a **line chart per employee** showing their score trend across all completed evaluations. This helps supervisors spot employees who are declining before the formal review cycle.

---

## 👩‍💼 HR Staff Improvements

### 13. Evaluation Assignment Wizard
> **Affects: HR Staff | Priority: 🟡 Medium**

Instead of manually assigning templates one-by-one, add an "Assign in Bulk" wizard in [`pending-approvals.php`](file:///c:/xampp/htdocs/Raquel_HRD_System/manager/pending-approvals.php):
1. Select template
2. Select target department or branch
3. Set period dates + optional due date
4. System auto-creates evaluation rows for all matching employees

---

### 14. PDS Completeness Tracker
> **Affects: HR Staff | Priority: 🟡 Medium**

Add a completeness percentage per employee in [`pds-submissions.php`](file:///c:/xampp/htdocs/Raquel_HRD_System/manager/pds-submissions.php). Calculate based on which PDS sub-tables have data vs. are empty, and flag employees with < 60% completeness.

---

## 🧑 Employee (Self-Service) Improvements

### 15. Personal Performance History Timeline
> **Affects: Employee | Priority: 🟡 Medium**

In the employee portal ([`dashboard.php`](file:///c:/xampp/htdocs/Raquel_HRD_System/employee/dashboard.php)), add a **visual timeline** of all completed evaluations showing:
- Period → Score → Performance Level → Comments from supervisor
- A simple line chart showing score trend over time

---

### 16. Career Path Visualization
> **Affects: Employee | Priority: 🟢 Nice-to-have**

Using the existing `job_titles.reports_to` hierarchy and `career_movements` table, display a visual org-chart-style career ladder showing:
- Current position
- Positions they've held (from career movements)
- Possible next positions based on the job title hierarchy

---

### 17. Notification Preferences
> **Affects: Employee, all HR roles | Priority: 🟢 Nice-to-have**

Let users choose which notification types they want to receive (evaluation assigned, score released, PDS reminder, etc.). Add a `notification_preferences` column (JSON or separate table) to `users`.

---

### 18. Downloadable Personal Data Sheet (PDF)
> **Affects: Employee | Priority: 🟡 Medium**

Allow employees to download their own filled-out PDS as a PDF from the employee portal. Currently only HR can print evaluations ([`print-evaluation.php`](file:///c:/xampp/htdocs/Raquel_HRD_System/manager/print-evaluation.php)). Use a PHP PDF library (mPDF or TCPDF) already available in XAMPP.

---

## 📊 Cross-Role / Universal Improvements

### 19. Global Search Bar
> **Affects: All HR roles | Priority: 🟡 Medium**

Add a global search in the top navigation (already in [`header.php`](file:///c:/xampp/htdocs/Raquel_HRD_System/includes/header.php)) that searches across:
- Employees by name / code
- Evaluations by employee name
- Audit logs by action type

Results open in a dropdown with quick-action links.

---

### 20. Dark Mode Toggle
> **Affects: All roles | Priority: 🟢 Nice-to-have**

Add a dark/light mode toggle stored in `localStorage`. The system appears to use a custom CSS theme — adding CSS variables for both modes is straightforward and greatly improves usability in low-light environments.

---

### 21. Notification Read-Receipt & Snooze
> **Affects: All roles | Priority: 🟢 Nice-to-have**

Upgrade the existing `notifications` table/pages to allow:
- "Snooze" a notification for 1 hour / tomorrow
- Mark all as read
- Notification categories (evaluation, career, system, PDS)

---

### 22. Mobile-Responsive Layout Audit
> **Affects: All roles | Priority: 🟡 Medium**

Several pages (especially `pending-approvals.php` at 122KB and `self-rating.php` at 128KB) are very heavy. Audit the most-used pages for mobile responsiveness and refactor long tables into collapsible card layouts on small screens.

---

### 23. Login Page "Remember Me" Security Hardening
> **Affects: All roles | Priority: 🟡 Medium**

The existing Remember Me in [`index.php`](file:///c:/xampp/htdocs/Raquel_HRD_System/index.php#L90-L100) stores the plain username in a cookie. Harden this by:
- Using a secure random token stored in a `remember_tokens` table
- Rotating the token on each use
- Associating the token with a user agent + IP fingerprint

---

## 🛠️ Technical / Developer Quality

### 24. CSRF Protection on All POST Forms
> **Affects: Security for all roles | Priority: 🔴 High**

None of the forms appear to use CSRF tokens. Add a global `csrf_token` generated per session and validated on every POST request via a helper in `functions.php`.

---

### 25. Centralized Error Logging
> **Affects: Admin, Developers | Priority: 🟡 Medium**

Add a `system_error_logs` table (or file-based log) that captures PHP errors and exceptions in production, viewable from the Admin panel, instead of relying on PHP's default error display.

---

## 📋 Summary Table

| # | Feature | Roles Affected | Priority |
|---|---------|---------------|----------|
| 1 | Re-enable brute-force protection | All | 🔴 Critical |
| 24 | CSRF protection | All | 🔴 High |
| 2 | 2FA via email OTP | Admin, Manager, Supervisor | 🔴 High |
| 8 | Evaluation deadline engine | All | 🔴 High |
| 3 | Session timeout warning | All | 🟡 Medium |
| 4 | Password strength on first login | All | 🟡 Medium |
| 6 | Bulk user actions | Admin | 🟡 Medium |
| 11 | Score override justification | Supervisor | 🟡 Medium |
| 13 | Bulk evaluation assignment | HR Staff | 🟡 Medium |
| 14 | PDS completeness tracker | HR Staff | 🟡 Medium |
| 15 | Employee performance timeline | Employee | 🟡 Medium |
| 18 | Downloadable PDS PDF | Employee | 🟡 Medium |
| 19 | Global search bar | All HR roles | 🟡 Medium |
| 22 | Mobile responsiveness | All | 🟡 Medium |
| 9 | Dept performance heatmap | Manager | 🟢 Nice |
| 10 | Succession planning module | Manager | 🟢 Nice |
| 12 | Team trend chart | Supervisor | 🟢 Nice |
| 16 | Career path visualization | Employee | 🟢 Nice |
| 17 | Notification preferences | All | 🟢 Nice |
| 20 | Dark mode toggle | All | 🟢 Nice |
| 21 | Notification snooze/categorize | All | 🟢 Nice |

