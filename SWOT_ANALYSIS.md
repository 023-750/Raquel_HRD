# SWOT Analysis — Raquel Pawnshop HRIS

---

## Strengths

**1. Comprehensive HR Data Model**
The database covers the full Philippine Civil Service PDS (Personal Data Sheet) format — employee identity, government IDs (SSN/PhilHealth/Pag-IBIG/TIN), education, work history, and family background. This is directly aligned with local HR compliance requirements.

**2. Multi-Branch, Multi-Role Architecture**
The system supports multiple branches and five distinct roles (Admin, HR Manager, HR Supervisor, HR Staff, Employee), each with its own portal and gated access. This reflects the real organizational hierarchy of a pawnshop chain.

**3. Solid Security Foundations**
- Prepared statements throughout — no raw SQL injection vectors found
- CSRF protection using `hash_equals()` on all POST forms and AJAX endpoints
- bcrypt password hashing
- Brute-force lockout with IP + username tracking
- Output escaping via a global `e()` helper preventing XSS
- Audit logging on all significant actions

**4. Employee Self-Service Portal (ESS)**
Employees can update their own PDS, view their performance ratings, and submit career movement requests — reducing HR workload and giving employees direct access to their own data.

**5. Performance Evaluation Workflow**
Multi-step evaluation (self-rating → supervisor review → manager lock) with a formal confirm-and-lock mechanism. Completed ratings are preserved as a historical record.

**6. Built-in Audit Trail**
Every login, data change, and deletion is logged to `audit_logs` with user ID, IP address, action type, and entity. This is essential for accountability in an HR system.

**7. Backup Functionality**
Admin-facing backup page with download/delete capability, keeping operations recoverable without external tools.

---

## Weaknesses

**1. Database Credentials Hardcoded in Version-Controlled File**
`config/database.php` stores the DB password (`admin`) in plaintext and is committed to Git. Anyone with repo access has full database access.

**2. No MVC / Framework Structure**
Business logic, SQL queries, and HTML rendering are all mixed in the same PHP files. This makes the codebase harder to maintain, test, and scale as features grow.

**3. Backup Directory Inside Web Root**
The `backups/` folder is publicly accessible at `/FINAL_RAQUEL_PAWNSHOP_HRD/backups/`. SQL dump files containing all employee PII could be directly downloaded by anyone who guesses or finds the filename.

**4. GET-Based Backup Deletion Has No CSRF Protection**
`admin/backup.php?delete=filename` performs a destructive action via a plain GET request. Any malicious link or image tag on another page could trigger deletion.

**5. Session Timeout Not Enforced**
A session timeout is stored in settings, but `session-check.php` only validates `$_SESSION['user_id']` presence — it never checks elapsed time. Inactive sessions stay valid indefinitely.

**6. No Password Reset Flow**
The "Forgot Password" link exists on the login page but leads nowhere. Locked-out or forgotten-password users have no self-service recovery path — an admin must intervene manually.

**7. Utility Script Exposed in Web Root**
`misc/hash.php` (a live password-hashing utility) is accessible from the browser. This is a reconnaissance aid for attackers.

**8. No HTTPS Enforcement**
The system is built for XAMPP/localhost without any redirect logic forcing HTTPS. Deployed on a network without TLS, all session cookies and credentials travel in plaintext.

---

## Opportunities

**1. Payroll Integration**
The employee and government ID data is already structured for payroll computation (SSS, PhilHealth, Pag-IBIG contributions). A payroll module would be a natural, high-value extension.

**2. Leave Management Module**
There's no leave request or leave balance tracking. Adding this would consolidate the HR workflow completely within one system.

**3. Mobile-Responsive Enhancement**
CSS files like `hr-department-mobile.css` show awareness of mobile use. Fully optimizing the ESS portal for mobile would improve adoption since branch employees are unlikely to always have desktop access.

**4. Document Generation (COE, Payslip)**
The rich employee data model supports generating Certificates of Employment, contracts, and payslips as PDF — a common need in Philippine HR that would reduce manual document preparation.

**5. API Layer / SPA Migration**
The AJAX endpoints already exist (`employee/ajax/`). Extracting business logic into a proper REST API would enable a future Vue/React front-end or mobile app without a full rewrite.

**6. Role-Based Report Exports**
Chart.js dashboards are already present. Adding CSV/PDF export for HR analytics (headcount, turnover, evaluation scores by branch) would make the system valuable for management decisions.

---

## Threats

**1. Employee PII Exposure Risk**
The system stores highly sensitive Philippine government ID numbers (TIN, SSS, PhilHealth, Pag-IBIG), physical details, and family data. A single misconfiguration (e.g., accessible `backups/` directory) or SQL injection bypassed by a missed prepared statement would be a significant data breach with legal exposure under the Philippine Data Privacy Act (RA 10173).

**2. Single Server / No Redundancy**
Built on XAMPP, there's no indication of database replication, offsite backups, or failover. Hardware failure means total data loss unless backups are manually downloaded and stored elsewhere.

**3. Hardcoded Credentials = Supply Chain Risk**
If the Git repository is ever pushed to a public platform (GitHub, GitLab) or accessed by a departing developer, the database credentials are immediately compromised.

**4. Scalability Ceiling**
Vanilla PHP with no caching layer (Redis/Memcached), no job queue, and no connection pooling will struggle under concurrent load as the pawnshop chain grows or during peak evaluation periods.

**5. Dependency on XAMPP/Local Deployment**
No containerization (Docker) or deployment configuration means the system is tied to a specific machine setup. Migrating servers or onboarding a new developer is error-prone and undocumented beyond the SQL seed files.

**6. No Rate Limiting on Non-Login Endpoints**
Brute-force protection is implemented only for the login form. Other POST endpoints (PDS save, evaluation submit) have no rate limiting, leaving them open to automated abuse.

---

## Summary

The system is a well-featured, locally-compliant HR tool with genuinely good security groundwork at the application layer. The critical gaps are all at the infrastructure and configuration level — hardcoded credentials, exposed backups, no HTTPS enforcement, and no session expiry. Addressing those five items would significantly close the gap between a solid internal prototype and a production-ready deployment.
