# Implementation Plan: Performance Rating System Alignment

This plan outlines the steps to align the existing HRIS evaluation module with the 7-step workflow and role-based permissions defined in the `performance_rating_system.html` documentation.

## 1. Database Schema Updates
To support the "Identification of KRA/KPI by Heads" (Step 1) and tracking assignments, the following changes are required:

*   **`evaluations` Table**:
    *   Add `status` value: `'Pending Self-Rating'` to the ENUM.
    *   Add `assigned_by` (INT): Foreign key to `users.user_id` to track which Head initiated the evaluation.
    *   Add `assigned_at` (DATETIME): To track when the process was started.
*   **`evaluation_criteria` Table**:
    *   (Optional) Add `is_custom` (TINYINT): To flag KRAs that were specifically modified by a Head for a single employee evaluation.

## 2. Step 1: Identification Workflow (Heads)
Currently, employees pick their own templates. We will shift initiation to the Heads (Supervisors/Managers).

*   **New Page: `supervisor/assign-evaluation.php`**:
    *   **Employee Selection**: Head selects a subordinate.
    *   **Template Selection**: Head picks a base template (e.g., "Annual Review").
    *   **KRA Customization**: Head can review and "Identify" specific KRAs for that employee.
    *   **Submission**: Once saved, a record is created in `evaluations` with status `Pending Self-Rating`.
*   **Notification**: The employee receives a notification: *"Your Head has identified your KRAs. Please proceed with your self-rating."*

## 3. Step 2-3: Self-Rating (Employees)
*   **Update `employee/self-rating.php`**:
    *   **Priority List**: Display "Assigned Evaluations" at the top of the page.
    *   **Locked Templates**: If an evaluation was assigned by a Head, the template selection is locked, and the KRAs identified by the Head are pre-loaded.
    *   **Self-Rating**: Employee encodes their scores and submits (Status changes to `Pending Head Review`).

## 4. Step 4-7: Review & Final Approval
*   **Update `supervisor/pending-endorsements.php`**:
    *   Handles Step 4 (Receive), Step 5 (Edit/Approve), and Step 6 (Submit final).
*   **Update `manager/pending-approvals.php`**:
    *   Handles Step 7 (HR Admin/Manager receipt and final approval).

## 5. Role-Based Access Enforcement
We will implement the strict permission matrix defined in the documentation:

| Role | Permissions | Enforcement Logic |
| :--- | :--- | :--- |
| **HR Staff** | **View All** | Remove all "Edit", "Approve", or "Return" buttons; show "View" only. |
| **HR Supervisor** | **Edit R&F Only** | Allow "Endorse/Approve" only if `rank_category_id = 5` (Rank & File). |
| **HR Manager** | **Edit All** | Unrestricted access to all evaluation actions. |

## 6. UI/UX Enhancements
*   **Process Tracker**: Add a visual 7-step progress bar (from the HTML mockup) to the top of evaluation detail modals and pages.
*   **Badge System**: Integrate the premium badge styles (`badge-head`, `badge-employee`, `badge-hr`) for consistency.

## 7. Execution Steps
1.  **Step 1**: Execute SQL migrations for table updates.
2.  **Step 2**: Build the `assign-evaluation.php` interface for Supervisors.
3.  **Step 3**: Refactor `self-rating.php` to handle Head-initiated assignments.
4.  **Step 4**: Implement the Rank-based restriction logic in Supervisor and Manager portals.
5.  **Step 5**: Design and integrate the Process Tracker UI component.
