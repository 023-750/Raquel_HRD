# CSS Structure Restructuring & Modernization Plan

This implementation plan details findings from our codebase CSS audit and proposes a restructured, maintainable styling system for the Raquel Pawnshop HRIS.

---

## Findings Audit

### 1. File Responsibilities & Sizes
*   **`style.css` (122 KB, 5,434 lines)**: Acts as the massive master stylesheet for all HR portals (Admin, Manager, Supervisor, Staff). It contains base variables, common styles, layout grids, and specific views. Maintaining this single file is difficult and slow.
*   **`raquel-hris-login.css` (24.8 KB, 1,097 lines)**: Contains the login sliding track, branding panels, and forms.
*   **`employee-portal.css` and sub-files (1KB - 11KB)**: Already modularized for the Employee Portal. They use CSS `@import` rules, which are clean but load sequentially.

### 2. Duplicate & Conflicting Styles
*   **Duplicate Login CSS**: `style.css` lines 62-147 contain old, redundant login screen styling. The actual login screens link only to `raquel-hris-login.css`.
*   **Variable Naming Divergence**: 
    *   HRIS Portal (`style.css`): Uses variables like `--primary-blue` (`#082E06`) and `--primary-light` (`#CBA135`).
    *   Employee Portal (`employee-portal-variables.css`): Uses `--color-primary` (`#082E06`) and `--color-primary-light` (`#CBA135`).
*   **Conflicting Resets**: Reset styles for body tag typography and defaults are declared in multiple files, causing style overrides.

### 3. Inline `<style>` Blocks & `style="..."` Attributes
*   Over **45 PHP/HTML files** have embedded `<style>` blocks at the bottom of the page, leading to page-specific layout leakages.
*   Numerous elements use `style="..."` attributes for layout properties (e.g. `font-size`, `margin`, `padding`, `display`, `flex`) despite Bootstrap 5.3.0 being available globally.

---

## User Review Required

> [!IMPORTANT]
> **Dynamic CSS Loading in Header**
> Instead of loading a monolithic `style.css` for every page, we will dynamically inject the appropriate portal-level CSS file based on the directory directory name (e.g. `portals/admin/style.css` for `/admin/` pages) in `includes/header.php`. This will keep initial page render payloads smaller and styling concerns isolated.
>
> **Bootstrap Utility Class Transition**
> To replace inline styles (`style="..."`), we will refactor inline styles to use Bootstrap 5's responsive utilities (`d-flex`, `justify-content-between`, `mb-2`, `fs-6`, etc.) or custom common helper classes.

---

## Open Questions

> [!NOTE]
> **Q1**: Do you want us to perform the full conversion of inline `style="..."` attributes across all 45+ views now, or start by restructuring the core CSS files first and migrating inline HTML attributes iteratively?
> 
> **Q2**: Should we keep using CSS `@import` declarations for the Employee Portal, or combine them into a single build/link bundle for faster loading?

---

## Proposed Changes

We propose the following new CSS directory structure under `assets/css/` to separate concerns:

```
assets/css/
├── base/
│   ├── variables.css             <-- Global CSS variables (unified green/gold theme values)
│   ├── reset.css                 <-- Global resets, typography default, and accessibility skips
│   └── common.css                <-- Header, sidebar layout, default tables, badges, and modals
├── portals/
│   ├── admin/
│   │   └── style.css             <-- Admin portal overrides and mobile tables
│   ├── manager/
│   │   └── style.css             <-- Template wizards, branches insights explorer, departments layout
│   ├── supervisor/
│   │   └── style.css             <-- Analytics wrappers, endorsement grids, reports filters
│   ├── staff/
│   │   └── style.css             <-- Directory directories, history layout, career movements construction page
│   └── employee/                 <-- Modular portal files migrated to subfolder
│       ├── master.css
│       ├── layout.css
│       └── variables.css (etc.)
├── pages/
│   ├── login.css                 <-- Renamed and modernized raquel-hris-login.css
│   ├── print-evaluation.css      <-- Print layout stylesheet for evaluations
│   └── print-organization.css   <-- Print layout stylesheet for organization charts
```

### Core Architecture Changes

#### [MODIFY] [header.php](file:///c:/xampp/htdocs/Raquel_HRD_System/includes/header.php)
*   Replace monolithic stylesheet link with standard modular links:
    ```html
    <link href="<?php echo BASE_URL; ?>/assets/css/base/variables.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/base/reset.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/base/common.css" rel="stylesheet">
    ```
*   Add dynamic portal stylesheet loader based on `$current_dir`:
    ```php
    <?php
    $portal_css_path = "/assets/css/portals/{$current_dir}/style.css";
    if (file_exists(__DIR__ . '/..' . $portal_css_path)):
    ?>
    <link href="<?php echo BASE_URL . $portal_css_path; ?>?v=<?php echo time(); ?>" rel="stylesheet">
    <?php endif; ?>
    ```

#### [DELETE] [style.css](file:///c:/xampp/htdocs/Raquel_HRD_System/assets/css/style.css)
*   Split contents of the massive 122KB sheet into the respective files above.

#### [NEW] [variables.css](file:///c:/xampp/htdocs/Raquel_HRD_System/assets/css/base/variables.css)
*   Define global theme values (Dark Green `#082E06`, Gold `#CBA135`, etc.) with unified variable naming, accessible project-wide.

#### [NEW] [reset.css](file:///c:/xampp/htdocs/Raquel_HRD_System/assets/css/base/reset.css)
*   Basic resets and base body layouts.

#### [NEW] [common.css](file:///c:/xampp/htdocs/Raquel_HRD_System/assets/css/base/common.css)
*   Shared components like the `.top-navbar`, `.sidebar`, `.stat-card`, `.btn-primary`, and dynamic alert notifications.

#### [NEW] [portals/admin/style.css](file:///c:/xampp/htdocs/Raquel_HRD_System/assets/css/portals/admin/style.css)
*   Contains `.admin-area` layout customisations and mobile tables styling from `style.css`.

#### [NEW] [portals/manager/style.css](file:///c:/xampp/htdocs/Raquel_HRD_System/assets/css/portals/manager/style.css)
*   Contains dynamic template dynamic builder layout rules, Branch Insights explorer CSS, and wizard sheets styling.

#### [NEW] [portals/supervisor/style.css](file:///c:/xampp/htdocs/Raquel_HRD_System/assets/css/portals/supervisor/style.css)
*   Contains validation forms, supervisor indicators, evaluation metrics, and reports formatting rules.

#### [NEW] [portals/staff/style.css](file:///c:/xampp/htdocs/Raquel_HRD_System/assets/css/portals/staff/style.css)
*   Contains staff dashboard panels layout, career movements construction screen style, and directory view rules.

#### [MODIFY] [index.php](file:///c:/xampp/htdocs/Raquel_HRD_System/index.php) and [employee/index.php](file:///c:/xampp/htdocs/Raquel_HRD_System/employee/index.php)
*   Update references from `/assets/css/raquel-hris-login.css` to `/assets/css/pages/login.css`.
*   Remove redundant inline `<style>` tags.

---

## Verification Plan

### Automated Checks
*   Verify that no console errors appear in the browser developer tools regarding stylesheet 404s.
*   Ensure that all CSS files parse cleanly with standard linting rules.

### Manual Verification
1.  **Layout Consistency**: Access each dashboard (Admin, Manager, Supervisor, HR Staff, Employee) on desktop and mobile viewport sizes to ensure the sidebar, top navigation, typography, colors, and layout structure remain identical.
2.  **Login Visual Flow**: Validate login screens and panel switching transitions to ensure smooth CSS animations.
3.  **Module Functionality**: Test specific complex views (e.g. Branch Insights Explorer under HR Manager dashboard, dynamic charts, evaluation history view, wizards) to ensure their styles are correctly loaded and active.
4.  **Print Previews**: Test print dialog triggers for organization charts and evaluation forms.
