USE raquel_hris;
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================================
-- IT DEPARTMENT AGDANGAN BRANCH - TEST EMPLOYEES SEED
-- =====================================================================
-- 3 mockup employees from Agdangan Branch, IT Department
-- - Helpdesk Assistant 1
-- - IT Supervisor
-- - IT Manager

-- ---------------------------------------------------------
-- 1. EMPLOYEES (IT Department - Agdangan Branch)
-- ---------------------------------------------------------
REPLACE INTO employees (employee_id, employee_code, first_name, last_name, middle_name, date_of_birth, place_of_birth, gender, civil_status, hire_date, job_title_id, job_title, department_id, rank_category_id, branch_id, employment_status, employment_type, profile_picture) VALUES
-- IT Manager
(501, 'IT-AGD-001', 'Raymond', 'Santos', 'Mercado', '1985-03-18', 'Tayabas, Quezon', 'Male', 'Married', '2015-06-01', 800, 'IT Manager I', 8, 3, 1, 'Regular', 'Full-time', 'orange.jpg'),
-- IT Supervisor
(502, 'IT-AGD-002', 'Marie', 'Cruz', 'Dela Rosa', '1990-07-22', 'Lucena, Quezon', 'Female', 'Single', '2017-09-15', 804, 'IT Supervisor I', 8, 4, 1, 'Regular', 'Full-time', 'chirstine.jpg'),
-- Helpdesk Assistant 1
(503, 'IT-AGD-003', 'Jerome', 'Reyes', 'Garcia', '1998-11-05', 'Agdangan, Quezon', 'Male', 'Single', '2022-03-10', 816, 'Helpdesk Assistant I', 8, 5, 1, 'Regular', 'Full-time', 'flowg.jpg');

-- ---------------------------------------------------------
-- 2. EMPLOYEE CONTACTS
-- ---------------------------------------------------------
REPLACE INTO employee_contacts (employee_id, personal_email, mobile_number, telephone_number) VALUES
(501, 'raymond.santos@raquel.com', '09171234501', '888-5001'),
(502, 'marie.cruz@raquel.com', '09181234502', '888-5002'),
(503, 'jerome.reyes@raquel.com', '09191234503', '888-5003');

-- ---------------------------------------------------------
-- 3. EMPLOYEE DETAILS (Physical & Citizenship)
-- ---------------------------------------------------------
REPLACE INTO employee_details (employee_id, height_m, weight_kg, blood_type, citizenship) VALUES
(501, 1.75, 75.0, 'O+', 'Filipino'),
(502, 1.62, 58.0, 'A+', 'Filipino'),
(503, 1.70, 68.0, 'B+', 'Filipino');

-- ---------------------------------------------------------
-- 4. FAMILY BACKGROUND
-- ---------------------------------------------------------
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

-- ---------------------------------------------------------
-- 5. EDUCATIONAL BACKGROUND
-- ---------------------------------------------------------
REPLACE INTO employee_education (employee_id, education_level, school_name, degree_course, year_graduated) VALUES
(501, 'College', 'Batangas State University', 'BS Information Technology', '2007'),
(501, 'Graduate Studies', 'Ateneo de Manila University', 'MS Information Technology Management', '2012'),
(502, 'College', 'Quezon City University', 'BS Computer Science', '2012'),
(502, 'Graduate Studies', 'University of the Philippines', 'MA Information Systems', '2016'),
(503, 'College', 'Polytechnic University of the Philippines', 'BS Information Technology', '2020');

-- ---------------------------------------------------------
-- 6. WORK EXPERIENCE
-- ---------------------------------------------------------
REPLACE INTO employee_work_experience (employee_id, date_from, date_to, job_title, company_name, monthly_salary) VALUES
(501, '2007-07-01', '2015-05-31', 'Senior Systems Administrator', 'TechCorp Solutions Inc.', 45000.00),
(502, '2012-09-01', '2017-08-31', 'IT Support Specialist', 'Digital Services Philippines', 32000.00),
(503, '2020-06-01', '2022-02-28', 'Technical Support Associate', 'Customer Service Innovations Ltd.', 24000.00);

-- ---------------------------------------------------------
-- 7. TRAININGS & SEMINARS
-- ---------------------------------------------------------
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

-- ---------------------------------------------------------
-- 8. DISCLOSURES
-- ---------------------------------------------------------
REPLACE INTO employee_disclosures (employee_id, is_related_to_company, has_admin_offense, has_criminal_charge) VALUES
(501, 0, 0, 0),
(502, 0, 0, 0),
(503, 0, 0, 0);

-- ---------------------------------------------------------
-- 9. GOVERNMENT IDS
-- ---------------------------------------------------------
REPLACE INTO employee_government_ids (employee_id, sss_number, philhealth_number, pagibig_number, tin_number) VALUES
(501, '33-6234567-5', '12-587654321-9', '1210-6666-1122', '623-456-789-005'),
(502, '33-7234567-6', '12-487654321-8', '1210-5555-1122', '723-456-789-006'),
(503, '33-8234567-7', '12-387654321-7', '1210-4444-1122', '823-456-789-007');

-- ---------------------------------------------------------
-- 10. ADDRESSES & EMERGENCY CONTACTS
-- ---------------------------------------------------------
REPLACE INTO employee_addresses (employee_id, address_type, barangay, city, province) VALUES
(501, 'Residential', 'Agdangan', 'Quezon', 'Quezon'),
(501, 'Permanent', 'Agdangan', 'Quezon', 'Quezon'),
(502, 'Residential', 'Agdangan', 'Quezon', 'Quezon'),
(502, 'Permanent', 'Pagbilao', 'Quezon', 'Quezon'),
(503, 'Residential', 'Agdangan', 'Quezon', 'Quezon'),
(503, 'Permanent', 'Agdangan', 'Quezon', 'Quezon');

REPLACE INTO employee_emergency_contacts (employee_id, contact_name, relationship, contact_number) VALUES
(501, 'Andrea Santos', 'Spouse', '09171111501'),
(502, 'Juan Cruz', 'Father', '09181111502'),
(503, 'Miguel Reyes', 'Father', '09191111503');

SET FOREIGN_KEY_CHECKS = 1;
