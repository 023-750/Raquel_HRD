# SWOT — Raquel Pawnshop HRIS

PHP/MySQL HRD system for multi-branch Philippine pawnshop operations: employee records, PDS, performance evaluations, career movements, and HR analytics.

Payroll and attendance are **intentionally out of scope** — the client already runs those on separate systems.

| Stack | Details |
| --- | --- |
| Runtime | LAMP / XAMPP |
| Frontend | Bootstrap 5 |
| Database | MySQL · `raquel_hris` |
| Access | 5 role portals |
| Relationship | Complements payroll & attendance |

## Strategic snapshot

Strongest as a focused performance, PDS, and career HRIS that sits beside the client’s existing payroll and attendance systems. Biggest risks are architecture/migration debt and employee-data drift across those separate systems — not missing pay or time modules.

---

## Strengths (internal — what already works)

1. **Clear complementary scope**  
   Intentionally excludes payroll and attendance — those stay on the client’s existing systems — so this HRIS focuses on records, PDS, performance, and career.

2. **Domain-fit HRIS**  
   Built for Raquel Pawnshop: multi-branch org, PH employment data (SSS, PhilHealth, Pag-IBIG, TIN), and HRD-style evaluations.

3. **Role-based portals**  
   Separate Admin, HR Manager, Supervisor, Staff, and Employee flows with `checkRole()` and distinct UX for self-service vs HR work.

4. **Deep evaluation engine**  
   Configurable KRA/Behavior templates, multi-level approval, team packages, score overrides, development plans, and print/export.

5. **Solid security basics**  
   Prepared statements, CSRF on POSTs, bcrypt passwords, login lockouts, session revalidation, and audit logging with IP/UA.

6. **Career & succession**  
   Promotion/transfer/demotion workflows plus succession candidates drawn from historical performance scores.

## Weaknesses (internal — what holds it back)

1. **No link to payroll / attendance**  
   Employee master lives here while pay and time live elsewhere — without sync or shared IDs, HR may re-enter or reconcile data by hand.

2. **Monolithic architecture**  
   Heavy logic in a large `functions.php`; duplicated UI across role folders; schema changes applied at runtime instead of versioned migrations.

3. **Schema & docs drift**  
   Base SQL lags newer package/career tables; README is informal setup notes; referenced seed files are missing.

4. **Production readiness gaps**  
   Default XAMPP root/blank password, hardcoded Windows mysqldump path, CDN dependencies, shared seed credentials.

5. **Limited quality gates**  
   No Composer dependency lock, automated tests, or CI visible; evaluation governance still evolving.

## Opportunities (external — where to grow)

1. **Finish org-driven evals**  
   Complete Board/Audit governance, cross-dept reporting UI, and lock the hierarchy-as-source-of-truth model already client-approved.

2. **Production harden & host**  
   Move off local XAMPP defaults: env-based secrets, proper migrations, HTTPS hosting, and backup strategy for live branches.

3. **Light integration with existing systems**  
   Export/import or employee-ID sync with the client’s payroll and attendance apps — keep boundaries, reduce duplicate entry.

4. **Mobile field adoption**  
   WebView APK path and mobile CSS already exist — package for branch supervisors doing evaluations on site.

5. **Analytics value**  
   Dashboards and succession data can drive talent decisions if reporting is polished and trusted by management.

## Threats (external — what could derail it)

1. **Data silos across HR stack**  
   If employee status, branch, or position changes here but not in payroll/attendance (or the reverse), reports and pay eligibility can disagree.

2. **Runtime schema risk**  
   CREATE/ALTER on request can fail mid-flight, diverge environments, and complicate backups/restores across branches.

3. **Security exposure at scale**  
   Default credentials, residual raw queries, and noisy login alerts become liabilities if the app is internet-facing.

4. **Maintainer bottleneck**  
   Procedural monolith without tests raises the cost of change as evaluation and career flows keep evolving.

5. **Scope creep pressure**  
   Future requests to absorb leave, recruitment, or even payroll/attendance could dilute focus unless boundaries stay explicit with the client.

---

## Priority moves

Ordered by leverage: protect strengths, close blockers, then expand scope.

| Priority | Action | SWOT link | Why it matters |
| --- | --- | --- | --- |
| 1 | Stabilize schema with versioned migrations | W → T | Stops environment drift and backup/restore failures |
| 2 | Production harden (secrets, HTTPS, dump paths) | W → T | Required before any public or multi-branch hosting |
| 3 | Complete org-driven evaluation governance | S → O | Locks in the system’s core differentiator for leadership |
| 4 | Ship mobile WebView for field evaluators | O | Extends reach to branch supervisors without a rewrite |
| 5 | Define employee sync with payroll/attendance | W → O → T | Keeps separate systems aligned without merging products |

---

## Scope boundary

**In scope:** org structure, employee master + PDS, performance cycles, career movements, succession, audit, backups, analytics.

**Explicitly out of scope** (client already has other systems): payroll and attendance/timekeeping.

Other HR areas (e.g. leave, recruitment) remain optional future decisions — not assumed gaps.

---

*Source: codebase review of Raquel_HRD (PHP portals, database schema, evaluation plans, security helpers). Point-in-time assessment.*
