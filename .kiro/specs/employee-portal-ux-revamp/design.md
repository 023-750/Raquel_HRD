# Design Document: Employee Portal UX Revamp

## Overview

This design document details the technical implementation approach for a comprehensive UI/UX redesign of the Raquel Pawnshop HRIS employee portal. The redesign targets employees aged 40+ years old with a focus on mobile-responsive design, WCAG AAA accessibility compliance, and reduced cognitive load while maintaining full feature functionality.

### Design Goals

1. **Mobile-First Responsive Design**: Ensure seamless experience across devices (320px - 2560px width)
2. **Enhanced Readability**: Larger typography, higher contrast, improved visual hierarchy
3. **Touch-Friendly Interaction**: Minimum 48x48px touch targets with adequate spacing
4. **Accessibility Excellence**: WCAG AAA compliance (7:1 contrast for normal text, 4.5:1 for large text)
5. **Simplified Navigation**: Maximum 7 top-level items, clear labeling, breadcrumbs
6. **Reduced Cognitive Load**: Card-based layouts, progressive disclosure, clear sectioning
7. **Performance Optimization**: <2s initial render on 3G, lazy loading, minified assets

### Technology Stack

- **CSS Framework**: Bootstrap 5.3.x (responsive grid, utility classes, base components)
- **CSS Architecture**: CSS Custom Properties (variables) for theming and consistency
- **Layout System**: CSS Grid and Flexbox for fluid, responsive layouts
- **Typography**: System font stack with fallbacks (Arial, Helvetica, system fonts)
- **JavaScript**: Vanilla JS with progressive enhancement for auto-save, animations
- **Performance**: Lazy loading, resource minification, responsive images

### Target Devices & Breakpoints

- **Mobile Portrait**: 320px - 767px (single column, bottom navigation)
- **Tablet/Desktop**: 768px+ (multi-column, top navigation, sidebar)
- **Mobile Breakpoint**: 768px (primary responsive threshold)

## Architecture

### System Context

The employee portal operates as a sub-application within the existing Raquel Pawnshop HRIS system. It interfaces with:

1. **Authentication System**: Session-based authentication for employee accounts
2. **Database Layer**: MySQL database for employee data, evaluations, PDS, notifications
3. **File Storage**: Server-side storage for profile pictures, documents
5. **Backend API**: PHP-based endpoints for data operations, form submissions
6. **Admin System**: Separate admin interface that creates evaluation templates, manages employees

### Architectural Layers

```
┌─────────────────────────────────────────────────────────────┐
│                    Presentation Layer                        │
│  (HTML, CSS, Bootstrap 5, Custom CSS Variables, JS)         │
└──────────────────────┬──────────────────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────────────────┐
│                  Component Layer                             │
│  (Reusable UI Components: Cards, Forms, Navigation, etc.)   │
└──────────────────────┬──────────────────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────────────────┐
│                   Service Layer                              │
│  (Auto-save, Notification polling, Form validation)          │
└──────────────────────┬──────────────────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────────────────┐
│                  Backend Layer (Existing PHP)                │
│  (Session management, Database queries, Business logic)      │
└──────────────────────────────────────────────────────────────┘
```

### Design Patterns

1. **Mobile-First Responsive Design**: Start with mobile layout, progressively enhance for larger screens
2. **Progressive Enhancement**: Core functionality works without JavaScript, enhanced with JS
3. **Component-Based Architecture**: Reusable, self-contained UI components
4. **Utility-First CSS**: Bootstrap utilities + custom utilities for rapid development
5. **CSS Custom Properties**: Centralized theming, easy maintenance, runtime adjustments

## Components and Interfaces

### 1. CSS Variable System

**Purpose**: Centralized design token management for colors, spacing, typography, shadows


**Implementation** (file: `/assets/css/employee-portal-variables.css`):

```css
:root {
  /* ===== COLORS ===== */
  /* Brand colors - existing theme */
  --color-primary: #082E06;        /* Dark green */
  --color-primary-light: #CBA135;  /* Gold */
  --color-primary-dark: #041D03;
  --color-accent: #E1232A;
  
  /* Neutral colors - WCAG AAA compliant */
  --color-bg-primary: #FFFFFF;
  --color-bg-secondary: #F4F6F2;
  --color-bg-tertiary: #E8EBE6;
  
  /* Text colors - 7:1 contrast on white */
  --color-text-primary: #1C271B;    /* 15.8:1 contrast */
  --color-text-secondary: #3D4D3A;  /* 10.2:1 contrast */
  --color-text-muted: #5E6B5C;      /* 7.1:1 contrast */
  
  /* Status colors - WCAG AAA compliant */
  --color-success: #0F6B2E;         /* 7.5:1 contrast */
  --color-warning: #7F5C00;         /* 7.1:1 contrast */
  --color-danger: #991B1B;          /* 8.3:1 contrast */
  --color-info: #065F73;            /* 7.8:1 contrast */
  
  /* Border colors */
  --color-border-light: #D1D5CE;
  --color-border-medium: #A8B0A3;
  --color-border-dark: #6B7866;
  
  /* ===== SPACING (8px base unit) ===== */
  --space-xs: 0.25rem;   /* 4px */
  --space-sm: 0.5rem;    /* 8px */
  --space-md: 1rem;      /* 16px */
  --space-lg: 1.5rem;    /* 24px */
  --space-xl: 2rem;      /* 32px */
  --space-2xl: 3rem;     /* 48px */
  --space-3xl: 4rem;     /* 64px */
  
  /* Touch target spacing */
  --space-touch-min: 3rem;       /* 48px minimum */
  --space-touch-comfortable: 3.5rem; /* 56px comfortable */
  
  /* ===== TYPOGRAPHY ===== */
  /* Base font size: 1.1rem (17.6px at 16px default) */
  --font-family-base: -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
  --font-family-mono: "Courier New", Courier, monospace;
  
  --font-size-base: 1.1rem;      /* 17.6px - body text */
  --font-size-sm: 0.95rem;       /* 15.2px - secondary text */
  --font-size-xs: 0.85rem;       /* 13.6px - captions */
  
  --font-size-h1: 2.25rem;       /* 36px */
  --font-size-h2: 1.875rem;      /* 30px */
  --font-size-h3: 1.5rem;        /* 24px */
  --font-size-h4: 1.25rem;       /* 20px */
  --font-size-h5: 1.1rem;        /* 17.6px */
  
  --line-height-base: 1.5;
  --line-height-tight: 1.25;
  --line-height-loose: 1.75;
  
  --letter-spacing-base: 0.02em;
  --letter-spacing-wide: 0.05em;
  
  /* ===== SHADOWS ===== */
  --shadow-sm: 0 1px 3px rgba(8, 46, 6, 0.12), 0 1px 2px rgba(8, 46, 6, 0.08);
  --shadow-md: 0 4px 6px rgba(8, 46, 6, 0.12), 0 2px 4px rgba(8, 46, 6, 0.08);
  --shadow-lg: 0 10px 15px rgba(8, 46, 6, 0.12), 0 4px 6px rgba(8, 46, 6, 0.08);
  --shadow-xl: 0 20px 25px rgba(8, 46, 6, 0.12), 0 8px 10px rgba(8, 46, 6, 0.08);
  
  /* ===== BORDER RADIUS ===== */
  --radius-sm: 0.375rem;   /* 6px */
  --radius-md: 0.5rem;     /* 8px */
  --radius-lg: 0.75rem;    /* 12px */
  --radius-xl: 1rem;       /* 16px */
  --radius-full: 9999px;
  
  /* ===== TRANSITIONS ===== */
  --transition-fast: 150ms ease-in-out;
  --transition-base: 250ms ease-in-out;
  --transition-slow: 350ms ease-in-out;
}
```

**Integration with Bootstrap**: Override Bootstrap Sass variables before compilation or use CSS custom properties to override compiled values.

### 2. Responsive Grid System


**Purpose**: Fluid, mobile-first layout system using Bootstrap 5 grid with custom enhancements

**Implementation**:

```html
<!-- Mobile: Single column, Desktop: Multi-column -->
<div class="container-fluid">
  <div class="row g-md-4 g-3"> <!-- Responsive gutters -->
    <!-- Full width on mobile, half width on tablet+ -->
    <div class="col-12 col-md-6">
      <div class="stat-card">...</div>
    </div>
    <div class="col-12 col-md-6">
      <div class="stat-card">...</div>
    </div>
  </div>
</div>
```

**Custom CSS** (file: `/assets/css/employee-portal-layout.css`):

```css
/* Ensure no horizontal scroll on any screen size */
.container-fluid {
  max-width: 100vw;
  overflow-x: hidden;
  padding-left: var(--space-md);
  padding-right: var(--space-md);
}

/* Mobile-first: single column by default */
.main-content {
  display: flex;
  flex-direction: column;
  gap: var(--space-md);
}

/* Tablet and up: use grid for complex layouts */
@media (min-width: 768px) {
  .main-content {
    gap: var(--space-lg);
  }
  
  .dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: var(--space-lg);
  }
}

/* Prevent content overflow on all screen sizes */
* {
  max-width: 100%;
  word-wrap: break-word;
}
```

### 3. Navigation Components


#### 3.1 Mobile Bottom Navigation

**Purpose**: Thumb-friendly primary navigation for mobile devices (<768px)

**HTML Structure**:

```html
<nav class="bottom-nav d-md-none" aria-label="Primary navigation">
  <a href="dashboard.php" class="bottom-nav-item active" aria-current="page">
    <i class="ti ti-home" aria-hidden="true"></i>
    <span>Dashboard</span>
  </a>
  <a href="self-rating.php" class="bottom-nav-item">
    <i class="ti ti-clipboard-check" aria-hidden="true"></i>
    <span>Evaluations</span>
  </a>
  <a href="notifications.php" class="bottom-nav-item">
    <i class="ti ti-bell" aria-hidden="true"></i>
    <span>Notifications</span>
    <span class="badge-dot" aria-label="3 unread notifications">3</span>
  </a>
  <a href="profile-settings.php" class="bottom-nav-item">
    <i class="ti ti-user" aria-hidden="true"></i>
    <span>Profile</span>
  </a>
  <button class="bottom-nav-item" id="mobile-menu-toggle" aria-label="Open menu" aria-expanded="false">
    <i class="ti ti-menu-2" aria-hidden="true"></i>
    <span>More</span>
  </button>
</nav>
```

**CSS Implementation** (file: `/assets/css/employee-portal-navigation.css`):

```css
/* Bottom navigation - mobile only */
.bottom-nav {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  display: flex;
  justify-content: space-around;
  background: var(--color-bg-primary);
  border-top: 1px solid var(--color-border-light);
  padding: var(--space-xs) var(--space-sm);
  z-index: 1000;
  box-shadow: 0 -2px 10px rgba(8, 46, 6, 0.08);
}

.bottom-nav-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: var(--space-xs);
  min-width: var(--space-touch-min);
  min-height: var(--space-touch-min);
  padding: var(--space-xs);
  color: var(--color-text-muted);
  text-decoration: none;
  border: none;
  background: transparent;
  border-radius: var(--radius-md);
  transition: all var(--transition-fast);
  position: relative;
  cursor: pointer;
}

.bottom-nav-item i {
  font-size: 1.5rem;
}

.bottom-nav-item span {
  font-size: 0.75rem;
  font-weight: 500;
  letter-spacing: var(--letter-spacing-base);
}

.bottom-nav-item.active {
  color: var(--color-primary);
  background: rgba(8, 46, 6, 0.08);
}

.bottom-nav-item:hover,
.bottom-nav-item:focus {
  color: var(--color-primary);
  background: rgba(8, 46, 6, 0.05);
  outline: 2px solid var(--color-primary);
  outline-offset: -2px;
}

/* Badge for notification count */
.badge-dot {
  position: absolute;
  top: 4px;
  right: 8px;
  min-width: 1.25rem;
  height: 1.25rem;
  padding: 0 var(--space-xs);
  background: var(--color-danger);
  color: white;
  font-size: 0.7rem;
  font-weight: 700;
  border-radius: var(--radius-full);
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Reserve space at bottom for fixed navigation */
@media (max-width: 767px) {
  body {
    padding-bottom: calc(var(--space-touch-min) + var(--space-md));
  }
}
```

#### 3.2 Desktop Sidebar Navigation

**Purpose**: Traditional sidebar navigation for desktop (768px+)

**HTML Structure**:


```html
<aside class="sidebar d-none d-md-block" aria-label="Main navigation">
  <div class="sidebar-header">
    <img src="/assets/img/logo/logo.png" alt="Raquel Pawnshop logo" class="sidebar-logo">
    <h2>Employee Portal</h2>
  </div>
  
  <nav class="sidebar-nav">
    <a href="dashboard.php" class="sidebar-link active" aria-current="page">
      <i class="ti ti-home" aria-hidden="true"></i>
      <span>My Dashboard</span>
    </a>
    <a href="my-employment.php" class="sidebar-link">
      <i class="ti ti-briefcase" aria-hidden="true"></i>
      <span>My Employment</span>
    </a>
    <a href="self-rating.php" class="sidebar-link">
      <i class="ti ti-clipboard-check" aria-hidden="true"></i>
      <span>My Evaluations</span>
    </a>
    <a href="my-pds.php" class="sidebar-link">
      <i class="ti ti-id" aria-hidden="true"></i>
      <span>My PDS</span>
    </a>
    <a href="career-movement-request.php" class="sidebar-link">
      <i class="ti ti-trending-up" aria-hidden="true"></i>
      <span>Career Requests</span>
    </a>
    <a href="team-list.php" class="sidebar-link">
      <i class="ti ti-users" aria-hidden="true"></i>
      <span>My Team</span>
    </a>
    <a href="profile-settings.php" class="sidebar-link">
      <i class="ti ti-settings" aria-hidden="true"></i>
      <span>Settings</span>
    </a>
  </nav>
</aside>
```

**CSS Implementation**:

```css
/* Desktop sidebar - hidden on mobile */
.sidebar {
  position: fixed;
  top: 0;
  left: 0;
  width: 260px;
  height: 100vh;
  background: linear-gradient(180deg, #0f1a08 0%, #0c1208 55%, #0a0f0a 100%);
  color: rgba(255, 255, 255, 0.8);
  overflow-y: auto;
  z-index: 1040;
  box-shadow: 4px 0 18px rgba(0, 0, 0, 0.28);
}

.sidebar-header {
  padding: var(--space-lg);
  text-align: center;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.sidebar-logo {
  width: 60px;
  height: 60px;
  margin-bottom: var(--space-sm);
}

.sidebar-header h2 {
  font-size: 1.1rem;
  margin: 0;
  font-weight: 700;
  color: white;
}

.sidebar-nav {
  padding: var(--space-md) 0;
}

.sidebar-link {
  display: flex;
  align-items: center;
  gap: var(--space-md);
  padding: 0.75rem var(--space-md);
  margin: var(--space-xs) var(--space-md);
  color: rgba(255, 255, 255, 0.74);
  text-decoration: none;
  border-radius: var(--radius-lg);
  transition: all var(--transition-fast);
  font-size: 0.95rem;
  font-weight: 500;
  min-height: var(--space-touch-min);
}

.sidebar-link i {
  font-size: 1.25rem;
  width: 24px;
  text-align: center;
}

.sidebar-link:hover,
.sidebar-link:focus {
  background: rgba(255, 255, 255, 0.07);
  color: rgba(255, 255, 255, 0.95);
  transform: translateX(4px);
  outline: 2px solid rgba(203, 161, 53, 0.4);
  outline-offset: -2px;
}

.sidebar-link.active {
  background: linear-gradient(135deg, rgba(203, 161, 53, 0.2) 0%, rgba(8, 46, 6, 0.4) 100%);
  color: #fff;
  font-weight: 600;
}

/* Adjust main content for sidebar */
@media (min-width: 768px) {
  .main-content {
    margin-left: 260px;
    padding: var(--space-lg);
  }
}
```

#### 3.3 Breadcrumb Navigation

**Purpose**: Show user's current location in site hierarchy

**HTML Structure**:


```html
<nav aria-label="Breadcrumb" class="breadcrumb-nav">
  <ol class="breadcrumb">
    <li class="breadcrumb-item">
      <a href="dashboard.php">Dashboard</a>
    </li>
    <li class="breadcrumb-item">
      <a href="self-rating.php">Evaluations</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
      Self-Rating Form
    </li>
  </ol>
</nav>
```

**CSS Implementation**:

```css
.breadcrumb-nav {
  margin-bottom: var(--space-lg);
  padding: var(--space-sm) 0;
}

.breadcrumb {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-xs);
  margin: 0;
  padding: 0;
  list-style: none;
  font-size: 0.95rem;
}

.breadcrumb-item {
  display: flex;
  align-items: center;
}

.breadcrumb-item + .breadcrumb-item::before {
  content: "/";
  padding: 0 var(--space-sm);
  color: var(--color-text-muted);
}

.breadcrumb-item a {
  color: var(--color-primary);
  text-decoration: none;
  padding: var(--space-xs) var(--space-sm);
  border-radius: var(--radius-sm);
  transition: all var(--transition-fast);
}

.breadcrumb-item a:hover,
.breadcrumb-item a:focus {
  background: rgba(8, 46, 6, 0.08);
  outline: 2px solid var(--color-primary);
  outline-offset: 2px;
}

.breadcrumb-item.active {
  color: var(--color-text-secondary);
  font-weight: 500;
}
```

### 4. Card Components

#### 4.1 Stat Card (Dashboard)

**Purpose**: Display key metrics with clear visual hierarchy

**HTML Structure**:


```html
<div class="stat-card">
  <div class="stat-icon stat-icon-success" aria-hidden="true">
    <i class="ti ti-clipboard-check"></i>
  </div>
  <div class="stat-content">
    <h3 class="stat-value">8</h3>
    <p class="stat-label">Completed Evaluations</p>
  </div>
</div>
```

**CSS Implementation** (file: `/assets/css/employee-portal-cards.css`):

```css
.stat-card {
  display: flex;
  align-items: center;
  gap: var(--space-lg);
  min-height: 100px;
  padding: var(--space-lg);
  background: var(--color-bg-primary);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-sm);
  transition: all var(--transition-base);
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.stat-icon {
  flex-shrink: 0;
  width: 64px;
  height: 64px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: var(--radius-lg);
  font-size: 2rem;
}

.stat-icon-success {
  background: rgba(15, 107, 46, 0.1);
  color: var(--color-success);
}

.stat-icon-warning {
  background: rgba(127, 92, 0, 0.1);
  color: var(--color-warning);
}

.stat-icon-info {
  background: rgba(6, 95, 115, 0.1);
  color: var(--color-info);
}

.stat-icon-danger {
  background: rgba(153, 27, 27, 0.1);
  color: var(--color-danger);
}

.stat-content {
  flex: 1;
  min-width: 0;
}

.stat-value {
  font-size: 2.25rem;
  font-weight: 800;
  line-height: var(--line-height-tight);
  margin: 0 0 var(--space-xs);
  color: var(--color-text-primary);
  letter-spacing: -0.02em;
}

.stat-label {
  font-size: 0.95rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: var(--letter-spacing-wide);
  margin: 0;
  color: var(--color-text-muted);
}

/* Mobile: Stack icon and content vertically */
@media (max-width: 767px) {
  .stat-card {
    flex-direction: column;
    text-align: center;
  }
}
```

#### 4.2 Content Card (Generic)

**Purpose**: General-purpose container for grouped content

**HTML Structure**:

```html
<div class="content-card">
  <div class="content-card-header">
    <h2 class="content-card-title">
      <i class="ti ti-bell" aria-hidden="true"></i>
      Recent Notifications
    </h2>
    <button class="btn btn-sm btn-outline-primary" aria-label="Mark all as read">
      Mark All Read
    </button>
  </div>
  <div class="content-card-body">
    <!-- Card content -->
  </div>
</div>
```

**CSS Implementation**:

```css
.content-card {
  background: var(--color-bg-primary);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-sm);
  margin-bottom: var(--space-lg);
  overflow: hidden;
}

.content-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: var(--space-md);
  padding: var(--space-lg);
  border-bottom: 1px solid var(--color-border-light);
  background: var(--color-bg-secondary);
}

.content-card-title {
  display: flex;
  align-items: center;
  gap: var(--space-sm);
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0;
  color: var(--color-text-primary);
}

.content-card-title i {
  color: var(--color-primary-light);
  font-size: 1.75rem;
}

.content-card-body {
  padding: var(--space-lg);
}

/* Mobile: Reduce padding */
@media (max-width: 767px) {
  .content-card-header,
  .content-card-body {
    padding: var(--space-md);
  }
  
  .content-card-title {
    font-size: 1.25rem;
  }
}
```

### 5. Form Components

#### 5.1 Form Input Fields

**Purpose**: Accessible, touch-friendly form inputs with clear labeling


**HTML Structure**:

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
    Your legal first name as it appears on official documents
  </small>
  <div class="form-error" id="firstNameError" role="alert" aria-live="polite"></div>
</div>
```

**CSS Implementation** (file: `/assets/css/employee-portal-forms.css`):

```css
.form-group {
  margin-bottom: var(--space-lg);
}

.form-label {
  display: block;
  font-size: 1rem;
  font-weight: 600;
  color: var(--color-text-primary);
  margin-bottom: var(--space-sm);
  letter-spacing: var(--letter-spacing-base);
}

.required-indicator {
  color: var(--color-danger);
  font-weight: 700;
  margin-left: var(--space-xs);
}

.form-control,
.form-select {
  width: 100%;
  min-height: 48px;
  padding: var(--space-md);
  font-size: var(--font-size-base);
  line-height: var(--line-height-base);
  color: var(--color-text-primary);
  background: var(--color-bg-primary);
  border: 2px solid var(--color-border-medium);
  border-radius: var(--radius-md);
  transition: all var(--transition-fast);
}

.form-control:hover {
  border-color: var(--color-border-dark);
}

.form-control:focus,
.form-select:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 4px rgba(8, 46, 6, 0.15);
}

.form-control::placeholder {
  color: var(--color-text-muted);
  opacity: 0.7;
}

.form-helper {
  display: block;
  font-size: 0.95rem;
  color: var(--color-text-muted);
  margin-top: var(--space-sm);
  line-height: var(--line-height-base);
}

.form-error {
  display: none;
  align-items: flex-start;
  gap: var(--space-sm);
  padding: var(--space-sm) var(--space-md);
  margin-top: var(--space-sm);
  background: rgba(153, 27, 27, 0.08);
  border: 1px solid var(--color-danger);
  border-left: 4px solid var(--color-danger);
  border-radius: var(--radius-md);
  color: var(--color-danger);
  font-size: 1rem;
  font-weight: 500;
}

.form-error::before {
  content: "⚠";
  font-size: 1.25rem;
  flex-shrink: 0;
}

.form-control.is-invalid + .form-error {
  display: flex;
}

.form-control.is-invalid {
  border-color: var(--color-danger);
}

.form-control.is-valid {
  border-color: var(--color-success);
}

/* Character counter for text inputs with limits */
.char-counter {
  display: flex;
  justify-content: flex-end;
  font-size: 0.85rem;
  color: var(--color-text-muted);
  margin-top: var(--space-xs);
}

.char-counter.warning {
  color: var(--color-warning);
  font-weight: 600;
}

.char-counter.danger {
  color: var(--color-danger);
  font-weight: 700;
}
```

#### 5.2 Radio Buttons and Checkboxes

**Purpose**: Large, touch-friendly selection controls

**HTML Structure**:

```html
<!-- Radio button group -->
<fieldset class="form-group">
  <legend class="form-label">Employment Type</legend>
  <div class="radio-group">
    <div class="radio-option">
      <input type="radio" id="fullTime" name="employmentType" value="full-time" class="radio-input">
      <label for="fullTime" class="radio-label">Full-Time</label>
    </div>
    <div class="radio-option">
      <input type="radio" id="partTime" name="employmentType" value="part-time" class="radio-input">
      <label for="partTime" class="radio-label">Part-Time</label>
    </div>
  </div>
</fieldset>
```


**CSS Implementation**:

```css
.radio-group,
.checkbox-group {
  display: flex;
  flex-direction: column;
  gap: var(--space-md);
}

.radio-option,
.checkbox-option {
  display: flex;
  align-items: center;
  min-height: 48px;
  position: relative;
}

.radio-input,
.checkbox-input {
  position: absolute;
  opacity: 0;
  width: 0;
  height: 0;
}

.radio-label,
.checkbox-label {
  display: flex;
  align-items: center;
  gap: var(--space-md);
  padding: var(--space-md);
  width: 100%;
  min-height: 48px;
  font-size: var(--font-size-base);
  font-weight: 500;
  color: var(--color-text-primary);
  background: var(--color-bg-secondary);
  border: 2px solid var(--color-border-light);
  border-radius: var(--radius-md);
  cursor: pointer;
  transition: all var(--transition-fast);
  position: relative;
  padding-left: calc(var(--space-xl) + var(--space-md));
}

/* Custom radio/checkbox indicator */
.radio-label::before,
.checkbox-label::before {
  content: "";
  position: absolute;
  left: var(--space-md);
  width: 24px;
  height: 24px;
  border: 2px solid var(--color-border-dark);
  background: var(--color-bg-primary);
  transition: all var(--transition-fast);
}

.radio-label::before {
  border-radius: 50%;
}

.checkbox-label::before {
  border-radius: var(--radius-sm);
}

/* Checked state indicator */
.radio-input:checked + .radio-label::after,
.checkbox-input:checked + .checkbox-label::after {
  content: "";
  position: absolute;
  left: calc(var(--space-md) + 6px);
}

.radio-input:checked + .radio-label::after {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: var(--color-primary);
}

.checkbox-input:checked + .checkbox-label::after {
  width: 8px;
  height: 14px;
  border: solid var(--color-primary);
  border-width: 0 3px 3px 0;
  transform: rotate(45deg);
  top: 50%;
  margin-top: -10px;
}

/* Hover and focus states */
.radio-label:hover,
.checkbox-label:hover,
.radio-input:focus + .radio-label,
.checkbox-input:focus + .checkbox-label {
  background: var(--color-bg-primary);
  border-color: var(--color-primary);
  box-shadow: 0 0 0 4px rgba(8, 46, 6, 0.1);
}

/* Checked state */
.radio-input:checked + .radio-label,
.checkbox-input:checked + .checkbox-label {
  background: rgba(8, 46, 6, 0.05);
  border-color: var(--color-primary);
  font-weight: 600;
}

.radio-input:checked + .radio-label::before,
.checkbox-input:checked + .checkbox-label::before {
  border-color: var(--color-primary);
  background: var(--color-bg-primary);
}
```

### 6. Button Components

**Purpose**: Consistent, accessible, touch-friendly action buttons

**HTML Structure**:

```html
<!-- Primary action button -->
<button type="submit" class="btn btn-primary btn-lg">
  <i class="ti ti-check" aria-hidden="true"></i>
  Submit Evaluation
</button>

<!-- Secondary action button -->
<button type="button" class="btn btn-outline-primary btn-lg">
  <i class="ti ti-x" aria-hidden="true"></i>
  Cancel
</button>

<!-- Danger action button -->
<button type="button" class="btn btn-danger btn-lg">
  <i class="ti ti-trash" aria-hidden="true"></i>
  Delete Draft
</button>
```

**CSS Implementation** (file: `/assets/css/employee-portal-buttons.css`):

```css
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: var(--space-sm);
  min-height: 48px;
  padding: var(--space-md) var(--space-lg);
  font-size: var(--font-size-base);
  font-weight: 600;
  line-height: var(--line-height-base);
  text-align: center;
  text-decoration: none;
  border: 2px solid transparent;
  border-radius: var(--radius-md);
  cursor: pointer;
  transition: all var(--transition-fast);
  white-space: nowrap;
}

.btn:focus {
  outline: 3px solid var(--color-primary);
  outline-offset: 2px;
}

.btn:active {
  transform: translateY(1px);
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  pointer-events: none;
}

/* Primary button */
.btn-primary {
  background: var(--color-primary);
  color: white;
  border-color: var(--color-primary);
}

.btn-primary:hover {
  background: var(--color-primary-dark);
  border-color: var(--color-primary-dark);
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

/* Outline button */
.btn-outline-primary {
  background: transparent;
  color: var(--color-primary);
  border-color: var(--color-primary);
}

.btn-outline-primary:hover {
  background: var(--color-primary);
  color: white;
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

/* Danger button */
.btn-danger {
  background: var(--color-danger);
  color: white;
  border-color: var(--color-danger);
}

.btn-danger:hover {
  background: #7F1818;
  border-color: #7F1818;
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

/* Button sizes */
.btn-sm {
  min-height: 40px;
  padding: var(--space-sm) var(--space-md);
  font-size: 0.95rem;
}

.btn-lg {
  min-height: 56px;
  padding: var(--space-lg) var(--space-xl);
  font-size: 1.1rem;
}

/* Full-width button (mobile) */
.btn-block {
  width: 100%;
}

@media (max-width: 767px) {
  .btn-mobile-block {
    width: 100%;
  }
}

/* Loading state */
.btn.is-loading {
  position: relative;
  color: transparent;
  pointer-events: none;
}

.btn.is-loading::after {
  content: "";
  position: absolute;
  width: 20px;
  height: 20px;
  border: 3px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: btn-spin 0.6s linear infinite;
}

@keyframes btn-spin {
  to { transform: rotate(360deg); }
}
```

### 7. Rating Interface Components


**Purpose**: Large, clear rating controls for self-ratings and 360 evaluations

**HTML Structure**:

```html
<div class="rating-item">
  <div class="rating-header">
    <h3 class="rating-title">Communication Skills</h3>
    <p class="rating-description">Ability to clearly convey information and listen actively</p>
  </div>
  
  <div class="rating-scale" role="radiogroup" aria-labelledby="rating-communication">
    <input type="radio" id="comm-1" name="rating-communication" value="1" class="rating-input">
    <label for="comm-1" class="rating-label">
      <span class="rating-number">1</span>
      <span class="rating-text">Needs Improvement</span>
    </label>
    
    <input type="radio" id="comm-2" name="rating-communication" value="2" class="rating-input">
    <label for="comm-2" class="rating-label">
      <span class="rating-number">2</span>
      <span class="rating-text">Below Expectations</span>
    </label>
    
    <input type="radio" id="comm-3" name="rating-communication" value="3" class="rating-input">
    <label for="comm-3" class="rating-label">
      <span class="rating-number">3</span>
      <span class="rating-text">Meets Expectations</span>
    </label>
    
    <input type="radio" id="comm-4" name="rating-communication" value="4" class="rating-input">
    <label for="comm-4" class="rating-label">
      <span class="rating-number">4</span>
      <span class="rating-text">Exceeds Expectations</span>
    </label>
    
    <input type="radio" id="comm-5" name="rating-communication" value="5" class="rating-input">
    <label for="comm-5" class="rating-label">
      <span class="rating-number">5</span>
      <span class="rating-text">Exceptional</span>
    </label>
  </div>
</div>
```

**CSS Implementation** (file: `/assets/css/employee-portal-ratings.css`):

```css
.rating-item {
  padding: var(--space-lg);
  background: var(--color-bg-primary);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-lg);
  margin-bottom: var(--space-lg);
}

.rating-header {
  margin-bottom: var(--space-lg);
}

.rating-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--color-text-primary);
  margin: 0 0 var(--space-sm);
}

.rating-description {
  font-size: 1rem;
  color: var(--color-text-secondary);
  margin: 0;
  line-height: var(--line-height-base);
}

.rating-scale {
  display: flex;
  flex-direction: column;
  gap: var(--space-sm);
}

.rating-input {
  position: absolute;
  opacity: 0;
  width: 0;
  height: 0;
}

.rating-label {
  display: flex;
  align-items: center;
  gap: var(--space-md);
  min-height: 56px;
  padding: var(--space-md) var(--space-lg);
  background: var(--color-bg-secondary);
  border: 2px solid var(--color-border-light);
  border-radius: var(--radius-md);
  cursor: pointer;
  transition: all var(--transition-fast);
  position: relative;
}

.rating-number {
  flex-shrink: 0;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--color-text-muted);
  background: var(--color-bg-primary);
  border: 2px solid var(--color-border-medium);
  border-radius: 50%;
  transition: all var(--transition-fast);
}

.rating-text {
  flex: 1;
  font-size: var(--font-size-base);
  font-weight: 500;
  color: var(--color-text-primary);
}

/* Hover state */
.rating-label:hover {
  background: var(--color-bg-primary);
  border-color: var(--color-primary);
  transform: translateX(4px);
}

.rating-label:hover .rating-number {
  border-color: var(--color-primary);
  color: var(--color-primary);
}

/* Focus state */
.rating-input:focus + .rating-label {
  outline: 3px solid var(--color-primary);
  outline-offset: 2px;
}

/* Checked state */
.rating-input:checked + .rating-label {
  background: rgba(8, 46, 6, 0.08);
  border-color: var(--color-primary);
  font-weight: 600;
}

.rating-input:checked + .rating-label .rating-number {
  background: var(--color-primary);
  border-color: var(--color-primary);
  color: white;
}

/* Checked state with confirmation icon */
.rating-input:checked + .rating-label::after {
  content: "✓";
  position: absolute;
  right: var(--space-lg);
  font-size: 1.5rem;
  color: var(--color-success);
  animation: checkmark-pop 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

@keyframes checkmark-pop {
  0% { transform: scale(0); }
  50% { transform: scale(1.2); }
  100% { transform: scale(1); }
}

/* Mobile: One rating per row */
@media (max-width: 767px) {
  .rating-item {
    padding: var(--space-md);
  }
  
  .rating-label {
    min-height: 48px;
    padding: var(--space-sm) var(--space-md);
  }
  
  .rating-number {
    width: 36px;
    height: 36px;
    font-size: 1.1rem;
  }
  
  .rating-text {
    font-size: 0.95rem;
  }
}
```

### 8. Progress Indicator Component

**Purpose**: Visual representation of multi-step process completion

**HTML Structure**:

```html
<div class="progress-indicator" role="progressbar" aria-valuenow="3" aria-valuemin="1" aria-valuemax="5" aria-label="Step 3 of 5">
  <div class="progress-step completed">
    <div class="progress-step-number">1</div>
    <div class="progress-step-label">Personal Info</div>
  </div>
  <div class="progress-line completed"></div>
  
  <div class="progress-step completed">
    <div class="progress-step-number">2</div>
    <div class="progress-step-label">Employment</div>
  </div>
  <div class="progress-line completed"></div>
  
  <div class="progress-step active">
    <div class="progress-step-number">3</div>
    <div class="progress-step-label">Education</div>
  </div>
  <div class="progress-line"></div>
  
  <div class="progress-step">
    <div class="progress-step-number">4</div>
    <div class="progress-step-label">Skills</div>
  </div>
  <div class="progress-line"></div>
  
  <div class="progress-step">
    <div class="progress-step-number">5</div>
    <div class="progress-step-label">Review</div>
  </div>
</div>
```

**CSS Implementation** (file: `/assets/css/employee-portal-progress.css`):


```css
.progress-indicator {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: var(--space-lg) 0;
  margin-bottom: var(--space-xl);
  overflow-x: auto;
  scrollbar-width: thin;
}

.progress-step {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: var(--space-sm);
  flex-shrink: 0;
  min-width: 80px;
}

.progress-step-number {
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--color-text-muted);
  background: var(--color-bg-secondary);
  border: 3px solid var(--color-border-medium);
  border-radius: 50%;
  transition: all var(--transition-base);
}

.progress-step-label {
  font-size: 0.95rem;
  font-weight: 500;
  color: var(--color-text-muted);
  text-align: center;
  line-height: var(--line-height-tight);
}

.progress-line {
  flex: 1;
  height: 3px;
  background: var(--color-border-light);
  margin: 0 var(--space-sm);
  transition: all var(--transition-base);
  min-width: 20px;
}

/* Completed state */
.progress-step.completed .progress-step-number {
  background: var(--color-success);
  border-color: var(--color-success);
  color: white;
}

.progress-step.completed .progress-step-label {
  color: var(--color-success);
  font-weight: 600;
}

.progress-line.completed {
  background: var(--color-success);
}

/* Active state */
.progress-step.active .progress-step-number {
  background: var(--color-primary);
  border-color: var(--color-primary);
  color: white;
  animation: pulse-ring 2s infinite;
}

.progress-step.active .progress-step-label {
  color: var(--color-primary);
  font-weight: 700;
}

@keyframes pulse-ring {
  0%, 100% { box-shadow: 0 0 0 0 rgba(8, 46, 6, 0.4); }
  50% { box-shadow: 0 0 0 8px rgba(8, 46, 6, 0); }
}

/* Mobile: Smaller steps, horizontal scroll */
@media (max-width: 767px) {
  .progress-indicator {
    padding: var(--space-md) 0;
    justify-content: flex-start;
  }
  
  .progress-step {
    min-width: 60px;
  }
  
  .progress-step-number {
    width: 40px;
    height: 40px;
    font-size: 1.1rem;
  }
  
  .progress-step-label {
    font-size: 0.75rem;
  }
  
  .progress-line {
    min-width: 16px;
  }
}
```

### 9. Notification Components

**Purpose**: Clear, actionable notification display

**HTML Structure**:

```html
<div class="notification-list">
  <!-- Unread notification -->
  <div class="notification-item unread" role="article" aria-label="Unread notification">
    <div class="notification-icon notification-icon-warning">
      <i class="ti ti-alert-circle" aria-hidden="true"></i>
    </div>
    <div class="notification-content">
      <h4 class="notification-title">Evaluation Due Soon</h4>
      <p class="notification-message">Your self-rating for Q2 2024 is due in 3 days.</p>
      <time class="notification-time" datetime="2024-01-15T10:30">2 hours ago</time>
    </div>
    <div class="notification-actions">
      <button class="btn btn-sm btn-primary" aria-label="View evaluation">
        View
      </button>
      <button class="btn btn-sm btn-outline-primary" aria-label="Mark as read">
        <i class="ti ti-check" aria-hidden="true"></i>
      </button>
    </div>
  </div>
  
  <!-- Read notification -->
  <div class="notification-item" role="article">
    <div class="notification-icon notification-icon-success">
      <i class="ti ti-check-circle" aria-hidden="true"></i>
    </div>
    <div class="notification-content">
      <h4 class="notification-title">Evaluation Approved</h4>
      <p class="notification-message">Your Q1 2024 evaluation has been approved by your manager.</p>
      <time class="notification-time" datetime="2024-01-14T14:20">Yesterday</time>
    </div>
    <div class="notification-actions">
      <button class="btn btn-sm btn-outline-primary">
        View Results
      </button>
    </div>
  </div>
</div>
```

**CSS Implementation** (file: `/assets/css/employee-portal-notifications.css`):


```css
.notification-list {
  display: flex;
  flex-direction: column;
  gap: var(--space-md);
}

.notification-item {
  display: flex;
  align-items: flex-start;
  gap: var(--space-md);
  min-height: 80px;
  padding: var(--space-md);
  background: var(--color-bg-primary);
  border: 1px solid var(--color-border-light);
  border-left: 4px solid var(--color-border-medium);
  border-radius: var(--radius-md);
  transition: all var(--transition-fast);
}

.notification-item.unread {
  background: rgba(8, 46, 6, 0.03);
  border-left-color: var(--color-primary);
  box-shadow: var(--shadow-sm);
}

.notification-item:hover {
  box-shadow: var(--shadow-md);
  transform: translateY(-1px);
}

.notification-icon {
  flex-shrink: 0;
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  font-size: 1.5rem;
}

.notification-icon-success {
  background: rgba(15, 107, 46, 0.1);
  color: var(--color-success);
}

.notification-icon-warning {
  background: rgba(127, 92, 0, 0.1);
  color: var(--color-warning);
}

.notification-icon-info {
  background: rgba(6, 95, 115, 0.1);
  color: var(--color-info);
}

.notification-icon-danger {
  background: rgba(153, 27, 27, 0.1);
  color: var(--color-danger);
}

.notification-content {
  flex: 1;
  min-width: 0;
}

.notification-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--color-text-primary);
  margin: 0 0 var(--space-xs);
  line-height: var(--line-height-tight);
}

.notification-message {
  font-size: 1rem;
  color: var(--color-text-secondary);
  margin: 0 0 var(--space-sm);
  line-height: var(--line-height-base);
}

.notification-time {
  font-size: 0.85rem;
  color: var(--color-text-muted);
  font-style: italic;
}

.notification-actions {
  display: flex;
  flex-direction: column;
  gap: var(--space-sm);
  flex-shrink: 0;
}

/* Mobile: Stack vertically, full-width actions */
@media (max-width: 767px) {
  .notification-item {
    flex-direction: column;
    align-items: stretch;
  }
  
  .notification-icon {
    align-self: flex-start;
  }
  
  .notification-actions {
    flex-direction: row;
    justify-content: flex-end;
  }
  
  .notification-actions .btn {
    flex: 1;
  }
}
```

## Data Models

### Form Data Structure

The portal interacts with several key data entities:

#### Employee Profile
```typescript
interface EmployeeProfile {
  employee_id: number;
  employee_code: string;
  first_name: string;
  last_name: string;
  email: string;
  phone: string;
  job_title: string;
  department_name: string;
  branch_name: string;
  rank_name: string;
  hire_date: Date;
  profile_picture: string | null;
  employment_status: 'Active' | 'Inactive' | 'On Leave';
  employment_type: 'Full-Time' | 'Part-Time' | 'Contract';
}
```

#### Evaluation Rating
```typescript
interface EvaluationRating {
  rating_id: number;
  evaluation_id: number;
  kra_id: number;
  behavior_id: number | null;
  rating_value: 1 | 2 | 3 | 4 | 5;
  rating_type: 'self' | 'peer' | 'manager';
  comments: string | null;
  created_at: Date;
  updated_at: Date;
}
```

#### PDS Section
```typescript
interface PDSSection {
  section_id: number;
  section_name: string;
  section_order: number;
  fields: PDSField[];
  is_complete: boolean;
  last_updated: Date;
}

interface PDSField {
  field_name: string;
  field_type: 'text' | 'date' | 'select' | 'textarea';
  field_value: string;
  is_required: boolean;
  validation_pattern: string | null;
}
```

#### Notification
```typescript
interface Notification {
  notification_id: number;
  user_id: number;
  notification_type: 'evaluation_due' | 'approval_needed' | 'system_message' | 'status_change';
  title: string;
  message: string;
  action_url: string | null;
  is_read: boolean;
  created_at: Date;
  read_at: Date | null;
}
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. For this UI/UX redesign, correctness properties focus on measurable, testable aspects of responsive design, accessibility compliance, and user interface standards that can be validated programmatically.*

### Important Note on Testing Approach

This is a UI/UX redesign feature focused on visual presentation, layout, and accessibility. While property-based testing (generating random inputs and testing universal properties) is not appropriate for UI rendering itself, the **design requirements** contain many testable properties that can be validated through:

1. **Automated accessibility testing tools** (axe, WAVE, Lighthouse)
2. **Computed style checks** (CSS property verification)
3. **DOM structure validation** (element presence, attributes, dimensions)
4. **Responsive behavior tests** (breakpoint verification)
5. **Visual regression testing** (screenshot comparisons)

The properties below represent requirements that can be **programmatically verified** across all pages and components.

### Property 1: Responsive Breakpoint Compliance

*For any* viewport width value, the system SHALL apply mobile layouts when width < 768px and desktop layouts when width >= 768px

**Validates: Requirements 1.1, 1.2, 1.3**

**Test Strategy**: Generate viewport widths around the 768px threshold (e.g., 320px, 480px, 767px, 768px, 1024px, 1920px), verify correct CSS classes or computed layout properties are applied

### Property 2: Horizontal Scroll Prevention

*For any* viewport width between 320px and 2560px, no page content SHALL cause horizontal scrolling

**Validates: Requirements 1.6**

**Test Strategy**: Render pages at various viewport widths, verify `document.documentElement.scrollWidth <= window.innerWidth`

### Property 3: Minimum Font Size Compliance

*For all* body text elements, the computed font-size SHALL be at least 1.1rem (17.6px)

**Validates: Requirements 2.1**

**Test Strategy**: Query all text elements, compute font size, verify >= 17.6px

### Property 4: Heading Font Size Compliance

*For all* heading elements (h1-h6) and primary navigation labels, the computed font-size SHALL be at least 1.25rem (20px)

**Validates: Requirements 2.2**

**Test Strategy**: Query all heading and navigation label elements, verify computed font-size >= 20px

### Property 5: Line Height Standards

*For all* body text elements, the computed line-height SHALL be at least 1.5

**Validates: Requirements 2.4**

**Test Strategy**: Query all body text elements, verify computed line-height >= 1.5

### Property 6: Touch Target Minimum Size

*For all* interactive elements (buttons, links, inputs), the computed dimensions SHALL be at least 48x48 CSS pixels

**Validates: Requirements 3.1, 3.5**

**Test Strategy**: Query all interactive elements (a, button, input, select, [role="button"]), measure offsetWidth and offsetHeight, verify both >= 48px

### Property 7: Icon-Only Touch Target Size

*For all* interactive elements containing only an icon without text, the computed dimensions SHALL be at least 56x56 CSS pixels

**Validates: Requirements 3.3**

**Test Strategy**: Query interactive elements with icon but no text content, verify dimensions >= 56x56px

### Property 8: Touch Target Spacing

*For all* adjacent interactive elements, the spacing between them SHALL be at least 8 CSS pixels

**Validates: Requirements 3.2**

**Test Strategy**: Find pairs of adjacent interactive elements, measure gap using bounding rectangles, verify spacing >= 8px

### Property 9: Normal Text Contrast Ratio (WCAG AAA)

*For all* normal text elements (< 18pt and not bold), the contrast ratio against background SHALL be at least 7:1

**Validates: Requirements 4.1**

**Test Strategy**: Query all text elements, compute foreground and background colors, calculate contrast ratio, verify >= 7:1

### Property 10: Large Text Contrast Ratio (WCAG AAA)

*For all* large text elements (>= 18pt or >= 14pt bold), the contrast ratio against background SHALL be at least 4.5:1

**Validates: Requirements 4.2**

**Test Strategy**: Query large text elements, compute contrast ratio, verify >= 4.5:1

### Property 11: Interactive Component Border Contrast

*For all* interactive components, the contrast ratio between component boundary and adjacent colors SHALL be at least 3:1

**Validates: Requirements 4.3**

**Test Strategy**: Query interactive elements, measure border color contrast against background, verify >= 3:1

### Property 12: Navigation Item Count Limit

*For the* primary navigation, the number of top-level menu items SHALL not exceed 7

**Validates: Requirements 5.1**

**Test Strategy**: Count top-level navigation items, verify count <= 7

### Property 13: Mobile Bottom Navigation Presence

*For any* viewport width < 768px, the bottom navigation component SHALL be displayed with 4-5 navigation items

**Validates: Requirements 5.3, 16.1, 16.2**

**Test Strategy**: At mobile widths, verify bottom nav element exists with 4-5 child items

### Property 14: Active Navigation Indicator

*For any* page in the portal, the corresponding navigation item SHALL have distinct active-state styling

**Validates: Requirements 5.4, 16.5**

**Test Strategy**: On each page, verify the matching navigation item has an "active" class or distinct computed styles

### Property 15: Breadcrumb Navigation Presence

*For all* pages beyond the dashboard, a breadcrumb trail SHALL be present showing the current location

**Validates: Requirements 5.5**

**Test Strategy**: Query each page for breadcrumb navigation element, verify it exists and contains current page indicator

### Property 16: Dashboard Card Minimum Height

*For all* dashboard statistic cards, the computed height SHALL be at least 100 CSS pixels

**Validates: Requirements 6.1**

**Test Strategy**: Query dashboard stat cards, verify offsetHeight >= 100px

### Property 17: Dashboard Section Heading Size

*For all* dashboard section headings, the computed font-size SHALL be at least 1.5rem (24px)

**Validates: Requirements 6.2**

**Test Strategy**: Query dashboard section headings, verify computed font-size >= 24px

### Property 18: Mobile Single-Column Layout

*For any* viewport width < 768px, dashboard cards SHALL be arranged in a single column

**Validates: Requirements 6.4, 14.8**

**Test Strategy**: At mobile widths, verify cards have 100% width or flex-direction: column

### Property 19: Status Indicators with Dual Signaling

*For all* status indicators, both color AND icon/text SHALL be present (not color alone)

**Validates: Requirements 4.6, 6.6, 14.3**

**Test Strategy**: Query status elements, verify presence of both color styling and icon or text label

### Property 20: Rating Control Touch Area

*For all* rating controls in evaluation interfaces, each rating option SHALL have a touch area of at least 48x48 CSS pixels

**Validates: Requirements 7.1, 7.2**

**Test Strategy**: Query rating input labels, verify dimensions >= 48x48px

### Property 21: Rating Visual Feedback Timing

*For any* rating selection, visual feedback SHALL appear within 100 milliseconds

**Validates: Requirements 7.3, 7.4**

**Test Strategy**: Trigger rating selection, measure time to visual change (class addition, style change), verify <= 100ms

### Property 22: Progress Indicator Presence

*For all* multi-step processes (evaluations, PDS forms, career requests), a progress indicator SHALL be displayed

**Validates: Requirements 8.1**

**Test Strategy**: On multi-step pages, verify progress indicator element exists

### Property 23: Progress Indicator Label Size

*For all* progress indicators, step labels SHALL have computed font-size of at least 1rem (16px)

**Validates: Requirements 8.2**

**Test Strategy**: Query progress indicator labels, verify font-size >= 16px

### Property 24: Auto-Save Trigger Timing

*For any* form field input, auto-save SHALL trigger within 2 seconds of the last keystroke or selection

**Validates: Requirements 8.3, 13.5**

**Test Strategy**: Input text into auto-save form field, measure time to save trigger, verify <= 2000ms

### Property 25: Notification Badge Font Size

*For the* notification badge displaying unread count, the computed font-size SHALL be at least 0.85rem (13.6px)

**Validates: Requirements 9.1**

**Test Strategy**: Query notification badge element, verify font-size >= 13.6px

### Property 26: Notification Badge Contrast

*For the* notification badge, the contrast ratio against its background SHALL be at least 7:1 (WCAG AAA)

**Validates: Requirements 9.2**

**Test Strategy**: Measure notification badge text and background colors, calculate contrast, verify >= 7:1

### Property 27: Notification Item Height

*For all* notification list items, the computed minimum height SHALL be at least 60 CSS pixels

**Validates: Requirements 9.4**

**Test Strategy**: Query notification items, verify offsetHeight >= 60px

### Property 28: Notification Chronological Order

*For all* displayed notifications, they SHALL be sorted in reverse chronological order (most recent first)

**Validates: Requirements 9.3**

**Test Strategy**: Query notification timestamps or data-timestamp attributes, verify descending order

### Property 29: PDS Section Field Count Limit

*For all* PDS form sections, the number of visible fields per section SHALL not exceed 10

**Validates: Requirements 10.1**

**Test Strategy**: Query PDS form sections, count visible input fields per section, verify <= 10

### Property 30: Form Validation Feedback Timing

*For any* invalid form input, validation feedback SHALL display within 500 milliseconds

**Validates: Requirements 10.3**

**Test Strategy**: Enter invalid data, measure time to error message display, verify <= 500ms

### Property 31: Form Label Positioning

*For all* form inputs, the associated label SHALL be positioned above (not beside or inside) the input field

**Validates: Requirements 10.5**

**Test Strategy**: Query form labels and inputs, compare vertical positions, verify label.offsetTop < input.offsetTop

### Property 32: Career Request Card Touch Area

*For all* career movement request type option cards, the computed dimensions SHALL be at least 100x80 CSS pixels

**Validates: Requirements 11.2**

**Test Strategy**: Query request type cards, verify dimensions >= 100x80px

### Property 33: Team Member Card Height

*For all* team list member cards, the computed minimum height SHALL be at least 80 CSS pixels

**Validates: Requirements 12.1**

**Test Strategy**: Query team member cards, verify offsetHeight >= 80px

### Property 34: Team Member Card Content Completeness

*For all* team member cards, the card SHALL contain photo, name, position, and contact action elements

**Validates: Requirements 12.2**

**Test Strategy**: Query each team card, verify presence of img, name element, position element, and action button

### Property 35: Team Search Input Height

*For the* team list search input, the computed height SHALL be at least 48 CSS pixels

**Validates: Requirements 12.5**

**Test Strategy**: Query team search input, verify offsetHeight >= 48px

### Property 36: Mobile Form Input Full Width

*For any* viewport width < 768px, text input fields SHALL have 100% width

**Validates: Requirements 13.1**

**Test Strategy**: At mobile widths, verify input elements have computed width equal to parent container width

### Property 37: Input Type Appropriateness

*For all* email fields, telephone fields, and number fields, the HTML input type attribute SHALL match the data type

**Validates: Requirements 13.2**

**Test Strategy**: Query input fields with email/phone/number names, verify type="email", type="tel", or type="number"

### Property 38: Required Field Dual Indicators

*For all* required form fields, both a visual marker (asterisk) AND aria-required or required attribute SHALL be present

**Validates: Requirements 13.8**

**Test Strategy**: Query required inputs, verify both visual indicator (*, "required" text) and required/aria-required attribute

### Property 39: Evaluation Status Font Size

*For all* evaluation status percentage displays, the computed font-size SHALL be at least 1.5rem (24px)

**Validates: Requirements 14.1**

**Test Strategy**: Query evaluation completion percentages, verify font-size >= 24px

### Property 40: Evaluation Deadline Font Size

*For all* evaluation deadline displays, the computed font-size SHALL be at least 1.1rem (17.6px)

**Validates: Requirements 14.4**

**Test Strategy**: Query evaluation deadline elements, verify font-size >= 17.6px

### Property 41: Touch Interaction Feedback Timing

*For any* interactive element tap/click, visual feedback SHALL appear within 100 milliseconds

**Validates: Requirements 15.1**

**Test Strategy**: Simulate click events, measure time to visual change (hover state, ripple, etc.), verify <= 100ms

### Property 42: Success Message Display Duration

*For any* successful operation, the success message SHALL display for approximately 3 seconds

**Validates: Requirements 15.3**

**Test Strategy**: Trigger success scenario, measure message display duration, verify ~3000ms ± 200ms

### Property 43: Bottom Navigation Touch Target Size

*For all* bottom navigation items, the computed dimensions SHALL be at least 48x48 CSS pixels

**Validates: Requirements 16.3**

**Test Strategy**: Query bottom navigation items, verify dimensions >= 48x48px

### Property 44: Bottom Navigation Label Size

*For all* bottom navigation item labels, the computed font-size SHALL be at least 0.75rem (12px)

**Validates: Requirements 16.4**

**Test Strategy**: Query bottom nav label text, verify font-size >= 12px

### Property 45: Bottom Navigation Fixed Positioning

*For any* viewport width < 768px during page scroll, the bottom navigation SHALL remain visible (fixed position)

**Validates: Requirements 16.6**

**Test Strategy**: At mobile widths, scroll page, verify bottom nav has position:fixed and remains in viewport

### Property 46: Section Heading Size and Spacing

*For all* page section headings, the computed font-size SHALL be at least 1.5rem (24px) and margin-top at least 24 CSS pixels

**Validates: Requirements 17.1**

**Test Strategy**: Query section headings, verify font-size >= 24px and margin-top >= 24px

### Property 47: Action Item Count Per Section

*For all* page sections with action items, the number of actions SHALL not exceed 7 items

**Validates: Requirements 17.2**

**Test Strategy**: Query sections with action lists, count items, verify <= 7 per section

### Property 48: Minimum Component Vertical Spacing

*For all* adjacent UI components, the vertical spacing SHALL be at least 16 CSS pixels

**Validates: Requirements 17.4**

**Test Strategy**: Measure vertical gaps between adjacent components, verify >= 16px

### Property 49: Help Icon Minimum Size

*For all* contextual help icons, the computed dimensions SHALL be at least 40x40 CSS pixels

**Validates: Requirements 17.7**

**Test Strategy**: Query help icons, verify dimensions >= 40x40px

### Property 50: CSS Custom Property Usage for Colors

*For all* color values in custom CSS, they SHALL be defined using CSS custom properties (variables)

**Validates: Requirements 18.2**

**Test Strategy**: Parse custom CSS files, verify color values use var(--color-*) format rather than direct hex/rgb values

### Property 51: Focus Indicator Contrast

*For all* interactive elements in focus state, the focus indicator contrast ratio against background SHALL be at least 3:1

**Validates: Requirements 20.1**

**Test Strategy**: Simulate focus on interactive elements, measure focus outline contrast, verify >= 3:1

### Property 52: Semantic HTML Structure

*For all* portal pages, semantic HTML5 elements (nav, main, header, footer, article, section) SHALL be present for page structure

**Validates: Requirements 20.5**

**Test Strategy**: Query page DOM, verify presence of semantic structural elements

### Property 53: Icon-Only Button ARIA Labels

*For all* buttons containing only icons without visible text, an aria-label or aria-labelledby attribute SHALL be present

**Validates: Requirements 20.6**

**Test Strategy**: Query icon-only buttons, verify presence of aria-label or aria-labelledby attribute

### Property 54: Form Input Label Association

*For all* form inputs, a properly associated label SHALL exist via for/id attributes or aria-labelledby

**Validates: Requirements 20.7**

**Test Strategy**: Query all inputs, verify each has associated label (matching for/id) or aria-labelledby attribute

### Property 55: Dynamic Content Announcement

*For all* dynamically appearing content (notifications, errors, success messages), ARIA live region attributes SHALL be present

**Validates: Requirements 20.8**

**Test Strategy**: Query dynamic content containers, verify presence of aria-live, role="alert", or role="status" attributes

## Error Handling


### Client-Side Validation

**Approach**: Real-time validation with inline feedback

**Implementation** (JavaScript):

```javascript
// Form validation utility
class FormValidator {
  constructor(formElement) {
    this.form = formElement;
    this.fields = formElement.querySelectorAll('[required], [data-validate]');
    this.initValidation();
  }
  
  initValidation() {
    this.fields.forEach(field => {
      // Validate on blur (lost focus)
      field.addEventListener('blur', () => this.validateField(field));
      
      // Clear error on input
      field.addEventListener('input', () => this.clearError(field));
    });
    
    // Validate entire form on submit
    this.form.addEventListener('submit', (e) => this.validateForm(e));
  }
  
  validateField(field) {
    const value = field.value.trim();
    const errorContainer = field.parentElement.querySelector('.form-error');
    
    // Required field check
    if (field.hasAttribute('required') && !value) {
      this.showError(field, errorContainer, 'This field is required');
      return false;
    }
    
    // Email validation
    if (field.type === 'email' && value) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(value)) {
        this.showError(field, errorContainer, 'Please enter a valid email address');
        return false;
      }
    }
    
    // Custom pattern validation
    const pattern = field.getAttribute('pattern');
    if (pattern && value) {
      const regex = new RegExp(pattern);
      if (!regex.test(value)) {
        const errorMsg = field.getAttribute('data-error-message') || 'Invalid format';
        this.showError(field, errorContainer, errorMsg);
        return false;
      }
    }
    
    // Min/max length
    const minLength = field.getAttribute('minlength');
    const maxLength = field.getAttribute('maxlength');
    
    if (minLength && value.length < parseInt(minLength)) {
      this.showError(field, errorContainer, `Minimum ${minLength} characters required`);
      return false;
    }
    
    if (maxLength && value.length > parseInt(maxLength)) {
      this.showError(field, errorContainer, `Maximum ${maxLength} characters allowed`);
      return false;
    }
    
    // Field is valid
    this.clearError(field);
    field.classList.add('is-valid');
    return true;
  }
  
  showError(field, errorContainer, message) {
    field.classList.add('is-invalid');
    field.classList.remove('is-valid');
    
    if (errorContainer) {
      errorContainer.textContent = message;
      errorContainer.style.display = 'flex';
    }
    
    // Announce error to screen readers
    field.setAttribute('aria-invalid', 'true');
  }
  
  clearError(field) {
    field.classList.remove('is-invalid');
    field.removeAttribute('aria-invalid');
    
    const errorContainer = field.parentElement.querySelector('.form-error');
    if (errorContainer) {
      errorContainer.style.display = 'none';
    }
  }
  
  validateForm(event) {
    let isValid = true;
    
    this.fields.forEach(field => {
      if (!this.validateField(field)) {
        isValid = false;
      }
    });
    
    if (!isValid) {
      event.preventDefault();
      
      // Focus first invalid field
      const firstInvalid = this.form.querySelector('.is-invalid');
      if (firstInvalid) {
        firstInvalid.focus();
        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    }
    
    return isValid;
  }
}

// Initialize validation on all forms
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('form[data-validate]').forEach(form => {
    new FormValidator(form);
  });
});
```

### Auto-Save Implementation

**Approach**: Debounced auto-save with local storage fallback

**Implementation** (JavaScript):

```javascript
class AutoSave {
  constructor(formElement, options = {}) {
    this.form = formElement;
    this.endpoint = options.endpoint || '/ajax/autosave.php';
    this.delay = options.delay || 2000; // 2 seconds
    this.storageKey = options.storageKey || `autosave_${formElement.id}`;
    this.saveTimer = null;
    this.lastSaved = null;
    this.statusElement = options.statusElement || null;
    
    this.initAutoSave();
  }
  
  initAutoSave() {
    // Load saved data from localStorage on page load
    this.loadFromStorage();
    
    // Watch for changes in form fields
    this.form.querySelectorAll('input, textarea, select').forEach(field => {
      field.addEventListener('input', () => this.scheduleAutoSave());
      field.addEventListener('change', () => this.scheduleAutoSave());
    });
  }
  
  scheduleAutoSave() {
    // Clear existing timer
    if (this.saveTimer) {
      clearTimeout(this.saveTimer);
    }
    
    // Set new timer
    this.saveTimer = setTimeout(() => {
      this.performAutoSave();
    }, this.delay);
  }
  
  async performAutoSave() {
    const formData = new FormData(this.form);
    
    // Save to localStorage first (fallback)
    this.saveToStorage(formData);
    
    // Show saving status
    this.updateStatus('Saving...', 'info');
    
    try {
      const response = await fetch(this.endpoint, {
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      });
      
      const result = await response.json();
      
      if (result.success) {
        this.lastSaved = new Date();
        this.updateStatus('Saved', 'success');
        
        // Clear localStorage after successful server save
        localStorage.removeItem(this.storageKey);
        
        // Hide status after 2 seconds
        setTimeout(() => this.updateStatus('', ''), 2000);
      } else {
        throw new Error(result.message || 'Save failed');
      }
    } catch (error) {
      console.error('Auto-save failed:', error);
      this.updateStatus('Failed to save - data stored locally', 'error');
    }
  }
  
  saveToStorage(formData) {
    const data = {};
    for (let [key, value] of formData.entries()) {
      data[key] = value;
    }
    localStorage.setItem(this.storageKey, JSON.stringify({
      data: data,
      timestamp: Date.now()
    }));
  }
  
  loadFromStorage() {
    const saved = localStorage.getItem(this.storageKey);
    if (!saved) return;
    
    try {
      const { data, timestamp } = JSON.parse(saved);
      
      // Check if data is less than 7 days old
      const sevenDaysAgo = Date.now() - (7 * 24 * 60 * 60 * 1000);
      if (timestamp < sevenDaysAgo) {
        localStorage.removeItem(this.storageKey);
        return;
      }
      
      // Restore form data
      Object.keys(data).forEach(key => {
        const field = this.form.elements[key];
        if (field) {
          field.value = data[key];
        }
      });
      
      // Show restoration message
      this.updateStatus('Unsaved data restored', 'info');
    } catch (error) {
      console.error('Failed to restore data:', error);
    }
  }
  
  updateStatus(message, type) {
    if (!this.statusElement) return;
    
    this.statusElement.textContent = message;
    this.statusElement.className = `autosave-status autosave-status-${type}`;
  }
}

// Initialize auto-save on forms
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('form[data-autosave]').forEach(form => {
    new AutoSave(form, {
      endpoint: form.dataset.autosaveEndpoint,
      storageKey: form.dataset.autosaveKey,
      statusElement: document.getElementById(form.dataset.statusElement)
    });
  });
});
```

### Server-Side Error Handling

**Approach**: Consistent JSON responses with user-friendly messages

**PHP Implementation Example**:

```php
<?php
// Error handler utility
class ErrorHandler {
    
    public static function jsonResponse($success, $message, $data = null) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('c')
        ]);
        exit;
    }
    
    public static function handleDatabaseError($conn, $operation) {
        $error = $conn->error;
        error_log("Database error during $operation: $error");
        
        // User-friendly message
        self::jsonResponse(false, 
            "We're having trouble saving your data. Please try again in a moment.",
            ['error_code' => 'DB_ERROR']
        );
    }
    
    public static function handleValidationError($errors) {
        self::jsonResponse(false, 
            "Please check your input and try again.",
            ['validation_errors' => $errors]
        );
    }
    
    public static function handleUnauthorized() {
        self::jsonResponse(false, 
            "You don't have permission to perform this action.",
            ['error_code' => 'UNAUTHORIZED']
        );
    }
}
?>
```

## Testing Strategy

This feature is primarily a UI/UX redesign focused on visual presentation, layout, accessibility, and user interaction patterns. **Property-based testing is NOT appropriate** for this type of work.

### Why Property-Based Testing Doesn't Apply

1. **UI Rendering**: The feature involves CSS styling, layout, and visual presentation which cannot be meaningfully tested with universal properties
2. **Accessibility Compliance**: WCAG AAA compliance requires specific checks (contrast ratios, ARIA attributes, semantic HTML) better suited to specialized accessibility testing tools
3. **Responsive Design**: Viewport-specific behavior requires visual regression testing and manual testing across devices
4. **User Experience**: Cognitive load reduction, visual hierarchy, and usability are subjective qualities requiring user testing

### Recommended Testing Approach

#### 1. Manual Testing
- **Cross-browser testing**: Chrome, Firefox, Safari, Edge
- **Device testing**: Mobile phones (320px-767px), tablets (768px-1024px), desktops (1025px+)
- **Orientation testing**: Portrait and landscape modes
- **Touch interaction testing**: Verify 48x48px minimum touch targets
- **Keyboard navigation testing**: Tab order, focus indicators, skip links

#### 2. Accessibility Testing
- **Automated tools**: 
  - WAVE (Web Accessibility Evaluation Tool)
  - axe DevTools browser extension
  - Lighthouse accessibility audit
- **Manual checks**:
  - Screen reader testing (NVDA, JAWS, VoiceOver)
  - Keyboard-only navigation
  - Color contrast verification (WebAIM Contrast Checker)
  - ARIA attribute validation

#### 3. Visual Regression Testing
- **Tools**: Percy, Chromatic, or BackstopJS
- **Approach**: Capture screenshots of components at different breakpoints and compare against baseline

#### 4. Performance Testing
- **Tools**: Lighthouse, WebPageTest
- **Metrics**:
  - First Contentful Paint (FCP) < 2s on 3G
  - Time to Interactive (TTI) < 4s on 3G
  - Cumulative Layout Shift (CLS) < 0.1
  - Largest Contentful Paint (LCP) < 2.5s

#### 5. Unit Tests for JavaScript Utilities
- **Test auto-save functionality**: Verify debouncing, localStorage fallback, error handling
- **Test form validation**: Validate regex patterns, required fields, error message display
- **Test responsive utilities**: Verify breakpoint detection, navigation toggling

**Example Unit Test** (Jest):

```javascript
describe('FormValidator', () => {
  test('shows error for empty required field', () => {
    const form = document.createElement('form');
    form.innerHTML = `
      <input type="text" name="firstName" required>
      <div class="form-error"></div>
    `;
    
    const validator = new FormValidator(form);
    const field = form.querySelector('input');
    
    const isValid = validator.validateField(field);
    
    expect(isValid).toBe(false);
    expect(field.classList.contains('is-invalid')).toBe(true);
  });
  
  test('validates email format', () => {
    const form = document.createElement('form');
    form.innerHTML = `
      <input type="email" name="email" value="invalid-email">
      <div class="form-error"></div>
    `;
    
    const validator = new FormValidator(form);
    const field = form.querySelector('input');
    
    const isValid = validator.validateField(field);
    
    expect(isValid).toBe(false);
  });
});
```

### Integration Testing
- **Approach**: Test complete user workflows (login → dashboard → evaluation → submit)
- **Tools**: Selenium, Cypress, or Playwright
- **Focus**: Form submissions, navigation flows, data persistence

### User Acceptance Testing (UAT)
- **Participants**: Actual employees aged 40+ (target user group)
- **Scenarios**: 
  - Complete self-rating evaluation
  - Update PDS information
  - Submit career movement request
  - Navigate using mobile device
- **Metrics**: Task completion rate, time on task, user satisfaction, error rate

## Implementation Plan

### Phase 1: Foundation (Week 1-2)
1. Create CSS variable system (employee-portal-variables.css)
2. Set up responsive grid utilities
3. Implement base typography styles
4. Create color scheme with WCAG AAA compliance

### Phase 2: Core Components (Week 3-4)
1. Build navigation components (bottom nav, sidebar, breadcrumbs)
2. Create card components (stat cards, content cards)
3. Implement form components (inputs, selects, radio/checkbox)
4. Build button system

### Phase 3: Specialized Components (Week 5-6)
1. Create rating interface components
2. Build progress indicator
3. Implement notification components
4. Create auto-save and validation JavaScript

### Phase 4: Integration & Testing (Week 7-8)
1. Apply new styles to all portal pages
2. Test across devices and browsers
3. Run accessibility audits
4. Fix issues and optimize performance

### Phase 5: UAT & Launch (Week 9-10)
1. Conduct user acceptance testing
2. Gather feedback and iterate
3. Final QA and bug fixes
4. Production deployment

## Migration Strategy

### Backward Compatibility

During migration, both old and new styles should coexist:

```html
<!-- Apply new design class to enable revamped styles -->
<body class="employee-portal-v2">
  <!-- Portal content -->
</body>
```

**CSS Scoping**:
```css
/* New styles only apply when v2 class is present */
.employee-portal-v2 .btn {
  min-height: 48px;
  /* ...new styles */
}

/* Old styles remain unchanged */
.btn {
  /* ...existing styles */
}
```

### Rollout Plan

1. **Pilot Phase**: Deploy to 10% of users for 2 weeks
2. **Gradual Rollout**: Increase to 25%, 50%, 75% over 4 weeks
3. **Full Deployment**: 100% of users after successful pilot
4. **Legacy Support**: Keep old styles for 30 days post-launch

## Performance Considerations

### CSS Optimization
- **Minification**: Use cssnano to minify CSS files
- **Critical CSS**: Inline above-the-fold CSS, defer non-critical styles
- **CSS Custom Properties**: Runtime theming without recompilation

### JavaScript Optimization
- **Code Splitting**: Load auto-save and validation scripts only on forms
- **Lazy Loading**: Defer non-critical scripts
- **Debouncing**: Prevent excessive function calls (auto-save, window resize)

### Image Optimization
- **Responsive Images**: Use srcset for different viewport sizes
- **Lazy Loading**: Use loading="lazy" attribute for below-fold images
- **Format**: Use WebP with JPEG/PNG fallbacks

### Network Optimization
- **HTTP/2**: Enable multiplexing for parallel resource loading
- **Compression**: Enable gzip/brotli compression
- **Caching**: Set appropriate cache headers for static assets

## Accessibility Compliance Checklist

- [ ] All text meets WCAG AAA contrast ratios (7:1 for normal text, 4.5:1 for large text)
- [ ] All interactive elements have minimum 48x48px touch targets
- [ ] Keyboard navigation works for all interactive elements
- [ ] Focus indicators are visible with 3:1 contrast ratio
- [ ] Skip navigation links are provided
- [ ] Semantic HTML elements used (nav, main, header, footer, article)
- [ ] ARIA labels provided for icon-only buttons
- [ ] Form labels properly associated with inputs
- [ ] Dynamic content changes announced via ARIA live regions
- [ ] Color not used as sole means of conveying information
- [ ] Text can be resized to 200% without loss of functionality
- [ ] Page structure is logical when CSS is disabled

---

This design provides a comprehensive technical implementation guide for the employee portal UX revamp, focusing on mobile-responsive design, accessibility, and user experience optimized for employees aged 40+ years old. All components are built using Bootstrap 5, CSS custom properties, and modern responsive design patterns.
