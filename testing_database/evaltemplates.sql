USE raquel_hris;
SET FOREIGN_KEY_CHECKS = 0;

-- =================================-------------------------
-- EVALUATION TEMPLATES SEED (FROM TEST SUITE)
-- =================================-------------------------

REPLACE INTO evaluation_templates (template_id, template_name, description, target_department, status, created_by) VALUES
(1001, 'Marketing Staff Performance Review', 'Annual evaluation for Marketing Staff I/II', 'Marketing', 'Active', 1),
(1002, 'Marketing Leadership Evaluation', 'Executive review for Marketing Managers', 'Marketing', 'Active', 1),
(1003, 'HR Specialist Performance Form', 'Operational review for HR Staff members', 'Human Resources', 'Active', 1),
(1004, 'Payroll Compliance Audit', 'Specific evaluation for HR Payroll/Supervisor roles', 'Human Resources', 'Active', 1),
(1005, 'General HR Operations Review', 'General performance review for all HR personnel', 'Human Resources', 'Active', 1),
(1006, 'General Staff Review', 'Standard evaluation for all departments', 'All Departments', 'Active', 1);

-- ---------------------------------------------------------
-- EVALUATION CRITERIA (Sample Data)
-- ---------------------------------------------------------
REPLACE INTO evaluation_criteria (template_id, section, criterion_name, weight, scoring_method) VALUES
-- Marketing Staff Review (1001)
(1001, 'KRA', 'Campaign Execution', 20.00, 'Scale_1_4'),
(1001, 'KRA', 'Social Media Engagement', 20.00, 'Scale_1_4'),
(1001, 'KRA', 'Market Research', 20.00, 'Scale_1_4'),
(1001, 'KRA', 'Content Creation', 20.00, 'Scale_1_4'),
(1001, 'KRA', 'Event Coordination', 20.00, 'Scale_1_4'),
(1001, 'Behavior', 'Creativity and Innovation', 50.00, 'Scale_1_4'),
(1001, 'Behavior', 'Teamwork', 50.00, 'Scale_1_4'),

-- Marketing Leadership (1002)
(1002, 'KRA', 'Strategic Planning', 20.00, 'Scale_1_4'),
(1002, 'KRA', 'Budget Management', 20.00, 'Scale_1_4'),
(1002, 'KRA', 'Brand Performance', 20.00, 'Scale_1_4'),
(1002, 'KRA', 'Team Performance', 20.00, 'Scale_1_4'),
(1002, 'KRA', 'Market Expansion', 20.00, 'Scale_1_4'),
(1002, 'Behavior', 'Leadership', 50.00, 'Scale_1_4'),
(1002, 'Behavior', 'Strategic Thinking', 50.00, 'Scale_1_4'),

-- HR Specialist (1003)
(1003, 'KRA', 'Recruitment Efficiency', 20.00, 'Scale_1_4'),
(1003, 'KRA', 'Onboarding Experience', 20.00, 'Scale_1_4'),
(1003, 'KRA', 'Employee Relations', 20.00, 'Scale_1_4'),
(1003, 'KRA', 'HRIS Accuracy', 20.00, 'Scale_1_4'),
(1003, 'KRA', 'Compliance Reporting', 20.00, 'Scale_1_4'),
(1003, 'Behavior', 'Confidentiality', 50.00, 'Scale_1_4'),
(1003, 'Behavior', 'Communication', 50.00, 'Scale_1_4'),

-- Payroll Audit (1004)
(1004, 'KRA', 'Payroll Accuracy', 20.00, 'Scale_1_4'),
(1004, 'KRA', 'Tax Filing Timeliness', 20.00, 'Scale_1_4'),
(1004, 'KRA', 'Benefit Administration', 20.00, 'Scale_1_4'),
(1004, 'KRA', 'Statutory Compliance', 20.00, 'Scale_1_4'),
(1004, 'KRA', 'Audit Readiness', 20.00, 'Scale_1_4'),
(1004, 'Behavior', 'Attention to Detail', 50.00, 'Scale_1_4'),
(1004, 'Behavior', 'Integrity', 50.00, 'Scale_1_4'),

-- General HR (1005)
(1005, 'KRA', 'Policy Implementation', 20.00, 'Scale_1_4'),
(1005, 'KRA', 'Employee Engagement', 20.00, 'Scale_1_4'),
(1005, 'KRA', 'Performance Monitoring', 20.00, 'Scale_1_4'),
(1005, 'KRA', 'Records Management', 20.00, 'Scale_1_4'),
(1005, 'KRA', 'Conflict Resolution', 20.00, 'Scale_1_4'),
(1005, 'Behavior', 'Interpersonal Skills', 50.00, 'Scale_1_4'),
(1005, 'Behavior', 'Professionalism', 50.00, 'Scale_1_4'),

-- General Staff Review (1006)
(1006, 'KRA', 'Attendance & Punctuality', 20.00, 'Scale_1_4'),
(1006, 'KRA', 'Quality of Work', 20.00, 'Scale_1_4'),
(1006, 'KRA', 'Efficiency & Productivity', 20.00, 'Scale_1_4'),
(1006, 'KRA', 'Adherence to Policies', 20.00, 'Scale_1_4'),
(1006, 'KRA', '5S & Workplace Maintenance', 20.00, 'Scale_1_4'),
(1006, 'Behavior', 'Professionalism', 50.00, 'Scale_1_4'),
(1006, 'Behavior', 'Reliability', 50.00, 'Scale_1_4');

SET FOREIGN_KEY_CHECKS = 1;
