# Implementation Plan: Employee Portal UX Revamp

## Overview

This implementation plan breaks down the comprehensive UI/UX redesign of the Raquel Pawnshop HRIS employee portal into actionable coding tasks. The redesign focuses on mobile-responsive design, WCAG AAA accessibility compliance, enhanced typography and touch targets, simplified navigation, and reduced cognitive load for employees aged 40+.

The implementation follows a component-based approach, starting with foundational CSS variables and layout systems, then building reusable UI components (cards, forms, buttons, ratings), followed by page-specific implementations, JavaScript utilities (auto-save, validation), and finishing with accessibility compliance and performance optimization.

## Tasks

- [ ] 1. Set up CSS variable system and foundational styles
  - [ ] 1.1 Create CSS variable definitions file
    - Create `/assets/css/employee-portal-variables.css`
    - Define all color variables (primary, secondary, text, status, borders) with WCAG AAA compliant values
    - Define spacing variables based on 8px base unit (xs, sm, md, lg, xl, 2xl, 3xl, touch-min, touch-comfortable)
    - Define typography variables (font families, sizes, line heights, letter spacing)
    - Define shadow variables (sm, md, lg, xl)
    - Define border radius variables (sm, md, lg, xl, full)
    - Define transition timing variables (fast, base, slow)
    - _Requirements: 4.5, 18.2, 18.3, 18.4, 18.5_
  
  - [ ] 1.2 Create base layout CSS file
    - Create `/assets/css/employee-portal-layout.css`
    - Implement container-fluid with horizontal scroll prevention
    - Implement mobile-first single-column layout with flex-direction: column
    - Implement responsive grid for tablet/desktop (768px+) using CSS Grid
    - Apply max-width: 100% and word-wrap: break-word to all elements
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.6, 18.8_

- [ ] 2. Build navigation components
  - [ ] 2.1 Implement mobile bottom navigation
    - Create bottom navigation HTML structure in `/includes/header.php` or separate include
    - Create `/assets/css/employee-portal-navigation.css` with bottom-nav styles
    - Style bottom navigation with fixed positioning, 48px minimum touch targets
    - Add active state styling with color and background distinction
    - Add notification badge styling with WCAG AAA contrast
    - Implement 4-5 primary navigation items (Dashboard, Evaluations, Notifications, Profile, More)
    - Add body padding-bottom to reserve space for fixed bottom nav on mobile
    - _Requirements: 5.1, 5.3, 5.4, 9.1, 9.2, 16.1, 16.2, 16.3, 16.4, 16.5, 16.6_
  
  - [ ] 2.2 Implement desktop sidebar navigation
    - Create sidebar HTML structure with logo, title, and navigation links
    - Add sidebar CSS styling with fixed positioning, 260px width, vertical layout
    - Style sidebar links with 48px minimum height, hover effects, active state
    - Implement main content left margin adjustment (260px) for desktop
    - Ensure sidebar is hidden on mobile (<768px) and visible on desktop (>=768px)
    - _Requirements: 5.1, 5.2, 5.4, 5.6_
  
  - [ ] 2.3 Implement breadcrumb navigation component
    - Create breadcrumb HTML structure with proper ARIA labels
    - Style breadcrumbs with separator, link hover states, active page indicator
    - Ensure breadcrumbs appear on all pages beyond dashboard
    - Apply minimum 8px spacing between breadcrumb items
    - _Requirements: 5.5_

- [ ] 3. Checkpoint - Verify navigation accessibility
  - Ensure all tests pass, verify navigation components work across devices, ask the user if questions arise.

- [ ] 4. Create card components
  - [ ] 4.1 Implement stat card component for dashboard
    - Create stat card HTML structure with icon, value, and label
    - Style stat cards in `/assets/css/employee-portal-cards.css` with 100px minimum height
    - Implement responsive flex layout (horizontal desktop, vertical mobile)
    - Add stat-icon variants (success, warning, info, danger) with background colors
    - Style stat-value with 2.25rem font size and stat-label with 0.95rem uppercase
    - Add hover transform and shadow effects
    - _Requirements: 6.1, 6.3, 6.6, 14.3_
  
  - [ ] 4.2 Implement generic content card component
    - Create content card HTML structure with header, title, and body sections
    - Style content-card with border, border-radius, shadow, and overflow handling
    - Style content-card-header with flex layout, section title (1.5rem), and action buttons
    - Style content-card-body with appropriate padding (lg on desktop, md on mobile)
    - _Requirements: 6.2, 17.1_

- [ ] 5. Build form components
  - [ ] 5.1 Implement text input fields with labels and validation
    - Create form-group HTML structure with label, input, helper text, and error container
    - Style form-label with 1rem font size, 600 weight, required indicator (asterisk)
    - Style form-control with 48px minimum height, 2px border, focus states
    - Implement form-helper text styling (0.95rem, muted color)
    - Implement form-error styling with icon, red border, background, and ARIA live region
    - Add is-invalid and is-valid state classes
    - _Requirements: 3.5, 10.4, 10.5, 13.1, 13.3, 13.8, 20.7_
  
  - [ ] 5.2 Implement radio button and checkbox components
    - Create radio-group and checkbox-group HTML structures with fieldset and legend
    - Hide native radio/checkbox inputs with position: absolute and opacity: 0
    - Style custom radio-label and checkbox-label with 48px minimum height, padding, borders
    - Implement custom visual indicators using ::before pseudo-elements
    - Style checked state with ::after pseudo-elements (filled circle for radio, checkmark for checkbox)
    - Add hover, focus, and checked state styling with color changes and shadows
    - _Requirements: 3.1, 3.5, 13.7_
  
  - [ ] 5.3 Implement character counter for text inputs
    - Create char-counter HTML element to display "X/Y characters"
    - Style character counter with 0.85rem font size, right alignment
    - Add warning class (yellow) when approaching limit, danger class (red) when at limit
    - _Requirements: 13.4_

- [ ] 6. Create button components
  - [ ] 6.1 Implement button base styles and variants
    - Create `/assets/css/employee-portal-buttons.css` for button styling
    - Style .btn base class with 48px minimum height, flex layout, padding, border-radius
    - Implement .btn-primary with primary color background, white text
    - Implement .btn-outline-primary with transparent background, primary border
    - Implement .btn-danger for destructive actions
    - Add hover, focus, active, and disabled states for all button variants
    - Implement button size variants (.btn-sm 40px, .btn-lg 56px)
    - Add .btn-block and .btn-mobile-block for full-width buttons
    - _Requirements: 3.1, 3.4, 6.3, 9.6, 12.7, 14.7, 15.1, 15.2_
  
  - [ ] 6.2 Implement button loading state
    - Add .is-loading class styling with spinner animation
    - Create ::after pseudo-element for loading spinner with rotation animation
    - Disable pointer events and make text transparent during loading
    - _Requirements: 15.2_

- [ ] 7. Checkpoint - Verify form and button accessibility
  - Ensure all tests pass, verify forms work with keyboard navigation and screen readers, ask the user if questions arise.

- [ ] 8. Implement rating interface components
  - [ ] 8.1 Create rating item structure for evaluations
    - Create rating-item HTML with rating-header (title, description) and rating-scale
    - Style rating-item in `/assets/css/employee-portal-ratings.css` with card layout, border, padding
    - Style rating-title (1.25rem, bold) and rating-description (1rem, secondary color)
    - _Requirements: 7.5, 7.6, 7.7, 7.8_
  
  - [ ] 8.2 Style rating scale controls
    - Hide native radio inputs with position: absolute, opacity: 0
    - Style rating-label with 56px minimum height, flex layout, border, padding
    - Style rating-number as circular indicator (40px circle with border)
    - Style rating-text with 1.1rem font size, 500 weight
    - Implement hover state with transform and border color change
    - Implement checked state with primary color fill, checkmark animation
    - Add checkmark-pop animation using ::after pseudo-element
    - Reduce to 48px height on mobile devices
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.9, 7.10_

- [ ] 9. Create progress indicator component
  - [ ] 9.1 Implement multi-step progress visualization
    - Create progress-indicator HTML with progress-step and progress-line elements
    - Create `/assets/css/employee-portal-progress.css` for progress styling
    - Style progress-step with flex column layout, 48px circular number, label text
    - Style progress-step-number as circle with border (muted by default)
    - Style progress-line as horizontal bar connecting steps (3px height)
    - Implement .completed state (green background, white text for numbers, green lines)
    - Implement .active state (primary color, pulse-ring animation)
    - Add horizontal scroll support and reduce size on mobile (40px numbers)
    - _Requirements: 8.1, 8.2, 8.7_

- [ ] 10. Implement notification components
  - [ ] 10.1 Create notification list and item structure
    - Create notification-list and notification-item HTML structure
    - Create `/assets/css/employee-portal-notifications.css` for notification styling
    - Style notification-item with flex layout, 80px minimum height, border-left accent
    - Implement .unread class with background tint and primary border-left color
    - Style notification-icon variants (success, warning, info, danger) with colored backgrounds
    - Style notification-content with title (1.1rem bold), message (1rem), and time (0.85rem italic)
    - Style notification-actions with flex column layout for action buttons
    - Stack vertically on mobile with full-width actions
    - _Requirements: 9.3, 9.4, 9.5, 9.6, 9.7_

- [ ] 11. Checkpoint - Verify rating, progress, and notification components
  - Ensure all tests pass, verify components render correctly and are accessible, ask the user if questions arise.

- [ ] 12. Implement dashboard page enhancements
  - [ ] 12.1 Apply responsive grid layout to dashboard
    - Update `/employee/dashboard.php` with new grid structure using Bootstrap 5 classes
    - Wrap statistics in stat-card components with icons and values
    - Use .row and .col-12 .col-md-6 for responsive 2-column layout on desktop
    - Add dashboard-grid class for auto-fit grid layout on larger screens
    - _Requirements: 1.1, 1.2, 1.3, 6.1, 6.4_
  
  - [ ] 12.2 Organize dashboard quick actions with visual hierarchy
    - Group related quick actions with section headings (1.5rem size)
    - Limit quick actions to maximum 6 items per section
    - Apply primary button styling to highest-priority actions
    - Add icons to action buttons for visual clarity
    - _Requirements: 6.2, 6.3, 6.5, 17.2_
  
  - [ ] 12.3 Apply status indicators with dual signaling
    - Ensure all status badges use both color and icon or text label
    - Implement status color variants (success, warning, danger, info)
    - _Requirements: 6.6, 14.3_

- [ ] 13. Update self-rating and 360-evaluation pages
  - [ ] 13.1 Apply rating interface to self-rating page
    - Update `/employee/self-rating.php` with new rating-item components
    - Separate KRA ratings and behavior ratings into clearly labeled sections
    - Apply rating-scale components to all rating inputs
    - Add section headings (1.5rem) for each rating category
    - Ensure single-column layout on mobile with adequate spacing
    - _Requirements: 7.1, 7.3, 7.5, 7.7, 7.9_
  
  - [ ] 13.2 Apply rating interface to 360-evaluation pages
    - Update peer/manager evaluation pages with rating-item components
    - Implement same section separation and rating-scale components
    - Apply consistent styling across all evaluation types
    - _Requirements: 7.2, 7.4, 7.6, 7.8, 7.10_
  
  - [ ] 13.3 Add progress indicator to evaluation pages
    - Insert progress-indicator component showing evaluation completion status
    - Display step numbers and labels for evaluation sections
    - Update progress state (completed, active, pending) based on user progress
    - _Requirements: 8.1, 8.2, 14.1, 14.2, 14.6_

- [ ] 14. Enhance profile and PDS management pages
  - [ ] 14.1 Reorganize PDS form into logical sections
    - Update `/employee/my-pds.php` with section-based layout
    - Limit each section to maximum 10 fields
    - Add section headings (1.5rem) with clear visual separation
    - Apply form-group structure to all input fields
    - _Requirements: 10.1, 10.2, 17.1, 17.4_
  
  - [ ] 14.2 Implement form validation feedback
    - Add validation error display containers to all form fields
    - Style validation messages with error icons and descriptive text
    - Position validation messages directly below associated fields (1rem font size)
    - Implement is-invalid class application on validation failure
    - _Requirements: 10.3, 10.4, 13.8_
  
  - [ ] 14.3 Apply proper label positioning and required indicators
    - Position all form labels above input fields (not beside or inside)
    - Add required-indicator spans (asterisks) to required fields
    - Add aria-required="true" attributes to required fields
    - Ensure labels are properly associated with inputs using for/id attributes
    - _Requirements: 10.5, 13.8, 20.7_
  
  - [ ] 14.4 Optimize form inputs for mobile
    - Apply appropriate input types (tel, email, number, date) to trigger correct keyboards
    - Set minimum 48px height on all form inputs
    - Add placeholder text or helper text with instructions/examples
    - Implement date picker styling optimized for touch
    - _Requirements: 10.6, 13.1, 13.2, 13.3_
  
  - [ ] 14.5 Add PDS section completion indicator
    - Display visual indicator showing which PDS sections are complete
    - Use checkmark icons and color coding for completed sections
    - Highlight incomplete sections that need attention
    - _Requirements: 10.8_

- [ ] 15. Checkpoint - Verify dashboard, evaluation, and profile pages
  - Ensure all tests pass, verify page layouts work on mobile and desktop, ask the user if questions arise.

- [ ] 16. Update career movement request interface
  - [ ] 16.1 Implement request type selection cards
    - Update `/employee/career-movement-request.php` with card-based request type selection
    - Style request type cards with minimum 100x80px touch areas
    - Add icons and descriptions to each request type (promotion, transfer, position change)
    - _Requirements: 11.2_
  
  - [ ] 16.2 Add progress indicator to career request form
    - Insert progress-indicator showing step-by-step guidance
    - Display eligibility requirements before allowing submission
    - _Requirements: 11.1, 11.3_
  
  - [ ] 16.3 Create confirmation and status tracking displays
    - Design confirmation screen with request number and expected timeline
    - Create status tracking page with visual indicators for approval stages
    - _Requirements: 11.4, 11.5_

- [ ] 17. Enhance team list display
  - [ ] 17.1 Apply card layout to team member list
    - Update `/employee/team-list.php` with card-based layout
    - Style team member cards with 80px minimum height
    - Display photo, name, position, and contact action for each member
    - Apply single-column layout on mobile, 2-3 column grid on desktop
    - _Requirements: 12.1, 12.2, 12.3, 12.4_
  
  - [ ] 17.2 Implement team search and filter functionality
    - Add search input field with 48px minimum height
    - Style filter buttons with clear labels for department, position, status
    - Ensure contact action buttons (email, message) have 40x40px minimum touch areas
    - _Requirements: 12.5, 12.6, 12.7_

- [ ] 18. Enhance notification system interface
  - [ ] 18.1 Update notification panel with new components
    - Update `/employee/notifications.php` with notification-list component
    - Display notifications in reverse chronological order (most recent first)
    - Apply notification-item styling with unread/read states
    - Add "Mark All Read" button with proper touch target size
    - _Requirements: 9.1, 9.2, 9.3, 9.4, 9.8_
  
  - [ ] 18.2 Implement notification badge on navigation
    - Add notification badge to notification icon in bottom nav and sidebar
    - Display unread count with 0.85rem minimum font size
    - Apply WCAG AAA contrast (7:1) for badge background and text
    - _Requirements: 9.1, 9.2_

- [ ] 19. Implement JavaScript auto-save utility
  - [ ] 19.1 Create auto-save JavaScript module
    - Create `/assets/js/auto-save.js` with auto-save functionality
    - Implement debounced save trigger (2 seconds after last keystroke)
    - Add AJAX call to save endpoint with form data
    - Store unsaved data in localStorage as backup
    - _Requirements: 8.3, 8.5, 13.5_
  
  - [ ] 19.2 Add auto-save feedback messages
    - Display success confirmation message for 2 seconds after successful save
    - Display error message (persistent until dismissed) on save failure
    - Restore unsaved data from localStorage when user returns to incomplete forms
    - _Requirements: 8.4, 8.5, 8.6, 15.3, 15.4_
  
  - [ ] 19.3 Integrate auto-save with evaluation and PDS forms
    - Apply auto-save to self-rating and 360-evaluation rating inputs
    - Apply auto-save to PDS form fields
    - Apply auto-save to career request forms
    - Add data-autosave attribute to forms that should trigger auto-save
    - _Requirements: 8.3, 10.7_

- [ ] 20. Implement form validation JavaScript utility
  - [ ] 20.1 Create client-side validation module
    - Create `/assets/js/form-validation.js` with validation logic
    - Implement real-time validation on blur and input events
    - Display validation feedback within 500ms of invalid input
    - Apply is-invalid and is-valid classes to form controls
    - _Requirements: 10.3, 10.4_
  
  - [ ] 20.2 Add character counter functionality
    - Track character count for inputs with maxlength attributes
    - Update character counter display in real-time
    - Apply warning class when approaching limit (90%), danger class at limit
    - _Requirements: 13.4_
  
  - [ ] 20.3 Integrate validation with all forms
    - Apply validation to PDS forms with required field checks
    - Apply validation to career request forms
    - Apply validation to profile settings forms
    - Display validation errors in form-error containers
    - _Requirements: 10.3, 13.8_

- [ ] 21. Checkpoint - Verify JavaScript utilities functionality
  - Ensure all tests pass, verify auto-save and validation work correctly, ask the user if questions arise.

- [ ] 22. Implement visual feedback system
  - [ ] 22.1 Add touch interaction feedback to all interactive elements
    - Create utility CSS classes for hover, focus, and active states
    - Apply ripple or scale animation on tap/click (complete within 100ms)
    - Add transition effects to all buttons, links, and form controls
    - _Requirements: 15.1, 15.6_
  
  - [ ] 22.2 Implement loading indicators
    - Add loading spinners for AJAX operations
    - Add skeleton screens for page content loading
    - Display loading state on submit buttons (disable button, show spinner)
    - _Requirements: 15.2, 15.5_
  
  - [ ] 22.3 Add success and error message displays
    - Create toast notification system for success messages (3 second duration)
    - Create persistent error message displays (remain until dismissed)
    - Add checkmark icons to success messages, warning icons to error messages
    - Implement ARIA live regions for screen reader announcements
    - _Requirements: 15.3, 15.4, 20.8_

- [ ] 23. Implement accessibility enhancements
  - [ ] 23.1 Add focus indicators to all interactive elements
    - Apply visible focus outline (3:1 contrast minimum) to all focusable elements
    - Use outline or box-shadow for focus indicators (2-3px width)
    - Ensure focus indicators are not removed with outline: none without replacement
    - _Requirements: 20.1_
  
  - [ ] 23.2 Ensure keyboard navigation support
    - Verify Tab key navigates through interactive elements in logical order
    - Verify Enter and Space keys activate buttons and links
    - Add skip navigation links to bypass repetitive elements
    - Test navigation order matches visual layout
    - _Requirements: 20.2, 20.3, 20.4_
  
  - [ ] 23.3 Apply semantic HTML structure
    - Use semantic elements (nav, main, header, footer, article, section) appropriately
    - Ensure heading hierarchy is logical (h1, h2, h3 in proper order)
    - Add landmark roles where semantic elements are not used
    - _Requirements: 20.5_
  
  - [ ] 23.4 Add ARIA labels and attributes
    - Add aria-label to icon-only buttons and links
    - Add aria-labelledby or for/id associations for form labels
    - Add aria-required to required form fields
    - Add aria-live regions for dynamic content updates
    - Add aria-expanded, aria-current, and other state attributes as needed
    - _Requirements: 20.6, 20.7, 20.8_

- [ ] 24. Optimize for performance
  - [ ] 24.1 Implement lazy loading for images
    - Add loading="lazy" attribute to images below the fold
    - Implement intersection observer for progressive image loading
    - Use responsive image sizes with srcset for different viewport widths
    - _Requirements: 19.3, 19.6_
  
  - [ ] 24.2 Minify and concatenate CSS and JavaScript
    - Combine all employee portal CSS files into a single minified file
    - Combine all employee portal JavaScript files into a single minified file
    - Set up build process or manual minification for production assets
    - _Requirements: 19.4, 19.5_
  
  - [ ] 24.3 Defer non-critical JavaScript
    - Add defer or async attributes to non-critical script tags
    - Load auto-save and validation scripts after initial page render
    - Prioritize critical rendering path (above-the-fold content first)
    - _Requirements: 19.7_
  
  - [ ] 24.4 Optimize initial page render speed
    - Inline critical CSS for above-the-fold content
    - Reduce HTTP requests by combining resources
    - Ensure above-the-fold content renders within 2 seconds on 3G
    - Test with Chrome DevTools throttling (Fast 3G setting)
    - _Requirements: 19.1, 19.2_

- [ ] 25. Checkpoint - Final accessibility and performance verification
  - Ensure all tests pass, run Lighthouse audit and accessibility checker, ask the user if questions arise.

- [ ] 26. Conduct cross-browser and cross-device testing
  - [ ] 26.1 Test responsive layouts at key breakpoints
    - Test at 320px width (smallest mobile)
    - Test at 375px width (common mobile)
    - Test at 768px width (tablet/desktop threshold)
    - Test at 1024px width (desktop)
    - Test at 1920px width (large desktop)
    - Verify no horizontal scrolling at any width
    - _Requirements: 1.1, 1.2, 1.3, 1.6_
  
  - [ ] 26.2 Test orientation changes
    - Test portrait to landscape orientation change on mobile devices
    - Verify content reflows within 300ms without data loss
    - Ensure layouts adapt correctly to both orientations
    - _Requirements: 1.5_
  
  - [ ] 26.3 Test touch interactions on mobile devices
    - Verify all touch targets are at least 48x48px (56x56px for icon-only)
    - Verify 8px minimum spacing between adjacent touch targets
    - Test tap feedback appears within 100ms
    - Test rating selections provide immediate visual confirmation
    - _Requirements: 3.1, 3.2, 3.3, 7.3, 7.4, 15.1_
  
  - [ ] 26.4 Test with assistive technologies
    - Test with screen reader (NVDA, JAWS, or VoiceOver)
    - Verify all interactive elements are announced correctly
    - Verify form labels and validation messages are read aloud
    - Verify skip navigation and ARIA live regions work
    - _Requirements: 20.1, 20.2, 20.3, 20.4, 20.5, 20.6, 20.7, 20.8_

- [ ] 27. Create documentation and handoff materials
  - [ ] 27.1 Document CSS variable usage
    - Create developer guide explaining CSS variable system
    - Document how to add new colors, spacing, or typography values
    - Document component class naming conventions
    - _Requirements: 18.2, 18.3, 18.4, 18.5, 18.7_
  
  - [ ] 27.2 Document component library
    - Create HTML examples for each reusable component
    - Document component variants and modifiers
    - Provide usage guidelines for cards, forms, buttons, ratings, progress, notifications
    - _Requirements: 17.3_
  
  - [ ] 27.3 Create maintenance guide
    - Document responsive breakpoint strategy
    - Document accessibility requirements (WCAG AAA compliance)
    - Document performance optimization techniques used
    - Provide testing checklist for future changes
    - _Requirements: 1.1, 4.1, 4.2, 4.3, 19.1, 19.2_

- [ ] 28. Final checkpoint - Complete verification and user acceptance
  - Ensure all tests pass, verify all pages work correctly across devices, demonstrate to user for acceptance, ask the user if questions arise.

## Notes

- This is a UI/UX redesign feature focused on styling, layout, and accessibility improvements
- Testing focuses on automated accessibility tools (Lighthouse, WAVE, axe), computed style verification, DOM structure validation, and responsive behavior testing rather than property-based testing
- All pages should be tested at mobile (<768px) and desktop (>=768px) breakpoints
- CSS custom properties enable centralized theming and easy future modifications
- Progressive enhancement ensures core functionality works without JavaScript
- WCAG AAA compliance (7:1 contrast for normal text, 4.5:1 for large text) is a strict requirement
- Component-based architecture promotes reusability and consistency across pages
- Checkpoints ensure incremental validation and allow user feedback at key milestones
- Documentation tasks help with future maintenance and onboarding

## Task Dependency Graph

```json
{
  "waves": [
    {
      "id": 0,
      "tasks": ["1.1", "1.2"]
    },
    {
      "id": 1,
      "tasks": ["2.1", "2.2", "2.3", "4.1", "4.2"]
    },
    {
      "id": 2,
      "tasks": ["5.1", "5.2", "5.3", "6.1", "6.2"]
    },
    {
      "id": 3,
      "tasks": ["8.1", "8.2", "9.1", "10.1"]
    },
    {
      "id": 4,
      "tasks": ["12.1", "12.2", "12.3", "13.1", "13.2"]
    },
    {
      "id": 5,
      "tasks": ["13.3", "14.1", "14.2", "14.3", "14.4", "14.5"]
    },
    {
      "id": 6,
      "tasks": ["16.1", "16.2", "16.3", "17.1", "17.2", "18.1", "18.2"]
    },
    {
      "id": 7,
      "tasks": ["19.1", "19.2", "20.1", "20.2"]
    },
    {
      "id": 8,
      "tasks": ["19.3", "20.3", "22.1", "22.2", "22.3"]
    },
    {
      "id": 9,
      "tasks": ["23.1", "23.2", "23.3", "23.4"]
    },
    {
      "id": 10,
      "tasks": ["24.1", "24.2", "24.3", "24.4"]
    },
    {
      "id": 11,
      "tasks": ["26.1", "26.2", "26.3", "26.4"]
    },
    {
      "id": 12,
      "tasks": ["27.1", "27.2", "27.3"]
    }
  ]
}
```
