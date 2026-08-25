# Raquel HRIS - Database Import & Testing Playbook

## 1. Database Import Sequence

> **Note:** Drop and recreate the `raquel_hris` database prior to executing the scripts below in exact sequence.

1. `database/1st_schema_tables.sql`
2. `database/2nd_seed_organization.sql`
3. `database/3rd_seed_HR_accounts_.sql`
4. `sample_db_seeds/01_test_employees.sql` *(Must run before portal accounts)*
5. `database/xPortal_accounts.sql`
6. `database/data/seed_templates.sql`
7. `sample_db_seeds/02_test_hrd_portal_accounts.sql`
8. `sample_db_seeds/03_test_governance_approvers.sql`

---

## 2. Environment Setup & Default Credentials

- **Employee Portal Test Accounts Password:** `password` (for all test codes)
- **HR Manager:** `elena.delgado` / `password`
- **Department Sizing:** Each department contains 2–4 people.
- **Full Pilot Chain (Accounts Payable / AP):**
  `AP-T01` &rarr; `AP-T02` &rarr; `AP-T03` &rarr; `AP-T04` &rarr; `GOV-AUD` &rarr; `GOV-BOD`

---

## 3. Test Execution Sequence

| # | What to Prove | How / Procedure | Expected Result |
| :---: | :--- | :--- | :--- |
| **1** | **Governance Assigned** | HRIS &rarr; Evaluation Governance | Board = `GOV-BOD`<br>Audit = `GOV-AUD` |
| **2** | **Incomplete Team Blocks** | 1. Submit AP Annual as `AP-T01` only.<br>2. Login as `AP-T02` and check **Team Packages**. | Submission is blocked / Team Packages still waiting for remaining members. |
| **3** | **Happy Path + Lock** | 1. Submit AP Annual as `AP-T02`, `AP-T03`, and `AP-T04`.<br>2. Sequential approvals: `AP-T02` &rarr; `AP-T03` &rarr; `AP-T04` &rarr; `GOV-AUD` &rarr; `GOV-BOD`. | Package successfully approved, locked, and applied. |
| **4** | **Lock Holds** | Open the same package that was approved in Step 3. | No options available to **Adjust** or **Approve**. |
| **5** | **Return / Revision Flow** | 1. Use IT logins (`IT-T01` ... `IT-T03`).<br>2. At manager level, trigger **Return for revision**.<br>3. Re-submit and finish to Board (`GOV-BOD`). | Workflow routes back for revision successfully and completes through Board approval upon re-submission. |
| **6** | **HRD Department Flow** | 1. Self-rate as `HRD-003`, `HRD-002`, `HRD-001` via **Employee Portal** (not HRIS).<br>2. Log in as Patricia to consolidate.<br>3. Route through `Elena` &rarr; `GOV-AUD` &rarr; `GOV-BOD`. | HRD-specific evaluation workflow consolidates and completes executive approval sequence. |
| **7** | **Other Departments (Optional)** | Execute same pattern with that department's `*-T0*` logins and department-specific Annual template. | Standard routing rules apply consistently across all departments. |

---

> **Reference:** For detailed step-by-step logins and blockers, refer to `sample_db_seeds/TEST_PLAYBOOK.md`.
