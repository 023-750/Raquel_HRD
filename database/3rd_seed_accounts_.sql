USE raquel_hris;
SET FOREIGN_KEY_CHECKS = 0;

-- ====================================
-- 1. System Administrator (Standalone)
-- ====================================
REPLACE INTO users (user_id, employee_id, username, email, full_name, password_hash, role, branch_id, is_active, created_at) VALUES
(1, NULL, 'admin', 'admin@example.com', 'System Admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 102, 1, NOW());

-- ====================================
-- 2. EMPLOYEES (Marketing & HRD Teams)
-- ====================================
REPLACE INTO employees (employee_id, employee_code, first_name, last_name, middle_name, hire_date, date_of_birth, place_of_birth, gender, civil_status, job_title_id, job_title, department_id, rank_category_id, branch_id, employment_status, employment_type, profile_picture) VALUES
-- HR Admin
(101, 'HRD-001', 'Elena', 'Delgado', 'Santos', '2020-01-15', '1990-05-15', 'Tayabas, Quezon', 'Female', 'Married', 700, 'HR Manager I', 7, 3, 102, 'Regular', 'Full-time', 'malupiton.jpg'),
-- Marketing Team
(201, 'MKT-001', 'Marcus', 'Reyes', 'Vidal', '2015-03-10', '1988-07-22', 'Manila, Metro Manila', 'Male', 'Single', 900, 'Marketing Manager I', 9, 3, 102, 'Regular', 'Full-time', 'black.jpg'),
(202, 'MKT-002', 'Sarah', 'Miller', 'Jane', '2018-06-15', '1992-03-14', 'Makati, Metro Manila', 'Female', 'Married', 902, 'Marketing Supervisor I', 9, 4, 102, 'Regular', 'Full-time', 'bossdogs.jpg'),
(203, 'MKT-003', 'Kevin', 'Santiago', 'Alonzo', '2021-11-20', '1998-09-30', 'Quezon City, Metro Manila', 'Male', 'Married', 905, 'Marketing Staff I', 9, 5, 102, 'Regular', 'Full-time', 'chano.jpg'),
-- HRD Team
(301, 'HRD-002', 'Patricia', 'Gomez', 'Luna', '2017-02-14', '1989-11-08', 'Pasig, Metro Manila', 'Female', 'Widowed', 705, 'HR Supervisor I', 7, 4, 102, 'Regular', 'Full-time', 'cat.jpg'),
(302, 'HRD-003', 'Miguel', 'Torres', 'Cruz', '2022-01-05', '2000-04-17', 'Caloocan, Metro Manila', 'Male', 'Single', 711, 'HR Staff I', 7, 5, 102, 'Regular', 'Full-time', 'xplit.jpg');

-- =====================================================
-- 3. EMPLOYEE CONTACTS (Required for Portal Validation)
-- =====================================================
REPLACE INTO employee_contacts (employee_id, personal_email, mobile_number, telephone_number) VALUES
(101, 'elena.delgado@example.com', '09161234567', '888-0101'),
(201, 'marcus.reyes@example.com', '09171234567', '888-1001'),
(202, 'sarah.miller@example.com', '09181234567', '888-1002'),
(203, 'kevin.santiago@example.com', '09191234567', '888-1003'),
(301, 'patricia.gomez@example.com', '09201234567', '888-3001'),
(302, 'miguel.torres@example.com', '09211234567', '888-3002');

-- ============================================
-- 3. EMPLOYEE DETAILS (Physical & Citizenship)
-- ============================================
REPLACE INTO employee_details (employee_id, height_m, weight_kg, blood_type, citizenship) VALUES
(101, 1.68, 62.0, 'A+', 'Filipino'),
(201, 1.75, 75.0, 'O+', 'Filipino'),
(202, 1.62, 58.0, 'A+', 'Filipino'),
(203, 1.70, 65.0, 'B+', 'Filipino'),
(301, 1.58, 52.0, 'AB+', 'Filipino'),
(302, 1.72, 70.0, 'O-', 'Filipino');

-- ====================
-- 4. FAMILY BACKGROUND
-- ====================
REPLACE INTO employee_family (employee_id, member_type, surname, first_name, middle_name, occupation) VALUES
(101, 'Father', 'Delgado', 'Santiago', 'Rodriguez', 'Retired Government Officer'),
(101, 'Mother', 'Delgado', 'Carmen', 'Santos', 'Retired Teacher'),
(101, 'Spouse', 'Delgado', 'Marco', 'Antonio', 'Civil Engineer'),
(201, 'Father', 'Reyes', 'Vicente', 'Santos', 'Retired Engineer'),
(201, 'Mother', 'Reyes', 'Magdalena', 'Vidal', 'Teacher'),
(202, 'Father', 'Miller', 'Robert', 'James', 'Businessman'),
(202, 'Mother', 'Miller', 'Jennifer', 'Santos', 'Accountant'),
(202, 'Spouse', 'Miller', 'David', 'Gray', 'Software Developer'),
(203, 'Father', 'Santiago', 'Roberto', 'Perez', 'Engineer'),
(203, 'Mother', 'Santiago', 'Maria', 'Alonzo', 'Teacher'),
(203, 'Spouse', 'Santiago', 'Ana', 'Ramos', 'Nurse'),
(301, 'Father', 'Gomez', 'Carlos', 'Reyes', 'Businessman'),
(301, 'Mother', 'Gomez', 'Rosario', 'Luna', 'Educator'),
(301, 'Spouse', 'Gomez', 'Fernando', 'Valdez', 'Architect'),
(302, 'Father', 'Torres', 'Manuel', 'Pascual', 'Government Employee'),
(302, 'Mother', 'Torres', 'Rosa', 'Cruz', 'Nurse');

REPLACE INTO employee_children (employee_id, surname, first_name, middle_name, date_of_birth) VALUES
(101, 'Delgado', 'Sofia', 'Santos', '2018-03-04'),
(201, 'Reyes', 'Marcus Jr.', 'Vidal', '2017-08-15'),
(202, 'Miller', 'Sarah', 'Gray', '2016-03-22'),
(202, 'Miller', 'Jennifer', 'Gray', '2019-07-10'),
(203, 'Santiago', 'Kevin Jr.', 'Ramos', '2018-05-12'),
(301, 'Gomez', 'Patricia', 'Valdez', '2015-11-28'),
(302, 'Torres', 'Miguel Jr.', 'Santos', '2020-02-14');

REPLACE INTO employee_siblings (employee_id, surname, first_name, middle_name, date_of_birth) VALUES
(101, 'Delgado', 'Miguel', 'Santos', '1992-08-10'),
(201, 'Reyes', 'Antonio', 'Vidal', '1978-05-20'),
(202, 'Miller', 'James', 'Andrew', '1992-09-15'),
(301, 'Gomez', 'Gabriela', 'Luna', '1988-12-03'),
(302, 'Torres', 'Emmanuel', 'Cruz', '1995-06-18');

-- =========================
-- 5. EDUCATIONAL BACKGROUND
-- =========================
REPLACE INTO employee_education (employee_id, education_level, school_name, degree_course, year_graduated) VALUES
(101, 'College', 'University of the Philippines', 'BS Psychology', '2012'),
(101, 'Graduate Studies', 'Ateneo de Manila University', 'MA Human Resource Management', '2016'),
(201, 'College', 'University of the Philippines', 'BS Business Administration', '2010'),
(201, 'Graduate Studies', 'University of the Philippines', 'MA Organizational Psychology', '2013'),
(202, 'College', 'De La Salle University', 'BS Marketing Management', '2014'),
(203, 'College', 'Polytechnic University of the Philippines', 'BS Advertising', '2019'),
(301, 'College', 'Ateneo de Manila University', 'BS Management', '2012'),
(302, 'College', 'University of Santo Tomas', 'BS Human Resource Management', '2019');

-- ==================
-- 6. WORK EXPERIENCE
-- ==================
REPLACE INTO employee_work_experience (employee_id, date_from, date_to, job_title, company_name, monthly_salary) VALUES
(101, '2012-07-01', '2020-01-14', 'HR Specialist', 'Business Process Outsourcing Inc.', 35000.00),
(201, '2010-07-01', '2015-02-28', 'Marketing Coordinator', 'Digital Solutions Corp', 28000.00),
(202, '2014-08-15', '2018-05-31', 'Brand Associate', 'Advertising Plus Inc.', 32000.00),
(203, '2019-06-01', '2021-10-30', 'Junior Graphic Designer', 'Creative Agency Inc.', 25000.00),
(301, '2012-09-01', '2017-03-31', 'HR Coordinator', 'Global Staffing Solutions', 30000.00),
(302, '2019-02-01', '2022-12-15', 'HR Assistant', 'Talent Quest Recruitment', 22000.00);

-- =========================
-- 7. TRAININGS & SEMINARS
-- =========================
REPLACE INTO employee_trainings (employee_id, training_title, conducted_by, no_of_hours) VALUES
(101, 'Advanced HR Management Seminar', 'Philippine HR Association', 40.0),
(101, 'Employee Relations & Conflict Resolution', 'Training & Development Center', 24.0),
(201, 'Advanced Marketing Strategy', 'Marketing Institute of Excellence', 32.0),
(201, 'Social Media Management Mastery', 'Digital Marketing Academy', 16.0),
(202, 'Advanced Leadership Workshop', 'HR Management Association', 24.0),
(202, 'Supervisory Skills Development', 'Professional Development Center', 20.0),
(203, 'Digital Marketing Essentials', 'Google Academy', 40.0),
(203, 'Adobe Creative Suite Basics', 'Training Institute Philippines', 30.0),
(301, 'HR Compliance and Labor Law Update', 'Department of Labor', 24.0),
(301, 'Performance Management Workshop', 'Human Resources Institute', 16.0),
(302, 'Recruitment Best Practices', 'HR Development Forum', 20.0),
(302, 'Employee Relations Fundamentals', 'Training and Development Center', 18.0);

-- ==================
-- 8. DISCLOSURES
-- ==================
REPLACE INTO employee_disclosures (employee_id, is_related_to_company, has_admin_offense, has_criminal_charge) VALUES
(101, 0, 0, 0), (201, 0, 0, 0), (202, 0, 0, 0), (203, 0, 0, 0), (301, 0, 0, 0), (302, 0, 0, 0);

-- ==================
-- 9. GOVERNMENT IDS
-- ==================
REPLACE INTO employee_government_ids (employee_id, sss_number, philhealth_number, pagibig_number, tin_number) VALUES
(101, '33-0234567-0', '12-787654321-5', '1210-6666-7788', '023-456-789-001'),
(201, '33-1234567-1', '12-987654321-0', '1210-5566-7788', '123-456-789-000'),
(202, '33-2234567-2', '12-887654321-1', '1210-4466-7788', '223-456-789-001'),
(203, '33-3234567-3', '12-787654321-2', '1210-3366-7788', '323-456-789-002'),
(301, '33-4234567-4', '12-687654321-3', '1210-2266-7788', '423-456-789-002'),
(302, '33-5234567-5', '12-587654321-4', '1210-1166-7788', '523-456-789-003');

-- ==================================
-- 10. ADDRESSES & EMERGENCY CONTACTS
-- ==================================
REPLACE INTO employee_addresses (employee_id, address_type, barangay, city, province) VALUES
(101, 'Residential', 'San Isidro', 'Tayabas City', 'Quezon'),
(101, 'Permanent', 'San Isidro', 'Tayabas City', 'Quezon'),
(201, 'Residential', 'Mandaluyong', 'Mandaluyong City', 'Metro Manila'),
(201, 'Permanent', 'Bulacan', 'Meycauayan', 'Bulacan'),
(202, 'Residential', 'Makati', 'Makati City', 'Metro Manila'),
(202, 'Permanent', 'Laguna', 'Calamba', 'Laguna'),
(203, 'Residential', 'San Isidro', 'Tayabas City', 'Quezon'),
(203, 'Permanent', 'San Isidro', 'Tayabas City', 'Quezon'),
(301, 'Residential', 'Quezon City', 'Quezon City', 'Metro Manila'),
(301, 'Permanent', 'Nueva Ecija', 'Cabanatuan City', 'Nueva Ecija'),
(302, 'Residential', 'Taguig', 'Taguig City', 'Metro Manila'),
(302, 'Permanent', 'Cavite', 'Dasmariñas', 'Cavite');

REPLACE INTO employee_emergency_contacts (employee_id, contact_name, relationship, contact_number) VALUES
(101, 'Marco Antonio Delgado', 'Spouse', '09161111111'),
(201, 'Magdalena Reyes', 'Mother', '09125558888'),
(202, 'Robert Miller', 'Father', '09165559999'),
(203, 'Ana Ramos Santiago', 'Spouse', '09123334445'),
(301, 'Carlos Gomez', 'Father', '09175556666'),
(302, 'Rosa Torres', 'Mother', '09185557777');

-- =========================================================
-- 11. SALN INFORMATION (Real/Personal Property/Liabilities)
-- =========================================================
REPLACE INTO employee_real_properties (employee_id, description, kind, acquisition_cost) VALUES
(101, 'Residential House', 'Building', 2500000.00),
(201, 'Residential Lot', 'Land', 1500000.00),
(202, 'Condominium Unit', 'Building', 3200000.00),
(301, 'Residential House', 'Building', 2800000.00),
(302, 'Vacant Lot', 'Land', 950000.00);

REPLACE INTO employee_personal_properties (employee_id, description, acquisition_cost) VALUES
(101, 'Toyota Fortuner 2019', 1350000.00),
(201, 'Honda Civic 2018', 950000.00),
(202, 'Mitsubishi Mirage 2019', 650000.00),
(203, 'Toyota Vios 2020', 850000.00),
(301, 'Hyundai Elantra 2021', 1100000.00),
(302, 'Yamaha Motorcycle', 120000.00);

REPLACE INTO employee_liabilities (employee_id, nature_of_liability, creditor_name, outstanding_balance) VALUES
(101, 'Housing Loan', 'BDO Unibank', 1800000.00),
(201, 'Home Loan', 'BDO Unibank', 1200000.00),
(202, 'Condo Loan', 'Metrobank', 2500000.00),
(203, 'Car Loan', 'BDO Unibank', 450000.00),
(301, 'Housing Loan', 'Banco de Oro', 1800000.00),
(302, 'Personal Loan', 'Philippine National Bank', 280000.00);

-- ========================
-- 12. CHARACTER REFERENCES
-- ========================
REPLACE INTO employee_references (employee_id, reference_name, reference_address, reference_telephone) VALUES
(101, 'Dr. Reynaldo Cruz', 'Pasig City', '02-9876-5432'),
(201, 'Dr. Juan Dela Cruz', 'Quezon City', '02-8123-4567'),
(202, 'Engr. Maria Santos', 'Makati City', '02-7654-3210'),
(203, 'Dr. Juan Dela Cruz', 'Quezon City', '02-8123-4567'),
(301, 'Atty. Rosa Fernandez', 'Pasig City', '02-5432-1098'),
(302, 'Prof. Antonio Lopez', 'Caloocan City', '02-3456-7891');

-- =====================================================================
-- IT DEPARTMENT AGDANGAN BRANCH - TEST EMPLOYEES SEED
-- =====================================================================
-- 3 mockup employees from Agdangan Branch, IT Department
-- - Helpdesk Assistant 1
-- - IT Supervisor
-- - IT Manager

-- ===============================================
-- 13. EMPLOYEES (IT Department - Agdangan Branch)
-- ===============================================
REPLACE INTO employees (employee_id, employee_code, first_name, last_name, middle_name, date_of_birth, place_of_birth, gender, civil_status, hire_date, job_title_id, job_title, department_id, rank_category_id, branch_id, employment_status, employment_type, profile_picture) VALUES
-- IT Manager
(501, 'IT-AGD-001', 'Raymond', 'Santos', 'Mercado', '1985-03-18', 'Tayabas, Quezon', 'Male', 'Married', '2015-06-01', 800, 'IT Manager I', 8, 3, 1, 'Regular', 'Full-time', 'orange.jpg'),
-- IT Supervisor
(502, 'IT-AGD-002', 'Marie', 'Cruz', 'Dela Rosa', '1990-07-22', 'Lucena, Quezon', 'Female', 'Single', '2017-09-15', 804, 'IT Supervisor I', 8, 4, 1, 'Regular', 'Full-time', 'chirstine.jpg'),
-- Helpdesk Assistant 1
(503, 'IT-AGD-003', 'Jerome', 'Reyes', 'Garcia', '1998-11-05', 'Agdangan, Quezon', 'Male', 'Single', '2022-03-10', 816, 'Helpdesk Assistant I', 8, 5, 1, 'Regular', 'Full-time', 'flowg.jpg');

-- =====================
-- 14. EMPLOYEE CONTACTS
-- =====================
REPLACE INTO employee_contacts (employee_id, personal_email, mobile_number, telephone_number) VALUES
(501, 'raymond.santos@raquel.com', '09171234501', '888-5001'),
(502, 'marie.cruz@raquel.com', '09181234502', '888-5002'),
(503, 'jerome.reyes@raquel.com', '09191234503', '888-5003');

-- =============================================
-- 15. EMPLOYEE DETAILS (Physical & Citizenship)
-- =============================================
REPLACE INTO employee_details (employee_id, height_m, weight_kg, blood_type, citizenship) VALUES
(501, 1.75, 75.0, 'O+', 'Filipino'),
(502, 1.62, 58.0, 'A+', 'Filipino'),
(503, 1.70, 68.0, 'B+', 'Filipino');

-- =====================
-- 16. FAMILY BACKGROUND
-- =====================
REPLACE INTO employee_family (employee_id, member_type, surname, first_name, middle_name, occupation) VALUES
(501, 'Father', 'Santos', 'Felipe', 'Mercado', 'Retired Government Employee'),
(501, 'Mother', 'Santos', 'Angelina', 'Gonzales', 'Retired Teacher'),
(501, 'Spouse', 'Santos', 'Andrea', 'Lim', 'Accountant'),
(502, 'Father', 'Cruz', 'Juan', 'Reyes', 'Government Employee'),
(502, 'Mother', 'Cruz', 'Teresa', 'Dela Rosa', 'Educator'),
(503, 'Father', 'Reyes', 'Miguel', 'Santos', 'Businessman'),
(503, 'Mother', 'Reyes', 'Lucia', 'Garcia', 'Teacher');

REPLACE INTO employee_children (employee_id, surname, first_name, middle_name, date_of_birth) VALUES
(501, 'Santos', 'Mark', 'Mercado', '2017-04-12'),
(501, 'Santos', 'Patricia', 'Mercado', '2019-08-25');

REPLACE INTO employee_siblings (employee_id, surname, first_name, middle_name, date_of_birth) VALUES
(501, 'Santos', 'Antonio', 'Mercado', '1988-06-10'),
(502, 'Cruz', 'Carlos', 'Reyes', '1992-03-20');

-- ==========================
-- 17. EDUCATIONAL BACKGROUND
-- ==========================
REPLACE INTO employee_education (employee_id, education_level, school_name, degree_course, year_graduated) VALUES
(501, 'College', 'Batangas State University', 'BS Information Technology', '2007'),
(501, 'Graduate Studies', 'Ateneo de Manila University', 'MS Information Technology Management', '2012'),
(502, 'College', 'Quezon City University', 'BS Computer Science', '2012'),
(502, 'Graduate Studies', 'University of the Philippines', 'MA Information Systems', '2016'),
(503, 'College', 'Polytechnic University of the Philippines', 'BS Information Technology', '2020');

-- ===================
-- 18. WORK EXPERIENCE
-- ===================
REPLACE INTO employee_work_experience (employee_id, date_from, date_to, job_title, company_name, monthly_salary) VALUES
(501, '2007-07-01', '2015-05-31', 'Senior Systems Administrator', 'TechCorp Solutions Inc.', 45000.00),
(502, '2012-09-01', '2017-08-31', 'IT Support Specialist', 'Digital Services Philippines', 32000.00),
(503, '2020-06-01', '2022-02-28', 'Technical Support Associate', 'Customer Service Innovations Ltd.', 24000.00);

-- ========================
-- 19. TRAININGS & SEMINARS
-- ========================
REPLACE INTO employee_trainings (employee_id, training_title, conducted_by, no_of_hours) VALUES
(501, 'Advanced IT Management and Leadership', 'Information Technology Institute of the Philippines', 40.0),
(501, 'Network Infrastructure and Security', 'CISCO Academy', 48.0),
(501, 'IT Service Management (ITIL)', 'British Computer Society', 32.0),
(502, 'IT Supervisor Workshop', 'HR Development Center', 24.0),
(502, 'Advanced Troubleshooting Techniques', 'Technical Support Academy', 30.0),
(502, 'Customer Service Excellence', 'Service Quality Institute', 20.0),
(503, 'Helpdesk Fundamentals', 'IT Support Training Center', 24.0),
(503, 'Windows and Linux Administration', 'CompTIA Training Partner', 36.0),
(503, 'Professional Customer Service', 'Customer Care Excellence Institute', 16.0);

-- ===============
-- 20. DISCLOSURES
-- ===============
REPLACE INTO employee_disclosures (employee_id, is_related_to_company, has_admin_offense, has_criminal_charge) VALUES
(501, 0, 0, 0),
(502, 0, 0, 0),
(503, 0, 0, 0);

-- ==================
-- 21. GOVERNMENT IDS
-- ==================
REPLACE INTO employee_government_ids (employee_id, sss_number, philhealth_number, pagibig_number, tin_number) VALUES
(501, '33-6234567-5', '12-587654321-9', '1210-6666-1122', '623-456-789-005'),
(502, '33-7234567-6', '12-487654321-8', '1210-5555-1122', '723-456-789-006'),
(503, '33-8234567-7', '12-387654321-7', '1210-4444-1122', '823-456-789-007');

-- ==================================
-- 22. ADDRESSES & EMERGENCY CONTACTS
-- ==================================
REPLACE INTO employee_addresses (employee_id, address_type, barangay, city, province) VALUES
(501, 'Residential', 'Poblacion', 'Agdangan', 'Quezon'),
(501, 'Permanent', 'Poblacion', 'Agdangan', 'Quezon'),
(502, 'Residential', 'Poblacion', 'Agdangan', 'Quezon'),
(502, 'Permanent', 'Poblacion', 'Pagbilao', 'Quezon'),
(503, 'Residential', 'Poblacion', 'Agdangan', 'Quezon'),
(503, 'Permanent', 'Poblacion', 'Agdangan', 'Quezon');

REPLACE INTO employee_emergency_contacts (employee_id, contact_name, relationship, contact_number) VALUES
(501, 'Andrea Santos', 'Spouse', '09171111501'),
(502, 'Juan Cruz', 'Father', '09181111502'),
(503, 'Miguel Reyes', 'Father', '09191111503');

SET FOREIGN_KEY_CHECKS = 1;