# Evaluation System Automation & Scheduling Plan

This document outlines the proposed strategies for handling the dynamic generation of employee evaluations based on their status and reference dates as described in `Evaluation_Template_addition.txt`.

## User Review Required

> [!IMPORTANT]
> Please review the proposed database changes and the logic for the transition from Probationary to Regular. Since standard PHP does not have a persistent background worker, we need to decide whether to use a daily Cron Job (Task Scheduler in Windows) or evaluate schedules dynamically when a manager/HR logs in.

## Open Questions

> [!WARNING]
> 1. **Reference Dates:** Do we agree to add explicit `regularization_date` and `separation_date` fields to the `employees` table?
> 2. **Evaluation Frequency (Quarterly):** For Probationary employees, is it strictly every 3 months indefinitely until regularized, or is it the standard PH practice of 3rd month and 5th month evaluations before the 6th month regularization?
> 3. **Cron Job vs. On-Demand:** Are we able to set up a Cron Job (Task Scheduler) on the server to run a daily evaluation checker, or should the system generate evaluations on-the-fly when HR visits the Evaluations Dashboard?

## Proposed Changes

### 1. Database Adjustments

We need to add new anchor dates to the `employees` table so that we don't solely rely on `hire_date`.

#### [MODIFY] [1st_schema_tables.sql](file:///c:/xampp/htdocs/FINAL_RAQUEL_PAWNSHOP_HRD/database/1st_schema_tables.sql)
- **Table:** `employees`
- **Additions:**
  - `regularization_date DATE NULL`
  - `separation_date DATE NULL`
  - `probation_end_date DATE NULL` (Optional, to notify HR to evaluate before it ends)

### 2. Evaluation Reference Date Logic

We will define clear rules for each Evaluation Type:

- **Initial Evaluation**: Triggered as a one-time event shortly after `hire_date`. 
- **Quarterly Evaluation**: Anchored to `hire_date`. The system will generate these at `hire_date + 3 months` and `hire_date + 6 months` (if still probationary).
- **Annual Evaluation**: Anchored to `regularization_date`. This avoids schedule shifting if an employee gets delayed in regularization. Generated at `regularization_date + 1 year`, `+ 2 years`, etc.
- **Final Evaluation**: Anchored to `separation_date`. Triggered when `employment_status` is updated to any separated status (e.g., Resignation, Termination).

### 3. Handling the Probationary → Regular Transition

When HR updates an employee's status from `Probationary` to `Regular`:
1. The HR user must provide the `regularization_date` in the UI prompt.
2. The backend will update the employee's `employment_status` to `Regular` and save the `regularization_date`.
3. The backend will automatically **cancel or void** any pending Quarterly evaluations that are no longer applicable.
4. The system's scheduling logic will now look at `regularization_date` and queue the next Annual evaluation 1 year from that date.

### 4. Automated Evaluation Generator (Cron Job / Daily Task)

To automate this, we will create a script `generate_evaluations.php` in a new `cron/` or `jobs/` directory.
- **Logic:**
  1. Fetch all active employees.
  2. For `Probationary` employees: Check if `CURRENT_DATE >= hire_date + 3 months` (and no evaluation exists for this period). If true, insert an evaluation record.
  3. For `Regular` employees: Check if `CURRENT_DATE >= regularization_date + 1 year` (and no evaluation exists for this year). If true, insert an evaluation record.
  4. The generated evaluations will have status `Draft` or `Pending Self-Rating`.
- **Execution:** Set up a Windows Task Scheduler (since it's XAMPP) or Linux Cron Job to curl or run this PHP script every day at 12:00 AM.

## Verification Plan

### Manual Verification
- We will manually update an employee's `hire_date` and `employment_status` to test the script's evaluation generation.
- Change a Probationary employee to Regular and verify that their next evaluation defaults to the Annual template based on their `regularization_date`.
- We need the client to confirm the open questions before proceeding with database and code updates.
