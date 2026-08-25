-- ============================================================================
-- EVALUATION-FLOW TEST EMPLOYEES (lean roster)
-- Import AFTER 3rd_seed_HR_accounts_.sql and BEFORE xPortal_accounts.sql
-- so portal usernames are created from employee_code automatically.
--
-- Do NOT import AP_seed.sql / HR_seed.sql / other full department seeds.
-- Password for every Employee portal account created by xPortal: password
-- ============================================================================
USE raquel_hris;
SET FOREIGN_KEY_CHECKS = 0;

-- Wire the 3 existing HRD HRIS people into one reporting chain:
-- Miguel (staff) -> Patricia (supervisor / consolidator) -> Elena (manager)
UPDATE employees SET reports_to = 301 WHERE employee_id = 302;
UPDATE employees SET reports_to = 101 WHERE employee_id = 301;
UPDATE employees SET reports_to = NULL WHERE employee_id = 101;

-- ============================================================================
-- Mini teams: Staff -> Supervisor -> Manager (and AP also has a VP).
-- Board / Audit sit outside every department so they are not package members
-- and are not in any reports_to chain (required for governance assignment).
-- ============================================================================
REPLACE INTO employees (
    employee_id, employee_code, first_name, last_name, middle_name,
    hire_date, date_of_birth, place_of_birth, gender, civil_status,
    job_title_id, job_title, department_id, rank_category_id, branch_id,
    employment_status, employment_type, reports_to, is_active
) VALUES
-- Acquired Properties (dept 1) — primary pilot
(90101, 'AP-T01', 'Leonora', 'Gomez', 'Cruz', '2023-02-01', '1998-11-04', 'Lucena City', 'Female', 'Single', 109, 'AP Staff I', 1, 5, 102, 'Regular', 'Full-time', 90102, 1),
(90102, 'AP-T02', 'Ronald', 'Lopez', 'Del Rosario', '2021-07-14', '1994-07-12', 'Lucena City', 'Male', 'Married', 105, 'AP Supervisor I', 1, 4, 102, 'Regular', 'Full-time', 90103, 1),
(90103, 'AP-T03', 'Christopher', 'Tolentino', 'Gomez', '2018-01-22', '1985-12-03', 'Lucena City', 'Male', 'Married', 101, 'AP Manager I', 1, 3, 102, 'Regular', 'Full-time', 90104, 1),
(90104, 'AP-T04', 'Eduardo', 'Aquino', 'Villanueva', '2014-09-03', '1979-11-24', 'Lucena City', 'Male', 'Married', 100, 'VP for Acquired Properties', 1, 1, 102, 'Regular', 'Full-time', NULL, 1),

-- Audit (dept 2)
(90201, 'AUD-T01', 'Ramon', 'Villanueva', 'Cruz', '2023-03-01', '1996-04-12', 'Lucena City', 'Male', 'Single', 207, 'Auditor I', 2, 5, 102, 'Regular', 'Full-time', 90202, 1),
(90202, 'AUD-T02', 'Isabel', 'Mendoza', 'Reyes', '2020-06-15', '1992-08-20', 'Lucena City', 'Female', 'Married', 203, 'Audit Supervisor I', 2, 4, 102, 'Regular', 'Full-time', 90203, 1),
(90203, 'AUD-T03', 'Hector', 'Santos', 'Diaz', '2016-02-10', '1984-01-15', 'Lucena City', 'Male', 'Married', 200, 'Audit Manager I', 2, 3, 102, 'Regular', 'Full-time', NULL, 1),

-- Business Development (dept 3) — no supervisor rank; officer consolidates
(90301, 'BD-T01', 'Paolo', 'Garcia', 'Lim', '2024-01-08', '1999-05-02', 'Lucena City', 'Male', 'Single', 302, 'Business Development Staff I', 3, 5, 102, 'Regular', 'Full-time', 90303, 1),
(90302, 'BD-T02', 'Nina', 'Ramos', 'Tan', '2024-04-11', '2000-09-18', 'Lucena City', 'Female', 'Single', 303, 'Business Development Staff II', 3, 5, 102, 'Regular', 'Full-time', 90303, 1),
(90303, 'BD-T03', 'Andrea', 'Villar', 'Cruz', '2019-08-01', '1988-03-22', 'Lucena City', 'Female', 'Married', 300, 'Business Development Officer I', 3, 3, 102, 'Regular', 'Full-time', NULL, 1),

-- Compliance (dept 4)
(90401, 'COM-T01', 'Carlo', 'Bautista', 'Ong', '2023-05-20', '1997-12-01', 'Lucena City', 'Male', 'Single', 403, 'Compliance Staff I', 4, 5, 102, 'Regular', 'Full-time', 90402, 1),
(90402, 'COM-T02', 'Mara', 'Evangelista', 'Go', '2021-09-12', '1993-07-09', 'Lucena City', 'Female', 'Single', 401, 'Compliance Supervisor II', 4, 4, 102, 'Regular', 'Full-time', 90403, 1),
(90403, 'COM-T03', 'Felix', 'Navarro', 'Uy', '2017-11-03', '1986-02-28', 'Lucena City', 'Male', 'Married', 400, 'Compliance Supervisor I', 4, 4, 102, 'Regular', 'Full-time', NULL, 1),

-- Finance (dept 5)
(90501, 'FIN-T01', 'Julia', 'Castillo', 'Perez', '2023-08-14', '1998-06-16', 'Lucena City', 'Female', 'Single', 512, 'Accounting Staff I', 5, 5, 102, 'Regular', 'Full-time', 90502, 1),
(90502, 'FIN-T02', 'Oscar', 'Dela Cruz', 'Reyes', '2020-03-09', '1991-10-05', 'Lucena City', 'Male', 'Married', 502, 'Accounting Supervisor I', 5, 4, 102, 'Regular', 'Full-time', 90503, 1),
(90503, 'FIN-T03', 'Bea', 'Soriano', 'Lim', '2015-05-18', '1983-04-11', 'Lucena City', 'Female', 'Married', 501, 'Accounting Manager I', 5, 3, 102, 'Regular', 'Full-time', NULL, 1),

-- General Services (dept 6)
(90601, 'GS-T01', 'Noel', 'Hernandez', 'Cruz', '2022-12-01', '1995-01-30', 'Lucena City', 'Male', 'Single', 609, 'Driver I', 6, 5, 102, 'Regular', 'Full-time', 90602, 1),
(90602, 'GS-T02', 'Liza', 'Morales', 'Santos', '2019-04-22', '1990-08-14', 'Lucena City', 'Female', 'Married', 605, 'GS Supervisor I', 6, 4, 102, 'Regular', 'Full-time', 90603, 1),
(90603, 'GS-T03', 'Victor', 'Pascual', 'Gomez', '2014-07-07', '1981-12-19', 'Lucena City', 'Male', 'Married', 601, 'GS Manager I', 6, 3, 102, 'Regular', 'Full-time', NULL, 1),

-- Information Technology (dept 8)
(90801, 'IT-T01', 'Ken', 'Alonzo', 'Rivera', '2024-02-15', '2000-03-08', 'Lucena City', 'Male', 'Single', 811, 'Technical Support Staff I', 8, 5, 102, 'Regular', 'Full-time', 90802, 1),
(90802, 'IT-T02', 'Dana', 'Flores', 'Aquino', '2021-01-19', '1994-11-21', 'Lucena City', 'Female', 'Single', 804, 'IT Supervisor I', 8, 4, 102, 'Regular', 'Full-time', 90803, 1),
(90803, 'IT-T03', 'Marco', 'Santiago', 'Tan', '2016-09-05', '1987-06-02', 'Lucena City', 'Male', 'Married', 800, 'IT Manager I', 8, 3, 102, 'Regular', 'Full-time', NULL, 1),

-- Marketing (dept 9)
(90901, 'MKT-T01', 'Ella', 'Domingo', 'Cruz', '2023-10-02', '1999-02-14', 'Lucena City', 'Female', 'Single', 905, 'Marketing Staff I', 9, 5, 102, 'Regular', 'Full-time', 90902, 1),
(90902, 'MKT-T02', 'Ryan', 'Chua', 'Gomez', '2020-08-17', '1992-05-27', 'Lucena City', 'Male', 'Single', 902, 'Marketing Supervisor I', 9, 4, 102, 'Regular', 'Full-time', 90903, 1),
(90903, 'MKT-T03', 'Patricia', 'Lim', 'Santos', '2017-03-13', '1986-09-09', 'Lucena City', 'Female', 'Married', 900, 'Marketing Manager I', 9, 3, 102, 'Regular', 'Full-time', NULL, 1),

-- Office of the President (dept 10)
(91001, 'OP-T01', 'Sofia', 'Reyes', 'Villanueva', '2022-06-01', '1996-07-23', 'Lucena City', 'Female', 'Single', 1101, 'Executive Assistant I', 10, 5, 102, 'Regular', 'Full-time', 91002, 1),
(91002, 'OP-T02', 'Gabriel', 'Mendoza', 'Santos', '2010-01-04', '1975-03-12', 'Lucena City', 'Male', 'Married', 1100, 'President and CEO', 10, 1, 102, 'Regular', 'Full-time', NULL, 1),

-- Operations (dept 11)
(91101, 'OPS-T01', 'Jon', 'Mercado', 'Perez', '2023-11-20', '1998-08-08', 'Lucena City', 'Male', 'Single', 1013, 'Branch Staff I', 11, 5, 102, 'Regular', 'Full-time', 91102, 1),
(91102, 'OPS-T02', 'Katrina', 'Lopez', 'Diaz', '2019-02-25', '1991-01-19', 'Lucena City', 'Female', 'Married', 1004, 'Area Coordinator I', 11, 4, 102, 'Regular', 'Full-time', 91103, 1),
(91103, 'OPS-T03', 'Alvin', 'Torres', 'Cruz', '2015-08-30', '1984-10-03', 'Lucena City', 'Male', 'Married', 1001, 'Regional Manager I', 11, 3, 102, 'Regular', 'Full-time', NULL, 1),

-- Purchasing (dept 12)
(91201, 'PUR-T01', 'Hannah', 'Go', 'Santos', '2024-03-04', '2001-04-22', 'Lucena City', 'Female', 'Single', 1202, 'Purchasing Staff I', 12, 5, 102, 'Regular', 'Full-time', 91203, 1),
(91202, 'PUR-T02', 'Leo', 'Tan', 'Villanueva', '2024-05-12', '2000-12-30', 'Lucena City', 'Male', 'Single', 1202, 'Purchasing Staff I', 12, 5, 102, 'Regular', 'Full-time', 91203, 1),
(91203, 'PUR-T03', 'Irene', 'Yap', 'Cruz', '2018-10-08', '1989-06-17', 'Lucena City', 'Female', 'Married', 1200, 'Purchasing Supervisor I', 12, 4, 102, 'Regular', 'Full-time', NULL, 1),

-- Independent governance people (no department, nobody reports to them)
(99001, 'GOV-BOD', 'Board', 'Approver', 'Test', '2012-01-01', '1970-01-01', 'Manila', 'Male', 'Married', NULL, 'Board of Directors (test)', NULL, 2, 102, 'Regular', 'Full-time', NULL, 1),
(99002, 'GOV-AUD', 'Audit', 'Approver', 'Test', '2013-01-01', '1972-02-02', 'Manila', 'Female', 'Married', NULL, 'Audit Committee (test)', NULL, 2, 102, 'Regular', 'Full-time', NULL, 1);

REPLACE INTO employee_contacts (employee_id, personal_email, mobile_number, telephone_number) VALUES
(90101, 'ap.t01@test.local', '09170000001', '888-1001'),
(90102, 'ap.t02@test.local', '09170000002', '888-1002'),
(90103, 'ap.t03@test.local', '09170000003', '888-1003'),
(90104, 'ap.t04@test.local', '09170000004', '888-1004'),
(90201, 'aud.t01@test.local', '09170000011', '888-2001'),
(90202, 'aud.t02@test.local', '09170000012', '888-2002'),
(90203, 'aud.t03@test.local', '09170000013', '888-2003'),
(90301, 'bd.t01@test.local', '09170000021', '888-3001'),
(90302, 'bd.t02@test.local', '09170000022', '888-3002'),
(90303, 'bd.t03@test.local', '09170000023', '888-3003'),
(90401, 'com.t01@test.local', '09170000031', '888-4001'),
(90402, 'com.t02@test.local', '09170000032', '888-4002'),
(90403, 'com.t03@test.local', '09170000033', '888-4003'),
(90501, 'fin.t01@test.local', '09170000041', '888-5001'),
(90502, 'fin.t02@test.local', '09170000042', '888-5002'),
(90503, 'fin.t03@test.local', '09170000043', '888-5003'),
(90601, 'gs.t01@test.local', '09170000051', '888-6001'),
(90602, 'gs.t02@test.local', '09170000052', '888-6002'),
(90603, 'gs.t03@test.local', '09170000053', '888-6003'),
(90801, 'it.t01@test.local', '09170000061', '888-8001'),
(90802, 'it.t02@test.local', '09170000062', '888-8002'),
(90803, 'it.t03@test.local', '09170000063', '888-8003'),
(90901, 'mkt.t01@test.local', '09170000071', '888-9001'),
(90902, 'mkt.t02@test.local', '09170000072', '888-9002'),
(90903, 'mkt.t03@test.local', '09170000073', '888-9003'),
(91001, 'op.t01@test.local', '09170000081', '888-0011'),
(91002, 'op.t02@test.local', '09170000082', '888-0012'),
(91101, 'ops.t01@test.local', '09170000091', '888-1101'),
(91102, 'ops.t02@test.local', '09170000092', '888-1102'),
(91103, 'ops.t03@test.local', '09170000093', '888-1103'),
(91201, 'pur.t01@test.local', '09170000101', '888-1201'),
(91202, 'pur.t02@test.local', '09170000102', '888-1202'),
(91203, 'pur.t03@test.local', '09170000103', '888-1203'),
(99001, 'gov.bod@test.local', '09170000901', '888-0001'),
(99002, 'gov.aud@test.local', '09170000902', '888-0002');

REPLACE INTO employee_details (employee_id, height_m, weight_kg, blood_type, citizenship) VALUES
(90101, 1.58, 52.0, 'O+', 'Filipino'),
(90102, 1.72, 70.0, 'A+', 'Filipino'),
(90103, 1.75, 74.0, 'B+', 'Filipino'),
(90104, 1.78, 80.0, 'O+', 'Filipino'),
(90201, 1.70, 68.0, 'A+', 'Filipino'),
(90202, 1.60, 54.0, 'B+', 'Filipino'),
(90203, 1.74, 76.0, 'O+', 'Filipino'),
(90301, 1.71, 69.0, 'O+', 'Filipino'),
(90302, 1.57, 50.0, 'A+', 'Filipino'),
(90303, 1.63, 56.0, 'AB+', 'Filipino'),
(90401, 1.69, 67.0, 'O+', 'Filipino'),
(90402, 1.59, 53.0, 'A+', 'Filipino'),
(90403, 1.73, 72.0, 'B+', 'Filipino'),
(90501, 1.61, 55.0, 'O+', 'Filipino'),
(90502, 1.74, 73.0, 'A+', 'Filipino'),
(90503, 1.62, 57.0, 'B+', 'Filipino'),
(90601, 1.70, 71.0, 'O+', 'Filipino'),
(90602, 1.58, 52.0, 'A+', 'Filipino'),
(90603, 1.76, 78.0, 'O+', 'Filipino'),
(90801, 1.72, 70.0, 'O+', 'Filipino'),
(90802, 1.60, 54.0, 'A+', 'Filipino'),
(90803, 1.75, 75.0, 'B+', 'Filipino'),
(90901, 1.57, 51.0, 'O+', 'Filipino'),
(90902, 1.73, 72.0, 'A+', 'Filipino'),
(90903, 1.64, 58.0, 'B+', 'Filipino'),
(91001, 1.59, 53.0, 'O+', 'Filipino'),
(91002, 1.77, 79.0, 'A+', 'Filipino'),
(91101, 1.70, 69.0, 'O+', 'Filipino'),
(91102, 1.61, 55.0, 'A+', 'Filipino'),
(91103, 1.74, 74.0, 'B+', 'Filipino'),
(91201, 1.58, 52.0, 'O+', 'Filipino'),
(91202, 1.71, 70.0, 'A+', 'Filipino'),
(91203, 1.63, 56.0, 'B+', 'Filipino'),
(99001, 1.76, 78.0, 'O+', 'Filipino'),
(99002, 1.62, 58.0, 'A+', 'Filipino');

SET FOREIGN_KEY_CHECKS = 1;
