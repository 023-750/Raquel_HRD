SET FOREIGN_KEY_CHECKS = 0;
USE raquel_hris;

-- Clear existing data to prevent duplicate entry errors
TRUNCATE TABLE `employee_references`;
TRUNCATE TABLE `employee_liabilities`;
TRUNCATE TABLE `employee_personal_properties`;
TRUNCATE TABLE `employee_real_properties`;
TRUNCATE TABLE `employee_memberships`;
TRUNCATE TABLE `employee_recognitions`;
TRUNCATE TABLE `employee_skills`;
TRUNCATE TABLE `employee_eligibility`;
TRUNCATE TABLE `employee_voluntary_work`;
TRUNCATE TABLE `employee_trainings`;
TRUNCATE TABLE `employee_work_experience`;
TRUNCATE TABLE `employee_education`;
TRUNCATE TABLE `employee_siblings`;
TRUNCATE TABLE `employee_children`;
TRUNCATE TABLE `employee_family`;
TRUNCATE TABLE `employee_government_ids`;
TRUNCATE TABLE `employee_details`;
TRUNCATE TABLE `employee_addresses`;
TRUNCATE TABLE `employee_contacts`;
TRUNCATE TABLE `employee_disclosures`;
TRUNCATE TABLE `employee_emergency_contacts`;
TRUNCATE TABLE `users`;
TRUNCATE TABLE `employees`;

-- ============================================
-- 4. BUILT-IN SYSTEM ADMIN
-- ============================================
-- Default password: password
INSERT INTO `users` (`employee_id`, `username`, `email`, `password_hash`, `full_name`, `role`, `branch_id`, `is_active`, `first_login_completed`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'admin', 'admin@raquel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Raquel HR Admin', 'Admin', 102, 1, 1, NOW(), NOW(), NULL);


-- ---------------------------------------------------------
-- EMPLOYEE 1: Elena Delgado (HR Manager)
-- ---------------------------------------------------------
INSERT INTO `employees` (`employee_id`, `employee_code`, `first_name`, `last_name`, `middle_name`, `date_of_birth`, `place_of_birth`, `gender`, `civil_status`, `hire_date`, `job_title`, `job_title_id`, `rank_category_id`, `department_id`, `branch_id`, `employment_status`, `employment_type`, `profile_picture`, `is_active`, `created_at`, `updated_at`) VALUES
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

-- ---------------------------------------------------------
-- EMPLOYEE 2: John Wick (HR Supervisor)
-- ---------------------------------------------------------
INSERT INTO `employees` (`employee_id`, `employee_code`, `first_name`, `last_name`, `middle_name`, `date_of_birth`, `place_of_birth`, `gender`, `civil_status`, `hire_date`, `job_title`, `job_title_id`, `rank_category_id`, `department_id`, `branch_id`, `employment_status`, `employment_type`, `profile_picture`, `is_active`, `created_at`, `updated_at`) VALUES
(3, '026-002', 'John', 'Wick', 'Helena', '1980-09-02', 'New York City, USA', 'Male', 'Widowed', '2023-06-15', 'HR Supervisor I', 73, 4, 6, 102, 'Regular', 'Full-time', NULL, 1, NOW(), NOW());

INSERT INTO `employee_contacts` (`employee_id`, `mobile_number`, `telephone_number`, `personal_email`) VALUES
(3, '09198887766', '02-8123-4567', 'john.wick@continental.com');

INSERT INTO `employee_addresses` (`employee_id`, `address_type`, `house_no`, `street`, `subdivision`, `barangay`, `city`, `province`, `zip_code`) VALUES
(3, 'Residential', '56C', 'Continental Avenue', 'The Continental Hotel', 'Poblacion', 'Makati', 'Metro Manila', '1200'),
(3, 'Permanent', '123', 'Orchard Street', 'Silver Lake Subdivision', 'San Antonio', 'Pasig', 'Metro Manila', '1600');

INSERT INTO `employee_details` (`employee_id`, `height_m`, `weight_kg`, `blood_type`, `citizenship`) VALUES
(3, 1.85, 85.00, 'O+', 'American');

INSERT INTO `employee_government_ids` (`employee_id`, `sss_number`, `philhealth_number`, `pagibig_number`, `tin_number`) VALUES
(3, '15-1234567-8', '20-123456789-0', '1234-5678-9012', '123-456-789-000');

INSERT INTO `employee_family` (`employee_id`, `member_type`, `surname`, `first_name`, `middle_name`, `occupation`) VALUES
(3, 'Spouse', 'Wick', 'Helen', 'Morrison', 'Interior Designer'),
(3, 'Father', 'Wick', 'George', 'Thomas', 'Military Officer (Retired)'),
(3, 'Mother', 'Higgins', 'Patricia', 'Ann', 'Nurse');

INSERT INTO `employee_siblings` (`employee_id`, `surname`, `first_name`, `middle_name`, `date_of_birth`) VALUES
(3, 'Wick', 'Marcus', 'George', '1978-04-10'),
(3, 'Wick', 'Sarah', 'Helena', '1985-12-05');

INSERT INTO `employee_education` (`employee_id`, `education_level`, `school_name`, `degree_course`, `period_from`, `period_to`, `highest_level_units`, `year_graduated`, `honors_received`) VALUES
(3, 'Elementary', 'St. Michael\'s Preparatory School', NULL, '1986-06-01', '1992-03-31', 'Graduated', '1992', 'Class Salutatorian'),
(3, 'Secondary', 'St. Francis High School', NULL, '1992-06-01', '1996-03-31', 'Graduated', '1996', 'Athlete of the Year'),
(3, 'Senior High School', 'United World College', 'STEM Track', '2015-06-01', '2017-03-31', 'Graduated', '2017', 'Debate Champion'),
(3, 'College', 'University of the Philippines Diliman', 'BS Psychology', '1996-06-01', '2000-03-31', 'Graduated', '2000', 'Magna Cum Laude'),
(3, 'Graduate Studies', 'University of Pennsylvania', 'MBA Human Resource Management', '2005-06-01', '2007-03-31', 'Graduated', '2007', 'With Distinction');

INSERT INTO `employee_work_experience` (`employee_id`, `date_from`, `date_to`, `job_title`, `company_name`, `monthly_salary`, `appointment_status`, `reason_for_leaving`) VALUES
(3, '2000-06-01', '2003-12-31', 'Training Coordinator', 'Global Security Services Inc.', 35000.00, 'Full-time', 'Pursued higher education'),
(3, '2007-06-01', '2012-08-31', 'HR Specialist', 'Continental Hotels Group', 55000.00, 'Full-time', 'Career advancement'),
(3, '2012-09-01', '2018-03-31', 'HR Manager', 'Gold Coin Pawnshop Chain', 75000.00, 'Full-time', 'Relocated to Philippines'),
(3, '2019-01-01', '2023-05-31', 'Senior HR Consultant', 'Premier Pawnshop Services', 85000.00, 'Full-time', 'Sought regular employment');

INSERT INTO `employee_trainings` (`employee_id`, `date_from`, `date_to`, `training_title`, `training_type`, `no_of_hours`, `conducted_by`) VALUES
(3, '2015-03-10', '2015-03-12', 'Strategic Human Resource Management', 'Management', 24, 'SHRM Philippines'),
(3, '2018-07-15', '2018-07-17', 'Conflict Resolution and Mediation', 'Soft Skills', 16, 'Center for Mediation Excellence'),
(3, '2020-09-05', '2020-09-06', 'Digital HR Transformation', 'Technical', 12, 'HR Tech Institute'),
(3, '2022-11-20', '2022-11-22', 'Leadership Excellence Program', 'Management', 24, 'John Maxwell Team');

INSERT INTO `employee_voluntary_work` (`employee_id`, `organization_name`, `organization_address`, `date_from`, `date_to`, `no_of_hours`, `position_nature`) VALUES
(3, 'HR Leaders Association Philippines', 'Makati City', '2015-01-01', '2019-12-31', 480, 'Board Member'),
(3, 'Animal Welfare League', 'Quezon City', '2019-06-01', NULL, 120, 'Volunteer Coordinator'),
(3, 'American Chamber of Commerce HR Committee', 'Makati City', '2020-01-01', NULL, 96, 'Committee Member');

INSERT INTO `employee_eligibility` (`employee_id`, `license_title`, `license_number`, `date_of_exam`, `place_of_exam`, `date_from`, `date_to`) VALUES
(3, 'Professional HR Practitioner', 'PR-2010-001234', '2010-06-15', 'Manila', '2010-06-15', '2099-12-31'),
(3, 'Certified Public Accountant', 'CPA-2008-567890', '2008-10-20', 'New York', '2008-10-20', NULL),
(3, 'SHRM Senior Certified Professional', 'SHRM-SCP-2015-78901', NULL, NULL, '2015-01-01', '2025-12-31');

INSERT INTO `employee_skills` (`employee_id`, `skill_name`) VALUES
(3, 'Strategic Planning'), (3, 'Tactical Vehicle Operations'), (3, 'Firearms Proficiency'), (3, 'Close Combat Defense'), (3, 'Multilingual'), (3, 'Chess'), (3, 'Strategic Negotiation');

INSERT INTO `employee_recognitions` (`employee_id`, `recognition_title`) VALUES
(3, 'HR Excellence Award (2021)'), (3, 'Employee of the Year (2011)');

INSERT INTO `employee_memberships` (`employee_id`, `organization_name`) VALUES
(3, 'Society for Human Resource Management'), (3, 'People Management Association');

INSERT INTO `employee_real_properties` (`employee_id`, `description`, `kind`, `exact_location`, `assessed_value`, `market_value`, `acquisition_year_mode`, `acquisition_cost`) VALUES
(3, 'House and Lot', 'Residential', 'Makati City', 12000000.00, 15000000.00, 'Purchased 2018', 13500000.00);

INSERT INTO `employee_personal_properties` (`employee_id`, `description`, `year_acquired`, `acquisition_cost`) VALUES
(3, '1969 Ford Mustang', '2015', 2500000.00), (3, 'Custom tactical equipment', '2021', 250000.00);

INSERT INTO `employee_liabilities` (`employee_id`, `nature_of_liability`, `creditor_name`, `outstanding_balance`) VALUES
(3, 'Car Loan', 'Banco de Oro', 1500000.00), (3, 'Home Mortgage', 'BPI', 8500000.00);

INSERT INTO `employee_references` (`employee_id`, `reference_name`, `reference_address`, `reference_telephone`) VALUES
(3, 'Winston Scott', 'New York, USA', '+1-212-555-0100'),
(3, 'Sofia Al-Azwar', 'Casablanca, Morocco', '+212-555-0200'),
(3, 'The Bowery King', 'New York, USA', '+1-212-555-0300');

INSERT INTO `employee_disclosures` (`employee_id`, `is_related_to_company`, `has_admin_offense`, `has_criminal_charge`, `has_criminal_conviction`, `has_been_separated`, `is_pwd`, `is_solo_parent`, `solo_parent_details`) VALUES
(3, 0, 0, 0, 0, 0, 0, 1, 'Widower since 2014');

INSERT INTO `employee_emergency_contacts` (`employee_id`, `contact_name`, `relationship`, `contact_number`) VALUES
(3, 'Charon', 'Friend / Associate', '09197778899');

INSERT INTO `users` (`user_id`, `employee_id`, `username`, `email`, `password_hash`, `full_name`, `role`, `branch_id`, `is_active`, `first_login_completed`, `created_at`, `updated_at`) VALUES
(3, 3, 'john.wick', 'john.wick@continental.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Wick', 'HR Supervisor', 102, 1, 1, NOW(), NOW());

-- ---------------------------------------------------------
-- EMPLOYEE 3: Sarah Connor (HR Staff)
-- ---------------------------------------------------------
INSERT INTO `employees` (`employee_id`, `employee_code`, `first_name`, `last_name`, `middle_name`, `date_of_birth`, `place_of_birth`, `gender`, `civil_status`, `hire_date`, `job_title`, `job_title_id`, `rank_category_id`, `department_id`, `branch_id`, `employment_status`, `employment_type`, `profile_picture`, `is_active`, `created_at`, `updated_at`) VALUES
(4, '026-003', 'Sarah', 'Connor', 'Jean', '1988-05-14', 'Los Angeles, USA', 'Female', 'Single', '2024-02-10', 'HR Staff I', 182, 5, 6, 102, 'Regular', 'Full-time', NULL, 1, NOW(), NOW());

INSERT INTO `employee_contacts` (`employee_id`, `mobile_number`, `personal_email`) VALUES
(4, '09171234567', 'sarah.connor@raquelhris.com');

INSERT INTO `employee_addresses` (`employee_id`, `address_type`, `house_no`, `street`, `barangay`, `city`, `province`, `zip_code`) VALUES
(4, 'Residential', '45B', 'Sunrise Avenue', 'San Isidro', 'Quezon City', 'Metro Manila', '1100'),
(4, 'Permanent', '102', 'Palm Street', 'Sta. Lucia', 'Pasig', 'Metro Manila', '1608');

INSERT INTO `employee_details` (`employee_id`, `height_m`, `weight_kg`, `blood_type`, `citizenship`) VALUES
(4, 1.68, 58.00, 'A+', 'American');

INSERT INTO `employee_government_ids` (`employee_id`, `sss_number`, `philhealth_number`, `pagibig_number`, `tin_number`) VALUES
(4, '16-7654321-9', '21-987654321-1', '9876-5432-1098', '987-654-321-000');

INSERT INTO `employee_family` (`employee_id`, `member_type`, `surname`, `first_name`, `middle_name`, `occupation`) VALUES
(4, 'Father', 'Connor', 'Michael', 'David', 'Police Officer'),
(4, 'Mother', 'Williams', 'Catherine', 'Marie', 'Teacher');

INSERT INTO `employee_siblings` (`employee_id`, `surname`, `first_name`, `middle_name`, `date_of_birth`) VALUES
(4, 'Connor', 'Emily', 'Grace', '1992-08-19'),
(4, 'Connor', 'Daniel', 'James', '1995-11-03');

INSERT INTO `employee_education` (`employee_id`, `education_level`, `school_name`, `degree_course`, `period_from`, `period_to`, `highest_level_units`, `year_graduated`) VALUES
(4, 'Elementary', 'St. Peter Academy', NULL, '1994-06-01', '2000-03-31', 'Graduated', '2000'),
(4, 'Secondary', 'Quezon Science High School', NULL, '2000-06-01', '2004-03-31', 'Graduated', '2004'),
(4, 'College', 'University of Santo Tomas', 'BS Psychology', '2004-06-01', '2008-03-31', 'Graduated', '2008');

INSERT INTO `employee_work_experience` (`employee_id`, `date_from`, `date_to`, `job_title`, `company_name`, `monthly_salary`, `appointment_status`, `reason_for_leaving`) VALUES
(4, '2008-06-01', '2012-05-31', 'HR Assistant', 'Metro Retail Group', 22000.00, 'Full-time', 'Career growth'),
(4, '2012-06-01', '2018-12-31', 'Recruitment Associate', 'Prime Solutions Inc.', 32000.00, 'Full-time', 'Better opportunity'),
(4, '2019-01-01', '2024-01-15', 'HR Coordinator', 'Global Tech Services', 45000.00, 'Full-time', 'Relocation');

INSERT INTO `employee_trainings` (`employee_id`, `date_from`, `date_to`, `training_title`, `training_type`, `no_of_hours`, `conducted_by`) VALUES
(4, '2019-05-10', '2019-05-12', 'Employee Relations', 'Management', 24, 'PMAP'),
(4, '2022-03-20', '2022-03-22', 'HRIS Administration', 'Technical', 20, 'HR Tech Academy');

INSERT INTO `employee_voluntary_work` (`employee_id`, `organization_name`, `organization_address`, `date_from`, `date_to`, `no_of_hours`, `position_nature`) VALUES
(4, 'Youth Development Foundation', 'Quezon City', '2018-01-01', '2021-12-31', 240, 'Volunteer Mentor');

INSERT INTO `employee_eligibility` (`employee_id`, `license_title`, `license_number`, `date_of_exam`, `place_of_exam`) VALUES
(4, 'Civil Service Professional', 'CSE-2010-456789', '2010-08-15', 'Manila');

INSERT INTO `employee_skills` (`employee_id`, `skill_name`) VALUES
(4, 'Recruitment'), (4, 'Employee Relations'), (4, 'Payroll Administration'), (4, 'Public Speaking'), (4, 'HRIS Management');

INSERT INTO `employee_recognitions` (`employee_id`, `recognition_title`) VALUES
(4, 'Best HR Coordinator (2022)'), (4, 'Outstanding Specialist (2021)');

INSERT INTO `employee_memberships` (`employee_id`, `organization_name`) VALUES
(4, 'PMAP'), (4, 'HR Educators Association');

INSERT INTO `employee_real_properties` (`employee_id`, `description`, `kind`, `exact_location`, `assessed_value`, `market_value`, `acquisition_year_mode`, `acquisition_cost`) VALUES
(4, 'Condominium Unit', 'Residential', 'Quezon City', 3200000.00, 3800000.00, 'Purchased 2021', 3500000.00);

INSERT INTO `employee_personal_properties` (`employee_id`, `description`, `year_acquired`, `acquisition_cost`) VALUES
(4, 'Toyota Vios 2022', '2022', 950000.00);

INSERT INTO `employee_liabilities` (`employee_id`, `nature_of_liability`, `creditor_name`, `outstanding_balance`) VALUES
(4, 'Housing Loan', 'Security Bank', 1800000.00);

INSERT INTO `employee_references` (`employee_id`, `reference_name`, `reference_address`, `reference_telephone`) VALUES
(4, 'Angela Reyes', 'Makati City', '09181112222'),
(4, 'Mark Davidson', 'Pasig City', '09183334444');

INSERT INTO `employee_disclosures` (`employee_id`, `is_related_to_company`, `has_admin_offense`, `has_criminal_charge`, `has_criminal_conviction`, `has_been_separated`, `is_pwd`, `is_solo_parent`) VALUES
(4, 0, 0, 0, 0, 0, 0, 0);

INSERT INTO `employee_emergency_contacts` (`employee_id`, `contact_name`, `relationship`, `contact_number`) VALUES
(4, 'Emily Connor', 'Sister', '09179998888');

INSERT INTO `users` (`user_id`, `employee_id`, `username`, `email`, `password_hash`, `full_name`, `role`, `branch_id`, `is_active`, `first_login_completed`, `created_at`, `updated_at`) VALUES
(4, 4, 'sarah.connor', 'sarah.connor@raquelhris.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah Connor', 'HR Staff', 102, 1, 1, NOW(), NOW());




SET FOREIGN_KEY_CHECKS = 1;