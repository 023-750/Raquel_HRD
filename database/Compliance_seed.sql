-- Mockup Employee Seeds for Compliance Department
USE raquel_hris;
SET FOREIGN_KEY_CHECKS = 0;

-- ====================================
-- 1. EMPLOYEES (Compliance Team)
-- ====================================
REPLACE INTO employees (employee_id, employee_code, first_name, last_name, middle_name, hire_date, date_of_birth, place_of_birth, gender, civil_status, job_title_id, job_title, department_id, rank_category_id, branch_id, employment_status, employment_type, profile_picture) VALUES
(4001, 'COMP-001', 'Robert', 'Garcia', 'Del Rosario', '2020-01-16', '1995-06-04', 'Lucena City, Quezon', 'Male', 'Single', 400, 'Compliance Supervisor I', 4, 4, 102, 'Regular', 'Full-time', 'avatar_m.jpg'),
(4002, 'COMP-002', 'Emilio', 'Bautista', 'Ocampo', '2020-04-11', '1997-12-11', 'Lucena City, Quezon', 'Male', 'Separated', 401, 'Compliance Supervisor II', 4, 4, 102, 'Regular', 'Full-time', 'avatar_m.jpg'),
(4003, 'COMP-003', 'Michelle', 'Gonzales', 'Torres', '2020-11-28', '1998-05-03', 'Lucena City, Quezon', 'Female', 'Single', 402, 'Compliance Supervisor III', 4, 4, 102, 'Regular', 'Full-time', 'avatar_f.jpg'),
(4004, 'COMP-004', 'Rosario', 'Torres', 'Gomez', '2023-02-18', '2005-02-11', 'Lucena City, Quezon', 'Female', 'Separated', 403, 'Compliance Staff I', 4, 5, 102, 'Regular', 'Full-time', 'avatar_f.jpg'),
(4005, 'COMP-005', 'Antonio', 'Mendoza', 'Reyes', '2025-12-17', '2001-09-27', 'Lucena City, Quezon', 'Male', 'Married', 404, 'Compliance Staff II', 4, 5, 102, 'Regular', 'Full-time', 'avatar_m.jpg'),
(4006, 'COMP-006', 'Kenneth', 'Aquino', 'Cruz', '2023-12-28', '1995-09-04', 'Lucena City, Quezon', 'Male', 'Married', 405, 'Compliance Staff III', 4, 5, 102, 'Regular', 'Full-time', 'avatar_m.jpg');

REPLACE INTO employee_contacts (employee_id, personal_email, mobile_number, telephone_number) VALUES
(4001, 'robert.garcia@example.com', '09178225192', '888-4001'),
(4002, 'emilio.bautista@example.com', '09173820798', '888-4002'),
(4003, 'michelle.gonzales@example.com', '09172514923', '888-4003'),
(4004, 'rosario.torres@example.com', '09177485471', '888-4004'),
(4005, 'antonio.mendoza@example.com', '09171633297', '888-4005'),
(4006, 'kenneth.aquino@example.com', '09179655523', '888-4006');

REPLACE INTO employee_details (employee_id, height_m, weight_kg, blood_type, citizenship) VALUES
(4001, 1.57, 60.9, 'A-', 'Filipino'),
(4002, 1.54, 69.5, 'O-', 'Filipino'),
(4003, 1.71, 65.2, 'AB+', 'Filipino'),
(4004, 1.61, 72.6, 'A-', 'Filipino'),
(4005, 1.73, 77.8, 'O-', 'Filipino'),
(4006, 1.78, 57.7, 'A+', 'Filipino');

REPLACE INTO employee_family (employee_id, member_type, surname, first_name, middle_name, occupation) VALUES
(4001, 'Father', 'Fernandez', 'Michael', 'Salvador', 'Retired'),
(4001, 'Mother', 'Evangelista', 'Aurora', 'Gonzales', 'Homemaker'),
(4002, 'Father', 'Santiago', 'Joseph', 'Gonzales', 'Retired'),
(4002, 'Mother', 'Tolentino', 'Aurora', 'Ocampo', 'Homemaker'),
(4003, 'Father', 'Pascual', 'Ronald', 'Cruz', 'Retired'),
(4003, 'Mother', 'Valenzuela', 'Teresa', 'Soriano', 'Homemaker'),
(4004, 'Father', 'Sarmiento', 'Anthony', 'Ocampo', 'Retired'),
(4004, 'Mother', 'Reyes', 'Grace', 'Soriano', 'Homemaker'),
(4005, 'Father', 'Sarmiento', 'Antonio', 'Perez', 'Retired'),
(4005, 'Mother', 'Soriano', 'Elizabeth', 'Gomez', 'Homemaker'),
(4005, 'Spouse', 'Castillo', 'Leonora', 'Aquino', 'Office Employee'),
(4006, 'Father', 'Gonzales', 'Angelo', 'Ocampo', 'Retired'),
(4006, 'Mother', 'Soriano', 'Christina', 'Rivera', 'Homemaker'),
(4006, 'Spouse', 'Valenzuela', 'Elena', 'Villanueva', 'Office Employee');

REPLACE INTO employee_education (employee_id, education_level, school_name, degree_course, year_graduated) VALUES
(4001, 'College', 'Ateneo de Manila University', 'BS Accountancy', '2016'),
(4002, 'College', 'University of Santo Tomas', 'BS Business Administration', '2018'),
(4003, 'College', 'University of Santo Tomas', 'BS Hotel and Restaurant Management', '2019'),
(4004, 'College', 'De La Salle University', 'BS Hotel and Restaurant Management', '2026'),
(4005, 'College', 'Pamantasan ng Lungsod ng Maynila', 'BS Psychology', '2022'),
(4006, 'College', 'Pamantasan ng Lungsod ng Maynila', 'BS Hotel and Restaurant Management', '2016');

REPLACE INTO employee_work_experience (employee_id, date_from, date_to, job_title, company_name, monthly_salary) VALUES
(4001, '2016-01-15', '2019-12-15', 'Previous I Role', 'BPO Solutions Inc.', 32294),
(4002, '2016-01-15', '2019-12-15', 'Previous II Role', 'Global Retail Corp.', 26387),
(4003, '2016-01-15', '2019-12-15', 'Previous III Role', 'United Services Group', 34428),
(4004, '2019-01-15', '2022-12-15', 'Previous I Role', 'Summit Property Management', 39497),
(4005, '2021-01-15', '2024-12-15', 'Previous II Role', 'Pacific Marketing Group', 29937),
(4006, '2019-01-15', '2022-12-15', 'Previous III Role', 'BPO Solutions Inc.', 31595);

REPLACE INTO employee_trainings (employee_id, training_title, conducted_by, no_of_hours) VALUES
(4001, 'Advanced Management & Leadership', 'Corporate Training Dept', 16.0),
(4002, 'IT Infrastructure and Security', 'Corporate Training Dept', 16.0),
(4003, 'ISO 9001:2015 Quality Management', 'Corporate Training Dept', 16.0),
(4004, 'Occupational Safety and Health', 'Corporate Training Dept', 16.0),
(4005, 'IT Infrastructure and Security', 'Corporate Training Dept', 16.0),
(4006, 'Advanced Management & Leadership', 'Corporate Training Dept', 16.0);

REPLACE INTO employee_disclosures (employee_id, is_related_to_company, has_admin_offense, has_criminal_charge) VALUES
(4001, 0, 0, 0),
(4002, 0, 0, 0),
(4003, 0, 0, 0),
(4004, 0, 0, 0),
(4005, 0, 0, 0),
(4006, 0, 0, 0);

REPLACE INTO employee_government_ids (employee_id, sss_number, philhealth_number, pagibig_number, tin_number) VALUES
(4001, '68-2306317-5', '83-560940492-9', '7626-7843-5743', '217-514-121-000'),
(4002, '32-3564729-1', '32-929839316-7', '8601-8354-6295', '984-742-423-000'),
(4003, '17-8558523-1', '53-866990485-1', '9266-3827-1641', '353-824-548-000'),
(4004, '26-2421349-4', '81-504456327-5', '3093-9624-2531', '761-786-533-000'),
(4005, '66-5780297-7', '39-673390109-3', '6070-8684-4178', '476-794-684-000'),
(4006, '60-9422281-5', '40-514642708-1', '7142-4678-1461', '426-201-959-000');

REPLACE INTO employee_addresses (employee_id, address_type, region, barangay, city, province) VALUES
(4001, 'Residential', 'Region IV-A (CALABARZON)', 'Barangay 6', 'Tayabas City', 'Quezon'),
(4001, 'Permanent', 'Region IV-A (CALABARZON)', 'Barangay 6', 'Tayabas City', 'Quezon'),
(4002, 'Residential', 'Region IV-A (CALABARZON)', 'Barangay 3', 'Candelaria', 'Quezon'),
(4002, 'Permanent', 'Region IV-A (CALABARZON)', 'Barangay 3', 'Candelaria', 'Quezon'),
(4003, 'Residential', 'Region IV-A (CALABARZON)', 'Barangay 8', 'Pagbilao', 'Quezon'),
(4003, 'Permanent', 'Region IV-A (CALABARZON)', 'Barangay 8', 'Pagbilao', 'Quezon'),
(4004, 'Residential', 'Region IV-A (CALABARZON)', 'Barangay 9', 'Sariaya', 'Quezon'),
(4004, 'Permanent', 'Region IV-A (CALABARZON)', 'Barangay 9', 'Sariaya', 'Quezon'),
(4005, 'Residential', 'Region IV-A (CALABARZON)', 'Barangay 8', 'Candelaria', 'Quezon'),
(4005, 'Permanent', 'Region IV-A (CALABARZON)', 'Barangay 8', 'Candelaria', 'Quezon'),
(4006, 'Residential', 'Region IV-A (CALABARZON)', 'Barangay 12', 'Sariaya', 'Quezon'),
(4006, 'Permanent', 'Region IV-A (CALABARZON)', 'Barangay 12', 'Sariaya', 'Quezon');

REPLACE INTO employee_emergency_contacts (employee_id, contact_name, relationship, contact_number) VALUES
(4001, 'Michael Fernandez', 'Father', '09188719145'),
(4002, 'Joseph Santiago', 'Father', '09182145653'),
(4003, 'Ronald Pascual', 'Father', '09189770256'),
(4004, 'Anthony Sarmiento', 'Father', '09181305857'),
(4005, 'Antonio Sarmiento', 'Father', '09185727345'),
(4006, 'Angelo Gonzales', 'Father', '09183450300');

REPLACE INTO employee_real_properties (employee_id, description, kind, acquisition_cost) VALUES
(4001, 'Residential House and Lot', 'Building and Land', 3244884.00),
(4002, 'Residential House and Lot', 'Building and Land', 2483398.00),
(4003, 'Residential House and Lot', 'Building and Land', 2778854.00),
(4004, 'Residential House and Lot', 'Building and Land', 2260422.00),
(4005, 'Residential House and Lot', 'Building and Land', 3131844.00),
(4006, 'Residential House and Lot', 'Building and Land', 1788567.00);

REPLACE INTO employee_personal_properties (employee_id, description, acquisition_cost) VALUES
(4001, 'Personal Effects and Savings', 461554.00),
(4002, 'Personal Effects and Savings', 331844.00),
(4003, 'Personal Effects and Savings', 183228.00),
(4004, 'Personal Effects and Savings', 261934.00),
(4005, 'Personal Effects and Savings', 300211.00),
(4006, 'Personal Effects and Savings', 120081.00);

REPLACE INTO employee_liabilities (employee_id, nature_of_liability, creditor_name, outstanding_balance) VALUES
(4001, 'Personal Loan', 'Bank', 104857.00),
(4002, 'Personal Loan', 'Bank', 89382.00),
(4003, 'Personal Loan', 'Bank', 105393.00),
(4004, 'Personal Loan', 'Bank', 57254.00),
(4005, 'Personal Loan', 'Bank', 141833.00),
(4006, 'Personal Loan', 'Bank', 85203.00);

REPLACE INTO employee_references (employee_id, reference_name, reference_address, reference_telephone) VALUES
(4001, 'Reference Manuel Lopez', 'Quezon Province', '09202776088'),
(4002, 'Reference Ronald Aquino', 'Quezon Province', '09201942411'),
(4003, 'Reference Mark Valenzuela', 'Quezon Province', '09205746166'),
(4004, 'Reference Robert Torres', 'Quezon Province', '09209158272'),
(4005, 'Reference Joseph Lopez', 'Quezon Province', '09203718884'),
(4006, 'Reference Anthony Soriano', 'Quezon Province', '09208926478');

SET FOREIGN_KEY_CHECKS = 1;