USE raquel_hris;
SET FOREIGN_KEY_CHECKS = 0;

-- ==========================================================
-- PERFORMANCE EVALUATION TEST SEED (MARKETING & HRD)
-- ==========================================================

-- ---------------------------------------------------------
-- 1. EMPLOYEES (Marketing & HRD Teams)
-- ---------------------------------------------------------
REPLACE INTO employees (employee_id, employee_code, first_name, last_name, middle_name, hire_date, job_title_id, job_title, department_id, rank_category_id, branch_id, employment_status, employment_type) VALUES
-- Marketing Team
(201, 'MKT-001', 'Marcus', 'Reyes', 'Vidal', '2015-03-10', 900, 'Marketing Manager I', 9, 3, 102, 'Regular', 'Full-time'),
(202, 'MKT-002', 'Sarah', 'Miller', 'Jane', '2018-06-15', 902, 'Marketing Supervisor I', 9, 4, 102, 'Regular', 'Full-time'),
(203, 'MKT-003', 'Kevin', 'Santiago', 'Alonzo', '2021-11-20', 905, 'Marketing Staff I', 9, 5, 102, 'Regular', 'Full-time'),
-- HRD Team
(301, 'HRD-002', 'Patricia', 'Gomez', 'Luna', '2017-02-14', 705, 'HR Supervisor I', 7, 4, 102, 'Regular', 'Full-time'),
(302, 'HRD-003', 'Miguel', 'Torres', 'Cruz', '2022-01-05', 711, 'HR Staff I', 7, 5, 102, 'Regular', 'Full-time');

-- ---------------------------------------------------------
-- 2. USERS (Portal Accounts)
-- ---------------------------------------------------------
REPLACE INTO users (user_id, employee_id, username, email, full_name, password_hash, role, branch_id, is_active) VALUES
(201, 201, 'marcus.reyes', 'marcus.reyes@example.com', 'Marcus V. Reyes', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'HR Manager', 102, 1), -- Manager Role
(202, 202, 'sarah.miller', 'sarah.miller@example.com', 'Sarah J. Miller', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'HR Supervisor', 102, 1), -- Supervisor Role
(203, 203, 'kevin.santiago', 'kevin.santiago@example.com', 'Kevin A. Santiago', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Employee', 102, 1),
(301, 301, 'patricia.gomez', 'patricia.gomez@example.com', 'Patricia L. Gomez', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'HR Supervisor', 102, 1),
(302, 302, 'miguel.torres', 'miguel.torres@example.com', 'Miguel C. Torres', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'HR Staff', 102, 1);

-- ---------------------------------------------------------
-- 3. EMPLOYEE CONTACTS (Required for Portal Validation)
-- ---------------------------------------------------------
REPLACE INTO employee_contacts (employee_id, personal_email, mobile_number, telephone_number) VALUES
(201, 'marcus.reyes@example.com', '09171234567', '888-1001'),
(202, 'sarah.miller@example.com', '09181234567', '888-1002'),
(203, 'kevin.santiago@example.com', '09191234567', '888-1003'),
(301, 'patricia.gomez@example.com', '09201234567', '888-3001'),
(302, 'miguel.torres@example.com', '09211234567', '888-3002');

-- ---------------------------------------------------------
-- 4. EMPLOYEE DETAILS (Physical & Citizenship)
-- ---------------------------------------------------------
REPLACE INTO employee_details (employee_id, height_m, weight_kg, blood_type, citizenship) VALUES
(201, 1.75, 75.0, 'O+', 'Filipino'),
(202, 1.62, 58.0, 'A+', 'Filipino'),
(203, 1.70, 65.0, 'B+', 'Filipino'),
(301, 1.58, 52.0, 'AB+', 'Filipino'),
(302, 1.72, 70.0, 'O-', 'Filipino');

-- ---------------------------------------------------------
-- 5. FAMILY BACKGROUND
-- ---------------------------------------------------------
REPLACE INTO employee_family (employee_id, member_type, surname, first_name, middle_name, occupation) VALUES
(203, 'Father', 'Santiago', 'Roberto', 'Perez', 'Engineer'),
(203, 'Mother', 'Santiago', 'Maria', 'Alonzo', 'Teacher'),
(203, 'Spouse', 'Santiago', 'Ana', 'Ramos', 'Nurse');

REPLACE INTO employee_children (employee_id, surname, first_name, middle_name, date_of_birth) VALUES
(203, 'Santiago', 'Kevin Jr.', 'Ramos', '2018-05-12');

-- ---------------------------------------------------------
-- 6. EDUCATIONAL BACKGROUND
-- ---------------------------------------------------------
REPLACE INTO employee_education (employee_id, education_level, school_name, degree_course, year_graduated) VALUES
(201, 'College', 'University of the Philippines', 'BS Business Administration', '2010'),
(202, 'College', 'De La Salle University', 'BS Marketing Management', '2014'),
(203, 'College', 'Polytechnic University of the Philippines', 'BS Advertising', '2019');

-- ---------------------------------------------------------
-- 7. WORK EXPERIENCE
-- ---------------------------------------------------------
REPLACE INTO employee_work_experience (employee_id, date_from, date_to, job_title, company_name, monthly_salary) VALUES
(203, '2019-06-01', '2021-10-30', 'Junior Graphic Designer', 'Creative Agency Inc.', 25000.00);

-- ---------------------------------------------------------
-- 8. TRAININGS & SEMINARS
-- ---------------------------------------------------------
REPLACE INTO employee_trainings (employee_id, training_title, conducted_by, no_of_hours) VALUES
(203, 'Digital Marketing Essentials', 'Google Academy', 40.0),
(202, 'Advanced Leadership Workshop', 'HR Management Association', 24.0);

-- ---------------------------------------------------------
-- 9. DISCLOSURES
-- ---------------------------------------------------------
REPLACE INTO employee_disclosures (employee_id, is_related_to_company, has_admin_offense, has_criminal_charge) VALUES
(201, 0, 0, 0), (202, 0, 0, 0), (203, 0, 0, 0), (301, 0, 0, 0), (302, 0, 0, 0);

-- ---------------------------------------------------------
-- 10. GOVERNMENT IDS
-- ---------------------------------------------------------
REPLACE INTO employee_government_ids (employee_id, sss_number, philhealth_number, pagibig_number, tin_number) VALUES
(201, '33-1234567-1', '12-987654321-0', '1210-5566-7788', '123-456-789-000'),
(202, '33-2234567-2', '12-887654321-1', '1210-4466-7788', '223-456-789-001'),
(203, '33-3234567-3', '12-787654321-2', '1210-3366-7788', '323-456-789-002');

-- ---------------------------------------------------------
-- 11. ADDRESSES & EMERGENCY CONTACTS
-- ---------------------------------------------------------
REPLACE INTO employee_addresses (employee_id, address_type, barangay, city, province) VALUES
(203, 'Residential', 'San Isidro', 'Tayabas City', 'Quezon'),
(203, 'Permanent', 'San Isidro', 'Tayabas City', 'Quezon');

REPLACE INTO employee_emergency_contacts (employee_id, contact_name, relationship, contact_number) VALUES
(203, 'Ana Ramos Santiago', 'Spouse', '09123334445');

-- ---------------------------------------------------------
-- 12. SALN INFORMATION (Real/Personal Property/Liabilities)
-- ---------------------------------------------------------
REPLACE INTO employee_real_properties (employee_id, description, kind, acquisition_cost) VALUES
(201, 'Residential Lot', 'Land', 1500000.00);

REPLACE INTO employee_personal_properties (employee_id, description, acquisition_cost) VALUES
(203, 'Toyota Vios 2020', 850000.00);

REPLACE INTO employee_liabilities (employee_id, nature_of_liability, creditor_name, outstanding_balance) VALUES
(203, 'Car Loan', 'BDO Unibank', 450000.00);

-- ---------------------------------------------------------
-- 13. CHARACTER REFERENCES
-- ---------------------------------------------------------
REPLACE INTO employee_references (employee_id, reference_name, reference_address, reference_telephone) VALUES
(203, 'Dr. Juan Dela Cruz', 'Quezon City', '02-8123-4567');

SET FOREIGN_KEY_CHECKS = 1;
