# Evaluation UX Findings — Assessment & Implementation Plan

Status: **Assessment only** (not yet implemented).  
Source: product walkthrough notes, 24 Aug 2026.  
Related: `To-Do List/new_evaluation_implementation_plan.md` (org-driven package engine).

This plan rearranges the eight findings into **workstreams**, maps each to real pages, scores **current vs gap**, and sets a build order. It is the checklist for the next implementation pass.

---

## 0. Page map (canonical names)

| Intended page | Actual file | Audience |
| --- | --- | --- |
| Team Evaluation History | `employee/team-evaluation-history.php` | Current-step reviewers |
| Team List | `employee/team-list.php` | Branch supervisors/managers (`?filter=all` or `direct`) |
| Team Evaluation Packages | `employee/team-evaluation-packages.php` | Current-step reviewers |
| Individual Evaluation History | `employee/evaluation-history.php` | Employee (own cycles) |
| Completed / locked archive | `employee/completed-ratings.php` | Employee (final results) |
| HRIS evaluation history | `manager|supervisor|staff/evaluation-history.php` | HR roles |

**Scope rule (finding 7):**  
- `completed-ratings.php` = locked, fully approved results.  
- `evaluation-history.php` = audit trail: original self-rating, officer adjustments, turn-by-turn status.

---

## 1. Findings grouped into workstreams

### A. Visibility & notifications (findings 1, 5)

**Intent:** Supervisors know immediately when someone submits, who is still pending, and which pipeline stage a package is in. Higher officers must not browse packages that are not yet their turn.

| ID | Finding | Current assessment | Target |
| --- | --- | --- | --- |
| A1 | Bell + sidebar badge when a team member finishes self-rating | Notifications exist on package hand-off (`createNotification` to next reviewer). **Gap:** no dedicated “self-rating completed” event to the consolidator, and sidebar badge is not tied to that event. | On submit of self-rating: notify current consolidator; increment Team Packages badge. |
| A2 | Pending-submissions roster on Team Evaluation History | History page lists **completed/assigned packages**, not a live “who has not submitted” roster. | Add a **Waiting for self-ratings** panel: name, position, submitted Y/N. |
| A3 | Sequential visibility (higher officers cannot see records until the current evaluator submits) | Packages query is already **Pending step + current user**. History may still expose earlier packages. Team List is **not** gated by route. | History + list + packages: only current Pending step (plus own completed after lock). No peeking ahead. |
| A4 | Pipeline status badges everywhere | Package status strings exist (`Pending Consolidation`, `Pending Review`, `Pending Audit Approval`, `Pending Board Approval`). Labels are generic, not “Pending Supervisor [Name]”. | Human-readable stage: `Pending {step_label} — {reviewer name}` on packages, history, team list, HRIS portals. |

### B. Team List score badge (finding 2)

**Intent:** `.score-circle.no-score` fills as soon as the employee **submits** a self-rating, not only after final `Approved`.

| ID | Finding | Current assessment | Target |
| --- | --- | --- | --- |
| B1 | Score circle stays empty until approved | `team-list.php` loads `total_score` only where `status = 'Approved'`. Submitted-but-in-review scores stay empty. | Show submitted self-rating (or current effective score) immediately; use `no-score` only for Draft / not started. Optional tooltip: “Self-rating (not yet finalized)”. |

### C. Package actions & hand-off copy (finding 3)

**Intent:** One action for view+edit; confirmations name the **next person**; supervisor edits persist for later reviewers.

| ID | Finding | Current assessment | Target |
| --- | --- | --- | --- |
| C1 | Remove redundant View; Adjust does both | Member rows still have View + Adjust. Governance steps already hide Adjust. | Drop View for editable steps; Adjust opens `package-member-review.php` (read + edit). Keep a read-only path only for locked/governance. |
| C2 | Descriptive hand-off messages | Success is generic: “Package action saved successfully.” Next-person name is in the **notification**, not the flash. | Flash: “Package adjusted and forwarded to {Next Name} ({step_label}).” Same tone for return: “Returned to {Previous Name}.” |
| C3 | Supervisor edits persist for the next consolidator | Overrides live on `evaluation_scores` (`supervisor_override_score`) and shared Behavior is recalculated. Next reviewer should already see them if they open Adjust/View. | Verify end-to-end in AP test roster; if next step sees original only, fix query to prefer override. |

### D. Audit trail: original vs modified (finding 4)

**Intent:** When an officer changes a score, history shows **self-rating vs current**.

| ID | Finding | Current assessment | Target |
| --- | --- | --- | --- |
| D1 | Original vs modified on Evaluation History | Review UI already columns Self-rating vs Reviewer rating. `evaluation-history.php` list shows current KRA/Behavior/Total only. | Detail view: per-criterion original vs override; header: original total vs current total. `completed-ratings.php` stays locked finals only. |

### E. Accessibility & CSS (finding 6)

**Intent:** Larger type, higher contrast, more space for senior users; no inline CSS; Raquel palette in a dedicated stylesheet.

| ID | Finding | Current assessment | Target |
| --- | --- | --- | --- |
| E1 | Shared Behavior + package layout readability | Package pages mix Bootstrap + existing portal CSS; Shared Behavior is compact. | Bump type/contrast/spacing on Shared Behavior, tables, and status chips. |
| E2 | Move inline styles to CSS | Package/history/list still use inline styles. `assets/css/evaluation-packages.css` already exists. | Extract remaining inline rules into that file (or `evaluation-workflow.css`); tokens from Raquel gold/green. Do **not** restyle the entire HRIS in this pass. |

### F. HR hierarchy permissions (finding 8) — **blocker**

**Intent:** HR Staff self-rate; HR Supervisor and HR Manager receive **Team Evaluation Packages** when their reports submit.

| ID | Finding | Current assessment | Target |
| --- | --- | --- | --- |
| F1 | HR Supervisor/Manager missing Team Packages on Employee portal | Employee-portal Team Packages/My Team/History are added only when `$is_supervisor_menu && department !== 'Human Resources'`. **HR is explicitly excluded.** | Include HR Supervisor/Manager (and consolidators) in that menu when they have direct reports or a Pending package step. |
| F2 | HR Supervisor missing Team Packages on HRIS | Manager HRIS menu **has** Team Evaluation Packages. Supervisor HRIS menu **does not**. Staff should not consolidate unless they are the route consolidator. | Add Team Packages to HR Supervisor sidebar (same `employee/team-evaluation-packages.php`). |
| F3 | Page ACL vs HR Staff | `team-evaluation-packages.php` allows `Employee`, `HR Manager`, `HR Supervisor` only — **not HR Staff**. Correct unless a Staff user is literally the consolidator. | Keep Staff out of consolidate unless seeded as consolidator; then allow by **route assignment**, not role name. |
| F4 | Route must include HR Supervisor → HR Manager | Package route follows `reports_to`. Test seed must set Miguel → Patricia → Elena. | Confirm HRD `reports_to` + dual portal logins (`HRD-001`…`003`) before UX polish. |

---

## 2. Priority (build this order)

Critical path first: HR cannot even open the module.

| Seq | Workstream | Why first |
| --- | --- | --- |
| 1 | **F — HR menu + ACL** | Without this, HRD pilot (finding 8) cannot be tested. |
| 2 | **A3 — turn-based visibility** | Core workflow rule; other UI is misleading if skipped. |
| 3 | **A1 + A2 — notify + pending roster** | Makes the complete-team gate usable. |
| 4 | **C1 + C2 + C3 — Adjust-only + copy + persistence** | Daily consolidator UX. |
| 5 | **B1 — Team List score on submit** | Fast visual confirmation. |
| 6 | **A4 + D1 — pipeline labels + original vs modified** | Transparency / audit. |
| 7 | **E — a11y + stylesheet** | After behavior is correct, restyle those pages only. |
| 8 | **Finding 7** | Copy/nav labels so archive vs history stay distinct (small docs/UI pass). |

---

## 3. Acceptance slices (for tomorrow’s test after code)

Use `sample_db_seeds` AP + HRD roster.

1. **HR access:** Login Patricia (`HRD-002` portal and/or HR Supervisor HRIS) → Team Evaluation Packages is visible after Miguel submits. Elena sees it only after Patricia forwards.
2. **Pending roster:** After only `AP-T01` submits, consolidator sees T02–T04 listed as outstanding.
3. **Bell/badge:** Consolidator gets a notification when `AP-T01` submits; badge count increases.
4. **No peeking:** `AP-T03` cannot open the AP package until T02 approves.
5. **Adjust only:** Package member row has Adjust, not View (until lock/governance).
6. **Hand-off copy:** After T02 approve, flash names `AP-T03` (or job title).
7. **Persistence:** T02 changes a KRA; T03 sees that value, not the raw self-score only.
8. **Team List:** `AP-T01` circle shows a number after submit, before Board lock.
9. **History vs completed:** History shows original vs adjusted; Completed Ratings shows locked totals only.
10. **Pipeline badge:** Status reads like “Pending AP Supervisor I — Ronald Lopez”, not only “Pending Review”.

---

## 4. Out of scope this pass

- Rebuilding payroll/attendance.
- Full org-chart client sign-off (still in the org-driven plan).
- Global HRIS visual redesign (only evaluation workflow pages).
- Changing Board/Audit lock/apply rules (already implemented).

---

## 5. Decision log (from these findings)

| Decision | Rationale |
| --- | --- |
| History = audit; Completed Ratings = locked archive | Matches finding 7; avoids mixing draft vs official scores. |
| Visibility is by **current route step**, not by rank | Enforces sequential hand-off (finding 1 / A3). |
| HR exclusion in the employee menu is a **bug**, not a product rule | Finding 8; HRD is a first-class department in the org-driven flow. |
| Adjust replaces View on active steps | Fewer duplicate actions (finding 3). |
| Accessibility work follows functional slices | Avoid restyling a broken permission model. |
