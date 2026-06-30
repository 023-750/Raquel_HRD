-- ============================================
-- 4th Seed: Branch Employees
-- Branch: 102 (Raquel Pawnshop Main Office)
-- ============================================
--
-- SECTION A: Acquired Properties Department
--   Employee IDs: 1000–1016 | Codes: AP-001–AP-017
--   Department ID: 1 | Job Title IDs: 100–116
--
-- SECTION B: Audit Department
--   Employee IDs: 1017–1028 | Codes: AU-001–AU-012
--   Department ID: 2 | Job Title IDs: 200–211
-- ============================================

USE raquel_hris;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- ============================================
-- SECTION A: ACQUIRED PROPERTIES
-- ============================================
-- ============================================

-- ============================================
-- A-1. EMPLOYEES (Acquired Properties)
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
-- 100: VP for Acquired Properties (Executives)
(1000, 'AP-001', 'Ricardo', 'Villanueva', 'Santos','2010-06-01', '1970-03-15', 'Lucena City, Quezon','Male', 'Married',100, 'VP for Acquired Properties',1, 1, 102, 'Regular', 'Full-time'),

-- 101: AP Manager I (Manager)
(1001, 'AP-002', 'Maribel', 'Reyes', 'Cruz',
 '2013-08-12', '1978-07-22', 'Tayabas, Quezon',
 'Female', 'Married',
 101, 'AP Manager I',
 1, 3, 102, 'Regular', 'Full-time'),

-- 102: AP Manager II (Manager)
(1002, 'AP-003', 'Danilo', 'Aguilar', 'Mendoza',
 '2014-03-20', '1980-11-05', 'Sariaya, Quezon',
 'Male', 'Single',
 102, 'AP Manager II',
 1, 3, 102, 'Regular', 'Full-time'),

-- 103: AP Manager III (Manager)
(1003, 'AP-004', 'Rosario', 'Castillo', 'Flores',
 '2015-01-10', '1982-04-18', 'Candelaria, Quezon',
 'Female', 'Married',
 103, 'AP Manager III',
 1, 3, 102, 'Regular', 'Full-time'),

-- 104: AP Manager IV (Manager)
(1004, 'AP-005', 'Fernando', 'Bautista', 'Lim',
 '2016-07-25', '1979-09-30', 'Pagbilao, Quezon',
 'Male', 'Married',
 104, 'AP Manager IV',
 1, 3, 102, 'Regular', 'Full-time'),

-- 105: AP Supervisor I (Supervisor)
(1005, 'AP-006', 'Carmela', 'Navarro', 'Garcia',
 '2018-02-14', '1985-12-01', 'Lucban, Quezon',
 'Female', 'Single',
 105, 'AP Supervisor I',
 1, 4, 102, 'Regular', 'Full-time'),

-- 106: AP Supervisor II (Supervisor)
(1006, 'AP-007', 'Jerome', 'Pascual', 'Dela Cruz',
 '2018-09-03', '1987-05-14', 'Gumaca, Quezon',
 'Male', 'Married',
 106, 'AP Supervisor II',
 1, 4, 102, 'Regular', 'Full-time'),

-- 107: AP Supervisor III (Supervisor)
(1007, 'AP-008', 'Leilani', 'Ramos', 'Buenaventura',
 '2019-04-22', '1990-08-27', 'Atimonan, Quezon',
 'Female', 'Single',
 107, 'AP Supervisor III',
 1, 4, 102, 'Regular', 'Full-time'),

-- 108: AP Staff on Probation (R&F)
(1008, 'AP-009', 'Carlo', 'Mendez', 'Rivera',
 '2025-11-01', '1999-02-10', 'Calauag, Quezon',
 'Male', 'Single',
 108, 'AP Staff on Probation',
 1, 5, 102, 'Probationary', 'Full-time'),

-- 109: AP Staff I (R&F)
(1009, 'AP-010', 'Alicia', 'Santiago', 'Hernandez',
 '2021-05-17', '1995-06-03', 'Tiaong, Quezon',
 'Female', 'Single',
 109, 'AP Staff I',
 1, 5, 102, 'Regular', 'Full-time'),

-- 110: AP Staff II (R&F)
(1010, 'AP-011', 'Mark Anthony', 'Dela Torre', 'Perez',
 '2020-08-10', '1993-10-21', 'Dolores, Quezon',
 'Male', 'Single',
 110, 'AP Staff II',
 1, 5, 102, 'Regular', 'Full-time'),

-- 111: AP Staff III (R&F)
(1011, 'AP-012', 'Josephine', 'Aquino', 'Morales',
 '2019-11-04', '1991-01-16', 'Lopez, Quezon',
 'Female', 'Married',
 111, 'AP Staff III',
 1, 5, 102, 'Regular', 'Full-time'),

-- 112: AP Staff IV (R&F)
(1012, 'AP-013', 'Ryan', 'Ocampo', 'Villar',
 '2018-06-30', '1990-04-08', 'Catanauan, Quezon',
 'Male', 'Married',
 112, 'AP Staff IV',
 1, 5, 102, 'Regular', 'Full-time'),

-- 113: AP Staff V (R&F)
(1013, 'AP-014', 'Maricel', 'Fernandez', 'Abad',
 '2017-03-15', '1988-07-25', 'Mulanay, Quezon',
 'Female', 'Single',
 113, 'AP Staff V',
 1, 5, 102, 'Regular', 'Full-time'),

-- 114: Sales Associate on Probation (R&F)
(1014, 'AP-015', 'Joshua', 'Reyes', 'Tan',
 '2025-10-15', '2001-12-05', 'Tagkawayan, Quezon',
 'Male', 'Single',
 114, 'Sales Associate on Probation',
 1, 5, 102, 'Probationary', 'Full-time'),

-- 115: Sales Associate I (R&F)
(1015, 'AP-016', 'Hannah', 'Cruz', 'Soriano',
 '2022-07-11', '1997-03-19', 'San Andres, Quezon',
 'Female', 'Single',
 115, 'Sales Associate I',
 1, 5, 102, 'Regular', 'Full-time'),

-- 116: Sales Associate II (R&F)
(1016, 'AP-017', 'Patrick', 'Lozano', 'Batungbakal',
 '2021-02-08', '1996-09-12', 'Unisan, Quezon',
 'Male', 'Single',
 116, 'Sales Associate II',
 1, 5, 102, 'Regular', 'Full-time');

-- ============================================
-- 2. EMPLOYEE CONTACTS
-- ============================================
REPLACE INTO employee_contacts (employee_id, mobile_number, personal_email) VALUES
(1000, '09170000100', 'ricardo.villanueva@example.com'),
(1001, '09170000101', 'maribel.reyes@example.com'),
(1002, '09170000102', 'danilo.aguilar@example.com'),
(1003, '09170000103', 'rosario.castillo@example.com'),
(1004, '09170000104', 'fernando.bautista@example.com'),
(1005, '09170000105', 'carmela.navarro@example.com'),
(1006, '09170000106', 'jerome.pascual@example.com'),
(1007, '09170000107', 'leilani.ramos@example.com'),
(1008, '09170000108', 'carlo.mendez@example.com'),
(1009, '09170000109', 'alicia.santiago@example.com'),
(1010, '09170000110', 'mark.delatorre@example.com'),
(1011, '09170000111', 'josephine.aquino@example.com'),
(1012, '09170000112', 'ryan.ocampo@example.com'),
(1013, '09170000113', 'maricel.fernandez@example.com'),
(1014, '09170000114', 'joshua.reyes@example.com'),
(1015, '09170000115', 'hannah.cruz@example.com'),
(1016, '09170000116', 'patrick.lozano@example.com');

-- ============================================
-- 3. EMPLOYEE DETAILS (Physical & Citizenship)
-- ============================================
REPLACE INTO employee_details (employee_id, height_m, weight_kg, blood_type, citizenship) VALUES
(1000, 1.72, 78.0, 'O+',  'Filipino'),
(1001, 1.60, 56.0, 'A+',  'Filipino'),
(1002, 1.70, 72.0, 'B+',  'Filipino'),
(1003, 1.58, 54.0, 'AB+', 'Filipino'),
(1004, 1.74, 80.0, 'O+',  'Filipino'),
(1005, 1.62, 58.0, 'A-',  'Filipino'),
(1006, 1.68, 70.0, 'B+',  'Filipino'),
(1007, 1.55, 50.0, 'O-',  'Filipino'),
(1008, 1.73, 68.0, 'A+',  'Filipino'),
(1009, 1.61, 55.0, 'O+',  'Filipino'),
(1010, 1.75, 75.0, 'B-',  'Filipino'),
(1011, 1.59, 53.0, 'AB-', 'Filipino'),
(1012, 1.71, 73.0, 'A+',  'Filipino'),
(1013, 1.57, 51.0, 'O+',  'Filipino'),
(1014, 1.76, 71.0, 'B+',  'Filipino'),
(1015, 1.63, 57.0, 'A-',  'Filipino'),
(1016, 1.69, 69.0, 'O+',  'Filipino');

-- ============================================
-- 4. ADDRESSES
-- ============================================
REPLACE INTO employee_addresses (employee_id, address_type, barangay, city, province, zip_code) VALUES
(1000, 'Residential', 'Mayuwi',        'Tayabas City', 'Quezon', '4327'),
(1000, 'Permanent',   'Mayuwi',        'Tayabas City', 'Quezon', '4327'),
(1001, 'Residential', 'Ibabang Dupay', 'Lucena City',  'Quezon', '4301'),
(1001, 'Permanent',   'Ibabang Dupay', 'Lucena City',  'Quezon', '4301'),
(1002, 'Residential', 'Poblacion',     'Sariaya',      'Quezon', '4322'),
(1002, 'Permanent',   'Poblacion',     'Sariaya',      'Quezon', '4322'),
(1003, 'Residential', 'Poblacion',     'Candelaria',   'Quezon', '4323'),
(1003, 'Permanent',   'Poblacion',     'Candelaria',   'Quezon', '4323'),
(1004, 'Residential', 'Poblacion',     'Pagbilao',     'Quezon', '4302'),
(1004, 'Permanent',   'Poblacion',     'Pagbilao',     'Quezon', '4302'),
(1005, 'Residential', 'Ilayang Dupay', 'Lucena City',  'Quezon', '4301'),
(1005, 'Permanent',   'Ilayang Dupay', 'Lucena City',  'Quezon', '4301'),
(1006, 'Residential', 'Poblacion',     'Gumaca',       'Quezon', '4307'),
(1006, 'Permanent',   'Poblacion',     'Gumaca',       'Quezon', '4307'),
(1007, 'Residential', 'Poblacion',     'Atimonan',     'Quezon', '4331'),
(1007, 'Permanent',   'Poblacion',     'Atimonan',     'Quezon', '4331'),
(1008, 'Residential', 'Tignoan',       'Calauag',      'Quezon', '4318'),
(1008, 'Permanent',   'Tignoan',       'Calauag',      'Quezon', '4318'),
(1009, 'Residential', 'Poblacion',     'Tiaong',       'Quezon', '4325'),
(1009, 'Permanent',   'Poblacion',     'Tiaong',       'Quezon', '4325'),
(1010, 'Residential', 'Maligaya',      'Lucena City',  'Quezon', '4301'),
(1010, 'Permanent',   'Maligaya',      'Lucena City',  'Quezon', '4301'),
(1011, 'Residential', 'Poblacion',     'Lopez',        'Quezon', '4316'),
(1011, 'Permanent',   'Poblacion',     'Lopez',        'Quezon', '4316'),
(1012, 'Residential', 'Gulang-Gulang', 'Lucena City',  'Quezon', '4301'),
(1012, 'Permanent',   'Gulang-Gulang', 'Lucena City',  'Quezon', '4301'),
(1013, 'Residential', 'Poblacion',     'Mulanay',      'Quezon', '4311'),
(1013, 'Permanent',   'Poblacion',     'Mulanay',      'Quezon', '4311'),
(1014, 'Residential', 'Poblacion',     'Tagkawayan',   'Quezon', '4336'),
(1014, 'Permanent',   'Poblacion',     'Tagkawayan',   'Quezon', '4336'),
(1015, 'Residential', 'Poblacion',     'San Andres',   'Quezon', '4314'),
(1015, 'Permanent',   'Poblacion',     'San Andres',   'Quezon', '4314'),
(1016, 'Residential', 'Poblacion',     'Unisan',       'Quezon', '4305'),
(1016, 'Permanent',   'Poblacion',     'Unisan',       'Quezon', '4305');

-- ============================================
-- 5. EMERGENCY CONTACTS
-- ============================================
REPLACE INTO employee_emergency_contacts (employee_id, contact_name, relationship, contact_number) VALUES
(1000, 'Maria Villanueva',    'Spouse',  '09170001000'),
(1001, 'Roberto Reyes',       'Spouse',  '09170001001'),
(1002, 'Lourdes Aguilar',     'Mother',  '09170001002'),
(1003, 'Eduardo Castillo',    'Spouse',  '09170001003'),
(1004, 'Angela Bautista',     'Spouse',  '09170001004'),
(1005, 'Teresita Navarro',    'Mother',  '09170001005'),
(1006, 'Ma. Liza Pascual',    'Spouse',  '09170001006'),
(1007, 'Aida Ramos',          'Mother',  '09170001007'),
(1008, 'Luisa Mendez',        'Mother',  '09170001008'),
(1009, 'Pedro Santiago',      'Father',  '09170001009'),
(1010, 'Gloria Dela Torre',   'Mother',  '09170001010'),
(1011, 'Rodel Aquino',        'Spouse',  '09170001011'),
(1012, 'Cristina Ocampo',     'Spouse',  '09170001012'),
(1013, 'Natividad Fernandez', 'Mother',  '09170001013'),
(1014, 'Benjamin Reyes',      'Father',  '09170001014'),
(1015, 'Evelyn Cruz',         'Mother',  '09170001015'),
(1016, 'Arlene Lozano',       'Mother',  '09170001016');

-- ============================================
-- 6. FAMILY BACKGROUND
-- ============================================
REPLACE INTO employee_family (employee_id, member_type, surname, first_name, middle_name, occupation) VALUES
-- VP
(1000, 'Father', 'Villanueva', 'Ernesto',  'Santos',    'Retired'),
(1000, 'Mother', 'Villanueva', 'Conchita', 'Reyes',     'Retired'),
(1000, 'Spouse', 'Villanueva', 'Maria',    'Gonzales',  'Accountant'),
-- AP Manager I
(1001, 'Father', 'Reyes',      'Roberto',  'Dela Cruz', 'Farmer'),
(1001, 'Mother', 'Reyes',      'Nilda',    'Cruz',      'Housewife'),
(1001, 'Spouse', 'Reyes',      'Eduardo',  'Garcia',    'Engineer'),
-- AP Manager II
(1002, 'Father', 'Aguilar',    'Renato',   'Mendoza',   'Carpenter'),
(1002, 'Mother', 'Aguilar',    'Lourdes',  'Santos',    'Teacher'),
-- AP Manager III
(1003, 'Father', 'Castillo',   'Nestor',   'Flores',    'Businessman'),
(1003, 'Mother', 'Castillo',   'Lita',     'Ramos',     'Nurse'),
(1003, 'Spouse', 'Castillo',   'Eduardo',  'Perez',     'Lawyer'),
-- AP Manager IV
(1004, 'Father', 'Bautista',   'Rodrigo',  'Lim',       'Retired'),
(1004, 'Mother', 'Bautista',   'Cecilia',  'Torres',    'Retired'),
(1004, 'Spouse', 'Bautista',   'Angela',   'Marquez',   'Doctor'),
-- AP Supervisor I
(1005, 'Father', 'Navarro',    'Pablito',  'Garcia',    'Driver'),
(1005, 'Mother', 'Navarro',    'Teresita', 'Lopez',     'Housewife'),
-- AP Supervisor II
(1006, 'Father', 'Pascual',    'Ernesto',  'Dela Cruz', 'Fisherman'),
(1006, 'Mother', 'Pascual',    'Maricel',  'Buenaventura', 'Teacher'),
(1006, 'Spouse', 'Pascual',    'Ma. Liza', 'Bautista',  'Nurse'),
-- AP Supervisor III
(1007, 'Father', 'Ramos',      'Cornelio', 'Buenaventura', 'Farmer'),
(1007, 'Mother', 'Ramos',      'Aida',     'Santiago',  'Housewife'),
-- AP Staff on Probation
(1008, 'Father', 'Mendez',     'Raul',     'Rivera',    'Security Guard'),
(1008, 'Mother', 'Mendez',     'Luisa',    'Soriano',   'Housewife'),
-- AP Staff I
(1009, 'Father', 'Santiago',   'Pedro',    'Hernandez', 'Carpenter'),
(1009, 'Mother', 'Santiago',   'Gloria',   'Macaraeg',  'Housewife'),
-- AP Staff II
(1010, 'Father', 'Dela Torre', 'Victorino','Perez',     'Tricycle Driver'),
(1010, 'Mother', 'Dela Torre', 'Gloria',   'Batungbakal','Market Vendor'),
-- AP Staff III
(1011, 'Father', 'Aquino',     'Marcelino','Morales',   'Farmer'),
(1011, 'Mother', 'Aquino',     'Resurreccion','Navarro', 'Housewife'),
(1011, 'Spouse', 'Aquino',     'Rodel',    'Delos Santos','Factory Worker'),
-- AP Staff IV
(1012, 'Father', 'Ocampo',     'Herminio', 'Villar',    'Retired'),
(1012, 'Mother', 'Ocampo',     'Erlinda',  'Bautista',  'Retired'),
(1012, 'Spouse', 'Ocampo',     'Cristina', 'Fuentes',   'Teacher'),
-- AP Staff V
(1013, 'Father', 'Fernandez',  'Paciano',  'Abad',      'Farmer'),
(1013, 'Mother', 'Fernandez',  'Natividad','Vergara',   'Housewife'),
-- Sales Associate on Probation
(1014, 'Father', 'Reyes',      'Benjamin', 'Tan',       'OFW'),
(1014, 'Mother', 'Reyes',      'Michelle', 'Santos',    'Sales Clerk'),
-- Sales Associate I
(1015, 'Father', 'Cruz',       'Eduardo',  'Soriano',   'Jeepney Driver'),
(1015, 'Mother', 'Cruz',       'Evelyn',   'Villanueva','Housewife'),
-- Sales Associate II
(1016, 'Father', 'Lozano',     'Aurelio',  'Batungbakal','Electrician'),
(1016, 'Mother', 'Lozano',     'Arlene',   'Pascual',   'Dressmaker');

-- ============================================
-- 7. EDUCATIONAL BACKGROUND
-- ============================================
REPLACE INTO employee_education (employee_id, education_level, school_name, degree_course, year_graduated) VALUES
(1000, 'College',          'University of the Philippines',          'BS Business Administration', '1993'),
(1000, 'Graduate Studies', 'De La Salle University',                 'MBA',                        '1997'),
(1001, 'College',          'Polytechnic University of the Philippines','BS Business Management',   '2001'),
(1001, 'Graduate Studies', 'Pamantasan ng Lungsod ng Maynila',       'MBA',                        '2008'),
(1002, 'College',          'Lyceum of the Philippines University',   'BS Commerce',                '2003'),
(1003, 'College',          'Laguna State Polytechnic University',    'BS Business Administration', '2004'),
(1003, 'Graduate Studies', 'University of Santo Tomas',              'MBA',                        '2010'),
(1004, 'College',          'Manuel S. Enverga University Foundation','BS Accountancy',             '2002'),
(1004, 'Graduate Studies', 'Far Eastern University',                 'MBA',                        '2008'),
(1005, 'College',          'Quezon City University',                 'BS Office Administration',   '2007'),
(1006, 'College',          'Southern Luzon State University',        'BS Business Management',     '2009'),
(1007, 'College',          'Manuel S. Enverga University Foundation','BS Commerce',                '2012'),
(1008, 'College',          'Laguna State Polytechnic University',    'BS Office Administration',   '2021'),
(1009, 'College',          'Southern Luzon State University',        'BS Business Administration', '2017'),
(1010, 'College',          'Manuel S. Enverga University Foundation','BS Commerce',                '2015'),
(1011, 'College',          'Quezon City University',                 'BS Business Management',     '2013'),
(1012, 'College',          'Polytechnic University of the Philippines','BS Commerce',              '2012'),
(1013, 'College',          'Laguna State Polytechnic University',    'BS Office Administration',   '2010'),
(1014, 'College',          'Southern Luzon State University',        'BS Marketing',               '2023'),
(1015, 'College',          'Manuel S. Enverga University Foundation','BS Business Administration', '2019'),
(1016, 'College',          'Manuel S. Enverga University Foundation','BS Commerce',                '2018');

-- ============================================
-- 8. WORK EXPERIENCE
-- ============================================
REPLACE INTO employee_work_experience (employee_id, date_from, date_to, job_title, company_name, monthly_salary) VALUES
(1000, '1993-08-01', '2010-05-31', 'Property Manager',       'Lucena Real Estate Corp.',          65000.00),
(1001, '2001-06-01', '2013-08-11', 'Property Coordinator',   'QP Realty Services',                28000.00),
(1002, '2003-07-01', '2014-03-19', 'Sales Coordinator',      'Metro Property Solutions',          25000.00),
(1003, '2004-06-01', '2015-01-09', 'AP Assistant Manager',   'Southern Realty Group',             35000.00),
(1004, '2002-07-01', '2016-07-24', 'Property Appraiser',     'Quezon Appraisal Services',         38000.00),
(1005, '2007-06-01', '2018-02-13', 'AP Staff',               'Metro Asset Management Inc.',       22000.00),
(1006, '2009-06-01', '2018-09-02', 'Property Clerk',         'Pacific Property Holdings',         20000.00),
(1007, '2012-06-01', '2019-04-21', 'AP Coordinator',         'Visayas Asset Recovery Corp.',      22000.00),
(1009, '2017-06-01', '2021-05-16', 'Sales Staff',            'QP Marketing Associates',           18000.00),
(1010, '2015-06-01', '2020-08-09', 'Property Aide',          'Lucena Commercial Properties',      17500.00),
(1011, '2013-06-01', '2019-11-03', 'Administrative Aide',    'Southern Quezon Realty',            16500.00),
(1012, '2012-06-01', '2018-06-29', 'Property Sales Aide',    'Eastern Property Group',            18000.00),
(1013, '2010-06-01', '2017-03-14', 'Clerical Staff',         'Raquel Trading Corp.',              15000.00);

-- ============================================
-- 9. GOVERNMENT IDs
-- ============================================
REPLACE INTO employee_government_ids (employee_id, sss_number, philhealth_number, pagibig_number, tin_number) VALUES
(1000, '34-1000000-1', '12-100000001-0', '1210-1000-0001', '100-000-001-000'),
(1001, '34-1000001-2', '12-100000002-0', '1210-1000-0002', '100-000-002-000'),
(1002, '34-1000002-3', '12-100000003-0', '1210-1000-0003', '100-000-003-000'),
(1003, '34-1000003-4', '12-100000004-0', '1210-1000-0004', '100-000-004-000'),
(1004, '34-1000004-5', '12-100000005-0', '1210-1000-0005', '100-000-005-000'),
(1005, '34-1000005-6', '12-100000006-0', '1210-1000-0006', '100-000-006-000'),
(1006, '34-1000006-7', '12-100000007-0', '1210-1000-0007', '100-000-007-000'),
(1007, '34-1000007-8', '12-100000008-0', '1210-1000-0008', '100-000-008-000'),
(1008, '34-1000008-9', '12-100000009-0', '1210-1000-0009', '100-000-009-000'),
(1009, '34-1000009-0', '12-100000010-0', '1210-1000-0010', '100-000-010-000'),
(1010, '34-1000010-1', '12-100000011-0', '1210-1000-0011', '100-000-011-000'),
(1011, '34-1000011-2', '12-100000012-0', '1210-1000-0012', '100-000-012-000'),
(1012, '34-1000012-3', '12-100000013-0', '1210-1000-0013', '100-000-013-000'),
(1013, '34-1000013-4', '12-100000014-0', '1210-1000-0014', '100-000-014-000'),
(1014, '34-1000014-5', '12-100000015-0', '1210-1000-0015', '100-000-015-000'),
(1015, '34-1000015-6', '12-100000016-0', '1210-1000-0016', '100-000-016-000'),
(1016, '34-1000016-7', '12-100000017-0', '1210-1000-0017', '100-000-017-000');

-- ============================================
-- 10. DISCLOSURES
-- ============================================
REPLACE INTO employee_disclosures (employee_id, is_related_to_company, has_admin_offense, has_criminal_charge) VALUES
(1000, 0, 0, 0), (1001, 0, 0, 0), (1002, 0, 0, 0),
(1003, 0, 0, 0), (1004, 0, 0, 0), (1005, 0, 0, 0),
(1006, 0, 0, 0), (1007, 0, 0, 0), (1008, 0, 0, 0),
(1009, 0, 0, 0), (1010, 0, 0, 0), (1011, 0, 0, 0),
(1012, 0, 0, 0), (1013, 0, 0, 0), (1014, 0, 0, 0),
(1015, 0, 0, 0), (1016, 0, 0, 0);



-- ============================================
-- ============================================
-- SECTION B: AUDIT DEPARTMENT
-- Department ID: 2 | Branch: 102
-- Employee IDs: 1017–1028 | Codes: AU-001–AU-012
-- Job Title IDs: 200–211
-- ============================================
-- ============================================

-- ============================================
-- B-1. EMPLOYEES (Audit)
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
-- 200: Audit Manager I (Manager)
(1017, 'AU-001', 'Gregorio', 'Tolentino', 'Salazar',
 '2011-03-07', '1972-08-14', 'Lucena City, Quezon',
 'Male', 'Married',
 200, 'Audit Manager I',
 2, 3, 102, 'Regular', 'Full-time'),

-- 201: Audit Manager II (Manager)
(1018, 'AU-002', 'Florencia', 'Magpayo', 'Reyes',
 '2014-06-16', '1979-02-28', 'Candelaria, Quezon',
 'Female', 'Married',
 201, 'Audit Manager II',
 2, 3, 102, 'Regular', 'Full-time'),

-- 202: Audit Manager III (Manager)
(1019, 'AU-003', 'Renato', 'Gutierrez', 'Villanueva',
 '2015-09-01', '1981-05-19', 'Sariaya, Quezon',
 'Male', 'Single',
 202, 'Audit Manager III',
 2, 3, 102, 'Regular', 'Full-time'),

-- 203: Audit Supervisor I (Supervisor)
(1020, 'AU-004', 'Leonora', 'Bautista', 'Santos',
 '2017-02-20', '1985-11-03', 'Gumaca, Quezon',
 'Female', 'Married',
 203, 'Audit Supervisor I',
 2, 4, 102, 'Regular', 'Full-time'),

-- 204: Audit Supervisor II (Supervisor)
(1021, 'AU-005', 'Herminio', 'Dela Cruz', 'Morales',
 '2018-07-09', '1987-06-25', 'Tayabas City, Quezon',
 'Male', 'Single',
 204, 'Audit Supervisor II',
 2, 4, 102, 'Regular', 'Full-time'),

-- 205: Audit Supervisor III (Supervisor)
(1022, 'AU-006', 'Precious', 'Lim', 'Ocampo',
 '2019-01-14', '1990-03-07', 'Pagbilao, Quezon',
 'Female', 'Single',
 205, 'Audit Supervisor III',
 2, 4, 102, 'Regular', 'Full-time'),

-- 206: Auditor on Probation (R&F)
(1023, 'AU-007', 'Arjay', 'Santos', 'Navarro',
 '2025-09-08', '2001-07-22', 'Tiaong, Quezon',
 'Male', 'Single',
 206, 'Auditor on Probation',
 2, 5, 102, 'Probationary', 'Full-time'),

-- 207: Auditor I (R&F)
(1024, 'AU-008', 'Cristina', 'Ramos', 'Torres',
 '2022-04-11', '1997-09-15', 'Calauag, Quezon',
 'Female', 'Single',
 207, 'Auditor I',
 2, 5, 102, 'Regular', 'Full-time'),

-- 208: Auditor II (R&F)
(1025, 'AU-009', 'Danilo', 'Perez', 'Buenaventura',
 '2021-08-23', '1994-12-04', 'Atimonan, Quezon',
 'Male', 'Single',
 208, 'Auditor II',
 2, 5, 102, 'Regular', 'Full-time'),

-- 209: Auditor III (R&F)
(1026, 'AU-010', 'Marianne', 'Aquino', 'Hernandez',
 '2020-03-02', '1992-04-30', 'Lucban, Quezon',
 'Female', 'Married',
 209, 'Auditor III',
 2, 5, 102, 'Regular', 'Full-time'),

-- 210: Auditor IV (R&F)
(1027, 'AU-011', 'Reynaldo', 'Castillo', 'Pascual',
 '2018-11-05', '1989-10-17', 'Lopez, Quezon',
 'Male', 'Married',
 210, 'Auditor IV',
 2, 5, 102, 'Regular', 'Full-time'),

-- 211: Auditor V (R&F)
(1028, 'AU-012', 'Analyn', 'Soriano', 'Aguilar',
 '2017-06-19', '1987-01-09', 'Lucena City, Quezon',
 'Female', 'Single',
 211, 'Auditor V',
 2, 5, 102, 'Regular', 'Full-time');

-- ============================================
-- B-2. EMPLOYEE CONTACTS (Audit)
-- ============================================
REPLACE INTO employee_contacts (employee_id, mobile_number, personal_email) VALUES
(1017, '09170000117', 'gregorio.tolentino@example.com'),
(1018, '09170000118', 'florencia.magpayo@example.com'),
(1019, '09170000119', 'renato.gutierrez@example.com'),
(1020, '09170000120', 'leonora.bautista@example.com'),
(1021, '09170000121', 'herminio.delacruz@example.com'),
(1022, '09170000122', 'precious.lim@example.com'),
(1023, '09170000123', 'arjay.santos@example.com'),
(1024, '09170000124', 'cristina.ramos@example.com'),
(1025, '09170000125', 'danilo.perez@example.com'),
(1026, '09170000126', 'marianne.aquino@example.com'),
(1027, '09170000127', 'reynaldo.castillo@example.com'),
(1028, '09170000128', 'analyn.soriano@example.com');

-- ============================================
-- B-3. EMPLOYEE DETAILS (Audit)
-- ============================================
REPLACE INTO employee_details (employee_id, height_m, weight_kg, blood_type, citizenship) VALUES
(1017, 1.73, 76.0, 'O+',  'Filipino'),
(1018, 1.59, 55.0, 'A+',  'Filipino'),
(1019, 1.71, 74.0, 'B+',  'Filipino'),
(1020, 1.61, 57.0, 'AB+', 'Filipino'),
(1021, 1.74, 79.0, 'O+',  'Filipino'),
(1022, 1.58, 52.0, 'A-',  'Filipino'),
(1023, 1.75, 70.0, 'B+',  'Filipino'),
(1024, 1.62, 56.0, 'O-',  'Filipino'),
(1025, 1.72, 73.0, 'A+',  'Filipino'),
(1026, 1.60, 54.0, 'O+',  'Filipino'),
(1027, 1.70, 77.0, 'B-',  'Filipino'),
(1028, 1.57, 50.0, 'AB-', 'Filipino');

-- ============================================
-- B-4. ADDRESSES (Audit)
-- ============================================
REPLACE INTO employee_addresses (employee_id, address_type, barangay, city, province, zip_code) VALUES
(1017, 'Residential', 'Poblacion',     'Lucena City',  'Quezon', '4301'),
(1017, 'Permanent',   'Poblacion',     'Lucena City',  'Quezon', '4301'),
(1018, 'Residential', 'Poblacion',     'Candelaria',   'Quezon', '4323'),
(1018, 'Permanent',   'Poblacion',     'Candelaria',   'Quezon', '4323'),
(1019, 'Residential', 'Poblacion',     'Sariaya',      'Quezon', '4322'),
(1019, 'Permanent',   'Poblacion',     'Sariaya',      'Quezon', '4322'),
(1020, 'Residential', 'Poblacion',     'Gumaca',       'Quezon', '4307'),
(1020, 'Permanent',   'Poblacion',     'Gumaca',       'Quezon', '4307'),
(1021, 'Residential', 'Mayuwi',        'Tayabas City', 'Quezon', '4327'),
(1021, 'Permanent',   'Mayuwi',        'Tayabas City', 'Quezon', '4327'),
(1022, 'Residential', 'Poblacion',     'Pagbilao',     'Quezon', '4302'),
(1022, 'Permanent',   'Poblacion',     'Pagbilao',     'Quezon', '4302'),
(1023, 'Residential', 'Poblacion',     'Tiaong',       'Quezon', '4325'),
(1023, 'Permanent',   'Poblacion',     'Tiaong',       'Quezon', '4325'),
(1024, 'Residential', 'Tignoan',       'Calauag',      'Quezon', '4318'),
(1024, 'Permanent',   'Tignoan',       'Calauag',      'Quezon', '4318'),
(1025, 'Residential', 'Poblacion',     'Atimonan',     'Quezon', '4331'),
(1025, 'Permanent',   'Poblacion',     'Atimonan',     'Quezon', '4331'),
(1026, 'Residential', 'Ilayang Dupay', 'Lucena City',  'Quezon', '4301'),
(1026, 'Permanent',   'Ilayang Dupay', 'Lucena City',  'Quezon', '4301'),
(1027, 'Residential', 'Poblacion',     'Lopez',        'Quezon', '4316'),
(1027, 'Permanent',   'Poblacion',     'Lopez',        'Quezon', '4316'),
(1028, 'Residential', 'Gulang-Gulang', 'Lucena City',  'Quezon', '4301'),
(1028, 'Permanent',   'Gulang-Gulang', 'Lucena City',  'Quezon', '4301');

-- ============================================
-- B-5. EMERGENCY CONTACTS (Audit)
-- ============================================
REPLACE INTO employee_emergency_contacts (employee_id, contact_name, relationship, contact_number) VALUES
(1017, 'Corazon Tolentino',   'Spouse',  '09170001017'),
(1018, 'Eduardo Magpayo',     'Spouse',  '09170001018'),
(1019, 'Perla Gutierrez',     'Mother',  '09170001019'),
(1020, 'Rodrigo Bautista',    'Spouse',  '09170001020'),
(1021, 'Teresita Dela Cruz',  'Mother',  '09170001021'),
(1022, 'Nenita Lim',          'Mother',  '09170001022'),
(1023, 'Divino Santos',       'Father',  '09170001023'),
(1024, 'Leonora Ramos',       'Mother',  '09170001024'),
(1025, 'Carmelita Perez',     'Mother',  '09170001025'),
(1026, 'Bernardo Aquino',     'Spouse',  '09170001026'),
(1027, 'Roselyn Castillo',    'Spouse',  '09170001027'),
(1028, 'Virgilio Soriano',    'Father',  '09170001028');

-- ============================================
-- B-6. FAMILY BACKGROUND (Audit)
-- ============================================
REPLACE INTO employee_family (employee_id, member_type, surname, first_name, middle_name, occupation) VALUES
-- Audit Manager I
(1017, 'Father', 'Tolentino', 'Amado',     'Salazar',    'Retired'),
(1017, 'Mother', 'Tolentino', 'Lydia',     'Cruz',       'Retired'),
(1017, 'Spouse', 'Tolentino', 'Corazon',   'Reyes',      'Nurse'),
-- Audit Manager II
(1018, 'Father', 'Magpayo',   'Ernesto',   'Reyes',      'Farmer'),
(1018, 'Mother', 'Magpayo',   'Herminia',  'Santos',     'Housewife'),
(1018, 'Spouse', 'Magpayo',   'Eduardo',   'Villanueva', 'Engineer'),
-- Audit Manager III
(1019, 'Father', 'Gutierrez', 'Vicente',   'Villanueva', 'Tricycle Driver'),
(1019, 'Mother', 'Gutierrez', 'Perla',     'Garcia',     'Market Vendor'),
-- Audit Supervisor I
(1020, 'Father', 'Bautista',  'Andres',    'Santos',     'Carpenter'),
(1020, 'Mother', 'Bautista',  'Felicitas', 'Morales',    'Teacher'),
(1020, 'Spouse', 'Bautista',  'Rodrigo',   'Pascual',    'Accountant'),
-- Audit Supervisor II
(1021, 'Father', 'Dela Cruz', 'Crisanto',  'Morales',    'Security Guard'),
(1021, 'Mother', 'Dela Cruz', 'Teresita',  'Navarro',    'Housewife'),
-- Audit Supervisor III
(1022, 'Father', 'Lim',       'Alfonso',   'Ocampo',     'Businessman'),
(1022, 'Mother', 'Lim',       'Nenita',    'Dela Cruz',  'Housewife'),
-- Auditor on Probation
(1023, 'Father', 'Santos',    'Divino',    'Navarro',    'OFW'),
(1023, 'Mother', 'Santos',    'Felisa',    'Hernandez',  'Sales Clerk'),
-- Auditor I
(1024, 'Father', 'Ramos',     'Cornelio',  'Torres',     'Fisherman'),
(1024, 'Mother', 'Ramos',     'Leonora',   'Buenaventura','Housewife'),
-- Auditor II
(1025, 'Father', 'Perez',     'Nestor',    'Buenaventura','Farmer'),
(1025, 'Mother', 'Perez',     'Carmelita', 'Flores',     'Housewife'),
-- Auditor III
(1026, 'Father', 'Aquino',    'Rodrigo',   'Hernandez',  'Retired'),
(1026, 'Mother', 'Aquino',    'Milagros',  'Santos',     'Retired'),
(1026, 'Spouse', 'Aquino',    'Bernardo',  'Reyes',      'Teacher'),
-- Auditor IV
(1027, 'Father', 'Castillo',  'Hermogenes','Pascual',    'Electrician'),
(1027, 'Mother', 'Castillo',  'Caridad',   'Bautista',   'Dressmaker'),
(1027, 'Spouse', 'Castillo',  'Roselyn',   'Soriano',    'Nurse'),
-- Auditor V
(1028, 'Father', 'Soriano',   'Virgilio',  'Aguilar',    'Jeepney Driver'),
(1028, 'Mother', 'Soriano',   'Dolores',   'Lim',        'Housewife');

-- ============================================
-- B-7. EDUCATIONAL BACKGROUND (Audit)
-- ============================================
REPLACE INTO employee_education (employee_id, education_level, school_name, degree_course, year_graduated) VALUES
(1017, 'College',          'Manuel S. Enverga University Foundation','BS Accountancy',             '1995'),
(1017, 'Graduate Studies', 'University of the Philippines',          'Master in Management',       '2002'),
(1018, 'College',          'Polytechnic University of the Philippines','BS Accountancy',           '2002'),
(1018, 'Graduate Studies', 'De La Salle University',                 'MBA',                        '2009'),
(1019, 'College',          'Laguna State Polytechnic University',    'BS Accountancy',             '2004'),
(1020, 'College',          'Southern Luzon State University',        'BS Internal Auditing',       '2007'),
(1020, 'Graduate Studies', 'Manuel S. Enverga University Foundation','MBA',                        '2013'),
(1021, 'College',          'Quezon City University',                 'BS Accountancy',             '2009'),
(1022, 'College',          'Manuel S. Enverga University Foundation','BS Internal Auditing',       '2012'),
(1023, 'College',          'Southern Luzon State University',        'BS Accountancy',             '2023'),
(1024, 'College',          'Manuel S. Enverga University Foundation','BS Accountancy',             '2019'),
(1025, 'College',          'Laguna State Polytechnic University',    'BS Internal Auditing',       '2016'),
(1026, 'College',          'Polytechnic University of the Philippines','BS Accountancy',           '2014'),
(1027, 'College',          'Southern Luzon State University',        'BS Accountancy',             '2011'),
(1028, 'College',          'Manuel S. Enverga University Foundation','BS Internal Auditing',       '2009');

-- ============================================
-- B-8. WORK EXPERIENCE (Audit)
-- ============================================
REPLACE INTO employee_work_experience (employee_id, date_from, date_to, job_title, company_name, monthly_salary) VALUES
(1017, '1995-07-01', '2011-03-06', 'Senior Auditor',         'BDO Unibank Inc.',                  58000.00),
(1018, '2002-06-01', '2014-06-15', 'Internal Auditor',       'Quezon Province Cooperative Bank',  30000.00),
(1019, '2004-07-01', '2015-08-31', 'Audit Associate',        'Southern Quezon Audit Services',    26000.00),
(1020, '2007-06-01', '2017-02-19', 'Audit Staff',            'Metro Audit Partners',              23000.00),
(1021, '2009-06-01', '2018-07-08', 'Audit Clerk',            'Pacific Audit Group',               21000.00),
(1022, '2012-06-01', '2019-01-13', 'Junior Auditor',         'Lucena Commercial Audit Firm',      20000.00),
(1024, '2019-06-01', '2022-04-10', 'Audit Intern Staff',     'QP Financial Services',             17500.00),
(1025, '2016-06-01', '2021-08-22', 'Audit Support Staff',    'Southern Luzon Audit Corp.',        18000.00),
(1026, '2014-06-01', '2020-03-01', 'Internal Audit Aide',    'Raquel Trading Corp.',              16500.00),
(1027, '2011-06-01', '2018-11-04', 'Audit Clerk',            'Eastern Quezon Audit Group',        18500.00),
(1028, '2009-06-01', '2017-06-18', 'Clerical Auditor',       'Lucena Internal Audit Services',    16000.00);

-- ============================================
-- B-9. GOVERNMENT IDs (Audit)
-- ============================================
REPLACE INTO employee_government_ids (employee_id, sss_number, philhealth_number, pagibig_number, tin_number) VALUES
(1017, '34-1000017-8', '12-100000018-0', '1210-1000-0018', '100-000-018-000'),
(1018, '34-1000018-9', '12-100000019-0', '1210-1000-0019', '100-000-019-000'),
(1019, '34-1000019-0', '12-100000020-0', '1210-1000-0020', '100-000-020-000'),
(1020, '34-1000020-1', '12-100000021-0', '1210-1000-0021', '100-000-021-000'),
(1021, '34-1000021-2', '12-100000022-0', '1210-1000-0022', '100-000-022-000'),
(1022, '34-1000022-3', '12-100000023-0', '1210-1000-0023', '100-000-023-000'),
(1023, '34-1000023-4', '12-100000024-0', '1210-1000-0024', '100-000-024-000'),
(1024, '34-1000024-5', '12-100000025-0', '1210-1000-0025', '100-000-025-000'),
(1025, '34-1000025-6', '12-100000026-0', '1210-1000-0026', '100-000-026-000'),
(1026, '34-1000026-7', '12-100000027-0', '1210-1000-0027', '100-000-027-000'),
(1027, '34-1000027-8', '12-100000028-0', '1210-1000-0028', '100-000-028-000'),
(1028, '34-1000028-9', '12-100000029-0', '1210-1000-0029', '100-000-029-000');

-- ============================================
-- B-10. DISCLOSURES (Audit)
-- ============================================
REPLACE INTO employee_disclosures (employee_id, is_related_to_company, has_admin_offense, has_criminal_charge) VALUES
(1017, 0, 0, 0), (1018, 0, 0, 0), (1019, 0, 0, 0),
(1020, 0, 0, 0), (1021, 0, 0, 0), (1022, 0, 0, 0),
(1023, 0, 0, 0), (1024, 0, 0, 0), (1025, 0, 0, 0),
(1026, 0, 0, 0), (1027, 0, 0, 0), (1028, 0, 0, 0);

SET FOREIGN_KEY_CHECKS = 1;
