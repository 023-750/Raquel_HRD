-- ============================================
-- Seed: Compliance Department Employees
-- Branch: 102 (Raquel Pawnshop Main Office)
--
-- Employee IDs: 1033-1038 | Codes: CO-001-CO-006
-- Department ID: 4 | Job Title IDs: 400-405
-- ============================================

USE raquel_hris;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- 1. EMPLOYEES (Compliance)
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
(1033, 'CO-001', 'Rowena', 'Villanueva', 'Aquino',
 '2016-03-07', '1983-06-14', 'Lucena City, Quezon',
 'Female', 'Married',
 400, 'Compliance Supervisor I',
 4, 4, 102, 'Regular', 'Full-time'),

(1034, 'CO-002', 'Rodrigo', 'Macaraeg', 'Bautista',
 '2019-07-22', '1988-02-28', 'Tayabas City, Quezon',
 'Male', 'Married',
 401, 'Compliance Supervisor II',
 4, 4, 102, 'Regular', 'Full-time'),

(1035, 'CO-003', 'Leilani', 'Fontanilla', 'Cruz',
 '2021-01-11', '1991-09-03', 'Sariaya, Quezon',
 'Female', 'Single',
 402, 'Compliance Supervisor III',
 4, 4, 102, 'Regular', 'Full-time'),

(1036, 'CO-004', 'Emmanuel', 'Dalisay', 'Reyes',
 '2022-08-15', '1997-04-20', 'Candelaria, Quezon',
 'Male', 'Single',
 403, 'Compliance Staff I',
 4, 5, 102, 'Regular', 'Full-time'),

(1037, 'CO-005', 'Maricel', 'Paglinawan', 'Santos',
 '2023-02-01', '1999-11-30', 'Lucban, Quezon',
 'Female', 'Single',
 404, 'Compliance Staff II',
 4, 5, 102, 'Regular', 'Full-time'),

(1038, 'CO-006', 'Arnel', 'Tolentino', 'Flores',
 '2020-05-18', '1994-07-08', 'Tiaong, Quezon',
 'Male', 'Single',
 405, 'Compliance Staff III',
 4, 5, 102, 'Regular', 'Full-time');

-- ============================================
-- 2. REPORTS_TO CHAIN
-- Supervisors II & III + all R&F report to Supervisor I
-- ============================================
UPDATE employees SET reports_to = 1033 WHERE employee_id IN (1034, 1035, 1036, 1037, 1038);

-- ============================================
-- 3. EMPLOYEE CONTACTS
-- ============================================
REPLACE INTO employee_contacts (employee_id, mobile_number, personal_email) VALUES
(1033, '09170000133', 'rowena.villanueva@example.com'),
(1034, '09170000134', 'rodrigo.macaraeg@example.com'),
(1035, '09170000135', 'leilani.fontanilla@example.com'),
(1036, '09170000136', 'emmanuel.dalisay@example.com'),
(1037, '09170000137', 'maricel.paglinawan@example.com'),
(1038, '09170000138', 'arnel.tolentino@example.com');

-- ============================================
-- 4. EMPLOYEE DETAILS (Physical & Citizenship)
-- ============================================
REPLACE INTO employee_details (employee_id, height_m, weight_kg, blood_type, citizenship) VALUES
(1033, 1.60, 55.0, 'O+',  'Filipino'),
(1034, 1.73, 71.0, 'A+',  'Filipino'),
(1035, 1.58, 50.0, 'B+',  'Filipino'),
(1036, 1.70, 65.0, 'AB+', 'Filipino'),
(1037, 1.62, 52.0, 'O-',  'Filipino'),
(1038, 1.68, 68.0, 'A-',  'Filipino');

-- ============================================
-- 5. ADDRESSES
-- ============================================
REPLACE INTO employee_addresses (employee_id, address_type, barangay, city, province, zip_code) VALUES
(1033, 'Residential', 'Ibabang Dupay',  'Lucena City',  'Quezon', '4301'),
(1033, 'Permanent',   'Ibabang Dupay',  'Lucena City',  'Quezon', '4301'),
(1034, 'Residential', 'Lalig',          'Tiaong',       'Quezon', '4325'),
(1034, 'Permanent',   'Lalig',          'Tiaong',       'Quezon', '4325'),
(1035, 'Residential', 'Poblacion',      'Sariaya',      'Quezon', '4322'),
(1035, 'Permanent',   'Poblacion',      'Sariaya',      'Quezon', '4322'),
(1036, 'Residential', 'Poblacion',      'Candelaria',   'Quezon', '4323'),
(1036, 'Permanent',   'Poblacion',      'Candelaria',   'Quezon', '4323'),
(1037, 'Residential', 'Ilayang Dupay',  'Lucena City',  'Quezon', '4301'),
(1037, 'Permanent',   'San Antonio',    'Lucban',       'Quezon', '4328'),
(1038, 'Residential', 'San Roque',      'Tiaong',       'Quezon', '4325'),
(1038, 'Permanent',   'San Roque',      'Tiaong',       'Quezon', '4325');

-- ============================================
-- 6. EMERGENCY CONTACTS
-- ============================================
REPLACE INTO employee_emergency_contacts (employee_id, contact_name, relationship, contact_number) VALUES
(1033, 'Nestor Villanueva',   'Spouse', '09170001033'),
(1034, 'Gloria Macaraeg',     'Spouse', '09170001034'),
(1035, 'Crisanta Fontanilla', 'Mother', '09170001035'),
(1036, 'Teresita Dalisay',    'Mother', '09170001036'),
(1037, 'Roberto Paglinawan',  'Father', '09170001037'),
(1038, 'Nelia Tolentino',     'Mother', '09170001038');

-- ============================================
-- 7. FAMILY BACKGROUND
-- ============================================
REPLACE INTO employee_family (employee_id, member_type, surname, first_name, middle_name, occupation) VALUES
(1033, 'Father', 'Villanueva', 'Ernesto',   'Aquino',   'Retired Teacher'),
(1033, 'Mother', 'Villanueva', 'Caridad',   'Dela Cruz','Retired Government Employee'),
(1033, 'Spouse', 'Villanueva', 'Nestor',    'Ramos',    'Civil Engineer'),
(1034, 'Father', 'Macaraeg',   'Edilberto', 'Bautista', 'Farmer'),
(1034, 'Mother', 'Macaraeg',   'Concepcion','Navarro',  'Housewife'),
(1034, 'Spouse', 'Macaraeg',   'Gloria',    'Mendoza',  'Nurse'),
(1035, 'Father', 'Fontanilla', 'Romulo',    'Cruz',     'Jeepney Driver'),
(1035, 'Mother', 'Fontanilla', 'Crisanta',  'Valdez',   'Market Vendor'),
(1036, 'Father', 'Dalisay',    'Rodrigo',   'Reyes',    'Security Guard'),
(1036, 'Mother', 'Dalisay',    'Teresita',  'Ocampo',   'Housewife'),
(1037, 'Father', 'Paglinawan', 'Roberto',   'Santos',   'Carpenter'),
(1037, 'Mother', 'Paglinawan', 'Ligaya',    'Flores',   'Dressmaker'),
(1038, 'Father', 'Tolentino',  'Ernesto',   'Flores',   'Electrician'),
(1038, 'Mother', 'Tolentino',  'Nelia',     'Garcia',   'Teacher');

-- ============================================
-- 8. EDUCATIONAL BACKGROUND
-- ============================================
REPLACE INTO employee_education (employee_id, education_level, school_name, degree_course, year_graduated) VALUES
(1033, 'College',          'Manuel S. Enverga University Foundation', 'BS Criminology',         '2005'),
(1033, 'Graduate Studies', 'Lyceum of the Philippines - Batangas',    'Master in Public Admin', '2011'),
(1034, 'College',          'Southern Luzon State University',          'BS Criminology',         '2010'),
(1035, 'College',          'University of Perpetual Help System',      'BS Legal Management',    '2013'),
(1036, 'College',          'Manuel S. Enverga University Foundation',  'BS Criminology',         '2019'),
(1037, 'College',          'Southern Luzon State University',          'BS Legal Management',    '2021'),
(1038, 'College',          'Quezon City University',                   'BS Criminology',         '2016');

-- ============================================
-- 9. WORK EXPERIENCE
-- ============================================
REPLACE INTO employee_work_experience (employee_id, date_from, date_to, job_title, company_name, monthly_salary) VALUES
(1033, '2005-06-01', '2016-02-28', 'Compliance Officer',     'Eastern Savings Bank',         35000.00),
(1034, '2010-07-01', '2019-06-30', 'Compliance Analyst',     'SB Finance Corporation',       27000.00),
(1035, '2013-08-01', '2020-12-31', 'Legal Compliance Staff', 'Rural Bank of Candelaria',     22000.00),
(1036, '2019-06-01', '2022-07-31', 'Compliance Assistant',   'Pawnshop Network Cooperative', 18000.00),
(1037, '2021-07-01', '2023-01-15', 'Compliance Trainee',     'QuickCash Financing Co.',      16000.00),
(1038, '2016-06-01', '2020-04-30', 'Risk & Compliance Staff','Palawan Pawnshop Inc.',         20000.00);

-- ============================================
-- 10. GOVERNMENT IDs
-- ============================================
REPLACE INTO employee_government_ids (employee_id, sss_number, philhealth_number, pagibig_number, tin_number) VALUES
(1033, '34-1000033-4', '12-100000034-0', '1210-1000-0034', '100-000-034-000'),
(1034, '34-1000034-5', '12-100000035-0', '1210-1000-0035', '100-000-035-000'),
(1035, '34-1000035-6', '12-100000036-0', '1210-1000-0036', '100-000-036-000'),
(1036, '34-1000036-7', '12-100000037-0', '1210-1000-0037', '100-000-037-000'),
(1037, '34-1000037-8', '12-100000038-0', '1210-1000-0038', '100-000-038-000'),
(1038, '34-1000038-9', '12-100000039-0', '1210-1000-0039', '100-000-039-000');

-- ============================================
-- 11. DISCLOSURES
-- ============================================
REPLACE INTO employee_disclosures (employee_id, is_related_to_company, has_admin_offense, has_criminal_charge) VALUES
(1033, 0, 0, 0),
(1034, 0, 0, 0),
(1035, 0, 0, 0),
(1036, 0, 0, 0),
(1037, 0, 0, 0),
(1038, 0, 0, 0);

-- ============================================
-- 12. PORTAL ACCOUNTS (default password: password)
-- ============================================
REPLACE INTO users (
    employee_id,
    username,
    email,
    password_hash,
    full_name,
    role,
    branch_id,
    is_active,
    first_login_completed,
    created_at
) VALUES
(1033, 'CO-001', 'CO-001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Rowena Aquino Villanueva',  'Employee', 102, 1, 0, NOW()),
(1034, 'CO-002', 'CO-002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Rodrigo Bautista Macaraeg', 'Employee', 102, 1, 0, NOW()),
(1035, 'CO-003', 'CO-003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Leilani Cruz Fontanilla',   'Employee', 102, 1, 0, NOW()),
(1036, 'CO-004', 'CO-004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Emmanuel Reyes Dalisay',    'Employee', 102, 1, 0, NOW()),
(1037, 'CO-005', 'CO-005', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Maricel Santos Paglinawan', 'Employee', 102, 1, 0, NOW()),
(1038, 'CO-006', 'CO-006', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Arnel Flores Tolentino',    'Employee', 102, 1, 0, NOW());

SET FOREIGN_KEY_CHECKS = 1;
