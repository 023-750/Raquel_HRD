-- ============================================
-- Evaluation Templates & Criteria Seed
-- Updated: June 12, 2026
-- Effective: 2026-06-12 | Expires: 2026-07-12
--
-- Template breakdown:
--   1. AP Staff & Sales Performance Evaluation   (Acquired Properties)
--   2. AP Manager & Supervisor Evaluation        (Acquired Properties)
--   3. HR Staff Performance Evaluation           (Human Resources)
--   4. HR Manager & Supervisor Evaluation        (Human Resources)
--   5. General Employee Template                 (All Departments)
-- ============================================

SET FOREIGN_KEY_CHECKS = 0;
USE raquel_hris;

-- ============================================
-- EVALUATION TEMPLATES (5 total)
-- ============================================
REPLACE INTO evaluation_templates (
    template_id, template_name, description,
    target_department, evaluation_type,
    kra_weight, behavior_weight,
    form_code, revision_date, effective_date_form,
    status, created_by
) VALUES
-- 1: Acquired Properties — Staff & Sales
(1,
 'AP Staff & Sales Performance Evaluation',
 'Performance evaluation for Acquired Properties staff and sales associates covering property management tasks, client coordination, and sales targets.',
 'Acquired Properties', 'Annual',
 80.00, 20.00,
 'HRD Form-013.01', CURDATE(), '2026-06-12',
 'Active', 1),

-- 2: Acquired Properties — Managers & Supervisors
(2,
 'AP Manager & Supervisor Evaluation',
 'Evaluation template for AP Managers and Supervisors covering leadership, property portfolio oversight, team performance, and compliance.',
 'Acquired Properties', 'Annual',
 80.00, 20.00,
 'HRD Form-013.02', CURDATE(), '2026-06-12',
 'Active', 1),

-- 3: Human Resources — Staff
(3,
 'HR Staff Performance Evaluation',
 'Performance review for HR Staff covering recruitment support, employee records management, training coordination, and compliance.',
 'Human Resources', 'Annual',
 80.00, 20.00,
 'HRD Form-013.03', CURDATE(), '2026-06-12',
 'Active', 1),

-- 4: Human Resources — Managers & Supervisors
(4,
 'HR Manager & Supervisor Evaluation',
 'Evaluation for HR Managers and Supervisors covering HR strategy, policy enforcement, team leadership, and organizational development.',
 'Human Resources', 'Annual',
 80.00, 20.00,
 'HRD Form-013.04', CURDATE(), '2026-06-12',
 'Active', 1),

-- 5: All Departments — General
(5,
 'General Employee Template (All Departments)',
 'Standard performance evaluation applicable to all rank-and-file employees across all departments. Covers core work output, conduct, and collaboration.',
 'All Departments', 'Annual',
 80.00, 20.00,
 'HRD Form-013.05', CURDATE(), '2026-06-12',
 'Active', 1);

-- ============================================
-- KRA CRITERIA
-- ============================================

-- Template 1: AP Staff & Sales — KRA
REPLACE INTO evaluation_criteria (template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
(1, 'KRA', 'Property Documentation & Filing',
 'Accuracy and completeness of acquired property documents, deed records, and title tracking.',
 'Maintains 100% complete and up-to-date AP files with zero missing documents per audit cycle.',
 20.00, 'Scale_1_4', 1),

(1, 'KRA', 'Client Coordination & Follow-Through',
 'Timeliness and effectiveness of communication with buyers, sellers, and internal teams.',
 'Responds to all client inquiries within 24 hours and closes follow-up actions within agreed timelines.',
 20.00, 'Scale_1_4', 2),

(1, 'KRA', 'Sales Target Achievement',
 'Meeting or exceeding monthly and quarterly sales quotas for acquired properties.',
 'Achieves at least 90% of monthly sales targets as set by the AP Supervisor.',
 20.00, 'Scale_1_4', 3),

(1, 'KRA', 'Property Inspection & Condition Reporting',
 'Conducting timely inspections and submitting accurate property condition reports.',
 'Submits complete inspection reports within 2 working days of site visit with no factual discrepancies.',
 20.00, 'Scale_1_4', 4),

(1, 'KRA', 'Compliance with AP Policies & Procedures',
 'Adherence to internal AP department guidelines, deadlines, and regulatory requirements.',
 'Zero violations of AP compliance checklist per evaluation period.',
 20.00, 'Scale_1_4', 5);

-- Template 2: AP Manager & Supervisor — KRA
REPLACE INTO evaluation_criteria (template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
(2, 'KRA', 'Portfolio Management & Disposition',
 'Overseeing the acquired property portfolio and driving timely disposition of assets.',
 'Achieves property disposition rate of at least 85% of assigned portfolio per quarter.',
 20.00, 'Scale_1_4', 1),

(2, 'KRA', 'Team Supervision & Performance Monitoring',
 'Leading, evaluating, and coaching AP staff and sales associates to meet objectives.',
 'Conducts monthly one-on-ones and submits team performance reports on schedule.',
 20.00, 'Scale_1_4', 2),

(2, 'KRA', 'Revenue & Cost Management',
 'Managing department budget, monitoring expenses, and maximizing revenue from AP sales.',
 'Keeps department expenses within approved budget and reports variances within 3 business days.',
 20.00, 'Scale_1_4', 3),

(2, 'KRA', 'Regulatory & Legal Compliance',
 'Ensuring all property transactions comply with applicable laws, BSP guidelines, and internal policies.',
 'Zero legal findings or compliance deficiencies per internal audit report.',
 20.00, 'Scale_1_4', 4),

(2, 'KRA', 'Stakeholder Reporting & Coordination',
 'Preparing and presenting AP performance reports to VP and executive management.',
 'Submits accurate monthly AP performance reports by the 5th working day of each month.',
 20.00, 'Scale_1_4', 5);

-- Template 3: HR Staff — KRA
REPLACE INTO evaluation_criteria (template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
(3, 'KRA', 'Recruitment & Onboarding Support',
 'Assisting in sourcing, screening, and onboarding qualified candidates.',
 'Processes at least 90% of endorsed job requisitions within the prescribed turnaround time.',
 20.00, 'Scale_1_4', 1),

(3, 'KRA', 'Employee Records & 201 File Management',
 'Maintaining accurate, complete, and confidential employee 201 files and HR records.',
 'Achieves 100% completeness of 201 files per scheduled audit with zero missing mandatory documents.',
 20.00, 'Scale_1_4', 2),

(3, 'KRA', 'Training & Development Coordination',
 'Coordinating training schedules, attendance, and post-training documentation.',
 'Completes training coordination tasks within prescribed deadlines with 100% participant documentation.',
 20.00, 'Scale_1_4', 3),

(3, 'KRA', 'HR Compliance & Labor Law Adherence',
 'Ensuring HR processes comply with DOLE regulations, company policies, and data privacy laws.',
 'Zero non-compliance findings per HR audit within the evaluation period.',
 20.00, 'Scale_1_4', 4),

(3, 'KRA', 'Employee Assistance & Query Resolution',
 'Responding to employee concerns, requests, and HR inquiries in a timely and accurate manner.',
 'Resolves 95% of employee HR queries within 2 business days of receipt.',
 20.00, 'Scale_1_4', 5);

-- Template 4: HR Manager & Supervisor — KRA
REPLACE INTO evaluation_criteria (template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
(4, 'KRA', 'HR Strategy & Workforce Planning',
 'Developing and executing HR strategies aligned with organizational goals and manpower needs.',
 'Submits annual workforce plan and HR roadmap aligned with company objectives by Q1.',
 20.00, 'Scale_1_4', 1),

(4, 'KRA', 'Team Leadership & HR Staff Development',
 'Coaching, evaluating, and developing the HR team to maintain a high-performance department.',
 'Conducts quarterly performance reviews for all direct reports with documented development plans.',
 20.00, 'Scale_1_4', 2),

(4, 'KRA', 'Policy Development & Implementation',
 'Creating, updating, and enforcing HR policies and procedures across the organization.',
 'Reviews and updates at least 2 HR policies per year; achieves 100% policy dissemination rate.',
 20.00, 'Scale_1_4', 3),

(4, 'KRA', 'Performance Management Oversight',
 'Administering the company-wide performance appraisal cycle and ensuring timely completion.',
 'Achieves at least 95% completion rate of performance evaluations per appraisal cycle.',
 20.00, 'Scale_1_4', 4),

(4, 'KRA', 'HR Metrics & Executive Reporting',
 'Tracking key HR indicators and reporting insights to senior management.',
 'Submits monthly HR dashboard report to executive team by the 5th working day of each month.',
 20.00, 'Scale_1_4', 5);

-- Template 5: General (All Departments) — KRA
REPLACE INTO evaluation_criteria (template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
(5, 'KRA', 'Task Completion & Quality of Work',
 'Completing assigned tasks accurately and on time with minimal errors or rework.',
 'Delivers at least 95% of assigned tasks within set deadlines with acceptable quality standards.',
 20.00, 'Scale_1_4', 1),

(5, 'KRA', 'Attendance & Punctuality',
 'Adhering to prescribed work schedule, minimizing tardiness and unauthorized absences.',
 'Maintains tardiness and absence rate within company policy thresholds per evaluation period.',
 20.00, 'Scale_1_4', 2),

(5, 'KRA', 'Teamwork & Collaborative Support',
 'Cooperating constructively with teammates and other departments to achieve shared goals.',
 'Actively participates in team activities and receives no substantiated complaints regarding teamwork.',
 20.00, 'Scale_1_4', 3),

(5, 'KRA', 'Policy & Safety Adherence',
 'Following company rules, standard operating procedures, and workplace safety guidelines.',
 'Zero recorded policy or safety violations per evaluation period.',
 20.00, 'Scale_1_4', 4),

(5, 'KRA', 'Initiative & Continuous Learning',
 'Proactively identifying improvements and pursuing new skills relevant to the role.',
 'Completes at least 1 training or professional development activity per evaluation period.',
 20.00, 'Scale_1_4', 5);

-- ============================================
-- BEHAVIOR CRITERIA (same 8 items for all 5 templates)
-- ============================================

-- Template 1 — Behavior
REPLACE INTO evaluation_criteria (template_id, section, criterion_name, kpi_description, weight, scoring_method, sort_order) VALUES
(1, 'Behavior', 'Positive Attitude',         'Displays positive attitude at work.',                                                              0.00, 'Scale_1_4', 6),
(1, 'Behavior', 'Respect',                   'Shows respect to all people in the organization.',                                                 0.00, 'Scale_1_4', 7),
(1, 'Behavior', 'Accountability',            'Takes full responsibility of the job including special task or assignment.',                        0.00, 'Scale_1_4', 8),
(1, 'Behavior', 'Commitment',                'Demonstrates strong commitment to the job.',                                                       0.00, 'Scale_1_4', 9),
(1, 'Behavior', 'Teamwork',                  'Works cooperatively with others in achieving the goals.',                                          0.00, 'Scale_1_4', 10),
(1, 'Behavior', 'Integrity',                 'Exhibits honesty and strong moral uprightness.',                                                   0.00, 'Scale_1_4', 11),
(1, 'Behavior', 'Continuous Improvement',    'Provides diligent effort to continuously focus on getting better.',                                0.00, 'Scale_1_4', 12),
(1, 'Behavior', 'Excellent Client Experience','Delivers the service beyond the expectations of the internal and external clients.',               0.00, 'Scale_1_4', 13);

-- Template 2 — Behavior
REPLACE INTO evaluation_criteria (template_id, section, criterion_name, kpi_description, weight, scoring_method, sort_order) VALUES
(2, 'Behavior', 'Positive Attitude',         'Displays positive attitude at work.',                                                              0.00, 'Scale_1_4', 6),
(2, 'Behavior', 'Respect',                   'Shows respect to all people in the organization.',                                                 0.00, 'Scale_1_4', 7),
(2, 'Behavior', 'Accountability',            'Takes full responsibility of the job including special task or assignment.',                        0.00, 'Scale_1_4', 8),
(2, 'Behavior', 'Commitment',                'Demonstrates strong commitment to the job.',                                                       0.00, 'Scale_1_4', 9),
(2, 'Behavior', 'Teamwork',                  'Works cooperatively with others in achieving the goals.',                                          0.00, 'Scale_1_4', 10),
(2, 'Behavior', 'Integrity',                 'Exhibits honesty and strong moral uprightness.',                                                   0.00, 'Scale_1_4', 11),
(2, 'Behavior', 'Continuous Improvement',    'Provides diligent effort to continuously focus on getting better.',                                0.00, 'Scale_1_4', 12),
(2, 'Behavior', 'Excellent Client Experience','Delivers the service beyond the expectations of the internal and external clients.',               0.00, 'Scale_1_4', 13);

-- Template 3 — Behavior
REPLACE INTO evaluation_criteria (template_id, section, criterion_name, kpi_description, weight, scoring_method, sort_order) VALUES
(3, 'Behavior', 'Positive Attitude',         'Displays positive attitude at work.',                                                              0.00, 'Scale_1_4', 6),
(3, 'Behavior', 'Respect',                   'Shows respect to all people in the organization.',                                                 0.00, 'Scale_1_4', 7),
(3, 'Behavior', 'Accountability',            'Takes full responsibility of the job including special task or assignment.',                        0.00, 'Scale_1_4', 8),
(3, 'Behavior', 'Commitment',                'Demonstrates strong commitment to the job.',                                                       0.00, 'Scale_1_4', 9),
(3, 'Behavior', 'Teamwork',                  'Works cooperatively with others in achieving the goals.',                                          0.00, 'Scale_1_4', 10),
(3, 'Behavior', 'Integrity',                 'Exhibits honesty and strong moral uprightness.',                                                   0.00, 'Scale_1_4', 11),
(3, 'Behavior', 'Continuous Improvement',    'Provides diligent effort to continuously focus on getting better.',                                0.00, 'Scale_1_4', 12),
(3, 'Behavior', 'Excellent Client Experience','Delivers the service beyond the expectations of the internal and external clients.',               0.00, 'Scale_1_4', 13);

-- Template 4 — Behavior
REPLACE INTO evaluation_criteria (template_id, section, criterion_name, kpi_description, weight, scoring_method, sort_order) VALUES
(4, 'Behavior', 'Positive Attitude',         'Displays positive attitude at work.',                                                              0.00, 'Scale_1_4', 6),
(4, 'Behavior', 'Respect',                   'Shows respect to all people in the organization.',                                                 0.00, 'Scale_1_4', 7),
(4, 'Behavior', 'Accountability',            'Takes full responsibility of the job including special task or assignment.',                        0.00, 'Scale_1_4', 8),
(4, 'Behavior', 'Commitment',                'Demonstrates strong commitment to the job.',                                                       0.00, 'Scale_1_4', 9),
(4, 'Behavior', 'Teamwork',                  'Works cooperatively with others in achieving the goals.',                                          0.00, 'Scale_1_4', 10),
(4, 'Behavior', 'Integrity',                 'Exhibits honesty and strong moral uprightness.',                                                   0.00, 'Scale_1_4', 11),
(4, 'Behavior', 'Continuous Improvement',    'Provides diligent effort to continuously focus on getting better.',                                0.00, 'Scale_1_4', 12),
(4, 'Behavior', 'Excellent Client Experience','Delivers the service beyond the expectations of the internal and external clients.',               0.00, 'Scale_1_4', 13);

-- Template 5 — Behavior
REPLACE INTO evaluation_criteria (template_id, section, criterion_name, kpi_description, weight, scoring_method, sort_order) VALUES
(5, 'Behavior', 'Positive Attitude',         'Displays positive attitude at work.',                                                              0.00, 'Scale_1_4', 6),
(5, 'Behavior', 'Respect',                   'Shows respect to all people in the organization.',                                                 0.00, 'Scale_1_4', 7),
(5, 'Behavior', 'Accountability',            'Takes full responsibility of the job including special task or assignment.',                        0.00, 'Scale_1_4', 8),
(5, 'Behavior', 'Commitment',                'Demonstrates strong commitment to the job.',                                                       0.00, 'Scale_1_4', 9),
(5, 'Behavior', 'Teamwork',                  'Works cooperatively with others in achieving the goals.',                                          0.00, 'Scale_1_4', 10),
(5, 'Behavior', 'Integrity',                 'Exhibits honesty and strong moral uprightness.',                                                   0.00, 'Scale_1_4', 11),
(5, 'Behavior', 'Continuous Improvement',    'Provides diligent effort to continuously focus on getting better.',                                0.00, 'Scale_1_4', 12),
(5, 'Behavior', 'Excellent Client Experience','Delivers the service beyond the expectations of the internal and external clients.',               0.00, 'Scale_1_4', 13);

SET FOREIGN_KEY_CHECKS = 1;