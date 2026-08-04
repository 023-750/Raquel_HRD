-- =====================================================
-- RAQUEL HRIS -- SYSTEM TESTING & ANALYTICS SEED FILE
-- database/testing_seed.sql
-- =====================================================
-- Purpose: Populate realistic seed data to test:
--   1. Branch Employee Evaluation Routing Scenarios (All Departments)
--      - Scenario A: Branch with BOTH Supervisor & Manager (Pending Dept Supervisor)
--      - Scenario B: Branch with ONLY Branch Supervisor (Endorsed -> Pending HR Consolidation)
--      - Scenario C: Branch with ONLY Branch Manager (Pending Dept Manager)
--      - Scenario D: Branch with NEITHER Supervisor NOR Manager (Pending HR Consolidation)
--   2. HR Supervisor & HR Manager Review Stages (HR Specific Flow)
--      - Pending HR Supervisor Review (Pending Supervisor)
--      - Pending HR Manager Approval (Pending Manager)
--      - Pending HR Consolidation Pool
--   3. Career Progression Movements (Multi-Department)
--      - Pending Promotion, Transfer, Role Change, Demotion
--      - Historic Approved & Rejected movements for analytics
--   4. Analytics Dashboard Baseline Data
--      - At least 2 submitted/approved evaluations per department
--      - Criteria score breakdowns in evaluation_scores
--      - Development plans in evaluation_dev_plans
-- =====================================================
-- Template IDs by Department:
--   Human Resources      : 100-104
--   Acquired Properties  : 105-109
--   Audit                : 110-114
--   Business Development : 115-119
--   Compliance           : 120-124
--   Finance              : 125-129
--   General Services     : 130-134
--   Information Technology: 135-139
--   Marketing            : 140-144
--   Office of President  : 145-149
--   Operations           : 150-154
--   Purchasing           : 155-159
-- =====================================================

USE raquel_hris;
SET FOREIGN_KEY_CHECKS = 0;

-- ======================================================
-- CLEAN UP: Remove all previous test seed records
-- ======================================================
DELETE FROM evaluation_scores WHERE evaluation_id BETWEEN 9001 AND 9099;
DELETE FROM evaluation_dev_plans WHERE evaluation_id BETWEEN 9001 AND 9099;
DELETE FROM evaluations WHERE evaluation_id BETWEEN 9001 AND 9099;
DELETE FROM career_movements WHERE movement_id BETWEEN 9001 AND 9030;

-- ======================================================
-- SECTION 1: EVALUATIONS -- ALL DEPARTMENTS
-- ======================================================

-- =========================================================
-- HR DEPARTMENT (HR-specific flow: bypasses branch leaders)
-- employee 302  = HR Staff I     -> Pending Supervisor
-- employee 301  = HR Supervisor I -> Pending Manager
-- =========================================================

REPLACE INTO evaluations (
    evaluation_id, employee_id, template_id, evaluation_type,
    evaluation_period_start, evaluation_period_end, submitted_by,
    status, total_score, kra_subtotal, behavior_average, performance_level,
    submitted_date, staff_comments, current_position, months_in_position,
    desired_position, target_date, career_growth_suited, career_growth_details,
    created_at, updated_at
) VALUES
-- HR Staff -> Pending HR Supervisor
(9001, 302, 100, 'Annual', '2025-01-01', '2025-12-31',
 (SELECT user_id FROM users WHERE employee_id = 302 LIMIT 1),
 'Pending Supervisor', 3.55, 3.50, 3.75, 'Very Satisfactory',
 '2025-12-10 09:00:00', 'Processed over 150 applications this year and improved onboarding documentation accuracy.',
 'HR Staff I', 12, 'HR Supervisor I', '2026-09-01', 1, 'Seeking supervisory advancement in talent acquisition.', NOW(), NOW()),

-- HR Staff (2nd eval - approved, baseline analytics)
(9002, 7013, 100, 'Annual', '2024-01-01', '2024-12-31',
 (SELECT user_id FROM users WHERE employee_id = 7013 LIMIT 1),
 'Approved', 3.40, 3.35, 3.60, 'Very Satisfactory',
 '2024-12-05 10:00:00', 'Handled employee records with zero filing errors for the year.',
 'HR Staff II', 18, 'HR Supervisor II', '2026-06-01', 1, 'Interested in HR operations management.', NOW(), NOW()),

-- HR Supervisor -> Pending HR Manager
(9003, 301, 101, 'Annual', '2025-01-01', '2025-12-31',
 (SELECT user_id FROM users WHERE employee_id = 301 LIMIT 1),
 'Pending Manager', 3.80, 3.75, 4.00, 'Outstanding',
 '2025-12-08 11:00:00', 'Successfully conducted 12 administrative hearings with zero DOLE compliance findings.',
 'HR Supervisor I', 24, 'HR Manager I', '2027-01-01', 1, 'Aspiring to lead the employee relations division.', NOW(), NOW());


-- =========================================================
-- ACQUIRED PROPERTIES DEPARTMENT (dept_id=1)
-- Template: 105 (Annual), 106 (Quarterly)
-- 1009 = AP Staff on Probation
-- 1010 = AP Staff I
-- 1006 = AP Supervisor I
-- =========================================================

REPLACE INTO evaluations (
    evaluation_id, employee_id, template_id, evaluation_type,
    evaluation_period_start, evaluation_period_end, submitted_by,
    status, total_score, kra_subtotal, behavior_average, performance_level,
    submitted_date, staff_comments, current_position, months_in_position,
    desired_position, target_date, career_growth_suited,
    created_at, updated_at
) VALUES
-- AP Staff (R&F) -> Pending Dept Supervisor (branch has supervisor)
(9010, 1010, 105, 'Annual', '2025-01-01', '2025-12-31',
 (SELECT user_id FROM users WHERE employee_id = 1010 LIMIT 1),
 'Pending Dept Supervisor', 3.35, 3.30, 3.55, 'Very Satisfactory',
 '2025-12-12 09:30:00', 'Successfully assisted in 8 property auction events this year.',
 'AP Staff I', 16, 'AP Staff III', '2026-12-31', 0, NOW(), NOW()),

-- AP Staff II -> Approved (analytics baseline)
(9011, 1011, 105, 'Annual', '2024-01-01', '2024-12-31',
 (SELECT user_id FROM users WHERE employee_id = 1011 LIMIT 1),
 'Approved', 3.60, 3.55, 3.80, 'Very Satisfactory',
 '2024-12-02 08:45:00', 'Maintained accurate property inventory database with 98% match rate.',
 'AP Staff II', 10, 'AP Staff IV', '2026-06-01', 1, NOW(), NOW()),

-- AP Supervisor -> Pending Dept Manager (supervisor is submitting to their manager)
(9012, 1006, 106, 'Quarterly', '2025-10-01', '2025-12-31',
 (SELECT user_id FROM users WHERE employee_id = 1006 LIMIT 1),
 'Pending Dept Manager', 3.70, 3.65, 3.90, 'Very Satisfactory',
 '2025-12-15 14:00:00', 'Led appraisal of 34 acquired properties with zero BSP non-compliance.',
 'AP Supervisor I', 14, 'AP Manager I', '2027-01-01', 1, NOW(), NOW());


-- =========================================================
-- AUDIT DEPARTMENT (dept_id=2)
-- Template: 110 (Annual), 111 (Quarterly)
-- 2007 = Auditor on Probation
-- 2008 = Auditor I
-- 2004 = Audit Supervisor I
-- =========================================================

REPLACE INTO evaluations (
    evaluation_id, employee_id, template_id, evaluation_type,
    evaluation_period_start, evaluation_period_end, submitted_by,
    status, total_score, kra_subtotal, behavior_average, performance_level,
    submitted_date, staff_comments, current_position, months_in_position,
    desired_position, target_date, career_growth_suited,
    created_at, updated_at
) VALUES
-- Auditor (R&F) -> Pending Dept Supervisor
(9020, 2008, 110, 'Annual', '2025-01-01', '2025-12-31',
 (SELECT user_id FROM users WHERE employee_id = 2008 LIMIT 1),
 'Pending Dept Supervisor', 3.25, 3.20, 3.45, 'Satisfactory',
 '2025-12-11 10:00:00', 'Completed 22 branch audits with detailed working papers per cycle.',
 'Auditor I', 8, 'Auditor III', '2027-01-01', 0, NOW(), NOW()),

-- Auditor II -> Approved (analytics baseline)
(9021, 2009, 110, 'Annual', '2024-01-01', '2024-12-31',
 (SELECT user_id FROM users WHERE employee_id = 2009 LIMIT 1),
 'Approved', 3.50, 3.45, 3.75, 'Very Satisfactory',
 '2024-12-07 09:30:00', 'Zero repeat findings across all audit assignments for the year.',
 'Auditor II', 14, 'Audit Supervisor I', '2027-06-01', 1, NOW(), NOW()),

-- Audit Supervisor -> Pending HR Consolidation (supervisor level, no dept manager above in same dept)
(9022, 2004, 111, 'Quarterly', '2025-10-01', '2025-12-31',
 (SELECT user_id FROM users WHERE employee_id = 2004 LIMIT 1),
 'Pending HR Consolidation', 3.85, 3.80, 4.00, 'Outstanding',
 '2025-12-16 11:30:00', 'Delivered highest branch audit coverage rate across Q4 cycle.',
 'Audit Supervisor I', 20, 'Audit Manager I', '2027-01-01', 1, NOW(), NOW());


-- =========================================================
-- BUSINESS DEVELOPMENT DEPARTMENT (dept_id=3)
-- Template: 115 (Annual)
-- 3003 = BD Staff I
-- 3004 = BD Staff II
-- 3001 = BD Officer I (Manager-level)
-- =========================================================

REPLACE INTO evaluations (
    evaluation_id, employee_id, template_id, evaluation_type,
    evaluation_period_start, evaluation_period_end, submitted_by,
    status, total_score, kra_subtotal, behavior_average, performance_level,
    submitted_date, staff_comments, current_position, months_in_position,
    desired_position, target_date, career_growth_suited,
    created_at, updated_at
) VALUES
-- BD Staff I -> Pending HR Consolidation (BD has no supervisor rank in branch, only Manager-level)
(9030, 3003, 115, 'Annual', '2025-01-01', '2025-12-31',
 (SELECT user_id FROM users WHERE employee_id = 3003 LIMIT 1),
 'Pending Dept Manager', 3.20, 3.15, 3.40, 'Satisfactory',
 '2025-12-13 09:00:00', 'Contributed to 3 new branch partnership program proposals.',
 'Business Development Staff I', 20, 'Business Development Officer I', '2027-01-01', 1, NOW(), NOW()),

-- BD Staff II -> Approved (analytics baseline)
(9031, 3004, 115, 'Annual', '2024-01-01', '2024-12-31',
 (SELECT user_id FROM users WHERE employee_id = 3004 LIMIT 1),
 'Approved', 3.40, 3.35, 3.60, 'Very Satisfactory',
 '2024-12-01 08:00:00', 'Successfully supported 2 new branch feasibility studies.',
 'Business Development Staff II', 6, 'Business Development Staff III', '2026-06-01', 0, NOW(), NOW());


-- =========================================================
-- COMPLIANCE DEPARTMENT (dept_id=4)
-- Template: 120 (Annual), 121 (Quarterly)
-- 4004 = Compliance Staff I
-- 4005 = Compliance Staff II
-- 4001 = Compliance Supervisor I
-- =========================================================

REPLACE INTO evaluations (
    evaluation_id, employee_id, template_id, evaluation_type,
    evaluation_period_start, evaluation_period_end, submitted_by,
    status, total_score, kra_subtotal, behavior_average, performance_level,
    submitted_date, staff_comments, current_position, months_in_position,
    desired_position, target_date, career_growth_suited,
    created_at, updated_at
) VALUES
-- Compliance Staff I -> Pending Dept Supervisor
(9040, 4004, 120, 'Annual', '2025-01-01', '2025-12-31',
 (SELECT user_id FROM users WHERE employee_id = 4004 LIMIT 1),
 'Pending Dept Supervisor', 3.30, 3.25, 3.50, 'Satisfactory',
 '2025-12-10 10:30:00', 'Maintained 100% compliance checklist submission rate for all monitored branches.',
 'Compliance Staff I', 10, 'Compliance Staff III', '2027-01-01', 0, NOW(), NOW()),

-- Compliance Staff II -> Approved (analytics baseline)
(9041, 4005, 120, 'Annual', '2024-01-01', '2024-12-31',
 (SELECT user_id FROM users WHERE employee_id = 4005 LIMIT 1),
 'Approved', 3.55, 3.50, 3.75, 'Very Satisfactory',
 '2024-12-05 09:00:00', 'Zero AMLA compliance violations logged across all monitored units.',
 'Compliance Staff II', 4, 'Compliance Supervisor I', '2027-01-01', 1, NOW(), NOW()),

-- Compliance Supervisor -> Pending HR Consolidation (supervisor level, no dept manager above)
(9042, 4001, 121, 'Quarterly', '2025-10-01', '2025-12-31',
 (SELECT user_id FROM users WHERE employee_id = 4001 LIMIT 1),
 'Pending HR Consolidation', 3.75, 3.70, 3.95, 'Outstanding',
 '2025-12-17 13:00:00', 'Led compliance improvement program resulting in 40% fewer branch violations.',
 'Compliance Supervisor I', 18, 'Compliance Manager I', '2027-06-01', 1, NOW(), NOW());


-- =========================================================
-- FINANCE DEPARTMENT (dept_id=5)
-- Template: 125 (Annual), 126 (Quarterly)
-- 5011 = Accounting Staff on Probation
-- 5012 = Accounting Staff I
-- 5002 = Accounting Supervisor I
-- =========================================================

REPLACE INTO evaluations (
    evaluation_id, employee_id, template_id, evaluation_type,
    evaluation_period_start, evaluation_period_end, submitted_by,
    status, total_score, kra_subtotal, behavior_average, performance_level,
    submitted_date, staff_comments, current_position, months_in_position,
    desired_position, target_date, career_growth_suited,
    created_at, updated_at
) VALUES
-- Accounting Staff on Probation -> Pending Dept Supervisor
(9050, 5011, 125, 'Initial', '2025-06-01', '2025-12-31',
 (SELECT user_id FROM users WHERE employee_id = 5011 LIMIT 1),
 'Pending Dept Supervisor', 3.00, 2.95, 3.20, 'Satisfactory',
 '2025-12-12 08:00:00', 'Completed probationary period with satisfactory payroll audit support.',
 'Accounting Staff on Probation', 6, 'Accounting Staff I', '2026-06-01', 0, NOW(), NOW()),

-- Accounting Staff I -> Approved (analytics baseline)
(9051, 5012, 125, 'Annual', '2024-01-01', '2024-12-31',
 (SELECT user_id FROM users WHERE employee_id = 5012 LIMIT 1),
 'Approved', 3.20, 3.15, 3.40, 'Satisfactory',
 '2024-12-08 09:30:00', 'Maintained timely payroll disbursements with zero computation errors.',
 'Accounting Staff I', 12, 'Accounting Staff III', '2026-12-01', 0, NOW(), NOW()),

-- Accounting Supervisor -> Pending HR Consolidation (supervisor level, no dept manager above)
(9052, 5002, 126, 'Quarterly', '2025-10-01', '2025-12-31',
 (SELECT user_id FROM users WHERE employee_id = 5002 LIMIT 1),
 'Pending HR Consolidation', 3.80, 3.75, 4.00, 'Outstanding',
 '2025-12-14 10:00:00', 'Led quarterly financial audit reconciliation with 100% accuracy rate.',
 'Accounting Supervisor I', 22, 'Finance Manager I', '2027-01-01', 1, NOW(), NOW());


-- =========================================================
-- GENERAL SERVICES DEPARTMENT (dept_id=6)
-- Template: 130 (Annual), 131 (Quarterly)
-- 6010 = Driver I (R&F)
-- 6022 = Warehouse Staff I (R&F)
-- 6006 = GS Supervisor I
-- =========================================================

REPLACE INTO evaluations (
    evaluation_id, employee_id, template_id, evaluation_type,
    evaluation_period_start, evaluation_period_end, submitted_by,
    status, total_score, kra_subtotal, behavior_average, performance_level,
    submitted_date, staff_comments, current_position, months_in_position,
    desired_position, target_date, career_growth_suited,
    created_at, updated_at
) VALUES
-- Driver I (R&F) -> Pending Dept Supervisor
(9060, 6010, 130, 'Annual', '2025-01-01', '2025-12-31',
 (SELECT user_id FROM users WHERE employee_id = 6010 LIMIT 1),
 'Pending Dept Supervisor', 3.30, 3.25, 3.50, 'Satisfactory',
 '2025-12-11 07:30:00', 'Maintained zero vehicle incident record throughout the year.',
 'Driver I', 14, 'Driver III', '2027-06-01', 0, NOW(), NOW()),

-- Warehouse Staff I (R&F) -> Approved (analytics baseline)
(9061, 6022, 130, 'Annual', '2024-01-01', '2024-12-31',
 (SELECT user_id FROM users WHERE employee_id = 6022 LIMIT 1),
 'Approved', 3.45, 3.40, 3.65, 'Very Satisfactory',
 '2024-12-05 08:00:00', 'Conducted 100% physical inventory match for all warehouse items in Q4.',
 'Warehouse Staff I', 20, 'Warehouse Supervisor', '2027-01-01', 1, NOW(), NOW()),

-- GS Supervisor -> Pending HR Consolidation
(9062, 6006, 131, 'Quarterly', '2025-10-01', '2025-12-31',
 (SELECT user_id FROM users WHERE employee_id = 6006 LIMIT 1),
 'Pending HR Consolidation', 3.70, 3.65, 3.90, 'Very Satisfactory',
 '2025-12-16 09:00:00', 'Coordinated all Q4 facility maintenance requests within SLA timeframe.',
 'GS Supervisor I', 18, 'GS Manager I', '2027-01-01', 1, NOW(), NOW());


-- =========================================================
-- INFORMATION TECHNOLOGY DEPARTMENT (dept_id=8)
-- Template: 135 (Annual), 136 (Quarterly)
-- 8011 = Programmer I (R&F)
-- 8012 = Technical Support Staff I (R&F)
-- 8005 = IT Supervisor I
-- =========================================================

REPLACE INTO evaluations (
    evaluation_id, employee_id, template_id, evaluation_type,
    evaluation_period_start, evaluation_period_end, submitted_by,
    status, total_score, kra_subtotal, behavior_average, performance_level,
    submitted_date, staff_comments, current_position, months_in_position,
    desired_position, target_date, career_growth_suited,
    created_at, updated_at
) VALUES
-- Programmer I -> Pending Dept Supervisor
(9070, 8011, 135, 'Annual', '2025-01-01', '2025-12-31',
 (SELECT user_id FROM users WHERE employee_id = 8011 LIMIT 1),
 'Pending Dept Supervisor', 3.65, 3.60, 3.85, 'Very Satisfactory',
 '2025-12-13 09:00:00', 'Delivered 5 major HRIS feature enhancements with zero production downtime.',
 'Programmer I', 16, 'Programmer III', '2026-12-01', 1, NOW(), NOW()),

-- Technical Support Staff I -> Approved (analytics baseline)
(9071, 8012, 135, 'Annual', '2024-01-01', '2024-12-31',
 (SELECT user_id FROM users WHERE employee_id = 8012 LIMIT 1),
 'Approved', 3.50, 3.45, 3.70, 'Very Satisfactory',
 '2024-12-07 10:00:00', 'Resolved 98% of helpdesk tickets within SLA across all branches.',
 'Technical Support Staff I', 20, 'IT Supervisor I', '2027-01-01', 1, NOW(), NOW()),

-- IT Supervisor -> Pending HR Consolidation
(9072, 8005, 136, 'Quarterly', '2025-10-01', '2025-12-31',
 (SELECT user_id FROM users WHERE employee_id = 8005 LIMIT 1),
 'Pending HR Consolidation', 3.90, 3.85, 4.00, 'Outstanding',
 '2025-12-17 11:00:00', 'Led Q4 server migration with zero business interruption.',
 'IT Supervisor I', 24, 'IT Manager I', '2027-06-01', 1, NOW(), NOW());


-- =========================================================
-- MARKETING DEPARTMENT (dept_id=9)
-- Template: 140 (Annual), 141 (Quarterly)
-- 9006 = Marketing Staff I (R&F)
-- 9007 = Marketing Staff II (R&F)
-- 9003 = Marketing Supervisor I
-- =========================================================

REPLACE INTO evaluations (
    evaluation_id, employee_id, template_id, evaluation_type,
    evaluation_period_start, evaluation_period_end, submitted_by,
    status, total_score, kra_subtotal, behavior_average, performance_level,
    submitted_date, staff_comments, current_position, months_in_position,
    desired_position, target_date, career_growth_suited,
    created_at, updated_at
) VALUES
-- Marketing Staff I -> Pending Dept Supervisor
(9080, 9006, 140, 'Annual', '2025-01-01', '2025-12-31',
 (SELECT user_id FROM users WHERE employee_id = 9006 LIMIT 1),
 'Pending Dept Supervisor', 3.40, 3.35, 3.60, 'Very Satisfactory',
 '2025-12-14 10:00:00', 'Managed 6 social media campaigns with 15% average engagement rate increase.',
 'Marketing Staff I', 18, 'Marketing Supervisor I', '2027-06-01', 1, NOW(), NOW()),

-- Marketing Staff II -> Approved (analytics baseline)
(9081, 9007, 140, 'Annual', '2024-01-01', '2024-12-31',
 (SELECT user_id FROM users WHERE employee_id = 9007 LIMIT 1),
 'Approved', 3.60, 3.55, 3.80, 'Very Satisfactory',
 '2024-12-06 09:00:00', 'Produced all Q4 marketing collateral with zero revision requests from management.',
 'Marketing Staff II', 6, 'Marketing Supervisor I', '2027-01-01', 1, NOW(), NOW()),

-- Marketing Supervisor -> Pending HR Consolidation
(9082, 9003, 141, 'Quarterly', '2025-10-01', '2025-12-31',
 (SELECT user_id FROM users WHERE employee_id = 9003 LIMIT 1),
 'Pending HR Consolidation', 3.75, 3.70, 3.95, 'Very Satisfactory',
 '2025-12-15 12:00:00', 'Executed year-end ATL campaign within budget and on schedule.',
 'Marketing Supervisor I', 22, 'Marketing Manager I', '2027-01-01', 1, NOW(), NOW());


-- =========================================================
-- OFFICE OF THE PRESIDENT DEPARTMENT (dept_id=10)
-- Template: 145 (Annual)
-- 10002 = Executive Assistant I (R&F)
-- 10003 = Executive Assistant II (R&F)
-- =========================================================

REPLACE INTO evaluations (
    evaluation_id, employee_id, template_id, evaluation_type,
    evaluation_period_start, evaluation_period_end, submitted_by,
    status, total_score, kra_subtotal, behavior_average, performance_level,
    submitted_date, staff_comments, current_position, months_in_position,
    desired_position, target_date, career_growth_suited,
    created_at, updated_at
) VALUES
-- Executive Assistant I -> Pending HR Consolidation (OP has no supervisor/manager above in same dept)
(9085, 10002, 145, 'Annual', '2025-01-01', '2025-12-31',
 (SELECT user_id FROM users WHERE employee_id = 10002 LIMIT 1),
 'Pending HR Consolidation', 3.45, 3.40, 3.65, 'Very Satisfactory',
 '2025-12-11 09:00:00', 'Provided seamless executive scheduling and correspondence management.',
 'Executive Assistant I', 14, 'Executive Assistant III', '2027-01-01', 0, NOW(), NOW()),

-- Executive Assistant II -> Approved (analytics baseline)
(9086, 10003, 145, 'Annual', '2024-01-01', '2024-12-31',
 (SELECT user_id FROM users WHERE employee_id = 10003 LIMIT 1),
 'Approved', 3.65, 3.60, 3.85, 'Very Satisfactory',
 '2024-12-09 08:30:00', 'Coordinated 30+ executive meetings with 100% preparation accuracy.',
 'Executive Assistant II', 8, 'Executive Assistant III', '2026-12-01', 1, NOW(), NOW());


-- =========================================================
-- OPERATIONS DEPARTMENT (dept_id=11) - BRANCH ROUTING TEST CASES
-- Template: 150 (Annual), 151 (Quarterly)
-- 11013 = Branch Staff on Probation  (Branch 10 - no sup/no mgr -> Pending HR Consolidation)
-- 11015 = Branch Staff II            (Branch 3  - only supervisor -> supervisor endorsed -> Pending HR Consolidation)
-- 11016 = Branch Staff III           (Branch 7  - only manager -> Pending Dept Manager)
-- 11017 = Branch Staff IV            (Branch 4  - both sup & mgr -> Pending Dept Supervisor)
-- 11018 = Branch Staff V             (Branch 6  - both sup & mgr -> Pending Dept Supervisor)
-- 11014 = Branch Staff I             (Approved - analytics)
-- 11005 = Area Coordinator I (Supervisor) -> Pending HR Consolidation
-- =========================================================

REPLACE INTO evaluations (
    evaluation_id, employee_id, template_id, evaluation_type,
    evaluation_period_start, evaluation_period_end, submitted_by,
    supervisor_confirmed_by, supervisor_confirmed_date,
    status, total_score, kra_subtotal, behavior_average, performance_level,
    submitted_date, staff_comments, supervisor_comments, supervisor_rating,
    current_position, months_in_position, desired_position, target_date, career_growth_suited,
    created_at, updated_at
) VALUES
-- Scenario B: Only Supervisor (no manager) -> Supervisor endorsed -> Pending HR Consolidation
(9090, 11015, 150, 'Annual', '2025-01-01', '2025-12-31',
 (SELECT user_id FROM users WHERE employee_id = 11015 LIMIT 1),
 (SELECT user_id FROM users WHERE employee_id = 11005 LIMIT 1), '2025-12-20 10:00:00',
 'Pending HR Consolidation', 3.65, 3.60, 3.85, 'Very Satisfactory',
 '2025-12-15 09:00:00', 'Zero vault discrepancies and top pawn ticket volume in the branch.',
 'Angelo excels in collateral testing and branch compliance. Endorsed for HR review.', 3.70,
 'Branch Staff II', 14, 'Branch Supervisor I', '2026-08-01', 1, NOW(), NOW());

REPLACE INTO evaluations (
    evaluation_id, employee_id, template_id, evaluation_type,
    evaluation_period_start, evaluation_period_end, submitted_by,
    status, total_score, kra_subtotal, behavior_average, performance_level,
    submitted_date, staff_comments, current_position, months_in_position,
    desired_position, target_date, career_growth_suited,
    created_at, updated_at
) VALUES
-- Scenario A: Both Supervisor & Manager -> Pending Dept Supervisor
(9091, 11018, 150, 'Annual', '2025-01-01', '2025-12-31',
 (SELECT user_id FROM users WHERE employee_id = 11018 LIMIT 1),
 'Pending Dept Supervisor', 3.45, 3.40, 3.60, 'Very Satisfactory',
 '2025-12-16 09:30:00', 'Consistently hit daily transaction targets and assisted in vault counts.',
 'Branch Staff V', 18, 'Branch Supervisor I', '2026-06-30', 1, NOW(), NOW()),

-- Scenario A (2nd): Both Supervisor & Manager -> Pending Dept Supervisor
(9092, 11017, 150, 'Annual', '2025-01-01', '2025-12-31',
 (SELECT user_id FROM users WHERE employee_id = 11017 LIMIT 1),
 'Pending Dept Supervisor', 3.25, 3.20, 3.40, 'Satisfactory',
 '2025-12-17 10:00:00', 'Improved pawn ticket entry speed and reduced customer queue time.',
 'Branch Staff IV', 24, 'Branch Staff V', '2026-12-31', 0, NOW(), NOW()),

-- Scenario C: Only Manager (no supervisor) -> Pending Dept Manager
(9093, 11016, 150, 'Annual', '2025-01-01', '2025-12-31',
 (SELECT user_id FROM users WHERE employee_id = 11016 LIMIT 1),
 'Pending Dept Manager', 3.50, 3.45, 3.70, 'Very Satisfactory',
 '2025-12-18 11:00:00', 'Handled peak season customer volume efficiently with high satisfaction ratings.',
 'Branch Staff III', 20, 'Branch Manager Trainee', '2027-01-15', 1, NOW(), NOW()),

-- Scenario D: Neither Supervisor NOR Manager -> Pending HR Consolidation
(9094, 11013, 150, 'Initial', '2025-06-01', '2025-12-31',
 (SELECT user_id FROM users WHERE employee_id = 11013 LIMIT 1),
 'Pending HR Consolidation', 3.10, 3.00, 3.50, 'Satisfactory',
 '2025-12-19 08:00:00', 'Probationary initial assessment submitted directly for HR review.',
 'Branch Staff on Probation', 6, 'Regular Branch Staff', '2026-03-01', 0, NOW(), NOW()),

-- Approved (analytics baseline)
(9095, 11014, 150, 'Annual', '2024-01-01', '2024-12-31',
 (SELECT user_id FROM users WHERE employee_id = 11014 LIMIT 1),
 'Approved', 3.75, 3.70, 3.95, 'Outstanding',
 '2024-12-04 09:00:00', 'Achieved top pawn transaction count in the branch.',
 'Branch Staff I', 18, 'Branch Supervisor I', '2026-12-01', 1, NOW(), NOW()),

-- Area Coordinator (Supervisor) -> Pending HR Consolidation
(9096, 11005, 151, 'Quarterly', '2025-10-01', '2025-12-31',
 (SELECT user_id FROM users WHERE employee_id = 11005 LIMIT 1),
 'Pending HR Consolidation', 3.70, 3.65, 3.90, 'Very Satisfactory',
 '2025-12-20 12:00:00', 'Maintained 100% compliance in vault security inspections across assigned branch cluster.',
 'Area Coordinator I', 16, 'Regional Manager I', '2027-03-01', 1, NOW(), NOW());


-- =========================================================
-- PURCHASING DEPARTMENT (dept_id=12)
-- Template: 155 (Annual)
-- 12003 = Purchasing Staff I (R&F)
-- 12001 = Purchasing Supervisor I
-- =========================================================

REPLACE INTO evaluations (
    evaluation_id, employee_id, template_id, evaluation_type,
    evaluation_period_start, evaluation_period_end, submitted_by,
    status, total_score, kra_subtotal, behavior_average, performance_level,
    submitted_date, staff_comments, current_position, months_in_position,
    desired_position, target_date, career_growth_suited,
    created_at, updated_at
) VALUES
-- Purchasing Staff I -> Pending Dept Supervisor
(9097, 12003, 155, 'Annual', '2025-01-01', '2025-12-31',
 (SELECT user_id FROM users WHERE employee_id = 12003 LIMIT 1),
 'Pending Dept Supervisor', 3.35, 3.30, 3.55, 'Very Satisfactory',
 '2025-12-12 09:00:00', 'Processed all purchase requests within 2-day turnaround throughout the year.',
 'Purchasing Staff I', 12, 'Purchasing Supervisor I', '2027-01-01', 1, NOW(), NOW()),

-- Purchasing Supervisor -> Approved (analytics baseline; supervisor level, goes to HR consolidation path)
(9098, 12001, 155, 'Annual', '2024-01-01', '2024-12-31',
 (SELECT user_id FROM users WHERE employee_id = 12001 LIMIT 1),
 'Approved', 3.75, 3.70, 3.95, 'Very Satisfactory',
 '2024-12-07 10:00:00', 'Reduced procurement costs by 12% through better vendor negotiations.',
 'Purchasing Supervisor I', 28, 'Purchasing Manager I', '2027-01-01', 1, NOW(), NOW());


-- ======================================================
-- SECTION 2: EVALUATION DEV PLANS
-- ======================================================

REPLACE INTO evaluation_dev_plans (evaluation_id, improvement_area, support_needed, time_frame, sort_order) VALUES
(9001, 'Talent Sourcing on Digital Platforms', 'LinkedIn Recruiter & Online Sourcing Course', '2 Months', 1),
(9003, 'Labor Relations Management', 'DOLE Legal & Due Process Update Masterclass', '6 Months', 1),
(9010, 'Sales Negotiation & Closing Techniques', 'Property Sales Training Workshop', '3 Months', 1),
(9020, 'Advanced Audit Documentation', 'Institute of Internal Auditors Seminar', '2 Months', 1),
(9022, 'Leadership & Team Management', 'Supervisory Development Program', '6 Months', 1),
(9040, 'AMLA KYC Compliance Refresher', 'Anti-Money Laundering Council E-Learning', '1 Month', 1),
(9050, 'Payroll System Advanced Features', 'Finance System User Training', '1 Month', 1),
(9060, 'Defensive Driving Advanced Certification', 'LTO-Accredited Driving Safety Course', '2 Months', 1),
(9070, 'System Architecture & Scalability', 'AWS Cloud Practitioner Certification', '3 Months', 1),
(9072, 'IT Project Management', 'PMP Certification Prep Course', '6 Months', 1),
(9080, 'Digital Marketing Analytics', 'Google Analytics & Meta Ads Advanced Course', '2 Months', 1),
(9090, 'Branch Supervisory Fundamentals', 'Branch Supervisor Leadership Readiness Program', '6 Months', 1),
(9091, 'Gold & Jewelry Collateral Appraisal Speed', 'Advanced Gemology and Gold Testing Training', '3 Months', 1),
(9095, 'Leadership Readiness', 'Branch Supervisor Mentorship Program', '6 Months', 1),
(9097, 'Supply Chain & Vendor Evaluation', 'Purchasing Management Fundamentals Seminar', '2 Months', 1);


-- ======================================================
-- SECTION 3: CAREER MOVEMENTS -- ALL DEPARTMENTS
-- ======================================================

REPLACE INTO career_movements (
    movement_id, employee_id, movement_type, previous_position, new_position,
    previous_branch_id, new_branch_id, effective_date, reason,
    logged_by, approved_by, decision_date, manager_comments, approval_status, is_applied, created_at
) VALUES

-- [PENDING] Promotion: Operations Branch Staff -> Supervisor
(9001, 11015, 'Promotion', 'Branch Staff II', 'Branch Supervisor I',
 3, 3, '2026-03-01', 'Consistent top performance in branch operations and zero vault discrepancy record.',
 (SELECT user_id FROM users WHERE employee_id = 11005 LIMIT 1),
 NULL, NULL, NULL, 'Pending', 0, NOW()),

-- [PENDING] Promotion: AP Staff -> Supervisor
(9002, 1010, 'Promotion', 'AP Staff I', 'AP Staff III',
 102, 102, '2026-03-15', 'Outstanding performance in property auction support and disposition.',
 (SELECT user_id FROM users WHERE employee_id = 1006 LIMIT 1),
 NULL, NULL, NULL, 'Pending', 0, NOW()),

-- [PENDING] Transfer: Operations branch relocation
(9003, 11014, 'Transfer', 'Branch Staff I', 'Branch Staff I',
 9, 10, '2026-04-01', 'Relocation request to work closer to residential address.',
 (SELECT user_id FROM users WHERE employee_id = 11002 LIMIT 1),
 NULL, NULL, NULL, 'Pending', 0, NOW()),

-- [PENDING] Transfer: Finance staff relocation
(9004, 5013, 'Transfer', 'Accounting Staff II', 'Accounting Staff II',
 102, 102, '2026-04-15', 'Cross-unit transfer for workload balancing.',
 (SELECT user_id FROM users WHERE employee_id = 5002 LIMIT 1),
 NULL, NULL, NULL, 'Pending', 0, NOW()),

-- [PENDING] Role Change: HR Staff I to HR Specialist
(9005, 302, 'Role Change', 'HR Staff I', 'HR Talent Acquisition Specialist',
 102, 102, '2026-05-01', 'Specialization assignment in recruitment operations based on performance.',
 (SELECT user_id FROM users WHERE employee_id = 301 LIMIT 1),
 NULL, NULL, NULL, 'Pending', 0, NOW()),

-- [PENDING] Role Change: IT Programmer to Senior Developer
(9006, 8011, 'Role Change', 'Programmer I', 'Senior Programmer',
 102, 102, '2026-05-15', 'Reclassification based on technical contribution to HRIS system.',
 (SELECT user_id FROM users WHERE employee_id = 8005 LIMIT 1),
 NULL, NULL, NULL, 'Pending', 0, NOW()),

-- [PENDING] Promotion: Compliance Staff -> Supervisor
(9007, 4004, 'Promotion', 'Compliance Staff I', 'Compliance Supervisor I',
 102, 102, '2026-06-01', 'High compliance monitoring accuracy and zero AMLA violations.',
 (SELECT user_id FROM users WHERE employee_id = 4001 LIMIT 1),
 NULL, NULL, NULL, 'Pending', 0, NOW()),

-- [PENDING] Demotion / Reassignment
(9008, 11018, 'Demotion', 'Branch Staff V', 'Branch Staff III',
 6, 6, '2026-04-15', 'Performance alignment reassignment.',
 (SELECT user_id FROM users WHERE employee_id = 11002 LIMIT 1),
 NULL, NULL, NULL, 'Pending', 0, NOW()),

-- [APPROVED] Historic Promotion: Operations Coordinator (analytics timeline)
(9009, 11005, 'Promotion', 'Area Coordinator Trainee', 'Area Coordinator I',
 1, 1, '2025-01-01', 'Completed supervisor training with high distinction.',
 (SELECT user_id FROM users WHERE employee_id = 11002 LIMIT 1),
 (SELECT user_id FROM users WHERE employee_id = 101 LIMIT 1),
 '2024-12-20 10:00:00', 'Approved based on leadership assessment.', 'Approved', 1, '2024-12-20 10:00:00'),

-- [APPROVED] Historic Promotion: IT Supervisor (analytics timeline)
(9010, 8005, 'Promotion', 'IT Supervisor Trainee', 'IT Supervisor I',
 102, 102, '2025-02-01', 'Promoted based on system project delivery excellence.',
 (SELECT user_id FROM users WHERE employee_id = 8001 LIMIT 1),
 (SELECT user_id FROM users WHERE employee_id = 101 LIMIT 1),
 '2025-01-20 09:00:00', 'Fully approved.', 'Approved', 1, '2025-01-20 09:00:00'),

-- [APPROVED] Historic Transfer: Marketing Staff (analytics timeline)
(9011, 9006, 'Transfer', 'Marketing Staff I', 'Marketing Staff I',
 102, 102, '2025-03-01', 'Unit realignment for campaign division expansion.',
 (SELECT user_id FROM users WHERE employee_id = 9003 LIMIT 1),
 (SELECT user_id FROM users WHERE employee_id = 101 LIMIT 1),
 '2025-02-15 11:00:00', 'Approved.', 'Approved', 1, '2025-02-15 11:00:00'),

-- [REJECTED] Historic Promotion: Operations (analytics timeline)
(9012, 11017, 'Promotion', 'Branch Staff IV', 'Branch Supervisor I',
 4, 4, '2025-06-01', 'Early promotion request.',
 (SELECT user_id FROM users WHERE employee_id = 11002 LIMIT 1),
 (SELECT user_id FROM users WHERE employee_id = 101 LIMIT 1),
 '2025-05-15 09:30:00', 'Requires additional months in current rank before promotion eligibility.',
 'Rejected', 0, '2025-05-15 09:30:00'),

-- [REJECTED] Historic Promotion: Audit Staff (analytics timeline)
(9013, 2008, 'Promotion', 'Auditor I', 'Audit Supervisor I',
 102, 102, '2025-07-01', 'Performance-based promotion request.',
 (SELECT user_id FROM users WHERE employee_id = 2004 LIMIT 1),
 (SELECT user_id FROM users WHERE employee_id = 101 LIMIT 1),
 '2025-06-20 14:00:00', 'Not yet eligible; minimum 24 months in rank required.',
 'Rejected', 0, '2025-06-20 14:00:00');


SET FOREIGN_KEY_CHECKS = 1;
