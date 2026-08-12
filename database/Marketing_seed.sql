-- Mockup Employee Seeds for Marketing Department
USE raquel_hris;
SET FOREIGN_KEY_CHECKS = 0;

-- ====================================
-- 1. EMPLOYEES (Marketing Team)
-- ====================================
REPLACE INTO employees (employee_id, employee_code, first_name, last_name, middle_name, hire_date, date_of_birth, place_of_birth, gender, civil_status, job_title_id, job_title, department_id, rank_category_id, branch_id, employment_status, employment_type, profile_picture) VALUES
(9001, 'MKT-001', 'Manuel', 'Valenzuela', 'Reyes', '2020-06-12', '1977-09-19', 'Lucena City, Quezon', 'Male', 'Single', 900, 'Marketing Manager I', 9, 3, 102, 'Regular', 'Full-time', 'avatar_m.jpg'),
(9002, 'MKT-002', 'Rose', 'Tolentino', 'Villanueva', '2018-09-01', '1987-06-17', 'Lucena City, Quezon', 'Female', 'Single', 901, 'Marketing Manager II', 9, 3, 102, 'Regular', 'Full-time', 'avatar_f.jpg'),
(9003, 'MKT-003', 'Mark', 'Sarmiento', 'Castro', '2021-09-09', '1984-03-25', 'Lucena City, Quezon', 'Male', 'Single', 902, 'Marketing Supervisor I', 9, 4, 102, 'Regular', 'Full-time', 'avatar_m.jpg'),
(9004, 'MKT-004', 'Francis', 'Santiago', 'Rivera', '2021-03-19', '1988-05-15', 'Lucena City, Quezon', 'Male', 'Separated', 903, 'Marketing Supervisor II', 9, 4, 102, 'Regular', 'Full-time', 'avatar_m.jpg'),
(9005, 'MKT-005', 'Teresa', 'Pascual', 'Castro', '2023-05-12', '2001-09-18', 'Lucena City, Quezon', 'Female', 'Single', 904, 'Marketing Staff on Probation', 9, 5, 102, 'Probationary', 'Full-time', 'avatar_f.jpg'),
(9006, 'MKT-006', 'Gloria', 'Tolentino', 'Perez', '2023-09-15', '2001-12-19', 'Lucena City, Quezon', 'Female', 'Widowed', 905, 'Marketing Staff I', 9, 5, 102, 'Regular', 'Full-time', 'avatar_f.jpg'),
(9007, 'MKT-007', 'Juan', 'Santos', 'Gonzales', '2024-09-15', '2000-09-09', 'Lucena City, Quezon', 'Male', 'Married', 906, 'Marketing Staff II', 9, 5, 102, 'Regular', 'Full-time', 'avatar_m.jpg');

REPLACE INTO employee_contacts (employee_id, personal_email, mobile_number, telephone_number) VALUES
(9001, 'manuel.valenzuela@example.com', '09175639031', '888-9001'),
(9002, 'rose.tolentino@example.com', '09175418274', '888-9002'),
(9003, 'mark.sarmiento@example.com', '09177675310', '888-9003'),
(9004, 'francis.santiago@example.com', '09173200150', '888-9004'),
(9005, 'teresa.pascual@example.com', '09171332781', '888-9005'),
(9006, 'gloria.tolentino@example.com', '09177945583', '888-9006'),
(9007, 'juan.santos@example.com', '09173266851', '888-9007');

REPLACE INTO employee_details (employee_id, height_m, weight_kg, blood_type, citizenship) VALUES
(9001, 1.59, 80.7, 'AB+', 'Filipino'),
(9002, 1.6, 51.2, 'A-', 'Filipino'),
(9003, 1.59, 67.6, 'O+', 'Filipino'),
(9004, 1.82, 71.3, 'O+', 'Filipino'),
(9005, 1.65, 59.9, 'A-', 'Filipino'),
(9006, 1.57, 77.0, 'A+', 'Filipino'),
(9007, 1.82, 71.7, 'A+', 'Filipino');

REPLACE INTO employee_family (employee_id, member_type, surname, first_name, middle_name, occupation) VALUES
(9001, 'Father', 'Bautista', 'Anthony', 'Soriano', 'Retired'),
(9001, 'Mother', 'Aquino', 'Maria', 'Aquino', 'Homemaker'),
(9002, 'Father', 'Soriano', 'Eduardo', 'Castro', 'Retired'),
(9002, 'Mother', 'Cruz', 'Elena', 'Aquino', 'Homemaker'),
(9003, 'Father', 'Villanueva', 'Francis', 'Salvador', 'Retired'),
(9003, 'Mother', 'Fernandez', 'Ana', 'Gonzales', 'Homemaker'),
(9004, 'Father', 'Ramos', 'Ronald', 'Salvador', 'Retired'),
(9004, 'Mother', 'Gomez', 'Bernadette', 'Perez', 'Homemaker'),
(9005, 'Father', 'Santiago', 'Santiago', 'Soriano', 'Retired'),
(9005, 'Mother', 'Tolentino', 'Divina', 'Salvador', 'Homemaker'),
(9006, 'Father', 'Ramos', 'Eduardo', 'Garcia', 'Retired'),
(9006, 'Mother', 'De Leon', 'Cecilia', 'Reyes', 'Homemaker'),
(9007, 'Father', 'Sarmiento', 'Emilio', 'Torres', 'Retired'),
(9007, 'Mother', 'Pascual', 'Lourdes', 'Soriano', 'Homemaker'),
(9007, 'Spouse', 'Fernandez', 'Bernadette', 'Salvador', 'Office Employee');

REPLACE INTO employee_education (employee_id, education_level, school_name, degree_course, year_graduated) VALUES
(9001, 'College', 'Mapua University', 'BS Finance', '1998'),
(9002, 'College', 'Pamantasan ng Lungsod ng Maynila', 'BS Hotel and Restaurant Management', '2008'),
(9003, 'College', 'Mapua University', 'BS Information Technology', '2005'),
(9004, 'College', 'University of Santo Tomas', 'BS Information Technology', '2009'),
(9005, 'College', 'De La Salle University', 'BS Civil Engineering', '2022'),
(9006, 'College', 'University of Santo Tomas', 'BS Information Technology', '2022'),
(9007, 'College', 'Polytechnic University of the Philippines', 'BS Hotel and Restaurant Management', '2021');

REPLACE INTO employee_work_experience (employee_id, date_from, date_to, job_title, company_name, monthly_salary) VALUES
(9001, '2016-01-15', '2019-12-15', 'Previous I Role', 'Prime Logistics Co.', 36305),
(9002, '2014-01-15', '2017-12-15', 'Previous II Role', 'Metro Finance and Accounting', 25161),
(9003, '2017-01-15', '2020-12-15', 'Previous I Role', 'United Services Group', 21944),
(9004, '2017-01-15', '2020-12-15', 'Previous II Role', 'Global Retail Corp.', 33498),
(9005, '2019-01-15', '2022-12-15', 'Previous Probation Role', 'Metro Finance and Accounting', 28652),
(9006, '2019-01-15', '2022-12-15', 'Previous I Role', 'Pacific Marketing Group', 21375),
(9007, '2020-01-15', '2023-12-15', 'Previous II Role', 'United Services Group', 35683);

REPLACE INTO employee_trainings (employee_id, training_title, conducted_by, no_of_hours) VALUES
(9001, 'Professional Ethics in Workplace', 'Corporate Training Dept', 16.0),
(9002, 'Financial Management and Tax Audits', 'Corporate Training Dept', 16.0),
(9003, 'Strategic HR & Talent Development', 'Corporate Training Dept', 16.0),
(9004, 'Advanced Management & Leadership', 'Corporate Training Dept', 16.0),
(9005, 'Strategic HR & Talent Development', 'Corporate Training Dept', 16.0),
(9006, 'Professional Ethics in Workplace', 'Corporate Training Dept', 16.0),
(9007, 'IT Infrastructure and Security', 'Corporate Training Dept', 16.0);

REPLACE INTO employee_disclosures (employee_id, is_related_to_company, has_admin_offense, has_criminal_charge) VALUES
(9001, 0, 0, 0),
(9002, 0, 0, 0),
(9003, 0, 0, 0),
(9004, 0, 0, 0),
(9005, 0, 0, 0),
(9006, 0, 0, 0),
(9007, 0, 0, 0);

REPLACE INTO employee_government_ids (employee_id, sss_number, philhealth_number, pagibig_number, tin_number) VALUES
(9001, '74-5936726-5', '24-962152669-8', '5222-9813-5098', '810-553-486-000'),
(9002, '33-4190481-7', '35-352180476-3', '5348-4387-4755', '307-954-355-000'),
(9003, '50-1141240-0', '96-326293650-6', '2650-6331-6924', '727-982-365-000'),
(9004, '32-8071943-7', '27-777157408-8', '9748-6172-8972', '407-356-675-000'),
(9005, '67-2315459-6', '88-407652347-7', '3373-3745-6527', '531-746-727-000'),
(9006, '18-8938740-8', '16-128309105-6', '9584-2363-2702', '368-732-259-000'),
(9007, '21-8413185-4', '56-542600470-4', '3551-5973-1172', '100-635-934-000');

REPLACE INTO employee_addresses (employee_id, address_type, region, barangay, city, province) VALUES
(9001, 'Residential', 'Region IV-A (CALABARZON)', 'Barangay 10', 'Candelaria', 'Quezon'),
(9001, 'Permanent', 'Region IV-A (CALABARZON)', 'Barangay 10', 'Candelaria', 'Quezon'),
(9002, 'Residential', 'Region IV-A (CALABARZON)', 'Barangay 14', 'Pagbilao', 'Quezon'),
(9002, 'Permanent', 'Region IV-A (CALABARZON)', 'Barangay 14', 'Pagbilao', 'Quezon'),
(9003, 'Residential', 'Region IV-A (CALABARZON)', 'Barangay 3', 'Pagbilao', 'Quezon'),
(9003, 'Permanent', 'Region IV-A (CALABARZON)', 'Barangay 3', 'Pagbilao', 'Quezon'),
(9004, 'Residential', 'Region IV-A (CALABARZON)', 'Barangay 14', 'Pagbilao', 'Quezon'),
(9004, 'Permanent', 'Region IV-A (CALABARZON)', 'Barangay 14', 'Pagbilao', 'Quezon'),
(9005, 'Residential', 'Region IV-A (CALABARZON)', 'Barangay 10', 'Candelaria', 'Quezon'),
(9005, 'Permanent', 'Region IV-A (CALABARZON)', 'Barangay 10', 'Candelaria', 'Quezon'),
(9006, 'Residential', 'Region IV-A (CALABARZON)', 'Barangay 2', 'Candelaria', 'Quezon'),
(9006, 'Permanent', 'Region IV-A (CALABARZON)', 'Barangay 2', 'Candelaria', 'Quezon'),
(9007, 'Residential', 'Region IV-A (CALABARZON)', 'Barangay 13', 'Tayabas City', 'Quezon'),
(9007, 'Permanent', 'Region IV-A (CALABARZON)', 'Barangay 13', 'Tayabas City', 'Quezon');

REPLACE INTO employee_emergency_contacts (employee_id, contact_name, relationship, contact_number) VALUES
(9001, 'Anthony Bautista', 'Father', '09185081447'),
(9002, 'Eduardo Soriano', 'Father', '09186973148'),
(9003, 'Francis Villanueva', 'Father', '09185409875'),
(9004, 'Ronald Ramos', 'Father', '09181800429'),
(9005, 'Santiago Santiago', 'Father', '09182196955'),
(9006, 'Eduardo Ramos', 'Father', '09186190378'),
(9007, 'Emilio Sarmiento', 'Father', '09186769431');

REPLACE INTO employee_real_properties (employee_id, description, kind, acquisition_cost) VALUES
(9001, 'Residential House and Lot', 'Building and Land', 3088536.00),
(9002, 'Residential House and Lot', 'Building and Land', 1827146.00),
(9003, 'Residential House and Lot', 'Building and Land', 1997316.00),
(9004, 'Residential House and Lot', 'Building and Land', 2335509.00),
(9005, 'Residential House and Lot', 'Building and Land', 1665909.00),
(9006, 'Residential House and Lot', 'Building and Land', 1980994.00),
(9007, 'Residential House and Lot', 'Building and Land', 2101384.00);

REPLACE INTO employee_personal_properties (employee_id, description, acquisition_cost) VALUES
(9001, 'Personal Effects and Savings', 278080.00),
(9002, 'Personal Effects and Savings', 155935.00),
(9003, 'Personal Effects and Savings', 241247.00),
(9004, 'Personal Effects and Savings', 151891.00),
(9005, 'Personal Effects and Savings', 184934.00),
(9006, 'Personal Effects and Savings', 229707.00),
(9007, 'Personal Effects and Savings', 345994.00);

REPLACE INTO employee_liabilities (employee_id, nature_of_liability, creditor_name, outstanding_balance) VALUES
(9001, 'Personal Loan', 'Bank', 130284.00),
(9002, 'Personal Loan', 'Bank', 41425.00),
(9003, 'Personal Loan', 'Bank', 133560.00),
(9004, 'Personal Loan', 'Bank', 52819.00),
(9005, 'Personal Loan', 'Bank', 100079.00),
(9006, 'Personal Loan', 'Bank', 84494.00),
(9007, 'Personal Loan', 'Bank', 12180.00);

REPLACE INTO employee_references (employee_id, reference_name, reference_address, reference_telephone) VALUES
(9001, 'Reference Edward Gonzales', 'Quezon Province', '09201458868'),
(9002, 'Reference Anthony Reyes', 'Quezon Province', '09209265544'),
(9003, 'Reference Santiago Torres', 'Quezon Province', '09207139188'),
(9004, 'Reference Jose Santiago', 'Quezon Province', '09205266272'),
(9005, 'Reference Michael Torres', 'Quezon Province', '09206316334'),
(9006, 'Reference David Tolentino', 'Quezon Province', '09203120836'),
(9007, 'Reference Ricardo Valenzuela', 'Quezon Province', '09209253243');

SET FOREIGN_KEY_CHECKS = 1;