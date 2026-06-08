USE raquel_hris;
SET FOREIGN_KEY_CHECKS = 0;

INSERT INTO users (employee_id, username, email, full_name, password_hash, role, branch_id, is_active, first_login_completed) 
VALUES
(101, 'elena.delgado', 'elena@company.com', 'Elena Delgado', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'HR Manager', 102, 1, 0),
(301, 'patricia.gomez', 'pat@company.com', 'Patricia Gomez', '$2y$10$ShFYgbhwbnwUnO9BE0/Ny.Tdohwd2rFgJQ4XtCZJh.tkJylDBLw9e', 'HR Supervisor', 102, 1, 0),
(302, 'miguel.torres', 'miguel@company.com', 'Miguel Torres', '$2y$10$.ZTuiD7q3wdnDCHbEiYQQOyqFpn4IKK4d6G6SLbuLMFTVmKBl8GLK', 'HR Staff', 102, 1, 0);

SET FOREIGN_KEY_CHECKS = 1;