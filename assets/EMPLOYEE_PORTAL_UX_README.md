# Employee Portal UX Revamp — Implementation Guide

## Overview

This guide documents the implementation of the comprehensive UI/UX redesign for the Raquel Pawnshop HRIS employee portal. The redesign focuses on:

- **Mobile-first responsive design** (320px – 2560px, single 768px breakpoint)
- **WCAG AAA accessibility compliance** (7:1 contrast for normal text, 4.5:1 for large text)
- **Enhanced typography and readability** (minimum 1.1rem base font size)
- **Touch-friendly interactions** (48×48px minimum touch targets, 56×56px icon-only)
- **Simplified navigation** (bottom nav on mobile, sidebar on desktop)
- **Reduced cognitive load** (card-based layouts, clear visual hierarchy, max 7 items per section)

For the full developer reference including CSS variable usage, naming conventions, and maintenance procedures, see [`docs/employee-portal-developer-guide.md`](../docs/employee-portal-developer-guide.md).

---

## Quick Start

### 1. Include CSS Files

The portal CSS is loaded via `includes/header.php` for Employee role pages. The correct load order is:

```html
<!-- 1. Bootstrap 5 (required) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- 2. FontAwesome (icons) -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<!-- 3. Design tokens — MUST be first of the portal CSS files -->
<link href="/assets/css/employee-portal-variables.css" rel="stylesheet">

<!-- 4. Component files (order matters) -->
<link href="/assets/css/employee-portal-layout.css" rel="stylesheet">
<link href="/assets/css/employee-portal-navigation.css" rel="stylesheet">
<link href="/assets/css/employee-portal-cards.css" rel="stylesheet">
<link href="/assets/css/employee-portal-forms.css" rel="stylesheet">
<link href="/assets/css/employee-portal-buttons.css" rel="stylesheet">
<link href="/assets/css/employee-portal-ratings.css" rel="stylesheet">
<link href="/assets/css/employee-portal-progress.css" rel="stylesheet">
<link href="/assets/css/employee-portal-notifications.css" rel="stylesheet">
<link href="/assets/css/employee-portal-feedback.css" rel="stylesheet">
```

### 2. Include JavaScript Files

Add before the closing `</body>` tag, with `defer`:

```html
<script src="/assets/js/bootstrap.bundle.min.js" defer></script>
<script src="/assets/js/main.js" defer></script>
<script src="/assets/js/auto-save.js" defer></script>
<script src="/assets/js/form-validation.js" defer></script>
<script src="/assets/js/employee-portal-feedback.js" defer></script>
```

### 3. Page Structure

```html
<body>
  <!-- Skip navigation (accessibility — keyboard users) -->
  <a href="#main-content" class="skip-navigation">Skip to main content</a>

  <!-- Desktop sidebar (hidden on mobile) -->
  <aside class="sidebar d-none d-md-block" aria-label="Main navigation">
    <!-- See Navigation section below -->
  </aside>

  <!-- Main content -->
  <main id="main-content" class="main-content">
    <!-- Your page content -->
  </main>

  <!-- Mobile bottom navigation (hidden on desktop) -->
  <nav class="bottom-nav d-md-none" aria-label="Primary navigation">
    <!-- See Navigation section below -->
  </nav>
</body>
```

---

## Navigation Components

### Desktop Sidebar

```html
<aside class="sidebar d-none d-md-block" aria-label="Main navigation">
  <div class="sidebar-header">
    <img src="/assets/img/logo/logo.png" alt="Raquel Pawnshop logo" class="sidebar-logo"
         width="120" height="40">
    <h2>Employee Portal</h2>
  </div>

  <nav class="sidebar-nav">
    <a href="dashboard.php" class="sidebar-link active" aria-current="page">
      <i class="fas fa-home" aria-hidden="true"></i>
      <span>My Dashboard</span>
    </a>
    <a href="my-employment.php" class="sidebar-link">
      <i class="fas fa-briefcase" aria-hidden="true"></i>
      <span>My Employment</span>
    </a>
    <a href="self-rating.php" class="sidebar-link">
      <i class="fas fa-clipboard-check" aria-hidden="true"></i>
      <span>My Evaluations</span>
    </a>
    <a href="notifications.php" class="sidebar-link">
      <i class="fas fa-bell" aria-hidden="true"></i>
      <span>Notifications</span>
      <!-- Badge: only render when count > 0 -->
      <span class="badge-dot" aria-label="3 unread notifications">3</span>
    </a>
    <a href="profile-settings.php" class="sidebar-link">
      <i class="fas fa-user" aria-hidden="true"></i>
      <span>My Profile</span>
    </a>
  </nav>
</aside>
```

`aria-current="page"` is also set automatically by `employee-portal-feedback.js` based on the current URL — but always set it server-side too for initial render.

### Mobile Bottom Navigation

```html
<nav class="bottom-nav d-md-none" aria-label="Primary navigation">
  <a href="dashboard.php" class="bottom-nav-item active" aria-current="page">
    <i class="fas fa-home" aria-hidden="true"></i>
    <span>Dashboard</span>
  </a>
  <a href="self-rating.php" class="bottom-nav-item">
    <i class="fas fa-clipboard-check" aria-hidden="true"></i>
    <span>Evaluations</span>
  </a>
  <a href="notifications.php" class="bottom-nav-item">
    <i class="fas fa-bell" aria-hidden="true"></i>
    <span>Notifications</span>
    <span class="badge-dot" aria-label="3 unread notifications">3</span>
  </a>
  <a href="profile-settings.php" class="bottom-nav-item">
    <i class="fas fa-user" aria-hidden="true"></i>
    <span>Profile</span>
  </a>
</nav>
```

Bottom nav is fixed to the bottom of the viewport on mobile. `<body>` has `padding-bottom` applied by `employee-portal-layout.css` to prevent content being obscured.

### Breadcrumb Navigation

```html
<nav aria-label="Breadcrumb" class="breadcrumb-nav">
  <ol class="breadcrumb">
    <li class="breadcrumb-item">
      <a href="dashboard.php">Dashboard</a>
    </li>
    <li class="breadcrumb-item">
      <a href="self-rating.php">My Evaluations</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
      Self-Rating Form
    </li>
  </ol>
</nav>
```

Include breadcrumbs on all pages except the dashboard itself.

---

## Component Quick Reference

| Component | Root class | Variants / modifiers | Detail section |
|---|---|---|---|
| **Stat Card** | `.stat-card` | Icon color: `stat-icon-success`, `stat-icon-warning`, `stat-icon-info`, `stat-icon-danger` | [Card Components](#card-components) |
| **Content Card** | `.content-card` | `.content-card-header` (optional), `.content-card-title`, `.content-card-body` | [Card Components](#card-components) |
| **Status Badge** | `.status-badge` | `.status-success`, `.status-warning`, `.status-danger`, `.status-info` | [Card Components](#card-components) |
| **Button** | `.btn` | Themes: `.btn-primary`, `.btn-outline-primary`, `.btn-danger`, `.btn-success`; Sizes: `.btn-sm`, `.btn-lg`; Layout: `.btn-block`, `.btn-mobile-block`; State: `.is-loading` | [Button Components](#button-components) |
| **Form Group** | `.form-group` | `.form-control`, `.form-select`, `.form-label`, `.form-helper`, `.form-error`; Validation: `.is-invalid`, `.is-valid`; Error visibility: `.is-visible` on `.form-error` | [Form Components](#form-components) |
| **Rating Item** | `.rating-item` | `.rating-header`, `.rating-title`, `.rating-description`, `.rating-scale` (`role="radiogroup"`), `.rating-option`, `.rating-label`, `.rating-number`, `.rating-text` | [Rating Components](#rating-components) |
| **Rating Section** | `.rating-section` | `.rating-section-title`; wraps one or more `.rating-item` | [Rating Components](#rating-components) |
| **Progress Indicator** | `.progress-indicator` | Steps: `.progress-step`, `.progress-step-number`, `.progress-step-label`, `.progress-line`; States: `.completed`, `.active` (+ `aria-current="step"`) | [Progress Indicator](#progress-indicator) |
| **Progress Bar** | `.progress-bar-container` | `.progress-bar-label`, `.progress-bar` (`role="progressbar"`), `.progress-bar-fill` | [Progress Indicator](#progress-indicator) |
| **Notification Item** | `.notification-item` | State: `.unread`; Icon variants: `notification-icon-success/warning/info/danger`; Structure: `.notification-content`, `.notification-title`, `.notification-message`, `.notification-time`, `.notification-actions` | [Notification Components](#notification-components) |
| **Feedback Alert** | `.alert-ep-success` / `.alert-ep-error` | `.alert-ep-dismiss` button for persistent errors | [Notification Components](#notification-components) |
| **Loading** | `.is-loading` (on `.btn`) | Inline: `.loading-spinner`; Overlay: `.loading-overlay`; Skeleton: `.skeleton-card`, `.skeleton-line` + `.xl/.lg/.full/.half/.sm` | [Loading States](#loading-states) |

---

## Card Components

### Stat Card

Used on the dashboard to display a single key metric.

```html
<div class="stat-card">
  <div class="stat-icon stat-icon-success" aria-hidden="true">
    <i class="fas fa-check-circle"></i>
  </div>
  <div class="stat-content">
    <h3 class="stat-value">8</h3>
    <p class="stat-label">Completed Evaluations</p>
  </div>
</div>
```

**Icon variants** — always convey meaning with both color and icon shape, never color alone:

| Class | Color | Semantic meaning |
|---|---|---|
| `.stat-icon-success` | Green | Positive metric, completed |
| `.stat-icon-warning` | Amber | Needs attention, pending |
| `.stat-icon-info` | Teal | Informational count |
| `.stat-icon-danger` | Red | Critical, overdue, error |

Dashboard grid example:

```html
<div class="row g-3 g-md-4">
  <div class="col-12 col-md-6 col-xl-3">
    <div class="stat-card">
      <div class="stat-icon stat-icon-success" aria-hidden="true">
        <i class="fas fa-check-circle"></i>
      </div>
      <div class="stat-content">
        <h3 class="stat-value">8</h3>
        <p class="stat-label">Completed</p>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <div class="stat-card">
      <div class="stat-icon stat-icon-warning" aria-hidden="true">
        <i class="fas fa-clock"></i>
      </div>
      <div class="stat-content">
        <h3 class="stat-value">2</h3>
        <p class="stat-label">Pending</p>
      </div>
    </div>
  </div>
</div>
```

### Content Card

General-purpose container for grouped content.

```html
<div class="content-card">
  <div class="content-card-header">
    <h2 class="content-card-title">
      <i class="fas fa-bell" aria-hidden="true"></i>
      Recent Notifications
    </h2>
    <button class="btn btn-sm btn-outline-primary">Mark All Read</button>
  </div>
  <div class="content-card-body">
    <!-- Content here -->
  </div>
</div>
```

The `content-card-header` is optional — omit it when no title or action is needed.

### Status Badges

Always combine color with an icon and text label:

```html
<!-- Semantic status badges -->
<span class="status-badge status-success">
  <i class="fas fa-check-circle" aria-hidden="true"></i> Completed
</span>

<span class="status-badge status-warning">
  <i class="fas fa-clock" aria-hidden="true"></i> Pending
</span>

<span class="status-badge status-danger">
  <i class="fas fa-exclamation-circle" aria-hidden="true"></i> Overdue
</span>

<span class="status-badge status-info">
  <i class="fas fa-info-circle" aria-hidden="true"></i> In Review
</span>
```

---

## Form Components

### Text Input

```html
<div class="form-group">
  <label for="firstName" class="form-label">
    First Name
    <span class="required-indicator" aria-label="required">*</span>
  </label>
  <input
    type="text"
    id="firstName"
    name="first_name"
    class="form-control"
    placeholder="Enter your first name"
    required
    aria-required="true"
    aria-describedby="firstNameHelper"
  >
  <small id="firstNameHelper" class="form-helper">
    Your legal first name as it appears on official documents.
  </small>
  <div class="form-error" id="firstNameError" role="alert" aria-live="polite"></div>
</div>
```

### Select / Dropdown

```html
<div class="form-group">
  <label for="department" class="form-label">Department</label>
  <select id="department" name="department" class="form-select">
    <option value="">-- Select Department --</option>
    <option value="operations">Operations</option>
    <option value="admin">Administration</option>
    <option value="finance">Finance</option>
  </select>
  <div class="form-error" role="alert" aria-live="polite"></div>
</div>
```

### Textarea with Character Counter

```html
<div class="form-group">
  <label for="comments" class="form-label">Additional Comments</label>
  <textarea
    id="comments"
    name="comments"
    class="form-control"
    rows="4"
    maxlength="500"
    aria-describedby="commentsCounter"
  ></textarea>
  <div id="commentsCounter" class="char-counter">
    <span>500</span> characters remaining
  </div>
  <div class="form-error" role="alert" aria-live="polite"></div>
</div>
```

### Validation States

Applied by `form-validation.js` on blur/submit:

```html
<!-- Invalid field -->
<input type="email" class="form-control is-invalid" id="email" value="not-an-email">
<div class="form-error is-visible" role="alert">
  Please enter a valid email address.
</div>

<!-- Valid field -->
<input type="email" class="form-control is-valid" id="email" value="user@example.com">
```

### Radio Buttons

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

### Checkboxes

```html
<fieldset class="form-group">
  <legend class="form-label">Skills</legend>
  <div class="checkbox-group">
    <div class="checkbox-option">
      <input type="checkbox" id="skill1" name="skills[]" value="communication" class="checkbox-input">
      <label for="skill1" class="checkbox-label">Communication</label>
    </div>
    <div class="checkbox-option">
      <input type="checkbox" id="skill2" name="skills[]" value="leadership" class="checkbox-input">
      <label for="skill2" class="checkbox-label">Leadership</label>
    </div>
    <div class="checkbox-option">
      <input type="checkbox" id="skill3" name="skills[]" value="teamwork" class="checkbox-input">
      <label for="skill3" class="checkbox-label">Teamwork</label>
    </div>
  </div>
</fieldset>
```

### Auto-Save Form

```html
<form id="evaluationForm"
      data-autosave="evaluationForm"
      data-autosave-endpoint="/employee/ajax/save-pds-section.php">
  <!-- fields -->
</form>
```

Auto-save triggers 2 seconds after the last keystroke. Server must return:
```json
{ "success": true }
```
or
```json
{ "success": false, "message": "Descriptive error" }
```

### Form Validation

Add `data-validate` to enable client-side validation:

```html
<form data-validate>
  <div class="form-group">
    <label for="email" class="form-label">
      Email <span class="required-indicator" aria-label="required">*</span>
    </label>
    <input
      type="email"
      id="email"
      name="email"
      class="form-control"
      required
      maxlength="100"
      pattern="[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}$"
      data-pattern-message="Please enter a valid email address"
    >
    <div class="form-error" role="alert" aria-live="polite"></div>
  </div>
</form>
```

**Supported validation attributes:**

| Attribute | Validates |
|---|---|
| `required` | Field is not empty |
| `type="email"` | Valid email format |
| `type="tel"` | Phone number format |
| `type="number"` | Numeric value |
| `min` / `max` | Numeric range |
| `minlength` / `maxlength` | Character count |
| `pattern` | Custom regex |
| `data-pattern-message` | Custom error text for pattern |

**JavaScript API:**

```javascript
// Manually validate a form — returns true if valid
const isValid = window.validateForm('#myForm');

// Clear all validation errors
window.clearFormValidation('#myForm');
```

---

## Button Components

```html
<!-- Primary action -->
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

<!-- Confirmation / positive -->
<button type="button" class="btn btn-success">
  <i class="fas fa-check" aria-hidden="true"></i>
  Confirm Submission
</button>

<!-- Small — for card header actions -->
<button class="btn btn-sm btn-outline-primary">View All</button>

<!-- Large — primary CTA, evaluation submit -->
<button class="btn btn-lg btn-primary">Submit Evaluation</button>

<!-- Full-width on mobile only -->
<button class="btn btn-primary btn-mobile-block">Continue</button>

<!-- Full-width always -->
<button class="btn btn-primary btn-block">Next Step</button>

<!-- Loading state — applied via JS on form submit -->
<button class="btn btn-primary is-loading" aria-busy="true" disabled>
  Saving...
</button>
```

**Size reference:**

| Class | min-height | Typical use |
|---|---|---|
| `.btn-sm` | 40px | Card header secondary actions |
| _(default)_ | 48px | Standard inline actions |
| `.btn-lg` | 56px | Page-level primary CTAs, form submit |

**Loading state JavaScript:**

```javascript
function setButtonLoading(btn, loading) {
  if (loading) {
    btn.classList.add('is-loading');
    btn.disabled = true;
    btn.setAttribute('aria-busy', 'true');
  } else {
    btn.classList.remove('is-loading');
    btn.disabled = false;
    btn.removeAttribute('aria-busy');
  }
}
```

---

## Rating Components

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
</div>
```

**Interactive states:**
- Default: grey outline, muted number circle
- Hover / focus: slides 4px right, primary green border
- Selected: green tint background, filled circle with animated ✓ checkmark

Use a separate `.rating-section` for KRA and behavior categories.

**Accessible variant — add `role="radiogroup"` + `aria-labelledby` when multiple rating sections appear on one page**, so screen readers announce the criterion name before the choices:

```html
<div class="rating-item">
  <div class="rating-header">
    <h3 class="rating-title" id="kra1-label">Customer Service Excellence</h3>
    <p class="rating-description">Demonstrates exceptional service quality.</p>
  </div>
  <div class="rating-scale" role="radiogroup" aria-labelledby="kra1-label">
    <div class="rating-option">
      <input type="radio" id="kra1_1" name="kra1" value="1" class="rating-input">
      <label for="kra1_1" class="rating-label">
        <span class="rating-number">1</span>
        <span class="rating-text">Needs Improvement</span>
      </label>
    </div>
    <!-- options 2–5 follow the same pattern -->
  </div>
</div>
```

Each `id` on `rating-title` must be unique on the page (e.g. `kra1-label`, `kra2-label`, `behavior1-label`).

---

## Progress Indicator

For multi-step forms with 2–5 steps.

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
    <div class="progress-step-number" aria-label="Step 2, current">2</div>
    <div class="progress-step-label">Education</div>
  </div>

  <div class="progress-line" aria-hidden="true"></div>

  <div class="progress-step" role="listitem">
    <div class="progress-step-number" aria-label="Step 3, upcoming">3</div>
    <div class="progress-step-label">Work History</div>
  </div>

  <div class="progress-line" aria-hidden="true"></div>

  <div class="progress-step" role="listitem">
    <div class="progress-step-number" aria-label="Step 4, upcoming">4</div>
    <div class="progress-step-label">Review &amp; Submit</div>
  </div>

</div>
```

**Step classes:**

| `.progress-step` class | `.progress-line` class | Appearance |
|---|---|---|
| `.completed` | `.completed` | Green filled circle (✓), green line |
| `.active` | — | Primary filled circle (pulse), grey line |
| _(none)_ | _(none)_ | Grey outlined circle, grey line |

**Updating step state with JavaScript:**

```javascript
/**
 * Advance the progress indicator to the given step number (1-based).
 * Call this whenever the user navigates between form sections.
 */
function goToStep(stepNumber) {
  const steps = document.querySelectorAll('.progress-step');
  const lines = document.querySelectorAll('.progress-line');

  steps.forEach((step, idx) => {
    step.classList.remove('completed', 'active');
    step.removeAttribute('aria-current');

    if (idx < stepNumber - 1) {
      step.classList.add('completed');
      const numEl = step.querySelector('.progress-step-number');
      if (numEl) numEl.setAttribute('aria-label', `Step ${idx + 1} complete`);
    } else if (idx === stepNumber - 1) {
      step.classList.add('active');
      step.setAttribute('aria-current', 'step');
      const numEl = step.querySelector('.progress-step-number');
      if (numEl) numEl.setAttribute('aria-label', `Step ${idx + 1}, current step`);
    }
  });

  lines.forEach((line, idx) => {
    line.classList.toggle('completed', idx < stepNumber - 1);
  });
}

// Usage: advance to step 3
goToStep(3);
```

**Progress bar** (for > 5 steps or percentage-based progress):

```html
<div class="progress-bar-container">
  <div class="progress-bar-label">
    <span>Evaluation Progress</span>
    <span id="progressPercent">60%</span>
  </div>
  <div class="progress-bar"
       role="progressbar"
       aria-valuenow="60"
       aria-valuemin="0"
       aria-valuemax="100"
       aria-labelledby="progressPercent">
    <div class="progress-bar-fill" style="width: 60%;"></div>
  </div>
</div>
```

---

## Notification Components

### Notification List

```html
<div class="notification-list">

  <!-- Unread item -->
  <div class="notification-item unread">
    <div class="notification-icon notification-icon-info" aria-hidden="true">
      <i class="fas fa-info-circle"></i>
    </div>
    <div class="notification-content">
      <h3 class="notification-title">Evaluation Period Started</h3>
      <p class="notification-message">
        The Q2 2024 evaluation period has begun. Complete your self-rating by June 30.
      </p>
      <span class="notification-time">2 hours ago</span>
    </div>
    <div class="notification-actions">
      <button class="notification-action-btn" aria-label="Mark notification as read">
        <i class="fas fa-check" aria-hidden="true"></i>
      </button>
    </div>
  </div>

  <!-- Read item -->
  <div class="notification-item">
    <div class="notification-icon notification-icon-success" aria-hidden="true">
      <i class="fas fa-check-circle"></i>
    </div>
    <div class="notification-content">
      <h3 class="notification-title">Self-Rating Submitted</h3>
      <p class="notification-message">Your Q1 2024 self-rating has been recorded successfully.</p>
      <span class="notification-time">3 days ago</span>
    </div>
    <div class="notification-actions">
      <button class="notification-action-btn" aria-label="View notification">
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
| `.notification-icon-info` | Teal | Announcements, informational |
| `.notification-icon-danger` | Red | Overdue items, urgent actions |

**Marking a notification as read (JavaScript):**

```javascript
// Mark a single item read — removes .unread, calls server
async function markNotificationRead(item, notificationId) {
  const res = await fetch('/includes/ajax/notification-action.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id: notificationId, action: 'read' }),
  });
  const data = await res.json();
  if (data.success) {
    item.classList.remove('unread');
  }
}

// Mark all read
async function markAllNotificationsRead() {
  const res = await fetch('/includes/ajax/mark-notifications-read.php', { method: 'POST' });
  const data = await res.json();
  if (data.success) {
    document.querySelectorAll('.notification-item.unread').forEach(el => el.classList.remove('unread'));
    window.epAnnounce('All notifications marked as read.');
  }
}
```

### Inline Feedback Alerts

```html
<!-- Success — auto-dismisses after 3 seconds -->
<div class="alert-ep-success" role="status">
  Your changes have been saved successfully.
</div>

<!-- Error — persistent until dismissed -->
<div class="alert-ep-error" role="alert">
  Failed to save. Check your connection and try again.
  <button class="alert-ep-dismiss" aria-label="Dismiss error message">×</button>
</div>
```

### Toast Notifications (JavaScript)

```javascript
// Success — auto-dismisses after 3 seconds
window.showToast('Changes saved successfully.', 'success');

// Error — persistent until dismissed
window.showToast('Network error. Please try again.', 'error');

// Warning — auto-dismisses after 3 seconds
window.showToast('Evaluation deadline in 2 days.', 'warning');

// Info — auto-dismisses after 3 seconds
window.showToast('New notification received.', 'info');

// Custom duration (milliseconds)
window.showToast('Draft saved.', 'success', 5000);

// Persistent (duration = 0)
window.showToast('Your session is about to expire.', 'warning', 0);
```

`showToast` signature: `showToast(message, type, duration)` — see the developer guide for full parameter reference.

---

## Loading States

### Skeleton Screens (while content loads)

```html
<!-- Skeleton stat card -->
<div class="skeleton-card">
  <div class="skeleton-circle"></div>
  <div style="flex: 1;">
    <div class="skeleton-line xl"></div>
    <div class="skeleton-line sm"></div>
  </div>
</div>

<!-- Skeleton text block -->
<div>
  <div class="skeleton-line full"></div>
  <div class="skeleton-line half"></div>
  <div class="skeleton-line full"></div>
</div>
```

Skeleton size classes: `.xl` (large value), `.lg` (heading-height), `.full` (100% width), `.half` (50%), `.sm` (caption).

### Inline Spinner

```html
<span class="loading-spinner" aria-hidden="true"></span>
Loading results...
```

### Page-Level Overlay

```html
<div class="loading-overlay" role="status" aria-label="Loading, please wait">
  <div class="loading-overlay-spinner"></div>
</div>
```

Remove the overlay from the DOM once content is ready.

---

## CSS Variables Quick Reference

All design tokens live in `assets/css/employee-portal-variables.css`. Use variables — never hardcode hex values or pixel sizes in component files.

### Brand Colors

```css
--color-primary: #082E06;          /* Dark green */
--color-primary-light: #CBA135;    /* Gold accent */
--color-primary-dark: #041D03;
--color-accent: #E1232A;           /* Alert red */
```

### Text Colors (WCAG AAA)

```css
--color-text-primary: #1C271B;     /* 15.8:1 contrast on white */
--color-text-secondary: #3D4D3A;   /* 10.2:1 contrast */
--color-text-muted: #5E6B5C;       /* 7.1:1 contrast */
```

### Status Colors (WCAG AAA)

```css
--color-success: #0F6B2E;          /* 7.5:1 contrast */
--color-warning: #7F5C00;          /* 7.1:1 contrast */
--color-danger: #991B1B;           /* 8.3:1 contrast */
--color-info: #065F73;             /* 7.8:1 contrast */
```

### Spacing (8px base unit)

```css
--space-xs: 0.25rem;    /* 4px  */
--space-sm: 0.5rem;     /* 8px  */
--space-md: 1rem;       /* 16px */
--space-lg: 1.5rem;     /* 24px */
--space-xl: 2rem;       /* 32px */
--space-2xl: 3rem;      /* 48px */
--space-3xl: 4rem;      /* 64px */
--space-touch-min: 3rem;           /* 48px — minimum touch target */
--space-touch-comfortable: 3.5rem; /* 56px — icon-only touch target */
```

### Typography

```css
--font-size-base: 1.1rem;    /* 17.6px — body text */
--font-size-sm: 0.95rem;     /* 15.2px — secondary text */
--font-size-xs: 0.85rem;     /* 13.6px — timestamps, captions */
--font-size-h1: 2.25rem;     /* 36px */
--font-size-h2: 1.875rem;    /* 30px */
--font-size-h3: 1.5rem;      /* 24px */
--font-size-h4: 1.25rem;     /* 20px */
--font-size-h5: 1.1rem;      /* 17.6px */
--line-height-base: 1.5;
```

---

## Responsive Breakpoints

**Single breakpoint: 768px**

| Viewport | Layout | Navigation | Columns |
|---|---|---|---|
| < 768px | Mobile | Bottom nav (fixed) | Single column |
| ≥ 768px | Desktop | Left sidebar (fixed) | Multi-column |

```html
<!-- Bootstrap responsive grid -->
<div class="row g-3 g-md-4">
  <!-- Full width mobile, half on tablet+ -->
  <div class="col-12 col-md-6">...</div>
  <div class="col-12 col-md-6">...</div>

  <!-- Full width mobile, quarter on desktop -->
  <div class="col-12 col-md-6 col-xl-3">...</div>
</div>
```

Custom breakpoint CSS:

```css
/* Mobile base (always apply first) */
.my-component { flex-direction: column; }

/* Desktop override */
@media (min-width: 768px) {
  .my-component { flex-direction: row; }
}
```

---

## Accessibility Features

### Skip Navigation

Always included in the header — allows keyboard users to skip repeated nav:

```html
<a href="#main-content" class="skip-navigation">Skip to main content</a>
```

### Focus Indicators

All focusable elements automatically receive a 3px primary-color outline via `employee-portal-feedback.css`:

```css
*:focus-visible {
  outline: 3px solid var(--color-primary);
  outline-offset: 3px;
}
```

### ARIA Live Regions

Dynamic messages are announced automatically. You can also call the announcer directly:

```javascript
// Polite (for non-urgent updates)
window.epAnnounce('Draft saved successfully.');

// Assertive (for errors requiring immediate attention)
window.epAnnounce('Connection failed. Please try again.', 'assertive');
```

### Screen Reader Checklist

- All icon-only buttons have `aria-label`.
- All decorative icons have `aria-hidden="true"`.
- All form inputs have associated `<label for="...">`.
- Radio/checkbox groups use `<fieldset>` + `<legend>`.
- Dynamic regions use `role="alert"` or `aria-live="polite"`.
- Active nav items have `aria-current="page"`.
- Active progress step has `aria-current="step"`.

---

## File Structure

```
assets/
├── css/
│   ├── employee-portal-variables.css      # Design tokens (load first)
│   ├── employee-portal-layout.css         # Grid, containers, responsive layout
│   ├── employee-portal-navigation.css     # Sidebar, bottom-nav, breadcrumbs
│   ├── employee-portal-cards.css          # Stat cards, content cards, status badges
│   ├── employee-portal-forms.css          # Inputs, labels, validation, radio/checkbox
│   ├── employee-portal-buttons.css        # All button variants and states
│   ├── employee-portal-ratings.css        # Rating scale components
│   ├── employee-portal-progress.css       # Progress indicators and progress bars
│   ├── employee-portal-notifications.css  # Notification list, toast styles
│   ├── employee-portal-feedback.css       # Touch feedback, alerts, focus rings, loading
│   └── employee-portal-critical.css       # Inlined above-fold CSS (do not bloat this)
└── js/
    ├── auto-save.js                       # 2-second debounce auto-save
    ├── form-validation.js                 # Client-side validation
    └── employee-portal-feedback.js        # Toasts, haptics, ARIA, lazy images
```

---

## Migration Checklist

For adding the portal design system to a new Employee page:

- [ ] Page is only served to `$effective_role === 'Employee'`
- [ ] CSS loaded via `includes/header.php` (already included for Employee role)
- [ ] Page has `<a class="skip-navigation" href="#main-content">` in header
- [ ] Page uses `<main id="main-content" class="main-content">` as the content wrapper
- [ ] Sidebar and bottom nav are included (via header/footer includes)
- [ ] Active nav item has `aria-current="page"` server-side
- [ ] All images below the fold have `loading="lazy"` and explicit `width`/`height`
- [ ] Non-critical scripts have `defer` attribute
- [ ] Stat cards use correct `.stat-icon-*` variant (not color alone)
- [ ] All form inputs have `<label for="...">` associations
- [ ] All form inputs have `min-height: 48px` (`.form-control` / `.form-select` provides this)
- [ ] Submit buttons use `.btn-primary` and are wired for loading state
- [ ] Multi-step forms use the progress indicator component
- [ ] Forms with drafts have `data-autosave` and `data-autosave-endpoint`
- [ ] Run Lighthouse (Mobile, Incognito) — Accessibility score ≥ 95
- [ ] Test at 767px and 768px breakpoints

---

## Browser Support

- Chrome / Edge — latest 2 major versions
- Firefox — latest 2 major versions
- Safari — latest 2 major versions (test CSS `gap` and custom properties)
- iOS Safari — latest 2 versions (test on physical device or Xcode Simulator)
- Chrome on Android — latest 2 versions

---

## Version History

**Version 1.0.0** (Current)
- Mobile-first responsive design (single 768px breakpoint)
- WCAG AAA color system with all tokens in `employee-portal-variables.css`
- 10-file modular CSS architecture
- Full component library: cards, forms, buttons, ratings, progress, notifications, feedback
- Auto-save utility with 2-second debounce
- Client-side form validation with ARIA live region announcements
- Toast notification system with screen reader support
- Skeleton screens and loading overlays
- Haptic feedback on mobile for primary actions
