# Evaluation-flow test seeds

Use this folder **instead of** the full department seeds (`AP_seed.sql`, `HR_seed.sql`, and the rest). Those files load too many employees for package testing: a department package waits until **every** active employee with a user account has submitted.

All Employee portal passwords below: `password`

HRIS:


| Login           | Password   | Use for                                              |
| --------------- | ---------- | ---------------------------------------------------- |
| `admin`         | `password` | Admin                                                |
| `elena.delgado` | `password` | HR Manager — templates, governance UI, Team Packages |


Patricia / Miguel also have HRIS accounts from `xPortal_accounts.sql`; for **self-rating** use their portal codes (`HRD-002`, `HRD-003`).

---



## Import order (fresh database)

Drop `raquel_hris`, recreate it, then import **in this order**:

1. `database/1st_schema_tables.sql`
2. `database/2nd_seed_organization.sql`
3. `database/3rd_seed_HR_accounts_.sql`
4. `sample_db_seeds/01_test_employees.sql` ← before portal accounts
5. `database/xPortal_accounts.sql`
6. `database/data/seed_templates.sql`
7. `sample_db_seeds/02_test_hrd_portal_accounts.sql`
8. `sample_db_seeds/03_test_governance_approvers.sql`

Do **not** import `testing_seed.sql` (it is not in the repo) and do **not** import the large `*_seed.sql` department files.

Smoke-check:

- HRIS: `http://localhost/Raquel_HRD/` → `elena.delgado` / `password`
- Portal: `http://localhost/Raquel_HRD/employee/` → `AP-T01` / `password`

---



## Who is in the roster

Each operational department has **2–4** people and a frozen `reports_to` chain.


| Dept                            | Portal logins (staff → … → head)          | Consolidator        | Annual template to pick        |
| ------------------------------- | ----------------------------------------- | ------------------- | ------------------------------ |
| **Acquired Properties (pilot)** | `AP-T01` → `AP-T02` → `AP-T03` → `AP-T04` | `AP-T02` Supervisor | Acquired Properties **Annual** |
| Human Resources                 | `HRD-003` → `HRD-002` → `HRD-001`         | `HRD-002` Patricia  | Human Resources **Annual**     |
| Audit                           | `AUD-T01` → `AUD-T02` → `AUD-T03`         | `AUD-T02`           | Audit Annual                   |
| Business Development            | `BD-T01`, `BD-T02` → `BD-T03`             | `BD-T03` Officer    | BD Annual                      |
| Compliance                      | `COM-T01` → `COM-T02` → `COM-T03`         | `COM-T02`           | Compliance Annual              |
| Finance                         | `FIN-T01` → `FIN-T02` → `FIN-T03`         | `FIN-T02`           | Finance Annual                 |
| General Services                | `GS-T01` → `GS-T02` → `GS-T03`            | `GS-T02`            | GS Annual                      |
| Information Technology          | `IT-T01` → `IT-T02` → `IT-T03`            | `IT-T02`            | IT Annual                      |
| Marketing                       | `MKT-T01` → `MKT-T02` → `MKT-T03`         | `MKT-T02`           | Marketing Annual               |
| Office of the President         | `OP-T01` → `OP-T02`                       | `OP-T02` President  | OP Annual                      |
| Operations                      | `OPS-T01` → `OPS-T02` → `OPS-T03`         | `OPS-T02`           | Operations Annual              |
| Purchasing                      | `PUR-T01`, `PUR-T02` → `PUR-T03`          | `PUR-T03`           | Purchasing Annual              |


Governance (not department members):


| Portal login | Role on package                            |
| ------------ | ------------------------------------------ |
| `GOV-AUD`    | Audit Committee (before Board)             |
| `GOV-BOD`    | Board of Directors — **locks and applies** |


HRD dual login: Elena uses `elena.delgado` on HRIS and `HRD-001` on the Employee portal (self-rating).

---



## Tests in order (do AP first)

Use **one department at a time**. A package only waits on **that** department’s people.

Always use the **same Annual template** and the **same period** (Annual usually auto-fills Jan 1–Dec 31 of the current year). Submit — do not leave drafts.

### Test 1 — Governance is assigned

1. Login HRIS as `elena.delgado`.
2. Open **Evaluation Governance**.
3. Confirm **Board of Directors** = Board Approver and **Audit Committee** = Audit Approver are Active.

If empty, re-import `03_test_governance_approvers.sql` or assign `GOV-BOD` / `GOV-AUD` in the UI.

### Test 2 — Incomplete team blocks consolidation

1. Portal: `AP-T01` → Self Rating → AP **Annual** → fill scores 1–4 → **Submit**.
2. Logout. Do **not** submit as T02/T03/T04 yet.
3. Portal: `AP-T02` → **Team Evaluation Packages**.
4. **Pass:** card says waiting; consolidation is not available. Outstanding names include T02, T03, T04.



### Test 3 — Full AP happy path (Audit → Board lock)

1. Submit AP Annual as `AP-T02`, `AP-T03`, and `AP-T04` (same template/period as T01).
2. `AP-T02` → Team Packages → optional **Adjust** on a member → **Approve and send to next reviewer**.
3. `AP-T03` (Manager) → Approve.
4. `AP-T04` (VP) → Approve.
5. `GOV-AUD` → Approve (view-only; no Adjust).
6. `GOV-BOD` → **Approve, lock, and apply results** → confirm dialog.
7. **Pass:** status **Approved and Applied**; members get a notification; Evaluation History shows finals.



### Test 4 — Lock holds

1. As `GOV-BOD` or `AP-T02`, open the same package / Adjust URL again.
2. **Pass:** edits and further approvals are rejected (locked).



### Test 5 — Return for revision

Repeat Test 3 on a **different department** (IT is a good short chain: `IT-T01` → `IT-T02` → `IT-T03` → Audit → Board).

Before Board:

1. At Manager (`IT-T03`), click **Return for revision** with a reason.
2. **Pass:** package goes back to Supervisor `IT-T02` as Pending.
3. `IT-T02` Approve again, then finish Audit → Board.



### Test 6 — HRD chain (HRIS people + portal)

HRD package members are only Elena, Patricia, Miguel (3 people).

1. Portal self-rate HR **Annual** as `HRD-003`, `HRD-002`, `HRD-001`.
2. Consolidator: `HRD-002` (or HR Supervisor on Team Packages) → Approve.
3. `HRD-001` / Elena → Approve.
4. `GOV-AUD` → `GOV-BOD` lock.
5. **Pass:** HRD package completes like AP.



### Test 7 — Spot-check remaining departments

Same pattern, only that dept’s logins and **that** dept’s Annual template:

1. Audit (`AUD-T*`)
2. Finance (`FIN-T*`)
3. Operations (`OPS-T*`)
4. Purchasing (`PUR-T*`) — both staff must submit before `PUR-T03` can consolidate

You do not need to finish every department on day one. AP + HRD + one return test is enough to prove the flow.

---



## Common blockers


| Symptom                               | Cause                                                                                              |
| ------------------------------------- | -------------------------------------------------------------------------------------------------- |
| Waiting forever for submissions       | Someone in that dept still active did not submit, or used a different template/period              |
| No Audit/Board on the timeline        | Governance was assigned **after** the package was created — start a new period or another template |
| Board user rejected in UI             | User is already in a `reports_to` chain — use `GOV-BOD` / `GOV-AUD` only                           |
| Elena cannot open Self Rating on HRIS | Use Employee portal `HRD-001`, not `elena.delgado`                                                 |
| Too many people in a package          | You imported a full `*_seed.sql` department file — drop DB and follow the import list above        |


