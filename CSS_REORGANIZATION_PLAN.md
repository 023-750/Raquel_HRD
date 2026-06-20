# CSS Reorganization Implementation Plan

## Objective

Move all inline CSS from PHP/HTML files into external stylesheets, then organize those styles by shared scope, role, and module. The goal is easier navigation, safer maintenance, and clearer ownership of each stylesheet.

## Proposed CSS Structure

```text
assets/css/
  variables.css
  layout.css
  header-footer.css

  admin.css
  manager.css
  supervisor.css
  staff.css
  employee.css

  notifications.css
  forms.css
  tables.css
  evaluations.css
  reports.css
  profile.css
```

## File Responsibilities

### `variables.css`

Shared design tokens used across the whole system:

- Brand colors
- Text colors
- Background colors
- Font families
- Font sizes
- Spacing values
- Border radius values
- Shadows
- Transition timing

### `layout.css`

Global layout rules shared by most pages:

- Main content spacing
- Page wrappers
- Responsive layout helpers
- Shared container behavior
- App-wide utility classes

### `header-footer.css`

Styles for shared interface elements loaded through the common includes:

- Sidebar
- Top navbar
- Header logo
- User dropdown
- Notification dropdown trigger
- Footer
- Shared image modal
- Employee mobile bottom navigation

### Role Stylesheets

Each role stylesheet should contain only styles specific to that portal area:

- `admin.css` for `admin/`
- `manager.css` for `manager/`
- `supervisor.css` for `supervisor/`
- `staff.css` for `staff/`
- `employee.css` for `employee/`

### Module Stylesheets

Module stylesheets should contain reusable feature-specific styles:

- `notifications.css` for notification lists, badges, dropdown content, read/unread states, and empty states
- `forms.css` for forms, inputs, validation states, field groups, and reusable modals
- `tables.css` for tables, filters, search bars, pagination, and table actions
- `evaluations.css` for self-rating, confirmation, department manager review, HR review, score cards, KRA sections, and behavior sections
- `reports.css` for report pages, charts, export UI, and printable report views
- `profile.css` for profile settings, password forms, account details, and profile photo UI

## Implementation Steps

### 1. Audit Existing Styles

Scan all `.php` and `.html` files for:

- `<style>` blocks
- `style="..."` attributes
- existing stylesheet links

Create a short inventory showing:

- file path
- line number
- style type
- suggested destination stylesheet

### 2. Create Shared CSS Files

Create the shared CSS files first:

- `assets/css/variables.css`
- `assets/css/layout.css`
- `assets/css/header-footer.css`

Move global values and shared layout styles before touching role-specific pages.

### 3. Extract Header and Footer Styles

Move inline styles from:

- `includes/header.php`
- `includes/footer.php`

Replace inline styles with semantic classes, for example:

```html
<img class="sidebar-brand-logo">
```

Instead of:

```html
<img style="width: 50px; height: 50px;">
```

### 4. Update Shared Stylesheet Loading

Update `includes/header.php` to load shared CSS first:

```php
<link href="<?php echo BASE_URL; ?>/assets/css/variables.css?v=<?php echo time(); ?>" rel="stylesheet">
<link href="<?php echo BASE_URL; ?>/assets/css/layout.css?v=<?php echo time(); ?>" rel="stylesheet">
<link href="<?php echo BASE_URL; ?>/assets/css/header-footer.css?v=<?php echo time(); ?>" rel="stylesheet">
```

Then load role-specific styles based on the current folder:

```php
<?php if ($current_dir === 'admin'): ?>
<link href="<?php echo BASE_URL; ?>/assets/css/admin.css?v=<?php echo time(); ?>" rel="stylesheet">
<?php elseif ($current_dir === 'employee'): ?>
<link href="<?php echo BASE_URL; ?>/assets/css/employee.css?v=<?php echo time(); ?>" rel="stylesheet">
<?php elseif ($current_dir === 'manager'): ?>
<link href="<?php echo BASE_URL; ?>/assets/css/manager.css?v=<?php echo time(); ?>" rel="stylesheet">
<?php elseif ($current_dir === 'supervisor'): ?>
<link href="<?php echo BASE_URL; ?>/assets/css/supervisor.css?v=<?php echo time(); ?>" rel="stylesheet">
<?php elseif ($current_dir === 'staff'): ?>
<link href="<?php echo BASE_URL; ?>/assets/css/staff.css?v=<?php echo time(); ?>" rel="stylesheet">
<?php endif; ?>
```

### 5. Move Role-Specific Styles

Move styles from each role folder into the corresponding role stylesheet:

```text
admin/*.php      -> assets/css/admin.css
employee/*.php   -> assets/css/employee.css
manager/*.php    -> assets/css/manager.css
supervisor/*.php -> assets/css/supervisor.css
staff/*.php      -> assets/css/staff.css
```

Only move styles into a role stylesheet when they are clearly specific to that role.

### 6. Move Module-Specific Styles

Move reusable feature styles into module files:

```text
notification pages/dropdowns -> notifications.css
forms/modals/inputs          -> forms.css
tables/search/pagination     -> tables.css
rating/review workflows      -> evaluations.css
reports/charts/export UI     -> reports.css
profile/settings UI          -> profile.css
```

If a style is reused across multiple roles, prefer a module stylesheet over duplicating it in role files.

### 7. Preserve Existing Employee Portal CSS Carefully

The project already contains several employee portal stylesheets, including:

- `employee-portal-variables.css`
- `employee-portal-layout.css`
- `employee-portal-navigation.css`
- `employee-portal-notifications.css`
- `employee-portal-ratings.css`

Do not delete or merge these blindly. First confirm which pages depend on them. Then either:

- keep them as employee module files, or
- gradually consolidate them into the new structure after visual verification.

### 8. Replace Inline Styles With Classes

For every inline style:

1. Create a descriptive class name.
2. Move the CSS declaration into the correct stylesheet.
3. Replace the `style="..."` attribute with `class="..."`.
4. Preserve existing classes on the element.

Example:

```html
<a class="mark-all-read-link">
```

Instead of:

```html
<a style="font-size:0.75rem;font-weight:400;">
```

### 9. Verify Key Pages

After migration, visually check:

- Login page
- Admin dashboard
- Manager dashboard
- Supervisor dashboard
- Staff dashboard
- Employee dashboard
- Notifications page
- Employee self-rating page
- Employee confirm-rating page
- Department manager review page
- Profile settings page

Confirm that:

- layout is unchanged
- dropdowns still display correctly
- mobile navigation still works
- notification badges still align correctly
- forms and modals still look correct

### 10. Cleanup

After verification:

- Remove empty `<style>` blocks
- Remove unused CSS only after confirming it is no longer referenced
- Keep CSS comments short and section-based
- Avoid duplicating shared styles across role files

## Suggested Migration Order

1. Create `variables.css`
2. Create `header-footer.css`
3. Create `layout.css`
4. Move shared notification styles into `notifications.css`
5. Move employee portal styles carefully
6. Move admin styles
7. Move manager styles
8. Move supervisor styles
9. Move staff styles
10. Move remaining module styles
11. Verify key pages
12. Cleanup unused inline styles and empty blocks

## Notes

- Keep the migration behavior-preserving.
- Do not redesign pages during the CSS move.
- Avoid changing PHP logic while extracting styles.
- Prefer semantic class names over presentational names.
- Make smaller commits by section if using version control.
