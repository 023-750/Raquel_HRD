-- =====================================================
-- RAQUEL HRIS -- KRA EVALUATION TEMPLATES SEED
-- seed_templates.sql
-- =====================================================
-- Purpose  : Populate realistic, department-specific
--            evaluation templates for demo and client
--            presentation purposes.
-- Note     : template IDs start at 100 to avoid conflict
--            with existing seeds (which use IDs 1-2).
--            evaluation_type ENUM: 'Initial','Final',
--            'Quarterly','Annual'
-- =====================================================
USE raquel_hris;
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================
-- HUMAN RESOURCES DEPARTMENT  (HR-TMP-001 to HR-TMP-010)
-- template_id: 100 - 109
-- =====================================================
REPLACE INTO evaluation_templates
  (template_id, template_name, description, target_department,
   evaluation_type, kra_weight, behavior_weight,
   form_code, revision_date, effective_date_form, status, created_by)
VALUES
  (100,
   'Annual Recruitment Performance Evaluation',
   'This evaluation measures the effectiveness of the recruitment function by assessing sourcing quality, hiring turnaround time, offer acceptance rates, and compliance with approved manpower requisitions. The results guide the HR team in refining talent acquisition strategies and addressing gaps in the hiring pipeline. It supports the organization\'s objective of maintaining a fully staffed and competent workforce at all times.',
   'Human Resources', 'Annual', 80.00, 20.00,
   'HR-TMP-001', '2026-01-15', '2026-02-01', 'Active', 1),

  (101,
   'Employee Relations Effectiveness Assessment',
   'This evaluation assesses the HR staff member\'s ability to manage workplace concerns, mediate employee disputes, and maintain harmonious labor-management relations throughout the evaluation period. Key performance indicators include grievance resolution rate, case documentation completeness, and employee satisfaction feedback scores. The evaluation ensures the organization remains compliant with DOLE regulations and fosters a positive work culture.',
   'Human Resources', 'Annual', 80.00, 20.00,
   'HR-TMP-002', '2026-01-15', '2026-02-01', 'Active', 1),

  (102,
   'Learning and Development Program Evaluation',
   'This template evaluates the planning, execution, and outcomes of training and development programs facilitated or coordinated by the L&D specialist within the HR department. It measures training coverage rate, post-training competency improvement, trainer effectiveness ratings, and timeliness of program delivery. The results are used to improve future training calendars and align capability-building activities with organizational priorities.',
   'Human Resources', 'Annual', 80.00, 20.00,
   'HR-TMP-003', '2026-01-15', '2026-02-01', 'Active', 1),

  (103,
   'Compensation and Benefits Administration Evaluation',
   'This evaluation reviews the accuracy, timeliness, and compliance of payroll processing, government remittances (SSS, PhilHealth, Pag-IBIG), and benefits administration performed by the compensation team. It also assesses the HR officer\'s handling of leave processing, 13th month computation, and employee benefits queries. The evaluation aims to minimize payroll errors and ensure statutory compliance across all branches.',
   'Human Resources', 'Annual', 80.00, 20.00,
   'HR-TMP-004', '2026-01-15', '2026-02-01', 'Active', 1),

  (104,
   'Attendance Monitoring and Timekeeping Evaluation',
   'This evaluation measures the accuracy and timeliness of attendance monitoring, tardiness tracking, and leave management across all departments coordinated by the HR staff member. It evaluates the proper use of timekeeping systems, frequency of attendance report submissions, and responsiveness to attendance-related concerns raised by supervisors. This template supports consistent enforcement of attendance policies company-wide.',
   'Human Resources', 'Quarterly', 80.00, 20.00,
   'HR-TMP-005', '2026-01-15', '2026-02-01', 'Active', 1),

  (105,
   'Employee Engagement Program Assessment',
   'This template evaluates the HR staff\'s contribution to planning and implementing employee engagement activities such as team building events, recognition programs, employee satisfaction surveys, and wellness initiatives. Performance indicators include program participation rate, feedback scores, and the alignment of engagement activities with company values. The evaluation supports retention efforts and the cultivation of a motivated workforce.',
   'Human Resources', 'Annual', 80.00, 20.00,
   'HR-TMP-006', '2026-01-15', '2026-02-01', 'Active', 1),

  (106,
   'Policy Compliance and HR Audit Evaluation',
   'This evaluation measures the HR department\'s ability to maintain, update, and enforce HR policies in line with company standards and labor law requirements. It assesses policy documentation completeness, the frequency and quality of internal HR audits, and the rate at which policy violations are appropriately addressed. The results guide improvements in governance and HR policy administration.',
   'Human Resources', 'Annual', 80.00, 20.00,
   'HR-TMP-007', '2026-01-15', '2026-02-01', 'Active', 1),

  (107,
   'Employee Records Management Review',
   'This evaluation assesses the completeness, accuracy, confidentiality, and accessibility of employee records maintained by the HR team, including 201 files, government ID records, and evaluation archives. It measures the percentage of complete employee files, records retrieval time, and compliance with data privacy regulations. The evaluation promotes sound information governance within the HR function.',
   'Human Resources', 'Annual', 80.00, 20.00,
   'HR-TMP-008', '2026-01-15', '2026-02-01', 'Active', 1),

  (108,
   'HR Administration and Operations Efficiency Evaluation',
   'This template evaluates the day-to-day administrative output of the HR staff, including the processing of employment certifications, memoranda, disciplinary notices, and interdepartmental communications. It measures document turnaround time, error rates, and feedback from requesting departments. The evaluation supports the HR function\'s role as an efficient service provider to the broader organization.',
   'Human Resources', 'Quarterly', 80.00, 20.00,
   'HR-TMP-009', '2026-01-15', '2026-02-01', 'Active', 1),

  (109,
   'Performance Management Cycle Evaluation',
   'This evaluation assesses the HR staff member\'s effectiveness in facilitating the end-to-end performance evaluation cycle, from template preparation and employee onboarding to supervisor briefings, data consolidation, and result reporting. It measures the percentage of evaluations completed on time, data integrity, and the quality of performance feedback reports submitted to management. The evaluation ensures the performance management process delivers actionable insights for talent development.',
   'Human Resources', 'Annual', 80.00, 20.00,
   'HR-TMP-010', '2026-01-15', '2026-02-01', 'Active', 1);

-- =====================================================
-- ACQUIRED PROPERTIES DEPARTMENT  (AP-TMP-001 to AP-TMP-005)
-- template_id: 110 - 114
-- =====================================================
REPLACE INTO evaluation_templates
  (template_id, template_name, description, target_department,
   evaluation_type, kra_weight, behavior_weight,
   form_code, revision_date, effective_date_form, status, created_by)
VALUES
  (110,
   'Acquired Property Disposition Performance Evaluation',
   'This evaluation measures the efficiency and success rate of disposing acquired collateral properties through auction, negotiated sale, or other approved channels within the evaluation period. It assesses the ratio of properties sold versus total properties available, proceeds versus appraised value, and compliance with BSP and company disposition guidelines. The results help management optimize the monetization of acquired assets and reduce carrying costs.',
   'Acquired Properties', 'Annual', 80.00, 20.00,
   'AP-TMP-001', '2026-01-15', '2026-02-01', 'Active', 1),

  (111,
   'Property Appraisal Accuracy and Timeliness Review',
   'This template evaluates the accuracy of property valuations, including market comparables, inspection reports, and appraisal documentation completeness submitted by the acquired properties coordinator. It measures the deviation between appraised value and actual sale price, appraisal turnaround time, and compliance with internal appraisal standards. Accurate appraisals are essential to protecting the organization from financial loss during property transactions.',
   'Acquired Properties', 'Quarterly', 80.00, 20.00,
   'AP-TMP-002', '2026-01-15', '2026-02-01', 'Active', 1),

  (112,
   'Property Inventory Management Evaluation',
   'This evaluation assesses the maintenance of an accurate and up-to-date inventory of all acquired real and personal properties under the department\'s custody. It measures inventory record accuracy, the frequency of physical inspections, and the timeliness of status updates in the property management system. Effective inventory management minimizes idle assets and supports strategic property planning.',
   'Acquired Properties', 'Quarterly', 80.00, 20.00,
   'AP-TMP-003', '2026-01-15', '2026-02-01', 'Active', 1),

  (113,
   'Legal Documentation and Title Processing Evaluation',
   'This template evaluates the staff member\'s handling of legal documentation related to acquired properties, including title transfers, tax declarations, and court-related paperwork. It measures documentation completion rates, turnaround time for title processing, and compliance with relevant regulatory and legal requirements. Sound legal documentation prevents disputes and protects the company\'s property rights.',
   'Acquired Properties', 'Annual', 80.00, 20.00,
   'AP-TMP-004', '2026-01-15', '2026-02-01', 'Active', 1),

  (114,
   'Property Maintenance and Preservation Assessment',
   'This evaluation reviews the effectiveness of property upkeep activities including regular site inspections, vendor coordination for maintenance works, and preventive measures to preserve the value of foreclosed properties. Key indicators include the percentage of properties maintained in good condition, maintenance cost efficiency, and responsiveness to reported property concerns. Proper maintenance directly impacts the salability and value recovery of acquired assets.',
   'Acquired Properties', 'Quarterly', 80.00, 20.00,
   'AP-TMP-005', '2026-01-15', '2026-02-01', 'Active', 1);

-- =====================================================
-- AUDIT DEPARTMENT  (AUD-TMP-001 to AUD-TMP-005)
-- template_id: 115 - 119
-- =====================================================
REPLACE INTO evaluation_templates
  (template_id, template_name, description, target_department,
   evaluation_type, kra_weight, behavior_weight,
   form_code, revision_date, effective_date_form, status, created_by)
VALUES
  (115,
   'Branch Audit Execution and Reporting Evaluation',
   'This evaluation assesses the auditor\'s performance in planning, executing, and reporting branch audit assignments within the approved audit calendar. It measures audit coverage rate, findings accuracy, report timeliness, and the quality of recommendations that support branch operational compliance. Thorough branch audits are critical to detecting irregularities and protecting company assets across all pawnshop locations.',
   'Audit', 'Annual', 80.00, 20.00,
   'AUD-TMP-001', '2026-01-15', '2026-02-01', 'Active', 1),

  (116,
   'Audit Findings Follow-Up and Resolution Monitoring',
   'This template evaluates the auditor\'s effectiveness in monitoring the resolution status of previously issued audit findings and management action plans. It measures the percentage of findings addressed within agreed timelines, quality of follow-up documentation, and escalation rate for unresolved findings. Effective follow-up ensures that identified risks are mitigated and do not recur across the organization.',
   'Audit', 'Quarterly', 80.00, 20.00,
   'AUD-TMP-002', '2026-01-15', '2026-02-01', 'Active', 1),

  (117,
   'Cash and Vault Audit Performance Evaluation',
   'This evaluation measures the accuracy and thoroughness of cash count procedures, vault inspections, and petty cash audits conducted at assigned branches and departments. It assesses compliance with cash handling policies, the rate of cash variances discovered, and the timeliness of audit report submission. Cash audits serve as a key control mechanism against misappropriation and accounting errors.',
   'Audit', 'Quarterly', 80.00, 20.00,
   'AUD-TMP-003', '2026-01-15', '2026-02-01', 'Active', 1),

  (118,
   'Inventory and Collateral Audit Evaluation',
   'This template evaluates the auditor\'s performance in verifying pledged collateral inventories, pawn ticket reconciliation, and collateral valuation compliance during branch audit visits. Key indicators include discrepancy detection rate, documentation accuracy, and adherence to collateral audit procedures. Collateral integrity is fundamental to the financial health and regulatory standing of the pawnshop business.',
   'Audit', 'Annual', 80.00, 20.00,
   'AUD-TMP-004', '2026-01-15', '2026-02-01', 'Active', 1),

  (119,
   'Regulatory Compliance Audit Assessment',
   'This evaluation reviews the auditor\'s ability to assess compliance with BSP regulations, AMLA requirements, SEC filings, and local government permits across assigned audit engagements. It measures the completeness of compliance checklist coverage, the accuracy of regulatory findings, and the quality of management recommendations provided. Compliance audit results directly inform the company\'s risk exposure and regulatory standing.',
   'Audit', 'Annual', 80.00, 20.00,
   'AUD-TMP-005', '2026-01-15', '2026-02-01', 'Active', 1);

-- =====================================================
-- BUSINESS DEVELOPMENT DEPARTMENT  (BD-TMP-001 to BD-TMP-005)
-- template_id: 120 - 124
-- =====================================================
REPLACE INTO evaluation_templates
  (template_id, template_name, description, target_department,
   evaluation_type, kra_weight, behavior_weight,
   form_code, revision_date, effective_date_form, status, created_by)
VALUES
  (120,
   'Branch Expansion and Site Development Evaluation',
   'This evaluation assesses the business development officer\'s performance in identifying, evaluating, and recommending new branch locations aligned with the company\'s expansion strategy. It measures the number of viable sites assessed, the quality of feasibility studies submitted, and the percentage of approved sites progressing to actual branch opening. Successful site development is a direct driver of the company\'s revenue growth and market penetration.',
   'Business Development', 'Annual', 80.00, 20.00,
   'BD-TMP-001', '2026-01-15', '2026-02-01', 'Active', 1),

  (121,
   'Partnership and Business Tie-Up Evaluation',
   'This template evaluates the effectiveness of business development activities focused on establishing and maintaining partnerships, memoranda of agreement, and institutional tie-ups that support company growth. Key indicators include the number of new partnerships formalized, partnership revenue contribution, and deal closure timeline. Strategic alliances expand the company\'s product reach and reinforce its competitive position in target markets.',
   'Business Development', 'Annual', 80.00, 20.00,
   'BD-TMP-002', '2026-01-15', '2026-02-01', 'Active', 1),

  (122,
   'Market Research and Feasibility Study Review',
   'This evaluation assesses the quality and depth of market research reports and feasibility studies prepared by the business development team in support of expansion or new product decisions. It measures report accuracy, completeness of financial projections, use of data sources, and alignment with management decision timelines. Rigorous feasibility analysis reduces investment risk and guides sound strategic planning.',
   'Business Development', 'Quarterly', 80.00, 20.00,
   'BD-TMP-003', '2026-01-15', '2026-02-01', 'Active', 1),

  (123,
   'Competitor Intelligence and Industry Analysis Evaluation',
   'This template evaluates the business development officer\'s contribution to competitive benchmarking, industry trend analysis, and market positioning reports submitted to senior management. It measures the frequency and relevance of competitive intelligence reports, data accuracy, and the actionability of insights provided. Timely competitive analysis enables the company to adapt its strategies and maintain market advantage.',
   'Business Development', 'Quarterly', 80.00, 20.00,
   'BD-TMP-004', '2026-01-15', '2026-02-01', 'Active', 1),

  (124,
   'New Product and Service Development Assessment',
   'This evaluation measures the business development staff member\'s involvement in conceptualizing, developing, and launching new financial products or service enhancements aligned with market demand. Key performance indicators include the number of product proposals submitted, timelines from ideation to launch, and initial uptake metrics of newly introduced offerings. Continuous product innovation sustains the company\'s relevance and competitive edge.',
   'Business Development', 'Annual', 80.00, 20.00,
   'BD-TMP-005', '2026-01-15', '2026-02-01', 'Active', 1);

-- =====================================================
-- COMPLIANCE DEPARTMENT  (COM-TMP-001 to COM-TMP-005)
-- template_id: 125 - 129
-- =====================================================
REPLACE INTO evaluation_templates
  (template_id, template_name, description, target_department,
   evaluation_type, kra_weight, behavior_weight,
   form_code, revision_date, effective_date_form, status, created_by)
VALUES
  (125,
   'Regulatory Compliance Monitoring Evaluation',
   'This evaluation measures the compliance officer\'s effectiveness in monitoring adherence to BSP Circulars, SEC regulations, AMLA laws, and local government requirements applicable to pawnshop operations. It assesses the completeness of compliance monitoring checklists, the timeliness of regulatory filings, and the quality of management reports submitted. Proactive compliance monitoring protects the company from sanctions and reputational risk.',
   'Compliance', 'Annual', 80.00, 20.00,
   'COM-TMP-001', '2026-01-15', '2026-02-01', 'Active', 1),

  (126,
   'AMLA and Know-Your-Customer Compliance Assessment',
   'This template evaluates the compliance staff member\'s performance in implementing Anti-Money Laundering Act (AMLA) protocols, customer due diligence procedures, and suspicious transaction monitoring across assigned branches. Key indicators include the rate of completed KYC documentation, timeliness of covered transaction reports (CTRs), and accuracy of suspicious transaction reports (STRs) filed with the AMLC. Strict AMLA compliance is a legal obligation and reputational safeguard for the organization.',
   'Compliance', 'Quarterly', 80.00, 20.00,
   'COM-TMP-002', '2026-01-15', '2026-02-01', 'Active', 1),

  (127,
   'Internal Policy Compliance Review',
   'This evaluation assesses the compliance officer\'s work in reviewing and enforcing adherence to internal company policies, standard operating procedures, and the company\'s Code of Conduct across departments and branches. It measures the number of compliance reviews conducted, findings documented, and corrective action plans endorsed within the evaluation period. Internal policy enforcement maintains organizational discipline and reduces operational risk.',
   'Compliance', 'Annual', 80.00, 20.00,
   'COM-TMP-003', '2026-01-15', '2026-02-01', 'Active', 1),

  (128,
   'License and Permit Renewal Management Evaluation',
   'This evaluation reviews the compliance team\'s effectiveness in tracking, preparing, and submitting renewal applications for business permits, BSP certificates of authority, SEC registrations, and other regulatory licenses required for branch operations. It measures the percentage of licenses renewed on time, documentation completeness, and zero-lapse rate. Timely license management ensures uninterrupted branch operations and regulatory good standing.',
   'Compliance', 'Annual', 80.00, 20.00,
   'COM-TMP-004', '2026-01-15', '2026-02-01', 'Active', 1),

  (129,
   'Compliance Training and Awareness Program Evaluation',
   'This template evaluates the compliance officer\'s effectiveness in developing and delivering compliance training programs, policy orientation sessions, and awareness campaigns for employees across all levels. Key indicators include training completion rates, post-training assessment scores, and reduction in compliance violations following training interventions. An informed workforce is the first line of defense in maintaining a culture of compliance.',
   'Compliance', 'Annual', 80.00, 20.00,
   'COM-TMP-005', '2026-01-15', '2026-02-01', 'Active', 1);

-- =====================================================
-- FINANCE DEPARTMENT  (FIN-TMP-001 to FIN-TMP-005)
-- template_id: 130 - 134
-- =====================================================
REPLACE INTO evaluation_templates
  (template_id, template_name, description, target_department,
   evaluation_type, kra_weight, behavior_weight,
   form_code, revision_date, effective_date_form, status, created_by)
VALUES
  (130,
   'Financial Reporting Accuracy and Timeliness Evaluation',
   'This evaluation measures the finance staff member\'s ability to produce accurate, complete, and timely financial statements, management reports, and regulatory submissions in accordance with Philippine Financial Reporting Standards and company policy. It assesses error rates, reporting deadline compliance, and the quality of variance analysis and footnotes included in financial reports. Reliable financial reporting is essential to management decision-making and stakeholder confidence.',
   'Finance', 'Annual', 80.00, 20.00,
   'FIN-TMP-001', '2026-01-15', '2026-02-01', 'Active', 1),

  (131,
   'Budget Monitoring and Variance Analysis Evaluation',
   'This template evaluates the finance officer\'s performance in monitoring departmental and branch budget utilization, identifying material variances, and providing timely explanations and corrective recommendations to management. Key indicators include the frequency of budget review reports, accuracy of variance computations, and the quality of actionable insights provided. Effective budget monitoring controls costs and supports financial sustainability.',
   'Finance', 'Quarterly', 80.00, 20.00,
   'FIN-TMP-002', '2026-01-15', '2026-02-01', 'Active', 1),

  (132,
   'Cash Flow and Treasury Management Evaluation',
   'This evaluation assesses the finance staff member\'s management of daily cash positions, fund transfers, bank reconciliations, and liquidity forecasting to ensure the company meets its operational and financial obligations. It measures the accuracy of cash flow projections, frequency of unreconciled items, and turnaround time for bank reconciliation completion. Effective cash management safeguards the company\'s liquidity and financial stability.',
   'Finance', 'Quarterly', 80.00, 20.00,
   'FIN-TMP-003', '2026-01-15', '2026-02-01', 'Active', 1),

  (133,
   'Accounts Payable Processing Evaluation',
   'This template evaluates the accuracy, completeness, and timeliness of vendor invoice processing, check voucher preparation, and payment releases handled by the accounts payable team. It measures payment processing cycle time, error and rework rates, and vendor satisfaction with payment reliability. Efficient accounts payable operations protect supplier relationships and maintain the company\'s creditworthiness.',
   'Finance', 'Quarterly', 80.00, 20.00,
   'FIN-TMP-004', '2026-01-15', '2026-02-01', 'Active', 1),

  (134,
   'Tax Compliance and BIR Reporting Evaluation',
   'This evaluation measures the finance staff member\'s effectiveness in preparing and filing all required Bureau of Internal Revenue (BIR) returns, including VAT, income tax, expanded withholding tax, and other applicable filings, within prescribed deadlines. Key performance indicators include the percentage of returns filed on time, absence of tax deficiency notices, and accuracy of tax computations. Strict BIR compliance minimizes tax penalties and preserves the company\'s good standing with the revenue authority.',
   'Finance', 'Annual', 80.00, 20.00,
   'FIN-TMP-005', '2026-01-15', '2026-02-01', 'Active', 1);

-- =====================================================
-- GENERAL SERVICES DEPARTMENT  (GS-TMP-001 to GS-TMP-005)
-- template_id: 135 - 139
-- =====================================================
REPLACE INTO evaluation_templates
  (template_id, template_name, description, target_department,
   evaluation_type, kra_weight, behavior_weight,
   form_code, revision_date, effective_date_form, status, created_by)
VALUES
  (135,
   'Facilities Maintenance and Building Services Evaluation',
   'This evaluation assesses the general services staff member\'s effectiveness in coordinating preventive and corrective maintenance of company-owned and leased facilities, including branch offices, warehouses, and the main office. It measures the resolution rate of maintenance requests, average downtime of building utilities, and cost efficiency of maintenance works completed. Reliable facility maintenance directly supports employee productivity and branch operational continuity.',
   'General Services', 'Quarterly', 80.00, 20.00,
   'GS-TMP-001', '2026-01-15', '2026-02-01', 'Active', 1),

  (136,
   'Procurement and Vendor Management Evaluation',
   'This template evaluates the general services officer\'s performance in processing purchase requests, canvassing suppliers, preparing purchase orders, and coordinating deliveries in compliance with the company\'s procurement policy. Key indicators include compliance with the three-quote requirement, procurement cycle time, and vendor delivery performance ratings. Sound procurement practices ensure cost-effective purchasing and uninterrupted supply of office and operational requirements.',
   'General Services', 'Quarterly', 80.00, 20.00,
   'GS-TMP-002', '2026-01-15', '2026-02-01', 'Active', 1),

  (137,
   'Office Supplies Inventory Control Evaluation',
   'This evaluation measures the accuracy and efficiency of office supplies inventory management, including stock replenishment, issuance tracking, and periodic physical counts of supplies maintained at the general services warehouse. It assesses inventory record accuracy, stockout frequency, and the effectiveness of par-level controls. Effective inventory management prevents both shortages and unnecessary overstocking of supplies.',
   'General Services', 'Quarterly', 80.00, 20.00,
   'GS-TMP-003', '2026-01-15', '2026-02-01', 'Active', 1),

  (138,
   'Fleet and Vehicle Management Evaluation',
   'This template evaluates the general services coordinator\'s management of the company vehicle fleet, including scheduling, registration renewal, preventive maintenance coordination, and fuel consumption monitoring. Key performance indicators include vehicle availability rate, percentage of vehicles with up-to-date registrations and insurance, and maintenance cost per vehicle. Efficient fleet management reduces operational disruptions and controls transportation expenditures.',
   'General Services', 'Annual', 80.00, 20.00,
   'GS-TMP-004', '2026-01-15', '2026-02-01', 'Active', 1),

  (139,
   'Security and Safety Management Evaluation',
   'This evaluation assesses the general services staff member\'s oversight of security protocols, safety drill conduct, incident reporting, and coordination with security service providers across company premises. It measures the frequency and quality of security incident reports, drill completion rates, and compliance with DOLE Occupational Safety and Health (OSH) requirements. A safe and secure workplace is a legal obligation and a fundamental condition for productive operations.',
   'General Services', 'Annual', 80.00, 20.00,
   'GS-TMP-005', '2026-01-15', '2026-02-01', 'Active', 1);

-- =====================================================
-- INFORMATION TECHNOLOGY DEPARTMENT  (IT-TMP-001 to IT-TMP-005)
-- template_id: 140 - 144
-- =====================================================
REPLACE INTO evaluation_templates
  (template_id, template_name, description, target_department,
   evaluation_type, kra_weight, behavior_weight,
   form_code, revision_date, effective_date_form, status, created_by)
VALUES
  (140,
   'System Availability and Uptime Performance Evaluation',
   'This evaluation measures the IT staff member\'s effectiveness in maintaining core business systems, network infrastructure, and branch connectivity at acceptable uptime levels throughout the evaluation period. Key indicators include system availability percentage, mean time to recovery (MTTR) for outages, and scheduled maintenance completion rate. High system availability is essential to branch operations, customer service delivery, and business continuity.',
   'Information Technology', 'Quarterly', 80.00, 20.00,
   'IT-TMP-001', '2026-01-15', '2026-02-01', 'Active', 1),

  (141,
   'Helpdesk and Technical Support Evaluation',
   'This template assesses the IT support staff\'s responsiveness and resolution effectiveness for technical support tickets raised by employees across all branches and departments. Performance indicators include average ticket resolution time, first-call resolution rate, user satisfaction scores, and backlog management. Efficient helpdesk service minimizes employee downtime and sustains operational efficiency across the organization.',
   'Information Technology', 'Quarterly', 80.00, 20.00,
   'IT-TMP-002', '2026-01-15', '2026-02-01', 'Active', 1),

  (142,
   'Cybersecurity and Data Protection Compliance Assessment',
   'This evaluation measures the IT team member\'s contribution to implementing and enforcing cybersecurity policies, data protection protocols, user access controls, and vulnerability management activities within the IT environment. It assesses the frequency of security assessments, patch management compliance rates, and adherence to the company\'s data privacy obligations under the Data Privacy Act. Robust cybersecurity practices protect the company from data breaches and associated legal and reputational risks.',
   'Information Technology', 'Annual', 80.00, 20.00,
   'IT-TMP-003', '2026-01-15', '2026-02-01', 'Active', 1),

  (143,
   'Software Development and Systems Enhancement Review',
   'This template evaluates the programmer or developer\'s performance in delivering system enhancements, bug fixes, and new application features within agreed development timelines and quality standards. Key performance indicators include sprint delivery rate, defect density, code review compliance, and user acceptance test (UAT) pass rate. Quality software development directly supports HR, Finance, and Operations system functionality.',
   'Information Technology', 'Annual', 80.00, 20.00,
   'IT-TMP-004', '2026-01-15', '2026-02-01', 'Active', 1),

  (144,
   'IT Asset and License Management Evaluation',
   'This evaluation assesses the IT staff member\'s management of the company\'s hardware and software asset inventory, including tracking procurement, deployment, maintenance schedules, and software license renewals. It measures asset record accuracy, license compliance rate, and the percentage of assets with up-to-date warranty and support coverage. Proper IT asset management controls technology costs and ensures legal compliance with software licensing agreements.',
   'Information Technology', 'Annual', 80.00, 20.00,
   'IT-TMP-005', '2026-01-15', '2026-02-01', 'Active', 1);

-- =====================================================
-- MARKETING DEPARTMENT  (MKT-TMP-001 to MKT-TMP-005)
-- template_id: 145 - 149
-- =====================================================
REPLACE INTO evaluation_templates
  (template_id, template_name, description, target_department,
   evaluation_type, kra_weight, behavior_weight,
   form_code, revision_date, effective_date_form, status, created_by)
VALUES
  (145,
   'Marketing Campaign Effectiveness Evaluation',
   'This evaluation assesses the marketing staff member\'s ability to plan, execute, and measure the impact of promotional campaigns, product launches, and brand awareness initiatives within the evaluation period. Key performance indicators include campaign reach, lead generation volume, conversion rate, and return on marketing investment. Effective campaigns drive customer acquisition and strengthen brand recognition in target communities.',
   'Marketing', 'Quarterly', 80.00, 20.00,
   'MKT-TMP-001', '2026-01-15', '2026-02-01', 'Active', 1),

  (146,
   'Digital and Social Media Marketing Performance Review',
   'This template evaluates the marketing officer\'s management of the company\'s digital presence, including social media account performance, content publishing, online advertising campaigns, and community engagement metrics. It measures follower growth, engagement rate, post reach, and the quality and frequency of published digital content. A strong digital marketing presence is increasingly vital for customer reach and competitive differentiation.',
   'Marketing', 'Quarterly', 80.00, 20.00,
   'MKT-TMP-002', '2026-01-15', '2026-02-01', 'Active', 1),

  (147,
   'Brand Management and Communication Standards Evaluation',
   'This evaluation reviews the marketing staff\'s adherence to brand guidelines, visual identity standards, and communication protocols across all company materials, promotions, and customer touchpoints. Key indicators include the percentage of materials reviewed for brand compliance, correction rate, and timeliness of approval cycles. Consistent brand management reinforces customer trust and maintains the professional image of Raquel Pawnshop.',
   'Marketing', 'Annual', 80.00, 20.00,
   'MKT-TMP-003', '2026-01-15', '2026-02-01', 'Active', 1),

  (148,
   'Customer Acquisition and Retention Campaign Evaluation',
   'This template evaluates the marketing staff member\'s contribution to programs designed to attract new customers and retain existing clients through loyalty programs, referral campaigns, and repeat business incentives. It measures new customer acquisition numbers, retention rate, and the cost per acquired customer against approved marketing budgets. Customer-focused marketing programs directly support revenue growth and long-term client relationships.',
   'Marketing', 'Annual', 80.00, 20.00,
   'MKT-TMP-004', '2026-01-15', '2026-02-01', 'Active', 1),

  (149,
   'Market Research and Consumer Insights Evaluation',
   'This evaluation assesses the marketing officer\'s ability to conduct structured market research, analyze consumer behavior trends, and deliver actionable insights that guide marketing strategies and product positioning. Key indicators include the frequency and quality of research reports submitted, the relevance of recommendations to management decisions, and the use of credible data sources. Data-driven marketing decisions improve campaign effectiveness and resource allocation.',
   'Marketing', 'Annual', 80.00, 20.00,
   'MKT-TMP-005', '2026-01-15', '2026-02-01', 'Active', 1);

-- =====================================================
-- OFFICE OF THE PRESIDENT  (OP-TMP-001 to OP-TMP-005)
-- template_id: 150 - 154
-- =====================================================
REPLACE INTO evaluation_templates
  (template_id, template_name, description, target_department,
   evaluation_type, kra_weight, behavior_weight,
   form_code, revision_date, effective_date_form, status, created_by)
VALUES
  (150,
   'Executive Assistance and Office Management Evaluation',
   'This evaluation measures the executive assistant\'s effectiveness in managing the President\'s calendar, coordinating executive-level meetings, preparing correspondence, and maintaining office administration at the highest standard of professionalism. It assesses scheduling accuracy, communication quality, document turnaround time, and confidentiality in handling sensitive information. Exceptional executive support enables the President to operate with maximum efficiency and strategic focus.',
   'Office of the President', 'Annual', 80.00, 20.00,
   'OP-TMP-001', '2026-01-15', '2026-02-01', 'Active', 1),

  (151,
   'Board and Management Meeting Coordination Evaluation',
   'This template evaluates the executive assistant\'s performance in coordinating board meetings, management committee sessions, and executive presentations, including preparation of agendas, minutes of meetings, and supporting materials. Key indicators include meeting preparation timeliness, minutes quality, and distribution of documentation within approved timelines. Effective meeting coordination ensures that executive decisions are properly documented and actioned.',
   'Office of the President', 'Quarterly', 80.00, 20.00,
   'OP-TMP-002', '2026-01-15', '2026-02-01', 'Active', 1),

  (152,
   'Executive Communications and Stakeholder Liaison Evaluation',
   'This evaluation assesses the executive assistant\'s skill in managing correspondence, stakeholder communications, and protocol arrangements for the Office of the President, including interactions with government officials, investors, and major business partners. It measures communication accuracy, protocol compliance, and the professionalism of all correspondence produced on behalf of the President. Polished executive communications reflect positively on the organization\'s leadership and credibility.',
   'Office of the President', 'Annual', 80.00, 20.00,
   'OP-TMP-003', '2026-01-15', '2026-02-01', 'Active', 1),

  (153,
   'Confidentiality and Information Security Compliance Review',
   'This template evaluates the executive staff\'s adherence to confidentiality protocols, information security policies, and proper handling of sensitive executive and corporate documents. Key indicators include the absence of information breach incidents, compliance with document classification standards, and responsiveness to data handling policy updates. Strict confidentiality is a non-negotiable requirement for staff in proximity to top executive leadership.',
   'Office of the President', 'Annual', 80.00, 20.00,
   'OP-TMP-004', '2026-01-15', '2026-02-01', 'Active', 1),

  (154,
   'Strategic Project Support and Special Assignments Evaluation',
   'This evaluation measures the executive assistant\'s contribution to special projects, cross-departmental initiatives, and strategic assignments delegated by the President. It assesses project milestone adherence, quality of deliverables, and the ability to coordinate with multiple departments to achieve executive-directed outcomes. Reliable special project support allows the Office of the President to pursue strategic priorities with organized and timely execution.',
   'Office of the President', 'Annual', 80.00, 20.00,
   'OP-TMP-005', '2026-01-15', '2026-02-01', 'Active', 1);

-- =====================================================
-- OPERATIONS DEPARTMENT  (OPS-TMP-001 to OPS-TMP-005)
-- template_id: 155 - 159
-- =====================================================
REPLACE INTO evaluation_templates
  (template_id, template_name, description, target_department,
   evaluation_type, kra_weight, behavior_weight,
   form_code, revision_date, effective_date_form, status, created_by)
VALUES
  (155,
   'Branch Staff Daily Operations Performance Evaluation',
   'This evaluation measures the branch staff member\'s effectiveness in processing pawn transactions, customer loan releases, interest collections, and redemption activities in accordance with company operating procedures and BSP regulations. Key performance indicators include transaction accuracy rate, customer service quality scores, and compliance with collateral appraisal guidelines. Consistent branch operations ensure customer satisfaction and reliable revenue generation for the company.',
   'Operations', 'Annual', 80.00, 20.00,
   'OPS-TMP-001', '2026-01-15', '2026-02-01', 'Active', 1),

  (156,
   'Branch Cash Management and Accountability Evaluation',
   'This template evaluates the branch staff member\'s handling of cash transactions, vault balancing, cash position reporting, and compliance with cash handling policies at the branch level. It assesses cash variance rates, frequency of vault shortages or overages, and adherence to daily cash count procedures. Accurate cash management is fundamental to branch financial integrity and the prevention of cash-related irregularities.',
   'Operations', 'Quarterly', 80.00, 20.00,
   'OPS-TMP-002', '2026-01-15', '2026-02-01', 'Active', 1),

  (157,
   'Customer Service Excellence Evaluation',
   'This evaluation assesses the branch employee\'s delivery of professional and courteous customer service throughout all points of interaction, including client reception, transaction processing, and complaint handling. Key indicators include customer satisfaction survey scores, complaint resolution rate, and feedback from branch supervisors on service behavior. Exceptional customer service differentiates the branch and builds long-term client loyalty.',
   'Operations', 'Quarterly', 80.00, 20.00,
   'OPS-TMP-003', '2026-01-15', '2026-02-01', 'Active', 1),

  (158,
   'Collateral Appraisal and Loan Processing Accuracy Review',
   'This evaluation measures the accuracy and consistency of collateral appraisals, loan-to-value computations, and loan documentation completeness performed by the branch staff. It assesses appraisal deviation rates, percentage of complete loan documentation packages, and compliance with internal lending guidelines. Accurate appraisal and processing protects the company from overexposure and supports responsible lending practices.',
   'Operations', 'Annual', 80.00, 20.00,
   'OPS-TMP-004', '2026-01-15', '2026-02-01', 'Active', 1),

  (159,
   'Branch Compliance and Regulatory Adherence Evaluation',
   'This template evaluates the branch staff member\'s compliance with BSP regulations, company SOPs, AMLA protocols, and anti-fraud controls in the day-to-day performance of pawnshop operations. Key performance indicators include compliance audit scores, absence of regulatory violations, and completion rate of required compliance trainings. Branch-level compliance adherence is essential to protecting the organization\'s regulatory license and public trust.',
   'Operations', 'Annual', 80.00, 20.00,
   'OPS-TMP-005', '2026-01-15', '2026-02-01', 'Active', 1);

-- =====================================================
-- PURCHASING DEPARTMENT  (PUR-TMP-001 to PUR-TMP-005)
-- template_id: 160 - 164
-- =====================================================
REPLACE INTO evaluation_templates
  (template_id, template_name, description, target_department,
   evaluation_type, kra_weight, behavior_weight,
   form_code, revision_date, effective_date_form, status, created_by)
VALUES
  (160,
   'Purchase Requisition Processing Efficiency Evaluation',
   'This evaluation measures the purchasing staff member\'s accuracy and timeliness in receiving, validating, and processing purchase requisitions from departments and branches within the approved procurement cycle. It assesses PR-to-PO conversion time, documentation completeness, and the percentage of requisitions processed within the service-level agreement. Efficient requisition handling ensures departments receive their required supplies and services without operational delays.',
   'Purchasing', 'Quarterly', 80.00, 20.00,
   'PUR-TMP-001', '2026-01-15', '2026-02-01', 'Active', 1),

  (161,
   'Supplier Accreditation and Vendor Performance Evaluation',
   'This template assesses the purchasing officer\'s performance in evaluating, accrediting, and maintaining a roster of qualified suppliers and service providers that meet the company\'s quality, reliability, and cost-effectiveness standards. Key indicators include the number of vendors accredited per period, vendor evaluation completion rate, and the percentage of approved vendors meeting delivery and quality commitments. A strong supplier base ensures competitive pricing and consistent supply quality.',
   'Purchasing', 'Annual', 80.00, 20.00,
   'PUR-TMP-002', '2026-01-15', '2026-02-01', 'Active', 1),

  (162,
   'Cost Savings and Procurement Efficiency Evaluation',
   'This evaluation measures the purchasing staff member\'s ability to negotiate favorable pricing, leverage bulk purchasing opportunities, and achieve cost savings against approved budgets across all procurement categories. It assesses the variance between budgeted and actual procurement costs, number of cost-saving initiatives implemented, and compliance with the competitive bidding policy. Effective cost management in procurement directly contributes to the company\'s profitability.',
   'Purchasing', 'Annual', 80.00, 20.00,
   'PUR-TMP-003', '2026-01-15', '2026-02-01', 'Active', 1),

  (163,
   'Delivery Monitoring and Supplier Coordination Evaluation',
   'This template evaluates the purchasing officer\'s effectiveness in coordinating with suppliers to ensure timely delivery of goods and services, resolving delivery disputes, and maintaining accurate delivery tracking records. Key performance indicators include on-time delivery rate, percentage of delivery discrepancy cases resolved, and supplier communication responsiveness. Reliable delivery monitoring prevents supply shortages that could disrupt branch and departmental operations.',
   'Purchasing', 'Quarterly', 80.00, 20.00,
   'PUR-TMP-004', '2026-01-15', '2026-02-01', 'Active', 1),

  (164,
   'Procurement Compliance and Documentation Evaluation',
   'This evaluation assesses the purchasing staff\'s adherence to procurement policies, documentation requirements, and approval workflows across all purchasing activities within the evaluation period. It measures compliance with canvassing procedures, purchase order documentation accuracy, and the completeness of supplier contracts on file. Compliant procurement practices reduce the risk of audit findings and maintain the integrity of the company\'s purchasing function.',
   'Purchasing', 'Annual', 80.00, 20.00,
   'PUR-TMP-005', '2026-01-15', '2026-02-01', 'Active', 1);

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- SUMMARY
-- =====================================================
-- Total templates inserted: 65
--
-- template_id  Department                Count
-- 100-109      Human Resources           10
-- 110-114      Acquired Properties        5
-- 115-119      Audit                      5
-- 120-124      Business Development       5
-- 125-129      Compliance                 5
-- 130-134      Finance                    5
-- 135-139      General Services           5
-- 140-144      Information Technology     5
-- 145-149      Marketing                  5
-- 150-154      Office of the President    5
-- 155-159      Operations                 5
-- 160-164      Purchasing                 5
-- =====================================================
