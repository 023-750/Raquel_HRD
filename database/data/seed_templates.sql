-- =====================================================
-- RAQUEL HRIS -- KRA EVALUATION TEMPLATES & CRITERIA SEED
-- seed_templates.sql
-- =====================================================
-- Purpose  : Populate 5 realistic evaluation templates
--            with 13 criteria items each (5 KRAs + 8 Standard Behavior & Values)
--            for all 12 departments (60 templates total).
-- =====================================================
USE raquel_hris;
SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM evaluation_criteria WHERE template_id BETWEEN 100 AND 199;
DELETE FROM evaluation_templates WHERE template_id BETWEEN 100 AND 199;

-- =====================================================
-- HUMAN RESOURCES DEPARTMENT (100 to 104)
-- =====================================================
REPLACE INTO evaluation_templates
  (template_id, template_name, description, target_department,
   evaluation_type, kra_weight, behavior_weight,
   form_code, revision_date, effective_date_form, status, created_by)
VALUES
  (100, 'Annual Recruitment & Talent Acquisition Performance Evaluation', 'Evaluates candidate sourcing quality, hiring turnaround time, onboarding efficiency, and manpower requisition fulfillment.', 'Human Resources', 'Annual', 80.00, 20.00, 'HR-TMP-001', '2026-01-15', '2026-02-01', 'Active', 1),
  (101, 'Employee Relations & Labor Compliance Effectiveness Assessment', 'Assesses grievance resolution speed, administrative investigation handling, DOLE compliance, and workplace conflict management.', 'Human Resources', 'Quarterly', 80.00, 20.00, 'HR-TMP-002', '2026-01-15', '2026-02-01', 'Active', 1),
  (102, 'Learning and Development Program & Competency Evaluation', 'Evaluates training needs analysis, training execution, post-training evaluation, and employee competency growth.', 'Human Resources', 'Annual', 80.00, 20.00, 'HR-TMP-003', '2026-01-15', '2026-02-01', 'Active', 1),
  (103, 'Compensation, Benefits & Payroll Administration Review', 'Reviews accuracy and timeliness of payroll processing, government remittances (SSS, PhilHealth, Pag-IBIG), and employee benefits inquiries.', 'Human Resources', 'Quarterly', 80.00, 20.00, 'HR-TMP-004', '2026-01-15', '2026-02-01', 'Active', 1),
  (104, 'Performance Management Cycle & HR Operations Efficiency', 'Assesses end-to-end performance appraisal administration, HR records management, policy enforcement, and interdepartmental support.', 'Human Resources', 'Annual', 80.00, 20.00, 'HR-TMP-005', '2026-01-15', '2026-02-01', 'Active', 1);

-- Criteria for Template 100 (Annual Recruitment & Talent Acquisition ...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1000, 100, 'KRA', 'Candidate Sourcing & Talent Pipeline', 'Measures candidate quality, talent pool depth, and alignment with open job requisitions.', 'Measures candidate quality, talent pool depth, and alignment with open job requisitions.', 20.00, 'Scale_1_4', 1),
  (1001, 100, 'KRA', 'Hiring Turnaround Time (TAT)', 'Evaluates speed of filling open requisitions against standard SLA days.', 'Evaluates speed of filling open requisitions against standard SLA days.', 20.00, 'Scale_1_4', 2),
  (1002, 100, 'KRA', 'Offer Acceptance & Onboarding Rate', 'Assesses success rate of extended job offers and completion of onboarding requirements.', 'Assesses success rate of extended job offers and completion of onboarding requirements.', 20.00, 'Scale_1_4', 3),
  (1003, 100, 'KRA', 'Recruitment Compliance & Files', 'Verifies completeness of applicant files, interview scoring sheets, and approvals.', 'Verifies completeness of applicant files, interview scoring sheets, and approvals.', 20.00, 'Scale_1_4', 4),
  (1004, 100, 'KRA', 'Manpower Plan Alignment', 'Monitors compliance with approved department manpower headcounts and budgets.', 'Monitors compliance with approved department manpower headcounts and budgets.', 20.00, 'Scale_1_4', 5),
  (1005, 100, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1006, 100, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1007, 100, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1008, 100, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1009, 100, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1010, 100, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1011, 100, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1012, 100, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 101 (Employee Relations & Labor Compliance Ef...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1013, 101, 'KRA', 'Grievance & Dispute Resolution Speed', 'Measures turnaround time in handling employee complaints and labor concerns.', 'Measures turnaround time in handling employee complaints and labor concerns.', 20.00, 'Scale_1_4', 1),
  (1014, 101, 'KRA', 'Investigation & Administrative Due Process', 'Evaluates thoroughness of administrative hearing notices, explanation letters, and memos.', 'Evaluates thoroughness of administrative hearing notices, explanation letters, and memos.', 20.00, 'Scale_1_4', 2),
  (1015, 101, 'KRA', 'DOLE & Labor Law Compliance', 'Ensures zero labor compliance violations and adherence to statutory employment standards.', 'Ensures zero labor compliance violations and adherence to statutory employment standards.', 20.00, 'Scale_1_4', 3),
  (1016, 101, 'KRA', 'Employee Counseling & Wellness', 'Assesses frequency and effectiveness of employee guidance and counseling sessions.', 'Assesses frequency and effectiveness of employee guidance and counseling sessions.', 20.00, 'Scale_1_4', 4),
  (1017, 101, 'KRA', 'Labor Management Relations', 'Monitors proactive initiatives to maintain a positive, harmonious work environment.', 'Monitors proactive initiatives to maintain a positive, harmonious work environment.', 20.00, 'Scale_1_4', 5),
  (1018, 101, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1019, 101, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1020, 101, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1021, 101, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1022, 101, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1023, 101, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1024, 101, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1025, 101, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 102 (Learning and Development Program & Compe...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1026, 102, 'KRA', 'Training Calendar Execution Rate', 'Measures percentage of scheduled training sessions conducted as planned.', 'Measures percentage of scheduled training sessions conducted as planned.', 20.00, 'Scale_1_4', 1),
  (1027, 102, 'KRA', 'Post-Training Assessment Improvement', 'Evaluates employee competency test score gains following training modules.', 'Evaluates employee competency test score gains following training modules.', 20.00, 'Scale_1_4', 2),
  (1028, 102, 'KRA', 'Trainer & Module Quality Score', 'Assesses participant feedback ratings for training content, venue, and delivery.', 'Assesses participant feedback ratings for training content, venue, and delivery.', 20.00, 'Scale_1_4', 3),
  (1029, 102, 'KRA', 'Training Needs Analysis Accuracy', 'Verifies alignment of training programs with actual departmental skill gaps.', 'Verifies alignment of training programs with actual departmental skill gaps.', 20.00, 'Scale_1_4', 4),
  (1030, 102, 'KRA', 'L&D Budget & Resource Efficiency', 'Monitors cost-effectiveness per training hour and optimal use of training assets.', 'Monitors cost-effectiveness per training hour and optimal use of training assets.', 20.00, 'Scale_1_4', 5),
  (1031, 102, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1032, 102, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1033, 102, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1034, 102, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1035, 102, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1036, 102, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1037, 102, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1038, 102, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 103 (Compensation, Benefits & Payroll Adminis...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1039, 103, 'KRA', 'Payroll Computation Accuracy Rate', 'Measures error-free calculation of salaries, overtime, allowances, and deductions.', 'Measures error-free calculation of salaries, overtime, allowances, and deductions.', 20.00, 'Scale_1_4', 1),
  (1040, 103, 'KRA', 'Payroll Release Timeliness', 'Evaluates strict adherence to semi-monthly salary release schedules.', 'Evaluates strict adherence to semi-monthly salary release schedules.', 20.00, 'Scale_1_4', 2),
  (1041, 103, 'KRA', 'Government Statutory Remittances', 'Ensures on-time, accurate filing and payment of SSS, PhilHealth, and Pag-IBIG funds.', 'Ensures on-time, accurate filing and payment of SSS, PhilHealth, and Pag-IBIG funds.', 20.00, 'Scale_1_4', 3),
  (1042, 103, 'KRA', 'Benefits & Loan Request SLA', 'Assesses response speed to employee inquiries on HMO, leaves, and salary loans.', 'Assesses response speed to employee inquiries on HMO, leaves, and salary loans.', 20.00, 'Scale_1_4', 4),
  (1043, 103, 'KRA', '13th Month & Final Pay Processing', 'Verifies accurate calculation and timely issuance of final pay and 13th month pay.', 'Verifies accurate calculation and timely issuance of final pay and 13th month pay.', 20.00, 'Scale_1_4', 5),
  (1044, 103, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1045, 103, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1046, 103, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1047, 103, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1048, 103, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1049, 103, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1050, 103, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1051, 103, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 104 (Performance Management Cycle & HR Operat...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1052, 104, 'KRA', 'Performance Cycle Completion Rate', 'Measures percentage of employee performance reviews completed on schedule.', 'Measures percentage of employee performance reviews completed on schedule.', 20.00, 'Scale_1_4', 1),
  (1053, 104, 'KRA', '201 File & Document Audit Score', 'Evaluates completeness, security, and archiving of employee 201 records.', 'Evaluates completeness, security, and archiving of employee 201 records.', 20.00, 'Scale_1_4', 2),
  (1054, 104, 'KRA', 'HR Policy Updating & Enforcement', 'Assesses maintenance of employee handbook rules and company policy dissemination.', 'Assesses maintenance of employee handbook rules and company policy dissemination.', 20.00, 'Scale_1_4', 3),
  (1055, 104, 'KRA', 'Interdepartmental Service Responsiveness', 'Measures speed and courtesy in resolving administrative requests from branches.', 'Measures speed and courtesy in resolving administrative requests from branches.', 20.00, 'Scale_1_4', 4),
  (1056, 104, 'KRA', 'HR Information System (HRIS) Accuracy', 'Verifies data integrity of employee profiles, movements, and attendance logs.', 'Verifies data integrity of employee profiles, movements, and attendance logs.', 20.00, 'Scale_1_4', 5),
  (1057, 104, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1058, 104, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1059, 104, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1060, 104, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1061, 104, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1062, 104, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1063, 104, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1064, 104, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- =====================================================
-- ACQUIRED PROPERTIES DEPARTMENT (105 to 109)
-- =====================================================
REPLACE INTO evaluation_templates
  (template_id, template_name, description, target_department,
   evaluation_type, kra_weight, behavior_weight,
   form_code, revision_date, effective_date_form, status, created_by)
VALUES
  (105, 'Acquired Property Disposition Performance Evaluation', 'Measures sales target achievement, proceeds recovery, public bidding execution, and property monetization rate.', 'Acquired Properties', 'Annual', 80.00, 20.00, 'AP-TMP-001', '2026-01-15', '2026-02-01', 'Active', 1),
  (106, 'Property Appraisal Accuracy and Timeliness Review', 'Evaluates site inspection quality, market valuation accuracy, turnaround time, and valuation report completeness.', 'Acquired Properties', 'Quarterly', 80.00, 20.00, 'AP-TMP-002', '2026-01-15', '2026-02-01', 'Active', 1),
  (107, 'Property Inventory Management & Custody Control', 'Assesses physical inventory match, status updating, asset preservation, and unauthorized occupancy prevention.', 'Acquired Properties', 'Annual', 80.00, 20.00, 'AP-TMP-003', '2026-01-15', '2026-02-01', 'Active', 1),
  (108, 'Legal Documentation and Title Processing Evaluation', 'Reviews title consolidation speed, real property tax filings, legal clearance resolution, and document archiving.', 'Acquired Properties', 'Quarterly', 80.00, 20.00, 'AP-TMP-004', '2026-01-15', '2026-02-01', 'Active', 1),
  (109, 'Property Maintenance and Asset Preservation Assessment', 'Evaluates routine maintenance execution, vendor repair cost control, safety risk mitigation, and property salability.', 'Acquired Properties', 'Annual', 80.00, 20.00, 'AP-TMP-005', '2026-01-15', '2026-02-01', 'Active', 1);

-- Criteria for Template 105 (Acquired Property Disposition Performanc...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1065, 105, 'KRA', 'Property Disposition & Sales Target', 'Measures percentage of target foreclosed/acquired properties sold or monetized.', 'Measures percentage of target foreclosed/acquired properties sold or monetized.', 20.00, 'Scale_1_4', 1),
  (1066, 105, 'KRA', 'Proceeds vs Appraised Value Realization', 'Evaluates financial recovery rate against approved property valuation benchmarks.', 'Evaluates financial recovery rate against approved property valuation benchmarks.', 20.00, 'Scale_1_4', 2),
  (1067, 105, 'KRA', 'Auction & Bidding Process Execution', 'Assesses compliance and efficiency in conducting public property auctions.', 'Assesses compliance and efficiency in conducting public property auctions.', 20.00, 'Scale_1_4', 3),
  (1068, 105, 'KRA', 'Holding Cost Reduction', 'Monitors reduction of idle property carrying costs, security fees, and tax obligations.', 'Monitors reduction of idle property carrying costs, security fees, and tax obligations.', 20.00, 'Scale_1_4', 4),
  (1069, 105, 'KRA', 'Buyer Contract & Closing Turnaround', 'Evaluates speed of processing sales agreements and payment turn-overs.', 'Evaluates speed of processing sales agreements and payment turn-overs.', 20.00, 'Scale_1_4', 5),
  (1070, 105, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1071, 105, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1072, 105, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1073, 105, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1074, 105, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1075, 105, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1076, 105, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1077, 105, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 106 (Property Appraisal Accuracy and Timeline...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1078, 106, 'KRA', 'Appraisal Report Thoroughness', 'Evaluates depth of property inspection, market comparables, and technical reports.', 'Evaluates depth of property inspection, market comparables, and technical reports.', 20.00, 'Scale_1_4', 1),
  (1079, 106, 'KRA', 'Inspection Turnaround Time', 'Measures speed of conducting site visits and issuing formal appraisal results.', 'Measures speed of conducting site visits and issuing formal appraisal results.', 20.00, 'Scale_1_4', 2),
  (1080, 106, 'KRA', 'Market Price Variance Accuracy', 'Assesses precision by comparing appraised values against final buyer prices.', 'Assesses precision by comparing appraised values against final buyer prices.', 20.00, 'Scale_1_4', 3),
  (1081, 106, 'KRA', 'BSP Valuation Rule Compliance', 'Verifies strict adherence to BSP and internal property appraisal guidelines.', 'Verifies strict adherence to BSP and internal property appraisal guidelines.', 20.00, 'Scale_1_4', 4),
  (1082, 106, 'KRA', 'Comparable Market Data Updating', 'Monitors continuous updating of real estate market values in target areas.', 'Monitors continuous updating of real estate market values in target areas.', 20.00, 'Scale_1_4', 5),
  (1083, 106, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1084, 106, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1085, 106, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1086, 106, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1087, 106, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1088, 106, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1089, 106, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1090, 106, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 107 (Property Inventory Management & Custody ...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1091, 107, 'KRA', 'Physical Inventory Match Rate', 'Measures agreement between physical property status and system database records.', 'Measures agreement between physical property status and system database records.', 20.00, 'Scale_1_4', 1),
  (1092, 107, 'KRA', 'Periodic Inspection Frequency', 'Evaluates adherence to scheduled site inspection routines for all assets.', 'Evaluates adherence to scheduled site inspection routines for all assets.', 20.00, 'Scale_1_4', 2),
  (1093, 107, 'KRA', 'System Status Update Speed', 'Assesses promptness in logging newly acquired, pending, or sold assets.', 'Assesses promptness in logging newly acquired, pending, or sold assets.', 20.00, 'Scale_1_4', 3),
  (1094, 107, 'KRA', 'Illegal Occupant & Encroachment Mitigation', 'Monitors prompt actions to prevent or resolve illegal property occupancy.', 'Monitors prompt actions to prevent or resolve illegal property occupancy.', 20.00, 'Scale_1_4', 4),
  (1095, 107, 'KRA', 'Property Custody & Tagging Order', 'Verifies proper physical tagging and boundary security of acquired real estate.', 'Verifies proper physical tagging and boundary security of acquired real estate.', 20.00, 'Scale_1_4', 5),
  (1096, 107, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1097, 107, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1098, 107, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1099, 107, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1100, 107, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1101, 107, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1102, 107, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1103, 107, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 108 (Legal Documentation and Title Processing...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1104, 108, 'KRA', 'Title Transfer Consolidation Rate', 'Measures completion rate of transferring land titles under company name.', 'Measures completion rate of transferring land titles under company name.', 20.00, 'Scale_1_4', 1),
  (1105, 108, 'KRA', 'Real Property Tax Amortization Filing', 'Evaluates on-time payment of real estate taxes and municipal assessments.', 'Evaluates on-time payment of real estate taxes and municipal assessments.', 20.00, 'Scale_1_4', 2),
  (1106, 108, 'KRA', 'Adverse Claim & Litigation Resolution', 'Assesses progress in clearing legal encumbrances or pending court claims.', 'Assesses progress in clearing legal encumbrances or pending court claims.', 20.00, 'Scale_1_4', 3),
  (1107, 108, 'KRA', 'Deed of Sale & Notarization Speed', 'Verifies quick turnaround of legal deeds, contracts, and registry filings.', 'Verifies quick turnaround of legal deeds, contracts, and registry filings.', 20.00, 'Scale_1_4', 4),
  (1108, 108, 'KRA', 'Legal Repository Organization', 'Monitors orderly archiving and access control of land titles and deeds.', 'Monitors orderly archiving and access control of land titles and deeds.', 20.00, 'Scale_1_4', 5),
  (1109, 108, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1110, 108, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1111, 108, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1112, 108, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1113, 108, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1114, 108, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1115, 108, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1116, 108, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 109 (Property Maintenance and Asset Preservat...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1117, 109, 'KRA', 'Preventive Upkeep Schedule Adherence', 'Measures completion of routine physical maintenance for acquired buildings.', 'Measures completion of routine physical maintenance for acquired buildings.', 20.00, 'Scale_1_4', 1),
  (1118, 109, 'KRA', 'Vendor & Contractor Cost Efficiency', 'Evaluates competitive pricing and quality of third-party repair contractors.', 'Evaluates competitive pricing and quality of third-party repair contractors.', 20.00, 'Scale_1_4', 2),
  (1119, 109, 'KRA', 'Hazard & Structural Risk Mitigation', 'Assesses prompt repair of structural defects, leaks, or safety hazards.', 'Assesses prompt repair of structural defects, leaks, or safety hazards.', 20.00, 'Scale_1_4', 3),
  (1120, 109, 'KRA', 'Curb Appeal & Salability State', 'Monitors property cleanliness and readiness for prospective buyer viewing.', 'Monitors property cleanliness and readiness for prospective buyer viewing.', 20.00, 'Scale_1_4', 4),
  (1121, 109, 'KRA', 'Emergency Repair Response Time', 'Evaluates speed in addressing unexpected property damage from weather or incidents.', 'Evaluates speed in addressing unexpected property damage from weather or incidents.', 20.00, 'Scale_1_4', 5),
  (1122, 109, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1123, 109, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1124, 109, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1125, 109, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1126, 109, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1127, 109, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1128, 109, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1129, 109, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- =====================================================
-- AUDIT DEPARTMENT (110 to 114)
-- =====================================================
REPLACE INTO evaluation_templates
  (template_id, template_name, description, target_department,
   evaluation_type, kra_weight, behavior_weight,
   form_code, revision_date, effective_date_form, status, created_by)
VALUES
  (110, 'Branch Audit Execution and Risk Reporting Evaluation', 'Assesses audit plan coverage, findings validity, report turnaround time, and risk-based recommendation quality.', 'Audit', 'Annual', 80.00, 20.00, 'AUD-TMP-001', '2026-01-15', '2026-02-01', 'Active', 1),
  (111, 'Audit Findings Follow-Up and Resolution Monitoring', 'Evaluates corrective action tracking, repeat finding reduction, timely escalation, and resolution validation.', 'Audit', 'Quarterly', 80.00, 20.00, 'AUD-TMP-002', '2026-01-15', '2026-02-01', 'Active', 1),
  (112, 'Cash Count and Vault Audit Performance Review', 'Reviews cash count accuracy, vault reconciliation precision, shortage/overage detection, and cash handling policy checks.', 'Audit', 'Annual', 80.00, 20.00, 'AUD-TMP-003', '2026-01-15', '2026-02-01', 'Active', 1),
  (113, 'Inventory and Collateral Verification Audit Evaluation', 'Assesses pledged collateral match, appraisal rule compliance audit, vault security checks, and discrepancy reporting.', 'Audit', 'Quarterly', 80.00, 20.00, 'AUD-TMP-004', '2026-01-15', '2026-02-01', 'Active', 1),
  (114, 'Regulatory Compliance and Anti-Fraud Audit Assessment', 'Evaluates BSP circular audit coverage, AMLA transaction checklist checks, fraud risk detection, and report quality.', 'Audit', 'Annual', 80.00, 20.00, 'AUD-TMP-005', '2026-01-15', '2026-02-01', 'Active', 1);

-- Criteria for Template 110 (Branch Audit Execution and Risk Reportin...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1130, 110, 'KRA', 'Branch Audit Calendar Coverage', 'Measures percentage of assigned branches audited against annual audit plan.', 'Measures percentage of assigned branches audited against annual audit plan.', 20.00, 'Scale_1_4', 1),
  (1131, 110, 'KRA', 'Audit Findings Validity & Evidence', 'Evaluates accuracy, proof quality, and risk level assignment of findings.', 'Evaluates accuracy, proof quality, and risk level assignment of findings.', 20.00, 'Scale_1_4', 2),
  (1132, 110, 'KRA', 'Audit Report Turnaround Time', 'Assesses submission speed of formal audit reports post-field engagement.', 'Assesses submission speed of formal audit reports post-field engagement.', 20.00, 'Scale_1_4', 3),
  (1133, 110, 'KRA', 'Actionable Recommendation Quality', 'Verifies practical value of control recommendations to prevent financial loss.', 'Verifies practical value of control recommendations to prevent financial loss.', 20.00, 'Scale_1_4', 4),
  (1134, 110, 'KRA', 'Audit Working Paper Completeness', 'Evaluates organization and archiving of supporting audit working papers.', 'Evaluates organization and archiving of supporting audit working papers.', 20.00, 'Scale_1_4', 5),
  (1135, 110, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1136, 110, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1137, 110, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1138, 110, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1139, 110, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1140, 110, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1141, 110, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1142, 110, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 111 (Audit Findings Follow-Up and Resolution ...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1143, 111, 'KRA', 'Corrective Action Item Resolution Rate', 'Measures percentage of audit findings validated and closed on schedule.', 'Measures percentage of audit findings validated and closed on schedule.', 20.00, 'Scale_1_4', 1),
  (1144, 111, 'KRA', 'Repeat Finding Reduction Score', 'Evaluates decrease in recurring operational non-compliance across branches.', 'Evaluates decrease in recurring operational non-compliance across branches.', 20.00, 'Scale_1_4', 2),
  (1145, 111, 'KRA', 'High-Risk Issue Escalation Timeliness', 'Assesses promptness in escalating overdue critical findings to management.', 'Assesses promptness in escalating overdue critical findings to management.', 20.00, 'Scale_1_4', 3),
  (1146, 111, 'KRA', 'Follow-Up Verification Quality', 'Verifies strict evidence checking before closing out audit findings.', 'Verifies strict evidence checking before closing out audit findings.', 20.00, 'Scale_1_4', 4),
  (1147, 111, 'KRA', 'Branch Management Briefing Clarity', 'Monitors quality of exit briefings provided to branch heads post-audit.', 'Monitors quality of exit briefings provided to branch heads post-audit.', 20.00, 'Scale_1_4', 5),
  (1148, 111, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1149, 111, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1150, 111, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1151, 111, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1152, 111, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1153, 111, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1154, 111, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1155, 111, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 112 (Cash Count and Vault Audit Performance R...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1156, 112, 'KRA', 'Surprise Cash Count Precision', 'Measures accuracy in conducting unannounced cash counts and vault audits.', 'Measures accuracy in conducting unannounced cash counts and vault audits.', 20.00, 'Scale_1_4', 1),
  (1157, 112, 'KRA', 'Cash Shortage/Overage Detection', 'Evaluates detection speed and immediate reporting of cash variance items.', 'Evaluates detection speed and immediate reporting of cash variance items.', 20.00, 'Scale_1_4', 2),
  (1158, 112, 'KRA', 'Dual Control & Vault Key Audit', 'Assesses verification of dual key rules, vault limits, and security logs.', 'Assesses verification of dual key rules, vault limits, and security logs.', 20.00, 'Scale_1_4', 3),
  (1159, 112, 'KRA', 'Petty Cash & Float Reconciliation', 'Verifies completeness of petty cash vouchers and branch float balances.', 'Verifies completeness of petty cash vouchers and branch float balances.', 20.00, 'Scale_1_4', 4),
  (1160, 112, 'KRA', 'Cash Audit Documentation Quality', 'Monitors accuracy of cash count sheets and supervisory sign-offs.', 'Monitors accuracy of cash count sheets and supervisory sign-offs.', 20.00, 'Scale_1_4', 5),
  (1161, 112, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1162, 112, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1163, 112, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1164, 112, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1165, 112, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1166, 112, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1167, 112, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1168, 112, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 113 (Inventory and Collateral Verification Au...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1169, 113, 'KRA', 'Pledged Collateral Match Rate', 'Measures agreement between physical pawned items and pawn ticket records.', 'Measures agreement between physical pawned items and pawn ticket records.', 20.00, 'Scale_1_4', 1),
  (1170, 113, 'KRA', 'Appraisal Rate Compliance Audit', 'Evaluates audit checks on gold appraisal rates and loan-to-value caps.', 'Evaluates audit checks on gold appraisal rates and loan-to-value caps.', 20.00, 'Scale_1_4', 2),
  (1171, 113, 'KRA', 'Pawned Vault Safe Storage Check', 'Assesses vault layout, seal integrity, and collateral security measures.', 'Assesses vault layout, seal integrity, and collateral security measures.', 20.00, 'Scale_1_4', 3),
  (1172, 113, 'KRA', 'Collateral Discrepancy Escalation', 'Monitors speed of reporting missing, mislabeled, or damaged pawned goods.', 'Monitors speed of reporting missing, mislabeled, or damaged pawned goods.', 20.00, 'Scale_1_4', 4),
  (1173, 113, 'KRA', 'Inventory Audit Sampling Depth', 'Verifies sufficient statistical sample coverage during collateral audits.', 'Verifies sufficient statistical sample coverage during collateral audits.', 20.00, 'Scale_1_4', 5),
  (1174, 113, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1175, 113, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1176, 113, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1177, 113, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1178, 113, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1179, 113, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1180, 113, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1181, 113, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 114 (Regulatory Compliance and Anti-Fraud Aud...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1182, 114, 'KRA', 'BSP & AMLA Audit Checklist Coverage', 'Measures thoroughness of regulatory compliance audit checks.', 'Measures thoroughness of regulatory compliance audit checks.', 20.00, 'Scale_1_4', 1),
  (1183, 114, 'KRA', 'Regulatory Breach Risk Identification', 'Evaluates detection of AMLA filing gaps or municipal licensing lapses.', 'Evaluates detection of AMLA filing gaps or municipal licensing lapses.', 20.00, 'Scale_1_4', 2),
  (1184, 114, 'KRA', 'Fraud Indicator & Warning Sign Spotting', 'Assesses proactive identification of suspicious transactions or fraud risks.', 'Assesses proactive identification of suspicious transactions or fraud risks.', 20.00, 'Scale_1_4', 3),
  (1185, 114, 'KRA', 'Compliance Audit Report Precision', 'Verifies clarity of reporting potential regulatory fines and non-compliance.', 'Verifies clarity of reporting potential regulatory fines and non-compliance.', 20.00, 'Scale_1_4', 4),
  (1186, 114, 'KRA', 'Special Investigation Execution', 'Monitors efficiency when assigned to conduct targeted fraud investigations.', 'Monitors efficiency when assigned to conduct targeted fraud investigations.', 20.00, 'Scale_1_4', 5),
  (1187, 114, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1188, 114, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1189, 114, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1190, 114, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1191, 114, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1192, 114, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1193, 114, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1194, 114, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- =====================================================
-- BUSINESS DEVELOPMENT DEPARTMENT (115 to 119)
-- =====================================================
REPLACE INTO evaluation_templates
  (template_id, template_name, description, target_department,
   evaluation_type, kra_weight, behavior_weight,
   form_code, revision_date, effective_date_form, status, created_by)
VALUES
  (115, 'Branch Expansion and Site Development Evaluation', 'Evaluates site pipeline quality, feasibility study accuracy, site conversion rate, and branch launch timeline adherence.', 'Business Development', 'Annual', 80.00, 20.00, 'BD-TMP-001', '2026-01-15', '2026-02-01', 'Active', 1),
  (116, 'Strategic Partnership and Tie-Up Performance Review', 'Assesses corporate tie-up acquisition, revenue contribution, MOA contract negotiation, and partner relationship quality.', 'Business Development', 'Quarterly', 80.00, 20.00, 'BD-TMP-002', '2026-01-15', '2026-02-01', 'Active', 1),
  (117, 'Market Research and Feasibility Study Evaluation', 'Reviews consumer research depth, financial projection reliability, report turnaround time, and strategic recommendations.', 'Business Development', 'Annual', 80.00, 20.00, 'BD-TMP-003', '2026-01-15', '2026-02-01', 'Active', 1),
  (118, 'Competitor Intelligence and Industry Benchmarking', 'Evaluates competitive monitoring frequency, market trend analysis, tactical counter-proposals, and data reliability.', 'Business Development', 'Quarterly', 80.00, 20.00, 'BD-TMP-004', '2026-01-15', '2026-02-01', 'Active', 1),
  (119, 'New Product and Service Launch Assessment', 'Assesses new product proposal volume, launch timeline execution, initial uptake metrics, and operational hand-off quality.', 'Business Development', 'Annual', 80.00, 20.00, 'BD-TMP-005', '2026-01-15', '2026-02-01', 'Active', 1);

-- Criteria for Template 115 (Branch Expansion and Site Development Ev...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1195, 115, 'KRA', 'Site Pipeline & Identification Volume', 'Measures number of high-potential branch site locations vetted.', 'Measures number of high-potential branch site locations vetted.', 20.00, 'Scale_1_4', 1),
  (1196, 115, 'KRA', 'Feasibility Study & Foot Traffic Analysis', 'Evaluates demographic analysis quality and ROI projection accuracy.', 'Evaluates demographic analysis quality and ROI projection accuracy.', 20.00, 'Scale_1_4', 2),
  (1197, 115, 'KRA', 'Site Approval & Lease Conversion Rate', 'Assesses percentage of recommended locations approved by management.', 'Assesses percentage of recommended locations approved by management.', 20.00, 'Scale_1_4', 3),
  (1198, 115, 'KRA', 'Branch Opening Timeline Adherence', 'Monitors speed of coordinating site turn-over for branch opening.', 'Monitors speed of coordinating site turn-over for branch opening.', 20.00, 'Scale_1_4', 4),
  (1199, 115, 'KRA', 'Lease Term & Rent Negotiation Quality', 'Evaluates success in securing favorable lease rates and rental terms.', 'Evaluates success in securing favorable lease rates and rental terms.', 20.00, 'Scale_1_4', 5),
  (1200, 115, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1201, 115, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1202, 115, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1203, 115, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1204, 115, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1205, 115, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1206, 115, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1207, 115, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 116 (Strategic Partnership and Tie-Up Perform...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1208, 116, 'KRA', 'Corporate Partnership Deal Closure', 'Measures number of new remittance, payment, or commercial tie-ups signed.', 'Measures number of new remittance, payment, or commercial tie-ups signed.', 20.00, 'Scale_1_4', 1),
  (1209, 116, 'KRA', 'Partner Revenue Target Contribution', 'Evaluates actual fee revenue generated from strategic business tie-ups.', 'Evaluates actual fee revenue generated from strategic business tie-ups.', 20.00, 'Scale_1_4', 2),
  (1210, 116, 'KRA', 'MOA Term & Legal Negotiation Quality', 'Assesses fee structures, legal compliance, and contract terms optimization.', 'Assesses fee structures, legal compliance, and contract terms optimization.', 20.00, 'Scale_1_4', 3),
  (1211, 116, 'KRA', 'Partner Operational SLA Monitoring', 'Monitors ongoing operational alignment and performance of active partners.', 'Monitors ongoing operational alignment and performance of active partners.', 20.00, 'Scale_1_4', 4),
  (1212, 116, 'KRA', 'Business Network Expansion Scope', 'Evaluates growth in institutional relationship network across key sectors.', 'Evaluates growth in institutional relationship network across key sectors.', 20.00, 'Scale_1_4', 5),
  (1213, 116, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1214, 116, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1215, 116, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1216, 116, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1217, 116, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1218, 116, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1219, 116, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1220, 116, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 117 (Market Research and Feasibility Study Ev...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1221, 117, 'KRA', 'Market Research Credibility & Depth', 'Evaluates quality, data sources, and sample validity of research reports.', 'Evaluates quality, data sources, and sample validity of research reports.', 20.00, 'Scale_1_4', 1),
  (1222, 117, 'KRA', 'Financial Projection Variance Score', 'Measures agreement between projected vs actual new branch revenue.', 'Measures agreement between projected vs actual new branch revenue.', 20.00, 'Scale_1_4', 2),
  (1223, 117, 'KRA', 'Research Delivery Turnaround Time', 'Assesses speed in fulfilling executive market intelligence requests.', 'Assesses speed in fulfilling executive market intelligence requests.', 20.00, 'Scale_1_4', 3),
  (1224, 117, 'KRA', 'Strategic Recommendation Feasibility', 'Verifies actionability of expansion recommendations presented to board.', 'Verifies actionability of expansion recommendations presented to board.', 20.00, 'Scale_1_4', 4),
  (1225, 117, 'KRA', 'Consumer Demand Identification', 'Monitors proactive spot-checks of emerging customer financial service needs.', 'Monitors proactive spot-checks of emerging customer financial service needs.', 20.00, 'Scale_1_4', 5),
  (1226, 117, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1227, 117, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1228, 117, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1229, 117, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1230, 117, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1231, 117, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1232, 117, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1233, 117, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 118 (Competitor Intelligence and Industry Ben...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1234, 118, 'KRA', 'Competitor Benchmark Monitoring Cadence', 'Measures regularity of tracking competitor interest rates and gold appraisal rates.', 'Measures regularity of tracking competitor interest rates and gold appraisal rates.', 20.00, 'Scale_1_4', 1),
  (1235, 118, 'KRA', 'Industry Trend Analysis Depth', 'Evaluates insights delivered on pawnshop and fintech industry developments.', 'Evaluates insights delivered on pawnshop and fintech industry developments.', 20.00, 'Scale_1_4', 2),
  (1236, 118, 'KRA', 'Tactical Counter-Strategy Quality', 'Assesses quality of proposals submitted to counter competitor campaigns.', 'Assesses quality of proposals submitted to counter competitor campaigns.', 20.00, 'Scale_1_4', 3),
  (1237, 118, 'KRA', 'Field Intelligence Data Accuracy', 'Verifies reliability of mystery shopping and field price data collected.', 'Verifies reliability of mystery shopping and field price data collected.', 20.00, 'Scale_1_4', 4),
  (1238, 118, 'KRA', 'Executive Intelligence Summary Speed', 'Monitors prompt dissemination of urgent competitor intelligence updates.', 'Monitors prompt dissemination of urgent competitor intelligence updates.', 20.00, 'Scale_1_4', 5),
  (1239, 118, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1240, 118, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1241, 118, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1242, 118, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1243, 118, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1244, 118, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1245, 118, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1246, 118, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 119 (New Product and Service Launch Assessmen...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1247, 119, 'KRA', 'New Product Concept Generation', 'Measures number of viable financial product proposals created.', 'Measures number of viable financial product proposals created.', 20.00, 'Scale_1_4', 1),
  (1248, 119, 'KRA', 'Product Development Timeline SLA', 'Evaluates adherence to product milestones from pilot to full rollout.', 'Evaluates adherence to product milestones from pilot to full rollout.', 20.00, 'Scale_1_4', 2),
  (1249, 119, 'KRA', 'Initial Customer Adoption Metrics', 'Assesses transaction volume and customer uptake during post-launch phase.', 'Assesses transaction volume and customer uptake during post-launch phase.', 20.00, 'Scale_1_4', 3),
  (1250, 119, 'KRA', 'Product Manual & Operations Hand-Off', 'Verifies complete delivery of operational guidelines to branch personnel.', 'Verifies complete delivery of operational guidelines to branch personnel.', 20.00, 'Scale_1_4', 4),
  (1251, 119, 'KRA', 'Post-Launch Product Refinement', 'Monitors prompt adjustments based on pilot feedback and user metrics.', 'Monitors prompt adjustments based on pilot feedback and user metrics.', 20.00, 'Scale_1_4', 5),
  (1252, 119, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1253, 119, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1254, 119, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1255, 119, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1256, 119, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1257, 119, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1258, 119, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1259, 119, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- =====================================================
-- COMPLIANCE DEPARTMENT (120 to 124)
-- =====================================================
REPLACE INTO evaluation_templates
  (template_id, template_name, description, target_department,
   evaluation_type, kra_weight, behavior_weight,
   form_code, revision_date, effective_date_form, status, created_by)
VALUES
  (120, 'Regulatory Compliance Monitoring and Filing Review', 'Measures 100% on-time submission of BSP/SEC reports, checklist coverage, regulatory dissemination, and zero-sanction record.', 'Compliance', 'Annual', 80.00, 20.00, 'COM-TMP-001', '2026-01-15', '2026-02-01', 'Active', 1),
  (121, 'AMLA & Know-Your-Customer (KYC) Compliance Assessment', 'Evaluates KYC audit scores, CTR transmission speed, STR evaluation quality, and high-risk customer review coverage.', 'Compliance', 'Quarterly', 80.00, 20.00, 'COM-TMP-002', '2026-01-15', '2026-02-01', 'Active', 1),
  (122, 'Internal Policy Compliance & Code of Conduct Review', 'Assesses policy compliance review count, violation investigation speed, corrective action validation, and advisory response time.', 'Compliance', 'Annual', 80.00, 20.00, 'COM-TMP-003', '2026-01-15', '2026-02-01', 'Active', 1),
  (123, 'License and Permit Renewal Management Evaluation', 'Reviews business permit renewal success, advance preparation timeline, permit repository order, and LGU inspection handling.', 'Compliance', 'Quarterly', 80.00, 20.00, 'COM-TMP-004', '2026-01-15', '2026-02-01', 'Active', 1),
  (124, 'Compliance Awareness and Staff Training Assessment', 'Evaluates compliance orientation coverage, annual refresher execution, post-training scores, and module legal accuracy.', 'Compliance', 'Annual', 80.00, 20.00, 'COM-TMP-005', '2026-01-15', '2026-02-01', 'Active', 1);

-- Criteria for Template 120 (Regulatory Compliance Monitoring and Fil...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1260, 120, 'KRA', 'BSP & SEC Report Filing Timeliness', 'Measures 100% on-time submission of periodic regulatory compliance reports.', 'Measures 100% on-time submission of periodic regulatory compliance reports.', 20.00, 'Scale_1_4', 1),
  (1261, 120, 'KRA', 'Compliance Checklist Coverage Rate', 'Evaluates completeness of compliance monitoring across all operational units.', 'Evaluates completeness of compliance monitoring across all operational units.', 20.00, 'Scale_1_4', 2),
  (1262, 120, 'KRA', 'Regulatory Circular Dissemination', 'Assesses speed in interpreting and briefing branches on new BSP circulars.', 'Assesses speed in interpreting and briefing branches on new BSP circulars.', 20.00, 'Scale_1_4', 3),
  (1263, 120, 'KRA', 'Zero Penalty Track Record', 'Monitors prevention of regulatory fines, warnings, or show-cause orders.', 'Monitors prevention of regulatory fines, warnings, or show-cause orders.', 20.00, 'Scale_1_4', 4),
  (1264, 120, 'KRA', 'Regulatory Inquiry Response Speed', 'Evaluates quick turnaround when responding to regulatory agency requests.', 'Evaluates quick turnaround when responding to regulatory agency requests.', 20.00, 'Scale_1_4', 5),
  (1265, 120, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1266, 120, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1267, 120, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1268, 120, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1269, 120, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1270, 120, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1271, 120, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1272, 120, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 121 (AMLA & Know-Your-Customer (KYC) Complian...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1273, 121, 'KRA', 'KYC Verification Compliance Score', 'Measures employee adherence to customer ID and KYC validation rules.', 'Measures employee adherence to customer ID and KYC validation rules.', 20.00, 'Scale_1_4', 1),
  (1274, 121, 'KRA', 'Covered Transaction Report (CTR) Filing', 'Evaluates on-time daily automated submission of CTR files to AMLC.', 'Evaluates on-time daily automated submission of CTR files to AMLC.', 20.00, 'Scale_1_4', 2),
  (1275, 121, 'KRA', 'Suspicious Transaction Report (STR) Quality', 'Assesses speed and detail quality of evaluating and filing STR alerts.', 'Assesses speed and detail quality of evaluating and filing STR alerts.', 20.00, 'Scale_1_4', 3),
  (1276, 121, 'KRA', 'High-Risk Customer & Watchlist Audit', 'Monitors periodic review and updates to PEP and high-risk customer files.', 'Monitors periodic review and updates to PEP and high-risk customer files.', 20.00, 'Scale_1_4', 4),
  (1277, 121, 'KRA', 'AML System Alert Review Speed', 'Evaluates daily monitoring and disposition of automated AML system alerts.', 'Evaluates daily monitoring and disposition of automated AML system alerts.', 20.00, 'Scale_1_4', 5),
  (1278, 121, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1279, 121, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1280, 121, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1281, 121, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1282, 121, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1283, 121, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1284, 121, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1285, 121, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 122 (Internal Policy Compliance & Code of Con...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1286, 122, 'KRA', 'SOP Compliance Review Count', 'Measures number of operational department compliance reviews completed.', 'Measures number of operational department compliance reviews completed.', 20.00, 'Scale_1_4', 1),
  (1287, 122, 'KRA', 'Code of Conduct Investigation Speed', 'Evaluates quick, impartial investigation of reported policy violations.', 'Evaluates quick, impartial investigation of reported policy violations.', 20.00, 'Scale_1_4', 2),
  (1288, 122, 'KRA', 'Corrective Action Follow-Up Validation', 'Assesses verification of non-compliance remediation plans across units.', 'Assesses verification of non-compliance remediation plans across units.', 20.00, 'Scale_1_4', 3),
  (1289, 122, 'KRA', 'Compliance Advisory Response Time', 'Monitors turnaround in answering operational policy queries from branches.', 'Monitors turnaround in answering operational policy queries from branches.', 20.00, 'Scale_1_4', 4),
  (1290, 122, 'KRA', 'Internal Control Assessment Quality', 'Verifies effectiveness of compliance reviews in detecting control gaps.', 'Verifies effectiveness of compliance reviews in detecting control gaps.', 20.00, 'Scale_1_4', 5),
  (1291, 122, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1292, 122, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1293, 122, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1294, 122, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1295, 122, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1296, 122, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1297, 122, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1298, 122, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 123 (License and Permit Renewal Management Ev...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1299, 123, 'KRA', 'Permit & License Renewal Success Rate', 'Measures 100% renewal of local business permits and BSP operating licenses.', 'Measures 100% renewal of local business permits and BSP operating licenses.', 20.00, 'Scale_1_4', 1),
  (1300, 123, 'KRA', 'Advance Renewal Filing Timeline', 'Evaluates initiation schedule prior to permit expiration dates to prevent gaps.', 'Evaluates initiation schedule prior to permit expiration dates to prevent gaps.', 20.00, 'Scale_1_4', 2),
  (1301, 123, 'KRA', 'License Repository & Archive Order', 'Assesses completeness and accessibility of permit files for all branches.', 'Assesses completeness and accessibility of permit files for all branches.', 20.00, 'Scale_1_4', 3),
  (1302, 123, 'KRA', 'LGU & Agency Inspection Handling', 'Monitors seamless coordination with local government inspectors during visits.', 'Monitors seamless coordination with local government inspectors during visits.', 20.00, 'Scale_1_4', 4),
  (1303, 123, 'KRA', 'Permit Fee Budget Optimization', 'Evaluates accurate computation and timely payment of permit fees.', 'Evaluates accurate computation and timely payment of permit fees.', 20.00, 'Scale_1_4', 5),
  (1304, 123, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1305, 123, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1306, 123, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1307, 123, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1308, 123, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1309, 123, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1310, 123, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1311, 123, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 124 (Compliance Awareness and Staff Training ...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1312, 124, 'KRA', 'New Hire Compliance Orientation Rate', 'Measures percentage of new employees completing mandatory AMLA orientation.', 'Measures percentage of new employees completing mandatory AMLA orientation.', 20.00, 'Scale_1_4', 1),
  (1313, 124, 'KRA', 'Annual Refresher Training Delivery', 'Evaluates rollout and coverage of annual compliance refresher modules.', 'Evaluates rollout and coverage of annual compliance refresher modules.', 20.00, 'Scale_1_4', 2),
  (1314, 124, 'KRA', 'Post-Training Assessment Average', 'Assesses staff knowledge retention score post-compliance training.', 'Assesses staff knowledge retention score post-compliance training.', 20.00, 'Scale_1_4', 3),
  (1315, 124, 'KRA', 'Training Material Legal Accuracy', 'Verifies continuous update of training slides based on recent legal updates.', 'Verifies continuous update of training slides based on recent legal updates.', 20.00, 'Scale_1_4', 4),
  (1316, 124, 'KRA', 'Compliance Quiz & Awareness Campaign', 'Monitors frequency and participation in monthly compliance awareness quizzes.', 'Monitors frequency and participation in monthly compliance awareness quizzes.', 20.00, 'Scale_1_4', 5),
  (1317, 124, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1318, 124, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1319, 124, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1320, 124, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1321, 124, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1322, 124, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1323, 124, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1324, 124, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- =====================================================
-- FINANCE DEPARTMENT (125 to 129)
-- =====================================================
REPLACE INTO evaluation_templates
  (template_id, template_name, description, target_department,
   evaluation_type, kra_weight, behavior_weight,
   form_code, revision_date, effective_date_form, status, created_by)
VALUES
  (125, 'Financial Reporting Accuracy and Timeliness Evaluation', 'Evaluates financial statement accuracy, monthly closing speed, audit adjustment score, and financial variance depth.', 'Finance', 'Annual', 80.00, 20.00, 'FIN-TMP-001', '2026-01-15', '2026-02-01', 'Active', 1),
  (126, 'Budget Monitoring and Expenditure Variance Analysis', 'Assesses budget variance tracking, departmental expense alerts, cost reduction recommendations, and annual budget consolidation.', 'Finance', 'Quarterly', 80.00, 20.00, 'FIN-TMP-002', '2026-01-15', '2026-02-01', 'Active', 1),
  (127, 'Cash Flow, Treasury & Liquidity Management Review', 'Reviews daily cash position accuracy, bank reconciliation timeliness, fund transfer precision, and cash flow forecast reliability.', 'Finance', 'Annual', 80.00, 20.00, 'FIN-TMP-003', '2026-01-15', '2026-02-01', 'Active', 1),
  (128, 'Accounts Payable & Disbursement Processing Evaluation', 'Evaluates invoice processing speed, AP verification accuracy, early payment discount capture, and vendor inquiry resolution.', 'Finance', 'Quarterly', 80.00, 20.00, 'FIN-TMP-004', '2026-01-15', '2026-02-01', 'Active', 1),
  (129, 'BIR Tax Compliance and Statutory Filing Review', 'Measures BIR returns on-time filing, tax computation accuracy, withholding tax certificate issuance, and tax audit handling.', 'Finance', 'Annual', 80.00, 20.00, 'FIN-TMP-005', '2026-01-15', '2026-02-01', 'Active', 1);

-- Criteria for Template 125 (Financial Reporting Accuracy and Timelin...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1325, 125, 'KRA', 'Financial Statement Accuracy & PFRS', 'Measures absence of material errors in monthly P&L and Balance Sheet.', 'Measures absence of material errors in monthly P&L and Balance Sheet.', 20.00, 'Scale_1_4', 1),
  (1326, 125, 'KRA', 'Monthly Closing & Submission Speed', 'Evaluates speed of completing monthly books closure and financial packages.', 'Evaluates speed of completing monthly books closure and financial packages.', 20.00, 'Scale_1_4', 2),
  (1327, 125, 'KRA', 'External Audit Adjustment Score', 'Assesses minimization of external auditor adjustments and management notes.', 'Assesses minimization of external auditor adjustments and management notes.', 20.00, 'Scale_1_4', 3),
  (1328, 125, 'KRA', 'Financial Variance Explanatory Depth', 'Verifies quality of variance footnotes accompanying financial reports.', 'Verifies quality of variance footnotes accompanying financial reports.', 20.00, 'Scale_1_4', 4),
  (1329, 125, 'KRA', 'General Ledger Account Reconciliation', 'Monitors 100% monthly reconciliation of balance sheet GL accounts.', 'Monitors 100% monthly reconciliation of balance sheet GL accounts.', 20.00, 'Scale_1_4', 5),
  (1330, 125, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1331, 125, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1332, 125, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1333, 125, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1334, 125, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1335, 125, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1336, 125, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1337, 125, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 126 (Budget Monitoring and Expenditure Varian...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1338, 126, 'KRA', 'Budget Variance Monitoring Cadence', 'Measures regularity and precision in tracking departmental budget usage.', 'Measures regularity and precision in tracking departmental budget usage.', 20.00, 'Scale_1_4', 1),
  (1339, 126, 'KRA', 'Expense Threshold Alert Timeliness', 'Evaluates prompt alerts issued when units approach approved budget limits.', 'Evaluates prompt alerts issued when units approach approved budget limits.', 20.00, 'Scale_1_4', 2),
  (1340, 126, 'KRA', 'Cost Optimization Recommendation Value', 'Assesses financial savings realized from cost reduction proposals.', 'Assesses financial savings realized from cost reduction proposals.', 20.00, 'Scale_1_4', 3),
  (1341, 126, 'KRA', 'Annual Budget Consolidation Support', 'Monitors efficiency in consolidating annual operating expense requests.', 'Monitors efficiency in consolidating annual operating expense requests.', 20.00, 'Scale_1_4', 4),
  (1342, 126, 'KRA', 'Capital Expenditure (CAPEX) Audit', 'Verifies proper authorization and tracking of asset CAPEX spending.', 'Verifies proper authorization and tracking of asset CAPEX spending.', 20.00, 'Scale_1_4', 5),
  (1343, 126, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1344, 126, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1345, 126, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1346, 126, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1347, 126, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1348, 126, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1349, 126, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1350, 126, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 127 (Cash Flow, Treasury & Liquidity Manageme...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1351, 127, 'KRA', 'Daily Cash Position Accuracy', 'Measures precision in calculating daily available cash balances for branches.', 'Measures precision in calculating daily available cash balances for branches.', 20.00, 'Scale_1_4', 1),
  (1352, 127, 'KRA', 'Bank Reconciliation Timeliness', 'Evaluates 100% monthly bank reconciliation completion without backlog.', 'Evaluates 100% monthly bank reconciliation completion without backlog.', 20.00, 'Scale_1_4', 2),
  (1353, 127, 'KRA', 'Fund Transfer Execution Precision', 'Assesses error-free execution of inter-bank fund transfers and float resets.', 'Assesses error-free execution of inter-bank fund transfers and float resets.', 20.00, 'Scale_1_4', 3),
  (1354, 127, 'KRA', 'Cash Flow Forecast Reliability', 'Monitors predictive accuracy of weekly and monthly cash liquidity forecasts.', 'Monitors predictive accuracy of weekly and monthly cash liquidity forecasts.', 20.00, 'Scale_1_4', 4),
  (1355, 127, 'KRA', 'Bank Fee & Interest Optimization', 'Evaluates cost management regarding bank charges and interest yields.', 'Evaluates cost management regarding bank charges and interest yields.', 20.00, 'Scale_1_4', 5),
  (1356, 127, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1357, 127, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1358, 127, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1359, 127, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1360, 127, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1361, 127, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1362, 127, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1363, 127, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 128 (Accounts Payable & Disbursement Processi...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1364, 128, 'KRA', 'Invoice & Voucher Turnaround Speed', 'Measures cycle time from vendor invoice receipt to check release.', 'Measures cycle time from vendor invoice receipt to check release.', 20.00, 'Scale_1_4', 1),
  (1365, 128, 'KRA', 'AP Verification Accuracy Rate', 'Evaluates error-free match of billing statements, POs, and receiving reports.', 'Evaluates error-free match of billing statements, POs, and receiving reports.', 20.00, 'Scale_1_4', 2),
  (1366, 128, 'KRA', 'Early Payment Discount Capture', 'Assesses realization rate of vendor prompt payment discounts.', 'Assesses realization rate of vendor prompt payment discounts.', 20.00, 'Scale_1_4', 3),
  (1367, 128, 'KRA', 'Vendor Payment Inquiry Response', 'Monitors promptness in answering supplier payment status queries.', 'Monitors promptness in answering supplier payment status queries.', 20.00, 'Scale_1_4', 4),
  (1368, 128, 'KRA', 'Disbursement Voucher Archiving', 'Verifies complete document attachment and filing of check vouchers.', 'Verifies complete document attachment and filing of check vouchers.', 20.00, 'Scale_1_4', 5),
  (1369, 128, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1370, 128, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1371, 128, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1372, 128, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1373, 128, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1374, 128, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1375, 128, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1376, 128, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 129 (BIR Tax Compliance and Statutory Filing ...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1377, 129, 'KRA', 'BIR Return On-Time Filing & Payment', 'Measures 100% on-time filing of Monthly VAT, EWT, and Income Tax returns.', 'Measures 100% on-time filing of Monthly VAT, EWT, and Income Tax returns.', 20.00, 'Scale_1_4', 1),
  (1378, 129, 'KRA', 'Tax Computation Accuracy Score', 'Evaluates tax calculation precision to eliminate BIR penalties and interest.', 'Evaluates tax calculation precision to eliminate BIR penalties and interest.', 20.00, 'Scale_1_4', 2),
  (1379, 129, 'KRA', 'BIR Form 2307 Issuance SLA', 'Assesses turnaround speed in issuing withholding tax certificates to suppliers.', 'Assesses turnaround speed in issuing withholding tax certificates to suppliers.', 20.00, 'Scale_1_4', 3),
  (1380, 129, 'KRA', 'Tax Audit & LOA Support Handling', 'Monitors effective assistance during BIR tax audits and assessment letters.', 'Monitors effective assistance during BIR tax audits and assessment letters.', 20.00, 'Scale_1_4', 4),
  (1381, 129, 'KRA', 'Tax Law Update Implementation', 'Verifies immediate system updates in response to new TRAIN/CREATE tax rules.', 'Verifies immediate system updates in response to new TRAIN/CREATE tax rules.', 20.00, 'Scale_1_4', 5),
  (1382, 129, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1383, 129, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1384, 129, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1385, 129, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1386, 129, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1387, 129, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1388, 129, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1389, 129, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- =====================================================
-- GENERAL SERVICES DEPARTMENT (130 to 134)
-- =====================================================
REPLACE INTO evaluation_templates
  (template_id, template_name, description, target_department,
   evaluation_type, kra_weight, behavior_weight,
   form_code, revision_date, effective_date_form, status, created_by)
VALUES
  (130, 'Facilities Maintenance and Building Services Review', 'Evaluates work order resolution rate, preventive maintenance execution, building utility uptime, and repair cost optimization.', 'General Services', 'Annual', 80.00, 20.00, 'GS-TMP-001', '2026-01-15', '2026-02-01', 'Active', 1),
  (131, 'Procurement Canvassing and Supplier Management', 'Assesses purchase order cycle time, competitive canvassing compliance, supplier delivery SLA, and documentation completeness.', 'General Services', 'Quarterly', 80.00, 20.00, 'GS-TMP-002', '2026-01-15', '2026-02-01', 'Active', 1),
  (132, 'Office Supplies Inventory Control & Distribution', 'Reviews stockout minimization, physical inventory match, supplies dispatch speed, and inventory loss prevention.', 'General Services', 'Annual', 80.00, 20.00, 'GS-TMP-003', '2026-01-15', '2026-02-01', 'Active', 1),
  (133, 'Fleet and Company Vehicle Management Evaluation', 'Evaluates vehicle availability, LTO registration compliance, fuel consumption tracking, and driver safety oversight.', 'General Services', 'Quarterly', 80.00, 20.00, 'GS-TMP-004', '2026-01-15', '2026-02-01', 'Active', 1),
  (134, 'Security Guard Oversight and Safety Compliance', 'Assesses guard deployment oversight, safety drill conduct, OSH compliance, and security incident reporting speed.', 'General Services', 'Annual', 80.00, 20.00, 'GS-TMP-005', '2026-01-15', '2026-02-01', 'Active', 1);

-- Criteria for Template 130 (Facilities Maintenance and Building Serv...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1390, 130, 'KRA', 'Facility Work Order Resolution Speed', 'Measures percentage of repair work orders completed within SLA hours.', 'Measures percentage of repair work orders completed within SLA hours.', 20.00, 'Scale_1_4', 1),
  (1391, 130, 'KRA', 'Preventive Maintenance Execution', 'Evaluates completion of aircon, generator, and elevator service schedules.', 'Evaluates completion of aircon, generator, and elevator service schedules.', 20.00, 'Scale_1_4', 2),
  (1392, 130, 'KRA', 'Utility Outage Downtime Minimization', 'Assesses reduction of power, water, or building facility downtime.', 'Assesses reduction of power, water, or building facility downtime.', 20.00, 'Scale_1_4', 3),
  (1393, 130, 'KRA', 'Building Maintenance Cost Control', 'Monitors competitive canvassing for facility repairs and upkeep works.', 'Monitors competitive canvassing for facility repairs and upkeep works.', 20.00, 'Scale_1_4', 4),
  (1394, 130, 'KRA', 'Janitorial Service Quality Score', 'Evaluates cleanliness ratings of head office and branch premises.', 'Evaluates cleanliness ratings of head office and branch premises.', 20.00, 'Scale_1_4', 5),
  (1395, 130, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1396, 130, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1397, 130, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1398, 130, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1399, 130, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1400, 130, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1401, 130, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1402, 130, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 131 (Procurement Canvassing and Supplier Mana...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1403, 131, 'KRA', 'Purchase Order Processing Speed', 'Measures turnaround time from approved requisition to PO issuance.', 'Measures turnaround time from approved requisition to PO issuance.', 20.00, 'Scale_1_4', 1),
  (1404, 131, 'KRA', 'Competitive Canvassing Compliance', 'Evaluates strict adherence to 3-quote canvassing rules for purchases.', 'Evaluates strict adherence to 3-quote canvassing rules for purchases.', 20.00, 'Scale_1_4', 2),
  (1405, 131, 'KRA', 'Supplier On-Time Delivery Rate', 'Assesses percentage of vendor orders delivered on or before target date.', 'Assesses percentage of vendor orders delivered on or before target date.', 20.00, 'Scale_1_4', 3),
  (1406, 131, 'KRA', 'Procurement Documentation Completeness', 'Verifies archiving of canvas sheets, POs, and receiving reports.', 'Verifies archiving of canvas sheets, POs, and receiving reports.', 20.00, 'Scale_1_4', 4),
  (1407, 131, 'KRA', 'Vendor Price Negotiation Savings', 'Monitors cost savings achieved through supplier negotiation.', 'Monitors cost savings achieved through supplier negotiation.', 20.00, 'Scale_1_4', 5),
  (1408, 131, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1409, 131, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1410, 131, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1411, 131, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1412, 131, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1413, 131, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1414, 131, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1415, 131, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 132 (Office Supplies Inventory Control & Dist...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1416, 132, 'KRA', 'Supplies Stockout Minimization', 'Measures constant availability of essential office, janitorial, and branch supplies.', 'Measures constant availability of essential office, janitorial, and branch supplies.', 20.00, 'Scale_1_4', 1),
  (1417, 132, 'KRA', 'Physical Inventory Match Accuracy', 'Evaluates match rate between physical warehouse stock counts and log.', 'Evaluates match rate between physical warehouse stock counts and log.', 20.00, 'Scale_1_4', 2),
  (1418, 132, 'KRA', 'Branch Requisition Fulfillment Speed', 'Assesses speed of dispatching requested supplies to branch network.', 'Assesses speed of dispatching requested supplies to branch network.', 20.00, 'Scale_1_4', 3),
  (1419, 132, 'KRA', 'Inventory Waste & Damage Control', 'Monitors proper storage to prevent supply spoilage or obsolescence.', 'Monitors proper storage to prevent supply spoilage or obsolescence.', 20.00, 'Scale_1_4', 4),
  (1420, 132, 'KRA', 'Par-Level Re-order Control', 'Verifies timely re-ordering of inventory items reaching safety stock levels.', 'Verifies timely re-ordering of inventory items reaching safety stock levels.', 20.00, 'Scale_1_4', 5),
  (1421, 132, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1422, 132, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1423, 132, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1424, 132, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1425, 132, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1426, 132, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1427, 132, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1428, 132, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 133 (Fleet and Company Vehicle Management Eva...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1429, 133, 'KRA', 'Vehicle Availability & Fleet Uptime', 'Measures percentage of company fleet vehicles ready for trip deployment.', 'Measures percentage of company fleet vehicles ready for trip deployment.', 20.00, 'Scale_1_4', 1),
  (1430, 133, 'KRA', 'LTO Registration & Insurance Renewal', 'Evaluates 100% on-time renewal of vehicle LTO papers and insurance policies.', 'Evaluates 100% on-time renewal of vehicle LTO papers and insurance policies.', 20.00, 'Scale_1_4', 2),
  (1431, 133, 'KRA', 'Fuel Voucher & Mileage Tracking', 'Assesses tracking accuracy of trip tickets, odometer logs, and fuel receipts.', 'Assesses tracking accuracy of trip tickets, odometer logs, and fuel receipts.', 20.00, 'Scale_1_4', 3),
  (1432, 133, 'KRA', 'Preventive Vehicle Maintenance', 'Monitors execution of oil changes, tire replacements, and tune-ups.', 'Monitors execution of oil changes, tire replacements, and tune-ups.', 20.00, 'Scale_1_4', 4),
  (1433, 133, 'KRA', 'Driver Safety & Incident Rate', 'Evaluates zero-accident record and driver compliance with road safety rules.', 'Evaluates zero-accident record and driver compliance with road safety rules.', 20.00, 'Scale_1_4', 5),
  (1434, 133, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1435, 133, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1436, 133, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1437, 133, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1438, 133, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1439, 133, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1440, 133, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1441, 133, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 134 (Security Guard Oversight and Safety Comp...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1442, 134, 'KRA', 'Security Guard Deployment Monitoring', 'Measures daily post attendance tracking and performance monitoring of guards.', 'Measures daily post attendance tracking and performance monitoring of guards.', 20.00, 'Scale_1_4', 1),
  (1443, 134, 'KRA', 'Mandatory Safety Drill Conduct', 'Evaluates execution of fire, earthquake, and security drills across facilities.', 'Evaluates execution of fire, earthquake, and security drills across facilities.', 20.00, 'Scale_1_4', 2),
  (1444, 134, 'KRA', 'DOLE OSH Hazard Mitigation', 'Assesses prompt resolution of workplace safety hazards and OSH reports.', 'Assesses prompt resolution of workplace safety hazards and OSH reports.', 20.00, 'Scale_1_4', 3),
  (1445, 134, 'KRA', 'Security Incident Report Speed', 'Monitors detail quality and speed when reporting break-ins, loss, or damage.', 'Monitors detail quality and speed when reporting break-ins, loss, or damage.', 20.00, 'Scale_1_4', 4),
  (1446, 134, 'KRA', 'CCTV & Alarm System Operational Uptime', 'Verifies 100% operational status of branch security cameras and alarms.', 'Verifies 100% operational status of branch security cameras and alarms.', 20.00, 'Scale_1_4', 5),
  (1447, 134, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1448, 134, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1449, 134, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1450, 134, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1451, 134, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1452, 134, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1453, 134, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1454, 134, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- =====================================================
-- INFORMATION TECHNOLOGY DEPARTMENT (135 to 139)
-- =====================================================
REPLACE INTO evaluation_templates
  (template_id, template_name, description, target_department,
   evaluation_type, kra_weight, behavior_weight,
   form_code, revision_date, effective_date_form, status, created_by)
VALUES
  (135, 'Infrastructure Availability and Server Uptime Evaluation', 'Measures core network and server availability (99%+), MTTR outage recovery, automated database backups, and hardware health checks.', 'Information Technology', 'Annual', 80.00, 20.00, 'IT-TMP-001', '2026-01-15', '2026-02-01', 'Active', 1),
  (136, 'Technical Support and Helpdesk Resolution Review', 'Evaluates helpdesk ticket turnaround, first-contact resolution rate, user satisfaction score, and ticket backlog reduction.', 'Information Technology', 'Quarterly', 80.00, 20.00, 'IT-TMP-002', '2026-01-15', '2026-02-01', 'Active', 1),
  (137, 'Cybersecurity, Access Control & Data Privacy Review', 'Assesses vulnerability patching promptness, access provisioning speed, zero data breach track record, and security audit resolution.', 'Information Technology', 'Annual', 80.00, 20.00, 'IT-TMP-003', '2026-01-15', '2026-02-01', 'Active', 1),
  (138, 'Software Development and Systems Enhancement Review', 'Evaluates developer sprint milestone delivery, UAT code defect density, enhancement request delivery, and API documentation.', 'Information Technology', 'Quarterly', 80.00, 20.00, 'IT-TMP-004', '2026-01-15', '2026-02-01', 'Active', 1),
  (139, 'IT Asset Management and License Compliance Review', 'Reviews IT hardware inventory match, software licensing legal compliance, setup turnaround for new hires, and e-waste disposal.', 'Information Technology', 'Annual', 80.00, 20.00, 'IT-TMP-005', '2026-01-15', '2026-02-01', 'Active', 1);

-- Criteria for Template 135 (Infrastructure Availability and Server U...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1455, 135, 'KRA', 'Core Network & Server Uptime (99%+)', 'Measures percentage uptime of central servers, routers, and core HRIS systems.', 'Measures percentage uptime of central servers, routers, and core HRIS systems.', 20.00, 'Scale_1_4', 1),
  (1456, 135, 'KRA', 'Outage Mean Time to Recovery (MTTR)', 'Evaluates speed of restoring operations during unscheduled system outages.', 'Evaluates speed of restoring operations during unscheduled system outages.', 20.00, 'Scale_1_4', 2),
  (1457, 135, 'KRA', 'Automated Database Backup Test', 'Assesses 100% execution of daily automated database backups and recovery drills.', 'Assesses 100% execution of daily automated database backups and recovery drills.', 20.00, 'Scale_1_4', 3),
  (1458, 135, 'KRA', 'Infrastructure Preventive Maintenance', 'Monitors server health checks, storage cleanup, and hardware upgrades.', 'Monitors server health checks, storage cleanup, and hardware upgrades.', 20.00, 'Scale_1_4', 4),
  (1459, 135, 'KRA', 'Branch Connectivity & VPN Uptime', 'Evaluates stability and uptime of secure network connections to all branches.', 'Evaluates stability and uptime of secure network connections to all branches.', 20.00, 'Scale_1_4', 5),
  (1460, 135, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1461, 135, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1462, 135, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1463, 135, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1464, 135, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1465, 135, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1466, 135, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1467, 135, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 136 (Technical Support and Helpdesk Resolutio...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1468, 136, 'KRA', 'Helpdesk Ticket Resolution SLA Rate', 'Measures percentage of support tickets resolved within target SLA hours.', 'Measures percentage of support tickets resolved within target SLA hours.', 20.00, 'Scale_1_4', 1),
  (1469, 136, 'KRA', 'First-Contact Resolution (FCR) Rate', 'Evaluates percentage of technical inquiries resolved on initial contact.', 'Evaluates percentage of technical inquiries resolved on initial contact.', 20.00, 'Scale_1_4', 2),
  (1470, 136, 'KRA', 'User Technical Satisfaction Score', 'Assesses end-user feedback ratings following helpdesk service completion.', 'Assesses end-user feedback ratings following helpdesk service completion.', 20.00, 'Scale_1_4', 3),
  (1471, 136, 'KRA', 'Helpdesk Ticket Backlog Minimization', 'Monitors reduction of unresolved open technical support tickets.', 'Monitors reduction of unresolved open technical support tickets.', 20.00, 'Scale_1_4', 4),
  (1472, 136, 'KRA', 'Support Knowledgebase Maintenance', 'Verifies continuous updating of self-help technical guides for employees.', 'Verifies continuous updating of self-help technical guides for employees.', 20.00, 'Scale_1_4', 5),
  (1473, 136, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1474, 136, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1475, 136, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1476, 136, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1477, 136, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1478, 136, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1479, 136, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1480, 136, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 137 (Cybersecurity, Access Control & Data Pri...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1481, 137, 'KRA', 'Patch & Anti-Virus Update Promptness', 'Measures timely installation of OS security patches and anti-virus files.', 'Measures timely installation of OS security patches and anti-virus files.', 20.00, 'Scale_1_4', 1),
  (1482, 137, 'KRA', 'Access Provisioning & Revocation Speed', 'Evaluates speed of granting or revoking system access upon HR notice.', 'Evaluates speed of granting or revoking system access upon HR notice.', 20.00, 'Scale_1_4', 2),
  (1483, 137, 'KRA', 'Zero Data Security Breach Track Record', 'Assesses zero tolerance for unauthorized data exposure or policy breaches.', 'Assesses zero tolerance for unauthorized data exposure or policy breaches.', 20.00, 'Scale_1_4', 3),
  (1484, 137, 'KRA', 'IT Security Audit Remediation Rate', 'Monitors resolution rate of security audit vulnerabilities identified.', 'Monitors resolution rate of security audit vulnerabilities identified.', 20.00, 'Scale_1_4', 4),
  (1485, 137, 'KRA', 'Firewall & Intrusion Prevention Audit', 'Verifies daily monitoring and tuning of network security firewalls.', 'Verifies daily monitoring and tuning of network security firewalls.', 20.00, 'Scale_1_4', 5),
  (1486, 137, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1487, 137, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1488, 137, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1489, 137, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1490, 137, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1491, 137, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1492, 137, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1493, 137, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 138 (Software Development and Systems Enhance...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1494, 138, 'KRA', 'Developer Sprint Feature Delivery', 'Measures on-time release of custom features against developer sprint plan.', 'Measures on-time release of custom features against developer sprint plan.', 20.00, 'Scale_1_4', 1),
  (1495, 138, 'KRA', 'UAT Defect Density & Code Quality', 'Evaluates bug-free rate of custom software releases during user testing.', 'Evaluates bug-free rate of custom software releases during user testing.', 20.00, 'Scale_1_4', 2),
  (1496, 138, 'KRA', 'System Enhancement Request Delivery', 'Assesses delivery speed for approved departmental change requests.', 'Assesses delivery speed for approved departmental change requests.', 20.00, 'Scale_1_4', 3),
  (1497, 138, 'KRA', 'API & Technical Documentation Quality', 'Verifies comprehensive documentation of system endpoints and schemas.', 'Verifies comprehensive documentation of system endpoints and schemas.', 20.00, 'Scale_1_4', 4),
  (1498, 138, 'KRA', 'Database Query Optimization', 'Monitors performance tuning to ensure fast page load speed across applications.', 'Monitors performance tuning to ensure fast page load speed across applications.', 20.00, 'Scale_1_4', 5),
  (1499, 138, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1500, 138, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1501, 138, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1502, 138, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1503, 138, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1504, 138, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1505, 138, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1506, 138, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 139 (IT Asset Management and License Complian...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1507, 139, 'KRA', 'Hardware Asset Inventory Match', 'Measures match rate between physical computers/peripherals and IT log.', 'Measures match rate between physical computers/peripherals and IT log.', 20.00, 'Scale_1_4', 1),
  (1508, 139, 'KRA', 'Software License Legal Compliance', 'Evaluates 100% legal compliance of software installed on company devices.', 'Evaluates 100% legal compliance of software installed on company devices.', 20.00, 'Scale_1_4', 2),
  (1509, 139, 'KRA', 'New Hire Equipment Setup SLA', 'Assesses turnaround speed in preparing laptops and gear for new employees.', 'Assesses turnaround speed in preparing laptops and gear for new employees.', 20.00, 'Scale_1_4', 3),
  (1510, 139, 'KRA', 'E-Waste Disposal & Disk Wipe Security', 'Monitors systematic de-commissioning and secure wipe of retired assets.', 'Monitors systematic de-commissioning and secure wipe of retired assets.', 20.00, 'Scale_1_4', 4),
  (1511, 139, 'KRA', 'IT Hardware Warranty & Maintenance Log', 'Verifies tracking of vendor warranty periods and equipment service agreements.', 'Verifies tracking of vendor warranty periods and equipment service agreements.', 20.00, 'Scale_1_4', 5),
  (1512, 139, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1513, 139, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1514, 139, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1515, 139, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1516, 139, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1517, 139, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1518, 139, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1519, 139, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- =====================================================
-- MARKETING DEPARTMENT (140 to 144)
-- =====================================================
REPLACE INTO evaluation_templates
  (template_id, template_name, description, target_department,
   evaluation_type, kra_weight, behavior_weight,
   form_code, revision_date, effective_date_form, status, created_by)
VALUES
  (140, 'Marketing Campaign Effectiveness and ROI Evaluation', 'Evaluates campaign net ROI, promotional launch timeliness, creative collateral quality, and campaign budget efficiency.', 'Marketing', 'Annual', 80.00, 20.00, 'MKT-TMP-001', '2026-01-15', '2026-02-01', 'Active', 1),
  (141, 'Digital and Social Media Marketing Performance Review', 'Assesses social media reach growth, content publishing calendar consistency, online inquiry response speed, and digital ad performance.', 'Marketing', 'Quarterly', 80.00, 20.00, 'MKT-TMP-002', '2026-01-15', '2026-02-01', 'Active', 1),
  (142, 'Brand Management and Visual Standard Adherence', 'Reviews brand guideline compliance, branch visual audit score, corporate comms approval speed, and brand reputation protection.', 'Marketing', 'Annual', 80.00, 20.00, 'MKT-TMP-003', '2026-01-15', '2026-02-01', 'Active', 1),
  (143, 'Customer Acquisition and Loyalty Program Assessment', 'Evaluates new customer acquisition count, loyalty program retention rate, lapsed customer reactivation, and customer acquisition cost.', 'Marketing', 'Quarterly', 80.00, 20.00, 'MKT-TMP-004', '2026-01-15', '2026-02-01', 'Active', 1),
  (144, 'Market Research and Consumer Insights Review', 'Assesses customer feedback survey volume, consumer insight analysis quality, research report turnaround, and actionable strategy adoption.', 'Marketing', 'Annual', 80.00, 20.00, 'MKT-TMP-005', '2026-01-15', '2026-02-01', 'Active', 1);

-- Criteria for Template 140 (Marketing Campaign Effectiveness and ROI...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1520, 140, 'KRA', 'Campaign ROI & Revenue Conversion', 'Measures net revenue and customer transactions generated by marketing campaigns.', 'Measures net revenue and customer transactions generated by marketing campaigns.', 20.00, 'Scale_1_4', 1),
  (1521, 140, 'KRA', 'Campaign Launch Timeline Adherence', 'Evaluates on-time launching of promotional initiatives and seasonal offers.', 'Evaluates on-time launching of promotional initiatives and seasonal offers.', 20.00, 'Scale_1_4', 2),
  (1522, 140, 'KRA', 'Creative Collateral Design Quality', 'Assesses visual design, messaging clarity, and appeal of promo assets.', 'Assesses visual design, messaging clarity, and appeal of promo assets.', 20.00, 'Scale_1_4', 3),
  (1523, 140, 'KRA', 'Campaign Budget Spending Efficiency', 'Monitors cost control and effective spending within approved campaign budgets.', 'Monitors cost control and effective spending within approved campaign budgets.', 20.00, 'Scale_1_4', 4),
  (1524, 140, 'KRA', 'Multi-Channel Promo Coordination', 'Evaluates seamless alignment of print, social media, and branch promos.', 'Evaluates seamless alignment of print, social media, and branch promos.', 20.00, 'Scale_1_4', 5),
  (1525, 140, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1526, 140, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1527, 140, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1528, 140, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1529, 140, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1530, 140, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1531, 140, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1532, 140, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 141 (Digital and Social Media Marketing Perfo...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1533, 141, 'KRA', 'Social Media Engagement & Reach', 'Measures increase in page followers, post reach, and user engagement rates.', 'Measures increase in page followers, post reach, and user engagement rates.', 20.00, 'Scale_1_4', 1),
  (1534, 141, 'KRA', 'Content Calendar Publishing Cadence', 'Evaluates regularity and quality of social media posts across channels.', 'Evaluates regularity and quality of social media posts across channels.', 20.00, 'Scale_1_4', 2),
  (1535, 141, 'KRA', 'Digital Customer Inquiry Response SLA', 'Assesses response speed to customer inquiries on Facebook/website.', 'Assesses response speed to customer inquiries on Facebook/website.', 20.00, 'Scale_1_4', 3),
  (1536, 141, 'KRA', 'Digital Ad Spend Cost-Per-Click', 'Monitors optimization of paid ad budgets for maximum lead generation.', 'Monitors optimization of paid ad budgets for maximum lead generation.', 20.00, 'Scale_1_4', 4),
  (1537, 141, 'KRA', 'Video & Graphic Content Performance', 'Evaluates view rates and shares of promotional video and image assets.', 'Evaluates view rates and shares of promotional video and image assets.', 20.00, 'Scale_1_4', 5),
  (1538, 141, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1539, 141, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1540, 141, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1541, 141, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1542, 141, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1543, 141, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1544, 141, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1545, 141, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 142 (Brand Management and Visual Standard Adh...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1546, 142, 'KRA', 'Brand Guideline Adherence Score', 'Measures 100% compliance with logo, color, and typography standards.', 'Measures 100% compliance with logo, color, and typography standards.', 20.00, 'Scale_1_4', 1),
  (1547, 142, 'KRA', 'Branch Signage & Visual Appeal Audit', 'Evaluates visual appearance and brand display at physical pawnshop branches.', 'Evaluates visual appearance and brand display at physical pawnshop branches.', 20.00, 'Scale_1_4', 2),
  (1548, 142, 'KRA', 'Corporate Comms Review Speed', 'Assesses turnaround time for reviewing and approving official external assets.', 'Assesses turnaround time for reviewing and approving official external assets.', 20.00, 'Scale_1_4', 3),
  (1549, 142, 'KRA', 'Brand Reputation Incident Prevention', 'Monitors prompt resolution of negative customer feedback or misrepresentations.', 'Monitors prompt resolution of negative customer feedback or misrepresentations.', 20.00, 'Scale_1_4', 4),
  (1550, 142, 'KRA', 'Marketing Material Inventory Control', 'Verifies stock availability and dispatch of flyers and posters to branches.', 'Verifies stock availability and dispatch of flyers and posters to branches.', 20.00, 'Scale_1_4', 5),
  (1551, 142, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1552, 142, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1553, 142, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1554, 142, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1555, 142, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1556, 142, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1557, 142, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1558, 142, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 143 (Customer Acquisition and Loyalty Program...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1559, 143, 'KRA', 'New Customer Acquisition Count', 'Measures total volume of new pawn and loan customers gained via promos.', 'Measures total volume of new pawn and loan customers gained via promos.', 20.00, 'Scale_1_4', 1),
  (1560, 143, 'KRA', 'Customer Loyalty & Repeat Rate', 'Evaluates retention metrics and usage of customer loyalty programs.', 'Evaluates retention metrics and usage of customer loyalty programs.', 20.00, 'Scale_1_4', 2),
  (1561, 143, 'KRA', 'Lapsed Client Reactivation Rate', 'Assesses campaign performance in bringing back former inactive pawners.', 'Assesses campaign performance in bringing back former inactive pawners.', 20.00, 'Scale_1_4', 3),
  (1562, 143, 'KRA', 'Cost Per Acquired Customer (CAC)', 'Monitors reduction of marketing cost spent per validated new client.', 'Monitors reduction of marketing cost spent per validated new client.', 20.00, 'Scale_1_4', 4),
  (1563, 143, 'KRA', 'Branch Promo Conversion Tracking', 'Evaluates tracking accuracy of walk-in customers responding to local promos.', 'Evaluates tracking accuracy of walk-in customers responding to local promos.', 20.00, 'Scale_1_4', 5),
  (1564, 143, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1565, 143, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1566, 143, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1567, 143, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1568, 143, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1569, 143, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1570, 143, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1571, 143, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 144 (Market Research and Consumer Insights Re...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1572, 144, 'KRA', 'Customer Insight Survey Sample Size', 'Measures data volume collected through client feedback surveys.', 'Measures data volume collected through client feedback surveys.', 20.00, 'Scale_1_4', 1),
  (1573, 144, 'KRA', 'Market Research Analysis Depth', 'Evaluates clarity and strategic value of consumer preference reports.', 'Evaluates clarity and strategic value of consumer preference reports.', 20.00, 'Scale_1_4', 2),
  (1574, 144, 'KRA', 'Research Report Turnaround Time', 'Assesses delivery speed when management requests targeted market studies.', 'Assesses delivery speed when management requests targeted market studies.', 20.00, 'Scale_1_4', 3),
  (1575, 144, 'KRA', 'Actionable Strategy Adoption Rate', 'Verifies adoption rate of market survey recommendations in decisions.', 'Verifies adoption rate of market survey recommendations in decisions.', 20.00, 'Scale_1_4', 4),
  (1576, 144, 'KRA', 'Demographic Trend Spotting Accuracy', 'Monitors proactive identification of shifting customer borrowing habits.', 'Monitors proactive identification of shifting customer borrowing habits.', 20.00, 'Scale_1_4', 5),
  (1577, 144, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1578, 144, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1579, 144, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1580, 144, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1581, 144, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1582, 144, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1583, 144, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1584, 144, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- =====================================================
-- OFFICE OF THE PRESIDENT DEPARTMENT (145 to 149)
-- =====================================================
REPLACE INTO evaluation_templates
  (template_id, template_name, description, target_department,
   evaluation_type, kra_weight, behavior_weight,
   form_code, revision_date, effective_date_form, status, created_by)
VALUES
  (145, 'Executive Assistance and Calendar Management Review', 'Assesses zero-conflict calendar management, executive correspondence quality, VIP guest reception courtesy, and office administrative efficiency.', 'Office of the President', 'Annual', 80.00, 20.00, 'OP-TMP-001', '2026-01-15', '2026-02-01', 'Active', 1),
  (146, 'Board and Executive Committee Meeting Coordination', 'Evaluates meeting agenda distribution, minutes of meeting speed and accuracy, action item tracking, and technical venue setup.', 'Office of the President', 'Quarterly', 80.00, 20.00, 'OP-TMP-002', '2026-01-15', '2026-02-01', 'Active', 1),
  (147, 'Stakeholder Communication and Protocol Review', 'Reviews external partner liaison quality, executive speech drafting, protocol compliance during events, and information routing speed.', 'Office of the President', 'Annual', 80.00, 20.00, 'OP-TMP-003', '2026-01-15', '2026-02-01', 'Active', 1),
  (148, 'Confidentiality and Information Security Assessment', 'Measures zero information leakage record, executive file classification, NDA management, and strict access authorization control.', 'Office of the President', 'Quarterly', 80.00, 20.00, 'OP-TMP-004', '2026-01-15', '2026-02-01', 'Active', 1),
  (149, 'Special Strategic Project Support & Execution', 'Evaluates President-directed project milestone tracking, cross-departmental coordination, project status reporting, and resource management.', 'Office of the President', 'Annual', 80.00, 20.00, 'OP-TMP-005', '2026-01-15', '2026-02-01', 'Active', 1);

-- Criteria for Template 145 (Executive Assistance and Calendar Manage...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1585, 145, 'KRA', 'Executive Calendar Management', 'Measures zero-conflict appointment scheduling and prompt reminders.', 'Measures zero-conflict appointment scheduling and prompt reminders.', 20.00, 'Scale_1_4', 1),
  (1586, 145, 'KRA', 'Executive Correspondence Formatting', 'Evaluates accuracy, tone, and formatting of letters drafted for the President.', 'Evaluates accuracy, tone, and formatting of letters drafted for the President.', 20.00, 'Scale_1_4', 2),
  (1587, 145, 'KRA', 'VIP Guest & Protocol Reception', 'Assesses professional protocol during executive partner and guest visits.', 'Assesses professional protocol during executive partner and guest visits.', 20.00, 'Scale_1_4', 3),
  (1588, 145, 'KRA', 'Executive Office Admin Efficiency', 'Monitors seamless supply, filing, and executive suite administrative operations.', 'Monitors seamless supply, filing, and executive suite administrative operations.', 20.00, 'Scale_1_4', 4),
  (1589, 145, 'KRA', 'Travel & Itinerary Coordination', 'Evaluates booking accuracy and itinerary preparation for executive travel.', 'Evaluates booking accuracy and itinerary preparation for executive travel.', 20.00, 'Scale_1_4', 5),
  (1590, 145, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1591, 145, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1592, 145, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1593, 145, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1594, 145, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1595, 145, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1596, 145, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1597, 145, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 146 (Board and Executive Committee Meeting Co...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1598, 146, 'KRA', 'Board Meeting Agenda & Material Prep', 'Measures timely distribution of meeting agendas and board packet files.', 'Measures timely distribution of meeting agendas and board packet files.', 20.00, 'Scale_1_4', 1),
  (1599, 146, 'KRA', 'Board Minutes Accuracy & Speed', 'Evaluates prompt delivery of clear, accurate board committee minutes.', 'Evaluates prompt delivery of clear, accurate board committee minutes.', 20.00, 'Scale_1_4', 2),
  (1600, 146, 'KRA', 'Executive Action Item Tracking', 'Assesses tracking of executive directives assigned to department heads.', 'Assesses tracking of executive directives assigned to department heads.', 20.00, 'Scale_1_4', 3),
  (1601, 146, 'KRA', 'Meeting Technical & Venue Setup', 'Verifies flawless arrangement of presentation gear, venue, and catering.', 'Verifies flawless arrangement of presentation gear, venue, and catering.', 20.00, 'Scale_1_4', 4),
  (1602, 146, 'KRA', 'Executive Resolution Archiving', 'Monitors systematic indexing and archiving of approved board resolutions.', 'Monitors systematic indexing and archiving of approved board resolutions.', 20.00, 'Scale_1_4', 5),
  (1603, 146, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1604, 146, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1605, 146, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1606, 146, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1607, 146, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1608, 146, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1609, 146, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1610, 146, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 147 (Stakeholder Communication and Protocol R...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1611, 147, 'KRA', 'External Partner Liaison Quality', 'Measures professional coordination with banks, legal counsel, and partners.', 'Measures professional coordination with banks, legal counsel, and partners.', 20.00, 'Scale_1_4', 1),
  (1612, 147, 'KRA', 'Executive Speech & Briefing Drafting', 'Evaluates clarity and impact of executive speeches and official memos.', 'Evaluates clarity and impact of executive speeches and official memos.', 20.00, 'Scale_1_4', 2),
  (1613, 147, 'KRA', 'Protocol Adherence During Functions', 'Assesses compliance with corporate protocol during official company events.', 'Assesses compliance with corporate protocol during official company events.', 20.00, 'Scale_1_4', 3),
  (1614, 147, 'KRA', 'High-Level Information Routing Speed', 'Monitors speed and discretion in routing urgent incoming executive mail.', 'Monitors speed and discretion in routing urgent incoming executive mail.', 20.00, 'Scale_1_4', 4),
  (1615, 147, 'KRA', 'Executive Public Relations Support', 'Evaluates alignment of executive statements with company communications.', 'Evaluates alignment of executive statements with company communications.', 20.00, 'Scale_1_4', 5),
  (1616, 147, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1617, 147, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1618, 147, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1619, 147, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1620, 147, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1621, 147, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1622, 147, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1623, 147, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 148 (Confidentiality and Information Security...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1624, 148, 'KRA', 'Zero Information Leakage Record', 'Measures 100% protection of sensitive executive discussions and files.', 'Measures 100% protection of sensitive executive discussions and files.', 20.00, 'Scale_1_4', 1),
  (1625, 148, 'KRA', 'Executive Document Labeling & Encryption', 'Evaluates proper security classification and encrypted file archiving.', 'Evaluates proper security classification and encrypted file archiving.', 20.00, 'Scale_1_4', 2),
  (1626, 148, 'KRA', 'NDA & Confidentiality Tracking', 'Assesses management of signed non-disclosure agreements with partners.', 'Assesses management of signed non-disclosure agreements with partners.', 20.00, 'Scale_1_4', 3),
  (1627, 148, 'KRA', 'Strict Document Access Authorization', 'Monitors verification before sharing executive files with internal staff.', 'Monitors verification before sharing executive files with internal staff.', 20.00, 'Scale_1_4', 4),
  (1628, 148, 'KRA', 'Physical File Safe Custody', 'Verifies lock-and-key storage of physical executive contracts and deeds.', 'Verifies lock-and-key storage of physical executive contracts and deeds.', 20.00, 'Scale_1_4', 5),
  (1629, 148, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1630, 148, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1631, 148, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1632, 148, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1633, 148, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1634, 148, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1635, 148, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1636, 148, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 149 (Special Strategic Project Support & Exec...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1637, 149, 'KRA', 'Special Project Milestone Adherence', 'Measures progress tracking and target completion of President directives.', 'Measures progress tracking and target completion of President directives.', 20.00, 'Scale_1_4', 1),
  (1638, 149, 'KRA', 'Cross-Departmental Task Force Synergy', 'Evaluates teamwork and clarity when guiding special project task forces.', 'Evaluates teamwork and clarity when guiding special project task forces.', 20.00, 'Scale_1_4', 2),
  (1639, 149, 'KRA', 'Executive Project Status Report Quality', 'Assesses concise, data-driven reporting to the President on project health.', 'Assesses concise, data-driven reporting to the President on project health.', 20.00, 'Scale_1_4', 3),
  (1640, 149, 'KRA', 'Project Resource & Budget Control', 'Monitors efficient utilization of resources allocated for special assignments.', 'Monitors efficient utilization of resources allocated for special assignments.', 20.00, 'Scale_1_4', 4),
  (1641, 149, 'KRA', 'Post-Project Outcome Evaluation', 'Evaluates verification of project results against initial strategic goals.', 'Evaluates verification of project results against initial strategic goals.', 20.00, 'Scale_1_4', 5),
  (1642, 149, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1643, 149, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1644, 149, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1645, 149, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1646, 149, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1647, 149, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1648, 149, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1649, 149, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- =====================================================
-- OPERATIONS DEPARTMENT (150 to 154)
-- =====================================================
REPLACE INTO evaluation_templates
  (template_id, template_name, description, target_department,
   evaluation_type, kra_weight, behavior_weight,
   form_code, revision_date, effective_date_form, status, created_by)
VALUES
  (150, 'Branch Daily Pawn Operations Performance Evaluation', 'Evaluates pawn ticket transaction accuracy, daily interest collection, customer waiting time, and branch opening/closing protocols.', 'Operations', 'Annual', 80.00, 20.00, 'OPS-TMP-001', '2026-01-15', '2026-02-01', 'Active', 1),
  (151, 'Branch Vault Management & Cash Accountability Review', 'Assesses daily vault balancing, zero cash discrepancy record, petty cash compliance, and cash-in-transit security routines.', 'Operations', 'Quarterly', 80.00, 20.00, 'OPS-TMP-002', '2026-01-15', '2026-02-01', 'Active', 1),
  (152, 'Customer Service Excellence & Branch Ambience', 'Reviews walk-in customer satisfaction survey scores, complaint resolution speed, client retention, and branch orderliness.', 'Operations', 'Annual', 80.00, 20.00, 'OPS-TMP-003', '2026-01-15', '2026-02-01', 'Active', 1),
  (153, 'Collateral Appraisal & Loan-to-Value Accuracy', 'Evaluates gold testing precision, loan-to-value cap compliance, fake item detection rate, and appraisal speed.', 'Operations', 'Quarterly', 80.00, 20.00, 'OPS-TMP-004', '2026-01-15', '2026-02-01', 'Active', 1),
  (154, 'Operational SOP & Regulatory Compliance Review', 'Assesses internal audit score, pawn ticket tagging compliance, AMLA/KYC counter protocol adherence, and zero regulatory fines.', 'Operations', 'Annual', 80.00, 20.00, 'OPS-TMP-005', '2026-01-15', '2026-02-01', 'Active', 1);

-- Criteria for Template 150 (Branch Daily Pawn Operations Performance...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1650, 150, 'KRA', 'Pawn Ticket Transaction Accuracy', 'Measures error-free entry of pawn tickets, loan amounts, and customer data.', 'Measures error-free entry of pawn tickets, loan amounts, and customer data.', 20.00, 'Scale_1_4', 1),
  (1651, 150, 'KRA', 'Daily Interest Income Target Realization', 'Evaluates branch target achievement in interest collection and renewals.', 'Evaluates branch target achievement in interest collection and renewals.', 20.00, 'Scale_1_4', 2),
  (1652, 150, 'KRA', 'Customer Counter Service Speed', 'Assesses average customer waiting time and transaction processing speed.', 'Assesses average customer waiting time and transaction processing speed.', 20.00, 'Scale_1_4', 3),
  (1653, 150, 'KRA', 'Branch Opening/Closing Security Protocol', 'Verifies strict adherence to daily security routines and opening timings.', 'Verifies strict adherence to daily security routines and opening timings.', 20.00, 'Scale_1_4', 4),
  (1654, 150, 'KRA', 'End-of-Day Transaction Settlement', 'Monitors 100% daily closing settlement of all branch transaction logs.', 'Monitors 100% daily closing settlement of all branch transaction logs.', 20.00, 'Scale_1_4', 5),
  (1655, 150, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1656, 150, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1657, 150, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1658, 150, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1659, 150, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1660, 150, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1661, 150, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1662, 150, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 151 (Branch Vault Management & Cash Accountab...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1663, 151, 'KRA', 'Daily Vault Balancing Precision', 'Measures 100% accuracy during daily vault balancing and drawer checks.', 'Measures 100% accuracy during daily vault balancing and drawer checks.', 20.00, 'Scale_1_4', 1),
  (1664, 151, 'KRA', 'Zero Cash Discrepancy Record', 'Evaluates minimization of un-reconciled cash overages or shortages.', 'Evaluates minimization of un-reconciled cash overages or shortages.', 20.00, 'Scale_1_4', 2),
  (1665, 151, 'KRA', 'Petty Cash Float Documentation', 'Assesses proper voucher attachment for branch operational float expenses.', 'Assesses proper voucher attachment for branch operational float expenses.', 20.00, 'Scale_1_4', 3),
  (1666, 151, 'KRA', 'Armored Car Cash Transfer Protocol', 'Monitors strict compliance with cash-in-transit limits and pickup rules.', 'Monitors strict compliance with cash-in-transit limits and pickup rules.', 20.00, 'Scale_1_4', 4),
  (1667, 151, 'KRA', 'Dual Control Key & Lock Compliance', 'Verifies adherence to dual key holder rules for branch safe access.', 'Verifies adherence to dual key holder rules for branch safe access.', 20.00, 'Scale_1_4', 5),
  (1668, 151, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1669, 151, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1670, 151, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1671, 151, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1672, 151, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1673, 151, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1674, 151, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1675, 151, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 152 (Customer Service Excellence & Branch Amb...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1676, 152, 'KRA', 'Customer Satisfaction Survey Score', 'Measures positive feedback ratings received from branch walk-in clients.', 'Measures positive feedback ratings received from branch walk-in clients.', 20.00, 'Scale_1_4', 1),
  (1677, 152, 'KRA', 'Customer Complaint Resolution Speed', 'Evaluates prompt handling of client complaints regarding appraisals.', 'Evaluates prompt handling of client complaints regarding appraisals.', 20.00, 'Scale_1_4', 2),
  (1678, 152, 'KRA', 'Regular Client Courtesy & Retention', 'Assesses proactive courtesy and service building with repeat pawners.', 'Assesses proactive courtesy and service building with repeat pawners.', 20.00, 'Scale_1_4', 3),
  (1679, 152, 'KRA', 'Branch Premises Orderliness', 'Monitors cleanliness, air-conditioning, and professional counter appearance.', 'Monitors cleanliness, air-conditioning, and professional counter appearance.', 20.00, 'Scale_1_4', 4),
  (1680, 152, 'KRA', 'Branch Service Queue Management', 'Evaluates smooth queue flow during peak payout and interest payment days.', 'Evaluates smooth queue flow during peak payout and interest payment days.', 20.00, 'Scale_1_4', 5),
  (1681, 152, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1682, 152, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1683, 152, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1684, 152, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1685, 152, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1686, 152, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1687, 152, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1688, 152, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 153 (Collateral Appraisal & Loan-to-Value Acc...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1689, 153, 'KRA', 'Gold & Jewelry Testing Accuracy', 'Measures precision in acid/stone testing, karat rating, and weight measurement.', 'Measures precision in acid/stone testing, karat rating, and weight measurement.', 20.00, 'Scale_1_4', 1),
  (1690, 153, 'KRA', 'Loan-To-Value (LTV) Cap Compliance', 'Evaluates strict adherence to approved pawn loan value matrices.', 'Evaluates strict adherence to approved pawn loan value matrices.', 20.00, 'Scale_1_4', 2),
  (1691, 153, 'KRA', 'Counterfeit Item Detection Rate', 'Assesses 100% prevention of accepting fake or low-grade collateral.', 'Assesses 100% prevention of accepting fake or low-grade collateral.', 20.00, 'Scale_1_4', 3),
  (1692, 153, 'KRA', 'Appraisal Processing Speed', 'Monitors speed of item testing without compromising appraisal accuracy.', 'Monitors speed of item testing without compromising appraisal accuracy.', 20.00, 'Scale_1_4', 4),
  (1693, 153, 'KRA', 'Appraisal Scale Calibration Audit', 'Verifies daily calibration checks of digital weighing scales.', 'Verifies daily calibration checks of digital weighing scales.', 20.00, 'Scale_1_4', 5),
  (1694, 153, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1695, 153, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1696, 153, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1697, 153, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1698, 153, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1699, 153, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1700, 153, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1701, 153, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 154 (Operational SOP & Regulatory Compliance ...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1702, 154, 'KRA', 'Branch Internal Audit Score', 'Measures high score during unannounced operational compliance audits.', 'Measures high score during unannounced operational compliance audits.', 20.00, 'Scale_1_4', 1),
  (1703, 154, 'KRA', 'Pawned Item Vault Tagging Order', 'Evaluates proper tagging, vault placement, and security of pledged items.', 'Evaluates proper tagging, vault placement, and security of pledged items.', 20.00, 'Scale_1_4', 2),
  (1704, 154, 'KRA', 'Counter AMLA & KYC Protocol Check', 'Assesses customer ID checking and reporting of suspicious transactions.', 'Assesses customer ID checking and reporting of suspicious transactions.', 20.00, 'Scale_1_4', 3),
  (1705, 154, 'KRA', 'Zero Regulatory Penalty Record', 'Monitors branch compliance to avoid local LGU or BSP penalty citations.', 'Monitors branch compliance to avoid local LGU or BSP penalty citations.', 20.00, 'Scale_1_4', 4),
  (1706, 154, 'KRA', 'Operational Memo Dissemination Check', 'Verifies that branch staff understand and enforce new operational memos.', 'Verifies that branch staff understand and enforce new operational memos.', 20.00, 'Scale_1_4', 5),
  (1707, 154, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1708, 154, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1709, 154, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1710, 154, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1711, 154, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1712, 154, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1713, 154, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1714, 154, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- =====================================================
-- PURCHASING DEPARTMENT (155 to 159)
-- =====================================================
REPLACE INTO evaluation_templates
  (template_id, template_name, description, target_department,
   evaluation_type, kra_weight, behavior_weight,
   form_code, revision_date, effective_date_form, status, created_by)
VALUES
  (155, 'Purchase Requisition Processing Efficiency Review', 'Evaluates PR to PO cycle speed, specification verification accuracy, PO backlog reduction, and status update communication.', 'Purchasing', 'Annual', 80.00, 20.00, 'PUR-TMP-001', '2026-01-15', '2026-02-01', 'Active', 1),
  (156, 'Vendor Accreditation & Supplier Quality Rating', 'Assesses vendor vetting documentation, supplier scorecard reviews, backup supplier roster depth, and anti-kickback compliance.', 'Purchasing', 'Quarterly', 80.00, 20.00, 'PUR-TMP-002', '2026-01-15', '2026-02-01', 'Active', 1),
  (157, 'Procurement Cost Savings & Contract Negotiation', 'Measures cost savings vs budget targets, bulk contract execution, competitive canvassing depth, and substitute item proposals.', 'Purchasing', 'Annual', 80.00, 20.00, 'PUR-TMP-003', '2026-01-15', '2026-02-01', 'Active', 1),
  (158, 'Delivery Tracking & Supplier SLA Monitoring', 'Evaluates supplier on-time delivery rate, delivery discrepancy resolution speed, order expediting responsiveness, and warehouse hand-off.', 'Purchasing', 'Quarterly', 80.00, 20.00, 'PUR-TMP-004', '2026-01-15', '2026-02-01', 'Active', 1),
  (159, 'Procurement Documentation & Auditability Review', 'Reviews 100% attached canvass sheet completeness, internal audit compliance score, contract sign-off speed, and purchasing file repository.', 'Purchasing', 'Annual', 80.00, 20.00, 'PUR-TMP-005', '2026-01-15', '2026-02-01', 'Active', 1);

-- Criteria for Template 155 (Purchase Requisition Processing Efficien...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1715, 155, 'KRA', 'PR-to-PO Processing Turnaround Speed', 'Measures speed of processing approved requisitions into official POs.', 'Measures speed of processing approved requisitions into official POs.', 20.00, 'Scale_1_4', 1),
  (1716, 155, 'KRA', 'PR Specification Verification Accuracy', 'Evaluates thoroughness in verifying item specifications before canvassing.', 'Evaluates thoroughness in verifying item specifications before canvassing.', 20.00, 'Scale_1_4', 2),
  (1717, 155, 'KRA', 'Unassigned Requisition Backlog Control', 'Assesses minimization of pending unassigned purchase requisitions.', 'Assesses minimization of pending unassigned purchase requisitions.', 20.00, 'Scale_1_4', 3),
  (1718, 155, 'KRA', 'Requisitioner Status Update SLA', 'Monitors proactive status updates provided to requesting departments.', 'Monitors proactive status updates provided to requesting departments.', 20.00, 'Scale_1_4', 4),
  (1719, 155, 'KRA', 'Emergency Requisition SLA Execution', 'Evaluates rapid handling of urgent operational purchase requests.', 'Evaluates rapid handling of urgent operational purchase requests.', 20.00, 'Scale_1_4', 5),
  (1720, 155, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1721, 155, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1722, 155, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1723, 155, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1724, 155, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1725, 155, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1726, 155, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1727, 155, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 156 (Vendor Accreditation & Supplier Quality ...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1728, 156, 'KRA', 'Vendor Accreditation Documentation', 'Measures complete vetting of supplier business permits, BIR, and profiles.', 'Measures complete vetting of supplier business permits, BIR, and profiles.', 20.00, 'Scale_1_4', 1),
  (1729, 156, 'KRA', 'Annual Supplier Scorecard Evaluation', 'Evaluates annual scoring of vendors on quality, pricing, and timeliness.', 'Evaluates annual scoring of vendors on quality, pricing, and timeliness.', 20.00, 'Scale_1_4', 2),
  (1730, 156, 'KRA', 'Backup Supplier Roster Depth', 'Assesses availability of alternative vendors to prevent supply risks.', 'Assesses availability of alternative vendors to prevent supply risks.', 20.00, 'Scale_1_4', 3),
  (1731, 156, 'KRA', 'Anti-Corruption Policy Compliance', 'Monitors strict adherence to anti-kickback rules in vendor dealings.', 'Monitors strict adherence to anti-kickback rules in vendor dealings.', 20.00, 'Scale_1_4', 4),
  (1732, 156, 'KRA', 'Vendor Site Visit & Facility Audit', 'Evaluates periodic physical inspection of key supplier manufacturing sites.', 'Evaluates periodic physical inspection of key supplier manufacturing sites.', 20.00, 'Scale_1_4', 5),
  (1733, 156, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1734, 156, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1735, 156, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1736, 156, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1737, 156, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1738, 156, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1739, 156, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1740, 156, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 157 (Procurement Cost Savings & Contract Nego...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1741, 157, 'KRA', 'Cost Savings vs Approved Budget', 'Measures percentage of financial savings achieved through price negotiation.', 'Measures percentage of financial savings achieved through price negotiation.', 20.00, 'Scale_1_4', 1),
  (1742, 157, 'KRA', 'Volume & Bulk Contract Negotiation', 'Evaluates execution of bulk purchasing agreements for core supplies.', 'Evaluates execution of bulk purchasing agreements for core supplies.', 20.00, 'Scale_1_4', 2),
  (1743, 157, 'KRA', 'Competitive Canvassing Depth', 'Assesses depth of market price comparison across accredited suppliers.', 'Assesses depth of market price comparison across accredited suppliers.', 20.00, 'Scale_1_4', 3),
  (1744, 157, 'KRA', 'Cost-Effective Item Substitution', 'Monitors introduction of quality substitute items at lower costs.', 'Monitors introduction of quality substitute items at lower costs.', 20.00, 'Scale_1_4', 4),
  (1745, 157, 'KRA', 'Payment Term Optimization (Net 30/60)', 'Evaluates success in negotiating favorable payment credit terms.', 'Evaluates success in negotiating favorable payment credit terms.', 20.00, 'Scale_1_4', 5),
  (1746, 157, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1747, 157, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1748, 157, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1749, 157, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1750, 157, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1751, 157, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1752, 157, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1753, 157, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 158 (Delivery Tracking & Supplier SLA Monitor...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1754, 158, 'KRA', 'Supplier On-Time Delivery SLA Rate', 'Measures percentage of supplier orders delivered on or before target date.', 'Measures percentage of supplier orders delivered on or before target date.', 20.00, 'Scale_1_4', 1),
  (1755, 158, 'KRA', 'Defective Goods Return Resolution Speed', 'Evaluates speed of replacing damaged, defective, or wrong supplier items.', 'Evaluates speed of replacing damaged, defective, or wrong supplier items.', 20.00, 'Scale_1_4', 2),
  (1756, 158, 'KRA', 'Order Expediting & Transit Tracking', 'Assesses active tracking of critical operational goods in transit.', 'Assesses active tracking of critical operational goods in transit.', 20.00, 'Scale_1_4', 3),
  (1757, 158, 'KRA', 'Warehouse Receiving Hand-Off Order', 'Monitors seamless coordination with warehouse staff upon delivery arrival.', 'Monitors seamless coordination with warehouse staff upon delivery arrival.', 20.00, 'Scale_1_4', 4),
  (1758, 158, 'KRA', 'Supplier Delivery Penalty Enforcement', 'Verifies application of liquidating damages for delayed vendor shipments.', 'Verifies application of liquidating damages for delayed vendor shipments.', 20.00, 'Scale_1_4', 5),
  (1759, 158, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1760, 158, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1761, 158, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1762, 158, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1763, 158, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1764, 158, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1765, 158, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1766, 158, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

-- Criteria for Template 159 (Procurement Documentation & Auditability...)
REPLACE INTO evaluation_criteria (criterion_id, template_id, section, criterion_name, description, kpi_description, weight, scoring_method, sort_order) VALUES
  (1767, 159, 'KRA', 'Procurement Auditability & File Attachment', 'Measures 100% attachment of canvass sheets, POs, and quotes.', 'Measures 100% attachment of canvass sheets, POs, and quotes.', 20.00, 'Scale_1_4', 1),
  (1768, 159, 'KRA', 'Internal Procurement Audit Score', 'Evaluates zero-exception record during internal purchasing compliance audits.', 'Evaluates zero-exception record during internal purchasing compliance audits.', 20.00, 'Scale_1_4', 2),
  (1769, 159, 'KRA', 'Contract Execution & Legal Sign-off', 'Assesses prompt securing of legal sign-offs for supply contracts.', 'Assesses prompt securing of legal sign-offs for supply contracts.', 20.00, 'Scale_1_4', 3),
  (1770, 159, 'KRA', 'Purchasing Archive Repository Order', 'Monitors organized archiving of historical purchasing records and quotes.', 'Monitors organized archiving of historical purchasing records and quotes.', 20.00, 'Scale_1_4', 4),
  (1771, 159, 'KRA', 'Purchase Order Cancelation Audit', 'Verifies proper authorization and recording of canceled purchase orders.', 'Verifies proper authorization and recording of canceled purchase orders.', 20.00, 'Scale_1_4', 5),
  (1772, 159, 'Behavior', 'Positive Attitude', 'Displays positive attitude at work.', 'Displays positive attitude at work.', 12.50, 'Scale_1_4', 6),
  (1773, 159, 'Behavior', 'Respect', 'Shows respect to all people in the organization.', 'Shows respect to all people in the organization.', 12.50, 'Scale_1_4', 7),
  (1774, 159, 'Behavior', 'Accountability', 'Takes full responsibility of the job including special task or assignment.', 'Takes full responsibility of the job including special task or assignment.', 12.50, 'Scale_1_4', 8),
  (1775, 159, 'Behavior', 'Commitment', 'Demonstrates strong commitment to the job.', 'Demonstrates strong commitment to the job.', 12.50, 'Scale_1_4', 9),
  (1776, 159, 'Behavior', 'Teamwork', 'Works cooperatively with others in achieving the goals.', 'Works cooperatively with others in achieving the goals.', 12.50, 'Scale_1_4', 10),
  (1777, 159, 'Behavior', 'Integrity', 'Exhibits honesty and strong moral uprightness.', 'Exhibits honesty and strong moral uprightness.', 12.50, 'Scale_1_4', 11),
  (1778, 159, 'Behavior', 'Continuous Improvement', 'Provides diligent effort to continuously focus on getting better.', 'Provides diligent effort to continuously focus on getting better.', 12.50, 'Scale_1_4', 12),
  (1779, 159, 'Behavior', 'Excellent Client Experience', 'Delivers the service beyond the expectations of the internal and external clients.', 'Delivers the service beyond the expectations of the internal and external clients.', 12.50, 'Scale_1_4', 13);

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- SUMMARY
-- =====================================================
-- Total templates inserted : 60 (5 per department x 12 departments)
-- Total criteria inserted  : 780 (13 criteria per template: 5 KRA + 8 Behavior)
-- =====================================================