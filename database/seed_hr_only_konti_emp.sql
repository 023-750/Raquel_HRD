-- Raquel HRIS: Exclusive HR Department Seed Data
-- This script provides a focused seed for HR Manager, HR Supervisor, and HR Staff
-- all assigned to the Main Branch.

SET FOREIGN_KEY_CHECKS = 0;
USE raquel_hris;

-- Clear relevant tables to ensure a clean start for this seed
DELETE FROM `users`; ALTER TABLE `users` AUTO_INCREMENT = 1;
DELETE FROM `employees`; ALTER TABLE `employees` AUTO_INCREMENT = 1;
DELETE FROM `job_titles`; ALTER TABLE `job_titles` AUTO_INCREMENT = 1;
DELETE FROM `departments`; ALTER TABLE `departments` AUTO_INCREMENT = 1;
DELETE FROM `branches`; ALTER TABLE `branches` AUTO_INCREMENT = 1;
DELETE FROM `rank_categories`; ALTER TABLE `rank_categories` AUTO_INCREMENT = 1;
DELETE FROM `employee_pds_submissions`; ALTER TABLE `employee_pds_submissions` AUTO_INCREMENT = 1;


-- ============================================
-- 1. SEED RANK CATEGORIES
-- ============================================
INSERT INTO `rank_categories` (`rank_category_id`, `rank_name`, `level_order`) VALUES
(1, 'Executives', 1),
(2, 'Management Team', 2),
(3, 'Manager', 3),
(4, 'Supervisor', 4),
(5, 'R&F', 5);

-- ============================================
-- 2. SEED MAIN BRANCH
-- ============================================
INSERT INTO `branches` (`branch_id`, `branch_name`, `location`) VALUES
(1, 'Raquel Pawnshop Main Office', 'RGC Building, Diversion Road, Sitio 1 Barangay Mayuwi Tayabas City');

-- ============================================
-- 3. SEED HR DEPARTMENT
-- ============================================
INSERT INTO `departments` (`department_id`, `department_name`, `description`) VALUES
(1, 'Human Resources', 'Handles personnel management, recruitment, and payroll.');

-- ============================================
-- 4. SEED HR JOB TITLES
-- ============================================
INSERT INTO `job_titles` (`job_title_id`, `job_title`, `rank_category_id`, `department_id`) VALUES
(1, 'HR Manager', 3, 1),
(2, 'HR Supervisor', 4, 1),
(3, 'HR Staff', 5, 1);

-- ============================================
-- 5. SEED HR EMPLOYEES
-- ============================================
-- HR Manager
INSERT INTO `employees` (`employee_id`, `employee_code`, `first_name`, `last_name`, `hire_date`, `job_title_id`, `rank_category_id`, `department_id`, `branch_id`, `employment_status`) VALUES
(1, 'HR-001', 'Elena', 'Delgado', '2020-01-15', 1, 3, 1, 1, 'Regular');

-- HR Supervisor
INSERT INTO `employees` (`employee_id`, `employee_code`, `first_name`, `last_name`, `hire_date`, `job_title_id`, `rank_category_id`, `department_id`, `branch_id`, `employment_status`) VALUES
(2, 'HR-002', 'John', 'Wick', '2021-06-20', 2, 4, 1, 1, 'Regular');

-- HR Staff
INSERT INTO `employees` (`employee_id`, `employee_code`, `first_name`, `last_name`, `hire_date`, `job_title_id`, `rank_category_id`, `department_id`, `branch_id`, `employment_status`) VALUES
(3, 'HR-003', 'Sarah', 'Connor', '2022-03-10', 3, 5, 1, 1, 'Regular');

-- ============================================
-- 6. SEED HR USERS (Password: password)
-- ============================================
-- Note: password_hash is for 'password'

-- System Admin (Standalone account, not an employee)
INSERT INTO `users` (`employee_id`, `username`, `email`, `password_hash`, `full_name`, `role`, `branch_id`) VALUES
(NULL, 'admin', 'admin@raquelhris.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Admin', 'Admin', 1);

-- HR Department Users
INSERT INTO `users` (`employee_id`, `username`, `email`, `password_hash`, `full_name`, `role`, `branch_id`) VALUES
(1, 'hr.manager', 'manager@raquelhris.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Elena Delgado', 'HR Manager', 1),
(2, 'hr.supervisor', 'supervisor@raquelhris.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Wick', 'HR Supervisor', 1),
(3, 'hr.staff', 'staff@raquelhris.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah Connor', 'HR Staff', 1);

SET FOREIGN_KEY_CHECKS = 1;

