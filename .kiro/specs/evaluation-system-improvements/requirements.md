# Requirements Document

## Introduction

This document specifies requirements for enhancing the self-rating evaluation system in the Raquel HRD System. The improvements address critical security vulnerabilities, performance bottlenecks, user experience issues, data integrity concerns, and missing functionality. The enhancements maintain backward compatibility with existing evaluations while providing a more secure, performant, and user-friendly evaluation workflow.

## Glossary

- **System**: The Raquel HRD evaluation module including self-rating, confirmation, and review workflows
- **Evaluation_Engine**: The backend PHP components responsible for evaluation processing, routing, and storage
- **Database_Layer**: The MySQL database and all query execution components
- **Input_Validator**: The component responsible for validating and sanitizing user inputs
- **Score_Processor**: The component that calculates KRA subtotals, behavior averages, and total scores
- **Workflow_Router**: The component that determines evaluation routing based on employee roles and hierarchy
- **Notification_Service**: The component that sends notifications to users
- **Session_Manager**: The component that manages user sessions and draft evaluation data
- **Audit_Logger**: The component that records evaluation changes and status transitions
- **Query_Cache**: The in-memory cache for frequently accessed database queries
- **Transaction_Manager**: The component that manages database transactions
- **Access_Controller**: The component that enforces role-based access controls
- **CSRF_Token**: Cross-Site Request Forgery token for form security
- **Prepared_Statement**: Parameterized SQL query that prevents injection attacks
- **Composite_Index**: Database index on multiple columns for query optimization

## Requirements

### Requirement 1: Secure Input Handling

**User Story:** As a system administrator, I want all user inputs to be validated and sanitized, so that the system is protected from injection attacks and malicious data.

#### Acceptance Criteria

1. THE Input_Validator SHALL validate all score inputs to ensure values are within the range 0 to 4
2. WHEN a score value outside the range 0 to 4 is submitted, THE Input_Validator SHALL reject the input and return a descriptive error message
3. THE Input_Validator SHALL sanitize all text inputs to remove malicious code
4. WHEN evaluation data is submitted, THE Input_Validator SHALL validate all required fields are present
5. THE Database_Layer SHALL use Prepared_Statements for all SQL queries involving user inputs

### Requirement 2: SQL Injection Prevention

**User Story:** As a security officer, I want all database queries to use prepared statements, so that SQL injection attacks are prevented.

#### Acceptance Criteria

1. THE Database_Layer SHALL replace all string concatenation queries with Prepared_Statements in self-rating.php
2. THE Database_Layer SHALL replace all string concatenation queries with Prepared_Statements in confirm-rating.php
3. THE Database_Layer SHALL replace all string concatenation queries with Prepared_Statements in dept-manager-review.php
4. THE Database_Layer SHALL replace all string concatenation queries with Prepared_Statements in dashboard.php
5. WHEN a database query is executed, THE Database_Layer SHALL use parameter binding to prevent injection

### Requirement 3: CSRF Protection

**User Story:** As a security officer, I want all forms to be protected against CSRF attacks, so that unauthorized actions cannot be performed on behalf of users.

#### Acceptance Criteria

1. THE System SHALL generate a unique CSRF_Token for each user session
2. WHEN an evaluation form is rendered, THE System SHALL include the CSRF_Token as a hidden field
3. WHEN a form is submitted, THE System SHALL validate the CSRF_Token matches the session token
4. IF the CSRF_Token is invalid or missing, THEN THE System SHALL reject the request and return an error

### Requirement 4: Query Performance Optimization

**User Story:** As a system user, I want evaluation pages to load quickly, so that I can work efficiently without delays.

#### Acceptance Criteria

1. THE Database_Layer SHALL use JOIN queries to fetch related evaluation data in a single query instead of N+1 separate queries
2. THE Database_Layer SHALL create Composite_Index on (employee_id, status, deleted_at) in the evaluations table
3. THE Database_Layer SHALL create Composite_Index on (evaluation_id, criterion_id) in the evaluation_scores table
4. THE Database_Layer SHALL create Composite_Index on (user_id, is_read, created_at) in the notifications table
5. WHEN evaluation history is requested, THE Database_Layer SHALL fetch all related data in a single query using JOINs

### Requirement 5: Query Result Caching

**User Story:** As a system user, I want frequently accessed data to be cached, so that pages load faster.

#### Acceptance Criteria

1. THE Query_Cache SHALL cache supervisor lookup results for 5 minutes
2. THE Query_Cache SHALL cache HR role lookup results for 5 minutes
3. THE Query_Cache SHALL cache department manager lookup results for 5 minutes
4. WHEN cached data is updated in the database, THE Query_Cache SHALL invalidate the corresponding cache entries
5. THE Query_Cache SHALL use employee_id as the cache key for lookups

### Requirement 6: Auto-save Draft Evaluations

**User Story:** As an employee, I want my evaluation drafts to be automatically saved, so that I don't lose my work if my session expires.

#### Acceptance Criteria

1. WHEN an employee enters a score value, THE Session_Manager SHALL save the draft to the database within 30 seconds
2. WHEN an employee returns to an incomplete evaluation, THE System SHALL restore all previously entered scores
3. THE Session_Manager SHALL save draft evaluations every 30 seconds while the form is active
4. WHEN a draft evaluation is saved, THE System SHALL display a visual confirmation to the user
5. THE Session_Manager SHALL maintain draft status until the employee explicitly submits or discards the evaluation

### Requirement 7: Progress Indication

**User Story:** As an employee, I want to see my progress while completing an evaluation, so that I know how much work remains.

#### Acceptance Criteria

1. THE System SHALL display the total number of evaluation criteria on the form
2. THE System SHALL display the number of completed criteria as the employee progresses
3. THE System SHALL display a progress percentage based on completed criteria divided by total criteria
4. WHEN an employee completes a criterion, THE System SHALL update the progress indicator
5. THE System SHALL display a visual progress bar showing percentage completion

### Requirement 8: Inline Validation

**User Story:** As an employee, I want to see validation errors immediately, so that I can correct them without waiting for form submission.

#### Acceptance Criteria

1. WHEN an employee enters an invalid score value, THE System SHALL display an error message next to the field within 500 milliseconds
2. WHEN an employee leaves a required field empty, THE System SHALL display a warning indicator next to the field
3. THE System SHALL validate score fields in real-time as values are entered
4. WHEN all required fields are completed, THE System SHALL enable the submit button
5. WHILE required fields are incomplete, THE System SHALL disable the submit button

### Requirement 9: Comparison View

**User Story:** As an employee, I want to compare my self-rating with supervisor adjustments, so that I can understand the differences.

#### Acceptance Criteria

1. WHEN a supervisor modifies scores, THE System SHALL display both the original self-rating and the adjusted score
2. THE System SHALL visually highlight criteria where scores were adjusted
3. THE System SHALL calculate the difference between self-rating and adjusted scores
4. WHEN an employee views a confirmed evaluation, THE System SHALL display a side-by-side comparison of self-rating versus supervisor rating
5. THE System SHALL display supervisor comments explaining score adjustments

### Requirement 10: Concurrency Control

**User Story:** As a system administrator, I want to prevent concurrent edits to the same evaluation, so that data conflicts are avoided.

#### Acceptance Criteria

1. WHEN a user opens an evaluation for editing, THE Transaction_Manager SHALL acquire an exclusive lock on that evaluation record
2. IF another user attempts to edit the same evaluation, THEN THE System SHALL display a message indicating the evaluation is locked
3. WHEN a user saves or cancels an evaluation edit, THE Transaction_Manager SHALL release the lock
4. THE Transaction_Manager SHALL automatically release locks after 15 minutes of inactivity
5. THE System SHALL display the name of the user who currently holds the lock

### Requirement 11: Status Transition Validation

**User Story:** As a system administrator, I want evaluation status transitions to be validated, so that invalid workflow states are prevented.

#### Acceptance Criteria

1. THE Workflow_Router SHALL define valid status transitions for each evaluation status
2. WHEN an evaluation status update is attempted, THE Workflow_Router SHALL verify the transition is valid
3. IF an invalid status transition is attempted, THEN THE System SHALL reject the update and log an error
4. THE Workflow_Router SHALL enforce that Draft evaluations can only transition to Pending Self-Rating or Submitted
5. THE Workflow_Router SHALL enforce that Approved evaluations cannot transition to any other status

### Requirement 12: Audit Trail for Score Changes

**User Story:** As an HR manager, I want to see a complete history of score changes, so that I can audit evaluation modifications.

#### Acceptance Criteria

1. WHEN a supervisor modifies a score, THE Audit_Logger SHALL record the original value, new value, user_id, and timestamp
2. WHEN a manager modifies a score, THE Audit_Logger SHALL record the original value, new value, user_id, and timestamp
3. THE Audit_Logger SHALL record the criterion name for each score modification
4. WHEN an evaluation is viewed, THE System SHALL display the complete audit trail of score changes
5. THE Audit_Logger SHALL record the reason for score changes if provided by the supervisor or manager

### Requirement 13: Notification Batching

**User Story:** As a user, I want to receive consolidated notifications, so that I am not overwhelmed by notification spam.

#### Acceptance Criteria

1. THE Notification_Service SHALL batch notifications for the same evaluation into a single notification
2. WHEN multiple notifications for the same user are generated within 5 minutes, THE Notification_Service SHALL consolidate them
3. THE Notification_Service SHALL provide a digest option for users to receive daily notification summaries
4. WHEN a user enables digest mode, THE Notification_Service SHALL send one notification per day containing all pending items
5. THE Notification_Service SHALL allow users to configure notification preferences

### Requirement 14: Orphaned Record Cleanup

**User Story:** As a database administrator, I want orphaned records to be automatically cleaned up, so that database integrity is maintained.

#### Acceptance Criteria

1. WHEN an evaluation is deleted, THE Database_Layer SHALL cascade delete all associated evaluation_scores records
2. WHEN an evaluation is deleted, THE Database_Layer SHALL cascade delete all associated evaluation_dev_plans records
3. THE Database_Layer SHALL add ON DELETE CASCADE constraints to foreign keys in evaluation_scores table
4. THE Database_Layer SHALL add ON DELETE CASCADE constraints to foreign keys in evaluation_dev_plans table
5. THE System SHALL provide a cleanup job that removes orphaned records created before cascading constraints were added

### Requirement 15: Bulk Evaluation Assignment

**User Story:** As an HR manager, I want to assign evaluations to multiple employees at once, so that I can efficiently manage evaluation cycles.

#### Acceptance Criteria

1. THE System SHALL provide a bulk assignment interface for HR managers
2. THE System SHALL allow HR managers to select multiple employees by department, branch, or individual selection
3. WHEN employees are selected, THE System SHALL allow the HR manager to choose an evaluation template
4. WHEN a bulk assignment is submitted, THE System SHALL create evaluation records for all selected employees
5. THE Notification_Service SHALL send assignment notifications to all selected employees

### Requirement 16: Email Notifications

**User Story:** As a user, I want to receive email notifications for important evaluation events, so that I don't miss critical actions.

#### Acceptance Criteria

1. WHEN an evaluation is assigned to an employee, THE Notification_Service SHALL send an email notification
2. WHEN a supervisor confirms an evaluation, THE Notification_Service SHALL send an email notification to the employee
3. WHEN an evaluation is returned for revision, THE Notification_Service SHALL send an email notification to the employee
4. WHEN an evaluation is approved, THE Notification_Service SHALL send an email notification to the employee
5. THE Notification_Service SHALL allow users to opt out of email notifications

### Requirement 17: Reminder System

**User Story:** As an HR manager, I want automated reminders to be sent for pending evaluations, so that evaluation cycles are completed on time.

#### Acceptance Criteria

1. THE System SHALL send a reminder notification to employees with evaluations in Draft status for more than 7 days
2. THE System SHALL send a reminder notification to supervisors with evaluations in Pending Confirmation status for more than 3 days
3. THE System SHALL send a reminder notification to HR staff with evaluations in Pending HR Consolidation status for more than 5 days
4. THE System SHALL allow HR managers to configure reminder intervals
5. THE System SHALL allow HR managers to disable reminders for specific evaluation templates

### Requirement 18: Export Functionality

**User Story:** As an HR manager, I want to export evaluations to PDF and Excel, so that I can share and archive evaluation data.

#### Acceptance Criteria

1. THE System SHALL provide a PDF export option for individual evaluations
2. THE System SHALL provide an Excel export option for multiple evaluations
3. WHEN an evaluation is exported to PDF, THE System SHALL include all scores, comments, and audit history
4. WHEN evaluations are exported to Excel, THE System SHALL include employee details, scores, status, and dates
5. THE System SHALL allow HR managers to export evaluations filtered by department, branch, date range, or status

### Requirement 19: Evaluation Template Versioning

**User Story:** As an HR manager, I want to track template changes over time, so that I can maintain consistency and audit template modifications.

#### Acceptance Criteria

1. THE System SHALL maintain a version history for each evaluation template
2. WHEN a template is modified, THE System SHALL create a new version and preserve the previous version
3. THE System SHALL display the version number and modification date for each template
4. WHEN an evaluation is created, THE System SHALL record the template version used
5. THE System SHALL prevent modification of templates that are in use by active evaluations

### Requirement 20: Analytics Dashboard

**User Story:** As an HR manager, I want to see analytics on evaluation completion rates, so that I can monitor progress and identify bottlenecks.

#### Acceptance Criteria

1. THE System SHALL display the total number of evaluations by status
2. THE System SHALL display evaluation completion rate by department
3. THE System SHALL display evaluation completion rate by branch
4. THE System SHALL display average time to complete evaluations by status
5. THE System SHALL display a chart showing evaluation status distribution

### Requirement 21: Consistent Error Handling

**User Story:** As a developer, I want consistent error handling across the system, so that debugging and maintenance are easier.

#### Acceptance Criteria

1. THE System SHALL use try-catch blocks for all database operations
2. WHEN a database error occurs, THE System SHALL log the error with timestamp, user_id, and query details
3. WHEN an error occurs, THE System SHALL display a user-friendly error message
4. THE System SHALL return appropriate HTTP status codes for different error types
5. THE System SHALL send error notifications to system administrators for critical errors

### Requirement 22: Parser and Serializer for Evaluation Data

**User Story:** As a developer, I want to serialize and parse evaluation data consistently, so that data integrity is maintained during export and import operations.

#### Acceptance Criteria

1. WHEN evaluation data is exported, THE Parser SHALL serialize the evaluation object to JSON format
2. WHEN evaluation data is imported, THE Parser SHALL parse JSON format into an evaluation object
3. THE Parser SHALL validate the structure of evaluation data during parsing
4. IF evaluation data fails validation during parsing, THEN THE Parser SHALL return a descriptive error message
5. FOR ALL valid evaluation objects, parsing then serializing then parsing SHALL produce an equivalent object (round-trip property)

### Requirement 23: Magic Number Elimination

**User Story:** As a developer, I want configuration values to be defined as constants, so that the code is maintainable and changes are centralized.

#### Acceptance Criteria

1. THE System SHALL define rank_category_id values as named constants
2. THE System SHALL define KRA and Behavior weight percentages as configuration settings
3. THE System SHALL define status transition rules as configuration settings
4. THE System SHALL define notification intervals as configuration settings
5. THE System SHALL store all configuration settings in a centralized configuration file or database table

### Requirement 24: Reduced Code Nesting

**User Story:** As a developer, I want to refactor deeply nested code, so that it is easier to read and maintain.

#### Acceptance Criteria

1. THE Workflow_Router SHALL use early returns to reduce nesting depth
2. THE Score_Processor SHALL extract complex calculations into separate functions
3. THE System SHALL limit code nesting to a maximum of 3 levels
4. THE System SHALL use guard clauses to handle edge cases early
5. THE System SHALL extract complex conditional logic into well-named functions

### Requirement 25: Backward Compatibility

**User Story:** As a system administrator, I want improvements to maintain backward compatibility, so that existing evaluations continue to work without disruption.

#### Acceptance Criteria

1. THE System SHALL continue to support all existing evaluation statuses
2. THE System SHALL continue to support all existing evaluation templates
3. WHEN database schema changes are applied, THE System SHALL migrate existing data without loss
4. THE System SHALL provide fallback logic for evaluations created before improvements were implemented
5. THE System SHALL validate that all existing evaluations remain accessible after improvements are deployed
