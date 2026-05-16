SET FOREIGN_KEY_CHECKS = 0;
USE raquel_hris;

-- ============================================
-- 1. CORE SYSTEM DATA (Main Branch & HR)
-- ============================================

-- Main Branch
REPLACE INTO `branches` (`branch_id`, `branch_name`, `location`, `is_active`, `created_at`, `updated_at`) 
VALUES (102, 'Raquel Pawnshop Main Office', 'RGC Building, Diversion Road, Sitio 1 Barangay Mayuwi Tayabas City', 1, NOW(), NOW());

-- HR Department
REPLACE INTO `departments` (`department_id`, `department_name`, `description`) 
VALUES (6, 'Human Resources', 'Handles personnel management and recruitment.');

-- Manager Rank
REPLACE INTO `rank_categories` (`rank_category_id`, `rank_name`, `level_order`) 
VALUES (3, 'Manager', 3);

-- HR Manager Job Title
REPLACE INTO `job_titles` (`job_title_id`, `job_title`, `rank_category_id`, `department_id`) 
VALUES (25, 'HR Manager I', 3, 6);

-- ============================================
-- 2. SYSTEM ACCOUNTS & EMPLOYEES
-- ============================================

-- Admin User (Standalone)
REPLACE INTO `users` 
(`employee_id`, `username`, `email`, `password_hash`, `full_name`, `role`, `branch_id`, `is_active`, `first_login_completed`, `created_at`, `updated_at`) 
VALUES
(NULL, 'admin', 'admin@raquel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin Standalone Account', 'Admin', 102, 1, 1, NOW(), NOW());

-- EMPLOYEE 2: Elena Delgado (HR Manager)
-- ---------------------------------------------------------
REPLACE INTO `employees` (`employee_id`, `employee_code`, `first_name`, `last_name`, `middle_name`, `date_of_birth`, `place_of_birth`, `gender`, `civil_status`, `hire_date`, `job_title`, `job_title_id`, `rank_category_id`, `department_id`, `branch_id`, `employment_status`, `employment_type`, `profile_picture`, `is_active`, `created_at`, `updated_at`) VALUES
(2, '026-001', 'Elena', 'Delgado', 'Santos', '1990-05-15', 'Tayabas, Quezon', 'Female', 'Married', '2024-01-15', 'HR Manager I', 25, 3, 6, 102, 'Regular', 'Full-time', 'emp_69f06acc06ba1.jpg', 1, NOW(), NOW());



INSERT INTO `employee_contacts` (`employee_id`, `mobile_number`, `personal_email`) VALUES
(2, '09171234567', 'elena.delgado@email.com');

INSERT INTO `employee_addresses` (`employee_id`, `address_type`, `house_no`, `street`, `subdivision`, `barangay`, `city`, `province`, `zip_code`) VALUES
(2, 'Residential', '123', 'Sampaguita Street', 'Sunshine Village', 'Barangay Ilayang Talim', 'Binangonan', 'Rizal', '1940'),
(2, 'Permanent', '123', 'Sampaguita Street', 'Sunshine Village', 'Barangay Ilayang Talim', 'Binangonan', 'Rizal', '1940');

INSERT INTO `employee_details` (`employee_id`, `height_m`, `weight_kg`, `blood_type`, `citizenship`) VALUES
(2, 1.65, 60.00, 'A+', 'Filipino');

INSERT INTO `employee_family` (`employee_id`, `member_type`, `surname`, `first_name`, `middle_name`, `occupation`) VALUES
(2, 'Spouse', 'Delgado', 'Marco', 'Antonio', 'Civil Engineer'),
(2, 'Father', 'Santos', 'Ricardo', 'Valdez', 'Retired School Principal'),
(2, 'Mother', 'Quinto', 'Maria', 'Clara', 'Homemaker');

INSERT INTO `employee_children` (`employee_id`, `surname`, `first_name`, `middle_name`, `date_of_birth`) VALUES
(2, 'Delgado', 'Sofia', 'Santos', '2018-03-04');

INSERT INTO `employee_siblings` (`employee_id`, `surname`, `first_name`, `middle_name`, `date_of_birth`) VALUES
(2, 'Santos', 'Andrea', 'Reyes', '1998-04-28');

INSERT INTO `employee_education` (`employee_id`, `education_level`, `school_name`, `degree_course`, `period_from`, `period_to`, `highest_level_units`, `year_graduated`, `honors_received`) VALUES
(2, 'Elementary', 'San Isidro Elementary School', NULL, '1994-06-01', '2000-03-31', 'Graduated', '2000', 'Valedictorian'),
(2, 'Secondary', 'Rizal National High School', NULL, '2000-06-01', '2004-03-31', 'Graduated', '2004', 'With Honors'),
(2, 'College', 'University of the Philippines Diliman', 'BS Nursing', '2006-06-01', '2010-03-31', 'Graduated', '2010', 'Cum Laude');

INSERT INTO `employee_work_experience` (`employee_id`, `date_from`, `date_to`, `job_title`, `company_name`, `monthly_salary`, `appointment_status`, `reason_for_leaving`) VALUES
(2, '2010-06-01', '2013-12-31', 'Staff Nurse', 'Rizal Provincial Hospital', 18000.00, 'Full-time', 'Sought career growth'),
(2, '2016-01-01', '2019-12-31', 'Nursing Supervisor', 'St. Gabriel Medical Center', 40000.00, 'Full-time', 'Relocated');

INSERT INTO `employee_trainings` (`employee_id`, `date_from`, `date_to`, `training_title`, `training_type`, `no_of_hours`, `conducted_by`) VALUES
(2, '2010-08-01', '2010-08-02', 'Basic Life Support', 'Clinical', 16, 'Red Cross'),
(2, '2023-05-10', '2023-05-12', 'Advanced HR Management', 'Management', 24, 'People Management Association');

INSERT INTO `employee_eligibility` (`employee_id`, `license_title`, `license_number`, `date_of_exam`, `place_of_exam`) VALUES
(2, 'Registered Nurse', '00458291', '2010-06-15', 'Manila');

INSERT INTO `employee_skills` (`employee_id`, `skill_name`) VALUES
(2, 'Patient Care'), (2, 'HR Policy Development'), (2, 'Team Leadership');

INSERT INTO `employee_government_ids` (`employee_id`, `sss_number`, `philhealth_number`, `pagibig_number`, `tin_number`) VALUES
(2, '73-7272727-7', '10-927226525-2', '1981-7161-5151', '198-171-616-155');

INSERT INTO `employee_disclosures` (`employee_id`, `is_related_to_company`, `has_admin_offense`, `has_criminal_charge`, `has_criminal_conviction`, `has_been_separated`, `is_pwd`, `is_solo_parent`, `has_recent_hospital`, `has_current_treatment`) VALUES
(2, 0, 0, 0, 0, 0, 0, 0, 0, 0);

INSERT INTO `employee_emergency_contacts` (`employee_id`, `contact_name`, `relationship`, `contact_number`) VALUES
(2, 'Marco Antonio Delgado', 'Spouse', '09998765543');

INSERT INTO `users` (`user_id`, `employee_id`, `username`, `email`, `password_hash`, `full_name`, `role`, `branch_id`, `is_active`, `first_login_completed`, `created_at`, `updated_at`) VALUES
(2, 2, 'elena.delgado', 'elena.delgado@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Elena Delgado', 'HR Manager', 102, 1, 1, NOW(), NOW());


SET FOREIGN_KEY_CHECKS = 1;
