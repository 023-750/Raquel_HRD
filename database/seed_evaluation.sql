-- Seed data for Evaluation Templates
-- This file initializes sample templates for various departments and roles.

SET FOREIGN_KEY_CHECKS = 0;
USE raquel_hris;

-- 1. CLEANUP (Optional - only if you want to reset templates)
DELETE FROM evaluation_criteria WHERE template_id IN (1, 2, 3, 4, 5);
DELETE FROM evaluation_templates WHERE template_id IN (1, 2, 3, 4, 5);

-- 2. INSERT EVALUATION TEMPLATES
INSERT INTO evaluation_templates (template_id, template_name, description, target_department, evaluation_type, kra_weight, behavior_weight, form_code, revision_date, effective_date_form, status, created_by) VALUES
(1, 'Digital Marketing Specialist Performance Review', 'Performance review for Digital Marketing Specialists targeting SEO, PPC, and social media campaigns.', 'Marketing', 'Annual', 80.00, 20.00, 'HRD Form-013.01', '2026-06-05', '2026-06-05', 'Active', 1),
(2, 'Brand & Creative Specialist Evaluation', 'Evaluation for creative staff focusing on brand alignment, asset creation, and design.', 'Marketing', 'Annual', 80.00, 20.00, 'HRD Form-013.02', '2026-06-05', '2026-06-05', 'Active', 1),
(3, 'Marketing Manager Performance Review', 'Strategic review for Marketing Managers focusing on ROI, leadership, and budget control.', 'Marketing', 'Annual', 80.00, 20.00, 'HRD Form-013.03', '2026-06-05', '2026-06-05', 'Active', 1),
(4, 'General Employee Template (All Departments)', 'Standard evaluation template applicable to rank-and-file employees across all departments.', 'All Departments', 'Annual', 80.00, 20.00, 'HRD Form-013.04', '2026-06-05', '2026-06-05', 'Active', 1),
(5, 'HR Specialist Performance Evaluation', 'Review template for HR Specialists focusing on recruitment, engagement, and training compliance.', 'Human Resources', 'Annual', 80.00, 20.00, 'HRD Form-013.05', '2026-06-05', '2026-06-05', 'Active', 1);

-- 3. INSERT EVALUATION CRITERIA (KRA Section)
-- Weights sum to 100% per section for each template.

-- Template 1: Digital Marketing Specialist (KRA)
INSERT INTO evaluation_criteria (template_id, section, criterion_name, description, weight, scoring_method, sort_order) VALUES
(1, 'KRA', 'Campaign Execution & Performance', 'Designing, launching, and managing digital campaigns.', 20.00, 'Scale_1_4', 1),
(1, 'KRA', 'Content Strategy & Social Media Growth', 'Creating engaging content and expanding brand presence.', 20.00, 'Scale_1_4', 2),
(1, 'KRA', 'Search Engine Optimization & SEM', 'Optimizing website visibility and search marketing performance.', 20.00, 'Scale_1_4', 3),
(1, 'KRA', 'Lead Generation & Conversion Rates', 'Acquiring prospective customers and optimizing funnel conversions.', 20.00, 'Scale_1_4', 4),
(1, 'KRA', 'Marketing Analytics & Reporting', 'Tracking KPIs, preparing reports, and delivering actionable insights.', 20.00, 'Scale_1_4', 5);

-- Template 2: Brand & Creative Specialist (KRA)
INSERT INTO evaluation_criteria (template_id, section, criterion_name, description, weight, scoring_method, sort_order) VALUES
(2, 'KRA', 'Brand Strategy & Consistency', 'Ensuring all visual assets align with brand guidelines.', 20.00, 'Scale_1_4', 1),
(2, 'KRA', 'Creative Asset Development', 'Producing high-quality graphic, video, and copy designs.', 20.00, 'Scale_1_4', 2),
(2, 'KRA', 'Public & Media Relations', 'Managing public relations and maintaining media partnerships.', 20.00, 'Scale_1_4', 3),
(2, 'KRA', 'Event Planning & Activation', 'Executing promotional events and corporate brand activations.', 20.00, 'Scale_1_4', 4),
(2, 'KRA', 'Market Research & Intelligence', 'Conducting competitor analysis and monitoring industry trends.', 20.00, 'Scale_1_4', 5);

-- Template 3: Marketing Manager (KRA)
INSERT INTO evaluation_criteria (template_id, section, criterion_name, description, weight, scoring_method, sort_order) VALUES
(3, 'KRA', 'Strategic Planning & Budgeting', 'Developing annual marketing plans and managing the department budget.', 20.00, 'Scale_1_4', 1),
(3, 'KRA', 'Team Leadership & Development', 'Guiding, mentoring, and evaluating the marketing team members.', 20.00, 'Scale_1_4', 2),
(3, 'KRA', 'ROI & Business Development Alignment', 'Optimizing campaign return on investment and aligning with sales targets.', 20.00, 'Scale_1_4', 3),
(3, 'KRA', 'Campaign Oversight & Approvals', 'Supervising campaign timelines and approving marketing collateral.', 20.00, 'Scale_1_4', 4),
(3, 'KRA', 'Stakeholder Communication & Reporting', 'Presenting marketing performance metrics to executive leadership.', 20.00, 'Scale_1_4', 5);

-- Template 4: General Employee (All Departments) (KRA)
INSERT INTO evaluation_criteria (template_id, section, criterion_name, description, weight, scoring_method, sort_order) VALUES
(4, 'KRA', 'Task Completion & Quality of Work', 'Completing daily tasks accurately with minimal errors.', 20.00, 'Scale_1_4', 1),
(4, 'KRA', 'Attendance & Punctuality', 'Adhering to standard working hours and shift schedules.', 20.00, 'Scale_1_4', 2),
(4, 'KRA', 'Teamwork & Collaborative Support', 'Cooperating with teammates to achieve common objectives.', 20.00, 'Scale_1_4', 3),
(4, 'KRA', 'Policy & Safety Adherence', 'Following company rules, guidelines, and safety procedures.', 20.00, 'Scale_1_4', 4),
(4, 'KRA', 'Initiative & Continuous Learning', 'Proactively looking for improvements and acquiring new skills.', 20.00, 'Scale_1_4', 5);

-- Template 5: HR Specialist (KRA)
INSERT INTO evaluation_criteria (template_id, section, criterion_name, description, weight, scoring_method, sort_order) VALUES
(5, 'KRA', 'Talent Acquisition & Onboarding', 'Source and hire qualified candidates, and facilitate onboarding.', 20.00, 'Scale_1_4', 1),
(5, 'KRA', 'Employee Relations & Engagement', 'Resolving disputes, managing grievances, and organizing employee events.', 20.00, 'Scale_1_4', 2),
(5, 'KRA', 'Training & Development Coordination', 'Identifying skill gaps and coordinating employee training sessions.', 20.00, 'Scale_1_4', 3),
(5, 'KRA', 'HR Compliance & Record Keeping', 'Maintaining 201 files and ensuring labor standard compliance.', 20.00, 'Scale_1_4', 4),
(5, 'KRA', 'Performance Appraisal Administration', 'Monitoring and coordinating the execution of performance appraisal cycles.', 20.00, 'Scale_1_4', 5);

-- 4. INSERT EVALUATION CRITERIA (Behavior Section - Leave as is for each template)
-- Template 1 Behavior
INSERT INTO evaluation_criteria (template_id, section, criterion_name, kpi_description, weight, scoring_method, sort_order) VALUES
(1, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 0.00, 'Scale_1_4', 6),
(1, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 0.00, 'Scale_1_4', 7),
(1, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 0.00, 'Scale_1_4', 8),
(1, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 0.00, 'Scale_1_4', 9),
(1, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 0.00, 'Scale_1_4', 10),
(1, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 0.00, 'Scale_1_4', 11),
(1, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 0.00, 'Scale_1_4', 12),
(1, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 0.00, 'Scale_1_4', 13);

-- Template 2 Behavior
INSERT INTO evaluation_criteria (template_id, section, criterion_name, kpi_description, weight, scoring_method, sort_order) VALUES
(2, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 0.00, 'Scale_1_4', 6),
(2, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 0.00, 'Scale_1_4', 7),
(2, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 0.00, 'Scale_1_4', 8),
(2, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 0.00, 'Scale_1_4', 9),
(2, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 0.00, 'Scale_1_4', 10),
(2, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 0.00, 'Scale_1_4', 11),
(2, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 0.00, 'Scale_1_4', 12),
(2, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 0.00, 'Scale_1_4', 13);

-- Template 3 Behavior
INSERT INTO evaluation_criteria (template_id, section, criterion_name, kpi_description, weight, scoring_method, sort_order) VALUES
(3, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 0.00, 'Scale_1_4', 6),
(3, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 0.00, 'Scale_1_4', 7),
(3, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 0.00, 'Scale_1_4', 8),
(3, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 0.00, 'Scale_1_4', 9),
(3, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 0.00, 'Scale_1_4', 10),
(3, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 0.00, 'Scale_1_4', 11),
(3, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 0.00, 'Scale_1_4', 12),
(3, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 0.00, 'Scale_1_4', 13);

-- Template 4 Behavior
INSERT INTO evaluation_criteria (template_id, section, criterion_name, kpi_description, weight, scoring_method, sort_order) VALUES
(4, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 0.00, 'Scale_1_4', 6),
(4, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 0.00, 'Scale_1_4', 7),
(4, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 0.00, 'Scale_1_4', 8),
(4, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 0.00, 'Scale_1_4', 9),
(4, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 0.00, 'Scale_1_4', 10),
(4, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 0.00, 'Scale_1_4', 11),
(4, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 0.00, 'Scale_1_4', 12),
(4, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 0.00, 'Scale_1_4', 13);

-- Template 5 Behavior
INSERT INTO evaluation_criteria (template_id, section, criterion_name, kpi_description, weight, scoring_method, sort_order) VALUES
(5, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 0.00, 'Scale_1_4', 6),
(5, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 0.00, 'Scale_1_4', 7),
(5, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 0.00, 'Scale_1_4', 8),
(5, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 0.00, 'Scale_1_4', 9),
(5, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 0.00, 'Scale_1_4', 10),
(5, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 0.00, 'Scale_1_4', 11),
(5, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 0.00, 'Scale_1_4', 12),
(5, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 0.00, 'Scale_1_4', 13);

SET FOREIGN_KEY_CHECKS = 1;
