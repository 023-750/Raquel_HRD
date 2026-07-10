-- Mockup Employee Seeds for Purchasing Department
USE raquel_hris;
SET FOREIGN_KEY_CHECKS = 0;

-- ====================================
-- 1. EMPLOYEES (Purchasing Team)
-- ====================================
REPLACE INTO employees (employee_id, employee_code, first_name, last_name, middle_name, hire_date, date_of_birth, place_of_birth, gender, civil_status, job_title_id, job_title, department_id, rank_category_id, branch_id, employment_status, employment_type, profile_picture) VALUES
(12001, 'PUR-001', 'Divina', 'Lopez', 'Villanueva', '2021-06-07', '1992-07-03', 'Lucena City, Quezon', 'Female', 'Separated', 1200, 'Purchasing Supervisor I', 12, 4, 102, 'Regular', 'Full-time', 'avatar_f.jpg'),
(12002, 'PUR-002', 'Rose', 'Sarmiento', 'Soriano', '2019-05-19', '1985-11-11', 'Lucena City, Quezon', 'Female', 'Widowed', 1201, 'Purchasing Supervisor on Training', 12, 4, 102, 'Trainee', 'Full-time', 'avatar_f.jpg'),
(12003, 'PUR-003', 'Antonio', 'Santiago', 'Salvador', '2024-11-05', '1999-03-10', 'Lucena City, Quezon', 'Male', 'Separated', 1202, 'Purchasing Staff I', 12, 5, 102, 'Regular', 'Full-time', 'avatar_m.jpg');

REPLACE INTO employee_contacts (employee_id, personal_email, mobile_number, telephone_number) VALUES
(12001, 'divina.lopez@example.com', '09178233471', '888-12001'),
(12002, 'rose.sarmiento@example.com', '09176109503', '888-12002'),
(12003, 'antonio.santiago@example.com', '09173252801', '888-12003');

REPLACE INTO employee_details (employee_id, height_m, weight_kg, blood_type, citizenship) VALUES
(12001, 1.76, 62.8, 'B+', 'Filipino'),
(12002, 1.8, 79.8, 'A-', 'Filipino'),
(12003, 1.52, 70.0, 'A+', 'Filipino');

REPLACE INTO employee_family (employee_id, member_type, surname, first_name, middle_name, occupation) VALUES
(12001, 'Father', 'De Leon', 'Arthur', 'Villanueva', 'Retired'),
(12001, 'Mother', 'Evangelista', 'Elizabeth', 'Reyes', 'Homemaker'),
(12002, 'Father', 'Flores', 'Anthony', 'Reyes', 'Retired'),
(12002, 'Mother', 'Dela Cruz', 'Divina', 'Gomez', 'Homemaker'),
(12003, 'Father', 'Mendoza', 'Francis', 'Reyes', 'Retired'),
(12003, 'Mother', 'Tolentino', 'Christina', 'Rivera', 'Homemaker');

REPLACE INTO employee_education (employee_id, education_level, school_name, degree_course, year_graduated) VALUES
(12001, 'College', 'Holy Angel University', 'AB Communication', '2013'),
(12002, 'College', 'Southern Luzon State University', 'BS Accountancy', '2006'),
(12003, 'College', 'Pamantasan ng Lungsod ng Maynila', 'BS Management', '2020');

REPLACE INTO employee_work_experience (employee_id, date_from, date_to, job_title, company_name, monthly_salary) VALUES
(12001, '2017-01-15', '2020-12-15', 'Previous I Role', 'Prime Logistics Co.', 37404),
(12002, '2015-01-15', '2018-12-15', 'Previous Training Role', 'Global Retail Corp.', 27639),
(12003, '2020-01-15', '2023-12-15', 'Previous I Role', 'Pacific Marketing Group', 38422);

REPLACE INTO employee_trainings (employee_id, training_title, conducted_by, no_of_hours) VALUES
(12001, 'Strategic HR & Talent Development', 'Corporate Training Dept', 16.0),
(12002, 'Advanced Management & Leadership', 'Corporate Training Dept', 16.0),
(12003, 'ISO 9001:2015 Quality Management', 'Corporate Training Dept', 16.0);

REPLACE INTO employee_disclosures (employee_id, is_related_to_company, has_admin_offense, has_criminal_charge) VALUES
(12001, 0, 0, 0),
(12002, 0, 0, 0),
(12003, 0, 0, 0);

REPLACE INTO employee_government_ids (employee_id, sss_number, philhealth_number, pagibig_number, tin_number) VALUES
(12001, '51-2638360-7', '32-430750834-6', '2140-2781-7208', '435-606-760-000'),
(12002, '57-1891328-0', '81-981277125-5', '2797-5623-1510', '340-542-819-000'),
(12003, '68-9546564-6', '80-980544989-7', '7303-6821-4698', '290-534-719-000');

REPLACE INTO employee_addresses (employee_id, address_type, barangay, city, province) VALUES
(12001, 'Residential', 'Barangay 11', 'Lucena City', 'Quezon'),
(12001, 'Permanent', 'Barangay 11', 'Lucena City', 'Quezon'),
(12002, 'Residential', 'Barangay 10', 'Candelaria', 'Quezon'),
(12002, 'Permanent', 'Barangay 10', 'Candelaria', 'Quezon'),
(12003, 'Residential', 'Barangay 1', 'Pagbilao', 'Quezon'),
(12003, 'Permanent', 'Barangay 1', 'Pagbilao', 'Quezon');

REPLACE INTO employee_emergency_contacts (employee_id, contact_name, relationship, contact_number) VALUES
(12001, 'Arthur De Leon', 'Father', '09182518891'),
(12002, 'Anthony Flores', 'Father', '09186337290'),
(12003, 'Francis Mendoza', 'Father', '09189357686');

REPLACE INTO employee_real_properties (employee_id, description, kind, acquisition_cost) VALUES
(12001, 'Residential House and Lot', 'Building and Land', 1956364.00),
(12002, 'Residential House and Lot', 'Building and Land', 2992697.00),
(12003, 'Residential House and Lot', 'Building and Land', 3275876.00);

REPLACE INTO employee_personal_properties (employee_id, description, acquisition_cost) VALUES
(12001, 'Personal Effects and Savings', 172832.00),
(12002, 'Personal Effects and Savings', 300692.00),
(12003, 'Personal Effects and Savings', 483209.00);

REPLACE INTO employee_liabilities (employee_id, nature_of_liability, creditor_name, outstanding_balance) VALUES
(12001, 'Personal Loan', 'Bank', 10048.00),
(12002, 'Personal Loan', 'Bank', 116439.00),
(12003, 'Personal Loan', 'Bank', 72062.00);

REPLACE INTO employee_references (employee_id, reference_name, reference_address, reference_telephone) VALUES
(12001, 'Reference George Mendoza', 'Quezon Province', '09203503436'),
(12002, 'Reference Paul Lopez', 'Quezon Province', '09204543617'),
(12003, 'Reference Ricardo Fernandez', 'Quezon Province', '09205517272');

SET FOREIGN_KEY_CHECKS = 1;