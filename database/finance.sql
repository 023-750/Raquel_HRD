-- ============================================
-- Seed: Finance Department Employees
-- Branch: 102 (Raquel Pawnshop Main Office)
--
-- Employee IDs : 1039-1056 | Codes: FN-001 to FN-018
-- Department ID: 5
-- Sections     : VP for Finance, Accounting, Treasury
-- ============================================

USE raquel_hris;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- CLEANUP: Remove previous incorrect seed
-- ============================================
DELETE FROM users      WHERE employee_id IN (1039,1040,1041,1042,1043,1044);
DELETE FROM employees  WHERE employee_id IN (1039,1040,1041,1042,1043,1044);

-- ============================================
-- 1. EMPLOYEES (Finance)
-- ============================================
REPLACE INTO employees (
    employee_id, employee_code,
    first_name, last_name, middle_name,
    hire_date, date_of_birth, place_of_birth,
    gender, civil_status,
    job_title_id, job_title,
    department_id, rank_category_id, branch_id,
    employment_status, employment_type
) VALUES
-- === EXECUTIVE ===
(1039, 'FN-001', 'Cristina', 'Bautista', 'Mercado',
 '2010-03-01', '1975-06-14', 'Lucena City, Quezon',
 'Female', 'Married',
 500, 'VP for Finance',
 5, 1, 102, 'Regular', 'Full-time'),

-- === ACCOUNTING SUPERVISORS ===
(1040, 'FN-002', 'Roberto', 'Dela Cruz', 'Santos',
 '2013-08-12', '1981-03-22', 'Candelaria, Quezon',
 'Male', 'Married',
 501, 'Accounting Supervisor I',
 5, 4, 102, 'Regular', 'Full-time'),

(1041, 'FN-003', 'Marianne', 'Esguerra', 'Ramos',
 '2015-05-20', '1985-09-05', 'Tiaong, Quezon',
 'Female', 'Married',
 502, 'Accounting Supervisor II',
 5, 4, 102, 'Regular', 'Full-time'),

(1042, 'FN-004', 'Eduardo', 'Navarro', 'Lim',
 '2017-02-14', '1988-12-30', 'Sariaya, Quezon',
 'Male', 'Single',
 503, 'Accounting Supervisor III',
 5, 4, 102, 'Regular', 'Full-time'),

(1043, 'FN-005', 'Grace', 'Manalo', 'Villanueva',
 '2019-07-01', '1990-07-17', 'Lucban, Quezon',
 'Female', 'Single',
 504, 'Accounting Supervisor IV',
 5, 4, 102, 'Regular', 'Full-time'),

-- === TREASURY SUPERVISORS ===
(1044, 'FN-006', 'Renato', 'Castillo', 'Torres',
 '2012-11-05', '1979-04-08', 'Tayabas City, Quezon',
 'Male', 'Married',
 505, 'Treasury Supervisor I',
 5, 4, 102, 'Regular', 'Full-time'),

(1045, 'FN-007', 'Leonora', 'Pascual', 'Aquino',
 '2016-04-18', '1983-08-21', 'Lucena City, Quezon',
 'Female', 'Married',
 506, 'Treasury Supervisor II',
 5, 4, 102, 'Regular', 'Full-time'),

(1046, 'FN-008', 'Alfredo', 'Mendoza', 'Reyes',
 '2018-01-09', '1987-01-15', 'Candelaria, Quezon',
 'Male', 'Married',
 507, 'Treasury Supervisor III',
 5, 4, 102, 'Regular', 'Full-time'),

(1047, 'FN-009', 'Joanna', 'Salazar', 'Cruz',
 '2020-06-22', '1992-05-03', 'Tiaong, Quezon',
 'Female', 'Single',
 508, 'Treasury Supervisor IV',
 5, 4, 102, 'Regular', 'Full-time'),

(1048, 'FN-010', 'Bernard', 'Flores', 'Garcia',
 '2021-03-15', '1994-11-28', 'Sariaya, Quezon',
 'Male', 'Single',
 509, 'Treasury Supervisor V',
 5, 4, 102, 'Regular', 'Full-time'),

-- === ACCOUNTING STAFF (R&F) ===
(1049, 'FN-011', 'Analiza', 'Ocampo', 'Santos',
 '2023-06-01', '2000-02-10', 'Lucena City, Quezon',
 'Female', 'Single',
 510, 'Accounting Staff on Probation',
 5, 5, 102, 'Probationary', 'Full-time'),

(1050, 'FN-012', 'Danilo', 'Reyes', 'Ocampo',
 '2021-11-08', '1996-05-30', 'Sariaya, Quezon',
 'Male', 'Single',
 511, 'Accounting Staff I',
 5, 5, 102, 'Regular', 'Full-time'),

(1051, 'FN-013', 'Lorraine', 'Villanueva', 'Garcia',
 '2020-03-14', '1997-09-12', 'Lucban, Quezon',
 'Female', 'Single',
 512, 'Accounting Staff II',
 5, 5, 102, 'Regular', 'Full-time'),

(1052, 'FN-014', 'Mark', 'Tolentino', 'Navarro',
 '2019-08-19', '1995-02-18', 'Tayabas City, Quezon',
 'Male', 'Single',
 513, 'Accounting Staff III',
 5, 5, 102, 'Regular', 'Full-time'),

(1053, 'FN-015', 'Patricia', 'Guevarra', 'Dela Rosa',
 '2018-05-07', '1993-07-25', 'Lucena City, Quezon',
 'Female', 'Single',
 514, 'Accounting Staff IV',
 5, 5, 102, 'Regular', 'Full-time'),

(1054, 'FN-016', 'Jerome', 'Hernandez', 'Bautista',
 '2016-10-11', '1991-03-14', 'Candelaria, Quezon',
 'Male', 'Married',
 515, 'Accounting Staff V',
 5, 5, 102, 'Regular', 'Full-time'),

-- === TREASURY STAFF (R&F) ===
(1055, 'FN-017', 'Sheila', 'Macaraeg', 'Lopez',
 '2022-07-04', '1998-12-01', 'Sariaya, Quezon',
 'Female', 'Single',
 516, 'Treasury Staff I',
 5, 5, 102, 'Regular', 'Full-time'),

(1056, 'FN-018', 'Carlo', 'Paglinawan', 'Ramos',
 '2023-02-27', '2001-06-20', 'Lucena City, Quezon',
 'Male', 'Single',
 517, 'Treasury Staff II',
 5, 5, 102, 'Regular', 'Full-time');

-- ============================================
-- 2. REPORTS_TO CHAIN
-- All report to VP for Finance (1039)
-- Accounting Staff report to Accounting Supervisor I (1040)
-- Treasury Staff report to Treasury Supervisor I (1044)
-- ============================================
UPDATE employees SET reports_to = 1039 WHERE employee_id IN (1040,1041,1042,1043,1044,1045,1046,1047,1048);
UPDATE employees SET reports_to = 1040 WHERE employee_id IN (1049,1050,1051,1052,1053,1054);
UPDATE employees SET reports_to = 1044 WHERE employee_id IN (1055,1056);

-- ============================================
-- 3. EMPLOYEE CONTACTS
-- ============================================
REPLACE INTO employee_contacts (employee_id, mobile_number, personal_email) VALUES
(1039, '09170000139', 'cristina.bautista@example.com'),
(1040, '09170000140', 'roberto.delacruz@example.com'),
(1041, '09170000141', 'marianne.esguerra@example.com'),
(1042, '09170000142', 'eduardo.navarro@example.com'),
(1043, '09170000143', 'grace.manalo@example.com'),
(1044, '09170000144', 'renato.castillo@example.com'),
(1045, '09170000145', 'leonora.pascual@example.com'),
(1046, '09170000146', 'alfredo.mendoza@example.com'),
(1047, '09170000147', 'joanna.salazar@example.com'),
(1048, '09170000148', 'bernard.flores@example.com'),
(1049, '09170000149', 'analiza.ocampo@example.com'),
(1050, '09170000150', 'danilo.reyes@example.com'),
(1051, '09170000151', 'lorraine.villanueva@example.com'),
(1052, '09170000152', 'mark.tolentino@example.com'),
(1053, '09170000153', 'patricia.guevarra@example.com'),
(1054, '09170000154', 'jerome.hernandez@example.com'),
(1055, '09170000155', 'sheila.macaraeg@example.com'),
(1056, '09170000156', 'carlo.paglinawan@example.com');

-- ============================================
-- 4. EMPLOYEE DETAILS (Physical & Citizenship)
-- ============================================
REPLACE INTO employee_details (employee_id, height_m, weight_kg, blood_type, citizenship) VALUES
(1039, 1.61, 54.0, 'O+',  'Filipino'),
(1040, 1.74, 72.0, 'A+',  'Filipino'),
(1041, 1.59, 51.0, 'B+',  'Filipino'),
(1042, 1.72, 68.0, 'AB+', 'Filipino'),
(1043, 1.58, 50.0, 'O-',  'Filipino'),
(1044, 1.76, 78.0, 'A+',  'Filipino'),
(1045, 1.60, 55.0, 'B+',  'Filipino'),
(1046, 1.73, 70.0, 'O+',  'Filipino'),
(1047, 1.62, 52.0, 'AB-', 'Filipino'),
(1048, 1.70, 65.0, 'A-',  'Filipino'),
(1049, 1.57, 49.0, 'O+',  'Filipino'),
(1050, 1.71, 66.0, 'AB+', 'Filipino'),
(1051, 1.63, 53.0, 'O-',  'Filipino'),
(1052, 1.69, 67.0, 'A-',  'Filipino'),
(1053, 1.60, 52.0, 'B+',  'Filipino'),
(1054, 1.75, 74.0, 'O+',  'Filipino'),
(1055, 1.58, 50.0, 'A+',  'Filipino'),
(1056, 1.68, 63.0, 'AB+', 'Filipino');

-- ============================================
-- 5. ADDRESSES
-- ============================================
REPLACE INTO employee_addresses (employee_id, address_type, barangay, city, province, zip_code) VALUES
(1039, 'Residential', 'Ibabang Dupay', 'Lucena City',  'Quezon', '4301'),
(1039, 'Permanent',   'Ibabang Dupay', 'Lucena City',  'Quezon', '4301'),
(1040, 'Residential', 'Poblacion',     'Candelaria',   'Quezon', '4323'),
(1040, 'Permanent',   'Poblacion',     'Candelaria',   'Quezon', '4323'),
(1041, 'Residential', 'Lalig',         'Tiaong',       'Quezon', '4325'),
(1041, 'Permanent',   'Lalig',         'Tiaong',       'Quezon', '4325'),
(1042, 'Residential', 'Poblacion',     'Sariaya',      'Quezon', '4322'),
(1042, 'Permanent',   'Poblacion',     'Sariaya',      'Quezon', '4322'),
(1043, 'Residential', 'Ilayang Dupay', 'Lucena City',  'Quezon', '4301'),
(1043, 'Permanent',   'San Antonio',   'Lucban',       'Quezon', '4328'),
(1044, 'Residential', 'Sampaloc',      'Tayabas City', 'Quezon', '4327'),
(1044, 'Permanent',   'Sampaloc',      'Tayabas City', 'Quezon', '4327'),
(1045, 'Residential', 'Ibabang Dupay', 'Lucena City',  'Quezon', '4301'),
(1045, 'Permanent',   'Ibabang Dupay', 'Lucena City',  'Quezon', '4301'),
(1046, 'Residential', 'Poblacion',     'Candelaria',   'Quezon', '4323'),
(1046, 'Permanent',   'Poblacion',     'Candelaria',   'Quezon', '4323'),
(1047, 'Residential', 'Lalig',         'Tiaong',       'Quezon', '4325'),
(1047, 'Permanent',   'Lalig',         'Tiaong',       'Quezon', '4325'),
(1048, 'Residential', 'Poblacion',     'Sariaya',      'Quezon', '4322'),
(1048, 'Permanent',   'Poblacion',     'Sariaya',      'Quezon', '4322'),
(1049, 'Residential', 'San Roque',     'Lucena City',  'Quezon', '4301'),
(1049, 'Permanent',   'San Roque',     'Lucena City',  'Quezon', '4301'),
(1050, 'Residential', 'Poblacion',     'Sariaya',      'Quezon', '4322'),
(1050, 'Permanent',   'Poblacion',     'Sariaya',      'Quezon', '4322'),
(1051, 'Residential', 'Ilayang Dupay', 'Lucena City',  'Quezon', '4301'),
(1051, 'Permanent',   'San Antonio',   'Lucban',       'Quezon', '4328'),
(1052, 'Residential', 'Sampaloc',      'Tayabas City', 'Quezon', '4327'),
(1052, 'Permanent',   'Sampaloc',      'Tayabas City', 'Quezon', '4327'),
(1053, 'Residential', 'Ibabang Dupay', 'Lucena City',  'Quezon', '4301'),
(1053, 'Permanent',   'Ibabang Dupay', 'Lucena City',  'Quezon', '4301'),
(1054, 'Residential', 'Lalig',         'Tiaong',       'Quezon', '4325'),
(1054, 'Permanent',   'Lalig',         'Tiaong',       'Quezon', '4325'),
(1055, 'Residential', 'Poblacion',     'Sariaya',      'Quezon', '4322'),
(1055, 'Permanent',   'Poblacion',     'Sariaya',      'Quezon', '4322'),
(1056, 'Residential', 'Ilayang Dupay', 'Lucena City',  'Quezon', '4301'),
(1056, 'Permanent',   'Ilayang Dupay', 'Lucena City',  'Quezon', '4301');

-- ============================================
-- 6. EMERGENCY CONTACTS
-- ============================================
REPLACE INTO employee_emergency_contacts (employee_id, contact_name, relationship, contact_number) VALUES
(1039, 'Eduardo Bautista',    'Spouse', '09170001039'),
(1040, 'Lourdes Dela Cruz',   'Spouse', '09170001040'),
(1041, 'Carmelita Esguerra',  'Mother', '09170001041'),
(1042, 'Remedios Navarro',    'Mother', '09170001042'),
(1043, 'Hernando Manalo',     'Father', '09170001043'),
(1044, 'Josefina Castillo',   'Spouse', '09170001044'),
(1045, 'Ricardo Pascual',     'Spouse', '09170001045'),
(1046, 'Nelia Mendoza',       'Spouse', '09170001046'),
(1047, 'Caridad Salazar',     'Mother', '09170001047'),
(1048, 'Teresita Flores',     'Mother', '09170001048'),
(1049, 'Ligaya Ocampo',       'Mother', '09170001049'),
(1050, 'Remedios Reyes',      'Mother', '09170001050'),
(1051, 'Hernando Villanueva', 'Father', '09170001051'),
(1052, 'Josefina Tolentino',  'Mother', '09170001052'),
(1053, 'Antonio Guevarra',    'Father', '09170001053'),
(1054, 'Rowena Hernandez',    'Spouse', '09170001054'),
(1055, 'Gloria Macaraeg',     'Mother', '09170001055'),
(1056, 'Roberto Paglinawan',  'Father', '09170001056');

-- ============================================
-- 7. FAMILY BACKGROUND
-- ============================================
REPLACE INTO employee_family (employee_id, member_type, surname, first_name, middle_name, occupation) VALUES
(1039, 'Father', 'Bautista',   'Alfredo',   'Mercado',   'Retired Bank Manager'),
(1039, 'Mother', 'Bautista',   'Felicitas', 'Dela Rosa', 'Retired Teacher'),
(1039, 'Spouse', 'Bautista',   'Eduardo',   'Lim',       'Civil Engineer'),
(1040, 'Father', 'Dela Cruz',  'Ernesto',   'Santos',    'Farmer'),
(1040, 'Mother', 'Dela Cruz',  'Lourdes',   'Reyes',     'Housewife'),
(1040, 'Spouse', 'Dela Cruz',  'Marites',   'Buenaventura', 'Nurse'),
(1041, 'Father', 'Esguerra',   'Romulo',    'Ramos',     'Retired Employee'),
(1041, 'Mother', 'Esguerra',   'Carmelita', 'Valdez',    'Market Vendor'),
(1042, 'Father', 'Navarro',    'Rodrigo',   'Lim',       'Security Guard'),
(1042, 'Mother', 'Navarro',    'Remedios',  'Cruz',      'Housewife'),
(1043, 'Father', 'Manalo',     'Hernando',  'Villanueva','Carpenter'),
(1043, 'Mother', 'Manalo',     'Ligaya',    'Torres',    'Dressmaker'),
(1044, 'Father', 'Castillo',   'Ernesto',   'Torres',    'Retired Accountant'),
(1044, 'Mother', 'Castillo',   'Josefina',  'Dela Cruz', 'Housewife'),
(1044, 'Spouse', 'Castillo',   'Marilou',   'Santos',    'Teacher'),
(1045, 'Father', 'Pascual',    'Alfredo',   'Aquino',    'Farmer'),
(1045, 'Mother', 'Pascual',    'Leonida',   'Navarro',   'Market Vendor'),
(1045, 'Spouse', 'Pascual',    'Ricardo',   'Perez',     'Engineer'),
(1046, 'Father', 'Mendoza',    'Edilberto', 'Reyes',     'Retired Employee'),
(1046, 'Mother', 'Mendoza',    'Concepcion','Navarro',   'Housewife'),
(1046, 'Spouse', 'Mendoza',    'Nelia',     'Garcia',    'Nurse'),
(1047, 'Father', 'Salazar',    'Romulo',    'Cruz',      'Jeepney Driver'),
(1047, 'Mother', 'Salazar',    'Caridad',   'Valdez',    'Market Vendor'),
(1048, 'Father', 'Flores',     'Rodrigo',   'Garcia',    'Security Guard'),
(1048, 'Mother', 'Flores',     'Teresita',  'Ocampo',    'Housewife'),
(1049, 'Father', 'Ocampo',     'Rodolfo',   'Santos',    'Carpenter'),
(1049, 'Mother', 'Ocampo',     'Ligaya',    'Reyes',     'Dressmaker'),
(1050, 'Father', 'Reyes',      'Rodrigo',   'Ocampo',    'Security Guard'),
(1050, 'Mother', 'Reyes',      'Remedios',  'Cruz',      'Housewife'),
(1051, 'Father', 'Villanueva', 'Hernando',  'Garcia',    'Carpenter'),
(1051, 'Mother', 'Villanueva', 'Ligaya',    'Torres',    'Dressmaker'),
(1052, 'Father', 'Tolentino',  'Ernesto',   'Navarro',   'Electrician'),
(1052, 'Mother', 'Tolentino',  'Josefina',  'Dela Cruz', 'Teacher'),
(1053, 'Father', 'Guevarra',   'Antonio',   'Dela Rosa', 'Farmer'),
(1053, 'Mother', 'Guevarra',   'Caridad',   'Mendez',    'Housewife'),
(1054, 'Father', 'Hernandez',  'Alfredo',   'Bautista',  'Retired Employee'),
(1054, 'Mother', 'Hernandez',  'Norma',     'Lim',       'Housewife'),
(1054, 'Spouse', 'Hernandez',  'Rowena',    'Santos',    'Government Employee'),
(1055, 'Father', 'Macaraeg',   'Edilberto', 'Lopez',     'Farmer'),
(1055, 'Mother', 'Macaraeg',   'Gloria',    'Navarro',   'Housewife'),
(1056, 'Father', 'Paglinawan', 'Roberto',   'Ramos',     'Carpenter'),
(1056, 'Mother', 'Paglinawan', 'Ligaya',    'Flores',    'Market Vendor');

-- ============================================
-- 8. EDUCATIONAL BACKGROUND
-- ============================================
REPLACE INTO employee_education (employee_id, education_level, school_name, degree_course, year_graduated) VALUES
(1039, 'College',          'Manuel S. Enverga University Foundation', 'BS Accountancy',           '1997'),
(1039, 'Graduate Studies', 'Lyceum of the Philippines - Batangas',    'Master in Business Admin', '2003'),
(1040, 'College',          'Southern Luzon State University',          'BS Management Accounting', '2003'),
(1041, 'College',          'University of Perpetual Help System',      'BS Accountancy',           '2007'),
(1042, 'College',          'Manuel S. Enverga University Foundation',  'BS Accountancy',           '2010'),
(1043, 'College',          'Southern Luzon State University',          'BS Management Accounting', '2012'),
(1044, 'College',          'Manuel S. Enverga University Foundation',  'BS Accountancy',           '2001'),
(1044, 'Graduate Studies', 'Ateneo de Manila University',              'Master in Finance',        '2007'),
(1045, 'College',          'Southern Luzon State University',          'BS Accountancy',           '2005'),
(1046, 'College',          'University of Perpetual Help System',      'BS Management Accounting', '2009'),
(1047, 'College',          'Manuel S. Enverga University Foundation',  'BS Accountancy',           '2014'),
(1048, 'College',          'Southern Luzon State University',          'BS Accountancy',           '2016'),
(1049, 'College',          'Manuel S. Enverga University Foundation',  'BS Accountancy',           '2023'),
(1050, 'College',          'Manuel S. Enverga University Foundation',  'BS Accountancy',           '2018'),
(1051, 'College',          'Southern Luzon State University',          'BS Management Accounting', '2019'),
(1052, 'College',          'Quezon City University',                   'BS Accountancy',           '2017'),
(1053, 'College',          'University of Perpetual Help System',      'BS Accountancy',           '2015'),
(1054, 'College',          'Southern Luzon State University',          'BS Accountancy',           '2013'),
(1055, 'College',          'Manuel S. Enverga University Foundation',  'BS Accountancy',           '2020'),
(1056, 'College',          'Southern Luzon State University',          'BS Management Accounting', '2022');

-- ============================================
-- 9. WORK EXPERIENCE
-- ============================================
REPLACE INTO employee_work_experience (employee_id, date_from, date_to, job_title, company_name, monthly_salary) VALUES
(1039, '1997-06-01', '2010-02-28', 'Finance Manager',      'Eastern Savings Bank',         65000.00),
(1040, '2003-07-01', '2013-07-31', 'Accounting Officer',   'SB Finance Corporation',       35000.00),
(1041, '2007-08-01', '2015-04-30', 'Accounting Analyst',   'Rural Bank of Candelaria',     28000.00),
(1042, '2010-06-01', '2016-12-31', 'Accounting Staff',     'Pawnshop Network Cooperative', 24000.00),
(1043, '2012-07-01', '2019-06-14', 'Accounting Staff',     'QuickCash Financing Co.',      22000.00),
(1044, '2001-06-01', '2012-10-31', 'Treasury Officer',     'Eastern Savings Bank',         40000.00),
(1045, '2005-07-01', '2016-03-31', 'Treasury Analyst',     'SB Finance Corporation',       30000.00),
(1046, '2009-08-01', '2017-12-31', 'Treasury Staff',       'Rural Bank of Candelaria',     26000.00),
(1047, '2014-06-01', '2019-12-31', 'Treasury Assistant',   'Pawnshop Network Cooperative', 22000.00),
(1048, '2016-07-01', '2021-02-28', 'Treasury Staff',       'QuickCash Financing Co.',      20000.00),
(1049, '2023-01-01', '2023-05-31', 'Accounting Trainee',   'Palawan Pawnshop Inc.',        15000.00),
(1050, '2018-06-01', '2021-10-31', 'Accounting Assistant', 'Pawnshop Network Cooperative', 19000.00),
(1051, '2019-07-01', '2020-02-29', 'Accounting Trainee',   'QuickCash Financing Co.',      17000.00),
(1052, '2017-06-01', '2019-07-31', 'Accounting Assistant', 'Rural Bank of Candelaria',     18000.00),
(1053, '2015-08-01', '2018-04-30', 'Accounting Staff',     'Palawan Pawnshop Inc.',        20000.00),
(1054, '2013-06-01', '2016-09-30', 'Accounting Staff',     'SB Finance Corporation',       21000.00),
(1055, '2020-07-01', '2022-06-30', 'Treasury Trainee',     'QuickCash Financing Co.',      17000.00),
(1056, '2022-06-01', '2023-01-31', 'Treasury Intern',      'Palawan Pawnshop Inc.',        15000.00);

-- ============================================
-- 10. GOVERNMENT IDs
-- ============================================
REPLACE INTO employee_government_ids (employee_id, sss_number, philhealth_number, pagibig_number, tin_number) VALUES
(1039, '34-1000039-5', '12-100000040-0', '1210-1000-0040', '100-000-040-000'),
(1040, '34-1000040-6', '12-100000041-0', '1210-1000-0041', '100-000-041-000'),
(1041, '34-1000041-7', '12-100000042-0', '1210-1000-0042', '100-000-042-000'),
(1042, '34-1000042-8', '12-100000043-0', '1210-1000-0043', '100-000-043-000'),
(1043, '34-1000043-9', '12-100000044-0', '1210-1000-0044', '100-000-044-000'),
(1044, '34-1000044-0', '12-100000045-0', '1210-1000-0045', '100-000-045-000'),
(1045, '34-1000045-1', '12-100000046-0', '1210-1000-0046', '100-000-046-000'),
(1046, '34-1000046-2', '12-100000047-0', '1210-1000-0047', '100-000-047-000'),
(1047, '34-1000047-3', '12-100000048-0', '1210-1000-0048', '100-000-048-000'),
(1048, '34-1000048-4', '12-100000049-0', '1210-1000-0049', '100-000-049-000'),
(1049, '34-1000049-5', '12-100000050-0', '1210-1000-0050', '100-000-050-000'),
(1050, '34-1000050-6', '12-100000051-0', '1210-1000-0051', '100-000-051-000'),
(1051, '34-1000051-7', '12-100000052-0', '1210-1000-0052', '100-000-052-000'),
(1052, '34-1000052-8', '12-100000053-0', '1210-1000-0053', '100-000-053-000'),
(1053, '34-1000053-9', '12-100000054-0', '1210-1000-0054', '100-000-054-000'),
(1054, '34-1000054-0', '12-100000055-0', '1210-1000-0055', '100-000-055-000'),
(1055, '34-1000055-1', '12-100000056-0', '1210-1000-0056', '100-000-056-000'),
(1056, '34-1000056-2', '12-100000057-0', '1210-1000-0057', '100-000-057-000');

-- ============================================
-- 11. DISCLOSURES
-- ============================================
REPLACE INTO employee_disclosures (employee_id, is_related_to_company, has_admin_offense, has_criminal_charge) VALUES
(1039, 0, 0, 0),(1040, 0, 0, 0),(1041, 0, 0, 0),(1042, 0, 0, 0),(1043, 0, 0, 0),
(1044, 0, 0, 0),(1045, 0, 0, 0),(1046, 0, 0, 0),(1047, 0, 0, 0),(1048, 0, 0, 0),
(1049, 0, 0, 0),(1050, 0, 0, 0),(1051, 0, 0, 0),(1052, 0, 0, 0),(1053, 0, 0, 0),
(1054, 0, 0, 0),(1055, 0, 0, 0),(1056, 0, 0, 0);

-- ============================================
-- 12. PORTAL ACCOUNTS (default password: password)
-- ============================================
REPLACE INTO users (
    employee_id, username, email, password_hash,
    full_name, role, branch_id, is_active, first_login_completed, created_at
) VALUES
(1039,'FN-001','FN-001','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Cristina Mercado Bautista',   'Employee',102,1,0,NOW()),
(1040,'FN-002','FN-002','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Roberto Santos Dela Cruz',    'Employee',102,1,0,NOW()),
(1041,'FN-003','FN-003','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Marianne Ramos Esguerra',     'Employee',102,1,0,NOW()),
(1042,'FN-004','FN-004','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Eduardo Lim Navarro',         'Employee',102,1,0,NOW()),
(1043,'FN-005','FN-005','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Grace Villanueva Manalo',     'Employee',102,1,0,NOW()),
(1044,'FN-006','FN-006','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Renato Torres Castillo',      'Employee',102,1,0,NOW()),
(1045,'FN-007','FN-007','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Leonora Aquino Pascual',      'Employee',102,1,0,NOW()),
(1046,'FN-008','FN-008','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Alfredo Reyes Mendoza',       'Employee',102,1,0,NOW()),
(1047,'FN-009','FN-009','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Joanna Cruz Salazar',         'Employee',102,1,0,NOW()),
(1048,'FN-010','FN-010','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Bernard Garcia Flores',       'Employee',102,1,0,NOW()),
(1049,'FN-011','FN-011','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Analiza Santos Ocampo',       'Employee',102,1,0,NOW()),
(1050,'FN-012','FN-012','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Danilo Ocampo Reyes',         'Employee',102,1,0,NOW()),
(1051,'FN-013','FN-013','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Lorraine Garcia Villanueva',  'Employee',102,1,0,NOW()),
(1052,'FN-014','FN-014','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Mark Navarro Tolentino',      'Employee',102,1,0,NOW()),
(1053,'FN-015','FN-015','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Patricia Dela Rosa Guevarra', 'Employee',102,1,0,NOW()),
(1054,'FN-016','FN-016','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Jerome Bautista Hernandez',   'Employee',102,1,0,NOW()),
(1055,'FN-017','FN-017','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Sheila Lopez Macaraeg',       'Employee',102,1,0,NOW()),
(1056,'FN-018','FN-018','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Carlo Ramos Paglinawan',      'Employee',102,1,0,NOW());

SET FOREIGN_KEY_CHECKS = 1;
