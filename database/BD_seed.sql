-- Mockup Employee Seeds for Business Development Department
USE raquel_hris;
SET FOREIGN_KEY_CHECKS = 0;

-- ====================================
-- 1. EMPLOYEES (Business Development Team)
-- ====================================
REPLACE INTO employees (employee_id, employee_code, first_name, last_name, middle_name, hire_date, date_of_birth, place_of_birth, gender, civil_status, job_title_id, job_title, department_id, rank_category_id, branch_id, employment_status, employment_type, profile_picture) VALUES
(3001, 'BD-001', 'Antonio', 'Dela Cruz', 'Gonzales', '2017-05-02', '1987-01-06', 'Lucena City, Quezon', 'Male', 'Single', 300, 'Business Development Officer I', 3, 3, 102, 'Regular', 'Full-time', 'avatar_m.jpg'),
(3002, 'BD-002', 'Santiago', 'Tolentino', 'Soriano', '2023-09-10', '1997-07-20', 'Lucena City, Quezon', 'Male', 'Widowed', 301, 'Business Development Staff on Training', 3, 5, 102, 'Trainee', 'Full-time', 'avatar_m.jpg'),
(3003, 'BD-003', 'Emilio', 'Cruz', 'Salvador', '2021-11-25', '2000-07-12', 'Lucena City, Quezon', 'Male', 'Single', 302, 'Business Development Staff I', 3, 5, 102, 'Regular', 'Full-time', 'avatar_m.jpg'),
(3004, 'BD-004', 'Eduardo', 'Tolentino', 'Santos', '2025-07-15', '2004-04-13', 'Lucena City, Quezon', 'Male', 'Married', 303, 'Business Development Staff II', 3, 5, 102, 'Regular', 'Full-time', 'avatar_m.jpg');

REPLACE INTO employee_contacts (employee_id, personal_email, mobile_number, telephone_number) VALUES
(3001, 'antonio.delacruz@example.com', '09173126655', '888-3001'),
(3002, 'santiago.tolentino@example.com', '09178612081', '888-3002'),
(3003, 'emilio.cruz@example.com', '09178095702', '888-3003'),
(3004, 'eduardo.tolentino@example.com', '09176719285', '888-3004');

REPLACE INTO employee_details (employee_id, height_m, weight_kg, blood_type, citizenship) VALUES
(3001, 1.75, 65.9, 'A-', 'Filipino'),
(3002, 1.68, 62.6, 'A-', 'Filipino'),
(3003, 1.79, 72.5, 'B+', 'Filipino'),
(3004, 1.57, 58.6, 'B+', 'Filipino');

REPLACE INTO employee_family (employee_id, member_type, surname, first_name, middle_name, occupation) VALUES
(3001, 'Father', 'Villanueva', 'Manuel', 'Torres', 'Retired'),
(3001, 'Mother', 'Flores', 'Rosario', 'Ocampo', 'Homemaker'),
(3002, 'Father', 'Gomez', 'Danilo', 'Salvador', 'Retired'),
(3002, 'Mother', 'Gomez', 'Cecilia', 'Del Rosario', 'Homemaker'),
(3003, 'Father', 'Garcia', 'Santiago', 'Gonzales', 'Retired'),
(3003, 'Mother', 'Madrigal', 'Patricia', 'Ocampo', 'Homemaker'),
(3004, 'Father', 'Madrigal', 'George', 'Gomez', 'Retired'),
(3004, 'Mother', 'Castillo', 'Elena', 'Cruz', 'Homemaker'),
(3004, 'Spouse', 'Perez', 'Christina', 'Gomez', 'Office Employee');

REPLACE INTO employee_education (employee_id, education_level, school_name, degree_course, year_graduated) VALUES
(3001, 'College', 'Holy Angel University', 'BS Business Administration', '2008'),
(3002, 'College', 'Mapua University', 'AB Communication', '2018'),
(3003, 'College', 'University of Santo Tomas', 'BS Information Technology', '2021'),
(3004, 'College', 'University of the Philippines', 'BS Business Administration', '2025');

REPLACE INTO employee_work_experience (employee_id, date_from, date_to, job_title, company_name, monthly_salary) VALUES
(3001, '2013-01-15', '2016-12-15', 'Previous I Role', 'United Services Group', 34509),
(3002, '2019-01-15', '2022-12-15', 'Previous Training Role', 'Secure Tech Philippines', 31559),
(3003, '2017-01-15', '2020-12-15', 'Previous I Role', 'United Services Group', 25348),
(3004, '2021-01-15', '2024-12-15', 'Previous II Role', 'Summit Property Management', 32519);

REPLACE INTO employee_trainings (employee_id, training_title, conducted_by, no_of_hours) VALUES
(3001, 'IT Infrastructure and Security', 'Corporate Training Dept', 16.0),
(3002, 'Customer Service Excellence', 'Corporate Training Dept', 16.0),
(3003, 'Professional Ethics in Workplace', 'Corporate Training Dept', 16.0),
(3004, 'Occupational Safety and Health', 'Corporate Training Dept', 16.0);

REPLACE INTO employee_disclosures (employee_id, is_related_to_company, has_admin_offense, has_criminal_charge) VALUES
(3001, 0, 0, 0),
(3002, 0, 0, 0),
(3003, 0, 0, 0),
(3004, 0, 0, 0);

REPLACE INTO employee_government_ids (employee_id, sss_number, philhealth_number, pagibig_number, tin_number) VALUES
(3001, '88-1727544-8', '48-591819409-0', '1996-8847-7580', '536-802-210-000'),
(3002, '68-7689582-6', '22-435596237-6', '6120-5176-7132', '256-803-585-000'),
(3003, '54-7166651-1', '45-716435189-3', '8030-1430-5381', '129-284-379-000'),
(3004, '72-8416270-6', '44-331465414-8', '2864-6655-8043', '213-390-794-000');

REPLACE INTO employee_addresses (employee_id, address_type, region, barangay, city, province) VALUES
(3001, 'Residential', 'Region IV-A (CALABARZON)', 'Barangay 8', 'Candelaria', 'Quezon'),
(3001, 'Permanent', 'Region IV-A (CALABARZON)', 'Barangay 8', 'Candelaria', 'Quezon'),
(3002, 'Residential', 'Region IV-A (CALABARZON)', 'Barangay 2', 'Lucena City', 'Quezon'),
(3002, 'Permanent', 'Region IV-A (CALABARZON)', 'Barangay 2', 'Lucena City', 'Quezon'),
(3003, 'Residential', 'Region IV-A (CALABARZON)', 'Barangay 12', 'Sariaya', 'Quezon'),
(3003, 'Permanent', 'Region IV-A (CALABARZON)', 'Barangay 12', 'Sariaya', 'Quezon'),
(3004, 'Residential', 'Region IV-A (CALABARZON)', 'Barangay 11', 'Pagbilao', 'Quezon'),
(3004, 'Permanent', 'Region IV-A (CALABARZON)', 'Barangay 11', 'Pagbilao', 'Quezon');

REPLACE INTO employee_emergency_contacts (employee_id, contact_name, relationship, contact_number) VALUES
(3001, 'Manuel Villanueva', 'Father', '09182232969'),
(3002, 'Danilo Gomez', 'Father', '09182432176'),
(3003, 'Santiago Garcia', 'Father', '09186701311'),
(3004, 'George Madrigal', 'Father', '09189164342');

REPLACE INTO employee_real_properties (employee_id, description, kind, acquisition_cost) VALUES
(3001, 'Residential House and Lot', 'Building and Land', 3385899.00),
(3002, 'Residential House and Lot', 'Building and Land', 1695517.00),
(3003, 'Residential House and Lot', 'Building and Land', 2236149.00),
(3004, 'Residential House and Lot', 'Building and Land', 2605060.00);

REPLACE INTO employee_personal_properties (employee_id, description, acquisition_cost) VALUES
(3001, 'Personal Effects and Savings', 142357.00),
(3002, 'Personal Effects and Savings', 326427.00),
(3003, 'Personal Effects and Savings', 103201.00),
(3004, 'Personal Effects and Savings', 449769.00);

REPLACE INTO employee_liabilities (employee_id, nature_of_liability, creditor_name, outstanding_balance) VALUES
(3001, 'Personal Loan', 'Bank', 94439.00),
(3002, 'Personal Loan', 'Bank', 35313.00),
(3003, 'Personal Loan', 'Bank', 57554.00),
(3004, 'Personal Loan', 'Bank', 90850.00);

REPLACE INTO employee_references (employee_id, reference_name, reference_address, reference_telephone) VALUES
(3001, 'Reference Santiago Mendoza', 'Quezon Province', '09202101965'),
(3002, 'Reference Eduardo Pascual', 'Quezon Province', '09207251976'),
(3003, 'Reference Kenneth Mendoza', 'Quezon Province', '09207724045'),
(3004, 'Reference Jose Ramos', 'Quezon Province', '09207631419');

SET FOREIGN_KEY_CHECKS = 1;