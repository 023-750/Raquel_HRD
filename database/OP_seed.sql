-- Mockup Employee Seeds for Office of the President Department
USE raquel_hris;
SET FOREIGN_KEY_CHECKS = 0;

-- ====================================
-- 1. EMPLOYEES (Office of the President Team)
-- ====================================
REPLACE INTO employees (employee_id, employee_code, first_name, last_name, middle_name, hire_date, date_of_birth, place_of_birth, gender, civil_status, job_title_id, job_title, department_id, rank_category_id, branch_id, employment_status, employment_type, profile_picture) VALUES
(10001, 'OP-001', 'Bernadette', 'Salvador', 'Mendoza', '2018-07-18', '1979-06-07', 'Lucena City, Quezon', 'Female', 'Widowed', 1100, 'President and CEO', 10, 1, 102, 'Regular', 'Full-time', 'avatar_f.jpg'),
(10002, 'OP-002', 'Ricardo', 'Gonzales', 'Santos', '2022-04-11', '2002-01-16', 'Lucena City, Quezon', 'Male', 'Widowed', 1101, 'Executive Assistant I', 10, 5, 102, 'Regular', 'Full-time', 'avatar_m.jpg'),
(10003, 'OP-003', 'Susan', 'Cruz', 'Ocampo', '2024-04-12', '2004-11-10', 'Lucena City, Quezon', 'Female', 'Married', 1102, 'Executive Assistant II', 10, 5, 102, 'Regular', 'Full-time', 'avatar_f.jpg'),
(10004, 'OP-004', 'Arthur', 'Salvador', 'Gonzales', '2022-07-12', '1993-01-23', 'Lucena City, Quezon', 'Male', 'Separated', 1103, 'Executive Assistant III', 10, 5, 102, 'Regular', 'Full-time', 'avatar_m.jpg'),
(999, 'JUICE-999', 'Jarad', 'Higgins', 'Anthony', '2017-01-01', '1990-01-01', 'Lucena City, Quezon', 'Male', 'Married', 1103, 'Executive Assistant III', 10, 5, 102, 'Regular', 'Full-time', 'juice.png');

-- President is the final employee-portal reviewer for department packages.
UPDATE employees SET reports_to = NULL WHERE employee_code = 'OP-001';
UPDATE employees SET reports_to = 10001
WHERE employee_code IN ('OP-002', 'OP-003', 'OP-004', 'JUICE-999');

REPLACE INTO employee_contacts (employee_id, personal_email, mobile_number, telephone_number) VALUES
(10001, 'bernadette.salvador@example.com', '09178333427', '888-10001'),
(10002, 'ricardo.gonzales@example.com', '09178050508', '888-10002'),
(10003, 'susan.cruz@example.com', '09171873883', '888-10003'),
(10004, 'arthur.salvador@example.com', '09173260430', '888-10004'),
(999, 'jarad.higgins@example.com', '09171234567', '888-10005');

REPLACE INTO employee_details (employee_id, height_m, weight_kg, blood_type, citizenship) VALUES
(10001, 1.64, 53.6, 'A-', 'Filipino'),
(10002, 1.62, 75.2, 'B+', 'Filipino'),
(10003, 1.79, 55.4, 'O+', 'Filipino'),
(10004, 1.6, 49.3, 'A-', 'Filipino'),
(999, 1.75, 70.0, 'O+', 'Filipino');

REPLACE INTO employee_family (employee_id, member_type, surname, first_name, middle_name, occupation) VALUES
(10001, 'Father', 'Bautista', 'Albert', 'Perez', 'Retired'),
(10001, 'Mother', 'Lopez', 'Elena', 'Villanueva', 'Homemaker'),
(10002, 'Father', 'De Leon', 'David', 'Cruz', 'Retired'),
(10002, 'Mother', 'Santos', 'Christina', 'Garcia', 'Homemaker'),
(10003, 'Father', 'Tolentino', 'Michael', 'Rivera', 'Retired'),
(10003, 'Mother', 'Dela Cruz', 'Grace', 'Aquino', 'Homemaker'),
(10003, 'Spouse', 'Aquino', 'Ronald', 'Soriano', 'Office Employee'),
(10004, 'Father', 'Cruz', 'David', 'Mendoza', 'Retired'),
(10004, 'Mother', 'De Leon', 'Imelda', 'Pascual', 'Homemaker'),
(999, 'Father', 'Higgins', 'Antonio', 'Anthony', 'Retired');

REPLACE INTO employee_education (employee_id, education_level, school_name, degree_course, year_graduated) VALUES
(10001, 'College', 'Southern Luzon State University', 'BS Management', '2000'),
(10002, 'College', 'Mapua University', 'BS Business Administration', '2023'),
(10003, 'College', 'Ateneo de Manila University', 'BS Accountancy', '2025'),
(10004, 'College', 'University of Santo Tomas', 'BS Management', '2014'),
(999, 'College', 'Polytechnic University of the Philippines', 'BS Business Administration', '2011');

REPLACE INTO employee_work_experience (employee_id, date_from, date_to, job_title, company_name, monthly_salary) VALUES
(10001, '2014-01-15', '2017-12-15', 'Previous CEO Role', 'Summit Property Management', 27328),
(10002, '2018-01-15', '2021-12-15', 'Previous I Role', 'United Services Group', 23950),
(10003, '2020-01-15', '2023-12-15', 'Previous II Role', 'Secure Tech Philippines', 18292),
(10004, '2018-01-15', '2021-12-15', 'Previous III Role', 'Global Retail Corp.', 35813),
(999, '2013-06-01', '2016-12-31', 'Administrative Officer', 'Quezon Cooperative Bank', 18500);

REPLACE INTO employee_trainings (employee_id, training_title, conducted_by, no_of_hours) VALUES
(10001, 'Financial Management and Tax Audits', 'Corporate Training Dept', 16.0),
(10002, 'IT Infrastructure and Security', 'Corporate Training Dept', 16.0),
(10003, 'Advanced Management & Leadership', 'Corporate Training Dept', 16.0),
(10004, 'Customer Service Excellence', 'Corporate Training Dept', 16.0),
(999, 'Business Process Improvement', 'Corporate Training Dept', 16.0);

REPLACE INTO employee_disclosures (employee_id, is_related_to_company, has_admin_offense, has_criminal_charge) VALUES
(10001, 0, 0, 0),
(10002, 0, 0, 0),
(10003, 0, 0, 0),
(10004, 0, 0, 0),
(999, 0, 0, 0);

REPLACE INTO employee_government_ids (employee_id, sss_number, philhealth_number, pagibig_number, tin_number) VALUES
(10001, '62-4154127-1', '33-246698943-4', '2808-8460-8418', '378-817-737-000'),
(10002, '42-7401806-0', '25-924790131-3', '7304-8257-5049', '855-134-303-000'),
(10003, '84-8973718-9', '29-498786278-1', '5075-3583-9190', '193-802-733-000'),
(10004, '51-8858359-4', '86-695411648-2', '2128-9702-3539', '560-402-939-000'),
(999, '73-5621048-2', '41-378294561-7', '3916-7204-6183', '712-304-581-000');

REPLACE INTO employee_addresses (employee_id, address_type, region, barangay, city, province) VALUES
(10001, 'Residential', 'Region IV-A (CALABARZON)', 'Barangay 8', 'Tayabas City', 'Quezon'),
(10001, 'Permanent', 'Region IV-A (CALABARZON)', 'Barangay 8', 'Tayabas City', 'Quezon'),
(10002, 'Residential', 'Region IV-A (CALABARZON)', 'Barangay 8', 'Lucena City', 'Quezon'),
(10002, 'Permanent', 'Region IV-A (CALABARZON)', 'Barangay 8', 'Lucena City', 'Quezon'),
(10003, 'Residential', 'Region IV-A (CALABARZON)', 'Barangay 6', 'Lucena City', 'Quezon'),
(10003, 'Permanent', 'Region IV-A (CALABARZON)', 'Barangay 6', 'Lucena City', 'Quezon'),
(10004, 'Residential', 'Region IV-A (CALABARZON)', 'Barangay 5', 'Pagbilao', 'Quezon'),
(10004, 'Permanent', 'Region IV-A (CALABARZON)', 'Barangay 5', 'Pagbilao', 'Quezon'),
(999, 'Residential', 'Region IV-A (CALABARZON)', 'Barangay 3', 'Lucena City', 'Quezon'),
(999, 'Permanent', 'Region IV-A (CALABARZON)', 'Barangay 3', 'Lucena City', 'Quezon');

REPLACE INTO employee_emergency_contacts (employee_id, contact_name, relationship, contact_number) VALUES
(10001, 'Albert Bautista', 'Father', '09186379509'),
(10002, 'David De Leon', 'Father', '09189743263'),
(10003, 'Michael Tolentino', 'Father', '09182002860'),
(10004, 'David Cruz', 'Father', '09187159129'),
(999, 'Antonio Higgins', 'Father', '09185671234');

REPLACE INTO employee_real_properties (employee_id, description, kind, acquisition_cost) VALUES
(10001, 'Residential House and Lot', 'Building and Land', 1540675.00),
(10002, 'Residential House and Lot', 'Building and Land', 2896719.00),
(10003, 'Residential House and Lot', 'Building and Land', 2245574.00),
(10004, 'Residential House and Lot', 'Building and Land', 2452272.00),
(999, 'Residential House and Lot', 'Building and Land', 1875000.00);

REPLACE INTO employee_personal_properties (employee_id, description, acquisition_cost) VALUES
(10001, 'Personal Effects and Savings', 179770.00),
(10002, 'Personal Effects and Savings', 209286.00),
(10003, 'Personal Effects and Savings', 437942.00),
(10004, 'Personal Effects and Savings', 100377.00),
(999, 'Personal Effects and Savings', 154320.00);

REPLACE INTO employee_liabilities (employee_id, nature_of_liability, creditor_name, outstanding_balance) VALUES
(10001, 'Personal Loan', 'Bank', 23010.00),
(10002, 'Personal Loan', 'Bank', 133948.00),
(10003, 'Personal Loan', 'Bank', 50348.00),
(10004, 'Personal Loan', 'Bank', 142633.00),
(999, 'Personal Loan', 'Bank', 87500.00);

REPLACE INTO employee_references (employee_id, reference_name, reference_address, reference_telephone) VALUES
(10001, 'Reference Arthur Dela Cruz', 'Quezon Province', '09204119515'),
(10002, 'Reference Paul Evangelista', 'Quezon Province', '09206236326'),
(10003, 'Reference Ricardo Cruz', 'Quezon Province', '09202801733'),
(10004, 'Reference Paul Pascual', 'Quezon Province', '09204112748'),
(999    , 'Reference Maria Santos', 'Quezon Province', '09201358924');

SET FOREIGN_KEY_CHECKS = 1;
