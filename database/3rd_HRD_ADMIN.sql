USE raquel_hris;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. System Administrator (Standalone)
REPLACE INTO users (user_id, employee_id, username, email, full_name, password_hash, role, branch_id, is_active, created_at) VALUES
(1, NULL, 'admin', 'admin@example.com', 'System Admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 102, 1, NOW());

-- 2. Test Employee Record & Account
REPLACE INTO employees (employee_id, first_name, last_name, middle_name, employee_code, hire_date, job_title_id, job_title, department_id, rank_category_id, branch_id, employment_status, employment_type, created_at) VALUES
(101, 'Elena', 'Delgado', 'M', 'HRD-001', '2020-01-15', 700, 'HR Manager I', 7, 3, 102, 'Regular', 'Full-time', NOW());


REPLACE INTO employee_contacts (employee_id, personal_email) VALUES
(101, 'hr_manager@example.com');


SET FOREIGN_KEY_CHECKS = 1;