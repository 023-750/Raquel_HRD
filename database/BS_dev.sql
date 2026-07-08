-- ============================================
-- Seed: Business Development Employees
-- Branch: 102 (Raquel Pawnshop Main Office)
--
-- Employee IDs: 1029-1032 | Codes: BD-001-BD-004
-- Department ID: 3 | Job Title IDs: 300, 301, 302, 303
-- ============================================

USE raquel_hris;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- 1. EMPLOYEES (Business Development)
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
-- 1029: Business Development Officer I (Manager)
(1029, 'BD-001', 'Carlos', 'Valenzuela', 'Mendoza',
 '2018-05-10', '1984-09-18', 'Lucena City, Quezon',
 'Male', 'Married',
 300, 'Business Development Officer I',
 3, 3, 102, 'Regular', 'Full-time'),

-- 1030: Business Development Staff I (R&F)
(1030, 'BD-002', 'Patricia', 'Mercado', 'Santos',
 '2022-06-01', '1996-11-04', 'Tayabas City, Quezon',
 'Female', 'Single',
 302, 'Business Development Staff I',
 3, 5, 102, 'Regular', 'Full-time'),

-- 1031: Business Development Staff II (R&F)
(1031, 'BD-003', 'Ramil', 'Salazar', 'Bautista',
 '2021-08-16', '1994-05-12', 'Lucena City, Quezon',
 'Male', 'Single',
 303, 'Business Development Staff II',
 3, 5, 102, 'Regular', 'Full-time'),

-- 1032: Business Development Staff on Training (R&F)
(1032, 'BD-004', 'Joy', 'Dela Cruz', 'Santos',
 '2025-11-01', '2002-02-14', 'Candelaria, Quezon',
 'Female', 'Single',
 301, 'Business Development Staff on Training',
 3, 5, 102, 'Trainee', 'Full-time');

-- ============================================
-- 2. EMPLOYEE CONTACTS
-- ============================================
REPLACE INTO employee_contacts (employee_id, mobile_number, personal_email) VALUES
(1029, '09170000129', 'carlos.valenzuela@example.com'),
(1030, '09170000130', 'patricia.mercado@example.com'),
(1031, '09170000131', 'ramil.salazar@example.com'),
(1032, '09170000132', 'joy.delacruz@example.com');

-- ============================================
-- 3. EMPLOYEE DETAILS (Physical & Citizenship)
-- ============================================
REPLACE INTO employee_details (employee_id, height_m, weight_kg, blood_type, citizenship) VALUES
(1029, 1.75, 72.0, 'A+',  'Filipino'),
(1030, 1.62, 53.0, 'O+',  'Filipino'),
(1031, 1.70, 68.0, 'B+',  'Filipino'),
(1032, 1.58, 51.0, 'AB+', 'Filipino');

-- ============================================
-- 4. ADDRESSES
-- ============================================
REPLACE INTO employee_addresses (employee_id, address_type, barangay, city, province, zip_code) VALUES
(1029, 'Residential', 'Lalig',          'Tiaong',       'Quezon', '4325'),
(1029, 'Permanent',   'Lalig',          'Tiaong',       'Quezon', '4325'),
(1030, 'Residential', 'San Isidro',     'Tayabas City', 'Quezon', '4327'),
(1030, 'Permanent',   'San Isidro',     'Tayabas City', 'Quezon', '4327'),
(1031, 'Residential', 'Poblacion',      'Sariaya',      'Quezon', '4322'),
(1031, 'Permanent',   'Poblacion',      'Sariaya',      'Quezon', '4322'),
(1032, 'Residential', 'Poblacion',      'Candelaria',   'Quezon', '4323'),
(1032, 'Permanent',   'Poblacion',      'Candelaria',   'Quezon', '4323');

-- ============================================
-- 5. EMERGENCY CONTACTS
-- ============================================
REPLACE INTO employee_emergency_contacts (employee_id, contact_name, relationship, contact_number) VALUES
(1029, 'Elena Valenzuela',    'Spouse',  '09170001029'),
(1030, 'Mercedes Mercado',    'Mother',  '09170001030'),
(1031, 'Ernesto Salazar',     'Father',  '09170001031'),
(1032, 'Teresita Dela Cruz',  'Mother',  '09170001032');

-- ============================================
-- 6. FAMILY BACKGROUND
-- ============================================
REPLACE INTO employee_family (employee_id, member_type, surname, first_name, middle_name, occupation) VALUES
-- Manager (BD Officer I)
(1029, 'Father', 'Valenzuela', 'Ernesto',  'Mendoza',   'Retired'),
(1029, 'Mother', 'Valenzuela', 'Lydia',    'Santos',    'Retired'),
(1029, 'Spouse', 'Valenzuela', 'Elena',    'Pascual',   'Bank Teller'),
-- Staff I
(1030, 'Father', 'Mercado',    'Rogelio',  'Santos',    'Businessman'),
(1030, 'Mother', 'Mercado',    'Mercedes', 'Dela Cruz', 'Housewife'),
-- Staff II
(1031, 'Father', 'Salazar',    'Ernesto',  'Bautista',  'Farmer'),
(1031, 'Mother', 'Salazar',    'Elena',    'Reyes',     'Housewife'),
-- Staff on Training
(1032, 'Father', 'Dela Cruz',  'Crisanto', 'Santos',    'Driver'),
(1032, 'Mother', 'Dela Cruz',  'Teresita', 'Morales',   'Housewife');

-- ============================================
-- 7. EDUCATIONAL BACKGROUND
-- ============================================
REPLACE INTO employee_education (employee_id, education_level, school_name, degree_course, year_graduated) VALUES
(1029, 'College',          'Southern Luzon State University',        'BS Business Administration', '2006'),
(1029, 'Graduate Studies', 'Ateneo de Manila University',            'MBA',                        '2012'),
(1030, 'College',          'Manuel S. Enverga University Foundation','BS Business Management',     '2018'),
(1031, 'College',          'Southern Luzon State University',        'BS Business Administration', '2015'),
(1032, 'College',          'Southern Luzon State University',        'BS Marketing',               '2023');

-- ============================================
-- 8. WORK EXPERIENCE
-- ============================================
REPLACE INTO employee_work_experience (employee_id, date_from, date_to, job_title, company_name, monthly_salary) VALUES
(1029, '2006-06-01', '2012-05-31', 'Marketing Staff',        'Lucena Retail Group',               22000.00),
(1029, '2012-06-01', '2018-04-30', 'Business Dev Specialist','QP Corporate Expansion Inc.',       38000.00),
(1030, '2018-06-01', '2022-05-15', 'Business Dev Assistant', 'Metro Growth Partners',             20000.00),
(1031, '2015-06-01', '2021-07-31', 'Business Dev Associate', 'Southern Quezon Sales',             18000.00);

-- ============================================
-- 9. GOVERNMENT IDs
-- ============================================
REPLACE INTO employee_government_ids (employee_id, sss_number, philhealth_number, pagibig_number, tin_number) VALUES
(1029, '34-1000029-0', '12-100000030-0', '1210-1000-0030', '100-000-030-000'),
(1030, '34-1000030-1', '12-100000031-0', '1210-1000-0031', '100-000-031-000'),
(1031, '34-1000031-2', '12-100000032-0', '1210-1000-0032', '100-000-032-000'),
(1032, '34-1000032-3', '12-100000033-0', '1210-1000-0033', '100-000-033-000');

-- ============================================
-- 10. DISCLOSURES
-- ============================================
REPLACE INTO employee_disclosures (employee_id, is_related_to_company, has_admin_offense, has_criminal_charge) VALUES
(1029, 0, 0, 0),
(1030, 0, 0, 0),
(1031, 0, 0, 0),
(1032, 0, 0, 0);

-- ============================================
-- 11. PORTAL ACCOUNTS
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
(1029, 'BD-001', 'BD-001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Carlos Mendoza Valenzuela', 'Employee', 102, 1, 0, NOW()),
(1030, 'BD-002', 'BD-002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Patricia Santos Mercado', 'Employee', 102, 1, 0, NOW()),
(1031, 'BD-003', 'BD-003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ramil Bautista Salazar', 'Employee', 102, 1, 0, NOW()),
(1032, 'BD-004', 'BD-004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Joy Santos Dela Cruz', 'Employee', 102, 1, 0, NOW());

SET FOREIGN_KEY_CHECKS = 1;
