-- Seed data for Evaluation Templates
-- This file initializes sample templates for various departments and roles.

SET FOREIGN_KEY_CHECKS = 0;
USE raquel_hris;

-- 1. CLEANUP (Optional - only if you want to reset templates)
DELETE FROM evaluation_criteria WHERE template_id IN (1, 2, 3, 4, 5);
DELETE FROM evaluation_templates WHERE template_id IN (1, 2, 3, 4, 5);

-- 2. INSERT EVALUATION TEMPLATES
INSERT INTO evaluation_templates (template_id, template_name, description, target_position, evaluation_type, kra_weight, behavior_weight, status, created_by) VALUES
(1, 'HR Manager Performance Review', 'Standard annual evaluation for HR Manager role focusing on recruitment, retention, and policy implementation.', 'HR Manager', 'Annual', 80.00, 20.00, 'Active', 1),
(2, 'IT Department Evaluation', 'Performance metrics for IT staff, including programmers and technical support.', 'IT Staff', 'Annual', 80.00, 20.00, 'Active', 1),
(3, 'Marketing Team Template', 'Focuses on campaign performance, lead generation, and brand growth.', 'Marketing Staff', 'Annual', 80.00, 20.00, 'Active', 1),
(4, 'General Employee Template (All Departments)', 'Standard criteria applicable to all rank-and-file employees across the organization.', 'All Positions', 'Annual', 70.00, 30.00, 'Active', 1),
(5, 'Finance and Accounting Template', 'Focuses on accuracy, reporting timeliness, and financial compliance.', 'Finance Staff', 'Annual', 80.00, 20.00, 'Active', 1);

-- 3. INSERT EVALUATION CRITERIA (KRA Section)
-- Weights sum to 100% per section for each template.

-- Template 1: HR Manager (KRA)
INSERT INTO evaluation_criteria (template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
(1, 'KRA', 'Recruitment Efficiency', 'Effectiveness of hiring process and time-to-fill positions.', '90% of vacant positions filled within 45 days.', 20.00, 'Scale_1_4', 1),
(1, 'KRA', 'Employee Retention', 'Ability to maintain low turnover rates and high employee engagement.', 'Turnover rate kept below 10% annually.', 20.00, 'Scale_1_4', 2),
(1, 'KRA', 'Training Compliance', 'Ensuring employees complete mandatory training and developmental programs.', '100% completion of annual compliance training.', 20.00, 'Scale_1_4', 3),
(1, 'KRA', 'Policy Implementation', 'Updating and enforcing HR policies across all branches.', 'Zero major policy violations reported in audits.', 20.00, 'Scale_1_4', 4),
(1, 'KRA', 'Performance Management', 'Execution of the annual performance appraisal system.', '100% of appraisals completed and reviewed by deadline.', 20.00, 'Scale_1_4', 5);

-- Template 1: HR Manager (Behavior)
INSERT INTO evaluation_criteria (template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
(1, 'Behavior', 'Leadership', 'Ability to inspire and guide the HR team.', 'Consistently mentors staff and leads by example.', 20.00, 'Scale_1_4', 6),
(1, 'Behavior', 'Communication', 'Clarity and professionalism in internal and external communications.', 'Provides clear instructions and responsive to inquiries.', 20.00, 'Scale_1_4', 7),
(1, 'Behavior', 'Integrity', 'Maintaining confidentiality and ethical standards.', 'Strict adherence to data privacy and ethical guidelines.', 20.00, 'Scale_1_4', 8),
(1, 'Behavior', 'Adaptability', 'Responding effectively to organizational changes.', 'Successfully manages transitions and new HR initiatives.', 20.00, 'Scale_1_4', 9),
(1, 'Behavior', 'Decision Making', 'Making sound and timely judgments.', 'Resolves conflicts and issues with balanced judgment.', 20.00, 'Scale_1_4', 10);

-- Template 2: IT Department (KRA)
INSERT INTO evaluation_criteria (template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
(2, 'KRA', 'System Uptime', 'Reliability of server and network infrastructure.', '99.9% uptime for core business applications.', 25.00, 'Scale_1_4', 1),
(2, 'KRA', 'Ticket Resolution', 'Efficiency in handling technical support requests.', '95% of high-priority tickets resolved within SLA.', 25.00, 'Scale_1_4', 2),
(2, 'KRA', 'Software Development', 'Progress and quality of internal software projects.', 'Delivery of project milestones on schedule with <5% bug rate.', 20.00, 'Scale_1_4', 3),
(2, 'KRA', 'Data Security', 'Ensuring data backups and cybersecurity measures.', 'Zero data loss events and successful monthly backup audits.', 20.00, 'Scale_1_4', 4),
(2, 'KRA', 'Inventory Management', 'Tracking of IT assets and hardware.', '100% accuracy in quarterly IT asset inventory.', 10.00, 'Scale_1_4', 5);

-- Template 2: IT Department (Behavior)
INSERT INTO evaluation_criteria (template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
(2, 'Behavior', 'Problem Solving', 'Analytical approach to technical issues.', 'Identifies root causes and implements permanent fixes.', 20.00, 'Scale_1_4', 6),
(2, 'Behavior', 'Technical Excellence', 'Staying updated with technology trends.', 'Applies modern best practices in coding and support.', 20.00, 'Scale_1_4', 7),
(2, 'Behavior', 'Collaboration', 'Working effectively within the IT team and with other departments.', 'Active participant in cross-functional projects.', 20.00, 'Scale_1_4', 8),
(2, 'Behavior', 'Customer Orientation', 'Helpfulness towards non-technical staff.', 'Received positive feedback from users on support quality.', 20.00, 'Scale_1_4', 9),
(2, 'Behavior', 'Innovation', 'Proposing improvements to IT systems.', 'Suggests at least 2 system improvements per year.', 20.00, 'Scale_1_4', 10);

-- Template 3: Marketing (KRA)
INSERT INTO evaluation_criteria (template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
(3, 'KRA', 'Lead Generation', 'Success in attracting potential customers.', 'Generate at least 500 qualified leads per quarter.', 30.00, 'Scale_1_4', 1),
(3, 'KRA', 'Campaign Execution', 'Management and delivery of marketing campaigns.', 'Execute 4 major campaigns annually within budget.', 20.00, 'Scale_1_4', 2),
(3, 'KRA', 'Social Media Growth', 'Increasing brand presence on digital platforms.', '20% increase in social media engagement metrics.', 20.00, 'Scale_1_4', 3),
(3, 'KRA', 'Market Research', 'Quality of data gathered on competitors and trends.', 'Provide monthly market analysis reports with actionable insights.', 15.00, 'Scale_1_4', 4),
(3, 'KRA', 'Brand Consistency', 'Ensuring all materials align with brand guidelines.', 'Zero brand compliance issues in public-facing materials.', 15.00, 'Scale_1_4', 5);

-- Template 3: Marketing (Behavior)
INSERT INTO evaluation_criteria (template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
(3, 'Behavior', 'Creativity', 'Originality in ideas and visual design.', 'Consistently produces fresh and engaging content.', 25.00, 'Scale_1_4', 6),
(3, 'Behavior', 'Initiative', 'Proactive approach to tasks.', 'Takes ownership of projects without constant supervision.', 25.00, 'Scale_1_4', 7),
(3, 'Behavior', 'Communication', 'Effectiveness in presenting ideas.', 'Clearly articulates marketing strategies to stakeholders.', 20.00, 'Scale_1_4', 8),
(3, 'Behavior', 'Teamwork', 'Contribution to the marketing group.', 'Supports teammates during peak campaign periods.', 15.00, 'Scale_1_4', 9),
(3, 'Behavior', 'Result Orientation', 'Focus on achieving target outcomes.', 'Driven by metrics and performance indicators.', 15.00, 'Scale_1_4', 10);

-- Template 4: All Department (KRA)
INSERT INTO evaluation_criteria (template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
(4, 'KRA', 'Attendance & Punctuality', 'Adherence to working hours.', 'Less than 3 lates or absences per quarter.', 30.00, 'Scale_1_4', 1),
(4, 'KRA', 'Work Quality', 'Accuracy and thoroughness of assigned tasks.', 'Tasks completed with minimal errors and re-work.', 25.00, 'Scale_1_4', 2),
(4, 'KRA', 'Workplace Organization (5S)', 'Maintaining a clean and organized workspace.', 'Passes weekly 5S audits with a score of 90% or higher.', 15.00, 'Scale_1_4', 3),
(4, 'KRA', 'Policy Compliance', 'Following company rules and regulations.', 'Zero disciplinary actions or warnings.', 15.00, 'Scale_1_4', 4),
(4, 'KRA', 'Resource Management', 'Efficient use of company supplies and equipment.', 'Reduces waste and reports equipment issues promptly.', 15.00, 'Scale_1_4', 5);

-- Template 4: All Department (Behavior)
INSERT INTO evaluation_criteria (template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
(4, 'Behavior', 'Professionalism', 'Conduct in the workplace.', 'Displays respectful and business-like behavior.', 20.00, 'Scale_1_4', 6),
(4, 'Behavior', 'Teamwork', 'Working with colleagues.', 'Willing to help others and shares knowledge.', 20.00, 'Scale_1_4', 7),
(4, 'Behavior', 'Reliability', 'Consistency in performance.', 'Can be counted on to complete tasks on time.', 20.00, 'Scale_1_4', 8),
(4, 'Behavior', 'Communication', 'Listening and speaking skills.', 'Communicates clearly and effectively with others.', 20.00, 'Scale_1_4', 9),
(4, 'Behavior', 'Learning Agility', 'Willingness to learn and grow.', 'Open to feedback and actively seeks improvement.', 20.00, 'Scale_1_4', 10);

-- Template 5: Finance (KRA)
INSERT INTO evaluation_criteria (template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
(5, 'KRA', 'Reporting Accuracy', 'Precision in financial statements and records.', 'Zero significant errors found in external audits.', 30.00, 'Scale_1_4', 1),
(5, 'KRA', 'Timeliness', 'Meeting deadlines for reports and payments.', 'All monthly financial reports submitted by the 5th working day.', 25.00, 'Scale_1_4', 2),
(5, 'KRA', 'Budget Control', 'Monitoring and reporting on budget variances.', 'Identifies and reports variances within 48 hours of discovery.', 15.00, 'Scale_1_4', 3),
(5, 'KRA', 'Tax Compliance', 'Accurate and timely filing of tax returns.', '100% on-time filing with zero penalties.', 20.00, 'Scale_1_4', 4),
(5, 'KRA', 'Internal Controls', 'Adherence to financial protocols.', 'Ensures all disbursements have proper documentation.', 10.00, 'Scale_1_4', 5);

-- Template 5: Finance (Behavior)
INSERT INTO evaluation_criteria (template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
(5, 'Behavior', 'Attention to Detail', 'Precision in handling numbers and documents.', 'Consistently catches errors before final submission.', 25.00, 'Scale_1_4', 6),
(5, 'Behavior', 'Ethical Conduct', 'Honesty in financial dealings.', 'Maintains the highest standard of financial integrity.', 25.00, 'Scale_1_4', 7),
(5, 'Behavior', 'Analytical Thinking', 'Ability to interpret financial data.', 'Provides meaningful explanations for financial trends.', 20.00, 'Scale_1_4', 8),
(5, 'Behavior', 'Efficiency', 'Optimizing financial processes.', 'Suggests ways to streamline accounting workflows.', 15.00, 'Scale_1_4', 9),
(5, 'Behavior', 'Confidentiality', 'Protecting sensitive financial information.', 'Zero breaches of sensitive company data.', 15.00, 'Scale_1_4', 10);

SET FOREIGN_KEY_CHECKS = 1;
