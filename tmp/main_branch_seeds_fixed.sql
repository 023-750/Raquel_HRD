-- ============================================
-- Main Branch Complete Employee Seeds
-- Fictional Employees with Complete PDS Data
-- ============================================

USE raquel_hris;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- EMPLOYEES - All Departments & Positions
-- ============================================

-- Office of the President (Department 10)
REPLACE INTO employees (employee_id, employee_code, first_name, last_name, middle_name, hire_date, date_of_birth, place_of_birth, gender, civil_status, job_title_id, job_title, department_id, rank_category_id, branch_id, employment_status, employment_type, profile_picture) VALUES
(1100, 'OOP-001', 'Robert', 'Chen', 'Martinez', '2010-01-15', '1975-03-20', 'Manila, Metro Manila', 'Male', 'Married', 1100, 'President and CEO', 10, 1, 102, 'Regular', 'Full-time', NULL),
(1101, 'OOP-002', 'Catherine', 'Williams', 'Santos', '2012-06-01', '1985-08-14', 'Quezon City, Metro Manila', 'Female', 'Single', 1101, 'Executive Assistant I', 10, 5, 102, 'Regular', 'Full-time', NULL),
(1102, 'OOP-003', 'Diana', 'Reyes', 'Ocampo', '2014-03-10', '1987-11-22', 'Makati, Metro Manila', 'Female', 'Married', 1102, 'Executive Assistant II', 10, 5, 102, 'Regular', 'Full-time', NULL),
(1103, 'OOP-004', 'Michelle', 'Torres', 'Valdez', '2016-09-05', '1990-05-18', 'Pasig, Metro Manila', 'Female', 'Single', 1103, 'Executive Assistant III', 10, 5, 102, 'Regular', 'Full-time', NULL),

-- Acquired Properties (Department 1)
(2100, 'AP-001', 'Vincent', 'Rodriguez', 'Cruz', '2013-02-01', '1978-06-15', 'Lucena City, Quezon', 'Male', 'Married', 100, 'VP for Acquired Properties', 1, 1, 102, 'Regular', 'Full-time', NULL),
(2101, 'AP-002', 'Gregory', 'Santos', 'Mendoza', '2014-04-15', '1982-09-10', 'Tayabas, Quezon', 'Male', 'Married', 101, 'AP Manager I', 1, 3, 102, 'Regular', 'Full-time', NULL),
(2102, 'AP-003', 'Fernando', 'Garcia', 'Rivera', '2015-07-20', '1984-12-05', 'Sariaya, Quezon', 'Male', 'Married', 102, 'AP Manager II', 1, 3, 102, 'Regular', 'Full-time', NULL),
(2103, 'AP-004', 'Alberto', 'Ramos', 'Dela Cruz', '2016-01-10', '1986-03-25', 'Candelaria, Quezon', 'Male', 'Single', 103, 'AP Manager III', 1, 3, 102, 'Regular', 'Full-time', NULL),
(2104, 'AP-005', 'Carlos', 'Bautista', 'Reyes', '2017-05-15', '1988-07-30', 'Tiaong, Quezon', 'Male', 'Married', 104, 'AP Manager IV', 1, 3, 102, 'Regular', 'Full-time', NULL),
(2105, 'AP-006', 'Angela', 'Cruz', 'Villanueva', '2018-08-01', '1990-01-12', 'Lucena City, Quezon', 'Female', 'Single', 105, 'AP Supervisor I', 1, 4, 102, 'Regular', 'Full-time', NULL),
(2106, 'AP-007', 'Patricia', 'Gonzales', 'Morales', '2019-03-20', '1992-04-18', 'Tayabas, Quezon', 'Female', 'Married', 106, 'AP Supervisor II', 1, 4, 102, 'Regular', 'Full-time', NULL),
(2107, 'AP-008', 'Rebecca', 'Fernandez', 'Santiago', '2020-06-15', '1994-08-22', 'Sariaya, Quezon', 'Female', 'Single', 107, 'AP Supervisor III', 1, 4, 102, 'Regular', 'Full-time', NULL),
(2108, 'AP-009', 'Jonathan', 'Perez', 'Navarro', '2022-01-10', '1998-11-05', 'Candelaria, Quezon', 'Male', 'Single', 108, 'AP Staff on Probation', 1, 5, 102, 'Probationary', 'Full-time', NULL),
(2109, 'AP-010', 'Daniel', 'Lopez', 'Castro', '2019-09-01', '1993-02-14', 'Lucena City, Quezon', 'Male', 'Single', 109, 'AP Staff I', 1, 5, 102, 'Regular', 'Full-time', NULL),
(2110, 'AP-011', 'Sophia', 'Martinez', 'Aquino', '2020-11-15', '1995-06-20', 'Tayabas, Quezon', 'Female', 'Married', 110, 'AP Staff II', 1, 5, 102, 'Regular', 'Full-time', NULL),
(2111, 'AP-012', 'Isabella', 'Hernandez', 'Gutierrez', '2021-03-10', '1996-09-25', 'Sariaya, Quezon', 'Female', 'Single', 111, 'AP Staff III', 1, 5, 102, 'Regular', 'Full-time', NULL),
(2112, 'AP-013', 'Christopher', 'Diaz', 'Pascual', '2018-05-20', '1991-12-30', 'Candelaria, Quezon', 'Male', 'Married', 112, 'AP Staff IV', 1, 5, 102, 'Regular', 'Full-time', NULL),
(2113, 'AP-014', 'Matthew', 'Jimenez', 'Torres', '2017-07-15', '1989-03-08', 'Tiaong, Quezon', 'Male', 'Married', 113, 'AP Staff V', 1, 5, 102, 'Regular', 'Full-time', NULL),
(2114, 'AP-015', 'Nicole', 'Ruiz', 'Flores', '2022-02-01', '1999-05-12', 'Lucena City, Quezon', 'Female', 'Single', 114, 'Sales Associate on Probation', 1, 5, 102, 'Probationary', 'Full-time', NULL),
(2115, 'AP-016', 'Samantha', 'Alvarez', 'Ramos', '2020-08-10', '1994-07-18', 'Tayabas, Quezon', 'Female', 'Single', 115, 'Sales Associate I', 1, 5, 102, 'Regular', 'Full-time', NULL),
(2116, 'AP-017', 'Jessica', 'Castillo', 'Mendez', '2019-10-20', '1993-10-22', 'Sariaya, Quezon', 'Female', 'Married', 116, 'Sales Associate II', 1, 5, 102, 'Regular', 'Full-time', NULL),

-- Audit (Department 2)
(3200, 'AUD-001', 'Richard', 'Thompson', 'Valdez', '2014-03-01', '1980-04-10', 'Makati, Metro Manila', 'Male', 'Married', 200, 'Audit Manager I', 2, 3, 102, 'Regular', 'Full-time', NULL),
(3201, 'AUD-002', 'Benjamin', 'Anderson', 'Cruz', '2015-06-15', '1983-07-15', 'Quezon City, Metro Manila', 'Male', 'Married', 201, 'Audit Manager II', 2, 3, 102, 'Regular', 'Full-time', NULL),
(3202, 'AUD-003', 'Anthony', 'Taylor', 'Santos', '2016-09-10', '1985-10-20', 'Pasig, Metro Manila', 'Male', 'Single', 202, 'Audit Manager III', 2, 3, 102, 'Regular', 'Full-time', NULL),
(3203, 'AUD-004', 'Jennifer', 'White', 'Garcia', '2018-01-15', '1988-01-25', 'Mandaluyong, Metro Manila', 'Female', 'Married', 203, 'Audit Supervisor I', 2, 4, 102, 'Regular', 'Full-time', NULL),
(3204, 'AUD-005', 'Sarah', 'Martin', 'Lopez', '2019-04-20', '1990-04-30', 'Taguig, Metro Manila', 'Female', 'Single', 204, 'Audit Supervisor II', 2, 4, 102, 'Regular', 'Full-time', NULL),
(3205, 'AUD-006', 'Laura', 'Jackson', 'Reyes', '2020-07-10', '1992-07-05', 'Manila, Metro Manila', 'Female', 'Married', 205, 'Audit Supervisor III', 2, 4, 102, 'Regular', 'Full-time', NULL),
(3206, 'AUD-007', 'Kevin', 'Harris', 'Perez', '2022-01-05', '1998-09-15', 'Quezon City, Metro Manila', 'Male', 'Single', 206, 'Auditor on Probation', 2, 5, 102, 'Probationary', 'Full-time', NULL),
(3207, 'AUD-008', 'Brandon', 'Clark', 'Torres', '2020-03-10', '1994-11-20', 'Pasig, Metro Manila', 'Male', 'Single', 207, 'Auditor I', 2, 5, 102, 'Regular', 'Full-time', NULL),
(3208, 'AUD-009', 'Amanda', 'Lewis', 'Martinez', '2019-08-15', '1993-12-25', 'Makati, Metro Manila', 'Female', 'Married', 208, 'Auditor II', 2, 5, 102, 'Regular', 'Full-time', NULL),
(3209, 'AUD-010', 'Stephanie', 'Walker', 'Hernandez', '2018-11-20', '1992-03-30', 'Mandaluyong, Metro Manila', 'Female', 'Single', 209, 'Auditor III', 2, 5, 102, 'Regular', 'Full-time', NULL),
(3210, 'AUD-011', 'Ryan', 'Robinson', 'Diaz', '2017-02-15', '1990-06-08', 'Taguig, Metro Manila', 'Male', 'Married', 210, 'Auditor IV', 2, 5, 102, 'Regular', 'Full-time', NULL),
(3211, 'AUD-012', 'Justin', 'Young', 'Jimenez', '2016-05-10', '1989-08-12', 'Manila, Metro Manila', 'Male', 'Married', 211, 'Auditor V', 2, 5, 102, 'Regular', 'Full-time', NULL),

-- Business Development (Department 3)
(4300, 'BD-001', 'William', 'King', 'Alvarez', '2015-04-01', '1981-05-20', 'Makati, Metro Manila', 'Male', 'Married', 300, 'Business Development Manager I', 3, 3, 102, 'Regular', 'Full-time', NULL),
(4301, 'BD-002', 'Emily', 'Wright', 'Castillo', '2022-01-15', '1999-07-25', 'Quezon City, Metro Manila', 'Female', 'Single', 301, 'Business Development Staff on Training', 3, 5, 102, 'Trainee', 'Full-time', NULL),
(4302, 'BD-003', 'Olivia', 'Scott', 'Ruiz', '2020-05-20', '1995-09-30', 'Pasig, Metro Manila', 'Female', 'Single', 302, 'Business Development Staff I', 3, 5, 102, 'Regular', 'Full-time', NULL),
(4303, 'BD-004', 'Ethan', 'Green', 'Morales', '2019-08-10', '1993-11-05', 'Taguig, Metro Manila', 'Male', 'Married', 303, 'Business Development Staff II', 3, 5, 102, 'Regular', 'Full-time', NULL),
(4304, 'BD-005', 'Camille', 'Navarro', 'Reyes', '2021-03-01', '1990-07-14', 'Quezon City, Metro Manila', 'Female', 'Single', 300, 'Business Development Manager I', 3, 3, 102, 'Regular', 'Full-time', NULL),

-- Compliance (Department 4)
(5400, 'COM-001', 'Thomas', 'Adams', 'Fernandez', '2016-02-01', '1983-03-15', 'Manila, Metro Manila', 'Male', 'Married', 400, 'Compliance Supervisor I', 4, 4, 102, 'Regular', 'Full-time', NULL),
(5401, 'COM-002', 'Linda', 'Baker', 'Navarro', '2017-05-15', '1986-06-20', 'Quezon City, Metro Manila', 'Female', 'Married', 401, 'Compliance Supervisor II', 4, 4, 102, 'Regular', 'Full-time', NULL),
(5402, 'COM-003', 'Barbara', 'Nelson', 'Castro', '2018-08-20', '1989-09-25', 'Makati, Metro Manila', 'Female', 'Single', 402, 'Compliance Supervisor III', 4, 4, 102, 'Regular', 'Full-time', NULL),
(5403, 'COM-004', 'Rachel', 'Carter', 'Aquino', '2019-11-10', '1992-12-30', 'Pasig, Metro Manila', 'Female', 'Single', 403, 'Compliance Staff I', 4, 5, 102, 'Regular', 'Full-time', NULL),
(5404, 'COM-005', 'Ashley', 'Mitchell', 'Gutierrez', '2020-02-15', '1994-03-08', 'Taguig, Metro Manila', 'Female', 'Married', 404, 'Compliance Staff II', 4, 5, 102, 'Regular', 'Full-time', NULL),
(5405, 'COM-006', 'Megan', 'Perez', 'Pascual', '2021-05-20', '1996-06-12', 'Mandaluyong, Metro Manila', 'Female', 'Single', 405, 'Compliance Staff III', 4, 5, 102, 'Regular', 'Full-time', NULL),

-- Finance (Department 5)
(6500, 'FIN-001', 'Charles', 'Roberts', 'Mendoza', '2011-03-01', '1976-04-10', 'Makati, Metro Manila', 'Male', 'Married', 500, 'VP for Finance', 5, 1, 102, 'Regular', 'Full-time', NULL),
(6501, 'FIN-002', 'Nancy', 'Turner', 'Rivera', '2015-06-15', '1984-07-15', 'Manila, Metro Manila', 'Female', 'Married', 501, 'Accounting Supervisor I', 5, 4, 102, 'Regular', 'Full-time', NULL),
(6502, 'FIN-003', 'Karen', 'Phillips', 'Dela Cruz', '2016-09-20', '1987-10-20', 'Quezon City, Metro Manila', 'Female', 'Married', 502, 'Accounting Supervisor II', 5, 4, 102, 'Regular', 'Full-time', NULL),
(6503, 'FIN-004', 'Betty', 'Campbell', 'Villanueva', '2017-12-10', '1989-01-25', 'Pasig, Metro Manila', 'Female', 'Single', 503, 'Accounting Supervisor III', 5, 4, 102, 'Regular', 'Full-time', NULL),
(6504, 'FIN-005', 'Helen', 'Parker', 'Gonzales', '2018-03-15', '1991-04-30', 'Taguig, Metro Manila', 'Female', 'Married', 504, 'Accounting Supervisor IV', 5, 4, 102, 'Regular', 'Full-time', NULL),
(6505, 'FIN-006', 'Donald', 'Evans', 'Morales', '2016-01-20', '1985-05-05', 'Makati, Metro Manila', 'Male', 'Married', 505, 'Treasury Supervisor I', 5, 4, 102, 'Regular', 'Full-time', NULL),
(6506, 'FIN-007', 'Kenneth', 'Edwards', 'Santiago', '2017-04-25', '1988-08-10', 'Manila, Metro Manila', 'Male', 'Married', 506, 'Treasury Supervisor II', 5, 4, 102, 'Regular', 'Full-time', NULL),
(6507, 'FIN-008', 'Steven', 'Collins', 'Fernandez', '2018-07-30', '1990-11-15', 'Quezon City, Metro Manila', 'Male', 'Single', 507, 'Treasury Supervisor III', 5, 4, 102, 'Regular', 'Full-time', NULL),
(6508, 'FIN-009', 'Edward', 'Stewart', 'Navarro', '2019-10-15', '1992-02-20', 'Pasig, Metro Manila', 'Male', 'Single', 508, 'Treasury Supervisor IV', 5, 4, 102, 'Regular', 'Full-time', NULL),
(6509, 'FIN-010', 'Brian', 'Sanchez', 'Castro', '2020-01-20', '1993-05-25', 'Taguig, Metro Manila', 'Male', 'Married', 509, 'Treasury Supervisor V', 5, 4, 102, 'Regular', 'Full-time', NULL),
(6510, 'FIN-011', 'Dorothy', 'Morris', 'Aquino', '2022-01-10', '1998-06-30', 'Mandaluyong, Metro Manila', 'Female', 'Single', 510, 'Accounting Staff on Probation', 5, 5, 102, 'Probationary', 'Full-time', NULL),
(6511, 'FIN-012', 'Lisa', 'Rogers', 'Gutierrez', '2020-04-15', '1995-09-08', 'Makati, Metro Manila', 'Female', 'Single', 511, 'Accounting Staff I', 5, 5, 102, 'Regular', 'Full-time', NULL),
(6512, 'FIN-013', 'Sandra', 'Reed', 'Pascual', '2019-07-20', '1994-12-12', 'Manila, Metro Manila', 'Female', 'Married', 512, 'Accounting Staff II', 5, 5, 102, 'Regular', 'Full-time', NULL),
(6513, 'FIN-014', 'Carol', 'Cook', 'Torres', '2018-10-25', '1993-03-18', 'Quezon City, Metro Manila', 'Female', 'Married', 513, 'Accounting Staff III', 5, 5, 102, 'Regular', 'Full-time', NULL),
(6514, 'FIN-015', 'Ruth', 'Morgan', 'Flores', '2017-01-30', '1991-06-22', 'Pasig, Metro Manila', 'Female', 'Single', 514, 'Accounting Staff IV', 5, 5, 102, 'Regular', 'Full-time', NULL),
(6515, 'FIN-016', 'Sharon', 'Bell', 'Ramos', '2016-04-10', '1990-09-28', 'Taguig, Metro Manila', 'Female', 'Married', 515, 'Accounting Staff V', 5, 5, 102, 'Regular', 'Full-time', NULL),
(6516, 'FIN-017', 'Jason', 'Murphy', 'Mendez', '2020-06-15', '1995-11-03', 'Makati, Metro Manila', 'Male', 'Single', 516, 'Treasury Staff I', 5, 5, 102, 'Regular', 'Full-time', NULL),
(6517, 'FIN-018', 'Jeffrey', 'Bailey', 'Valdez', '2019-09-20', '1994-02-08', 'Manila, Metro Manila', 'Male', 'Married', 517, 'Treasury Staff II', 5, 5, 102, 'Regular', 'Full-time', NULL),

-- General Services (Department 6)
(7600, 'GS-001', 'Paul', 'Rivera', 'Cruz', '2012-04-01', '1977-05-15', 'Lucena City, Quezon', 'Male', 'Married', 600, 'VP for General Services', 6, 1, 102, 'Regular', 'Full-time', NULL),
(7601, 'GS-002', 'Mark', 'Cooper', 'Santos', '2014-07-15', '1982-08-20', 'Tayabas, Quezon', 'Male', 'Married', 601, 'GS Manager I', 6, 3, 102, 'Regular', 'Full-time', NULL),
(7602, 'GS-003', 'Larry', 'Richardson', 'Garcia', '2015-10-20', '1984-11-25', 'Sariaya, Quezon', 'Male', 'Married', 602, 'GS Manager II', 6, 3, 102, 'Regular', 'Full-time', NULL),
(7603, 'GS-004', 'Scott', 'Cox', 'Lopez', '2016-01-25', '1986-02-28', 'Candelaria, Quezon', 'Male', 'Single', 603, 'GS Manager III', 6, 3, 102, 'Regular', 'Full-time', NULL),
(7604, 'GS-005', 'Frank', 'Howard', 'Reyes', '2017-04-30', '1988-06-05', 'Tiaong, Quezon', 'Male', 'Married', 604, 'GS Manager IV', 6, 3, 102, 'Regular', 'Full-time', NULL),
(7605, 'GS-006', 'Raymond', 'Ward', 'Perez', '2018-07-10', '1990-09-10', 'Lucena City, Quezon', 'Male', 'Married', 605, 'GS Supervisor I', 6, 4, 102, 'Regular', 'Full-time', NULL),
(7606, 'GS-007', 'Gregory', 'Torres', 'Martinez', '2019-10-15', '1992-12-15', 'Tayabas, Quezon', 'Male', 'Single', 606, 'GS Supervisor II', 6, 4, 102, 'Regular', 'Full-time', NULL),
(7607, 'GS-008', 'Joshua', 'Peterson', 'Hernandez', '2020-01-20', '1994-03-20', 'Sariaya, Quezon', 'Male', 'Single', 607, 'GS Supervisor III', 6, 4, 102, 'Regular', 'Full-time', NULL),
(7608, 'GS-009', 'Jerry', 'Gray', 'Diaz', '2021-04-25', '1996-06-25', 'Candelaria, Quezon', 'Male', 'Married', 608, 'GS Supervisor IV', 6, 4, 102, 'Regular', 'Full-time', NULL),
(7609, 'GS-010', 'Dennis', 'Ramirez', 'Jimenez', '2019-05-01', '1993-07-30', 'Tiaong, Quezon', 'Male', 'Married', 609, 'Driver I', 6, 5, 102, 'Regular', 'Full-time', NULL),
(7610, 'GS-011', 'Walter', 'James', 'Ruiz', '2018-08-10', '1992-10-05', 'Lucena City, Quezon', 'Male', 'Married', 610, 'Driver II', 6, 5, 102, 'Regular', 'Full-time', NULL),
(7611, 'GS-012', 'Patrick', 'Watson', 'Alvarez', '2017-11-15', '1991-01-10', 'Tayabas, Quezon', 'Male', 'Single', 611, 'Driver III', 6, 5, 102, 'Regular', 'Full-time', NULL),
(7612, 'GS-013', 'Peter', 'Brooks', 'Castillo', '2016-02-20', '1989-04-15', 'Sariaya, Quezon', 'Male', 'Married', 612, 'Driver IV', 6, 5, 102, 'Regular', 'Full-time', NULL),
(7613, 'GS-014', 'Harold', 'Kelly', 'Morales', '2015-05-25', '1987-07-20', 'Candelaria, Quezon', 'Male', 'Married', 613, 'Driver V', 6, 5, 102, 'Regular', 'Full-time', NULL),
(7614, 'GS-015', 'Henry', 'Sanders', 'Fernandez', '2020-03-01', '1994-08-25', 'Tiaong, Quezon', 'Male', 'Single', 614, 'Security Monitoring Staff I', 6, 5, 102, 'Regular', 'Full-time', NULL),
(7615, 'GS-016', 'Carl', 'Price', 'Navarro', '2019-06-10', '1993-11-30', 'Lucena City, Quezon', 'Male', 'Married', 615, 'Security Monitoring Staff II', 6, 5, 102, 'Regular', 'Full-time', NULL),
(7616, 'GS-017', 'Arthur', 'Bennett', 'Castro', '2018-09-15', '1992-03-08', 'Tayabas, Quezon', 'Male', 'Single', 616, 'Security Monitoring Staff III', 6, 5, 102, 'Regular', 'Full-time', NULL),
(7617, 'GS-018', 'Roger', 'Wood', 'Aquino', '2017-12-20', '1990-06-12', 'Sariaya, Quezon', 'Male', 'Married', 617, 'Security Monitoring Staff IV', 6, 5, 102, 'Regular', 'Full-time', NULL),
(7618, 'GS-019', 'Albert', 'Barnes', 'Gutierrez', '2020-07-01', '1995-09-18', 'Candelaria, Quezon', 'Male', 'Single', 618, 'Facilities Maintenance Staff I', 6, 5, 102, 'Regular', 'Full-time', NULL),
(7619, 'GS-020', 'Joe', 'Ross', 'Pascual', '2019-10-10', '1994-12-22', 'Tiaong, Quezon', 'Male', 'Married', 619, 'Facilities Maintenance Staff II', 6, 5, 102, 'Regular', 'Full-time', NULL),
(7620, 'GS-021', 'Eugene', 'Henderson', 'Torres', '2018-01-15', '1992-03-28', 'Lucena City, Quezon', 'Male', 'Single', 620, 'Facilities Maintenance Staff III', 6, 5, 102, 'Regular', 'Full-time', NULL),
(7621, 'GS-022', 'Ralph', 'Coleman', 'Flores', '2020-11-01', '1996-06-03', 'Tayabas, Quezon', 'Male', 'Single', 621, 'Warehouse Staff I', 6, 5, 102, 'Regular', 'Full-time', NULL),
(7622, 'GS-023', 'Willie', 'Jenkins', 'Ramos', '2019-02-15', '1993-09-08', 'Sariaya, Quezon', 'Male', 'Married', 622, 'Messenger I', 6, 5, 102, 'Regular', 'Full-time', NULL),
(7623, 'GS-024', 'Roy', 'Perry', 'Mendez', '2018-05-20', '1992-12-12', 'Candelaria, Quezon', 'Male', 'Single', 623, 'Messenger II', 6, 5, 102, 'Regular', 'Full-time', NULL),

-- Human Resources (Department 7)
(8700, 'HR-001', 'Margaret', 'Powell', 'Valdez', '2013-05-01', '1979-06-15', 'Manila, Metro Manila', 'Female', 'Married', 700, 'HR Manager I', 7, 3, 102, 'Regular', 'Full-time', NULL),
(8701, 'HR-002', 'Donna', 'Long', 'Cruz', '2014-08-15', '1982-09-20', 'Quezon City, Metro Manila', 'Female', 'Married', 701, 'HR Manager II', 7, 3, 102, 'Regular', 'Full-time', NULL),
(8702, 'HR-003', 'Judith', 'Patterson', 'Santos', '2015-11-20', '1985-12-25', 'Makati, Metro Manila', 'Female', 'Single', 702, 'HR Manager III', 7, 3, 102, 'Regular', 'Full-time', NULL),
(8703, 'HR-004', 'Virginia', 'Hughes', 'Garcia', '2016-02-25', '1987-03-30', 'Pasig, Metro Manila', 'Female', 'Married', 703, 'HR Manager IV', 7, 3, 102, 'Regular', 'Full-time', NULL),
(8704, 'HR-005', 'Deborah', 'Flores', 'Lopez', '2017-05-30', '1989-07-05', 'Taguig, Metro Manila', 'Female', 'Married', 704, 'HR Manager V', 7, 3, 102, 'Regular', 'Full-time', NULL),
(8705, 'HR-006', 'Jacqueline', 'Washington', 'Reyes', '2018-08-10', '1991-10-10', 'Mandaluyong, Metro Manila', 'Female', 'Single', 705, 'HR Supervisor I', 7, 4, 102, 'Regular', 'Full-time', NULL),
(8706, 'HR-007', 'Kathleen', 'Butler', 'Perez', '2019-11-15', '1993-01-15', 'Manila, Metro Manila', 'Female', 'Married', 706, 'HR Supervisor II', 7, 4, 102, 'Regular', 'Full-time', NULL),
(8707, 'HR-008', 'Joyce', 'Simmons', 'Martinez', '2020-02-20', '1995-04-20', 'Quezon City, Metro Manila', 'Female', 'Single', 707, 'HR Supervisor III', 7, 4, 102, 'Regular', 'Full-time', NULL),
(8708, 'HR-009', 'Diane', 'Foster', 'Hernandez', '2021-05-25', '1997-07-25', 'Makati, Metro Manila', 'Female', 'Married', 708, 'HR Supervisor IV', 7, 4, 102, 'Regular', 'Full-time', NULL),
(8709, 'HR-010', 'Alice', 'Gonzales', 'Diaz', '2018-06-01', '1992-08-30', 'Pasig, Metro Manila', 'Female', 'Single', 709, 'HR Supervisor V', 7, 4, 102, 'Regular', 'Full-time', NULL),
(8710, 'HR-011', 'Teresa', 'Bryant', 'Jimenez', '2022-01-15', '1999-10-05', 'Taguig, Metro Manila', 'Female', 'Single', 710, 'HR Staff on Probation', 7, 5, 102, 'Probationary', 'Full-time', NULL),
(8711, 'HR-012', 'Doris', 'Alexander', 'Ruiz', '2020-04-20', '1996-01-10', 'Mandaluyong, Metro Manila', 'Female', 'Single', 711, 'HR Staff I', 7, 5, 102, 'Regular', 'Full-time', NULL),
(8712, 'HR-013', 'Frances', 'Russell', 'Alvarez', '2019-07-25', '1994-04-15', 'Manila, Metro Manila', 'Female', 'Married', 712, 'HR Staff II', 7, 5, 102, 'Regular', 'Full-time', NULL),
(8713, 'HR-014', 'Jean', 'Griffin', 'Castillo', '2018-10-30', '1993-07-20', 'Quezon City, Metro Manila', 'Female', 'Single', 713, 'HR Staff III', 7, 5, 102, 'Regular', 'Full-time', NULL),
(8714, 'HR-015', 'Marie', 'Diaz', 'Morales', '2017-02-05', '1991-10-25', 'Makati, Metro Manila', 'Female', 'Married', 714, 'HR Staff IV', 7, 5, 102, 'Regular', 'Full-time', NULL),
(8715, 'HR-016', 'Kathryn', 'Hayes', 'Fernandez', '2016-05-10', '1990-01-30', 'Pasig, Metro Manila', 'Female', 'Married', 715, 'HR Staff V', 7, 5, 102, 'Regular', 'Full-time', NULL),

-- Information Technology (Department 8)
(9800, 'IT-001', 'Harold', 'Myers', 'Navarro', '2014-06-01', '1981-07-15', 'Quezon City, Metro Manila', 'Male', 'Married', 800, 'IT Manager I', 8, 3, 102, 'Regular', 'Full-time', NULL),
(9801, 'IT-002', 'Arthur', 'Ford', 'Castro', '2015-09-15', '1984-10-20', 'Makati, Metro Manila', 'Male', 'Married', 801, 'IT Manager II', 8, 3, 102, 'Regular', 'Full-time', NULL),
(9802, 'IT-003', 'Gerald', 'Hamilton', 'Aquino', '2016-12-20', '1986-01-25', 'Pasig, Metro Manila', 'Male', 'Single', 802, 'IT Manager III', 8, 3, 102, 'Regular', 'Full-time', NULL),
(9803, 'IT-004', 'Carl', 'Graham', 'Gutierrez', '2017-03-25', '1988-04-30', 'Taguig, Metro Manila', 'Male', 'Married', 803, 'IT Manager IV', 8, 3, 102, 'Regular', 'Full-time', NULL),
(9804, 'IT-005', 'Keith', 'Sullivan', 'Pascual', '2018-06-30', '1990-08-05', 'Mandaluyong, Metro Manila', 'Male', 'Single', 804, 'IT Supervisor I', 8, 4, 102, 'Regular', 'Full-time', NULL),
(9805, 'IT-006', 'Roger', 'Wallace', 'Torres', '2019-09-10', '1992-11-10', 'Manila, Metro Manila', 'Male', 'Married', 805, 'IT Supervisor II', 8, 4, 102, 'Regular', 'Full-time', NULL),
(9806, 'IT-007', 'Terry', 'West', 'Flores', '2020-12-15', '1994-02-15', 'Quezon City, Metro Manila', 'Male', 'Single', 806, 'IT Supervisor III', 8, 4, 102, 'Regular', 'Full-time', NULL),
(9807, 'IT-008', 'Lawrence', 'Cole', 'Ramos', '2021-03-20', '1996-05-20', 'Makati, Metro Manila', 'Male', 'Married', 807, 'IT Supervisor IV', 8, 4, 102, 'Regular', 'Full-time', NULL),
(9808, 'IT-009', 'Sean', 'Owens', 'Mendez', '2019-04-01', '1993-06-25', 'Pasig, Metro Manila', 'Male', 'Single', 808, 'IT Supervisor V', 8, 4, 102, 'Regular', 'Full-time', NULL),
(9809, 'IT-010', 'Philip', 'Reynolds', 'Valdez', '2022-01-20', '1998-09-30', 'Taguig, Metro Manila', 'Male', 'Single', 809, 'Programmer on Probation', 8, 5, 102, 'Probationary', 'Full-time', NULL),
(9810, 'IT-011', 'Andrew', 'Fisher', 'Cruz', '2020-05-25', '1995-11-05', 'Mandaluyong, Metro Manila', 'Male', 'Single', 810, 'Programmer I', 8, 5, 102, 'Regular', 'Full-time', NULL),
(9811, 'IT-012', 'Johnny', 'Ellis', 'Santos', '2020-02-10', '1995-02-10', 'Manila, Metro Manila', 'Male', 'Married', 811, 'Technical Support Staff I', 8, 5, 102, 'Regular', 'Full-time', NULL),
(9812, 'IT-013', 'Randy', 'Barnes', 'Garcia', '2019-05-15', '1994-05-15', 'Quezon City, Metro Manila', 'Male', 'Single', 812, 'Technical Support Staff II', 8, 5, 102, 'Regular', 'Full-time', NULL),
(9813, 'IT-014', 'Bobby', 'Ross', 'Lopez', '2018-08-20', '1993-08-20', 'Makati, Metro Manila', 'Male', 'Married', 813, 'Technical Support Staff III', 8, 5, 102, 'Regular', 'Full-time', NULL),
(9814, 'IT-015', 'Jack', 'Henderson', 'Reyes', '2017-11-25', '1992-11-25', 'Pasig, Metro Manila', 'Male', 'Single', 814, 'Technical Support Staff IV', 8, 5, 102, 'Regular', 'Full-time', NULL),
(9815, 'IT-016', 'Albert', 'Coleman', 'Perez', '2016-02-28', '1991-02-28', 'Taguig, Metro Manila', 'Male', 'Married', 815, 'Technical Support Staff V', 8, 5, 102, 'Regular', 'Full-time', NULL),
(9816, 'IT-017', 'Clarence', 'Jenkins', 'Martinez', '2020-08-05', '1996-03-05', 'Mandaluyong, Metro Manila', 'Male', 'Single', 816, 'Helpdesk Assistant I', 8, 5, 102, 'Regular', 'Full-time', NULL),
(9817, 'IT-018', 'Willie', 'Perry', 'Hernandez', '2019-11-10', '1995-06-10', 'Manila, Metro Manila', 'Male', 'Married', 817, 'Helpdesk Assistant II', 8, 5, 102, 'Regular', 'Full-time', NULL),
(9818, 'IT-019', 'Martin', 'Powell', 'Diaz', '2018-02-15', '1993-09-15', 'Quezon City, Metro Manila', 'Male', 'Single', 818, 'Helpdesk Assistant III', 8, 5, 102, 'Regular', 'Full-time', NULL),

-- Marketing (Department 9)
(10900, 'MKT-001', 'Michelle', 'Long', 'Jimenez', '2015-07-01', '1983-08-15', 'Makati, Metro Manila', 'Female', 'Married', 900, 'Marketing Manager I', 9, 3, 102, 'Regular', 'Full-time', NULL),
(10901, 'MKT-002', 'Kimberly', 'Patterson', 'Ruiz', '2016-10-15', '1986-11-20', 'Manila, Metro Manila', 'Female', 'Married', 901, 'Marketing Manager II', 9, 3, 102, 'Regular', 'Full-time', NULL),
(10902, 'MKT-003', 'Melissa', 'Hughes', 'Alvarez', '2018-01-20', '1989-02-25', 'Quezon City, Metro Manila', 'Female', 'Single', 902, 'Marketing Supervisor I', 9, 4, 102, 'Regular', 'Full-time', NULL),
(10903, 'MKT-004', 'Amy', 'Flores', 'Castillo', '2019-04-25', '1991-05-30', 'Pasig, Metro Manila', 'Female', 'Married', 903, 'Marketing Supervisor II', 9, 4, 102, 'Regular', 'Full-time', NULL),
(10904, 'MKT-005', 'Angela', 'Washington', 'Morales', '2022-01-30', '1999-07-05', 'Taguig, Metro Manila', 'Female', 'Single', 904, 'Marketing Staff on Probation', 9, 5, 102, 'Probationary', 'Full-time', NULL),
(10905, 'MKT-006', 'Melissa', 'Butler', 'Fernandez', '2020-07-10', '1996-10-10', 'Mandaluyong, Metro Manila', 'Female', 'Single', 905, 'Marketing Staff I', 9, 5, 102, 'Regular', 'Full-time', NULL),
(10906, 'MKT-007', 'Brenda', 'Simmons', 'Navarro', '2019-10-15', '1995-01-15', 'Manila, Metro Manila', 'Female', 'Married', 906, 'Marketing Staff II', 9, 5, 102, 'Regular', 'Full-time', NULL),

-- Operations (Department 11)
(11000, 'OPS-001', 'George', 'Foster', 'Castro', '2011-08-01', '1976-09-15', 'Lucena City, Quezon', 'Male', 'Married', 1000, 'VP for Operations', 11, 1, 102, 'Regular', 'Full-time', NULL),
(11001, 'OPS-002', 'Larry', 'Gonzales', 'Aquino', '2014-11-15', '1982-12-20', 'Tayabas, Quezon', 'Male', 'Married', 1001, 'Regional Manager I', 11, 3, 102, 'Regular', 'Full-time', NULL),
(11002, 'OPS-003', 'Eugene', 'Bryant', 'Gutierrez', '2015-02-20', '1984-03-25', 'Sariaya, Quezon', 'Male', 'Married', 1002, 'Regional Manager II', 11, 3, 102, 'Regular', 'Full-time', NULL),
(11003, 'OPS-004', 'Nicholas', 'Alexander', 'Pascual', '2022-01-25', '1999-04-30', 'Candelaria, Quezon', 'Male', 'Single', 1003, 'Area Coordinator on Training', 11, 4, 102, 'Trainee', 'Full-time', NULL),
(11004, 'OPS-005', 'Russell', 'Russell', 'Torres', '2018-05-30', '1991-07-05', 'Tiaong, Quezon', 'Male', 'Married', 1004, 'Area Coordinator I', 11, 4, 102, 'Regular', 'Full-time', NULL),
(11005, 'OPS-006', 'Bobby', 'Griffin', 'Flores', '2019-08-10', '1993-10-10', 'Lucena City, Quezon', 'Male', 'Single', 1005, 'Area Coordinator II', 11, 4, 102, 'Regular', 'Full-time', NULL),
(11006, 'OPS-007', 'Jordan', 'Diaz', 'Ramos', '2020-11-15', '1995-01-15', 'Tayabas, Quezon', 'Male', 'Married', 1006, 'Area Coordinator III', 11, 4, 102, 'Regular', 'Full-time', NULL),
(11007, 'OPS-008', 'Victor', 'Hayes', 'Mendez', '2019-02-20', '1992-04-20', 'Sariaya, Quezon', 'Male', 'Single', 1007, 'Focal Person I', 11, 4, 102, 'Regular', 'Full-time', NULL),
(11008, 'OPS-009', 'Douglas', 'Myers', 'Valdez', '2018-05-25', '1991-07-25', 'Candelaria, Quezon', 'Male', 'Married', 1008, 'Focal Person II', 11, 4, 102, 'Regular', 'Full-time', NULL),
(11009, 'OPS-010', 'Aaron', 'Ford', 'Cruz', '2017-08-30', '1990-10-30', 'Tiaong, Quezon', 'Male', 'Married', 1009, 'Focal Person III', 11, 4, 102, 'Regular', 'Full-time', NULL),
(11010, 'OPS-011', 'Jose', 'Hamilton', 'Santos', '2016-12-05', '1989-02-05', 'Lucena City, Quezon', 'Male', 'Single', 1010, 'Focal Person IV', 11, 4, 102, 'Regular', 'Full-time', NULL),
(11011, 'OPS-012', 'Adam', 'Graham', 'Garcia', '2015-03-10', '1987-05-10', 'Tayabas, Quezon', 'Male', 'Married', 1011, 'Focal Person V', 11, 4, 102, 'Regular', 'Full-time', NULL),
(11012, 'OPS-013', 'Nathan', 'Sullivan', 'Lopez', '2022-02-15', '1998-06-15', 'Sariaya, Quezon', 'Male', 'Single', 1012, 'Branch Staff on Probation', 11, 5, 102, 'Probationary', 'Full-time', NULL),
(11013, 'OPS-014', 'Zachary', 'Wallace', 'Reyes', '2020-06-20', '1996-09-20', 'Candelaria, Quezon', 'Male', 'Single', 1013, 'Branch Staff I', 11, 5, 102, 'Regular', 'Full-time', NULL),
(11014, 'OPS-015', 'Kyle', 'West', 'Perez', '2019-09-25', '1995-12-25', 'Tiaong, Quezon', 'Male', 'Married', 1014, 'Branch Staff II', 11, 5, 102, 'Regular', 'Full-time', NULL),
(11015, 'OPS-016', 'Noah', 'Cole', 'Martinez', '2018-12-30', '1994-03-30', 'Lucena City, Quezon', 'Male', 'Single', 1015, 'Branch Staff III', 11, 5, 102, 'Regular', 'Full-time', NULL),
(11016, 'OPS-017', 'Ethan', 'Owens', 'Hernandez', '2017-04-05', '1992-06-05', 'Tayabas, Quezon', 'Male', 'Married', 1016, 'Branch Staff IV', 11, 5, 102, 'Regular', 'Full-time', NULL),
(11017, 'OPS-018', 'Caleb', 'Reynolds', 'Diaz', '2016-07-10', '1991-09-10', 'Sariaya, Quezon', 'Male', 'Married', 1017, 'Branch Staff V', 11, 5, 102, 'Regular', 'Full-time', NULL),

-- Purchasing (Department 12)
(12200, 'PUR-001', 'Samuel', 'Fisher', 'Jimenez', '2016-08-01', '1985-09-15', 'Lucena City, Quezon', 'Male', 'Married', 1200, 'Purchasing Supervisor I', 12, 4, 102, 'Regular', 'Full-time', NULL),
(12201, 'PUR-002', 'Jeremy', 'Ellis', 'Ruiz', '2022-02-01', '1999-10-20', 'Tayabas, Quezon', 'Male', 'Single', 1201, 'Purchasing Supervisor on Training', 12, 4, 102, 'Trainee', 'Full-time', NULL),
(12202, 'PUR-003', 'Christian', 'Barnes', 'Alvarez', '2020-03-15', '1996-01-25', 'Sariaya, Quezon', 'Male', 'Single', 1202, 'Purchasing Staff I', 12, 5, 102, 'Regular', 'Full-time', NULL);

-- ============================================
-- EMPLOYEE CONTACTS
-- ============================================
REPLACE INTO employee_contacts (employee_id, personal_email, mobile_number, telephone_number) VALUES
-- Office of the President
(1100, 'robert.chen@email.com', '09171234501', '042-373-1001'),
(1101, 'catherine.williams@email.com', '09181234502', '042-373-1002'),
(1102, 'diana.reyes@email.com', '09191234503', '042-373-1003'),
(1103, 'michelle.torres@email.com', '09201234504', '042-373-1004'),
-- Acquired Properties
(2100, 'vincent.rodriguez@email.com', '09211234505', '042-373-2001'),
(2101, 'gregory.santos@email.com', '09221234506', '042-373-2002'),
(2102, 'fernando.garcia@email.com', '09231234507', '042-373-2003'),
(2103, 'alberto.ramos@email.com', '09241234508', '042-373-2004'),
(2104, 'carlos.bautista@email.com', '09251234509', '042-373-2005'),
(2105, 'angela.cruz@email.com', '09261234510', '042-373-2006'),
(2106, 'patricia.gonzales@email.com', '09271234511', '042-373-2007'),
(2107, 'rebecca.fernandez@email.com', '09281234512', '042-373-2008'),
(2108, 'jonathan.perez@email.com', '09291234513', '042-373-2009'),
(2109, 'daniel.lopez@email.com', '09301234514', '042-373-2010'),
(2110, 'sophia.martinez@email.com', '09311234515', '042-373-2011'),
(2111, 'isabella.hernandez@email.com', '09321234516', '042-373-2012'),
(2112, 'christopher.diaz@email.com', '09331234517', '042-373-2013'),
(2113, 'matthew.jimenez@email.com', '09341234518', '042-373-2014'),
(2114, 'nicole.ruiz@email.com', '09351234519', '042-373-2015'),
(2115, 'samantha.alvarez@email.com', '09361234520', '042-373-2016'),
(2116, 'jessica.castillo@email.com', '09371234521', '042-373-2017'),
-- Audit
(3200, 'richard.thompson@email.com', '09381234522', '042-373-3001'),
(3201, 'benjamin.anderson@email.com', '09391234523', '042-373-3002'),
(3202, 'anthony.taylor@email.com', '09401234524', '042-373-3003'),
(3203, 'jennifer.white@email.com', '09411234525', '042-373-3004'),
(3204, 'sarah.martin@email.com', '09421234526', '042-373-3005'),
(3205, 'laura.jackson@email.com', '09431234527', '042-373-3006'),
(3206, 'kevin.harris@email.com', '09441234528', '042-373-3007'),
(3207, 'brandon.clark@email.com', '09451234529', '042-373-3008'),
(3208, 'amanda.lewis@email.com', '09461234530', '042-373-3009'),
(3209, 'stephanie.walker@email.com', '09471234531', '042-373-3010'),
(3210, 'ryan.robinson@email.com', '09481234532', '042-373-3011'),
(3211, 'justin.young@email.com', '09491234533', '042-373-3012'),
-- Business Development
(4300, 'william.king@email.com', '09501234534', '042-373-4001'),
(4301, 'emily.wright@email.com', '09511234535', '042-373-4002'),
(4302, 'olivia.scott@email.com', '09521234536', '042-373-4003'),
(4303, 'ethan.green@email.com', '09531234537', '042-373-4004'),
(4304, 'camille.navarro@email.com', '09541234648', '042-373-4005'),
-- Compliance
(5400, 'thomas.adams@email.com', '09541234538', '042-373-5001'),
(5401, 'linda.baker@email.com', '09551234539', '042-373-5002'),
(5402, 'barbara.nelson@email.com', '09561234540', '042-373-5003'),
(5403, 'rachel.carter@email.com', '09571234541', '042-373-5004'),
(5404, 'ashley.mitchell@email.com', '09581234542', '042-373-5005'),
(5405, 'megan.perez@email.com', '09591234543', '042-373-5006'),
-- Finance
(6500, 'charles.roberts@email.com', '09601234544', '042-373-6001'),
(6501, 'nancy.turner@email.com', '09611234545', '042-373-6002'),
(6502, 'karen.phillips@email.com', '09621234546', '042-373-6003'),
(6503, 'betty.campbell@email.com', '09631234547', '042-373-6004'),
(6504, 'helen.parker@email.com', '09641234548', '042-373-6005'),
(6505, 'donald.evans@email.com', '09651234549', '042-373-6006'),
(6506, 'kenneth.edwards@email.com', '09661234550', '042-373-6007'),
(6507, 'steven.collins@email.com', '09671234551', '042-373-6008'),
(6508, 'edward.stewart@email.com', '09681234552', '042-373-6009'),
(6509, 'brian.sanchez@email.com', '09691234553', '042-373-6010'),
(6510, 'dorothy.morris@email.com', '09701234554', '042-373-6011'),
(6511, 'lisa.rogers@email.com', '09711234555', '042-373-6012'),
(6512, 'sandra.reed@email.com', '09721234556', '042-373-6013'),
(6513, 'carol.cook@email.com', '09731234557', '042-373-6014'),
(6514, 'ruth.morgan@email.com', '09741234558', '042-373-6015'),
(6515, 'sharon.bell@email.com', '09751234559', '042-373-6016'),
(6516, 'jason.murphy@email.com', '09761234560', '042-373-6017'),
(6517, 'jeffrey.bailey@email.com', '09771234561', '042-373-6018'),
-- General Services (sample for brevity - add all)
(7600, 'paul.rivera@email.com', '09781234562', '042-373-7001'),
(7601, 'mark.cooper@email.com', '09791234563', '042-373-7002'),
(7602, 'larry.richardson@email.com', '09801234564', '042-373-7003'),
(7603, 'scott.cox@email.com', '09811234565', '042-373-7004'),
(7604, 'frank.howard@email.com', '09821234566', '042-373-7005'),
(7605, 'raymond.ward@email.com', '09831234567', '042-373-7006'),
(7606, 'gregory.torres@email.com', '09841234568', '042-373-7007'),
(7607, 'joshua.peterson@email.com', '09851234569', '042-373-7008'),
(7608, 'jerry.gray@email.com', '09861234570', '042-373-7009'),
(7609, 'dennis.ramirez@email.com', '09871234571', '042-373-7010'),
(7610, 'walter.james@email.com', '09881234572', '042-373-7011'),
(7611, 'patrick.watson@email.com', '09891234573', '042-373-7012'),
(7612, 'peter.brooks@email.com', '09901234574', '042-373-7013'),
(7613, 'harold.kelly@email.com', '09911234575', '042-373-7014'),
(7614, 'henry.sanders@email.com', '09921234576', '042-373-7015'),
(7615, 'carl.price@email.com', '09931234577', '042-373-7016'),
(7616, 'arthur.bennett@email.com', '09941234578', '042-373-7017'),
(7617, 'roger.wood@email.com', '09951234579', '042-373-7018'),
(7618, 'albert.barnes@email.com', '09961234580', '042-373-7019'),
(7619, 'joe.ross@email.com', '09971234581', '042-373-7020'),
(7620, 'eugene.henderson@email.com', '09981234582', '042-373-7021'),
(7621, 'ralph.coleman@email.com', '09991234583', '042-373-7022'),
(7622, 'willie.jenkins@email.com', '09101234584', '042-373-7023'),
(7623, 'roy.perry@email.com', '09111234585', '042-373-7024'),
-- Human Resources
(8700, 'margaret.powell@email.com', '09121234586', '042-373-8001'),
(8701, 'donna.long@email.com', '09131234587', '042-373-8002'),
(8702, 'judith.patterson@email.com', '09141234588', '042-373-8003'),
(8703, 'virginia.hughes@email.com', '09151234589', '042-373-8004'),
(8704, 'deborah.flores@email.com', '09161234590', '042-373-8005'),
(8705, 'jacqueline.washington@email.com', '09171234591', '042-373-8006'),
(8706, 'kathleen.butler@email.com', '09181234592', '042-373-8007'),
(8707, 'joyce.simmons@email.com', '09191234593', '042-373-8008'),
(8708, 'diane.foster@email.com', '09201234594', '042-373-8009'),
(8709, 'alice.gonzales@email.com', '09211234595', '042-373-8010'),
(8710, 'teresa.bryant@email.com', '09221234596', '042-373-8011'),
(8711, 'doris.alexander@email.com', '09231234597', '042-373-8012'),
(8712, 'frances.russell@email.com', '09241234598', '042-373-8013'),
(8713, 'jean.griffin@email.com', '09251234599', '042-373-8014'),
(8714, 'marie.diaz@email.com', '09261234600', '042-373-8015'),
(8715, 'kathryn.hayes@email.com', '09271234601', '042-373-8016'),
-- Information Technology
(9800, 'harold.myers@email.com', '09281234602', '042-373-9001'),
(9801, 'arthur.ford@email.com', '09291234603', '042-373-9002'),
(9802, 'gerald.hamilton@email.com', '09301234604', '042-373-9003'),
(9803, 'carl.graham@email.com', '09311234605', '042-373-9004'),
(9804, 'keith.sullivan@email.com', '09321234606', '042-373-9005'),
(9805, 'roger.wallace@email.com', '09331234607', '042-373-9006'),
(9806, 'terry.west@email.com', '09341234608', '042-373-9007'),
(9807, 'lawrence.cole@email.com', '09351234609', '042-373-9008'),
(9808, 'sean.owens@email.com', '09361234610', '042-373-9009'),
(9809, 'philip.reynolds@email.com', '09371234611', '042-373-9010'),
(9810, 'andrew.fisher@email.com', '09381234612', '042-373-9011'),
(9811, 'johnny.ellis@email.com', '09391234613', '042-373-9012'),
(9812, 'randy.barnes@email.com', '09401234614', '042-373-9013'),
(9813, 'bobby.ross@email.com', '09411234615', '042-373-9014'),
(9814, 'jack.henderson@email.com', '09421234616', '042-373-9015'),
(9815, 'albert.coleman@email.com', '09431234617', '042-373-9016'),
(9816, 'clarence.jenkins@email.com', '09441234618', '042-373-9017'),
(9817, 'willie.perry@email.com', '09451234619', '042-373-9018'),
(9818, 'martin.powell@email.com', '09461234620', '042-373-9019'),
-- Marketing
(10900, 'michelle.long@email.com', '09471234621', '042-373-10001'),
(10901, 'kimberly.patterson@email.com', '09481234622', '042-373-10002'),
(10902, 'melissa.hughes@email.com', '09491234623', '042-373-10003'),
(10903, 'amy.flores@email.com', '09501234624', '042-373-10004'),
(10904, 'angela.washington@email.com', '09511234625', '042-373-10005'),
(10905, 'melissa.butler@email.com', '09521234626', '042-373-10006'),
(10906, 'brenda.simmons@email.com', '09531234627', '042-373-10007'),
-- Operations
(11000, 'george.foster@email.com', '09541234628', '042-373-11001'),
(11001, 'larry.gonzales@email.com', '09551234629', '042-373-11002'),
(11002, 'eugene.bryant@email.com', '09561234630', '042-373-11003'),
(11003, 'nicholas.alexander@email.com', '09571234631', '042-373-11004'),
(11004, 'russell.russell@email.com', '09581234632', '042-373-11005'),
(11005, 'bobby.griffin@email.com', '09591234633', '042-373-11006'),
(11006, 'jordan.diaz@email.com', '09601234634', '042-373-11007'),
(11007, 'victor.hayes@email.com', '09611234635', '042-373-11008'),
(11008, 'douglas.myers@email.com', '09621234636', '042-373-11009'),
(11009, 'aaron.ford@email.com', '09631234637', '042-373-11010'),
(11010, 'jose.hamilton@email.com', '09641234638', '042-373-11011'),
(11011, 'adam.graham@email.com', '09651234639', '042-373-11012'),
(11012, 'nathan.sullivan@email.com', '09661234640', '042-373-11013'),
(11013, 'zachary.wallace@email.com', '09671234641', '042-373-11014'),
(11014, 'kyle.west@email.com', '09681234642', '042-373-11015'),
(11015, 'noah.cole@email.com', '09691234643', '042-373-11016'),
(11016, 'ethan.owens@email.com', '09701234644', '042-373-11017'),
(11017, 'caleb.reynolds@email.com', '09711234645', '042-373-11018'),
-- Purchasing
(12200, 'samuel.fisher@email.com', '09721234646', '042-373-12001'),
(12201, 'jeremy.ellis@email.com', '09731234647', '042-373-12002'),
(12202, 'christian.barnes@email.com', '09741234648', '042-373-12003');

-- ============================================
-- EMPLOYEE DETAILS (Physical & Citizenship)
-- ============================================
REPLACE INTO employee_details (employee_id, height_m, weight_kg, blood_type, citizenship) VALUES
-- Office of the President
(1100, 1.78, 82.0, 'O+', 'Filipino'),
(1101, 1.65, 58.0, 'A+', 'Filipino'),
(1102, 1.62, 55.0, 'B+', 'Filipino'),
(1103, 1.68, 60.0, 'AB+', 'Filipino'),
-- Acquired Properties
(2100, 1.75, 78.0, 'O+', 'Filipino'),
(2101, 1.72, 75.0, 'A+', 'Filipino'),
(2102, 1.76, 80.0, 'B+', 'Filipino'),
(2103, 1.70, 72.0, 'O-', 'Filipino'),
(2104, 1.74, 77.0, 'A-', 'Filipino'),
(2105, 1.63, 57.0, 'B+', 'Filipino'),
(2106, 1.66, 59.0, 'AB+', 'Filipino'),
(2107, 1.64, 56.0, 'O+', 'Filipino'),
(2108, 1.71, 70.0, 'A+', 'Filipino'),
(2109, 1.69, 68.0, 'B+', 'Filipino'),
(2110, 1.62, 54.0, 'O+', 'Filipino'),
(2111, 1.65, 58.0, 'A+', 'Filipino'),
(2112, 1.73, 74.0, 'B+', 'Filipino'),
(2113, 1.72, 73.0, 'O+', 'Filipino'),
(2114, 1.60, 52.0, 'AB+', 'Filipino'),
(2115, 1.64, 56.0, 'A+', 'Filipino'),
(2116, 1.67, 60.0, 'B+', 'Filipino'),
-- Audit
(3200, 1.77, 81.0, 'A+', 'Filipino'),
(3201, 1.75, 79.0, 'O+', 'Filipino'),
(3202, 1.73, 76.0, 'B+', 'Filipino'),
(3203, 1.66, 59.0, 'A+', 'Filipino'),
(3204, 1.64, 57.0, 'O+', 'Filipino'),
(3205, 1.67, 61.0, 'AB+', 'Filipino'),
(3206, 1.70, 69.0, 'B+', 'Filipino'),
(3207, 1.72, 71.0, 'O+', 'Filipino'),
(3208, 1.63, 56.0, 'A+', 'Filipino'),
(3209, 1.65, 58.0, 'B+', 'Filipino'),
(3210, 1.74, 75.0, 'O+', 'Filipino'),
(3211, 1.76, 78.0, 'A+', 'Filipino'),
-- Business Development
(4300, 1.78, 83.0, 'O+', 'Filipino'),
(4301, 1.61, 53.0, 'A+', 'Filipino'),
(4302, 1.64, 57.0, 'B+', 'Filipino'),
(4303, 1.71, 72.0, 'O+', 'Filipino'),
(4304, 1.65, 56.0, 'A+', 'Filipino'),
-- Compliance
(5400, 1.75, 77.0, 'A+', 'Filipino'),
(5401, 1.66, 60.0, 'B+', 'Filipino'),
(5402, 1.65, 59.0, 'O+', 'Filipino'),
(5403, 1.63, 56.0, 'AB+', 'Filipino'),
(5404, 1.67, 61.0, 'A+', 'Filipino'),
(5405, 1.64, 58.0, 'B+', 'Filipino'),
-- Finance (sample only due to size)
(6500, 1.79, 84.0, 'O+', 'Filipino'),
(6501, 1.67, 62.0, 'A+', 'Filipino'),
(6502, 1.68, 63.0, 'B+', 'Filipino'),
(6503, 1.65, 59.0, 'O+', 'Filipino'),
(6504, 1.66, 60.0, 'AB+', 'Filipino'),
(6505, 1.76, 79.0, 'A+', 'Filipino'),
(6506, 1.77, 80.0, 'B+', 'Filipino'),
(6507, 1.73, 74.0, 'O+', 'Filipino'),
(6508, 1.72, 73.0, 'A+', 'Filipino'),
(6509, 1.74, 76.0, 'B+', 'Filipino'),
(6510, 1.63, 55.0, 'O+', 'Filipino'),
(6511, 1.64, 57.0, 'A+', 'Filipino'),
(6512, 1.66, 60.0, 'B+', 'Filipino'),
(6513, 1.67, 61.0, 'O+', 'Filipino'),
(6514, 1.65, 58.0, 'AB+', 'Filipino'),
(6515, 1.68, 62.0, 'A+', 'Filipino'),
(6516, 1.71, 71.0, 'B+', 'Filipino'),
(6517, 1.73, 74.0, 'O+', 'Filipino');

-- Note: Add remaining employees following same pattern
-- For brevity, showing representative samples. Full implementation would include all 100+ employees.

-- ============================================
-- EMPLOYEE ADDRESSES
-- ============================================
REPLACE INTO employee_addresses (employee_id, address_type, house_no, street, subdivision, barangay, city, province, zip_code) VALUES
-- Office of the President
(1100, 'Residential', '123', 'Maharlika St', 'Executive Village', 'San Isidro', 'Tayabas City', 'Quezon', '4327'),
(1100, 'Permanent', '123', 'Maharlika St', 'Executive Village', 'San Isidro', 'Tayabas City', 'Quezon', '4327'),
(1101, 'Residential', '45', 'Commonwealth Ave', 'Rosewood Homes', 'Batasan Hills', 'Quezon City', 'Metro Manila', '1126'),
(1101, 'Permanent', '45', 'Commonwealth Ave', 'Rosewood Homes', 'Batasan Hills', 'Quezon City', 'Metro Manila', '1126'),
(1102, 'Residential', '67', 'Ayala Avenue', 'Salcedo Village', 'Poblacion', 'Makati City', 'Metro Manila', '1227'),
(1102, 'Permanent', '22', 'Riverside Road', '', 'San Roque', 'Calamba', 'Laguna', '4027'),
(1103, 'Residential', '89', 'Shaw Blvd', 'Capitol Hills', 'Kapitolyo', 'Pasig City', 'Metro Manila', '1603'),
(1103, 'Permanent', '89', 'Shaw Blvd', 'Capitol Hills', 'Kapitolyo', 'Pasig City', 'Metro Manila', '1603'),
-- Acquired Properties
(2100, 'Residential', '234', 'Diversion Road', 'Palm Grove', 'Mayuwi', 'Lucena City', 'Quezon', '4301'),
(2100, 'Permanent', '234', 'Diversion Road', 'Palm Grove', 'Mayuwi', 'Lucena City', 'Quezon', '4301'),
(2101, 'Residential', '156', 'Rizal Avenue', '', 'Poblacion', 'Tayabas City', 'Quezon', '4327'),
(2101, 'Permanent', '156', 'Rizal Avenue', '', 'Poblacion', 'Tayabas City', 'Quezon', '4327'),
(2102, 'Residential', '78', 'Highway Road', '', 'Concepcion Palahid', 'Sariaya', 'Quezon', '4322'),
(2102, 'Permanent', '78', 'Highway Road', '', 'Concepcion Palahid', 'Sariaya', 'Quezon', '4322'),
(2103, 'Residential', '92', 'Maharlika Highway', '', 'Mangilag Sur', 'Candelaria', 'Quezon', '4323'),
(2103, 'Permanent', '92', 'Maharlika Highway', '', 'Mangilag Sur', 'Candelaria', 'Quezon', '4323'),
(2104, 'Residential', '45', 'San Isidro Street', '', 'San Isidro', 'Tiaong', 'Quezon', '4325'),
(2104, 'Permanent', '45', 'San Isidro Street', '', 'San Isidro', 'Tiaong', 'Quezon', '4325');

-- Note: Add remaining addresses for all employees following same pattern

-- ============================================
-- EMPLOYEE FAMILY BACKGROUND
-- ============================================
REPLACE INTO employee_family (employee_id, member_type, surname, first_name, middle_name, occupation) VALUES
-- Office of the President
(1100, 'Father', 'Chen', 'Roberto', 'Martinez', 'Retired Business Owner'),
(1100, 'Mother', 'Chen', 'Elena', 'Rodriguez', 'Retired Teacher'),
(1100, 'Spouse', 'Chen', 'Maria', 'Santos', 'Physician'),
(1101, 'Father', 'Williams', 'John', 'David', 'Engineer'),
(1101, 'Mother', 'Williams', 'Rose', 'Santos', 'Accountant'),
(1102, 'Father', 'Reyes', 'Fernando', 'Ocampo', 'Government Employee'),
(1102, 'Mother', 'Reyes', 'Carmen', 'Cruz', 'Nurse'),
(1102, 'Spouse', 'Reyes', 'Antonio', 'Valdez', 'Architect'),
(1103, 'Father', 'Torres', 'Miguel', 'Valdez', 'Businessman'),
(1103, 'Mother', 'Torres', 'Patricia', 'Gomez', 'Teacher'),
-- Acquired Properties
(2100, 'Father', 'Rodriguez', 'Vicente', 'Cruz', 'Retired Engineer'),
(2100, 'Mother', 'Rodriguez', 'Luisa', 'Santos', 'Retired Nurse'),
(2100, 'Spouse', 'Rodriguez', 'Ana', 'Garcia', 'Business Manager'),
(2101, 'Father', 'Santos', 'Carlos', 'Mendoza', 'Farmer'),
(2101, 'Mother', 'Santos', 'Rosa', 'Reyes', 'Homemaker'),
(2101, 'Spouse', 'Santos', 'Jennifer', 'Cruz', 'Teacher'),
(2102, 'Father', 'Garcia', 'Manuel', 'Rivera', 'Government Employee'),
(2102, 'Mother', 'Garcia', 'Teresa', 'Lopez', 'Teacher'),
(2102, 'Spouse', 'Garcia', 'Lisa', 'Santos', 'Pharmacist'),
(2103, 'Father', 'Ramos', 'Antonio', 'Dela Cruz', 'Engineer'),
(2103, 'Mother', 'Ramos', 'Marilyn', 'Torres', 'Accountant'),
(2104, 'Father', 'Bautista', 'Eduardo', 'Reyes', 'Businessman'),
(2104, 'Mother', 'Bautista', 'Gloria', 'Santos', 'Educator'),
(2104, 'Spouse', 'Bautista', 'Sandra', 'Cruz', 'Lawyer'),
-- Audit Department
(3200, 'Father', 'Thompson', 'William', 'Valdez', 'Retired Auditor'),
(3200, 'Mother', 'Thompson', 'Margaret', 'Cruz', 'Retired Teacher'),
(3200, 'Spouse', 'Thompson', 'Susan', 'Garcia', 'CPA'),
(3201, 'Father', 'Anderson', 'Robert', 'Cruz', 'Engineer'),
(3201, 'Mother', 'Anderson', 'Linda', 'Santos', 'Nurse'),
(3201, 'Spouse', 'Anderson', 'Rachel', 'Lopez', 'Doctor'),
-- Finance Department
(6500, 'Father', 'Roberts', 'George', 'Mendoza', 'Retired Banker'),
(6500, 'Mother', 'Roberts', 'Elizabeth', 'Cruz', 'Retired Accountant'),
(6500, 'Spouse', 'Roberts', 'Catherine', 'Valdez', 'CFO'),
-- HR Department
(8700, 'Father', 'Powell', 'James', 'Valdez', 'Retired HR Director'),
(8700, 'Mother', 'Powell', 'Mary', 'Santos', 'Retired Psychologist'),
(8700, 'Spouse', 'Powell', 'David', 'Cruz', 'Corporate Lawyer'),
-- IT Department
(9800, 'Father', 'Myers', 'Richard', 'Navarro', 'Engineer'),
(9800, 'Mother', 'Myers', 'Nancy', 'Cruz', 'Teacher'),
(9800, 'Spouse', 'Myers', 'Jennifer', 'Santos', 'Software Engineer'),
-- Operations Department
(11000, 'Father', 'Foster', 'Thomas', 'Castro', 'Retired Operations Manager'),
(11000, 'Mother', 'Foster', 'Barbara', 'Santos', 'Retired Educator'),
(11000, 'Spouse', 'Foster', 'Helen', 'Cruz', 'Business Consultant');

-- ============================================
-- EMPLOYEE CHILDREN
-- ============================================
REPLACE INTO employee_children (employee_id, surname, first_name, middle_name, date_of_birth) VALUES
(1100, 'Chen', 'Robert Jr.', 'Santos', '2005-04-15'),
(1100, 'Chen', 'Michelle', 'Santos', '2008-08-20'),
(1100, 'Chen', 'David', 'Santos', '2012-11-10'),
(1102, 'Reyes', 'Antonio Jr.', 'Valdez', '2016-03-05'),
(1102, 'Reyes', 'Sofia', 'Valdez', '2019-07-12'),
(2100, 'Rodriguez', 'Vincent Jr.', 'Garcia', '2010-06-18'),
(2100, 'Rodriguez', 'Isabella', 'Garcia', '2013-09-22'),
(2101, 'Santos', 'Gregory Jr.', 'Cruz', '2015-02-14'),
(2101, 'Santos', 'Samantha', 'Cruz', '2018-05-28'),
(2102, 'Garcia', 'Fernando Jr.', 'Santos', '2014-10-03'),
(2104, 'Bautista', 'Carlos Jr.', 'Cruz', '2017-12-20'),
(3200, 'Thompson', 'Richard Jr.', 'Garcia', '2012-01-15'),
(3200, 'Thompson', 'Emily', 'Garcia', '2015-04-08'),
(3201, 'Anderson', 'Benjamin Jr.', 'Lopez', '2016-08-25'),
(6500, 'Roberts', 'Charles Jr.', 'Valdez', '2008-03-10'),
(6500, 'Roberts', 'Sarah', 'Valdez', '2011-07-18'),
(6500, 'Roberts', 'Michael', 'Valdez', '2014-11-22'),
(8700, 'Powell', 'Margaret Jr.', 'Cruz', '2010-05-30'),
(8700, 'Powell', 'James', 'Cruz', '2013-09-14'),
(9800, 'Myers', 'Harold Jr.', 'Santos', '2015-02-20'),
(11000, 'Foster', 'George Jr.', 'Cruz', '2009-10-12'),
(11000, 'Foster', 'Catherine', 'Cruz', '2012-12-28');

-- ============================================
-- EMPLOYEE SIBLINGS
-- ============================================
REPLACE INTO employee_siblings (employee_id, surname, first_name, middle_name, date_of_birth) VALUES
(1100, 'Chen', 'Michael', 'Martinez', '1978-06-25'),
(1100, 'Chen', 'Patricia', 'Martinez', '1980-11-30'),
(1101, 'Williams', 'Jennifer', 'David', '1987-02-14'),
(1102, 'Reyes', 'Fernando Jr.', 'Ocampo', '1985-09-18'),
(1102, 'Reyes', 'Carmen', 'Ocampo', '1989-12-22'),
(2100, 'Rodriguez', 'Antonio', 'Cruz', '1980-08-10'),
(2101, 'Santos', 'Maria', 'Mendoza', '1984-03-15'),
(2101, 'Santos', 'Jose', 'Mendoza', '1986-07-20'),
(2102, 'Garcia', 'Roberto', 'Rivera', '1982-11-05'),
(3200, 'Thompson', 'Elizabeth', 'Valdez', '1982-06-12'),
(6500, 'Roberts', 'William', 'Mendoza', '1978-10-25'),
(8700, 'Powell', 'Susan', 'Valdez', '1981-04-18'),
(9800, 'Myers', 'Patricia', 'Navarro', '1983-08-22'),
(11000, 'Foster', 'Robert', 'Castro', '1978-12-08');

-- ============================================
-- EDUCATIONAL BACKGROUND
-- ============================================
REPLACE INTO employee_education (employee_id, education_level, school_name, degree_course, year_graduated, honors_received) VALUES
-- Office of the President
(1100, 'Elementary', 'Lucena Elementary School', NULL, '1987', NULL),
(1100, 'Secondary', 'Quezon National High School', NULL, '1991', NULL),
(1100, 'College', 'University of the Philippines', 'BS Business Administration', '1995', 'Cum Laude'),
(1100, 'Graduate Studies', 'Asian Institute of Management', 'MBA', '2000', NULL),
(1101, 'Elementary', 'Quezon City Central School', NULL, '1997', NULL),
(1101, 'Secondary', 'Quezon City High School', NULL, '2001', NULL),
(1101, 'College', 'University of Santo Tomas', 'BS Business Administration', '2005', NULL),
(1102, 'Elementary', 'Makati Elementary School', NULL, '1999', NULL),
(1102, 'Secondary', 'Makati Science High School', NULL, '2003', NULL),
(1102, 'College', 'De La Salle University', 'BS Business Management', '2007', NULL),
(1103, 'Elementary', 'Pasig Elementary School', NULL, '2002', NULL),
(1103, 'Secondary', 'Pasig Catholic College', NULL, '2006', NULL),
(1103, 'College', 'Ateneo de Manila University', 'AB Communication', '2010', NULL),
-- Acquired Properties
(2100, 'Elementary', 'Lucena City Central School', NULL, '1990', NULL),
(2100, 'Secondary', 'Manuel S. Enverga Memorial School', NULL, '1994', NULL),
(2100, 'College', 'University of the Philippines', 'BS Real Estate Management', '1998', NULL),
(2100, 'Graduate Studies', 'Ateneo de Manila University', 'MBA', '2005', NULL),
(2101, 'Elementary', 'Tayabas Elementary School', NULL, '1994', NULL),
(2101, 'Secondary', 'Tayabas National High School', NULL, '1998', NULL),
(2101, 'College', 'Southern Luzon State University', 'BS Business Administration', '2002', NULL),
(2102, 'Elementary', 'Sariaya Central School', NULL, '1996', NULL),
(2102, 'Secondary', 'Sariaya National High School', NULL, '2000', NULL),
(2102, 'College', 'Polytechnic University of the Philippines', 'BS Real Estate Management', '2004', NULL),
-- Audit Department
(3200, 'Elementary', 'Makati Elementary School', NULL, '1992', NULL),
(3200, 'Secondary', 'Makati High School', NULL, '1996', NULL),
(3200, 'College', 'University of the Philippines', 'BS Accountancy', '2000', 'Cum Laude'),
(3200, 'Graduate Studies', 'De La Salle University', 'Master in Business Accounting', '2006', NULL),
(3201, 'Elementary', 'Quezon City Elementary', NULL, '1995', NULL),
(3201, 'Secondary', 'Quezon City High School', NULL, '1999', NULL),
(3201, 'College', 'University of Santo Tomas', 'BS Accountancy', '2003', NULL),
(3201, 'Graduate Studies', 'Ateneo de Manila University', 'MA Management', '2008', NULL),
-- Business Development
(4300, 'Elementary', 'Makati Central School', NULL, '1993', NULL),
(4300, 'Secondary', 'Makati Science High School', NULL, '1997', NULL),
(4300, 'College', 'University of the Philippines', 'BS Economics', '2001', NULL),
(4300, 'Graduate Studies', 'Asian Institute of Management', 'MBA', '2007', NULL),
(4304, 'Elementary', 'Quezon City Central School', NULL, '2002', NULL),
(4304, 'Secondary', 'Quezon City Science High School', NULL, '2006', NULL),
(4304, 'College', 'Ateneo de Manila University', 'BS Management', '2010', NULL),
(4304, 'Graduate Studies', 'Asian Institute of Management', 'MBA', '2016', NULL),
-- Compliance
(5400, 'Elementary', 'Manila Central School', NULL, '1995', NULL),
(5400, 'Secondary', 'Manila Science High School', NULL, '1999', NULL),
(5400, 'College', 'University of the Philippines', 'BS Legal Management', '2003', NULL),
(5400, 'Graduate Studies', 'Ateneo Law School', 'Juris Doctor', '2008', NULL),
-- Finance
(6500, 'Elementary', 'Makati Elementary', NULL, '1988', NULL),
(6500, 'Secondary', 'Makati High School', NULL, '1992', NULL),
(6500, 'College', 'University of the Philippines', 'BS Accountancy', '1996', 'Magna Cum Laude'),
(6500, 'Graduate Studies', 'Asian Institute of Management', 'MBA - Finance', '2002', NULL),
(6501, 'Elementary', 'Manila Central Elementary', NULL, '1996', NULL),
(6501, 'Secondary', 'Manila High School', NULL, '2000', NULL),
(6501, 'College', 'De La Salle University', 'BS Accountancy', '2004', NULL),
-- HR Department
(8700, 'Elementary', 'Manila Elementary School', NULL, '1991', NULL),
(8700, 'Secondary', 'Manila National High School', NULL, '1995', NULL),
(8700, 'College', 'University of the Philippines', 'BS Psychology', '1999', 'Cum Laude'),
(8700, 'Graduate Studies', 'Ateneo de Manila University', 'MA Industrial Psychology', '2005', NULL),
-- IT Department
(9800, 'Elementary', 'Quezon City Elementary', NULL, '1993', NULL),
(9800, 'Secondary', 'Science High School', NULL, '1997', NULL),
(9800, 'College', 'University of the Philippines', 'BS Computer Science', '2001', NULL),
(9800, 'Graduate Studies', 'Ateneo de Manila University', 'MS Information Technology', '2007', NULL),
-- Marketing
(10900, 'Elementary', 'Makati Elementary', NULL, '1995', NULL),
(10900, 'Secondary', 'Makati Science High', NULL, '1999', NULL),
(10900, 'College', 'De La Salle University', 'BS Marketing Management', '2003', NULL),
(10900, 'Graduate Studies', 'Asian Institute of Management', 'MBA - Marketing', '2009', NULL),
-- Operations
(11000, 'Elementary', 'Lucena Elementary', NULL, '1988', NULL),
(11000, 'Secondary', 'Quezon National High School', NULL, '1992', NULL),
(11000, 'College', 'University of the Philippines', 'BS Industrial Engineering', '1996', NULL),
(11000, 'Graduate Studies', 'De La Salle University', 'MBA - Operations Management', '2003', NULL);

-- ============================================
-- WORK EXPERIENCE
-- ============================================
REPLACE INTO employee_work_experience (employee_id, date_from, date_to, job_title, company_name, monthly_salary) VALUES
-- Office of the President
(1100, '1995-07-01', '2000-12-31', 'Business Analyst', 'PLDT Inc.', 25000.00),
(1100, '2001-01-01', '2005-12-31', 'Business Development Manager', 'Globe Telecom', 45000.00),
(1100, '2006-01-01', '2009-12-31', 'Vice President - Strategy', 'SM Investments Corporation', 85000.00),
(1101, '2005-07-01', '2012-05-31', 'Executive Secretary', 'Ayala Corporation', 28000.00),
(1102, '2007-08-01', '2014-02-28', 'Administrative Assistant', 'Jollibee Foods Corporation', 22000.00),
(1103, '2010-07-01', '2016-08-31', 'Executive Assistant', 'San Miguel Corporation', 30000.00),
-- Acquired Properties
(2100, '1998-08-01', '2005-12-31', 'Property Analyst', 'Ayala Land Inc.', 32000.00),
(2100, '2006-01-01', '2012-12-31', 'Property Manager', 'SM Development Corporation', 55000.00),
(2101, '2002-07-01', '2014-03-31', 'Real Estate Associate', 'Cuervo Real Estate', 28000.00),
(2102, '2004-08-01', '2015-06-30', 'Property Coordinator', 'Vista Land & Lifescapes', 30000.00),
-- Audit
(3200, '2000-07-01', '2008-12-31', 'Staff Auditor', 'SGV and Co.', 28000.00),
(3200, '2009-01-01', '2013-12-31', 'Senior Auditor', 'PwC Philippines', 45000.00),
(3201, '2003-08-01', '2010-12-31', 'Junior Auditor', 'Ernst and Young', 25000.00),
(3201, '2011-01-01', '2015-05-31', 'Audit Supervisor', 'Deloitte Philippines', 42000.00),
-- Business Development
(4300, '2001-07-01', '2008-12-31', 'Business Analyst', 'Metro Pacific Investments', 35000.00),
(4300, '2009-01-01', '2015-03-31', 'Business Development Manager', 'JG Summit Holdings', 55000.00),
(4304, '2010-07-01', '2016-02-28', 'Business Development Associate', 'SM Investments Corporation', 38000.00),
(4304, '2016-03-01', '2021-02-28', 'Business Development Officer', 'Robinsons Land Corporation', 52000.00),
-- Compliance
(5400, '2003-08-01', '2010-12-31', 'Compliance Officer', 'BDO Unibank', 32000.00),
(5400, '2011-01-01', '2016-01-31', 'Compliance Manager', 'Metrobank', 50000.00),
-- Finance
(6500, '1996-07-01', '2003-12-31', 'Accountant', 'Philippine National Bank', 28000.00),
(6500, '2004-01-01', '2008-12-31', 'Finance Manager', 'Land Bank of the Philippines', 50000.00),
(6500, '2009-01-01', '2010-12-31', 'Controller', 'BPI', 75000.00),
(6501, '2004-07-01', '2010-12-31', 'Junior Accountant', 'SGV and Co.', 25000.00),
(6501, '2011-01-01', '2015-05-31', 'Senior Accountant', 'Petron Corporation', 38000.00),
-- HR
(8700, '1999-07-01', '2006-12-31', 'HR Specialist', 'Procter and Gamble Philippines', 30000.00),
(8700, '2007-01-01', '2013-04-30', 'HR Manager', 'Unilever Philippines', 50000.00),
-- IT
(9800, '2001-07-01', '2008-12-31', 'Programmer', 'Accenture Philippines', 28000.00),
(9800, '2009-01-01', '2014-05-31', 'IT Manager', 'IBM Philippines', 55000.00),
-- Marketing
(10900, '2003-07-01', '2010-12-31', 'Marketing Associate', 'Nestle Philippines', 28000.00),
(10900, '2011-01-01', '2015-06-30', 'Marketing Manager', 'Coca-Cola Philippines', 48000.00),
-- Operations
(11000, '1996-08-01', '2005-12-31', 'Operations Supervisor', 'Jollibee Foods Corporation', 35000.00),
(11000, '2006-01-01', '2011-07-31', 'Operations Manager', 'McDonald''s Philippines', 60000.00);

-- ============================================
-- TRAININGS & SEMINARS
-- ============================================
REPLACE INTO employee_trainings (employee_id, training_title, conducted_by, no_of_hours) VALUES
-- Office of the President
(1100, 'Executive Leadership Program', 'Harvard Business School', 120.0),
(1100, 'Strategic Management Workshop', 'Asian Institute of Management', 40.0),
(1100, 'Corporate Governance Seminar', 'Institute of Corporate Directors', 24.0),
(1101, 'Executive Secretarial Excellence', 'Professional Development Center', 32.0),
(1101, 'Time Management & Productivity', 'Dale Carnegie Philippines', 16.0),
(1102, 'Advanced Office Management', 'Administrative Management Society', 24.0),
(1102, 'Communication Skills for Executives', 'Toastmasters International', 20.0),
(1103, 'Digital Office Systems', 'Microsoft Philippines', 16.0),
(1103, 'Professional Executive Assistant Training', 'Philippine Center for Executive Development', 32.0),
-- Acquired Properties
(2100, 'Real Estate Investment Analysis', 'Real Estate Management Association', 40.0),
(2100, 'Property Valuation and Assessment', 'Philippine Institute of Real Estate', 32.0),
(2100, 'Strategic Property Management', 'Asian Property Institute', 24.0),
(2101, 'Property Sales and Marketing', 'Real Estate Brokers Association', 32.0),
(2101, 'Customer Relations Management', 'Sales and Marketing Executives', 20.0),
(2102, 'Property Management Best Practices', 'Property Management Association', 28.0),
(2102, 'Legal Aspects of Real Estate', 'Integrated Bar of the Philippines', 24.0),
-- Audit
(3200, 'Advanced Auditing Standards', 'Philippine Institute of CPAs', 40.0),
(3200, 'Risk-Based Audit Approach', 'Institute of Internal Auditors', 32.0),
(3200, 'Fraud Detection and Prevention', 'Association of Certified Fraud Examiners', 24.0),
(3201, 'Internal Audit Fundamentals', 'Philippine Institute of CPAs', 32.0),
(3201, 'IT Audit and Controls', 'Information Systems Audit and Control Association', 28.0),
-- Business Development
(4300, 'Strategic Business Development', 'Asian Institute of Management', 40.0),
(4300, 'Market Analysis and Feasibility Studies', 'Philippine Chamber of Commerce', 32.0),
(4300, 'Negotiation Skills for Business Leaders', 'Harvard Business Publishing', 24.0),
(4304, 'Business Development Strategies', 'Asian Institute of Management', 36.0),
(4304, 'Strategic Partnership Management', 'Philippine Chamber of Commerce', 24.0),
(4304, 'Project Feasibility and Investment Analysis', 'Management Association of the Philippines', 20.0),
-- Compliance
(5400, 'Regulatory Compliance Management', 'Bangko Sentral ng Pilipinas', 32.0),
(5400, 'Anti-Money Laundering Act Implementation', 'AMLC Philippines', 24.0),
(5400, 'Corporate Governance and Compliance', 'Institute of Corporate Directors', 28.0),
-- Finance
(6500, 'Advanced Financial Management', 'Financial Executives Institute', 40.0),
(6500, 'Treasury Operations and Cash Management', 'Asian Development Bank', 32.0),
(6500, 'Philippine Financial Reporting Standards', 'Philippine Institute of CPAs', 24.0),
(6501, 'Accounting for Non-Accountants', 'Management Association of the Philippines', 24.0),
(6501, 'Tax Planning and Compliance', 'Tax Management Association', 28.0),
-- HR
(8700, 'Strategic Human Resource Management', 'People Management Association', 40.0),
(8700, 'Labor Relations and Employee Engagement', 'Philippine HR Association', 32.0),
(8700, 'Talent Development and Succession Planning', 'SHRM Philippines', 28.0),
-- IT
(9800, 'IT Service Management (ITIL)', 'ITIL Foundation', 40.0),
(9800, 'Cybersecurity and Data Protection', 'Cisco Networking Academy', 32.0),
(9800, 'Cloud Computing and Infrastructure', 'Microsoft Azure Fundamentals', 28.0),
-- Marketing
(10900, 'Digital Marketing Strategy', 'Google Digital Garage', 32.0),
(10900, 'Brand Management Excellence', 'Philippine Marketing Association', 28.0),
(10900, 'Social Media Marketing Mastery', 'Facebook Blueprint', 24.0),
-- Operations
(11000, 'Operations Management Excellence', 'Asian Institute of Management', 40.0),
(11000, 'Supply Chain and Logistics Management', 'APICS Philippines', 32.0),
(11000, 'Lean Six Sigma Green Belt', 'Philippine Quality Award Foundation', 40.0);

-- ============================================
-- VOLUNTARY WORK
-- ============================================
REPLACE INTO employee_voluntary_work (employee_id, date_from, date_to, organization_name, organization_address, no_of_hours, position_nature) VALUES
(1100, '2015-01-01', '2018-12-31', 'Philippine Red Cross', 'Lucena City Chapter', 200.0, 'Board Member'),
(1100, '2019-01-01', '2021-12-31', 'Rotary Club of Lucena', 'Lucena City', 150.0, 'Community Service Committee'),
(1101, '2018-06-01', '2020-12-31', 'Habitat for Humanity Philippines', 'Quezon City', 80.0, 'Volunteer Builder'),
(1102, '2016-01-01', '2019-12-31', 'Gawad Kalinga', 'Makati City', 120.0, 'Community Development Volunteer'),
(2100, '2014-01-01', '2020-12-31', 'Lions Club International', 'Lucena District', 180.0, 'Vice President'),
(3200, '2015-01-01', '2021-12-31', 'Junior Chamber International', 'Manila Chapter', 100.0, 'Financial Literacy Advocate'),
(6500, '2012-01-01', '2021-12-31', 'Financial Executives Institute', 'Metro Manila', 200.0, 'Pro Bono Financial Advisor'),
(8700, '2014-01-01', '2020-12-31', 'Philippine Business for Social Progress', 'Makati', 150.0, 'HR Volunteer Consultant'),
(9800, '2016-01-01', '2021-12-31', 'Code.org Philippines', 'Quezon City', 120.0, 'IT Literacy Instructor'),
(11000, '2013-01-01', '2021-12-31', 'Philippine Disaster Recovery Foundation', 'Quezon Province', 250.0, 'Operations Coordinator');

-- ============================================
-- ELIGIBILITY & LICENSES
-- ============================================
REPLACE INTO employee_eligibility (employee_id, license_title, license_number, date_of_exam, place_of_exam) VALUES
(1100, 'Certified Public Accountant', '0045678', '1996-10-15', 'Manila'),
(3200, 'Certified Public Accountant', '0089234', '2001-10-15', 'Manila'),
(3200, 'Certified Internal Auditor', 'CIA-45678', '2008-05-20', 'Makati City'),
(3201, 'Certified Public Accountant', '0095432', '2004-10-15', 'Manila'),
(5400, 'Licensed Attorney', 'Roll No. 67890', '2009-05-10', 'Manila'),
(6500, 'Certified Public Accountant', '0012345', '1997-10-15', 'Manila'),
(6500, 'Certified Financial Planner', 'CFP-12345', '2003-08-20', 'Makati City'),
(6501, 'Certified Public Accountant', '0098765', '2005-10-15', 'Manila'),
(8700, 'Licensed Psychologist', 'LP-34567', '2000-06-15', 'Quezon City'),
(8700, 'Certified HR Professional', 'CHP-98765', '2006-11-20', 'Manila'),
(9800, 'Certified Information Systems Auditor', 'CISA-56789', '2009-12-05', 'Manila'),
(10900, 'Certified Marketing Professional', 'CMP-45678', '2010-09-15', 'Makati City'),
(11000, 'Project Management Professional', 'PMP-78901', '2010-07-20', 'Manila');

-- ============================================
-- SKILLS & HOBBIES
-- ============================================
REPLACE INTO employee_skills (employee_id, skill_name) VALUES
(1100, 'Strategic Planning'),
(1100, 'Business Development'),
(1100, 'Financial Analysis'),
(1100, 'Leadership and Management'),
(1101, 'Office Administration'),
(1101, 'Calendar Management'),
(1101, 'Travel Coordination'),
(1102, 'Document Management'),
(1102, 'Event Planning'),
(1103, 'Communication'),
(1103, 'Scheduling'),
(2100, 'Property Management'),
(2100, 'Real Estate Valuation'),
(2100, 'Negotiation'),
(2101, 'Sales and Marketing'),
(2101, 'Customer Relations'),
(3200, 'Risk Assessment'),
(3200, 'Internal Controls'),
(3200, 'Compliance Auditing'),
(3201, 'Financial Auditing'),
(3201, 'Data Analysis'),
(4300, 'Market Research'),
(4300, 'Business Analysis'),
(4300, 'Partnership Development'),
(4304, 'Business Development'),
(4304, 'Strategic Planning'),
(4304, 'Client Relations'),
(5400, 'Regulatory Compliance'),
(5400, 'Policy Development'),
(5400, 'Legal Research'),
(6500, 'Financial Management'),
(6500, 'Treasury Operations'),
(6500, 'Budgeting & Forecasting'),
(6501, 'General Accounting'),
(6501, 'Financial Reporting'),
(6501, 'Tax Compliance'),
(8700, 'Recruitment & Selection'),
(8700, 'Employee Relations'),
(8700, 'Organizational Development'),
(8700, 'Performance Management'),
(9800, 'Software Development'),
(9800, 'System Administration'),
(9800, 'IT Infrastructure'),
(9800, 'Network Security'),
(10900, 'Digital Marketing'),
(10900, 'Brand Management'),
(10900, 'Market Research'),
(11000, 'Operations Management'),
(11000, 'Process Improvement'),
(11000, 'Team Leadership');

-- ============================================
-- RECOGNITIONS & AWARDS
-- ============================================
REPLACE INTO employee_recognitions (employee_id, recognition_title) VALUES
(1100, 'CEO of the Year 2020 - Philippine Business Excellence Awards'),
(1100, 'Outstanding Business Leader 2019 - Quezon Chamber of Commerce'),
(1100, 'Corporate Social Responsibility Award 2018'),
(1101, 'Employee of the Year 2019'),
(1102, 'Excellence in Service Award 2020'),
(2100, 'Property Manager of the Year 2019'),
(2100, 'Best Real Estate Development Project 2018'),
(3200, 'Internal Auditor of the Year 2020'),
(3200, 'Excellence in Audit Practice Award 2018'),
(6500, 'CFO Excellence Award 2019'),
(6500, 'Financial Leadership Award 2017'),
(8700, 'HR Professional of the Year 2020'),
(8700, 'Best HR Practices Award 2018'),
(9800, 'IT Manager of the Year 2019'),
(9800, 'Innovation in Technology Award 2017'),
(10900, 'Marketing Excellence Award 2020'),
(11000, 'Operations Excellence Award 2019'),
(11000, 'Process Innovation Award 2017');

-- ============================================
-- MEMBERSHIPS IN ASSOCIATIONS
-- ============================================
REPLACE INTO employee_memberships (employee_id, organization_name) VALUES
(1100, 'Philippine Institute of Certified Public Accountants'),
(1100, 'Financial Executives Institute of the Philippines'),
(1100, 'Management Association of the Philippines'),
(1101, 'Professional Secretaries International'),
(2100, 'Philippine Institute of Real Estate Management'),
(2100, 'Association of Real Estate Practitioners'),
(3200, 'Philippine Institute of Certified Public Accountants'),
(3200, 'Institute of Internal Auditors - Philippines'),
(3201, 'Philippine Institute of Certified Public Accountants'),
(4300, 'Philippine Chamber of Commerce and Industry'),
(4304, 'Philippine Chamber of Commerce and Industry'),
(4304, 'Management Association of the Philippines'),
(5400, 'Integrated Bar of the Philippines'),
(5400, 'Compliance Officers Association of the Philippines'),
(6500, 'Philippine Institute of Certified Public Accountants'),
(6500, 'Financial Executives Institute of the Philippines'),
(6501, 'Philippine Institute of Certified Public Accountants'),
(8700, 'People Management Association of the Philippines'),
(8700, 'Society for Human Resource Management Philippines'),
(9800, 'Information Technology Association of the Philippines'),
(9800, 'ISACA Philippines Chapter'),
(10900, 'Philippine Marketing Association'),
(10900, 'Advertising & Marketing Communications Managers Association'),
(11000, 'Philippine Association of Operations Management'),
(11000, 'Association of Process Excellence Professionals');

-- ============================================
-- PERSONAL DISCLOSURES
-- ============================================
REPLACE INTO employee_disclosures (employee_id, is_related_to_company, has_admin_offense, has_criminal_charge, has_criminal_conviction, has_been_separated, is_pwd, is_solo_parent, has_recent_hospital, has_current_treatment) VALUES
(1100, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(1101, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(1102, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(1103, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(2100, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(2101, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(2102, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(2103, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(2104, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(2105, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(2106, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(2107, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(2108, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(2109, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(2110, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(2111, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(2112, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(2113, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(2114, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(2115, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(2116, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(3200, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(3201, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(3202, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(3203, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(3204, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(3205, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(3206, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(3207, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(3208, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(3209, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(3210, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(3211, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(4300, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(4301, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(4302, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(4303, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(4304, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(5400, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(5401, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(5402, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(5403, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(5404, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(5405, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(6500, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(6501, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(6502, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(6503, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(6504, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(6505, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(6506, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(6507, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(6508, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(6509, 0, 0, 0, 0, 0, 0, 0, 0, 0);

-- Note: Continue pattern for all remaining employees

-- ============================================
-- GOVERNMENT IDs
-- ============================================
REPLACE INTO employee_government_ids (employee_id, sss_number, philhealth_number, pagibig_number, tin_number) VALUES
(1100, '33-1234567-8', '12-123456789-0', '1234-5678-9012', '123-456-789-000'),
(1101, '33-2234567-9', '12-223456789-1', '2234-5678-9012', '223-456-789-001'),
(1102, '33-3234567-0', '12-323456789-2', '3234-5678-9012', '323-456-789-002'),
(1103, '33-4234567-1', '12-423456789-3', '4234-5678-9012', '423-456-789-003'),
(2100, '33-5234567-2', '12-523456789-4', '5234-5678-9012', '523-456-789-004'),
(2101, '33-6234567-3', '12-623456789-5', '6234-5678-9012', '623-456-789-005'),
(2102, '33-7234567-4', '12-723456789-6', '7234-5678-9012', '723-456-789-006'),
(2103, '33-8234567-5', '12-823456789-7', '8234-5678-9012', '823-456-789-007'),
(2104, '33-9234567-6', '12-923456789-8', '9234-5678-9012', '923-456-789-008'),
(2105, '34-0234567-7', '12-023456780-9', '0234-5678-9013', '023-456-780-009'),
(2106, '34-1234567-8', '12-123456781-0', '1234-5679-9012', '123-456-781-000'),
(2107, '34-2234567-9', '12-223456782-1', '2234-5679-9012', '223-456-782-001'),
(2108, '34-3234567-0', '12-323456783-2', '3234-5679-9012', '323-456-783-002'),
(2109, '34-4234567-1', '12-423456784-3', '4234-5679-9012', '423-456-784-003'),
(2110, '34-5234567-2', '12-523456785-4', '5234-5679-9012', '523-456-785-004'),
(2111, '34-6234567-3', '12-623456786-5', '6234-5679-9012', '623-456-786-005'),
(2112, '34-7234567-4', '12-723456787-6', '7234-5679-9012', '723-456-787-006'),
(2113, '34-8234567-5', '12-823456788-7', '8234-5679-9012', '823-456-788-007'),
(2114, '34-9234567-6', '12-923456789-8', '9234-5679-9012', '923-456-789-008'),
(2115, '35-0234567-7', '12-023456790-9', '0234-5679-9013', '023-456-790-009'),
(2116, '35-1234567-8', '12-123456791-0', '1234-5680-9012', '123-456-791-000'),
(3200, '35-2234567-9', '12-223456792-1', '2234-5680-9012', '223-456-792-001'),
(3201, '35-3234567-0', '12-323456793-2', '3234-5680-9012', '323-456-793-002'),
(3202, '35-4234567-1', '12-423456794-3', '4234-5680-9012', '423-456-794-003'),
(3203, '35-5234567-2', '12-523456795-4', '5234-5680-9012', '523-456-795-004'),
(3204, '35-6234567-3', '12-623456796-5', '6234-5680-9012', '623-456-796-005'),
(3205, '35-7234567-4', '12-723456797-6', '7234-5680-9012', '723-456-797-006'),
(3206, '35-8234567-5', '12-823456798-7', '8234-5680-9012', '823-456-798-007'),
(3207, '35-9234567-6', '12-923456799-8', '9234-5680-9012', '923-456-799-008'),
(3208, '36-0234567-7', '12-023456800-9', '0234-5680-9013', '023-456-800-009'),
(3209, '36-1234567-8', '12-123456801-0', '1234-5681-9012', '123-456-801-000'),
(3210, '36-2234567-9', '12-223456802-1', '2234-5681-9012', '223-456-802-001'),
(3211, '36-3234567-0', '12-323456803-2', '3234-5681-9012', '323-456-803-002'),
(4300, '36-4234567-1', '12-423456804-3', '4234-5681-9012', '423-456-804-003'),
(4301, '36-5234567-2', '12-523456805-4', '5234-5681-9012', '523-456-805-004'),
(4302, '36-6234567-3', '12-623456806-5', '6234-5681-9012', '623-456-806-005'),
(4303, '36-7234567-4', '12-723456807-6', '7234-5681-9012', '723-456-807-006'),
(4304, '36-8234567-5', '12-823456808-6', '8234-5682-9012', '823-456-808-007'),
(5400, '36-8234567-5', '12-823456808-7', '8234-5681-9012', '823-456-808-007'),
(5401, '36-9234567-6', '12-923456809-8', '9234-5681-9012', '923-456-809-008'),
(5402, '37-0234567-7', '12-023456810-9', '0234-5681-9013', '023-456-810-009'),
(5403, '37-1234567-8', '12-123456811-0', '1234-5682-9012', '123-456-811-000'),
(5404, '37-2234567-9', '12-223456812-1', '2234-5682-9012', '223-456-812-001'),
(5405, '37-3234567-0', '12-323456813-2', '3234-5682-9012', '323-456-813-002'),
(6500, '37-4234567-1', '12-423456814-3', '4234-5682-9012', '423-456-814-003'),
(6501, '37-5234567-2', '12-523456815-4', '5234-5682-9012', '523-456-815-004'),
(6502, '37-6234567-3', '12-623456816-5', '6234-5682-9012', '623-456-816-005'),
(6503, '37-7234567-4', '12-723456817-6', '7234-5682-9012', '723-456-817-006'),
(6504, '37-8234567-5', '12-823456818-7', '8234-5682-9012', '823-456-818-007'),
(6505, '37-9234567-6', '12-923456819-8', '9234-5682-9012', '923-456-819-008'),
(6506, '38-0234567-7', '12-023456820-9', '0234-5682-9013', '023-456-820-009'),
(6507, '38-1234567-8', '12-123456821-0', '1234-5683-9012', '123-456-821-000'),
(6508, '38-2234567-9', '12-223456822-1', '2234-5683-9012', '223-456-822-001'),
(6509, '38-3234567-0', '12-323456823-2', '3234-5683-9012', '323-456-823-002');

-- Note: Continue pattern for remaining employees

-- ============================================
-- EMERGENCY CONTACTS
-- ============================================
REPLACE INTO employee_emergency_contacts (employee_id, contact_name, relationship, contact_number) VALUES
(1100, 'Maria Santos Chen', 'Spouse', '09171111111'),
(1101, 'John David Williams', 'Father', '09181111112'),
(1102, 'Antonio Valdez Reyes', 'Spouse', '09191111113'),
(1103, 'Patricia Gomez Torres', 'Mother', '09201111114'),
(2100, 'Ana Garcia Rodriguez', 'Spouse', '09211111115'),
(2101, 'Jennifer Cruz Santos', 'Spouse', '09221111116'),
(2102, 'Lisa Santos Garcia', 'Spouse', '09231111117'),
(2103, 'Marilyn Torres Ramos', 'Mother', '09241111118'),
(2104, 'Sandra Cruz Bautista', 'Spouse', '09251111119'),
(2105, 'Rosa Santos Cruz', 'Mother', '09261111120'),
(2106, 'Carlos Morales Gonzales', 'Spouse', '09271111121'),
(2107, 'Fernando Santiago Fernandez', 'Father', '09281111122'),
(2108, 'Maria Navarro Perez', 'Mother', '09291111123'),
(2109, 'Carmen Castro Lopez', 'Mother', '09301111124'),
(2110, 'Eduardo Aquino Martinez', 'Spouse', '09311111125'),
(2111, 'Jose Gutierrez Hernandez', 'Father', '09321111126'),
(2112, 'Ana Pascual Diaz', 'Spouse', '09331111127'),
(2113, 'Teresa Torres Jimenez', 'Spouse', '09341111128'),
(2114, 'Roberto Flores Ruiz', 'Father', '09351111129'),
(2115, 'Gloria Ramos Alvarez', 'Mother', '09361111130'),
(2116, 'Fernando Mendez Castillo', 'Spouse', '09371111131'),
(3200, 'Susan Garcia Thompson', 'Spouse', '09381111132'),
(3201, 'Rachel Lopez Anderson', 'Spouse', '09391111133'),
(3202, 'Linda Santos Taylor', 'Mother', '09401111134'),
(3203, 'Robert Garcia White', 'Spouse', '09411111135'),
(3204, 'William Lopez Martin', 'Father', '09421111136'),
(3205, 'James Jackson Reyes', 'Spouse', '09431111137'),
(3206, 'Mary Santos Harris', 'Mother', '09441111138'),
(3207, 'Patricia Perez Clark', 'Mother', '09451111139'),
(3208, 'David Martinez Lewis', 'Spouse', '09461111140'),
(3209, 'John Hernandez Walker', 'Father', '09471111141'),
(3210, 'Jennifer Diaz Robinson', 'Spouse', '09481111142'),
(3211, 'Elizabeth Jimenez Young', 'Spouse', '09491111143'),
(4300, 'Sarah Alvarez King', 'Spouse', '09501111144'),
(4301, 'Robert Castillo Wright', 'Father', '09511111145'),
(4302, 'Thomas Ruiz Scott', 'Father', '09521111146'),
(4303, 'Linda Morales Green', 'Spouse', '09531111147'),
(4304, 'Jose Antonio Navarro', 'Father', '09541111248'),
(5400, 'Barbara Fernandez Adams', 'Spouse', '09541111148'),
(5401, 'James Navarro Baker', 'Spouse', '09551111149'),
(5402, 'Richard Castro Nelson', 'Father', '09561111150'),
(5403, 'Michael Aquino Carter', 'Father', '09571111151'),
(5404, 'David Gutierrez Mitchell', 'Spouse', '09581111152'),
(5405, 'Robert Pascual Perez', 'Father', '09591111153'),
(6500, 'Catherine Valdez Roberts', 'Spouse', '09601111154'),
(6501, 'William Rivera Turner', 'Father', '09611111155'),
(6502, 'James Dela Cruz Phillips', 'Spouse', '09621111156'),
(6503, 'Robert Villanueva Campbell', 'Father', '09631111157'),
(6504, 'Charles Gonzales Parker', 'Spouse', '09641111158'),
(6505, 'Helen Morales Evans', 'Spouse', '09651111159'),
(6506, 'Nancy Santiago Edwards', 'Spouse', '09661111160'),
(6507, 'Karen Fernandez Collins', 'Mother', '09671111161'),
(6508, 'Betty Navarro Stewart', 'Mother', '09681111162'),
(6509, 'Linda Castro Sanchez', 'Spouse', '09691111163');

-- ============================================
-- SALN: REAL PROPERTIES
-- ============================================
REPLACE INTO employee_real_properties (employee_id, description, kind, acquisition_cost) VALUES
(1100, 'Residential House and Lot', 'Building', 8500000.00),
(1100, 'Vacation House - Tagaytay', 'Building', 4200000.00),
(1102, 'Condominium Unit - BGC', 'Building', 6800000.00),
(2100, 'Residential House and Lot - Lucena', 'Building', 3500000.00),
(2101, 'Residential Lot - Tayabas', 'Land', 1200000.00),
(2102, 'Residential House - Sariaya', 'Building', 2800000.00),
(2104, 'Commercial Lot - Tiaong', 'Land', 2500000.00),
(3200, 'Condominium Unit - Makati', 'Building', 5600000.00),
(3201, 'Townhouse - Quezon City', 'Building', 4200000.00),
(6500, 'Residential House and Lot - Ayala Alabang', 'Building', 12000000.00),
(6500, 'Beach House - Batangas', 'Building', 5500000.00),
(8700, 'Residential House - QC', 'Building', 6200000.00),
(9800, 'Condominium - Pasig', 'Building', 4800000.00),
(11000, 'Residential House - Lucena', 'Building', 4500000.00);

-- ============================================
-- SALN: PERSONAL PROPERTIES
-- ============================================
REPLACE INTO employee_personal_properties (employee_id, description, year_acquired, acquisition_cost) VALUES
(1100, 'Toyota Land Cruiser 2020', '2020', 4500000.00),
(1100, 'Honda Accord 2018', '2018', 1800000.00),
(1101, 'Toyota Vios 2019', '2019', 850000.00),
(1102, 'Mazda CX-5 2020', '2020', 1650000.00),
(1103, 'Honda City 2021', '2021', 950000.00),
(2100, 'Toyota Fortuner 2019', '2019', 1950000.00),
(2101, 'Mitsubishi Montero 2018', '2018', 1450000.00),
(2102, 'Honda CR-V 2020', '2020', 1750000.00),
(2104, 'Ford Ranger 2019', '2019', 1350000.00),
(3200, 'BMW 5 Series 2020', '2020', 3800000.00),
(3201, 'Mercedes-Benz C-Class 2019', '2019', 3200000.00),
(6500, 'Lexus RX 2021', '2021', 5200000.00),
(6500, 'Toyota Camry 2019', '2019', 1850000.00),
(8700, 'Mazda 6 2020', '2020', 1650000.00),
(9800, 'Honda Civic 2021', '2021', 1450000.00),
(11000, 'Toyota Hilux 2020', '2020', 1550000.00);

-- ============================================
-- SALN: LIABILITIES
-- ============================================
REPLACE INTO employee_liabilities (employee_id, nature_of_liability, creditor_name, outstanding_balance) VALUES
(1100, 'Housing Loan', 'BDO Unibank', 4500000.00),
(1100, 'Car Loan', 'Toyota Financial Services', 1200000.00),
(1102, 'Condo Mortgage', 'RCBC', 4200000.00),
(2100, 'Home Loan', 'Landbank of the Philippines', 2100000.00),
(2101, 'Personal Loan', 'BPI', 350000.00),
(2102, 'Housing Loan', 'Security Bank', 1800000.00),
(2104, 'Car Loan', 'BDO Unibank', 580000.00),
(3200, 'Condo Loan', 'Metrobank', 3200000.00),
(3201, 'Housing Loan', 'BPI', 2800000.00),
(6500, 'Home Mortgage', 'BDO Private Bank', 6500000.00),
(6500, 'Car Loan', 'Toyota Financial Services', 850000.00),
(8700, 'Housing Loan', 'RCBC', 3800000.00),
(9800, 'Condo Mortgage', 'Metrobank', 3100000.00),
(11000, 'Home Loan', 'Landbank', 2700000.00);

-- ============================================
-- CHARACTER REFERENCES
-- ============================================
REPLACE INTO employee_references (employee_id, reference_name, reference_address, reference_telephone) VALUES
(1100, 'Atty. Roberto M. Santos', 'Makati City, Metro Manila', '02-8123-4567'),
(1100, 'Dr. Maria C. Gonzales', 'Quezon City, Metro Manila', '02-7654-3210'),
(1100, 'Engr. Fernando R. Cruz', 'Pasig City, Metro Manila', '02-5432-1098'),
(1101, 'Prof. Elizabeth D. Reyes', 'Quezon City', '02-9876-5432'),
(1101, 'Dr. Antonio V. Garcia', 'Manila', '02-8765-4321'),
(1102, 'Atty. Carmen S. Lopez', 'Makati City', '02-7654-8901'),
(1102, 'CPA Jose M. Perez', 'Pasig City', '02-6543-7890'),
(2100, 'Engr. Vicente C. Rodriguez', 'Lucena City', '042-373-1234'),
(2100, 'Prof. Ana G. Santos', 'Tayabas City', '042-373-5678'),
(2101, 'Dr. Carlos M. Mendoza', 'Lucena City', '042-373-9012'),
(2102, 'Atty. Teresa L. Garcia', 'Sariaya, Quezon', '042-371-2345'),
(3200, 'CPA William V. Thompson', 'Makati City', '02-8234-5678'),
(3200, 'Dr. Margaret C. Santos', 'Manila', '02-7345-6789'),
(3201, 'Engr. Robert C. Anderson', 'Quezon City', '02-6456-7890'),
(6500, 'CPA George M. Roberts', 'Makati City', '02-8567-8901'),
(6500, 'Atty. Elizabeth V. Cruz', 'Manila', '02-7678-9012'),
(8700, 'Dr. James V. Powell', 'Quezon City', '02-6789-0123'),
(8700, 'Prof. Mary S. Santos', 'Manila', '02-5890-1234'),
(9800, 'Engr. Richard N. Myers', 'Quezon City', '02-4901-2345'),
(9800, 'CPA Nancy C. Cruz', 'Makati City', '02-3012-3456'),
(11000, 'Engr. Thomas C. Foster', 'Lucena City', '042-373-4567'),
(11000, 'Dr. Barbara S. Santos', 'Tayabas City', '042-373-8901');

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- END OF SEED DATA
-- ============================================
