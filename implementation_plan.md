# Implementation Plan: PDS & Add Employee Wizard Revamp

## Overview

A mobile-first revamp of the **Add New Employee (Personal Data Sheet) Wizard** used in two places:
- **HR Manager**: [`manager/add-employee.php`](file:///c:/xampp/htdocs/raquel-hris/manager/add-employee.php) — creates new employees
- **Employee Portal**: [`employee/pds-wizard.php`](file:///c:/xampp/htdocs/raquel-hris/employee/pds-wizard.php) — employee fills out their own PDS

Both share the same form body via [`includes/employee-form-steps.php`](file:///c:/xampp/htdocs/raquel-hris/includes/employee-form-steps.php) and the same JS in [`assets/js/employee-form.js`](file:///c:/xampp/htdocs/raquel-hris/assets/js/employee-form.js).

**No backend logic changes.** All PHP form processing in `add-employee.php` stays exactly the same. This is a pure **frontend revamp**.

---

## Scope (v1 — Pragmatic & Impactful)

| In Scope | Out of Scope |
|:---|:---|
| 4-portal step grouping (replaces 12 flat tabs) | Drag-reorder handles on repeaters |
| Sticky bottom mobile navigation bar | Radial progress ring (horizontal bar instead) |
| Accordion-style repeater cards (mobile) | Glassmorphic blur effects |
| Gov't ID auto-formatting (already partially done) | Light/dark mode toggle |
| Touch-optimized 48px tap targets | Real-time field-level validation engine |
| Review summary panel (Step 4 / final step) | Glow-sync save indicator animation |
| `inputmode`/`type` optimization for mobile keyboards | — |

---

## Audit Findings (What We're Working With)

### Current State
- **12 flat step tabs** rendered as pill buttons, wrapping into multiple rows on small screens
- **`showStep()`** in `employee-form.js` (line 7) controls visibility — already solid, just needs grouping layer on top
- **Progress bar** (`.pds-progress` / `.pds-progress-bar`) exists in `style.css` lines 3420–3432
- **`.repeater-row`** CSS exists (lines 1147–1194) — functional but no mobile accordion behavior
- **`.wizard-footer`** CSS class exists (lines 1122–1144) — sticky footer already defined but **not used** in the current wizard HTML
- **Gov't ID auto-formatters** already written in `employee-form.js` lines 591–656 ✅ — just need `inputmode` attributes added to the HTML
- **Draft restore banner** and localStorage autosave already implemented (lines 752–851)
- **Form step HTML** split across 12 `div#stepN` blocks in `employee-form-steps.php`

### Key Constraint
`employee-form-steps.php` is shared between the manager's add-employee page AND the employee's PDS wizard. Changes here affect both, which is fine — the revamp applies to both.

---

## Proposed Changes

### Stage 1 — CSS Updates
#### [MODIFY] [style.css](file:///c:/xampp/htdocs/raquel-hris/assets/css/style.css)

Add new CSS rules (appended, no deletions to existing rules):

1. **Portal tab group styles** — 4 large portal buttons replacing the 12 small pills
2. **Sub-step indicator** — small numbered dots inside each portal showing progress within that group
3. **Mobile sticky bottom nav** — upgrade the existing `.wizard-footer` to be always-active on mobile (`position: fixed` below `768px`)
4. **Mobile accordion repeater** — when viewport ≤768px, `.repeater-row` collapses to a summary line; tap to expand full inputs
5. **Touch target enforcement** — `.repeater-row .btn-remove-row`, `.btn-add-row`, and all `.form-control` inside wizard get `min-height: 48px` on mobile
6. **Portal progress bar** — wider, taller bar (12px height) with labeled percentage text

---

### Stage 2 — HTML Structure (Form Steps)
#### [MODIFY] [employee-form-steps.php](file:///c:/xampp/htdocs/raquel-hris/includes/employee-form-steps.php)

**No fields removed or renamed.** Only wrapping structure and `inputmode`/`type` attributes change.

**Portal grouping** — the 12 steps become 4 portals:

| Portal | Steps Included | Step IDs |
|:---|:---|:---|
| **1 · Core Identity** | Personal Info, Addresses & Contacts | `step1` |
| **2 · Background** | Family, Education, Work, Training, Voluntary | `step2`–`step6` |
| **3 · Qualifications** | Eligibility, Skills, Assets, Disclosures | `step7`–`step10` |
| **4 · Final** | References, Employment + Review | `step11`, `step12` |

The existing `step1`…`step12` `div` IDs stay **untouched** so no JS breaks.

**`inputmode` / keyboard type additions** (mobile keyboard optimization):
- `sss_number`, `philhealth_number`, `pagibig_number`, `tin_number`, `contact_number`, `telephone_number`, `emergency_contact_number` → add `inputmode="numeric"`
- `email` → `type="email"` (already set ✅)
- `date_of_birth`, `hire_date` → `type="date"` (already set ✅)

**Accordion wrapper for repeater containers** — each repeater container (`#childrenContainer`, `#workContainer`, etc.) gets a wrapping `div.repeater-accordion` so CSS can target mobile collapse behavior.

---

### Stage 3 — Wizard Navigation HTML
#### [MODIFY] [manager/add-employee.php](file:///c:/xampp/htdocs/raquel-hris/manager/add-employee.php)
#### [MODIFY] [employee/pds-wizard.php](file:///c:/xampp/htdocs/raquel-hris/employee/pds-wizard.php)

Replace the current 12-tab pill row with the **4-portal navigation block**:

```
[ ① Core Identity ]  [ ② Background ]  [ ③ Qualifications ]  [ ④ Final ]
```

Each portal button shows:
- Portal number + label (desktop)
- Number only + icon (mobile)
- A small "sub-step dot row" underneath showing how many steps are in that portal and which is active

Replace the current inline Back/Next button block with the **`wizard-footer` sticky bar** (the CSS class already exists — just needs to be wired up):
```
[ ← Back ]   Portal 2 of 4 · Step 3 of 5   [ Next → ]
                                             [ Save & Submit ] ← on final step
```

On **mobile (≤768px)**: footer becomes `position: fixed; bottom: 0` so it's always visible above the keyboard.

---

### Stage 4 — JavaScript Updates
#### [MODIFY] [assets/js/employee-form.js](file:///c:/xampp/htdocs/raquel-hris/assets/js/employee-form.js)

The existing `showStep()` function (line 7) stays. We add a **portal layer on top**:

1. **`PORTAL_MAP` constant** — maps each portal to its step range:
   ```js
   const PORTAL_MAP = [
     { id: 1, label: 'Core Identity',    steps: [1] },
     { id: 2, label: 'Background',       steps: [2,3,4,5,6] },
     { id: 3, label: 'Qualifications',   steps: [7,8,9,10] },
     { id: 4, label: 'Final',            steps: [11,12] },
   ];
   ```

2. **`showPortal(portalId)`** — navigates to the first step of that portal, updates portal tab active state and sub-step dots

3. **`nextStep()` / `prevStep()` upgrades** — when advancing past the last step in a portal, automatically move to the next portal with a smooth scroll-to-top; update portal tab states

4. **Progress bar update** — calculate `(currentStep / 12) * 100` same as before, but also update the portal sub-step dots

5. **Mobile accordion for repeaters** — on screens ≤768px, inject a summary `span` into each `.repeater-row` showing a one-line summary of the entry (e.g., "Juan dela Cruz — 2019–2023"). Clicking toggles a `.expanded` class that shows all inputs. On desktop, all inputs always visible (no change).

6. **`inputmode` enforcement** — for any numeric ID field that doesn't yet have `inputmode` in HTML, set it programmatically on `DOMContentLoaded` as a fallback.

---

### Stage 5 — Review Panel (Step 12 / Final Portal)

The current Step 12 in `pds-wizard.php` (lines 291–314) dynamically replaces its content via JS `innerHTML`. We upgrade this with a **structured summary panel**:

- Grouped into 4 sections matching the portals
- Each section shows key filled-in values in a clean 2-column layout
- Empty/blank fields shown in muted gray (not errors — just visual)
- "Edit" link per section that jumps back to that portal
- On `add-employee.php`, Step 12 shows the Employment fields (as is) + an inline summary of everything filled above

---

## File Change Summary

| File | Change Type | Description |
|:---|:---|:---|
| [`assets/css/style.css`](file:///c:/xampp/htdocs/raquel-hris/assets/css/style.css) | MODIFY | Append ~120 lines of new CSS for portal tabs, sticky mobile footer, accordion repeaters, touch targets |
| [`includes/employee-form-steps.php`](file:///c:/xampp/htdocs/raquel-hris/includes/employee-form-steps.php) | MODIFY | Add `inputmode` attributes; wrap repeater containers in `.repeater-accordion`; no field/name changes |
| [`manager/add-employee.php`](file:///c:/xampp/htdocs/raquel-hris/manager/add-employee.php) | MODIFY | Replace 12-tab pill row with 4-portal nav; replace inline nav buttons with `.wizard-footer` sticky bar |
| [`employee/pds-wizard.php`](file:///c:/xampp/htdocs/raquel-hris/employee/pds-wizard.php) | MODIFY | Same portal nav + sticky footer; upgrade Step 12 summary panel |
| [`assets/js/employee-form.js`](file:///c:/xampp/htdocs/raquel-hris/assets/js/employee-form.js) | MODIFY | Add `PORTAL_MAP`, `showPortal()`, upgrade `nextStep()`/`prevStep()`, add mobile accordion logic |

**No new files. No database changes. No PHP processing changes.**

---

## Verification Plan

### After Each Stage
- Test on desktop (Chrome, ≥1024px) — existing functionality must not break
- Test on mobile emulation (Chrome DevTools, 375px iPhone SE)
- Confirm all 12 steps are still reachable and all form fields submit correctly

### Final Verification
1. **Add Employee flow** (manager): Fill all 12 steps → submit → confirm employee created in DB
2. **PDS Wizard flow** (employee): Fill steps → submit → confirm PDS submission record created
3. **Draft restore**: Fill partial form, close tab, reopen → confirm draft banner appears and restores data
4. **Mobile keyboard**: Tap SSS/PhilHealth/mobile number fields on phone → confirm numeric keyboard appears
5. **Back/Next navigation**: Step through all 4 portals forward and backward → confirm progress bar and portal indicators update correctly

---

## Open Questions

> [!IMPORTANT]
> **Color scheme for portal tabs**
> The existing system uses deep forest green (`--primary-blue: #294306`) with gold accents (`--primary-light: #BD9414`). Should the 4 portal tabs use the **existing green/gold palette**, or a distinct color per portal (e.g., blue for Core Identity, green for Background, amber for Qualifications, purple for Final)?

> [!NOTE]
> **Step 12 on add-employee.php**
> Currently Step 12 on the manager's wizard shows **Employment fields** (hire date, department, job title, etc.) — these are critical required fields. The review summary panel should appear **after** or **alongside** these fields, not replace them. Confirm this is the right approach before we build Stage 5.
