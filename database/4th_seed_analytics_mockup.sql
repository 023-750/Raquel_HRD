USE raquel_hris;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- CLEANUP: Remove previous analytics mockup
-- ============================================
DELETE FROM evaluation_criteria WHERE template_id = 999;
DELETE FROM evaluations WHERE template_id = 999;
DELETE FROM evaluation_templates WHERE template_id = 999;
DELETE FROM career_movements WHERE logged_by IN (
    SELECT user_id FROM users WHERE username IN ('miguel.torres', 'patricia.gomez', 'elena.delgado')
);

-- ============================================
-- SECTION 1: Evaluation Template + Criteria
-- ============================================
INSERT INTO evaluation_templates (
    template_id, template_name, description, target_department,
    evaluation_type, kra_weight, behavior_weight, form_code, status, created_by
) VALUES (
    999,
    'Raquel Pawnshop Performance Evaluation Mockup',
    'Mockup evaluation template for testing Branch Analytics dashboard.',
    'All', 'Annual', 80.00, 20.00, 'HRD Form-013-MOCK', 'Active',
    (SELECT user_id FROM users WHERE role = 'Admin' LIMIT 1)
);

INSERT INTO evaluation_criteria (template_id, section, criterion_name, description, weight, scoring_method, sort_order) VALUES
(999, 'KRA',      'Job Knowledge & Quality',  'Accuracy, neatness, and timeliness of output.',         50.00, 'Scale_1_4', 1),
(999, 'KRA',      'Productivity & Efficiency','Volume of work accomplished within deadline.',           50.00, 'Scale_1_4', 2),
(999, 'Behavior', 'Core Values & Teamwork',   'Demonstration of integrity and cooperation.',           50.00, 'Scale_1_4', 3),
(999, 'Behavior', 'Attendance & Punctuality', 'Adherence to schedule and attendance rules.',           50.00, 'Scale_1_4', 4);

-- ============================================
-- SECTION 2: Evaluations (branch 102, relative dates)
-- submitted_by = Miguel Torres (HR Staff, user for branch 102)
-- endorsed_by  = Patricia Gomez (HR Supervisor, branch 102)
-- approved_by  = Elena Delgado (HR Manager, branch 102)
-- ============================================

-- ──────────── IT Department ────────────
-- 5 months ago
INSERT INTO evaluations (employee_id, template_id, evaluation_type, status, total_score, kra_subtotal, behavior_average, performance_level, submitted_by, endorsed_by, approved_by, submitted_date, endorsed_date, approved_date, supervisor_comments, manager_comments) VALUES
(8001, 999, 'Annual', 'Approved', 3.85, 3.90, 3.70, 'Outstanding',         (SELECT user_id FROM users WHERE username='miguel.torres' LIMIT 1), (SELECT user_id FROM users WHERE username='patricia.gomez' LIMIT 1), (SELECT user_id FROM users WHERE username='elena.delgado' LIMIT 1), DATE_SUB(NOW(), INTERVAL 5 MONTH), DATE_SUB(NOW(), INTERVAL 5 MONTH), DATE_SUB(NOW(), INTERVAL 5 MONTH), 'Exceptional leadership in infrastructure planning.', 'Fully agreed. Outstanding work!');

-- 4 months ago
INSERT INTO evaluations (employee_id, template_id, evaluation_type, status, total_score, kra_subtotal, behavior_average, performance_level, submitted_by, endorsed_by, approved_by, submitted_date, endorsed_date, approved_date, supervisor_comments, manager_comments) VALUES
(8002, 999, 'Annual', 'Approved', 3.25, 3.20, 3.40, 'Exceeds Expectations', (SELECT user_id FROM users WHERE username='miguel.torres' LIMIT 1), (SELECT user_id FROM users WHERE username='patricia.gomez' LIMIT 1), (SELECT user_id FROM users WHERE username='elena.delgado' LIMIT 1), DATE_SUB(NOW(), INTERVAL 4 MONTH), DATE_SUB(NOW(), INTERVAL 4 MONTH), DATE_SUB(NOW(), INTERVAL 4 MONTH), 'High-quality systems delivery.', 'Keep up the excellent work.');

INSERT INTO evaluations (employee_id, template_id, evaluation_type, status, total_score, kra_subtotal, behavior_average, performance_level, submitted_by, endorsed_by, approved_by, submitted_date, endorsed_date, approved_date, supervisor_comments, manager_comments) VALUES
(8003, 999, 'Annual', 'Approved', 2.95, 2.90, 3.10, 'Meets Expectations',   (SELECT user_id FROM users WHERE username='miguel.torres' LIMIT 1), (SELECT user_id FROM users WHERE username='patricia.gomez' LIMIT 1), (SELECT user_id FROM users WHERE username='elena.delgado' LIMIT 1), DATE_SUB(NOW(), INTERVAL 4 MONTH), DATE_SUB(NOW(), INTERVAL 4 MONTH), DATE_SUB(NOW(), INTERVAL 4 MONTH), 'Reliable and consistent output.', 'Meets all performance expectations.');

-- 3 months ago
INSERT INTO evaluations (employee_id, template_id, evaluation_type, status, total_score, kra_subtotal, behavior_average, performance_level, submitted_by, endorsed_by, approved_by, submitted_date, endorsed_date, approved_date, supervisor_comments, manager_comments) VALUES
(8004, 999, 'Annual', 'Approved', 1.75, 1.70, 1.90, 'Needs Improvement',   (SELECT user_id FROM users WHERE username='miguel.torres' LIMIT 1), (SELECT user_id FROM users WHERE username='patricia.gomez' LIMIT 1), (SELECT user_id FROM users WHERE username='elena.delgado' LIMIT 1), DATE_SUB(NOW(), INTERVAL 3 MONTH), DATE_SUB(NOW(), INTERVAL 3 MONTH), DATE_SUB(NOW(), INTERVAL 3 MONTH), 'Missed several delivery targets this cycle.', 'Coaching plan to be implemented.');

INSERT INTO evaluations (employee_id, template_id, evaluation_type, status, total_score, kra_subtotal, behavior_average, performance_level, submitted_by, endorsed_by, approved_by, submitted_date, endorsed_date, approved_date, supervisor_comments, manager_comments) VALUES
(8005, 999, 'Annual', 'Approved', 3.90, 3.95, 3.80, 'Outstanding',         (SELECT user_id FROM users WHERE username='miguel.torres' LIMIT 1), (SELECT user_id FROM users WHERE username='patricia.gomez' LIMIT 1), (SELECT user_id FROM users WHERE username='elena.delgado' LIMIT 1), DATE_SUB(NOW(), INTERVAL 3 MONTH), DATE_SUB(NOW(), INTERVAL 3 MONTH), DATE_SUB(NOW(), INTERVAL 3 MONTH), 'Maintained 99.9% network uptime all quarter.', 'Excellent technical execution, role model for the team.');

-- 2 months ago
INSERT INTO evaluations (employee_id, template_id, evaluation_type, status, total_score, kra_subtotal, behavior_average, performance_level, submitted_by, endorsed_by, approved_by, submitted_date, endorsed_date, approved_date, supervisor_comments, manager_comments) VALUES
(8006, 999, 'Annual', 'Approved', 3.10, 3.05, 3.25, 'Meets Expectations',   (SELECT user_id FROM users WHERE username='miguel.torres' LIMIT 1), (SELECT user_id FROM users WHERE username='patricia.gomez' LIMIT 1), (SELECT user_id FROM users WHERE username='elena.delgado' LIMIT 1), DATE_SUB(NOW(), INTERVAL 2 MONTH), DATE_SUB(NOW(), INTERVAL 2 MONTH), DATE_SUB(NOW(), INTERVAL 2 MONTH), 'Solid supervisor with good team rapport.', 'Approved. Consistent performer.');

-- 1 month ago
INSERT INTO evaluations (employee_id, template_id, evaluation_type, status, total_score, kra_subtotal, behavior_average, performance_level, submitted_by, endorsed_by, approved_by, submitted_date, endorsed_date, approved_date, supervisor_comments, manager_comments) VALUES
(8007, 999, 'Annual', 'Approved', 3.40, 3.35, 3.55, 'Exceeds Expectations', (SELECT user_id FROM users WHERE username='miguel.torres' LIMIT 1), (SELECT user_id FROM users WHERE username='patricia.gomez' LIMIT 1), (SELECT user_id FROM users WHERE username='elena.delgado' LIMIT 1), DATE_SUB(NOW(), INTERVAL 1 MONTH), DATE_SUB(NOW(), INTERVAL 1 MONTH), DATE_SUB(NOW(), INTERVAL 1 MONTH), 'Strong team coordination for software rollout.', 'Recognized for initiative and cross-dept collaboration.');

-- This month
INSERT INTO evaluations (employee_id, template_id, evaluation_type, status, total_score, kra_subtotal, behavior_average, performance_level, submitted_by, endorsed_by, approved_by, submitted_date, endorsed_date, approved_date, supervisor_comments, manager_comments) VALUES
(8008, 999, 'Annual', 'Approved', 3.65, 3.60, 3.80, 'Exceeds Expectations', (SELECT user_id FROM users WHERE username='miguel.torres' LIMIT 1), (SELECT user_id FROM users WHERE username='patricia.gomez' LIMIT 1), (SELECT user_id FROM users WHERE username='elena.delgado' LIMIT 1), NOW(), NOW(), NOW(), 'Efficient supervisor with strong technical skills.', 'Approved with distinction.');

-- Pending / Returned (IT)
INSERT INTO evaluations (employee_id, template_id, evaluation_type, status, total_score, submitted_by, submitted_date) VALUES
(8009, 999, 'Annual', 'Pending Supervisor', 3.00, (SELECT user_id FROM users WHERE username='miguel.torres' LIMIT 1), NOW());

INSERT INTO evaluations (employee_id, template_id, evaluation_type, status, total_score, submitted_by, submitted_date) VALUES
(8010, 999, 'Annual', 'Returned', 2.20, (SELECT user_id FROM users WHERE username='miguel.torres' LIMIT 1), DATE_SUB(NOW(), INTERVAL 1 MONTH));

-- ──────────── Marketing Department ────────────
INSERT INTO evaluations (employee_id, template_id, evaluation_type, status, total_score, kra_subtotal, behavior_average, performance_level, submitted_by, endorsed_by, approved_by, submitted_date, endorsed_date, approved_date, supervisor_comments, manager_comments) VALUES
(9003, 999, 'Annual', 'Approved', 3.55, 3.60, 3.40, 'Exceeds Expectations', (SELECT user_id FROM users WHERE username='miguel.torres' LIMIT 1), (SELECT user_id FROM users WHERE username='patricia.gomez' LIMIT 1), (SELECT user_id FROM users WHERE username='elena.delgado' LIMIT 1), DATE_SUB(NOW(), INTERVAL 5 MONTH), DATE_SUB(NOW(), INTERVAL 5 MONTH), DATE_SUB(NOW(), INTERVAL 5 MONTH), 'Campaign execution exceeded KPIs.', 'Very good results, strong branding initiatives.');

INSERT INTO evaluations (employee_id, template_id, evaluation_type, status, total_score, kra_subtotal, behavior_average, performance_level, submitted_by, endorsed_by, approved_by, submitted_date, endorsed_date, approved_date, supervisor_comments, manager_comments) VALUES
(9004, 999, 'Annual', 'Approved', 3.10, 3.00, 3.30, 'Meets Expectations',   (SELECT user_id FROM users WHERE username='miguel.torres' LIMIT 1), (SELECT user_id FROM users WHERE username='patricia.gomez' LIMIT 1), (SELECT user_id FROM users WHERE username='elena.delgado' LIMIT 1), DATE_SUB(NOW(), INTERVAL 4 MONTH), DATE_SUB(NOW(), INTERVAL 4 MONTH), DATE_SUB(NOW(), INTERVAL 4 MONTH), 'Good support during campaign launches.', 'Meets expectations. Keep up the consistency.');

INSERT INTO evaluations (employee_id, template_id, evaluation_type, status, total_score, kra_subtotal, behavior_average, performance_level, submitted_by, endorsed_by, approved_by, submitted_date, endorsed_date, approved_date, supervisor_comments, manager_comments) VALUES
(9005, 999, 'Annual', 'Approved', 2.80, 2.75, 2.95, 'Meets Expectations',   (SELECT user_id FROM users WHERE username='miguel.torres' LIMIT 1), (SELECT user_id FROM users WHERE username='patricia.gomez' LIMIT 1), (SELECT user_id FROM users WHERE username='elena.delgado' LIMIT 1), DATE_SUB(NOW(), INTERVAL 3 MONTH), DATE_SUB(NOW(), INTERVAL 3 MONTH), DATE_SUB(NOW(), INTERVAL 3 MONTH), 'Good probationary transition.', 'Regularization is recommended.');

INSERT INTO evaluations (employee_id, template_id, evaluation_type, status, total_score, kra_subtotal, behavior_average, performance_level, submitted_by, endorsed_by, approved_by, submitted_date, endorsed_date, approved_date, supervisor_comments, manager_comments) VALUES
(9006, 999, 'Annual', 'Approved', 1.90, 1.85, 2.10, 'Needs Improvement',   (SELECT user_id FROM users WHERE username='miguel.torres' LIMIT 1), (SELECT user_id FROM users WHERE username='patricia.gomez' LIMIT 1), (SELECT user_id FROM users WHERE username='elena.delgado' LIMIT 1), DATE_SUB(NOW(), INTERVAL 2 MONTH), DATE_SUB(NOW(), INTERVAL 2 MONTH), DATE_SUB(NOW(), INTERVAL 2 MONTH), 'Design deliverables submitted after deadlines.', 'Requires closer supervision and coaching plan.');

INSERT INTO evaluations (employee_id, template_id, evaluation_type, status, total_score, submitted_by, submitted_date) VALUES
(9007, 999, 'Annual', 'Pending Supervisor', 3.10, (SELECT user_id FROM users WHERE username='miguel.torres' LIMIT 1), NOW());

-- ──────────── HR Department ────────────
INSERT INTO evaluations (employee_id, template_id, evaluation_type, status, total_score, kra_subtotal, behavior_average, performance_level, submitted_by, endorsed_by, approved_by, submitted_date, endorsed_date, approved_date, supervisor_comments, manager_comments) VALUES
(7012, 999, 'Annual', 'Approved', 3.40, 3.35, 3.55, 'Exceeds Expectations', (SELECT user_id FROM users WHERE username='miguel.torres' LIMIT 1), (SELECT user_id FROM users WHERE username='patricia.gomez' LIMIT 1), (SELECT user_id FROM users WHERE username='elena.delgado' LIMIT 1), DATE_SUB(NOW(), INTERVAL 2 MONTH), DATE_SUB(NOW(), INTERVAL 2 MONTH), DATE_SUB(NOW(), INTERVAL 2 MONTH), 'Tremendous effort supporting the recruitment pipeline.', 'Excellent work, potential for regularization early.');

INSERT INTO evaluations (employee_id, template_id, evaluation_type, status, total_score, kra_subtotal, behavior_average, performance_level, submitted_by, endorsed_by, approved_by, submitted_date, endorsed_date, approved_date, supervisor_comments, manager_comments) VALUES
(7013, 999, 'Annual', 'Approved', 3.05, 3.00, 3.15, 'Meets Expectations',   (SELECT user_id FROM users WHERE username='miguel.torres' LIMIT 1), (SELECT user_id FROM users WHERE username='patricia.gomez' LIMIT 1), (SELECT user_id FROM users WHERE username='elena.delgado' LIMIT 1), DATE_SUB(NOW(), INTERVAL 1 MONTH), DATE_SUB(NOW(), INTERVAL 1 MONTH), DATE_SUB(NOW(), INTERVAL 1 MONTH), 'Consistent in maintaining employee records.', 'Approved.');

INSERT INTO evaluations (employee_id, template_id, evaluation_type, status, total_score, kra_subtotal, behavior_average, performance_level, submitted_by, endorsed_by, approved_by, submitted_date, endorsed_date, approved_date, supervisor_comments, manager_comments) VALUES
(7014, 999, 'Annual', 'Approved', 3.60, 3.65, 3.45, 'Exceeds Expectations', (SELECT user_id FROM users WHERE username='miguel.torres' LIMIT 1), (SELECT user_id FROM users WHERE username='patricia.gomez' LIMIT 1), (SELECT user_id FROM users WHERE username='elena.delgado' LIMIT 1), NOW(), NOW(), NOW(), 'Proactively handles employee documentation requests.', 'Approved with commendation.');

-- ──────────── Purchasing Department ────────────
INSERT INTO evaluations (employee_id, template_id, evaluation_type, status, total_score, submitted_by, submitted_date) VALUES
(12001, 999, 'Annual', 'Pending Supervisor', 3.60, (SELECT user_id FROM users WHERE username='miguel.torres' LIMIT 1), NOW());

INSERT INTO evaluations (employee_id, template_id, evaluation_type, status, total_score, submitted_by, endorsed_by, submitted_date, endorsed_date) VALUES
(12002, 999, 'Annual', 'Pending Manager', 3.20, (SELECT user_id FROM users WHERE username='miguel.torres' LIMIT 1), (SELECT user_id FROM users WHERE username='patricia.gomez' LIMIT 1), DATE_SUB(NOW(), INTERVAL 1 MONTH), DATE_SUB(NOW(), INTERVAL 1 MONTH));

INSERT INTO evaluations (employee_id, template_id, evaluation_type, status, total_score, submitted_by, submitted_date) VALUES
(12003, 999, 'Annual', 'Returned', 2.40, (SELECT user_id FROM users WHERE username='miguel.torres' LIMIT 1), NOW());

-- ──────────── Office of the President ────────────
INSERT INTO evaluations (employee_id, template_id, evaluation_type, status, total_score, submitted_by, submitted_date) VALUES
(10002, 999, 'Annual', 'Rejected', 1.50, (SELECT user_id FROM users WHERE username='miguel.torres' LIMIT 1), DATE_SUB(NOW(), INTERVAL 1 MONTH));

INSERT INTO evaluations (employee_id, template_id, evaluation_type, status, total_score, submitted_by, submitted_date) VALUES
(10003, 999, 'Annual', 'Pending Supervisor', 3.00, (SELECT user_id FROM users WHERE username='miguel.torres' LIMIT 1), NOW());

INSERT INTO evaluations (employee_id, template_id, evaluation_type, status, total_score, kra_subtotal, behavior_average, performance_level, submitted_by, endorsed_by, approved_by, submitted_date, endorsed_date, approved_date, supervisor_comments, manager_comments) VALUES
(10004, 999, 'Annual', 'Approved', 3.75, 3.80, 3.60, 'Outstanding',         (SELECT user_id FROM users WHERE username='miguel.torres' LIMIT 1), (SELECT user_id FROM users WHERE username='patricia.gomez' LIMIT 1), (SELECT user_id FROM users WHERE username='elena.delgado' LIMIT 1), DATE_SUB(NOW(), INTERVAL 2 MONTH), DATE_SUB(NOW(), INTERVAL 2 MONTH), DATE_SUB(NOW(), INTERVAL 2 MONTH), 'Exceptional executive support this quarter.', 'Outstanding. Highly commended by the President.');


-- ============================================
-- SECTION 3: Talent Mobility - career_movements
-- Covers: Promotions, Transfers, Demotions, Role Changes
-- These are approved and have effective_dates spread over last 6 months
-- ============================================

-- Promotion: John Ramos (IT) - IT Manager I → IT Manager II (3 months ago)
INSERT INTO career_movements (employee_id, movement_type, previous_position, new_position, previous_branch_id, new_branch_id, effective_date, reason, logged_by, approved_by, decision_date, manager_comments, approval_status, is_applied) VALUES
(8001, 'Promotion', 'IT Manager I', 'IT Manager II', 102, 102, DATE_SUB(CURDATE(), INTERVAL 3 MONTH),
 'Consistent outstanding performance for 2 consecutive cycles.', (SELECT user_id FROM users WHERE username='patricia.gomez' LIMIT 1), (SELECT user_id FROM users WHERE username='elena.delgado' LIMIT 1), DATE_SUB(NOW(), INTERVAL 3 MONTH), 'Well-deserved promotion. Approved.', 'Approved', 1);

-- Promotion: Ana Santiago (IT) - IT Supervisor III → IT Manager I (2 months ago)
INSERT INTO career_movements (employee_id, movement_type, previous_position, new_position, previous_branch_id, new_branch_id, effective_date, reason, logged_by, approved_by, decision_date, manager_comments, approval_status, is_applied) VALUES
(8007, 'Promotion', 'IT Supervisor III', 'IT Manager I', 102, 102, DATE_SUB(CURDATE(), INTERVAL 2 MONTH),
 'Demonstrated leadership in software rollout project.', (SELECT user_id FROM users WHERE username='patricia.gomez' LIMIT 1), (SELECT user_id FROM users WHERE username='elena.delgado' LIMIT 1), DATE_SUB(NOW(), INTERVAL 2 MONTH), 'Strong performance and initiative. Approved.', 'Approved', 1);

-- Promotion: Patricia Villanueva (HR) - HR Staff on Probation → HR Staff I (1 month ago)
INSERT INTO career_movements (employee_id, movement_type, previous_position, new_position, previous_branch_id, new_branch_id, effective_date, reason, logged_by, approved_by, decision_date, manager_comments, approval_status, is_applied) VALUES
(7012, 'Promotion', 'HR Staff on Probation', 'HR Staff I', 102, 102, DATE_SUB(CURDATE(), INTERVAL 1 MONTH),
 'Completed probation with exceptional performance rating.', (SELECT user_id FROM users WHERE username='patricia.gomez' LIMIT 1), (SELECT user_id FROM users WHERE username='elena.delgado' LIMIT 1), DATE_SUB(NOW(), INTERVAL 1 MONTH), 'Regularization approved. Excellent probationary performance.', 'Approved', 1);

-- Transfer: Teresa Pascual (Marketing) transferred to HR Support (4 months ago)
INSERT INTO career_movements (employee_id, movement_type, previous_position, new_position, previous_branch_id, new_branch_id, effective_date, reason, logged_by, approved_by, decision_date, manager_comments, approval_status, is_applied) VALUES
(9005, 'Transfer', 'Marketing Staff on Probation', 'HR Support Staff', 102, 102, DATE_SUB(CURDATE(), INTERVAL 4 MONTH),
 'Business needs require additional HR support headcount.', (SELECT user_id FROM users WHERE username='patricia.gomez' LIMIT 1), (SELECT user_id FROM users WHERE username='elena.delgado' LIMIT 1), DATE_SUB(NOW(), INTERVAL 4 MONTH), 'Transfer approved to support HR operations.', 'Approved', 1);

-- Transfer: David Perez (IT Supervisor IV) - reassigned 5 months ago (transfer from Main to Agdangan Branch)
INSERT INTO career_movements (employee_id, movement_type, previous_position, new_position, previous_branch_id, new_branch_id, effective_date, reason, logged_by, approved_by, decision_date, manager_comments, approval_status, is_applied) VALUES
(8008, 'Transfer', 'IT Supervisor IV', 'IT Project Lead', 102, 1, DATE_SUB(CURDATE(), INTERVAL 5 MONTH),
 'Project needs at head office require a seasoned IT lead.', (SELECT user_id FROM users WHERE username='patricia.gomez' LIMIT 1), (SELECT user_id FROM users WHERE username='elena.delgado' LIMIT 1), DATE_SUB(NOW(), INTERVAL 5 MONTH), 'Transfer recommended and approved.', 'Approved', 1);

-- Role Change: Gloria Tolentino (Marketing) - Role adjusted after low performance review (2 months ago)
INSERT INTO career_movements (employee_id, movement_type, previous_position, new_position, previous_branch_id, new_branch_id, effective_date, reason, logged_by, approved_by, decision_date, manager_comments, approval_status, is_applied) VALUES
(9006, 'Role Change', 'Marketing Staff I', 'Marketing Coordinator (On Improvement Plan)', 102, 102, DATE_SUB(CURDATE(), INTERVAL 2 MONTH),
 'Performance improvement plan requires role adjustment and closer supervision.', (SELECT user_id FROM users WHERE username='patricia.gomez' LIMIT 1), (SELECT user_id FROM users WHERE username='elena.delgado' LIMIT 1), DATE_SUB(NOW(), INTERVAL 2 MONTH), 'Adjusted role as part of PIP. Will be re-evaluated next quarter.', 'Approved', 1);

-- Promotion (pending): Mark Soriano (IT) - This month
INSERT INTO career_movements (employee_id, movement_type, previous_position, new_position, previous_branch_id, new_branch_id, effective_date, reason, logged_by, approval_status) VALUES
(8015, 'Promotion', 'Technical Support Staff IV', 'Technical Support Supervisor I', 102, 102, CURDATE(),
 'Excellent performance and readiness for supervisory role.', (SELECT user_id FROM users WHERE username='patricia.gomez' LIMIT 1), 'Pending');

-- Transfer (pending): Rose Sarmiento (Purchasing) - This month (transfer from Main to Alaminos Branch)
INSERT INTO career_movements (employee_id, movement_type, previous_position, new_position, previous_branch_id, new_branch_id, effective_date, reason, logged_by, approval_status) VALUES
(12002, 'Transfer', 'Purchasing Supervisor on Training', 'Purchasing Staff III', 102, 3, CURDATE(),
 'Operational needs re-alignment in Purchasing department.', (SELECT user_id FROM users WHERE username='patricia.gomez' LIMIT 1), 'Pending');


-- ============================================
-- SECTION 4: Year-Over-Year Progression
-- Add prior-year approved evaluations for same employees
-- so that the YoY chart shows data across multiple years
-- ============================================

-- Prior year (2025) evaluations for same employees with slightly lower scores
-- IT Department - prior year
INSERT INTO evaluations (employee_id, template_id, evaluation_type, status, total_score, kra_subtotal, behavior_average, performance_level, submitted_by, endorsed_by, approved_by, submitted_date, endorsed_date, approved_date, supervisor_comments, manager_comments) VALUES
(8001, 999, 'Annual', 'Approved', 3.50, 3.55, 3.35, 'Exceeds Expectations', (SELECT user_id FROM users WHERE username='miguel.torres' LIMIT 1), (SELECT user_id FROM users WHERE username='patricia.gomez' LIMIT 1), (SELECT user_id FROM users WHERE username='elena.delgado' LIMIT 1), DATE_SUB(NOW(), INTERVAL 14 MONTH), DATE_SUB(NOW(), INTERVAL 14 MONTH), DATE_SUB(NOW(), INTERVAL 14 MONTH), 'Strong foundational performance last year.', 'Approved. Good year for IT.');

INSERT INTO evaluations (employee_id, template_id, evaluation_type, status, total_score, kra_subtotal, behavior_average, performance_level, submitted_by, endorsed_by, approved_by, submitted_date, endorsed_date, approved_date, supervisor_comments, manager_comments) VALUES
(8003, 999, 'Annual', 'Approved', 2.70, 2.65, 2.90, 'Meets Expectations', (SELECT user_id FROM users WHERE username='miguel.torres' LIMIT 1), (SELECT user_id FROM users WHERE username='patricia.gomez' LIMIT 1), (SELECT user_id FROM users WHERE username='elena.delgado' LIMIT 1), DATE_SUB(NOW(), INTERVAL 16 MONTH), DATE_SUB(NOW(), INTERVAL 16 MONTH), DATE_SUB(NOW(), INTERVAL 16 MONTH), 'Stable performance last year.', 'Meets expectations.');

INSERT INTO evaluations (employee_id, template_id, evaluation_type, status, total_score, kra_subtotal, behavior_average, performance_level, submitted_by, endorsed_by, approved_by, submitted_date, endorsed_date, approved_date, supervisor_comments, manager_comments) VALUES
(8005, 999, 'Annual', 'Approved', 3.75, 3.80, 3.60, 'Outstanding', (SELECT user_id FROM users WHERE username='miguel.torres' LIMIT 1), (SELECT user_id FROM users WHERE username='patricia.gomez' LIMIT 1), (SELECT user_id FROM users WHERE username='elena.delgado' LIMIT 1), DATE_SUB(NOW(), INTERVAL 15 MONTH), DATE_SUB(NOW(), INTERVAL 15 MONTH), DATE_SUB(NOW(), INTERVAL 15 MONTH), 'Consistently high performer year-on-year.', 'Outstanding again. Remarkable consistency.');

-- Marketing Department - prior year
INSERT INTO evaluations (employee_id, template_id, evaluation_type, status, total_score, kra_subtotal, behavior_average, performance_level, submitted_by, endorsed_by, approved_by, submitted_date, endorsed_date, approved_date, supervisor_comments, manager_comments) VALUES
(9003, 999, 'Annual', 'Approved', 3.20, 3.15, 3.35, 'Meets Expectations', (SELECT user_id FROM users WHERE username='miguel.torres' LIMIT 1), (SELECT user_id FROM users WHERE username='patricia.gomez' LIMIT 1), (SELECT user_id FROM users WHERE username='elena.delgado' LIMIT 1), DATE_SUB(NOW(), INTERVAL 17 MONTH), DATE_SUB(NOW(), INTERVAL 17 MONTH), DATE_SUB(NOW(), INTERVAL 17 MONTH), 'Good performance last year, improved significantly.', 'Approved.');

INSERT INTO evaluations (employee_id, template_id, evaluation_type, status, total_score, kra_subtotal, behavior_average, performance_level, submitted_by, endorsed_by, approved_by, submitted_date, endorsed_date, approved_date, supervisor_comments, manager_comments) VALUES
(9006, 999, 'Annual', 'Approved', 2.60, 2.55, 2.80, 'Meets Expectations', (SELECT user_id FROM users WHERE username='miguel.torres' LIMIT 1), (SELECT user_id FROM users WHERE username='patricia.gomez' LIMIT 1), (SELECT user_id FROM users WHERE username='elena.delgado' LIMIT 1), DATE_SUB(NOW(), INTERVAL 14 MONTH), DATE_SUB(NOW(), INTERVAL 14 MONTH), DATE_SUB(NOW(), INTERVAL 14 MONTH), 'Met expectations but dipped this year requiring PIP.', 'Noted. Prior year was more consistent.');

-- HR Department - prior year
INSERT INTO evaluations (employee_id, template_id, evaluation_type, status, total_score, kra_subtotal, behavior_average, performance_level, submitted_by, endorsed_by, approved_by, submitted_date, endorsed_date, approved_date, supervisor_comments, manager_comments) VALUES
(7013, 999, 'Annual', 'Approved', 2.80, 2.75, 2.95, 'Meets Expectations', (SELECT user_id FROM users WHERE username='miguel.torres' LIMIT 1), (SELECT user_id FROM users WHERE username='patricia.gomez' LIMIT 1), (SELECT user_id FROM users WHERE username='elena.delgado' LIMIT 1), DATE_SUB(NOW(), INTERVAL 13 MONTH), DATE_SUB(NOW(), INTERVAL 13 MONTH), DATE_SUB(NOW(), INTERVAL 13 MONTH), 'Good prior year. Has shown improvement.', 'Approved.');


-- ============================================
-- SECTION 5: Demographics Insights
-- Ensures employees in scope have gender, civil_status, hire_date, employment_status set
-- (These are already set from employee seed files but we patch any nulls)
-- ============================================
UPDATE employees SET gender = 'Male',   civil_status = 'Separated', employment_status = 'Regular'   WHERE employee_id = 8001 AND gender IS NULL;
UPDATE employees SET gender = 'Female', civil_status = 'Widowed',   employment_status = 'Regular'   WHERE employee_id = 8002 AND gender IS NULL;
UPDATE employees SET gender = 'Female', civil_status = 'Single',    employment_status = 'Regular'   WHERE employee_id = 8003 AND gender IS NULL;
UPDATE employees SET gender = 'Male',   civil_status = 'Widowed',   employment_status = 'Regular'   WHERE employee_id = 8004 AND gender IS NULL;
UPDATE employees SET gender = 'Male',   civil_status = 'Single',    employment_status = 'Regular'   WHERE employee_id = 8005 AND gender IS NULL;
UPDATE employees SET gender = 'Female', civil_status = 'Separated', employment_status = 'Regular'   WHERE employee_id = 8006 AND gender IS NULL;
UPDATE employees SET gender = 'Female', civil_status = 'Married',   employment_status = 'Regular'   WHERE employee_id = 8007 AND gender IS NULL;
UPDATE employees SET gender = 'Male',   civil_status = 'Widowed',   employment_status = 'Regular'   WHERE employee_id = 8008 AND gender IS NULL;
UPDATE employees SET gender = 'Male',   civil_status = 'Widowed',   employment_status = 'Regular'   WHERE employee_id = 8009 AND gender IS NULL;
UPDATE employees SET gender = 'Female', civil_status = 'Single',    employment_status = 'Probationary' WHERE employee_id = 8010 AND gender IS NULL;
UPDATE employees SET gender = 'Male',   civil_status = 'Single',    employment_status = 'Regular'   WHERE employee_id = 9003 AND gender IS NULL;
UPDATE employees SET gender = 'Male',   civil_status = 'Separated', employment_status = 'Regular'   WHERE employee_id = 9004 AND gender IS NULL;
UPDATE employees SET gender = 'Female', civil_status = 'Single',    employment_status = 'Probationary' WHERE employee_id = 9005 AND gender IS NULL;
UPDATE employees SET gender = 'Female', civil_status = 'Widowed',   employment_status = 'Regular'   WHERE employee_id = 9006 AND gender IS NULL;
UPDATE employees SET gender = 'Male',   civil_status = 'Married',   employment_status = 'Regular'   WHERE employee_id = 9007 AND gender IS NULL;
UPDATE employees SET gender = 'Female', civil_status = 'Widowed',   employment_status = 'Probationary' WHERE employee_id = 7012 AND gender IS NULL;
UPDATE employees SET gender = 'Female', civil_status = 'Single',    employment_status = 'Regular'   WHERE employee_id = 7013 AND gender IS NULL;
UPDATE employees SET gender = 'Male',   civil_status = 'Widowed',   employment_status = 'Regular'   WHERE employee_id = 7014 AND gender IS NULL;
UPDATE employees SET gender = 'Female', civil_status = 'Separated', employment_status = 'Regular'   WHERE employee_id = 12001 AND gender IS NULL;
UPDATE employees SET gender = 'Female', civil_status = 'Widowed',   employment_status = 'Trainee'   WHERE employee_id = 12002 AND gender IS NULL;
UPDATE employees SET gender = 'Male',   civil_status = 'Separated', employment_status = 'Regular'   WHERE employee_id = 12003 AND gender IS NULL;
UPDATE employees SET gender = 'Male',   civil_status = 'Single',    employment_status = 'Regular'   WHERE employee_id = 10002 AND gender IS NULL;
UPDATE employees SET gender = 'Female', civil_status = 'Married',   employment_status = 'Regular'   WHERE employee_id = 10003 AND gender IS NULL;
UPDATE employees SET gender = 'Male',   civil_status = 'Separated', employment_status = 'Regular'   WHERE employee_id = 10004 AND gender IS NULL;


-- ============================================
-- SECTION 6: Organizational Analysis
-- Distribute employees across multiple branches to demonstrate comparative branch analytics
-- ============================================
UPDATE employees SET branch_id = 102, is_active = 1 WHERE employee_id IN (
    8003, 8004, 8005, 8006, 8007, 8008, 8009, 8010, 
    9005, 9006, 9007, 
    7013, 7014, 
    12001, 12003, 
    10002, 10003, 10004
);

-- Agdangan Branch (branch_id = 1)
UPDATE employees SET branch_id = 1, is_active = 1 WHERE employee_id IN (8001, 8002);

-- Alabat Branch (branch_id = 2)
UPDATE employees SET branch_id = 2, is_active = 1 WHERE employee_id IN (9003, 9004);

-- Alaminos Branch (branch_id = 3)
UPDATE employees SET branch_id = 3, is_active = 1 WHERE employee_id IN (12002);

-- Angono Branch 1 (branch_id = 4)
UPDATE employees SET branch_id = 4, is_active = 1 WHERE employee_id IN (7012);

SET FOREIGN_KEY_CHECKS = 1;
