# Employee Portal UX Revamp — Developer Guide

## Table of Contents

1. [CSS Variable System](#1-css-variable-system)
   - 1.1 [Adding New Colors](#11-adding-new-colors)
   - 1.2 [Adding New Spacing Values](#12-adding-new-spacing-values)
   - 1.3 [Adding New Typography Values](#13-adding-new-typography-values)
   - 1.4 [Adding Other Token Types](#14-adding-other-token-types)
   - 1.5 [Component Class Naming Conventions](#15-component-class-naming-conventions)
2. [CSS File Organization](#2-css-file-organization)
3. [Component Library](#3-component-library)
   - Quick Reference Table
   - 3.1 [Stat Card](#31-stat-card)
   - 3.2 [Content Card](#32-content-card)
   - 3.3 [Status Badges](#33-status-badges)
   - 3.4 [Buttons](#34-buttons)
   - 3.5 [Forms](#35-forms)
   - 3.6 [Rating Scale](#36-rating-scale)
   - 3.7 [Progress Indicator](#37-progress-indicator)
   - 3.8 [Progress Bar](#38-progress-bar)
   - 3.9 [Notifications](#39-notifications)
   - 3.10 [Feedback Alerts](#310-feedback-alerts)
   - 3.11 [Toast Notifications (JavaScript)](#311-toast-notifications-javascript)
   - 3.12 [Loading States](#312-loading-states)
4. [Maintenance Guide](#4-maintenance-guide)
   - 4.1 [Responsive Breakpoint Strategy](#41-responsive-breakpoint-strategy)
   - 4.2 [WCAG AAA Accessibility Requirements](#42-wcag-aaa-accessibility-requirements)
   - 4.3 [Performance Optimization](#43-performance-optimization)
   - 4.4 [Testing Checklist](#44-testing-checklist)
   - 4.5 [File Inclusion Order](#45-file-inclusion-order)

---

## 1. CSS Variable System

All design tokens are defined as CSS custom properties in `/assets/css/employee-portal-variables.css`. This file is the **single source of truth** for colors, spacing, typography, shadows, radii, and transitions. Every other CSS file consumes these variables — never hardcode hex values, pixel sizes, or font names outside this file.

### 1.1 Adding New Colors

All color additions must pass WCAG AAA contrast verification **before** being committed.

**Step 1 — Decide which category the color belongs to:**

| Category | Variable prefix | Purpose |
|---|---|---|
| Brand / theme | `--color-primary`, `--color-primary-light`, `--color-primary-dark` | Core brand identity |
| Backgrounds | `--color-bg-*` | Page and card backgrounds |
| Text | `--color-text-*` | Body copy, headings, muted labels |
| Status | `--color-success`, `--color-warning`, `--color-danger`, `--color-info` | Semantic feedback colors |
| Borders | `--color-border-*` | Dividers and input outlines |

**Step 2 — Add the variable under the correct section comment:**

```css
/* In employee-portal-variables.css */
:root {
  /* ===== COLORS ===== */

  /* Example: adding a secondary accent */
  --color-accent-secondary: #2E5F8A;  /* 7.2:1 contrast on white */
}
```

**Step 3 — Verify WCAG AAA contrast ratios:**

- Normal text on white background: **minimum 7:1**
- Large text (≥18pt / ≥14pt bold) on white background: **minimum 4.5:1**
- UI component boundaries against adjacent color: **minimum 3:1**
- Icons and graphical objects: **minimum 3:1**

Use https://webaim.org/resources/contrastchecker/ or the browser DevTools Accessibility panel to check.

**Step 4 — Add a matching tint for backgrounds if the color will be used on cards or badges:**

```css
/* In the component CSS file that uses the color: */
.my-badge {
  background: rgba(46, 95, 138, 0.10);   /* 10% tint of --color-accent-secondary */
  color: var(--color-accent-secondary);
  border: 1px solid var(--color-accent-secondary);
}
```

Always pair color with a non-color signal (icon, text label, pattern) to satisfy Requirement 4.6.

---

### 1.2 Adding New Spacing Values

The spacing scale uses an **8px base unit**. All values are multiples of 4px (half-step) to maintain visual rhythm.

Current scale:

| Variable | Value | Pixels | Typical use |
|---|---|---|---|
| `--space-xs` | 0.25rem | 4px | Tight gaps, icon margins |
| `--space-sm` | 0.5rem | 8px | Inner padding small elements |
| `--space-md` | 1rem | 16px | Standard element padding |
| `--space-lg` | 1.5rem | 24px | Card padding, section gaps |
| `--space-xl` | 2rem | 32px | Large section separation |
| `--space-2xl` | 3rem | 48px | Page-level section gaps |
| `--space-3xl` | 4rem | 64px | Hero / header spacing |
| `--space-touch-min` | 3rem | 48px | Minimum touch target |
| `--space-touch-comfortable` | 3.5rem | 56px | Icon-only touch target |

**Adding a new spacing step:**

```css
/* In employee-portal-variables.css, under ===== SPACING ===== */
:root {
  --space-4xl: 5rem;    /* 80px — use for full-page hero sections only */
}
```

Rules:
- Keep values on the 4px grid (multiples of 0.25rem).
- Do not create a variable for a one-off spacing need — use an existing token or `calc()`.
- Document the intended use case inline as a comment.

---

### 1.3 Adding New Typography Values

The base font size is `1.1rem` (≈17.6px at browser default of 16px). All sizes are expressed in `rem` to respect user browser settings.

Current type scale:

| Variable | Value | Pixels | Use |
|---|---|---|---|
| `--font-size-xs` | 0.85rem | 13.6px | Timestamps, captions |
| `--font-size-sm` | 0.95rem | 15.2px | Secondary text, table content |
| `--font-size-base` | 1.1rem | 17.6px | Body copy |
| `--font-size-h5` | 1.1rem | 17.6px | Smallest heading |
| `--font-size-h4` | 1.25rem | 20px | Sub-section headings |
| `--font-size-h3` | 1.5rem | 24px | Card and section titles |
| `--font-size-h2` | 1.875rem | 30px | Page sub-headings |
| `--font-size-h1` | 2.25rem | 36px | Page titles |

**Adding a new font-size token:**

```css
/* In employee-portal-variables.css, under ===== TYPOGRAPHY ===== */
:root {
  --font-size-display: 3rem;   /* 48px — hero/dashboard large number emphasis */
}
```

Rules:
- Never go below `0.85rem` for visible text — smaller sizes fail accessibility at low-zoom levels.
- Add a line-height comment if the new size requires a specific override.
- Heading variables (`--font-size-h*`) map directly to `h1`–`h5` defaults in the layout CSS.

---

### 1.4 Adding Other Token Types

**Shadows** — Copy an existing shadow and adjust opacity/spread. Always use the brand green (`rgba(8, 46, 6, …)`) as the shadow color to maintain tonal consistency:

```css
--shadow-NEW: 0 Xpx Ypx rgba(8, 46, 6, 0.12), 0 Xpx Ypx rgba(8, 46, 6, 0.08);
```

**Border radii** — Keep on a geometric scale (6 → 8 → 12 → 16 → full). Do not add values between existing stops unless a specific design token is justified:

```css
--radius-NEW: Xrem;   /* Xpx */
```

**Transitions** — Prefer the three existing durations (fast 150ms / base 250ms / slow 350ms). Only add a new token for a motion-sensitive animation that needs a distinct duration:

```css
--transition-NEW: 500ms ease-in-out;  /* explain why this differs */
```

---

### 1.5 Component Class Naming Conventions

The portal uses a flat BEM-lite naming pattern. Do not use double underscore (`__`) for elements; use a single hyphen join instead.

| Pattern | Example | Description |
|---|---|---|
| `component` | `.stat-card` | Root block element |
| `component-element` | `.stat-card-title`, `.stat-icon` | Child element inside a component |
| `component-modifier` | `.stat-icon-success`, `.btn-lg` | Visual or behavioral variant applied to the component |
| `is-state` | `.is-loading`, `.is-invalid`, `.is-visible` | Transient JS-applied state (boolean on/off) |
| `has-feature` | `.has-badge` | JS-applied modifier indicating a feature is present |
| `d-*` / `col-*` | `.d-none`, `.d-md-block`, `.col-md-6` | Bootstrap utility classes — do not replicate these |

**Rules:**
- Use lowercase and hyphens only — no camelCase or underscores in class names.
- Component names should be nouns (`.notification-item`, `.progress-step`).
- Modifier names should be adjectives or descriptors (`.btn-primary`, `.unread`, `.active`).
- State classes (`is-*`) are added/removed by JavaScript — never style them in component CSS files; put their rules in `employee-portal-feedback.css` if they apply across components, or at the bottom of the component's own CSS file if local.
- Avoid deep nesting selectors. Maximum recommended specificity: `2 classes` (e.g. `.content-card .content-card-title`).

---

## 2. CSS File Organization

```
assets/css/
├── employee-portal-variables.css      # Design tokens — MUST be loaded first
├── employee-portal-layout.css         # Grid, containers, responsive layout helpers
├── employee-portal-navigation.css     # Sidebar, bottom-nav, breadcrumbs
├── employee-portal-cards.css          # Stat cards, content cards, status badges
├── employee-portal-forms.css          # Inputs, labels, validation states, radio/checkbox
├── employee-portal-buttons.css        # All button variants, sizes, loading state
├── employee-portal-ratings.css        # Evaluation rating scale components
├── employee-portal-progress.css       # Multi-step progress indicators and progress bars
├── employee-portal-notifications.css  # Notification list, toast container styles
├── employee-portal-feedback.css       # Touch feedback, loading overlays, focus rings, alerts
└── employee-portal-critical.css       # Inlined above-the-fold CSS (do not add to this freely)
```

All files are loaded via `includes/header.php` for `$effective_role === 'Employee'` only. When adding a **new** component file:

1. Create the file under `assets/css/` following the naming convention `employee-portal-{concern}.css`.
2. Add a `<link>` tag in `header.php` after the existing employee-portal CSS links.
3. Load order matters: variables → layout → components.
4. Do not import CSS files with `@import` — use `<link>` tags to keep browser parallel loading.

---

## 3. Component Library

### Component Quick Reference

A summary of every reusable component, its root class, and its variants. Use the section links for full HTML examples.

| Component | Root class | Key child / modifier classes | Section |
|---|---|---|---|
| Stat Card | `.stat-card` | `.stat-icon`, `.stat-value`, `.stat-label`; icon variants: `stat-icon-success`, `stat-icon-warning`, `stat-icon-info`, `stat-icon-danger` | [3.1](#31-stat-card) |
| Content Card | `.content-card` | `.content-card-header`, `.content-card-title`, `.content-card-body` | [3.2](#32-content-card) |
| Status Badge | `.status-badge` | `.status-success`, `.status-warning`, `.status-danger`, `.status-info` | [3.3](#33-status-badges) |
| Button | `.btn` | `.btn-primary`, `.btn-outline-primary`, `.btn-danger`, `.btn-success`; sizes: `.btn-sm`, `.btn-lg`; layout: `.btn-block`, `.btn-mobile-block`; state: `.is-loading` | [3.4](#34-buttons) |
| Form Group | `.form-group` | `.form-label`, `.form-control`, `.form-select`, `.form-helper`, `.form-error`, `.required-indicator`; states: `.is-invalid`, `.is-valid`; `.is-visible` on `.form-error` | [3.5](#35-forms) |
| Rating Item | `.rating-item` | `.rating-header`, `.rating-title`, `.rating-description`, `.rating-scale`, `.rating-option`, `.rating-input`, `.rating-label`, `.rating-number`, `.rating-text` | [3.6](#36-rating-scale) |
| Rating Section | `.rating-section` | `.rating-section-title`; wraps one or more `.rating-item` | [3.6](#36-rating-scale) |
| Progress Indicator | `.progress-indicator` | `.progress-step`, `.progress-step-number`, `.progress-step-label`, `.progress-line`; step states: `.completed`, `.active` | [3.7](#37-progress-indicator) |
| Progress Bar | `.progress-bar-container` | `.progress-bar-label`, `.progress-bar`, `.progress-bar-fill` | [3.8](#38-progress-bar) |
| Notification Item | `.notification-item` | `.notification-icon`, `.notification-content`, `.notification-title`, `.notification-message`, `.notification-time`, `.notification-actions`, `.notification-action-btn`; icon variants: `notification-icon-success/warning/info/danger`; state: `.unread` | [3.9](#39-notifications) |
| Feedback Alert | `.alert-ep-success` / `.alert-ep-error` | `.alert-ep-dismiss` | [3.10](#310-feedback-alerts) |
| Loading States | `.is-loading` (on btn) | `.loading-spinner`, `.loading-overlay`, `.loading-overlay-spinner`, `.skeleton-card`, `.skeleton-circle`, `.skeleton-line` + `.xl/.lg/.full/.half/.sm` | [3.12](#312-loading-states) |

---

### 3.1 Stat Card

Used on the dashboard to display a single key metric with an icon.

```html
<div class="stat-card">
  <!-- Icon: one of success / warning / info / danger -->
  <div class="stat-icon stat-icon-success" aria-hidden="true">
    <i class="fas fa-check-circle"></i>
  </div>
  <div class="stat-content">
    <h3 class="stat-value">42</h3>
    <p class="stat-label">Completed Evaluations</p>
  </div>
</div>
```

**Icon variants:**

| Class | Color | Use for |
|---|---|---|
| `.stat-icon-success` | Green (`--color-success`) | Positive metrics, completed items |
| `.stat-icon-warning` | Amber (`--color-warning`) | Items needing attention, pending |
| `.stat-icon-info` | Teal (`--color-info`) | Informational counts |
| `.stat-icon-danger` | Red (`--color-danger`) | Critical items, overdue, errors |

**Usage guidelines:**
- Limit to 4–6 stat cards per dashboard row.
- The `stat-value` should be a number or short string (≤ 8 characters). Use `stat-label` for context.
- Mobile: cards stack vertically and center-align automatically via CSS.
- Always include `aria-hidden="true"` on icon elements — the label text carries semantic meaning.

---

### 3.2 Content Card

General-purpose container for any grouped content section.

```html
<div class="content-card">
  <div class="content-card-header">
    <h2 class="content-card-title">
      <i class="fas fa-bell" aria-hidden="true"></i>
      Recent Notifications
    </h2>
    <!-- Optional action button -->
    <button class="btn btn-sm btn-outline-primary">Mark All Read</button>
  </div>
  <div class="content-card-body">
    <!-- Any content here -->
  </div>
</div>
```

**Usage guidelines:**
- One `content-card-header` per card. Header is optional — omit `content-card-header` if no title or action is needed.
- Card title icon uses `--color-primary-light` (gold) automatically.
- Use `margin-bottom: var(--space-lg)` between stacked cards (the component adds this by default).
- Avoid nesting content cards inside other content cards.

---

### 3.3 Status Badges

Use semantic status badges to convey state with both color and text.

```html
<!-- Inline badge (e.g. in a table cell or card) -->
<span class="status-badge status-success">
  <i class="fas fa-check-circle" aria-hidden="true"></i>
  Completed
</span>

<span class="status-badge status-warning">
  <i class="fas fa-clock" aria-hidden="true"></i>
  Pending
</span>

<span class="status-badge status-danger">
  <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
  Overdue
</span>

<span class="status-badge status-info">
  <i class="fas fa-info-circle" aria-hidden="true"></i>
  In Review
</span>

<!-- Bootstrap badge overrides (same palette) -->
<span class="badge bg-success">
  <i class="fas fa-check" aria-hidden="true"></i> Active
</span>
```

**Rules:**
- Always pair color with an icon AND a text label. Never use color alone (Requirement 4.6).
- Use `.status-badge` + `.status-{variant}` for inline contextual badges.
- Bootstrap `.badge.bg-*` classes are overridden to use the same WCAG AAA palette.

---

### 3.4 Buttons

```html
<!-- Primary action (most prominent) -->
<button type="submit" class="btn btn-primary">
  <i class="fas fa-save" aria-hidden="true"></i>
  Save Changes
</button>

<!-- Secondary action -->
<button type="button" class="btn btn-outline-primary">
  <i class="fas fa-times" aria-hidden="true"></i>
  Cancel
</button>

<!-- Destructive action -->
<button type="button" class="btn btn-danger">
  <i class="fas fa-trash" aria-hidden="true"></i>
  Delete Draft
</button>

<!-- Confirmation / positive action -->
<button type="button" class="btn btn-success">
  <i class="fas fa-check" aria-hidden="true"></i>
  Confirm
</button>

<!-- Small size (secondary actions in card headers) -->
<button class="btn btn-sm btn-outline-primary">View All</button>

<!-- Large size (primary CTA, form submit) -->
<button class="btn btn-lg btn-primary">Submit Evaluation</button>

<!-- Full-width on mobile only -->
<button class="btn btn-primary btn-mobile-block">Continue</button>

<!-- Full-width always -->
<button class="btn btn-primary btn-block">Submit</button>

<!-- Loading state (applied by JS on form submit) -->
<button class="btn btn-primary is-loading" aria-busy="true" disabled>
  Saving...
</button>

<!-- Disabled -->
<button class="btn btn-primary" disabled>Not Available</button>
```

**Sizes:**

| Class | min-height | Use |
|---|---|---|
| `.btn-sm` | 40px | Compact contexts (card header actions) |
| _(default)_ | 48px | Standard actions |
| `.btn-lg` | 56px | Primary CTA, form submit buttons |

**State rules:**
- Apply `.is-loading` + `disabled` + `aria-busy="true"` together via JavaScript on form submission.
- The `.is-loading` class hides button text (via `color: transparent`) and shows a CSS spinner.
- For outline buttons, the spinner automatically uses the primary color instead of white.
- Remove `.is-loading` and `disabled` on server response (success or error).

---

### 3.5 Forms

#### Text input

```html
<div class="form-group">
  <label for="employeeId" class="form-label">
    Employee ID
    <span class="required-indicator" aria-label="required">*</span>
  </label>
  <input
    type="text"
    id="employeeId"
    name="employee_id"
    class="form-control"
    placeholder="e.g. EMP-001"
    required
    aria-required="true"
    aria-describedby="employeeIdHelper"
  >
  <small id="employeeIdHelper" class="form-helper">
    Your 7-character employee identifier
  </small>
  <!-- Error div is hidden by default; shown by JS validation -->
  <div class="form-error" id="employeeIdError" role="alert" aria-live="polite"></div>
</div>
```

#### Select / dropdown

```html
<div class="form-group">
  <label for="department" class="form-label">Department</label>
  <select id="department" name="department" class="form-select" aria-required="true">
    <option value="">-- Select Department --</option>
    <option value="operations">Operations</option>
    <option value="admin">Administration</option>
  </select>
  <div class="form-error" role="alert" aria-live="polite"></div>
</div>
```

#### Textarea with character counter

```html
<div class="form-group">
  <label for="comments" class="form-label">Comments</label>
  <textarea
    id="comments"
    name="comments"
    class="form-control"
    rows="4"
    maxlength="500"
    data-char-counter="commentsCounter"
    aria-describedby="commentsCounter"
  ></textarea>
  <div id="commentsCounter" class="char-counter">
    <span data-remaining="500">500</span> characters remaining
  </div>
  <div class="form-error" role="alert" aria-live="polite"></div>
</div>
```

Character counter states: `.char-counter` (normal) → `.char-counter.warning` (≤50 chars left, amber) → `.char-counter.danger` (≤10 chars left, red).

#### Radio buttons

```html
<fieldset class="form-group">
  <legend class="form-label">
    Employment Type
    <span class="required-indicator" aria-label="required">*</span>
  </legend>
  <div class="radio-group">
    <div class="radio-option">
      <input type="radio" id="fullTime" name="employmentType" value="full-time" class="radio-input">
      <label for="fullTime" class="radio-label">Full-Time</label>
    </div>
    <div class="radio-option">
      <input type="radio" id="partTime" name="employmentType" value="part-time" class="radio-input">
      <label for="partTime" class="radio-label">Part-Time</label>
    </div>
    <div class="radio-option">
      <input type="radio" id="contractual" name="employmentType" value="contractual" class="radio-input">
      <label for="contractual" class="radio-label">Contractual</label>
    </div>
  </div>
  <div class="form-error" role="alert" aria-live="polite"></div>
</fieldset>
```

#### Checkboxes

```html
<fieldset class="form-group">
  <legend class="form-label">Preferred Shift</legend>
  <div class="checkbox-group">
    <div class="checkbox-option">
      <input type="checkbox" id="morning" name="shifts[]" value="morning" class="checkbox-input">
      <label for="morning" class="checkbox-label">Morning (6AM – 2PM)</label>
    </div>
    <div class="checkbox-option">
      <input type="checkbox" id="afternoon" name="shifts[]" value="afternoon" class="checkbox-input">
      <label for="afternoon" class="checkbox-label">Afternoon (2PM – 10PM)</label>
    </div>
  </div>
</fieldset>
```

**Form validation states** (applied by `form-validation.js`):

```html
<!-- Invalid — JS adds is-invalid to the input; error div becomes visible -->
<input type="email" class="form-control is-invalid" ...>
<div class="form-error is-visible" role="alert">Please enter a valid email address.</div>

<!-- Valid -->
<input type="email" class="form-control is-valid" ...>
```

**Form rules:**
- Every `<input>`, `<select>`, `<textarea>` must have a matching `<label for="...">` — never use `placeholder` as the only label.
- All `<input>` and `<select>` must have `min-height: 48px` (the `.form-control` and `.form-select` classes apply this automatically).
- Use `<fieldset>` + `<legend>` for radio and checkbox groups.
- Position the `.form-error` div immediately after the input/select, never before it.

**Auto-save** — Add `data-autosave` to any form to enable 2-second debounce auto-save:

```html
<form id="pdsForm"
      data-autosave="pdsForm"
      data-autosave-endpoint="/employee/ajax/save-pds-section.php">
  <!-- fields -->
</form>
```

Server must return `{"success": true}` or `{"success": false, "message": "reason"}`.

---

### 3.6 Rating Scale

Used in self-rating and 360-evaluation pages for KRA and behavior scoring.

```html
<div class="rating-section">
  <h2 class="rating-section-title">Key Result Areas (KRA)</h2>

  <div class="rating-item">
    <div class="rating-header">
      <h3 class="rating-title">Customer Service Excellence</h3>
      <p class="rating-description">
        Demonstrates exceptional service quality and responsiveness to customer needs.
      </p>
    </div>

    <div class="rating-scale">
      <div class="rating-option">
        <input type="radio" id="kra1_1" name="kra1" value="1" class="rating-input">
        <label for="kra1_1" class="rating-label">
          <span class="rating-number">1</span>
          <span class="rating-text">Needs Improvement</span>
        </label>
      </div>
      <div class="rating-option">
        <input type="radio" id="kra1_2" name="kra1" value="2" class="rating-input">
        <label for="kra1_2" class="rating-label">
          <span class="rating-number">2</span>
          <span class="rating-text">Below Expectations</span>
        </label>
      </div>
      <div class="rating-option">
        <input type="radio" id="kra1_3" name="kra1" value="3" class="rating-input">
        <label for="kra1_3" class="rating-label">
          <span class="rating-number">3</span>
          <span class="rating-text">Meets Expectations</span>
        </label>
      </div>
      <div class="rating-option">
        <input type="radio" id="kra1_4" name="kra1" value="4" class="rating-input">
        <label for="kra1_4" class="rating-label">
          <span class="rating-number">4</span>
          <span class="rating-text">Exceeds Expectations</span>
        </label>
      </div>
      <div class="rating-option">
        <input type="radio" id="kra1_5" name="kra1" value="5" class="rating-input">
        <label for="kra1_5" class="rating-label">
          <span class="rating-number">5</span>
          <span class="rating-text">Exceptional</span>
        </label>
      </div>
    </div>
  </div>

  <!-- Repeat .rating-item for each KRA -->
</div>
```

**Interactive states (CSS-driven):**

| State | Visual |
|---|---|
| Default | Grey background, muted number circle |
| Hover / focus | Slides right 4px, primary border, primary number circle |
| Checked | Green tint background, filled primary number circle, animated ✓ |

**Usage guidelines:**
- Each `name` attribute must be unique per KRA (e.g. `kra1`, `kra2`, `behavior1`).
- Each `id` must be unique on the page — use a pattern like `{section}_{kra-index}_{value}`.
- Use `.rating-section` to group related criteria under a single heading.
- The minimum touch target for each rating option is 56px height (`.rating-label` default).
- When the rating group needs an explicit ARIA label (e.g. in a modal or dynamic context), add `role="radiogroup"` and `aria-labelledby` pointing at the `.rating-title`:

```html
<!-- Accessible variant with explicit ARIA grouping -->
<div class="rating-item">
  <div class="rating-header">
    <h3 class="rating-title" id="kra1-label">Customer Service Excellence</h3>
    <p class="rating-description">
      Demonstrates exceptional service quality and responsiveness to customer needs.
    </p>
  </div>
  <div class="rating-scale" role="radiogroup" aria-labelledby="kra1-label">
    <div class="rating-option">
      <input type="radio" id="kra1_1" name="kra1" value="1" class="rating-input">
      <label for="kra1_1" class="rating-label">
        <span class="rating-number">1</span>
        <span class="rating-text">Needs Improvement</span>
      </label>
    </div>
    <!-- ...options 2–5... -->
  </div>
</div>
```

The `role="radiogroup"` + `aria-labelledby` pattern is recommended on pages where multiple rating sections appear, so screen readers announce the criterion name before the rating choices.

---

### 3.7 Progress Indicator

Multi-step process indicator for forms with 2–5 steps.

```html
<div class="progress-indicator" role="list" aria-label="Form progress">

  <div class="progress-step completed" role="listitem">
    <div class="progress-step-number" aria-label="Step 1 complete">
      <i class="fas fa-check" aria-hidden="true"></i>
    </div>
    <div class="progress-step-label">Personal Info</div>
  </div>

  <div class="progress-line completed" aria-hidden="true"></div>

  <div class="progress-step active" role="listitem" aria-current="step">
    <div class="progress-step-number" aria-label="Step 2, current step">2</div>
    <div class="progress-step-label">Education</div>
  </div>

  <div class="progress-line" aria-hidden="true"></div>

  <div class="progress-step" role="listitem">
    <div class="progress-step-number" aria-label="Step 3, not yet started">3</div>
    <div class="progress-step-label">Work Experience</div>
  </div>

  <div class="progress-line" aria-hidden="true"></div>

  <div class="progress-step" role="listitem">
    <div class="progress-step-number" aria-label="Step 4, not yet started">4</div>
    <div class="progress-step-label">Review</div>
  </div>

</div>
```

**Step states:**

| Class on `.progress-step` | Class on `.progress-line` | Visual |
|---|---|---|
| `.completed` | `.completed` | Green filled circle with ✓, green connector line |
| `.active` | — | Primary filled circle with pulse animation, grey connector |
| _(none)_ | _(none)_ | Grey outlined circle, grey connector line |

**Updating step state with JavaScript:**

```javascript
function goToStep(stepNumber) {
  const steps = document.querySelectorAll('.progress-step');
  const lines = document.querySelectorAll('.progress-line');

  steps.forEach((step, idx) => {
    step.classList.remove('completed', 'active');
    step.removeAttribute('aria-current');
    if (idx < stepNumber - 1) {
      step.classList.add('completed');
    } else if (idx === stepNumber - 1) {
      step.classList.add('active');
      step.setAttribute('aria-current', 'step');
    }
  });

  lines.forEach((line, idx) => {
    line.classList.toggle('completed', idx < stepNumber - 1);
  });
}
```

---

### 3.8 Progress Bar

For processes with more than 5 steps or where percentage completion is more meaningful than discrete steps.

```html
<div class="progress-bar-container">
  <div class="progress-bar-label">
    <span>Evaluation Progress</span>
    <span>60%</span>
  </div>
  <div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100">
    <div class="progress-bar-fill" style="width: 60%;"></div>
  </div>
</div>
```

Update dynamically via JavaScript: set `style.width` and `aria-valuenow` on the outer `.progress-bar` element.

---

### 3.9 Notifications

#### Notification list

```html
<div class="notification-list">

  <!-- Unread notification -->
  <div class="notification-item unread">
    <div class="notification-icon notification-icon-info" aria-hidden="true">
      <i class="fas fa-info-circle"></i>
    </div>
    <div class="notification-content">
      <h3 class="notification-title">Evaluation Period Started</h3>
      <p class="notification-message">
        The Q2 2024 evaluation period has begun. Please complete your self-rating by June 30.
      </p>
      <span class="notification-time">2 hours ago</span>
    </div>
    <div class="notification-actions">
      <button class="notification-action-btn" aria-label="Mark notification as read">
        <i class="fas fa-check" aria-hidden="true"></i>
      </button>
    </div>
  </div>

  <!-- Read notification -->
  <div class="notification-item">
    <div class="notification-icon notification-icon-success" aria-hidden="true">
      <i class="fas fa-check-circle"></i>
    </div>
    <div class="notification-content">
      <h3 class="notification-title">Self-Rating Submitted</h3>
      <p class="notification-message">Your Q1 2024 self-rating has been recorded.</p>
      <span class="notification-time">3 days ago</span>
    </div>
    <div class="notification-actions">
      <button class="notification-action-btn" aria-label="View notification details">
        <i class="fas fa-eye" aria-hidden="true"></i>
      </button>
    </div>
  </div>

</div>
```

**Icon variants:**

| Class | Color | Use for |
|---|---|---|
| `.notification-icon-success` | Green | Approvals, completions |
| `.notification-icon-warning` | Amber | Upcoming deadlines, reminders |
| `.notification-icon-info` | Teal | General announcements |
| `.notification-icon-danger` | Red | Overdue items, urgent actions |

**Unread state:** Add `.unread` to `.notification-item`. This applies a left green border and a subtle tint background. Remove the class (via AJAX + JS) when the user marks the item as read.

**Marking a notification as read (JavaScript):**

```javascript
/**
 * Mark a single notification as read.
 * @param {HTMLElement} item - The .notification-item element
 * @param {string|number} notificationId - Server-side notification ID
 */
async function markNotificationRead(item, notificationId) {
  try {
    const res = await fetch('/includes/ajax/notification-action.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: notificationId, action: 'read' }),
    });
    const data = await res.json();
    if (data.success) {
      item.classList.remove('unread');
    } else {
      window.showToast('Could not mark notification as read.', 'error');
    }
  } catch {
    window.showToast('Network error. Please try again.', 'error');
  }
}

// Mark all notifications read
async function markAllNotificationsRead() {
  const res = await fetch('/includes/ajax/mark-notifications-read.php', { method: 'POST' });
  const data = await res.json();
  if (data.success) {
    document.querySelectorAll('.notification-item.unread').forEach(el => {
      el.classList.remove('unread');
    });
    window.epAnnounce('All notifications marked as read.');
  }
}
```

**Mobile behaviour:** Items reflow to a stacked layout (`flex-direction: column`) below 768px, with action buttons right-aligned at the bottom.

---

### 3.10 Feedback Alerts

Use inline alerts for page-level operation feedback (not notifications).

```html
<!-- Success — auto-dismisses after 3 seconds via employee-portal-feedback.js -->
<div class="alert-ep-success" role="status">
  Changes saved successfully.
</div>

<!-- Error — persists until user dismisses -->
<div class="alert-ep-error" role="alert">
  Failed to save. Check your network connection and try again.
  <button class="alert-ep-dismiss" aria-label="Dismiss error message">×</button>
</div>
```

**Rules:**
- `.alert-ep-success` uses `role="status"` (polite announcement) — for confirmations that do not require immediate action.
- `.alert-ep-error` uses `role="alert"` (assertive announcement) — for errors that need user attention.
- Place alerts at the top of the `<form>` or `<main>` content area, not inside individual form groups.
- The feedback JS (`employee-portal-feedback.js`) auto-dismisses `.alert-ep-success` and `.alert-ep-error.alert-ep-dismiss` automatically — no extra JavaScript needed.

---

### 3.11 Toast Notifications (JavaScript)

Toasts appear fixed top-right (desktop) or full-width bottom (mobile) and are managed by `employee-portal-feedback.js`.

```javascript
// Success — auto-dismisses after 3 seconds
window.showToast('Changes saved successfully.', 'success');

// Error — persistent until manually dismissed (duration = 0)
window.showToast('Network error. Please try again.', 'error');

// Warning — auto-dismisses after 3 seconds
window.showToast('Evaluation deadline in 2 days.', 'warning');

// Info — auto-dismisses after 3 seconds
window.showToast('New notification received.', 'info');

// Custom duration (milliseconds)
window.showToast('Draft saved.', 'success', 5000);

// Persistent (duration = 0)
window.showToast('Session expiring soon.', 'warning', 0);
```

**Function signature:** `showToast(message, type, duration)`

| Parameter | Type | Default | Notes |
|---|---|---|---|
| `message` | string | — | Plain text only — no HTML |
| `type` | string | `'success'` | `'success'` / `'error'` / `'warning'` / `'info'` |
| `duration` | number (ms) | 3000 (non-error), 0 (error) | `0` = persistent |

The function also announces the message to screen readers via an ARIA live region (`window.epAnnounce`).

---

### 3.12 Loading States

#### Button loading (on form submit)

Applied automatically by `employee-portal-feedback.js` to all submit buttons when a form is submitted. To apply manually:

```javascript
const btn = document.querySelector('#mySubmitBtn');
btn.classList.add('is-loading');
btn.disabled = true;
btn.setAttribute('aria-busy', 'true');

// Remove after response:
btn.classList.remove('is-loading');
btn.disabled = false;
btn.removeAttribute('aria-busy');
```

#### Inline spinner

```html
<span class="loading-spinner" aria-hidden="true"></span>
Loading data...
```

For use inside content areas while fetching data via AJAX.

#### Skeleton screen (while cards load)

```html
<div class="skeleton-card">
  <div class="skeleton-circle"></div>
  <div style="flex: 1;">
    <div class="skeleton-line xl"></div>
    <div class="skeleton-line sm"></div>
  </div>
</div>
```

Skeleton size modifiers: `.xl` (large value), `.lg` (heading), `.full` (full width), `.half` (50% width), `.sm` (caption).

#### Page-level loading overlay

```html
<div class="loading-overlay" role="status" aria-label="Loading, please wait">
  <div class="loading-overlay-spinner"></div>
</div>
```

Remove from DOM once content is ready.

---

## 4. Maintenance Guide

### 4.1 Responsive Breakpoint Strategy

The portal uses a **single breakpoint at 768px** — no intermediate tablet breakpoints.

| Viewport | Layout applied | Navigation | Columns |
|---|---|---|---|
| < 768px | Mobile | Bottom nav (fixed) | Single column |
| ≥ 768px | Desktop | Left sidebar (fixed 260px) | Multi-column grid |

**Writing responsive CSS:**

```css
/* Mobile-first: base styles (no media query) apply to < 768px */
.my-component {
  padding: var(--space-md);
  flex-direction: column;
}

/* Desktop enhancement at 768px and above */
@media (min-width: 768px) {
  .my-component {
    padding: var(--space-lg);
    flex-direction: row;
  }
}

/* Hiding/showing elements by breakpoint */
/* Hide on desktop, show on mobile: */
.d-md-none { }          /* Bootstrap utility */

/* Hide on mobile, show on desktop: */
.d-none.d-md-block { }  /* Bootstrap utilities */
```

**Rules:**
- Always write the mobile base style first, then the `min-width: 768px` override.
- Use Bootstrap's responsive utility classes (`d-none`, `d-md-block`, `col-12`, `col-md-6`) before writing custom media queries.
- Never add a new breakpoint (e.g. 480px, 1200px) without a compelling reason and team discussion.
- Test every layout change at exactly 767px and 768px — the transition point.

---

### 4.2 WCAG AAA Accessibility Requirements

The portal targets **WCAG 2.1 Level AAA** — the highest accessibility standard.

#### Color contrast

| Text / element type | Minimum ratio | Variable examples |
|---|---|---|
| Normal text (< 18pt / < 14pt bold) | **7:1** | `--color-text-primary` 15.8:1, `--color-text-secondary` 10.2:1, `--color-text-muted` 7.1:1 |
| Large text (≥ 18pt / ≥ 14pt bold) | **4.5:1** | All heading sizes qualify |
| Interactive component boundaries | **3:1** | Button borders, input borders |
| Icons and graphical objects | **3:1** | All status icon colors |

All existing variables in `employee-portal-variables.css` meet or exceed these thresholds. Never add new colors without first verifying with https://webaim.org/resources/contrastchecker/.

#### Touch targets

- Minimum: **48×48 CSS pixels** for all interactive elements.
- Icon-only buttons: **56×56 CSS pixels** minimum.
- Minimum gap between adjacent targets: **8px**.
- Apply `min-height: 48px` on all `<input>`, `<select>`, `<button>`, and `<a>` elements.

#### Semantic HTML

Always use the correct element for the job:

| Purpose | Correct element |
|---|---|
| Page structure | `<main>`, `<header>`, `<footer>`, `<aside>`, `<nav>` |
| Navigation | `<nav>` with `aria-label` |
| Content grouping | `<section>` with heading, `<article>` for self-contained content |
| Button that triggers action | `<button>` |
| Link that navigates | `<a href="...">` |
| Radio/checkbox group | `<fieldset>` + `<legend>` |
| Data table | `<table>` with `<th scope="col/row">` |

#### ARIA requirements

- Icon-only interactive elements must have `aria-label`.
- Decorative icons must have `aria-hidden="true"`.
- Dynamic content regions (notifications, error messages) must have `role="alert"` or `aria-live="polite"`.
- Active navigation items must have `aria-current="page"` (applied automatically by `employee-portal-feedback.js`).
- Multi-step progress must use `aria-current="step"` on the active step.
- Loading indicators must have `aria-busy="true"` on the parent container while loading.

#### Keyboard navigation

- Tab order must follow visual reading order (top-left to bottom-right).
- All interactive elements must be reachable with Tab and activatable with Enter or Space.
- Modals and dropdowns must trap focus while open and restore focus on close.
- Skip navigation link (`<a class="skip-navigation" href="#main-content">`) is provided in the header.

#### Color not as sole indicator

Never use color alone to communicate state. Always pair color with:
- An icon (`aria-hidden="true"` on the icon; label text carries the meaning), or
- A text label (e.g. "Completed", "Pending"), or
- A pattern or shape distinction.

---

### 4.3 Performance Optimization

#### Critical CSS

`employee-portal-critical.css` contains only the above-the-fold styles (navigation, page header, first stat card row). It is inlined in `<head>` to eliminate render-blocking for the initial paint. **Do not add non-critical rules here.** The threshold is: if the user cannot see it without scrolling on a 375px viewport, it belongs in the component CSS file, not critical CSS.

#### Images

```html
<!-- Lazy-load all images below the fold -->
<img src="photo.jpg" alt="Employee profile photo" loading="lazy" width="64" height="64">

<!-- For images that must load immediately (above-the-fold), omit loading="lazy" -->
<img src="logo.png" alt="Raquel Pawnshop logo" width="120" height="40">
```

The `employee-portal-feedback.js` also implements an `IntersectionObserver` polyfill for `data-src` lazy loading on browsers without native support:

```html
<img data-src="large-photo.jpg" src="placeholder.jpg" alt="..." loading="lazy">
```

#### JavaScript

```html
<!-- Non-critical scripts: defer until after DOM parsing -->
<script src="/assets/js/auto-save.js" defer></script>
<script src="/assets/js/form-validation.js" defer></script>
<script src="/assets/js/employee-portal-feedback.js" defer></script>

<!-- Bootstrap bundle: can use defer if no components needed before DOMContentLoaded -->
<script src="bootstrap.bundle.min.js" defer></script>
```

#### CSS delivery

- The modular CSS file approach keeps each file small and focused.
- `employee-portal-critical.css` is inlined in `<head>` to unblock the first paint.
- All other CSS files load via `<link>` tags — browsers fetch these in parallel.
- Avoid `@import` inside CSS files as it serializes requests.

#### Lighthouse targets

| Metric | Target |
|---|---|
| Accessibility score | ≥ 95 |
| Performance (mobile) | ≥ 75 |
| First Contentful Paint | ≤ 2s on 3G |
| Time to Interactive | ≤ 4s on 3G |

Run a Lighthouse audit in Chrome DevTools (Incognito mode, Mobile device emulation) after every significant change.

---

### 4.4 Testing Checklist

Use this checklist before merging any PR that touches the employee portal.

#### Responsive layout

- [ ] **320px** — No horizontal scroll; single-column layout; bottom nav visible; stat cards stack vertically
- [ ] **375px** — Cards are full width; forms span full width; text does not overflow
- [ ] **767px** — Confirm mobile layout is still active (sidebar hidden, bottom nav visible)
- [ ] **768px** — Sidebar appears; bottom nav hides; 2-column grids activate; content-card header wraps correctly
- [ ] **1024px** — Multi-column grids display; sidebar fixed; no layout breaks
- [ ] Rotate device/emulator to landscape — content reflows without data loss within 300ms

#### Accessibility

- [ ] Tab through all interactive elements — focus order is logical left-to-right, top-to-bottom
- [ ] All icon-only buttons have visible `aria-label` in DevTools accessibility tree
- [ ] All form inputs have associated `<label>` elements (check with DevTools or axe extension)
- [ ] Activate screen reader (NVDA/JAWS on Windows, VoiceOver on macOS) — navigate by headings, forms, and landmarks
- [ ] All images have descriptive `alt` text; decorative images have `alt=""`
- [ ] Dynamic content changes (toasts, validation errors, auto-save confirmations) are announced by screen reader
- [ ] Skip navigation link is visible on first Tab keypress and navigates to `#main-content`
- [ ] No content is conveyed by color alone — verify with a grayscale browser extension

#### Color contrast

- [ ] All new text colors pass WCAG AAA (7:1 normal / 4.5:1 large) — check at https://webaim.org/resources/contrastchecker/
- [ ] All new UI component borders pass 3:1 against adjacent background
- [ ] All new icons pass 3:1 against their background

#### Touch targets

- [ ] All buttons, links, inputs: minimum 48×48px (measure with DevTools box model)
- [ ] Icon-only buttons: minimum 56×56px
- [ ] Adjacent targets have at least 8px gap

#### Forms

- [ ] Required fields show asterisk indicator + `aria-required="true"`
- [ ] Validation errors appear below the relevant field within 500ms of blur
- [ ] Auto-save triggers 2 seconds after last keystroke; confirmation toast appears
- [ ] Submit button shows loading state and cannot be double-submitted
- [ ] Error state is announced to screen readers via `role="alert"`

#### JavaScript

- [ ] `showToast('Test', 'success')` — toast appears top-right, auto-dismisses after 3s
- [ ] `showToast('Test', 'error')` — toast appears and stays until dismissed
- [ ] Auto-save on a `data-autosave` form — check network tab for the AJAX call
- [ ] Form validation on `data-validate` form — check errors clear on correction

#### Performance

- [ ] Run Lighthouse audit in Incognito, Mobile preset — Accessibility ≥ 95
- [ ] All `<img>` below the fold have `loading="lazy"`
- [ ] Non-critical `<script>` tags use `defer`
- [ ] No new `@import` statements inside CSS files

#### Cross-browser

- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest) — especially test CSS custom properties and `gap` in flex
- [ ] Chrome on Android (DevTools remote debug or physical device)
- [ ] Safari on iOS (physical device or Xcode Simulator)

---

### 4.5 File Inclusion Order

The load order in `includes/header.php` (for Employee role):

```
1.  Bootstrap 5 CDN CSS
2.  FontAwesome CDN CSS
3.  Google Fonts (Inter, Outfit) — or system font stack fallback
4.  style.css                              (existing shared styles)
5.  employee-portal-critical.css           (inlined in <style> tag for critical path)
6.  employee-portal-variables.css          (MUST come before all other portal CSS)
7.  employee-portal-layout.css
8.  employee-portal-navigation.css
9.  employee-portal-cards.css
10. employee-portal-forms.css
11. employee-portal-buttons.css
12. employee-portal-ratings.css
13. employee-portal-progress.css
14. employee-portal-notifications.css
15. employee-portal-feedback.css
```

JavaScript in `includes/footer.php` (bottom of `<body>`, deferred):

```
1. bootstrap.bundle.min.js
2. zebra-stripe.js
3. main.js
4. auto-save.js                  (defer)
5. form-validation.js            (defer)
6. employee-portal-feedback.js   (defer)
```

When adding a new CSS or JS file, insert it after the last item in its group, maintaining the layering order (variables → layout → components → utilities).
