-- =====================================================
-- RAQUEL HRIS - DEMO SEED DATA
-- demo_seed.sql
-- =====================================================
-- Purpose : Populate a complete demonstration environment
--           so the system can be presented to a client
--           with realistic, fully-populated data.
--
-- Rules   : • All evaluations remain in a pending / in-progress state.
--           • All career movement requests remain Pending.
--           • Employee IDs start at 20001 to avoid conflicts with
--             existing department seeds (which use 101, 301-302,
--             999, 1001-12003).
--           • user_id for assigned_by / logged_by references user_id=1
--             (System Admin, already seeded in 3rd_seed_HR_accounts_.sql).
--           • Run AFTER all existing seeds have been imported.
-- =====================================================
USE raquel_hris;
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================
-- EVALUATION TEMPLATES (shared across departments)
-- =====================================================
REPLACE INTO evaluation_templates
  (template_id, template_name, description, target_department,
   evaluation_type, kra_weight, behavior_weight,
   form_code, revision_date, effective_date_form, status, created_by)
VALUES
  (1, 'Annual Performance Evaluation – Regular',
   'Standard annual evaluation form for regular employees.',
   NULL, 'Annual', 80.00, 20.00,
   'HRD Form-013.01', '2024-01-01', '2024-01-01', 'Active', 1),
  (2, 'Initial / Probationary Evaluation',
   'Evaluation form for employees on probation or initial contract.',
   NULL, 'Initial', 80.00, 20.00,
   'HRD Form-013.02', '2024-01-01', '2024-01-01', 'Active', 1);

-- =====================================================
-- EVALUATION CRITERIA – Template 1 (Annual, Regular)
-- =====================================================
REPLACE INTO evaluation_criteria
  (criterion_id, template_id, section, criterion_name, weight, scoring_method, sort_order)
VALUES
  (1,  1, 'KRA',      'Quality of Work',           25.00, 'Scale_1_4', 1),
  (2,  1, 'KRA',      'Quantity of Output',         25.00, 'Scale_1_4', 2),
  (3,  1, 'KRA',      'Timeliness',                 25.00, 'Scale_1_4', 3),
  (4,  1, 'KRA',      'Job Knowledge',              25.00, 'Scale_1_4', 4),
  (5,  1, 'Behavior', 'Teamwork & Collaboration',    5.00, 'Scale_1_4', 5),
  (6,  1, 'Behavior', 'Communication Skills',        5.00, 'Scale_1_4', 6),
  (7,  1, 'Behavior', 'Punctuality & Attendance',    5.00, 'Scale_1_4', 7),
  (8,  1, 'Behavior', 'Initiative & Creativity',     5.00, 'Scale_1_4', 8);

-- =====================================================
-- EVALUATION CRITERIA – Template 2 (Initial/Probation)
-- =====================================================
REPLACE INTO evaluation_criteria
  (criterion_id, template_id, section, criterion_name, weight, scoring_method, sort_order)
VALUES
  (9,  2, 'KRA',      'Work Performance',           35.00, 'Scale_1_4', 1),
  (10, 2, 'KRA',      'Accuracy & Attention to Detail', 35.00, 'Scale_1_4', 2),
  (11, 2, 'KRA',      'Learning Agility',           30.00, 'Scale_1_4', 3),
  (12, 2, 'Behavior', 'Attitude & Professionalism', 10.00, 'Scale_1_4', 4),
  (13, 2, 'Behavior', 'Adaptability',               10.00, 'Scale_1_4', 5);

-- =====================================================
-- INFORMATION TECHNOLOGY DEPARTMENT
-- =====================================================

-- Employees (demo) -----------------------------------
REPLACE INTO employees
  (employee_id, employee_code, first_name, last_name, middle_name,
   hire_date, date_of_birth, place_of_birth, gender, civil_status,
   job_title_id, job_title, department_id, rank_category_id, branch_id,
   employment_status, employment_type, profile_picture)
VALUES
  (20001,'DEMO-IT-001','Rafael','Dela Rosa','Aguilar','2020-03-15','1988-07-12','Lucena City, Quezon','Male','Married',800,'IT Manager I',8,3,102,'Regular','Full-time','avatar_m.jpg'),
  (20002,'DEMO-IT-002','Maricel','Buenaventura','Santos','2021-06-01','1992-04-05','Tayabas City, Quezon','Female','Single',804,'IT Supervisor I',8,4,1,'Regular','Full-time','avatar_f.jpg'),
  (20003,'DEMO-IT-003','Reynaldo','Castañeda','Ocampo','2022-02-14','1995-11-20','Lucena City, Quezon','Male','Single',810,'Programmer I',8,5,2,'Regular','Full-time','avatar_m.jpg'),
  (20004,'DEMO-IT-004','Liza','Manalo','Reyes','2023-01-09','1998-03-30','Candelaria, Quezon','Female','Married',811,'Technical Support Staff I',8,5,3,'Regular','Full-time','avatar_f.jpg'),
  (20005,'DEMO-IT-005','Rodolfo','Hernandez','Lim','2023-07-17','1999-08-14','Pagbilao, Quezon','Male','Single',816,'Helpdesk Assistant I',8,5,4,'Probationary','Full-time','avatar_m.jpg'),
  (20006,'DEMO-IT-006','Carla','Roque','Evangelista','2024-04-22','2001-01-25','Sariaya, Quezon','Female','Single',809,'Programmer on Probation',8,5,5,'Probationary','Full-time','avatar_f.jpg');

REPLACE INTO employee_contacts (employee_id, personal_email, mobile_number, telephone_number) VALUES
  (20001,'rafael.delarosa@example.com','09171000001','888-20001'),
  (20002,'maricel.buenaventura@example.com','09171000002','888-20002'),
  (20003,'reynaldo.castaneda@example.com','09171000003','888-20003'),
  (20004,'liza.manalo@example.com','09171000004','888-20004'),
  (20005,'rodolfo.hernandez@example.com','09171000005','888-20005'),
  (20006,'carla.roque@example.com','09171000006','888-20006');

REPLACE INTO employee_details (employee_id, height_m, weight_kg, blood_type, citizenship) VALUES
  (20001,1.72,70.0,'O+','Filipino'),(20002,1.60,54.0,'A+','Filipino'),
  (20003,1.75,68.0,'B+','Filipino'),(20004,1.58,52.0,'AB+','Filipino'),
  (20005,1.70,65.0,'O-','Filipino'),(20006,1.63,55.0,'A-','Filipino');

REPLACE INTO employee_family (employee_id, member_type, surname, first_name, middle_name, occupation) VALUES
  (20001,'Father','Dela Rosa','Ernesto','Aguilar','Retired'),
  (20001,'Mother','Dela Rosa','Milagros','Santos','Homemaker'),
  (20001,'Spouse','Reyes','Anabelle','Cruz','Teacher'),
  (20002,'Father','Buenaventura','Roberto','Santos','Retired'),
  (20002,'Mother','Buenaventura','Teresita','Gomez','Homemaker'),
  (20003,'Father','Castañeda','Armando','Ocampo','Retired'),
  (20003,'Mother','Castañeda','Felicitas','Reyes','Homemaker'),
  (20004,'Father','Manalo','Domingo','Reyes','Retired'),
  (20004,'Mother','Manalo','Cynthia','Flores','Homemaker'),
  (20004,'Spouse','Cruz','Jerome','Santos','Engineer'),
  (20005,'Father','Hernandez','Alfredo','Lim','Retired'),
  (20005,'Mother','Hernandez','Norma','Tan','Homemaker'),
  (20006,'Father','Roque','Nelson','Evangelista','Retired'),
  (20006,'Mother','Roque','Gloria','Dela Cruz','Homemaker');

REPLACE INTO employee_education (employee_id, education_level, school_name, degree_course, year_graduated) VALUES
  (20001,'College','De La Salle University','BS Computer Science','2009'),
  (20002,'College','Mapua University','BS Information Technology','2013'),
  (20003,'College','University of the Philippines','BS Computer Science','2017'),
  (20004,'College','Polytechnic University of the Philippines','BS Information Technology','2020'),
  (20005,'College','Southern Luzon State University','BS Computer Science','2021'),
  (20006,'Graduate Studies','Ateneo de Manila University','MS Information Technology','2023');

REPLACE INTO employee_work_experience (employee_id, date_from, date_to, job_title, company_name, monthly_salary) VALUES
  (20001,'2009-06-01','2020-03-14','Senior IT Officer','Tech Solutions Inc.',45000.00),
  (20002,'2013-08-01','2021-05-31','IT Support Specialist','Global BPO Corp.',32000.00),
  (20003,'2017-07-01','2022-01-31','Software Developer','Innovate PH',28000.00),
  (20004,'2020-03-01','2022-12-31','Helpdesk Analyst','Prime Tech Services',22000.00),
  (20005,'2021-05-01','2023-06-30','IT Intern','Metro Systems',18000.00),
  (20006,'2023-01-01','2024-04-21','Junior Programmer','StartUp Labs PH',24000.00);

REPLACE INTO employee_trainings (employee_id, training_title, conducted_by, no_of_hours) VALUES
  (20001,'ITIL Foundation Certification','Corporate Training Dept',24.0),
  (20002,'Network Security Essentials','Corporate Training Dept',16.0),
  (20003,'Agile Software Development','Corporate Training Dept',20.0),
  (20004,'Customer Service Excellence','Corporate Training Dept',16.0),
  (20005,'IT Infrastructure and Security','Corporate Training Dept',16.0),
  (20006,'Advanced Programming Techniques','Corporate Training Dept',24.0);

REPLACE INTO employee_disclosures (employee_id, is_related_to_company, has_admin_offense, has_criminal_charge) VALUES
  (20001,0,0,0),(20002,0,0,0),(20003,0,0,0),(20004,0,0,0),(20005,0,0,0),(20006,0,0,0);

REPLACE INTO employee_government_ids (employee_id, sss_number, philhealth_number, pagibig_number, tin_number) VALUES
  (20001,'20-1000001-1','20-100000001-1','2001-0001-0001','200-100-001-000'),
  (20002,'20-1000002-2','20-100000002-2','2001-0002-0002','200-100-002-000'),
  (20003,'20-1000003-3','20-100000003-3','2001-0003-0003','200-100-003-000'),
  (20004,'20-1000004-4','20-100000004-4','2001-0004-0004','200-100-004-000'),
  (20005,'20-1000005-5','20-100000005-5','2001-0005-0005','200-100-005-000'),
  (20006,'20-1000006-6','20-100000006-6','2001-0006-0006','200-100-006-000');

REPLACE INTO employee_addresses (employee_id, address_type, barangay, city, province) VALUES
  (20001,'Residential','San Isidro','Lucena City','Quezon'),(20001,'Permanent','San Isidro','Lucena City','Quezon'),
  (20002,'Residential','Barangay 5','Tayabas City','Quezon'),(20002,'Permanent','Barangay 5','Tayabas City','Quezon'),
  (20003,'Residential','Barangay 3','Candelaria','Quezon'),(20003,'Permanent','Barangay 3','Candelaria','Quezon'),
  (20004,'Residential','Barangay 8','Pagbilao','Quezon'),(20004,'Permanent','Barangay 8','Pagbilao','Quezon'),
  (20005,'Residential','Barangay 2','Sariaya','Quezon'),(20005,'Permanent','Barangay 2','Sariaya','Quezon'),
  (20006,'Residential','Barangay 10','Lucena City','Quezon'),(20006,'Permanent','Barangay 10','Lucena City','Quezon');

REPLACE INTO employee_emergency_contacts (employee_id, contact_name, relationship, contact_number) VALUES
  (20001,'Ernesto Dela Rosa','Father','09181000001'),
  (20002,'Roberto Buenaventura','Father','09181000002'),
  (20003,'Armando Castañeda','Father','09181000003'),
  (20004,'Jerome Cruz','Spouse','09181000004'),
  (20005,'Alfredo Hernandez','Father','09181000005'),
  (20006,'Nelson Roque','Father','09181000006');

REPLACE INTO employee_real_properties (employee_id, description, kind, acquisition_cost) VALUES
  (20001,'Residential House and Lot','Building and Land',2800000.00),
  (20002,'Residential House and Lot','Building and Land',2100000.00),
  (20003,'Residential House and Lot','Building and Land',1950000.00),
  (20004,'Residential House and Lot','Building and Land',1750000.00),
  (20005,'Residential House and Lot','Building and Land',1600000.00),
  (20006,'Residential House and Lot','Building and Land',1500000.00);

REPLACE INTO employee_personal_properties (employee_id, description, acquisition_cost) VALUES
  (20001,'Personal Vehicle and Savings',380000.00),(20002,'Personal Savings',210000.00),
  (20003,'Personal Savings',175000.00),(20004,'Personal Savings',155000.00),
  (20005,'Personal Savings',120000.00),(20006,'Personal Savings',100000.00);

REPLACE INTO employee_liabilities (employee_id, nature_of_liability, creditor_name, outstanding_balance) VALUES
  (20001,'Housing Loan','BDO Unibank',950000.00),(20002,'Personal Loan','Bank',85000.00),
  (20003,'Personal Loan','Bank',60000.00),(20004,'Personal Loan','Bank',45000.00),
  (20005,'Personal Loan','Bank',30000.00),(20006,'Personal Loan','Bank',25000.00);

REPLACE INTO employee_references (employee_id, reference_name, reference_address, reference_telephone) VALUES
  (20001,'Engr. Roberto Lim','Lucena City','02-8123-4501'),
  (20002,'Prof. Maria Santos','Quezon City','02-8123-4502'),
  (20003,'Dr. Jose Ramos','Manila','02-8123-4503'),
  (20004,'Atty. Linda Cruz','Lucena City','02-8123-4504'),
  (20005,'Mr. Carlos Tan','Pagbilao, Quezon','02-8123-4505'),
  (20006,'Ms. Rachelle Go','Tayabas City, Quezon','02-8123-4506');

-- Evaluations – IT (all pending, not finalized) -----
REPLACE INTO evaluations
  (evaluation_id, employee_id, template_id, evaluation_type, evaluation_period_start, evaluation_period_end, assigned_by, assigned_at, status, current_position, months_in_position, kra_subtotal, behavior_average, total_score, performance_level)
VALUES
  (2001,20001,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Self-Rating','IT Manager I',60,3.25,3.25,3.25,'Exceeds Expectations'),
  (2002,20002,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Supervisor','IT Supervisor I',42,3.25,3.25,3.25,'Exceeds Expectations'),
  (2003,20003,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending HR Consolidation','Programmer I',36,2.75,2.75,2.75,'Exceeds Expectations'),
  (2004,20004,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Manager','Technical Support Staff I',24,3.00,3.00,3.00,'Exceeds Expectations'),
  (2005,20005,2,'Initial','2025-04-01','2025-09-30',1,NOW(),'Pending Self-Rating','Helpdesk Assistant I',12,3.15,3.00,3.12,'Exceeds Expectations'),
  (2006,20006,2,'Initial','2025-04-01','2025-09-30',1,NOW(),'Pending Supervisor','Programmer on Probation',6,3.32,3.25,3.31,'Exceeds Expectations');

REPLACE INTO evaluation_scores (evaluation_id, criterion_id, score_value, weighted_score) VALUES
  (2001,1,3.00,0.75),(2001,2,3.50,0.88),(2001,3,3.00,0.75),(2001,4,3.50,0.88),
  (2001,5,3.00,3.00),(2001,6,3.50,3.50),(2001,7,3.00,3.00),(2001,8,3.50,3.50),
  (2002,1,3.50,0.88),(2002,2,3.00,0.75),(2002,3,3.50,0.88),(2002,4,3.00,0.75),
  (2002,5,3.50,3.50),(2002,6,3.00,3.00),(2002,7,3.50,3.50),(2002,8,3.00,3.00),
  (2003,1,2.50,0.63),(2003,2,3.00,0.75),(2003,3,2.50,0.63),(2003,4,3.00,0.75),
  (2003,5,3.00,3.00),(2003,6,2.50,2.50),(2003,7,3.00,3.00),(2003,8,2.50,2.50),
  (2004,1,3.00,0.75),(2004,2,3.00,0.75),(2004,3,3.00,0.75),(2004,4,3.00,0.75),
  (2004,5,3.00,3.00),(2004,6,3.00,3.00),(2004,7,3.00,3.00),(2004,8,3.00,3.00),
  -- Probationary / Initial evaluations (Template 2 – criteria 9-13)
  (2005,9,3.00,1.05),(2005,10,3.00,1.05),(2005,11,3.50,1.05),(2005,12,3.00,3.00),(2005,13,3.00,3.00),
  (2006,9,3.00,1.05),(2006,10,3.50,1.22),(2006,11,3.50,1.05),(2006,12,3.00,3.00),(2006,13,3.50,3.50);

-- Career Movements – IT (all Pending) ---------------
REPLACE INTO career_movements
  (movement_id, employee_id, movement_type, previous_position, new_position,
   previous_branch_id, new_branch_id, effective_date, reason, logged_by, approval_status, is_applied)
VALUES
  (2001,20003,'Promotion','Programmer I','IT Supervisor I',102,102,'2025-08-01',
   'Consistent outstanding performance and successful project deliveries.',1,'Pending',0),
  (2002,20004,'Role Change','Technical Support Staff I','Technical Support Staff II',102,102,'2025-09-01',
   'Skills upgrade and expanded responsibilities in network support.',1,'Pending',0);

-- =====================================================
-- HUMAN RESOURCES DEPARTMENT
-- =====================================================

-- Employees (demo) -----------------------------------
REPLACE INTO employees
  (employee_id, employee_code, first_name, last_name, middle_name,
   hire_date, date_of_birth, place_of_birth, gender, civil_status,
   job_title_id, job_title, department_id, rank_category_id, branch_id,
   employment_status, employment_type, profile_picture)
VALUES
  (20011,'DEMO-HR-001','Amelia','Navarro','Delos Reyes','2019-05-20','1986-09-15','Lucena City, Quezon','Female','Married',700,'HR Manager I',7,3,6,'Regular','Full-time','avatar_f.jpg'),
  (20012,'DEMO-HR-002','Bernard','Ocampo','Villanueva','2020-08-10','1990-12-01','Tayabas City, Quezon','Male','Single',705,'HR Supervisor I',7,4,7,'Regular','Full-time','avatar_m.jpg'),
  (20013,'DEMO-HR-003','Clarissa','Reyes','Santos','2022-03-01','1994-06-18','Candelaria, Quezon','Female','Single',711,'HR Staff I',7,5,8,'Regular','Full-time','avatar_f.jpg'),
  (20014,'DEMO-HR-004','Dennis','Salazar','Perez','2023-06-12','1996-11-09','Sariaya, Quezon','Male','Single',712,'HR Staff II',7,5,9,'Regular','Full-time','avatar_m.jpg'),
  (20015,'DEMO-HR-005','Evelyn','Aquino','Cruz','2024-01-08','1999-02-28','Pagbilao, Quezon','Female','Single',710,'HR Staff on Probation',7,5,10,'Probationary','Full-time','avatar_f.jpg');

REPLACE INTO employee_contacts (employee_id, personal_email, mobile_number, telephone_number) VALUES
  (20011,'amelia.navarro@example.com','09172000011','888-20011'),
  (20012,'bernard.ocampo@example.com','09172000012','888-20012'),
  (20013,'clarissa.reyes@example.com','09172000013','888-20013'),
  (20014,'dennis.salazar@example.com','09172000014','888-20014'),
  (20015,'evelyn.aquino@example.com','09172000015','888-20015');

REPLACE INTO employee_details (employee_id, height_m, weight_kg, blood_type, citizenship) VALUES
  (20011,1.62,56.0,'A+','Filipino'),(20012,1.74,72.0,'O+','Filipino'),
  (20013,1.59,53.0,'B+','Filipino'),(20014,1.70,68.0,'A-','Filipino'),
  (20015,1.57,50.0,'AB+','Filipino');

REPLACE INTO employee_family (employee_id, member_type, surname, first_name, middle_name, occupation) VALUES
  (20011,'Father','Navarro','Domingo','Delos Reyes','Retired'),
  (20011,'Mother','Navarro','Remedios','Santos','Homemaker'),
  (20011,'Spouse','Bautista','Frederick','Cruz','Engineer'),
  (20012,'Father','Ocampo','Leopoldo','Villanueva','Retired'),
  (20012,'Mother','Ocampo','Myrna','Garcia','Homemaker'),
  (20013,'Father','Reyes','Andres','Santos','Retired'),
  (20013,'Mother','Reyes','Consuelo','Lim','Homemaker'),
  (20014,'Father','Salazar','Gregorio','Perez','Retired'),
  (20014,'Mother','Salazar','Rosalinda','Torres','Homemaker'),
  (20015,'Father','Aquino','Marcelino','Cruz','Retired'),
  (20015,'Mother','Aquino','Dolores','Ramos','Homemaker');

REPLACE INTO employee_education (employee_id, education_level, school_name, degree_course, year_graduated) VALUES
  (20011,'Graduate Studies','University of the Philippines','MA Industrial and Organizational Psychology','2011'),
  (20012,'College','Ateneo de Manila University','BS Human Resource Management','2012'),
  (20013,'College','Far Eastern University','BS Psychology','2016'),
  (20014,'College','Pamantasan ng Lungsod ng Maynila','BS Human Resource Management','2018'),
  (20015,'College','Southern Luzon State University','BS Psychology','2021');

REPLACE INTO employee_work_experience (employee_id, date_from, date_to, job_title, company_name, monthly_salary) VALUES
  (20011,'2011-07-01','2019-05-19','HR Manager','Quezon Financial Group',48000.00),
  (20012,'2012-09-01','2020-08-09','HR Coordinator','Philippine Staffing Inc.',30000.00),
  (20013,'2016-07-01','2022-02-28','HR Assistant','Talent Bridge Corp.',22000.00),
  (20014,'2018-06-01','2023-06-11','Recruitment Staff','HireRight Philippines',20000.00),
  (20015,'2021-07-01','2023-12-31','HR Trainee','People Solutions Co.',18000.00);

REPLACE INTO employee_trainings (employee_id, training_title, conducted_by, no_of_hours) VALUES
  (20011,'Strategic HR & Talent Development','Corporate Training Dept',40.0),
  (20012,'Labor Relations and Employment Law','Corporate Training Dept',24.0),
  (20013,'Recruitment Best Practices','Corporate Training Dept',16.0),
  (20014,'Employee Relations Fundamentals','Corporate Training Dept',16.0),
  (20015,'HR Orientation and Standards','Corporate Training Dept',8.0);

REPLACE INTO employee_disclosures (employee_id, is_related_to_company, has_admin_offense, has_criminal_charge) VALUES
  (20011,0,0,0),(20012,0,0,0),(20013,0,0,0),(20014,0,0,0),(20015,0,0,0);

REPLACE INTO employee_government_ids (employee_id, sss_number, philhealth_number, pagibig_number, tin_number) VALUES
  (20011,'20-1000011-1','20-100001101-1','2001-0011-0011','200-100-011-000'),
  (20012,'20-1000012-2','20-100001202-2','2001-0012-0012','200-100-012-000'),
  (20013,'20-1000013-3','20-100001303-3','2001-0013-0013','200-100-013-000'),
  (20014,'20-1000014-4','20-100001404-4','2001-0014-0014','200-100-014-000'),
  (20015,'20-1000015-5','20-100001505-5','2001-0015-0015','200-100-015-000');

REPLACE INTO employee_addresses (employee_id, address_type, barangay, city, province) VALUES
  (20011,'Residential','San Nicolas','Tayabas City','Quezon'),(20011,'Permanent','San Nicolas','Tayabas City','Quezon'),
  (20012,'Residential','Barangay 7','Lucena City','Quezon'),(20012,'Permanent','Barangay 7','Lucena City','Quezon'),
  (20013,'Residential','Barangay 4','Candelaria','Quezon'),(20013,'Permanent','Barangay 4','Candelaria','Quezon'),
  (20014,'Residential','Barangay 9','Sariaya','Quezon'),(20014,'Permanent','Barangay 9','Sariaya','Quezon'),
  (20015,'Residential','Barangay 1','Pagbilao','Quezon'),(20015,'Permanent','Barangay 1','Pagbilao','Quezon');

REPLACE INTO employee_emergency_contacts (employee_id, contact_name, relationship, contact_number) VALUES
  (20011,'Frederick Bautista','Spouse','09182000011'),
  (20012,'Leopoldo Ocampo','Father','09182000012'),
  (20013,'Andres Reyes','Father','09182000013'),
  (20014,'Gregorio Salazar','Father','09182000014'),
  (20015,'Marcelino Aquino','Father','09182000015');

REPLACE INTO employee_real_properties (employee_id, description, kind, acquisition_cost) VALUES
  (20011,'Residential House and Lot','Building and Land',2900000.00),
  (20012,'Residential House and Lot','Building and Land',2200000.00),
  (20013,'Residential House and Lot','Building and Land',1800000.00),
  (20014,'Residential House and Lot','Building and Land',1650000.00),
  (20015,'Residential House and Lot','Building and Land',1400000.00);

REPLACE INTO employee_personal_properties (employee_id, description, acquisition_cost) VALUES
  (20011,'Personal Vehicle and Savings',420000.00),(20012,'Personal Savings',220000.00),
  (20013,'Personal Savings',160000.00),(20014,'Personal Savings',130000.00),
  (20015,'Personal Savings',80000.00);

REPLACE INTO employee_liabilities (employee_id, nature_of_liability, creditor_name, outstanding_balance) VALUES
  (20011,'Housing Loan','Metrobank',1100000.00),(20012,'Personal Loan','Bank',72000.00),
  (20013,'Personal Loan','Bank',48000.00),(20014,'Personal Loan','Bank',35000.00),
  (20015,'Personal Loan','Bank',20000.00);

REPLACE INTO employee_references (employee_id, reference_name, reference_address, reference_telephone) VALUES
  (20011,'Atty. Ma. Victoria Ramos','Lucena City','02-8234-5601'),
  (20012,'Prof. Eduardo Abad','Quezon City','02-8234-5602'),
  (20013,'Dr. Anna Santos','Tayabas City, Quezon','02-8234-5603'),
  (20014,'Mr. Bernard Lim','Lucena City','02-8234-5604'),
  (20015,'Ms. Patricia Gomez','Candelaria, Quezon','02-8234-5605');

-- Evaluations – HR (all pending, not finalized) -----
REPLACE INTO evaluations
  (evaluation_id, employee_id, template_id, evaluation_type, evaluation_period_start, evaluation_period_end, assigned_by, assigned_at, status, current_position, months_in_position, kra_subtotal, behavior_average, total_score, performance_level)
VALUES
  (2011,20011,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Supervisor','HR Manager I',72,3.63,3.63,3.63,'Outstanding'),
  (2012,20012,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending HR Consolidation','HR Supervisor I',54,3.25,3.13,3.23,'Exceeds Expectations'),
  (2013,20013,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Self-Rating','HR Staff I',36,2.75,2.75,2.75,'Exceeds Expectations'),
  (2014,20014,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Manager','HR Staff II',24,3.00,3.00,3.00,'Exceeds Expectations'),
  (2015,20015,2,'Initial','2025-01-01','2025-06-30',1,NOW(),'Pending Supervisor','HR Staff on Probation',6,2.83,3.25,2.91,'Exceeds Expectations');

REPLACE INTO evaluation_scores (evaluation_id, criterion_id, score_value, weighted_score) VALUES
  (2011,1,3.50,0.88),(2011,2,3.50,0.88),(2011,3,4.00,1.00),(2011,4,3.50,0.88),
  (2011,5,3.50,3.50),(2011,6,4.00,4.00),(2011,7,3.50,3.50),(2011,8,3.50,3.50),
  (2012,1,3.00,0.75),(2012,2,3.50,0.88),(2012,3,3.00,0.75),(2012,4,3.50,0.88),
  (2012,5,3.00,3.00),(2012,6,3.00,3.00),(2012,7,3.50,3.50),(2012,8,3.00,3.00),
  (2013,1,2.50,0.63),(2013,2,3.00,0.75),(2013,3,2.50,0.63),(2013,4,3.00,0.75),
  (2013,5,2.50,2.50),(2013,6,3.00,3.00),(2013,7,3.00,3.00),(2013,8,2.50,2.50),
  -- Probationary / Initial evaluation (Template 2 – criteria 9-13)
  (2015,9,3.00,1.05),(2015,10,2.50,0.88),(2015,11,3.00,0.90),(2015,12,3.50,3.50),(2015,13,3.00,3.00);

-- Career Movements – HR (all Pending) ---------------
REPLACE INTO career_movements
  (movement_id, employee_id, movement_type, previous_position, new_position,
   previous_branch_id, new_branch_id, effective_date, reason, logged_by, approval_status, is_applied)
VALUES
  (2011,20012,'Promotion','HR Supervisor I','HR Manager II',102,102,'2025-09-01',
   'Demonstrated exceptional supervisory skills and mentorship of junior HR staff.',1,'Pending',0),
  (2012,20013,'Role Change','HR Staff I','HR Staff II',102,102,'2025-08-15',
   'Additional responsibilities assumed in recruitment and onboarding.',1,'Pending',0);

-- =====================================================
-- FINANCE DEPARTMENT
-- =====================================================

-- Employees (demo) -----------------------------------
REPLACE INTO employees
  (employee_id, employee_code, first_name, last_name, middle_name,
   hire_date, date_of_birth, place_of_birth, gender, civil_status,
   job_title_id, job_title, department_id, rank_category_id, branch_id,
   employment_status, employment_type, profile_picture)
VALUES
  (20021,'DEMO-FIN-001','Gregorio','Mercado','Lacson','2018-04-10','1985-03-22','Lucena City, Quezon','Male','Married',500,'VP for Finance',5,1,11,'Regular','Full-time','avatar_m.jpg'),
  (20022,'DEMO-FIN-002','Jocelyn','Pascual','Avila','2019-09-03','1989-07-14','Tayabas City, Quezon','Female','Married',501,'Accounting Supervisor I',5,4,12,'Regular','Full-time','avatar_f.jpg'),
  (20023,'DEMO-FIN-003','Nestor','Aguilar','Romero','2020-11-16','1991-05-05','Candelaria, Quezon','Male','Single',505,'Treasury Supervisor I',5,4,13,'Regular','Full-time','avatar_m.jpg'),
  (20024,'DEMO-FIN-004','Patricia','Fuentes','Dela Cruz','2021-07-07','1993-01-17','Sariaya, Quezon','Female','Single',511,'Accounting Staff I',5,5,14,'Regular','Full-time','avatar_f.jpg'),
  (20025,'DEMO-FIN-005','Roberto','Ignacio','Paglinawan','2022-02-21','1994-10-08','Lucena City, Quezon','Male','Married',516,'Treasury Staff I',5,5,15,'Regular','Full-time','avatar_m.jpg'),
  (20026,'DEMO-FIN-006','Shiela','Domingo','Mendez','2023-08-14','1997-04-30','Pagbilao, Quezon','Female','Single',510,'Accounting Staff on Probation',5,5,16,'Probationary','Full-time','avatar_f.jpg'),
  (20027,'DEMO-FIN-007','Teodoro','Cabrera','Sandoval','2024-03-04','1999-12-11','Lucena City, Quezon','Male','Single',517,'Treasury Staff II',5,5,17,'Regular','Full-time','avatar_m.jpg');

REPLACE INTO employee_contacts (employee_id, personal_email, mobile_number, telephone_number) VALUES
  (20021,'gregorio.mercado@example.com','09173000021','888-20021'),
  (20022,'jocelyn.pascual@example.com','09173000022','888-20022'),
  (20023,'nestor.aguilar@example.com','09173000023','888-20023'),
  (20024,'patricia.fuentes@example.com','09173000024','888-20024'),
  (20025,'roberto.ignacio@example.com','09173000025','888-20025'),
  (20026,'shiela.domingo@example.com','09173000026','888-20026'),
  (20027,'teodoro.cabrera@example.com','09173000027','888-20027');

REPLACE INTO employee_details (employee_id, height_m, weight_kg, blood_type, citizenship) VALUES
  (20021,1.73,75.0,'A+','Filipino'),(20022,1.61,58.0,'O+','Filipino'),
  (20023,1.70,70.0,'B+','Filipino'),(20024,1.58,54.0,'A-','Filipino'),
  (20025,1.72,72.0,'AB+','Filipino'),(20026,1.60,55.0,'O-','Filipino'),
  (20027,1.74,73.0,'A+','Filipino');

REPLACE INTO employee_family (employee_id, member_type, surname, first_name, middle_name, occupation) VALUES
  (20021,'Father','Mercado','Filomeno','Lacson','Retired'),
  (20021,'Mother','Mercado','Simplicia','Torres','Homemaker'),
  (20021,'Spouse','Reyes','Lourdes','Gomez','Accountant'),
  (20022,'Father','Pascual','Victorino','Avila','Retired'),
  (20022,'Mother','Pascual','Herminia','Perez','Homemaker'),
  (20022,'Spouse','Castillo','Ronaldo','Luna','Teacher'),
  (20023,'Father','Aguilar','Herminio','Romero','Retired'),
  (20023,'Mother','Aguilar','Liwayway','Santos','Homemaker'),
  (20024,'Father','Fuentes','Elias','Dela Cruz','Retired'),
  (20024,'Mother','Fuentes','Purificacion','Gomez','Homemaker'),
  (20025,'Father','Ignacio','Renato','Paglinawan','Retired'),
  (20025,'Mother','Ignacio','Loreta','Rivera','Homemaker'),
  (20025,'Spouse','Santos','Cristina','Villanueva','Nurse'),
  (20026,'Father','Domingo','Rodrigo','Mendez','Retired'),
  (20026,'Mother','Domingo','Estrella','Ocampo','Homemaker'),
  (20027,'Father','Cabrera','Bonifacio','Sandoval','Retired'),
  (20027,'Mother','Cabrera','Flordeliza','Cruz','Homemaker');

REPLACE INTO employee_education (employee_id, education_level, school_name, degree_course, year_graduated) VALUES
  (20021,'Graduate Studies','Ateneo de Manila University','MBA Finance','2010'),
  (20022,'College','De La Salle University','BS Accountancy','2011'),
  (20023,'College','University of Santo Tomas','BS Finance','2013'),
  (20024,'College','Polytechnic University of the Philippines','BS Accountancy','2015'),
  (20025,'College','Far Eastern University','BS Finance','2016'),
  (20026,'College','Southern Luzon State University','BS Accountancy','2019'),
  (20027,'College','Mapua University','BS Finance','2022');

REPLACE INTO employee_work_experience (employee_id, date_from, date_to, job_title, company_name, monthly_salary) VALUES
  (20021,'2010-06-01','2018-04-09','Finance Manager','Quezon Savings Bank',55000.00),
  (20022,'2011-07-01','2019-09-02','Accounting Supervisor','Regional Cooperative',38000.00),
  (20023,'2013-08-01','2020-11-15','Treasury Officer','Provincial Lending Corp.',32000.00),
  (20024,'2015-06-01','2021-07-06','Accounting Staff','Metro Finance and Accounting',22000.00),
  (20025,'2016-07-01','2022-02-20','Treasury Staff','National Development Bank',24000.00),
  (20026,'2019-06-01','2023-08-13','Finance Intern','Quezon Financial Group',17000.00),
  (20027,'2022-07-01','2024-03-03','Finance Associate','Pacific Lending Co.',20000.00);

REPLACE INTO employee_trainings (employee_id, training_title, conducted_by, no_of_hours) VALUES
  (20021,'Financial Management and Tax Audits','Corporate Training Dept',32.0),
  (20022,'Advanced Accounting Standards','Corporate Training Dept',24.0),
  (20023,'Treasury and Cash Management','Corporate Training Dept',24.0),
  (20024,'Accounting Fundamentals','Corporate Training Dept',16.0),
  (20025,'Treasury Operations','Corporate Training Dept',16.0),
  (20026,'Bookkeeping Essentials','Corporate Training Dept',16.0),
  (20027,'Financial Reporting Standards','Corporate Training Dept',16.0);

REPLACE INTO employee_disclosures (employee_id, is_related_to_company, has_admin_offense, has_criminal_charge) VALUES
  (20021,0,0,0),(20022,0,0,0),(20023,0,0,0),(20024,0,0,0),(20025,0,0,0),(20026,0,0,0),(20027,0,0,0);

REPLACE INTO employee_government_ids (employee_id, sss_number, philhealth_number, pagibig_number, tin_number) VALUES
  (20021,'20-1000021-1','20-100002101-1','2001-0021-0021','200-100-021-000'),
  (20022,'20-1000022-2','20-100002202-2','2001-0022-0022','200-100-022-000'),
  (20023,'20-1000023-3','20-100002303-3','2001-0023-0023','200-100-023-000'),
  (20024,'20-1000024-4','20-100002404-4','2001-0024-0024','200-100-024-000'),
  (20025,'20-1000025-5','20-100002505-5','2001-0025-0025','200-100-025-000'),
  (20026,'20-1000026-6','20-100002606-6','2001-0026-0026','200-100-026-000'),
  (20027,'20-1000027-7','20-100002707-7','2001-0027-0027','200-100-027-000');

REPLACE INTO employee_addresses (employee_id, address_type, barangay, city, province) VALUES
  (20021,'Residential','Poblacion','Tayabas City','Quezon'),(20021,'Permanent','Poblacion','Tayabas City','Quezon'),
  (20022,'Residential','Barangay 6','Lucena City','Quezon'),(20022,'Permanent','Barangay 6','Lucena City','Quezon'),
  (20023,'Residential','Barangay 5','Candelaria','Quezon'),(20023,'Permanent','Barangay 5','Candelaria','Quezon'),
  (20024,'Residential','Barangay 11','Sariaya','Quezon'),(20024,'Permanent','Barangay 11','Sariaya','Quezon'),
  (20025,'Residential','Barangay 4','Lucena City','Quezon'),(20025,'Permanent','Barangay 4','Lucena City','Quezon'),
  (20026,'Residential','Barangay 7','Pagbilao','Quezon'),(20026,'Permanent','Barangay 7','Pagbilao','Quezon'),
  (20027,'Residential','Barangay 2','Lucena City','Quezon'),(20027,'Permanent','Barangay 2','Lucena City','Quezon');

REPLACE INTO employee_emergency_contacts (employee_id, contact_name, relationship, contact_number) VALUES
  (20021,'Lourdes Reyes','Spouse','09183000021'),
  (20022,'Ronaldo Castillo','Spouse','09183000022'),
  (20023,'Herminio Aguilar','Father','09183000023'),
  (20024,'Elias Fuentes','Father','09183000024'),
  (20025,'Cristina Santos','Spouse','09183000025'),
  (20026,'Rodrigo Domingo','Father','09183000026'),
  (20027,'Bonifacio Cabrera','Father','09183000027');

REPLACE INTO employee_real_properties (employee_id, description, kind, acquisition_cost) VALUES
  (20021,'Residential House and Lot','Building and Land',3500000.00),
  (20022,'Residential House and Lot','Building and Land',2400000.00),
  (20023,'Residential House and Lot','Building and Land',2100000.00),
  (20024,'Residential House and Lot','Building and Land',1850000.00),
  (20025,'Residential House and Lot','Building and Land',1900000.00),
  (20026,'Residential House and Lot','Building and Land',1550000.00),
  (20027,'Residential House and Lot','Building and Land',1450000.00);

REPLACE INTO employee_personal_properties (employee_id, description, acquisition_cost) VALUES
  (20021,'Personal Vehicle and Savings',490000.00),(20022,'Personal Vehicle and Savings',280000.00),
  (20023,'Personal Savings',240000.00),(20024,'Personal Savings',170000.00),
  (20025,'Personal Savings',190000.00),(20026,'Personal Savings',110000.00),
  (20027,'Personal Savings',95000.00);

REPLACE INTO employee_liabilities (employee_id, nature_of_liability, creditor_name, outstanding_balance) VALUES
  (20021,'Housing Loan','BDO Unibank',1800000.00),(20022,'Housing Loan','Metrobank',950000.00),
  (20023,'Personal Loan','Bank',90000.00),(20024,'Personal Loan','Bank',65000.00),
  (20025,'Personal Loan','Bank',75000.00),(20026,'Personal Loan','Bank',28000.00),
  (20027,'Personal Loan','Bank',22000.00);

REPLACE INTO employee_references (employee_id, reference_name, reference_address, reference_telephone) VALUES
  (20021,'CPA Ramon Ferrer','Lucena City','02-8345-6701'),
  (20022,'Prof. Celestina Sy','Quezon City','02-8345-6702'),
  (20023,'Mr. Victoriano Cruz','Candelaria, Quezon','02-8345-6703'),
  (20024,'Ms. Remedios Tan','Sariaya, Quezon','02-8345-6704'),
  (20025,'Mr. Arnaldo Bautista','Lucena City','02-8345-6705'),
  (20026,'Ms. Leonida Soriano','Pagbilao, Quezon','02-8345-6706'),
  (20027,'Mr. Ricardo Espino','Lucena City','02-8345-6707');

-- Evaluations – Finance (all pending, not finalized) -
REPLACE INTO evaluations
  (evaluation_id, employee_id, template_id, evaluation_type, evaluation_period_start, evaluation_period_end, assigned_by, assigned_at, status, current_position, months_in_position, kra_subtotal, behavior_average, total_score, performance_level)
VALUES
  (2021,20021,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Manager','VP for Finance',84,3.88,3.75,3.85,'Outstanding'),
  (2022,20022,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Supervisor','Accounting Supervisor I',66,3.50,3.38,3.48,'Exceeds Expectations'),
  (2023,20023,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending HR Consolidation','Treasury Supervisor I',48,3.13,3.13,3.13,'Exceeds Expectations'),
  (2024,20024,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Self-Rating','Accounting Staff I',42,2.88,2.75,2.85,'Exceeds Expectations'),
  (2025,20025,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Supervisor','Treasury Staff I',36,3.00,3.00,3.00,'Exceeds Expectations'),
  (2026,20026,2,'Initial','2025-08-01','2026-01-31',1,NOW(),'Pending Self-Rating','Accounting Staff on Probation',4,2.83,2.75,2.81,'Exceeds Expectations'),
  (2027,20027,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending HR Consolidation','Treasury Staff II',16,2.75,2.75,2.75,'Exceeds Expectations');

REPLACE INTO evaluation_scores (evaluation_id, criterion_id, score_value, weighted_score) VALUES
  (2021,1,4.00,1.00),(2021,2,4.00,1.00),(2021,3,3.50,0.88),(2021,4,4.00,1.00),
  (2021,5,4.00,4.00),(2021,6,3.50,3.50),(2021,7,4.00,4.00),(2021,8,3.50,3.50),
  (2022,1,3.50,0.88),(2022,2,3.50,0.88),(2022,3,3.50,0.88),(2022,4,3.50,0.88),
  (2022,5,3.50,3.50),(2022,6,3.50,3.50),(2022,7,3.50,3.50),(2022,8,3.00,3.00),
  (2023,1,3.00,0.75),(2023,2,3.50,0.88),(2023,3,3.00,0.75),(2023,4,3.00,0.75),
  (2023,5,3.00,3.00),(2023,6,3.00,3.00),(2023,7,3.50,3.50),(2023,8,3.00,3.00),
  (2024,1,3.00,0.75),(2024,2,2.50,0.63),(2024,3,3.00,0.75),(2024,4,3.00,0.75),
  (2024,5,2.50,2.50),(2024,6,3.00,3.00),(2024,7,3.00,3.00),(2024,8,2.50,2.50),
  (2025,1,3.00,0.75),(2025,2,3.00,0.75),(2025,3,3.00,0.75),(2025,4,3.00,0.75),
  (2025,5,3.00,3.00),(2025,6,3.00,3.00),(2025,7,3.00,3.00),(2025,8,3.00,3.00),
  (2027,1,2.50,0.63),(2027,2,3.00,0.75),(2027,3,2.50,0.63),(2027,4,3.00,0.75),
  (2027,5,3.00,3.00),(2027,6,2.50,2.50),(2027,7,3.00,3.00),(2027,8,2.50,2.50),
  -- Probationary / Initial evaluation (Template 2 – criteria 9-13)
  (2026,9,2.50,0.88),(2026,10,3.00,1.05),(2026,11,3.00,0.90),(2026,12,3.00,3.00),(2026,13,2.50,2.50);

-- Career Movements – Finance (all Pending) ----------
REPLACE INTO career_movements
  (movement_id, employee_id, movement_type, previous_position, new_position,
   previous_branch_id, new_branch_id, effective_date, reason, logged_by, approval_status, is_applied)
VALUES
  (2021,20022,'Promotion','Accounting Supervisor I','Accounting Supervisor II',102,102,'2025-10-01',
   'Excellent performance in managing accounts receivable and payroll processes.',1,'Pending',0),
  (2022,20024,'Role Change','Accounting Staff I','Accounting Staff II',102,102,'2025-09-15',
   'Expanded duties covering fixed assets and financial reconciliation.',1,'Pending',0),
  (2023,20026,'Regularization','Accounting Staff on Probation','Accounting Staff I',102,102,'2026-02-14',
   'Successfully completed probationary period with commendable performance.',1,'Pending',0);

-- =====================================================
-- MARKETING DEPARTMENT
-- =====================================================

-- Employees (demo) -----------------------------------
REPLACE INTO employees
  (employee_id, employee_code, first_name, last_name, middle_name,
   hire_date, date_of_birth, place_of_birth, gender, civil_status,
   job_title_id, job_title, department_id, rank_category_id, branch_id,
   employment_status, employment_type, profile_picture)
VALUES
  (20031,'DEMO-MKT-001','Irene','Villafuerte','Apostol','2019-07-01','1987-02-11','Lucena City, Quezon','Female','Married',900,'Marketing Manager I',9,3,18,'Regular','Full-time','avatar_f.jpg'),
  (20032,'DEMO-MKT-002','Jonathan','Benigno','Zamora','2020-10-05','1990-08-19','Tayabas City, Quezon','Male','Single',902,'Marketing Supervisor I',9,4,19,'Regular','Full-time','avatar_m.jpg'),
  (20033,'DEMO-MKT-003','Kristine','Lacsamana','Morales','2022-01-17','1995-05-03','Candelaria, Quezon','Female','Single',905,'Marketing Staff I',9,5,20,'Regular','Full-time','avatar_f.jpg'),
  (20034,'DEMO-MKT-004','Leonardo','Espiritu','Padilla','2023-04-24','1997-09-27','Lucena City, Quezon','Male','Single',906,'Marketing Staff II',9,5,21,'Regular','Full-time','avatar_m.jpg'),
  (20035,'DEMO-MKT-005','Marigold','Bitangcol','Reyes','2024-06-10','2000-12-15','Sariaya, Quezon','Female','Single',904,'Marketing Staff on Probation',9,5,22,'Probationary','Full-time','avatar_f.jpg');

REPLACE INTO employee_contacts (employee_id, personal_email, mobile_number, telephone_number) VALUES
  (20031,'irene.villafuerte@example.com','09174000031','888-20031'),
  (20032,'jonathan.benigno@example.com','09174000032','888-20032'),
  (20033,'kristine.lacsamana@example.com','09174000033','888-20033'),
  (20034,'leonardo.espiritu@example.com','09174000034','888-20034'),
  (20035,'marigold.bitangcol@example.com','09174000035','888-20035');

REPLACE INTO employee_details (employee_id, height_m, weight_kg, blood_type, citizenship) VALUES
  (20031,1.62,57.0,'A+','Filipino'),(20032,1.74,72.0,'O+','Filipino'),
  (20033,1.58,52.0,'B+','Filipino'),(20034,1.71,68.0,'AB+','Filipino'),
  (20035,1.60,54.0,'A-','Filipino');

REPLACE INTO employee_family (employee_id, member_type, surname, first_name, middle_name, occupation) VALUES
  (20031,'Father','Villafuerte','Crisanto','Apostol','Retired'),
  (20031,'Mother','Villafuerte','Lorenza','Cruz','Homemaker'),
  (20031,'Spouse','Natividad','Esteban','Santos','Businessman'),
  (20032,'Father','Benigno','Macario','Zamora','Retired'),
  (20032,'Mother','Benigno','Conchita','Villanueva','Homemaker'),
  (20033,'Father','Lacsamana','Prudencio','Morales','Retired'),
  (20033,'Mother','Lacsamana','Caridad','Reyes','Homemaker'),
  (20034,'Father','Espiritu','Isidro','Padilla','Retired'),
  (20034,'Mother','Espiritu','Teofila','Gonzales','Homemaker'),
  (20035,'Father','Bitangcol','Evaristo','Reyes','Retired'),
  (20035,'Mother','Bitangcol','Erlinda','Lim','Homemaker');

REPLACE INTO employee_education (employee_id, education_level, school_name, degree_course, year_graduated) VALUES
  (20031,'Graduate Studies','University of the Philippines','MA Marketing Management','2012'),
  (20032,'College','Ateneo de Manila University','BS Marketing','2012'),
  (20033,'College','De La Salle University','AB Mass Communication','2017'),
  (20034,'College','University of Santo Tomas','BS Marketing','2019'),
  (20035,'College','Pamantasan ng Lungsod ng Maynila','AB Communication','2022');

REPLACE INTO employee_work_experience (employee_id, date_from, date_to, job_title, company_name, monthly_salary) VALUES
  (20031,'2012-08-01','2019-06-30','Marketing Manager','Pacific Marketing Group',47000.00),
  (20032,'2012-09-01','2020-10-04','Marketing Supervisor','Global Retail Corp.',32000.00),
  (20033,'2017-07-01','2022-01-16','Content Marketing Specialist','Digital Agency PH',24000.00),
  (20034,'2019-06-01','2023-04-23','Marketing Associate','Promo Solutions Inc.',22000.00),
  (20035,'2022-07-01','2024-06-09','Marketing Trainee','Brand Forward PH',17000.00);

REPLACE INTO employee_trainings (employee_id, training_title, conducted_by, no_of_hours) VALUES
  (20031,'Brand Strategy and Digital Marketing','Corporate Training Dept',32.0),
  (20032,'Social Media and Campaign Management','Corporate Training Dept',24.0),
  (20033,'Content Creation Workshop','Corporate Training Dept',16.0),
  (20034,'Marketing Analytics Fundamentals','Corporate Training Dept',16.0),
  (20035,'Marketing Orientation Program','Corporate Training Dept',8.0);

REPLACE INTO employee_disclosures (employee_id, is_related_to_company, has_admin_offense, has_criminal_charge) VALUES
  (20031,0,0,0),(20032,0,0,0),(20033,0,0,0),(20034,0,0,0),(20035,0,0,0);

REPLACE INTO employee_government_ids (employee_id, sss_number, philhealth_number, pagibig_number, tin_number) VALUES
  (20031,'20-1000031-1','20-100003101-1','2001-0031-0031','200-100-031-000'),
  (20032,'20-1000032-2','20-100003202-2','2001-0032-0032','200-100-032-000'),
  (20033,'20-1000033-3','20-100003303-3','2001-0033-0033','200-100-033-000'),
  (20034,'20-1000034-4','20-100003404-4','2001-0034-0034','200-100-034-000'),
  (20035,'20-1000035-5','20-100003505-5','2001-0035-0035','200-100-035-000');

REPLACE INTO employee_addresses (employee_id, address_type, barangay, city, province) VALUES
  (20031,'Residential','Barangay 1','Tayabas City','Quezon'),(20031,'Permanent','Barangay 1','Tayabas City','Quezon'),
  (20032,'Residential','Barangay 9','Lucena City','Quezon'),(20032,'Permanent','Barangay 9','Lucena City','Quezon'),
  (20033,'Residential','Barangay 6','Candelaria','Quezon'),(20033,'Permanent','Barangay 6','Candelaria','Quezon'),
  (20034,'Residential','Barangay 3','Lucena City','Quezon'),(20034,'Permanent','Barangay 3','Lucena City','Quezon'),
  (20035,'Residential','Barangay 5','Sariaya','Quezon'),(20035,'Permanent','Barangay 5','Sariaya','Quezon');

REPLACE INTO employee_emergency_contacts (employee_id, contact_name, relationship, contact_number) VALUES
  (20031,'Esteban Natividad','Spouse','09184000031'),
  (20032,'Macario Benigno','Father','09184000032'),
  (20033,'Prudencio Lacsamana','Father','09184000033'),
  (20034,'Isidro Espiritu','Father','09184000034'),
  (20035,'Evaristo Bitangcol','Father','09184000035');

REPLACE INTO employee_real_properties (employee_id, description, kind, acquisition_cost) VALUES
  (20031,'Residential House and Lot','Building and Land',3100000.00),
  (20032,'Residential House and Lot','Building and Land',2300000.00),
  (20033,'Residential House and Lot','Building and Land',1800000.00),
  (20034,'Residential House and Lot','Building and Land',1700000.00),
  (20035,'Residential House and Lot','Building and Land',1450000.00);

REPLACE INTO employee_personal_properties (employee_id, description, acquisition_cost) VALUES
  (20031,'Personal Vehicle and Savings',350000.00),(20032,'Personal Savings',210000.00),
  (20033,'Personal Savings',155000.00),(20034,'Personal Savings',130000.00),
  (20035,'Personal Savings',85000.00);

REPLACE INTO employee_liabilities (employee_id, nature_of_liability, creditor_name, outstanding_balance) VALUES
  (20031,'Housing Loan','BDO Unibank',1200000.00),(20032,'Personal Loan','Bank',80000.00),
  (20033,'Personal Loan','Bank',55000.00),(20034,'Personal Loan','Bank',42000.00),
  (20035,'Personal Loan','Bank',18000.00);

REPLACE INTO employee_references (employee_id, reference_name, reference_address, reference_telephone) VALUES
  (20031,'Ms. Felicitas Quezon','Lucena City','02-8456-7801'),
  (20032,'Mr. Bernardo Santos','Quezon City','02-8456-7802'),
  (20033,'Prof. Angelita Rojas','Candelaria, Quezon','02-8456-7803'),
  (20034,'Mr. Hernan Dela Cruz','Lucena City','02-8456-7804'),
  (20035,'Ms. Rosario Flores','Sariaya, Quezon','02-8456-7805');

-- Evaluations – Marketing (all pending) -------------
REPLACE INTO evaluations
  (evaluation_id, employee_id, template_id, evaluation_type, evaluation_period_start, evaluation_period_end, assigned_by, assigned_at, status, current_position, months_in_position, kra_subtotal, behavior_average, total_score, performance_level)
VALUES
  (2031,20031,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending HR Consolidation','Marketing Manager I',72,3.75,3.75,3.75,'Outstanding'),
  (2032,20032,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Supervisor','Marketing Supervisor I',57,3.25,3.13,3.23,'Exceeds Expectations'),
  (2033,20033,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Self-Rating','Marketing Staff I',42,2.75,2.75,2.75,'Exceeds Expectations'),
  (2034,20034,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Manager','Marketing Staff II',27,3.00,3.00,3.00,'Exceeds Expectations'),
  (2035,20035,2,'Initial','2025-06-01','2025-11-30',1,NOW(),'Pending Supervisor','Marketing Staff on Probation',5,3.15,3.25,3.17,'Exceeds Expectations');

REPLACE INTO evaluation_scores (evaluation_id, criterion_id, score_value, weighted_score) VALUES
  (2031,1,3.50,0.88),(2031,2,4.00,1.00),(2031,3,3.50,0.88),(2031,4,4.00,1.00),
  (2031,5,3.50,3.50),(2031,6,4.00,4.00),(2031,7,3.50,3.50),(2031,8,4.00,4.00),
  (2032,1,3.00,0.75),(2032,2,3.50,0.88),(2032,3,3.00,0.75),(2032,4,3.50,0.88),
  (2032,5,3.00,3.00),(2032,6,3.50,3.50),(2032,7,3.00,3.00),(2032,8,3.00,3.00),
  (2033,1,2.50,0.63),(2033,2,3.00,0.75),(2033,3,3.00,0.75),(2033,4,2.50,0.63),
  (2033,5,3.00,3.00),(2033,6,2.50,2.50),(2033,7,3.00,3.00),(2033,8,2.50,2.50),
  (2034,1,3.00,0.75),(2034,2,3.00,0.75),(2034,3,3.00,0.75),(2034,4,3.00,0.75),
  (2034,5,3.00,3.00),(2034,6,3.00,3.00),(2034,7,3.00,3.00),(2034,8,3.00,3.00),
  -- Probationary / Initial evaluation (Template 2 – criteria 9-13)
  (2035,9,3.00,1.05),(2035,10,3.00,1.05),(2035,11,3.50,1.05),(2035,12,3.50,3.50),(2035,13,3.00,3.00);

-- Career Movements – Marketing (all Pending) --------
REPLACE INTO career_movements
  (movement_id, employee_id, movement_type, previous_position, new_position,
   previous_branch_id, new_branch_id, effective_date, reason, logged_by, approval_status, is_applied)
VALUES
  (2031,20032,'Promotion','Marketing Supervisor I','Marketing Supervisor II',102,102,'2025-11-01',
   'Led successful product launch campaigns with measurable ROI improvements.',1,'Pending',0),
  (2032,20033,'Role Change','Marketing Staff I','Marketing Staff II',102,102,'2025-10-01',
   'Assumed additional responsibility for social media management and analytics.',1,'Pending',0);

-- =====================================================
-- OPERATIONS DEPARTMENT
-- =====================================================

-- Employees (demo) -----------------------------------
REPLACE INTO employees
  (employee_id, employee_code, first_name, last_name, middle_name,
   hire_date, date_of_birth, place_of_birth, gender, civil_status,
   job_title_id, job_title, department_id, rank_category_id, branch_id,
   employment_status, employment_type, profile_picture)
VALUES
  (20041,'DEMO-OPS-001','Alfredo','Macapagal','Dimaculangan','2017-03-15','1980-11-04','Lucena City, Quezon','Male','Married',1000,'VP for Operations',11,1,10,'Regular','Full-time','avatar_m.jpg'),
  (20042,'DEMO-OPS-002','Belen','Guzman','Espino','2018-08-06','1984-07-22','Tayabas City, Quezon','Female','Married',1001,'Regional Manager I',11,3,1,'Regular','Full-time','avatar_f.jpg'),
  (20043,'DEMO-OPS-003','Carlos','Macaraeg','Agoncillo','2019-11-12','1987-03-17','Candelaria, Quezon','Male','Single',1004,'Area Coordinator I',11,4,2,'Regular','Full-time','avatar_m.jpg'),
  (20044,'DEMO-OPS-004','Diana','Panganiban','Quizon','2021-02-08','1992-09-30','Sariaya, Quezon','Female','Single',1007,'Focal Person I',11,4,3,'Regular','Full-time','avatar_f.jpg'),
  (20045,'DEMO-OPS-005','Eduardo','Crisostomo','Batungbakal','2022-05-23','1994-06-14','Lucena City, Quezon','Male','Married',1013,'Branch Staff I',11,5,4,'Regular','Full-time','avatar_m.jpg'),
  (20046,'DEMO-OPS-006','Florencia','Magbanua','Ilustre','2022-09-01','1995-08-20','Pagbilao, Quezon','Female','Single',1014,'Branch Staff II',11,5,5,'Regular','Full-time','avatar_f.jpg'),
  (20047,'DEMO-OPS-007','Gerry','Padua','Natividad','2023-11-14','1997-02-07','Lucena City, Quezon','Male','Single',1015,'Branch Staff III',11,5,6,'Regular','Full-time','avatar_m.jpg'),
  (20048,'DEMO-OPS-008','Hazel','Ortega','Lacaba','2024-07-01','2000-04-25','Tayabas City, Quezon','Female','Single',1012,'Branch Staff on Probation',11,5,7,'Probationary','Full-time','avatar_f.jpg');

REPLACE INTO employee_contacts (employee_id, personal_email, mobile_number, telephone_number) VALUES
  (20041,'alfredo.macapagal@example.com','09175000041','888-20041'),
  (20042,'belen.guzman@example.com','09175000042','888-20042'),
  (20043,'carlos.macaraeg@example.com','09175000043','888-20043'),
  (20044,'diana.panganiban@example.com','09175000044','888-20044'),
  (20045,'eduardo.crisostomo@example.com','09175000045','888-20045'),
  (20046,'florencia.magbanua@example.com','09175000046','888-20046'),
  (20047,'gerry.padua@example.com','09175000047','888-20047'),
  (20048,'hazel.ortega@example.com','09175000048','888-20048');

REPLACE INTO employee_details (employee_id, height_m, weight_kg, blood_type, citizenship) VALUES
  (20041,1.73,78.0,'O+','Filipino'),(20042,1.62,60.0,'A+','Filipino'),
  (20043,1.75,72.0,'B+','Filipino'),(20044,1.59,54.0,'AB+','Filipino'),
  (20045,1.71,70.0,'O-','Filipino'),(20046,1.60,55.0,'A-','Filipino'),
  (20047,1.72,69.0,'B+','Filipino'),(20048,1.58,52.0,'A+','Filipino');

REPLACE INTO employee_family (employee_id, member_type, surname, first_name, middle_name, occupation) VALUES
  (20041,'Father','Macapagal','Augusto','Dimaculangan','Retired'),
  (20041,'Mother','Macapagal','Concordia','Reyes','Homemaker'),
  (20041,'Spouse','Reyes','Veronica','Santos','Teacher'),
  (20042,'Father','Guzman','Rodrigo','Espino','Retired'),
  (20042,'Mother','Guzman','Norma','Villanueva','Homemaker'),
  (20042,'Spouse','Soriano','Melchor','Cruz','Engineer'),
  (20043,'Father','Macaraeg','Ernesto','Agoncillo','Retired'),
  (20043,'Mother','Macaraeg','Leonora','Perez','Homemaker'),
  (20044,'Father','Panganiban','Gerardo','Quizon','Retired'),
  (20044,'Mother','Panganiban','Rosita','Torres','Homemaker'),
  (20045,'Father','Crisostomo','Narciso','Batungbakal','Retired'),
  (20045,'Mother','Crisostomo','Milagros','Gomez','Homemaker'),
  (20045,'Spouse','Santos','Annaliza','Reyes','Nurse'),
  (20046,'Father','Magbanua','Honorio','Ilustre','Retired'),
  (20046,'Mother','Magbanua','Liwayway','Castillo','Homemaker'),
  (20047,'Father','Padua','Adriano','Natividad','Retired'),
  (20047,'Mother','Padua','Consuelo','Soriano','Homemaker'),
  (20048,'Father','Ortega','Crispin','Lacaba','Retired'),
  (20048,'Mother','Ortega','Remedios','Santos','Homemaker');

REPLACE INTO employee_education (employee_id, education_level, school_name, degree_course, year_graduated) VALUES
  (20041,'Graduate Studies','Ateneo de Manila University','MBA','2005'),
  (20042,'College','University of the Philippines','BS Business Administration','2006'),
  (20043,'College','De La Salle University','BS Management','2009'),
  (20044,'College','Far Eastern University','BS Business Administration','2014'),
  (20045,'College','Polytechnic University of the Philippines','BS Management','2016'),
  (20046,'College','Southern Luzon State University','BS Business Administration','2017'),
  (20047,'College','Mapua University','BS Management','2019'),
  (20048,'College','Pamantasan ng Lungsod ng Maynila','AB Communication','2022');

REPLACE INTO employee_work_experience (employee_id, date_from, date_to, job_title, company_name, monthly_salary) VALUES
  (20041,'2005-07-01','2017-03-14','Operations Manager','Prime Logistics Co.',55000.00),
  (20042,'2006-08-01','2018-08-05','Regional Supervisor','BPO Solutions Inc.',40000.00),
  (20043,'2009-06-01','2019-11-11','Area Coordinator','United Services Group',33000.00),
  (20044,'2014-07-01','2021-02-07','Branch Officer','Secure Tech Philippines',26000.00),
  (20045,'2016-07-01','2022-05-22','Branch Staff','Pacific Marketing Group',22000.00),
  (20046,'2017-07-01','2022-08-31','Branch Associate','Summit Property Management',20000.00),
  (20047,'2019-06-01','2023-11-13','Operations Associate','Global Retail Corp.',19000.00),
  (20048,'2022-07-01','2024-06-30','Trainee Staff','Metro Finance and Accounting',16000.00);

REPLACE INTO employee_trainings (employee_id, training_title, conducted_by, no_of_hours) VALUES
  (20041,'Operations Management Excellence','Corporate Training Dept',40.0),
  (20042,'Regional Leadership Program','Corporate Training Dept',24.0),
  (20043,'Area Coordination and Compliance','Corporate Training Dept',24.0),
  (20044,'Branch Operations Fundamentals','Corporate Training Dept',16.0),
  (20045,'Customer Service Excellence','Corporate Training Dept',16.0),
  (20046,'Branch Service Standards','Corporate Training Dept',16.0),
  (20047,'Occupational Safety and Health','Corporate Training Dept',16.0),
  (20048,'New Employee Orientation','Corporate Training Dept',8.0);

REPLACE INTO employee_disclosures (employee_id, is_related_to_company, has_admin_offense, has_criminal_charge) VALUES
  (20041,0,0,0),(20042,0,0,0),(20043,0,0,0),(20044,0,0,0),
  (20045,0,0,0),(20046,0,0,0),(20047,0,0,0),(20048,0,0,0);

REPLACE INTO employee_government_ids (employee_id, sss_number, philhealth_number, pagibig_number, tin_number) VALUES
  (20041,'20-1000041-1','20-100004101-1','2001-0041-0041','200-100-041-000'),
  (20042,'20-1000042-2','20-100004202-2','2001-0042-0042','200-100-042-000'),
  (20043,'20-1000043-3','20-100004303-3','2001-0043-0043','200-100-043-000'),
  (20044,'20-1000044-4','20-100004404-4','2001-0044-0044','200-100-044-000'),
  (20045,'20-1000045-5','20-100004505-5','2001-0045-0045','200-100-045-000'),
  (20046,'20-1000046-6','20-100004606-6','2001-0046-0046','200-100-046-000'),
  (20047,'20-1000047-7','20-100004707-7','2001-0047-0047','200-100-047-000'),
  (20048,'20-1000048-8','20-100004808-8','2001-0048-0048','200-100-048-000');

REPLACE INTO employee_addresses (employee_id, address_type, barangay, city, province) VALUES
  (20041,'Residential','Poblacion','Tayabas City','Quezon'),(20041,'Permanent','Poblacion','Tayabas City','Quezon'),
  (20042,'Residential','Barangay 3','Lucena City','Quezon'),(20042,'Permanent','Barangay 3','Lucena City','Quezon'),
  (20043,'Residential','Barangay 7','Candelaria','Quezon'),(20043,'Permanent','Barangay 7','Candelaria','Quezon'),
  (20044,'Residential','Barangay 4','Sariaya','Quezon'),(20044,'Permanent','Barangay 4','Sariaya','Quezon'),
  (20045,'Residential','Barangay 8','Lucena City','Quezon'),(20045,'Permanent','Barangay 8','Lucena City','Quezon'),
  (20046,'Residential','Barangay 6','Pagbilao','Quezon'),(20046,'Permanent','Barangay 6','Pagbilao','Quezon'),
  (20047,'Residential','Barangay 5','Lucena City','Quezon'),(20047,'Permanent','Barangay 5','Lucena City','Quezon'),
  (20048,'Residential','Barangay 2','Tayabas City','Quezon'),(20048,'Permanent','Barangay 2','Tayabas City','Quezon');

REPLACE INTO employee_emergency_contacts (employee_id, contact_name, relationship, contact_number) VALUES
  (20041,'Veronica Reyes','Spouse','09185000041'),
  (20042,'Melchor Soriano','Spouse','09185000042'),
  (20043,'Ernesto Macaraeg','Father','09185000043'),
  (20044,'Gerardo Panganiban','Father','09185000044'),
  (20045,'Annaliza Santos','Spouse','09185000045'),
  (20046,'Honorio Magbanua','Father','09185000046'),
  (20047,'Adriano Padua','Father','09185000047'),
  (20048,'Crispin Ortega','Father','09185000048');

REPLACE INTO employee_real_properties (employee_id, description, kind, acquisition_cost) VALUES
  (20041,'Residential House and Lot','Building and Land',3800000.00),
  (20042,'Residential House and Lot','Building and Land',2700000.00),
  (20043,'Residential House and Lot','Building and Land',2300000.00),
  (20044,'Residential House and Lot','Building and Land',1900000.00),
  (20045,'Residential House and Lot','Building and Land',1800000.00),
  (20046,'Residential House and Lot','Building and Land',1650000.00),
  (20047,'Residential House and Lot','Building and Land',1550000.00),
  (20048,'Residential House and Lot','Building and Land',1350000.00);

REPLACE INTO employee_personal_properties (employee_id, description, acquisition_cost) VALUES
  (20041,'Personal Vehicle and Savings',520000.00),(20042,'Personal Vehicle and Savings',310000.00),
  (20043,'Personal Savings',260000.00),(20044,'Personal Savings',180000.00),
  (20045,'Personal Savings',170000.00),(20046,'Personal Savings',140000.00),
  (20047,'Personal Savings',120000.00),(20048,'Personal Savings',75000.00);

REPLACE INTO employee_liabilities (employee_id, nature_of_liability, creditor_name, outstanding_balance) VALUES
  (20041,'Housing Loan','BDO Unibank',1500000.00),(20042,'Housing Loan','Metrobank',950000.00),
  (20043,'Personal Loan','Bank',110000.00),(20044,'Personal Loan','Bank',68000.00),
  (20045,'Personal Loan','Bank',60000.00),(20046,'Personal Loan','Bank',45000.00),
  (20047,'Personal Loan','Bank',38000.00),(20048,'Personal Loan','Bank',15000.00);

REPLACE INTO employee_references (employee_id, reference_name, reference_address, reference_telephone) VALUES
  (20041,'Engr. Tomas Dela Cruz','Lucena City','02-8567-8901'),
  (20042,'Ms. Anastacia Reyes','Quezon City','02-8567-8902'),
  (20043,'Mr. Herminio Salcedo','Candelaria, Quezon','02-8567-8903'),
  (20044,'Prof. Loreto Santos','Sariaya, Quezon','02-8567-8904'),
  (20045,'Mr. Macario Villanueva','Lucena City','02-8567-8905'),
  (20046,'Ms. Felipa Cruz','Pagbilao, Quezon','02-8567-8906'),
  (20047,'Mr. Ambrosio Torres','Lucena City','02-8567-8907'),
  (20048,'Ms. Eulalia Gomez','Tayabas City, Quezon','02-8567-8908');

-- Evaluations – Operations (all pending) ------------
REPLACE INTO evaluations
  (evaluation_id, employee_id, template_id, evaluation_type, evaluation_period_start, evaluation_period_end, assigned_by, assigned_at, status, current_position, months_in_position, kra_subtotal, behavior_average, total_score, performance_level)
VALUES
  (2041,20041,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Manager','VP for Operations',96,3.88,3.88,3.88,'Outstanding'),
  (2042,20042,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending HR Consolidation','Regional Manager I',82,3.50,3.38,3.48,'Exceeds Expectations'),
  (2043,20043,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Supervisor','Area Coordinator I',68,3.13,3.13,3.13,'Exceeds Expectations'),
  (2044,20044,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Self-Rating','Focal Person I',53,3.00,3.00,3.00,'Exceeds Expectations'),
  (2045,20045,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Supervisor','Branch Staff I',38,2.75,2.75,2.75,'Exceeds Expectations'),
  (2046,20046,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending HR Consolidation','Branch Staff II',34,2.75,2.88,2.78,'Exceeds Expectations'),
  (2047,20047,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Manager','Branch Staff III',20,3.00,3.00,3.00,'Exceeds Expectations'),
  (2048,20048,2,'Initial','2025-07-01','2025-12-31',1,NOW(),'Pending Supervisor','Branch Staff on Probation',5,2.83,3.00,2.86,'Exceeds Expectations');

REPLACE INTO evaluation_scores (evaluation_id, criterion_id, score_value, weighted_score) VALUES
  (2041,1,4.00,1.00),(2041,2,4.00,1.00),(2041,3,3.50,0.88),(2041,4,4.00,1.00),
  (2041,5,4.00,4.00),(2041,6,3.50,3.50),(2041,7,4.00,4.00),(2041,8,4.00,4.00),
  (2042,1,3.50,0.88),(2042,2,3.50,0.88),(2042,3,3.50,0.88),(2042,4,3.50,0.88),
  (2042,5,3.50,3.50),(2042,6,3.50,3.50),(2042,7,3.50,3.50),(2042,8,3.00,3.00),
  (2043,1,3.00,0.75),(2043,2,3.50,0.88),(2043,3,3.00,0.75),(2043,4,3.00,0.75),
  (2043,5,3.00,3.00),(2043,6,3.00,3.00),(2043,7,3.50,3.50),(2043,8,3.00,3.00),
  (2044,1,3.00,0.75),(2044,2,3.00,0.75),(2044,3,3.00,0.75),(2044,4,3.00,0.75),
  (2044,5,3.00,3.00),(2044,6,3.00,3.00),(2044,7,3.00,3.00),(2044,8,3.00,3.00),
  (2045,1,2.50,0.63),(2045,2,3.00,0.75),(2045,3,2.50,0.63),(2045,4,3.00,0.75),
  (2045,5,2.50,2.50),(2045,6,3.00,3.00),(2045,7,3.00,3.00),(2045,8,2.50,2.50),
  (2046,1,3.00,0.75),(2046,2,2.50,0.63),(2046,3,3.00,0.75),(2046,4,2.50,0.63),
  (2046,5,3.00,3.00),(2046,6,2.50,2.50),(2046,7,3.00,3.00),(2046,8,3.00,3.00),
  (2047,1,3.00,0.75),(2047,2,3.00,0.75),(2047,3,3.00,0.75),(2047,4,3.00,0.75),
  (2047,5,3.00,3.00),(2047,6,3.00,3.00),(2047,7,3.00,3.00),(2047,8,3.00,3.00),
  -- Probationary / Initial evaluation (Template 2 – criteria 9-13)
  (2048,9,3.00,1.05),(2048,10,2.50,0.88),(2048,11,3.00,0.90),(2048,12,3.00,3.00),(2048,13,3.00,3.00);

-- Career Movements – Operations (all Pending) -------
REPLACE INTO career_movements
  (movement_id, employee_id, movement_type, previous_position, new_position,
   previous_branch_id, new_branch_id, effective_date, reason, logged_by, approval_status, is_applied)
VALUES
  (2041,20042,'Promotion','Regional Manager I','Regional Manager II',1,1,'2025-10-01',
   'Exceptional performance in regional expansion and branch profitability.',1,'Pending',0),
  (2042,20043,'Promotion','Area Coordinator I','Area Coordinator II',2,2,'2025-10-15',
   'Consistently met operational targets with commendable leadership.',1,'Pending',0),
  (2043,20045,'Transfer','Branch Staff I','Branch Staff I',4,8,'2025-09-01',
   'Transfer requested to be closer to residence and to address branch staffing needs.',1,'Pending',0),
  (2044,20048,'Regularization','Branch Staff on Probation','Branch Staff I',7,7,'2026-01-01',
   'Successfully completed all probationary milestones.',1,'Pending',0);

-- =====================================================
-- PURCHASING DEPARTMENT
-- =====================================================

-- Employees (demo) -----------------------------------
REPLACE INTO employees
  (employee_id, employee_code, first_name, last_name, middle_name,
   hire_date, date_of_birth, place_of_birth, gender, civil_status,
   job_title_id, job_title, department_id, rank_category_id, branch_id,
   employment_status, employment_type, profile_picture)
VALUES
  (20051,'DEMO-PUR-001','Nilda','Corpuz','Banaag','2020-02-17','1988-06-28','Lucena City, Quezon','Female','Married',1200,'Purchasing Supervisor I',12,4,23,'Regular','Full-time','avatar_f.jpg'),
  (20052,'DEMO-PUR-002','Oscar','Domingo','Maceda','2021-09-13','1991-04-12','Tayabas City, Quezon','Male','Single',1202,'Purchasing Staff I',12,5,24,'Regular','Full-time','avatar_m.jpg'),
  (20053,'DEMO-PUR-003','Perla','Enriquez','Sison','2023-05-08','1996-10-01','Candelaria, Quezon','Female','Single',1202,'Purchasing Staff I',12,5,25,'Regular','Full-time','avatar_f.jpg'),
  (20054,'DEMO-PUR-004','Quirino','Flores','Batac','2024-11-04','1998-01-19','Lucena City, Quezon','Male','Single',1201,'Purchasing Supervisor on Training',12,4,26,'Trainee','Full-time','avatar_m.jpg');

REPLACE INTO employee_contacts (employee_id, personal_email, mobile_number, telephone_number) VALUES
  (20051,'nilda.corpuz@example.com','09176000051','888-20051'),
  (20052,'oscar.domingo@example.com','09176000052','888-20052'),
  (20053,'perla.enriquez@example.com','09176000053','888-20053'),
  (20054,'quirino.flores@example.com','09176000054','888-20054');

REPLACE INTO employee_details (employee_id, height_m, weight_kg, blood_type, citizenship) VALUES
  (20051,1.62,59.0,'A+','Filipino'),(20052,1.73,71.0,'O+','Filipino'),
  (20053,1.59,53.0,'B+','Filipino'),(20054,1.72,70.0,'AB+','Filipino');

REPLACE INTO employee_family (employee_id, member_type, surname, first_name, middle_name, occupation) VALUES
  (20051,'Father','Corpuz','Marcelo','Banaag','Retired'),
  (20051,'Mother','Corpuz','Primitiva','Santos','Homemaker'),
  (20051,'Spouse','Lim','Julius','Cruz','Businessman'),
  (20052,'Father','Domingo','Calixto','Maceda','Retired'),
  (20052,'Mother','Domingo','Adoracion','Perez','Homemaker'),
  (20053,'Father','Enriquez','Exequiel','Sison','Retired'),
  (20053,'Mother','Enriquez','Elvira','Gomez','Homemaker'),
  (20054,'Father','Flores','Hermenegildo','Batac','Retired'),
  (20054,'Mother','Flores','Felicidad','Torres','Homemaker');

REPLACE INTO employee_education (employee_id, education_level, school_name, degree_course, year_graduated) VALUES
  (20051,'College','University of the Philippines','BS Business Administration','2010'),
  (20052,'College','Pamantasan ng Lungsod ng Maynila','BS Management','2013'),
  (20053,'College','De La Salle University','BS Business Administration','2018'),
  (20054,'College','Far Eastern University','BS Management','2020');

REPLACE INTO employee_work_experience (employee_id, date_from, date_to, job_title, company_name, monthly_salary) VALUES
  (20051,'2010-07-01','2020-02-16','Purchasing Officer','Quezon Cooperative',36000.00),
  (20052,'2013-08-01','2021-09-12','Procurement Staff','Metro Supplies Inc.',24000.00),
  (20053,'2018-07-01','2023-05-07','Purchasing Associate','National Procurement Hub',20000.00),
  (20054,'2020-07-01','2024-11-03','Procurement Trainee','Provincial Logistics Inc.',18000.00);

REPLACE INTO employee_trainings (employee_id, training_title, conducted_by, no_of_hours) VALUES
  (20051,'Supply Chain and Procurement Management','Corporate Training Dept',24.0),
  (20052,'ISO 9001:2015 Quality Management','Corporate Training Dept',16.0),
  (20053,'Procurement Fundamentals','Corporate Training Dept',16.0),
  (20054,'Strategic Sourcing and Vendor Management','Corporate Training Dept',16.0);

REPLACE INTO employee_disclosures (employee_id, is_related_to_company, has_admin_offense, has_criminal_charge) VALUES
  (20051,0,0,0),(20052,0,0,0),(20053,0,0,0),(20054,0,0,0);

REPLACE INTO employee_government_ids (employee_id, sss_number, philhealth_number, pagibig_number, tin_number) VALUES
  (20051,'20-1000051-1','20-100005101-1','2001-0051-0051','200-100-051-000'),
  (20052,'20-1000052-2','20-100005202-2','2001-0052-0052','200-100-052-000'),
  (20053,'20-1000053-3','20-100005303-3','2001-0053-0053','200-100-053-000'),
  (20054,'20-1000054-4','20-100005404-4','2001-0054-0054','200-100-054-000');

REPLACE INTO employee_addresses (employee_id, address_type, barangay, city, province) VALUES
  (20051,'Residential','Barangay 3','Tayabas City','Quezon'),(20051,'Permanent','Barangay 3','Tayabas City','Quezon'),
  (20052,'Residential','Barangay 11','Lucena City','Quezon'),(20052,'Permanent','Barangay 11','Lucena City','Quezon'),
  (20053,'Residential','Barangay 5','Candelaria','Quezon'),(20053,'Permanent','Barangay 5','Candelaria','Quezon'),
  (20054,'Residential','Barangay 9','Lucena City','Quezon'),(20054,'Permanent','Barangay 9','Lucena City','Quezon');

REPLACE INTO employee_emergency_contacts (employee_id, contact_name, relationship, contact_number) VALUES
  (20051,'Julius Lim','Spouse','09186000051'),
  (20052,'Calixto Domingo','Father','09186000052'),
  (20053,'Exequiel Enriquez','Father','09186000053'),
  (20054,'Hermenegildo Flores','Father','09186000054');

REPLACE INTO employee_real_properties (employee_id, description, kind, acquisition_cost) VALUES
  (20051,'Residential House and Lot','Building and Land',2600000.00),
  (20052,'Residential House and Lot','Building and Land',1900000.00),
  (20053,'Residential House and Lot','Building and Land',1700000.00),
  (20054,'Residential House and Lot','Building and Land',1500000.00);

REPLACE INTO employee_personal_properties (employee_id, description, acquisition_cost) VALUES
  (20051,'Personal Vehicle and Savings',300000.00),(20052,'Personal Savings',170000.00),
  (20053,'Personal Savings',140000.00),(20054,'Personal Savings',100000.00);

REPLACE INTO employee_liabilities (employee_id, nature_of_liability, creditor_name, outstanding_balance) VALUES
  (20051,'Housing Loan','BDO Unibank',900000.00),(20052,'Personal Loan','Bank',65000.00),
  (20053,'Personal Loan','Bank',45000.00),(20054,'Personal Loan','Bank',30000.00);

REPLACE INTO employee_references (employee_id, reference_name, reference_address, reference_telephone) VALUES
  (20051,'Mr. Eustaquio Santos','Lucena City','02-8678-9001'),
  (20052,'Prof. Honesto Cruz','Quezon City','02-8678-9002'),
  (20053,'Ms. Basilisa Reyes','Candelaria, Quezon','02-8678-9003'),
  (20054,'Mr. Apolinario Gomez','Lucena City','02-8678-9004');

-- Evaluations – Purchasing (all pending) ------------
REPLACE INTO evaluations
  (evaluation_id, employee_id, template_id, evaluation_type, evaluation_period_start, evaluation_period_end, assigned_by, assigned_at, status, current_position, months_in_position, kra_subtotal, behavior_average, total_score, performance_level)
VALUES
  (2051,20051,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending HR Consolidation','Purchasing Supervisor I',60,3.38,3.38,3.38,'Exceeds Expectations'),
  (2052,20052,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Supervisor','Purchasing Staff I',46,3.00,3.00,3.00,'Exceeds Expectations'),
  (2053,20053,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Self-Rating','Purchasing Staff I',27,2.63,2.75,2.65,'Exceeds Expectations'),
  (2054,20054,2,'Initial','2024-11-01','2025-04-30',1,NOW(),'Pending Manager','Purchasing Supervisor on Training',8,3.15,3.00,3.12,'Exceeds Expectations');

REPLACE INTO evaluation_scores (evaluation_id, criterion_id, score_value, weighted_score) VALUES
  (2051,1,3.50,0.88),(2051,2,3.50,0.88),(2051,3,3.00,0.75),(2051,4,3.50,0.88),
  (2051,5,3.50,3.50),(2051,6,3.50,3.50),(2051,7,3.00,3.00),(2051,8,3.50,3.50),
  (2052,1,3.00,0.75),(2052,2,3.00,0.75),(2052,3,3.00,0.75),(2052,4,3.00,0.75),
  (2052,5,3.00,3.00),(2052,6,3.00,3.00),(2052,7,3.00,3.00),(2052,8,3.00,3.00),
  (2053,1,2.50,0.63),(2053,2,3.00,0.75),(2053,3,2.50,0.63),(2053,4,2.50,0.63),
  (2053,5,3.00,3.00),(2053,6,2.50,2.50),(2053,7,3.00,3.00),(2053,8,2.50,2.50),
  -- Probationary / Initial evaluation (Template 2 – criteria 9-13)
  (2054,9,3.00,1.05),(2054,10,3.00,1.05),(2054,11,3.50,1.05),(2054,12,3.00,3.00),(2054,13,3.00,3.00);

-- Career Movements – Purchasing (all Pending) -------
REPLACE INTO career_movements
  (movement_id, employee_id, movement_type, previous_position, new_position,
   previous_branch_id, new_branch_id, effective_date, reason, logged_by, approval_status, is_applied)
VALUES
  (2051,20052,'Promotion','Purchasing Staff I','Purchasing Supervisor on Training',102,102,'2025-10-01',
   'Strong performance in vendor negotiation and cost reduction initiatives.',1,'Pending',0);

-- =====================================================
-- COMPLIANCE DEPARTMENT
-- =====================================================

-- Employees (demo) -----------------------------------
REPLACE INTO employees
  (employee_id, employee_code, first_name, last_name, middle_name,
   hire_date, date_of_birth, place_of_birth, gender, civil_status,
   job_title_id, job_title, department_id, rank_category_id, branch_id,
   employment_status, employment_type, profile_picture)
VALUES
  (20061,'DEMO-COMP-001','Renato','Baluyot','Manalac','2019-01-07','1986-04-15','Lucena City, Quezon','Male','Married',400,'Compliance Supervisor I',4,4,27,'Regular','Full-time','avatar_m.jpg'),
  (20062,'DEMO-COMP-002','Salvacion','Chua','Laxamana','2020-06-22','1990-10-23','Tayabas City, Quezon','Female','Single',401,'Compliance Supervisor II',4,4,28,'Regular','Full-time','avatar_f.jpg'),
  (20063,'DEMO-COMP-003','Tomas','De Villa','Hernandez','2021-11-03','1993-07-08','Candelaria, Quezon','Male','Single',403,'Compliance Staff I',4,5,29,'Regular','Full-time','avatar_m.jpg'),
  (20064,'DEMO-COMP-004','Ursula','Evangelio','Magsino','2023-02-14','1996-01-30','Sariaya, Quezon','Female','Single',404,'Compliance Staff II',4,5,30,'Regular','Full-time','avatar_f.jpg'),
  (20065,'DEMO-COMP-005','Vicente','Ferrer','Tablante','2024-08-19','1998-09-12','Lucena City, Quezon','Male','Single',405,'Compliance Staff III',4,5,31,'Regular','Full-time','avatar_m.jpg');

REPLACE INTO employee_contacts (employee_id, personal_email, mobile_number, telephone_number) VALUES
  (20061,'renato.baluyot@example.com','09177000061','888-20061'),
  (20062,'salvacion.chua@example.com','09177000062','888-20062'),
  (20063,'tomas.devilla@example.com','09177000063','888-20063'),
  (20064,'ursula.evangelio@example.com','09177000064','888-20064'),
  (20065,'vicente.ferrer@example.com','09177000065','888-20065');

REPLACE INTO employee_details (employee_id, height_m, weight_kg, blood_type, citizenship) VALUES
  (20061,1.71,73.0,'O+','Filipino'),(20062,1.60,55.0,'A+','Filipino'),
  (20063,1.73,70.0,'B+','Filipino'),(20064,1.59,52.0,'AB+','Filipino'),
  (20065,1.72,71.0,'O-','Filipino');

REPLACE INTO employee_family (employee_id, member_type, surname, first_name, middle_name, occupation) VALUES
  (20061,'Father','Baluyot','Remigio','Manalac','Retired'),
  (20061,'Mother','Baluyot','Rosario','Perez','Homemaker'),
  (20061,'Spouse','Santos','Carmelita','Reyes','Teacher'),
  (20062,'Father','Chua','Cornelio','Laxamana','Retired'),
  (20062,'Mother','Chua','Natividad','Villanueva','Homemaker'),
  (20063,'Father','De Villa','Honesto','Hernandez','Retired'),
  (20063,'Mother','De Villa','Lorenza','Santos','Homemaker'),
  (20064,'Father','Evangelio','Faustino','Magsino','Retired'),
  (20064,'Mother','Evangelio','Lucila','Gomez','Homemaker'),
  (20065,'Father','Ferrer','Eleuterio','Tablante','Retired'),
  (20065,'Mother','Ferrer','Epifania','Cruz','Homemaker');

REPLACE INTO employee_education (employee_id, education_level, school_name, degree_course, year_graduated) VALUES
  (20061,'Graduate Studies','Ateneo de Manila University','LLB','2011'),
  (20062,'College','University of the Philippines','BS Legal Management','2012'),
  (20063,'College','De La Salle University','BS Business Administration','2015'),
  (20064,'College','Far Eastern University','BS Accountancy','2018'),
  (20065,'College','Mapua University','BS Management','2020');

REPLACE INTO employee_work_experience (employee_id, date_from, date_to, job_title, company_name, monthly_salary) VALUES
  (20061,'2011-07-01','2019-01-06','Compliance Officer','Quezon Cooperative Bank',40000.00),
  (20062,'2012-08-01','2020-06-21','Regulatory Affairs Officer','Regional Financial Corp.',32000.00),
  (20063,'2015-07-01','2021-11-02','Compliance Associate','National Compliance Services',24000.00),
  (20064,'2018-06-01','2023-02-13','Compliance Staff','Audit & Compliance Corp.',20000.00),
  (20065,'2020-07-01','2024-08-18','Compliance Trainee','Metro Regulatory Inc.',17000.00);

REPLACE INTO employee_trainings (employee_id, training_title, conducted_by, no_of_hours) VALUES
  (20061,'Regulatory Compliance and Risk Management','Corporate Training Dept',32.0),
  (20062,'Anti-Money Laundering (AML) Program','Corporate Training Dept',24.0),
  (20063,'Compliance Monitoring Techniques','Corporate Training Dept',16.0),
  (20064,'Professional Ethics in Workplace','Corporate Training Dept',16.0),
  (20065,'ISO 9001:2015 Quality Management','Corporate Training Dept',16.0);

REPLACE INTO employee_disclosures (employee_id, is_related_to_company, has_admin_offense, has_criminal_charge) VALUES
  (20061,0,0,0),(20062,0,0,0),(20063,0,0,0),(20064,0,0,0),(20065,0,0,0);

REPLACE INTO employee_government_ids (employee_id, sss_number, philhealth_number, pagibig_number, tin_number) VALUES
  (20061,'20-1000061-1','20-100006101-1','2001-0061-0061','200-100-061-000'),
  (20062,'20-1000062-2','20-100006202-2','2001-0062-0062','200-100-062-000'),
  (20063,'20-1000063-3','20-100006303-3','2001-0063-0063','200-100-063-000'),
  (20064,'20-1000064-4','20-100006404-4','2001-0064-0064','200-100-064-000'),
  (20065,'20-1000065-5','20-100006505-5','2001-0065-0065','200-100-065-000');

REPLACE INTO employee_addresses (employee_id, address_type, barangay, city, province) VALUES
  (20061,'Residential','Barangay 4','Tayabas City','Quezon'),(20061,'Permanent','Barangay 4','Tayabas City','Quezon'),
  (20062,'Residential','Barangay 12','Lucena City','Quezon'),(20062,'Permanent','Barangay 12','Lucena City','Quezon'),
  (20063,'Residential','Barangay 8','Candelaria','Quezon'),(20063,'Permanent','Barangay 8','Candelaria','Quezon'),
  (20064,'Residential','Barangay 3','Sariaya','Quezon'),(20064,'Permanent','Barangay 3','Sariaya','Quezon'),
  (20065,'Residential','Barangay 7','Lucena City','Quezon'),(20065,'Permanent','Barangay 7','Lucena City','Quezon');

REPLACE INTO employee_emergency_contacts (employee_id, contact_name, relationship, contact_number) VALUES
  (20061,'Carmelita Santos','Spouse','09187000061'),
  (20062,'Cornelio Chua','Father','09187000062'),
  (20063,'Honesto De Villa','Father','09187000063'),
  (20064,'Faustino Evangelio','Father','09187000064'),
  (20065,'Eleuterio Ferrer','Father','09187000065');

REPLACE INTO employee_real_properties (employee_id, description, kind, acquisition_cost) VALUES
  (20061,'Residential House and Lot','Building and Land',2800000.00),
  (20062,'Residential House and Lot','Building and Land',2100000.00),
  (20063,'Residential House and Lot','Building and Land',1850000.00),
  (20064,'Residential House and Lot','Building and Land',1600000.00),
  (20065,'Residential House and Lot','Building and Land',1450000.00);

REPLACE INTO employee_personal_properties (employee_id, description, acquisition_cost) VALUES
  (20061,'Personal Vehicle and Savings',370000.00),(20062,'Personal Savings',220000.00),
  (20063,'Personal Savings',165000.00),(20064,'Personal Savings',120000.00),
  (20065,'Personal Savings',90000.00);

REPLACE INTO employee_liabilities (employee_id, nature_of_liability, creditor_name, outstanding_balance) VALUES
  (20061,'Housing Loan','Metrobank',1000000.00),(20062,'Personal Loan','Bank',78000.00),
  (20063,'Personal Loan','Bank',58000.00),(20064,'Personal Loan','Bank',40000.00),
  (20065,'Personal Loan','Bank',25000.00);

REPLACE INTO employee_references (employee_id, reference_name, reference_address, reference_telephone) VALUES
  (20061,'Atty. Crisanto Dela Cruz','Lucena City','02-8789-0101'),
  (20062,'Prof. Telesforo Santos','Quezon City','02-8789-0102'),
  (20063,'Mr. Ambrocio Reyes','Candelaria, Quezon','02-8789-0103'),
  (20064,'Ms. Filomena Torres','Sariaya, Quezon','02-8789-0104'),
  (20065,'Mr. Isidoro Gomez','Lucena City','02-8789-0105');

-- Evaluations – Compliance (all pending) ------------
REPLACE INTO evaluations
  (evaluation_id, employee_id, template_id, evaluation_type, evaluation_period_start, evaluation_period_end, assigned_by, assigned_at, status, current_position, months_in_position, kra_subtotal, behavior_average, total_score, performance_level)
VALUES
  (2061,20061,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Manager','Compliance Supervisor I',72,3.75,3.63,3.73,'Outstanding'),
  (2062,20062,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending HR Consolidation','Compliance Supervisor II',55,3.25,3.13,3.23,'Exceeds Expectations'),
  (2063,20063,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Supervisor','Compliance Staff I',44,3.00,3.00,3.00,'Exceeds Expectations'),
  (2064,20064,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Self-Rating','Compliance Staff II',28,2.75,2.75,2.75,'Exceeds Expectations'),
  (2065,20065,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Supervisor','Compliance Staff III',11,2.75,2.75,2.75,'Exceeds Expectations');

REPLACE INTO evaluation_scores (evaluation_id, criterion_id, score_value, weighted_score) VALUES
  (2061,1,3.50,0.88),(2061,2,4.00,1.00),(2061,3,3.50,0.88),(2061,4,4.00,1.00),
  (2061,5,3.50,3.50),(2061,6,4.00,4.00),(2061,7,3.50,3.50),(2061,8,3.50,3.50),
  (2062,1,3.00,0.75),(2062,2,3.50,0.88),(2062,3,3.00,0.75),(2062,4,3.50,0.88),
  (2062,5,3.00,3.00),(2062,6,3.00,3.00),(2062,7,3.50,3.50),(2062,8,3.00,3.00),
  (2063,1,3.00,0.75),(2063,2,3.00,0.75),(2063,3,3.00,0.75),(2063,4,3.00,0.75),
  (2063,5,3.00,3.00),(2063,6,3.00,3.00),(2063,7,3.00,3.00),(2063,8,3.00,3.00),
  (2064,1,2.50,0.63),(2064,2,3.00,0.75),(2064,3,2.50,0.63),(2064,4,3.00,0.75),
  (2064,5,3.00,3.00),(2064,6,2.50,2.50),(2064,7,3.00,3.00),(2064,8,2.50,2.50),
  (2065,1,3.00,0.75),(2065,2,2.50,0.63),(2065,3,3.00,0.75),(2065,4,2.50,0.63),
  (2065,5,2.50,2.50),(2065,6,3.00,3.00),(2065,7,3.00,3.00),(2065,8,2.50,2.50);

-- Career Movements – Compliance (all Pending) -------
REPLACE INTO career_movements
  (movement_id, employee_id, movement_type, previous_position, new_position,
   previous_branch_id, new_branch_id, effective_date, reason, logged_by, approval_status, is_applied)
VALUES
  (2061,20062,'Promotion','Compliance Supervisor II','Compliance Supervisor III',102,102,'2025-11-01',
   'Exemplary performance in regulatory reporting and process documentation.',1,'Pending',0),
  (2062,20063,'Salary Adjustment','Compliance Staff I','Compliance Staff I',102,102,'2025-10-01',
   'Merit-based salary review following annual performance evaluation.',1,'Pending',0);

-- =====================================================
-- BUSINESS DEVELOPMENT DEPARTMENT
-- =====================================================

-- Employees (demo) -----------------------------------
REPLACE INTO employees
  (employee_id, employee_code, first_name, last_name, middle_name,
   hire_date, date_of_birth, place_of_birth, gender, civil_status,
   job_title_id, job_title, department_id, rank_category_id, branch_id,
   employment_status, employment_type, profile_picture)
VALUES
  (20071,'DEMO-BD-001','Wilhelmina','Gonzaga','Abad','2018-05-14','1985-08-07','Lucena City, Quezon','Female','Married',300,'Business Development Officer I',3,3,32,'Regular','Full-time','avatar_f.jpg'),
  (20072,'DEMO-BD-002','Xavier','Hidalgo','Brion','2020-12-01','1991-02-18','Tayabas City, Quezon','Male','Single',302,'Business Development Staff I',3,5,33,'Regular','Full-time','avatar_m.jpg'),
  (20073,'DEMO-BD-003','Yolanda','Ignacio','Catalan','2022-07-04','1994-11-26','Candelaria, Quezon','Female','Single',303,'Business Development Staff II',3,5,34,'Regular','Full-time','avatar_f.jpg'),
  (20074,'DEMO-BD-004','Zoilo','Jacinto','Dimzon','2024-02-19','1998-05-13','Lucena City, Quezon','Male','Single',301,'Business Development Staff on Training',3,5,35,'Trainee','Full-time','avatar_m.jpg');

REPLACE INTO employee_contacts (employee_id, personal_email, mobile_number, telephone_number) VALUES
  (20071,'wilhelmina.gonzaga@example.com','09178000071','888-20071'),
  (20072,'xavier.hidalgo@example.com','09178000072','888-20072'),
  (20073,'yolanda.ignacio@example.com','09178000073','888-20073'),
  (20074,'zoilo.jacinto@example.com','09178000074','888-20074');

REPLACE INTO employee_details (employee_id, height_m, weight_kg, blood_type, citizenship) VALUES
  (20071,1.63,58.0,'A+','Filipino'),(20072,1.75,72.0,'O+','Filipino'),
  (20073,1.59,53.0,'B+','Filipino'),(20074,1.72,69.0,'AB+','Filipino');

REPLACE INTO employee_family (employee_id, member_type, surname, first_name, middle_name, occupation) VALUES
  (20071,'Father','Gonzaga','Teodoro','Abad','Retired'),
  (20071,'Mother','Gonzaga','Resurreccion','Santos','Homemaker'),
  (20071,'Spouse','Reyes','Mariano','Cruz','Lawyer'),
  (20072,'Father','Hidalgo','Wenceslao','Brion','Retired'),
  (20072,'Mother','Hidalgo','Visitacion','Perez','Homemaker'),
  (20073,'Father','Ignacio','Alejo','Catalan','Retired'),
  (20073,'Mother','Ignacio','Bibiana','Gomez','Homemaker'),
  (20074,'Father','Jacinto','Epifanio','Dimzon','Retired'),
  (20074,'Mother','Jacinto','Consorcia','Torres','Homemaker');

REPLACE INTO employee_education (employee_id, education_level, school_name, degree_course, year_graduated) VALUES
  (20071,'Graduate Studies','Ateneo de Manila University','MBA','2010'),
  (20072,'College','University of the Philippines','BS Business Administration','2013'),
  (20073,'College','De La Salle University','AB Mass Communication','2016'),
  (20074,'College','Far Eastern University','BS Management','2020');

REPLACE INTO employee_work_experience (employee_id, date_from, date_to, job_title, company_name, monthly_salary) VALUES
  (20071,'2010-07-01','2018-05-13','Business Development Manager','Growth Ventures Inc.',44000.00),
  (20072,'2013-08-01','2020-11-30','Business Development Associate','Expansion Corp.',26000.00),
  (20073,'2016-07-01','2022-07-03','Business Analyst','Strategy Partners PH',22000.00),
  (20074,'2020-07-01','2024-02-18','BD Trainee','Market Expansion Inc.',17000.00);

REPLACE INTO employee_trainings (employee_id, training_title, conducted_by, no_of_hours) VALUES
  (20071,'Strategic Business Development','Corporate Training Dept',32.0),
  (20072,'Market Expansion Strategies','Corporate Training Dept',16.0),
  (20073,'Business Analysis Techniques','Corporate Training Dept',16.0),
  (20074,'Business Development Fundamentals','Corporate Training Dept',8.0);

REPLACE INTO employee_disclosures (employee_id, is_related_to_company, has_admin_offense, has_criminal_charge) VALUES
  (20071,0,0,0),(20072,0,0,0),(20073,0,0,0),(20074,0,0,0);

REPLACE INTO employee_government_ids (employee_id, sss_number, philhealth_number, pagibig_number, tin_number) VALUES
  (20071,'20-1000071-1','20-100007101-1','2001-0071-0071','200-100-071-000'),
  (20072,'20-1000072-2','20-100007202-2','2001-0072-0072','200-100-072-000'),
  (20073,'20-1000073-3','20-100007303-3','2001-0073-0073','200-100-073-000'),
  (20074,'20-1000074-4','20-100007404-4','2001-0074-0074','200-100-074-000');

REPLACE INTO employee_addresses (employee_id, address_type, barangay, city, province) VALUES
  (20071,'Residential','Barangay 6','Tayabas City','Quezon'),(20071,'Permanent','Barangay 6','Tayabas City','Quezon'),
  (20072,'Residential','Barangay 10','Lucena City','Quezon'),(20072,'Permanent','Barangay 10','Lucena City','Quezon'),
  (20073,'Residential','Barangay 9','Candelaria','Quezon'),(20073,'Permanent','Barangay 9','Candelaria','Quezon'),
  (20074,'Residential','Barangay 6','Lucena City','Quezon'),(20074,'Permanent','Barangay 6','Lucena City','Quezon');

REPLACE INTO employee_emergency_contacts (employee_id, contact_name, relationship, contact_number) VALUES
  (20071,'Mariano Reyes','Spouse','09188000071'),
  (20072,'Wenceslao Hidalgo','Father','09188000072'),
  (20073,'Alejo Ignacio','Father','09188000073'),
  (20074,'Epifanio Jacinto','Father','09188000074');

REPLACE INTO employee_real_properties (employee_id, description, kind, acquisition_cost) VALUES
  (20071,'Residential House and Lot','Building and Land',2900000.00),
  (20072,'Residential House and Lot','Building and Land',1950000.00),
  (20073,'Residential House and Lot','Building and Land',1750000.00),
  (20074,'Residential House and Lot','Building and Land',1400000.00);

REPLACE INTO employee_personal_properties (employee_id, description, acquisition_cost) VALUES
  (20071,'Personal Vehicle and Savings',380000.00),(20072,'Personal Savings',180000.00),
  (20073,'Personal Savings',145000.00),(20074,'Personal Savings',85000.00);

REPLACE INTO employee_liabilities (employee_id, nature_of_liability, creditor_name, outstanding_balance) VALUES
  (20071,'Housing Loan','BDO Unibank',1100000.00),(20072,'Personal Loan','Bank',60000.00),
  (20073,'Personal Loan','Bank',45000.00),(20074,'Personal Loan','Bank',20000.00);

REPLACE INTO employee_references (employee_id, reference_name, reference_address, reference_telephone) VALUES
  (20071,'Atty. Ireneo Santos','Lucena City','02-8890-1201'),
  (20072,'Mr. Cornelio Reyes','Quezon City','02-8890-1202'),
  (20073,'Ms. Bonifacia Cruz','Candelaria, Quezon','02-8890-1203'),
  (20074,'Mr. Emeterio Gomez','Lucena City','02-8890-1204');

-- Evaluations – Business Development (all pending) --
REPLACE INTO evaluations
  (evaluation_id, employee_id, template_id, evaluation_type, evaluation_period_start, evaluation_period_end, assigned_by, assigned_at, status, current_position, months_in_position, kra_subtotal, behavior_average, total_score, performance_level)
VALUES
  (2071,20071,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending HR Consolidation','Business Development Officer I',84,3.63,3.75,3.65,'Outstanding'),
  (2072,20072,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Supervisor','Business Development Staff I',54,3.00,3.00,3.00,'Exceeds Expectations'),
  (2073,20073,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Self-Rating','Business Development Staff II',36,2.75,2.75,2.75,'Exceeds Expectations'),
  (2074,20074,2,'Initial','2024-02-01','2024-07-31',1,NOW(),'Pending Manager','Business Development Staff on Training',17,3.15,3.25,3.17,'Exceeds Expectations');

REPLACE INTO evaluation_scores (evaluation_id, criterion_id, score_value, weighted_score) VALUES
  (2071,1,3.50,0.88),(2071,2,3.50,0.88),(2071,3,4.00,1.00),(2071,4,3.50,0.88),
  (2071,5,3.50,3.50),(2071,6,4.00,4.00),(2071,7,3.50,3.50),(2071,8,4.00,4.00),
  (2072,1,3.00,0.75),(2072,2,3.00,0.75),(2072,3,3.00,0.75),(2072,4,3.00,0.75),
  (2072,5,3.00,3.00),(2072,6,3.00,3.00),(2072,7,3.00,3.00),(2072,8,3.00,3.00),
  (2073,1,2.50,0.63),(2073,2,3.00,0.75),(2073,3,2.50,0.63),(2073,4,3.00,0.75),
  (2073,5,3.00,3.00),(2073,6,2.50,2.50),(2073,7,3.00,3.00),(2073,8,2.50,2.50),
  -- Probationary / Initial evaluation (Template 2 – criteria 9-13)
  (2074,9,3.00,1.05),(2074,10,3.00,1.05),(2074,11,3.50,1.05),(2074,12,3.00,3.00),(2074,13,3.50,3.50);

-- Career Movements – Business Development (Pending) -
REPLACE INTO career_movements
  (movement_id, employee_id, movement_type, previous_position, new_position,
   previous_branch_id, new_branch_id, effective_date, reason, logged_by, approval_status, is_applied)
VALUES
  (2071,20072,'Salary Adjustment','Business Development Staff I','Business Development Staff I',102,102,'2025-10-01',
   'Annual merit increase based on performance evaluation results.',1,'Pending',0);

-- =====================================================
-- AUDIT DEPARTMENT
-- =====================================================

-- Employees (demo) -----------------------------------
REPLACE INTO employees
  (employee_id, employee_code, first_name, last_name, middle_name,
   hire_date, date_of_birth, place_of_birth, gender, civil_status,
   job_title_id, job_title, department_id, rank_category_id, branch_id,
   employment_status, employment_type, profile_picture)
VALUES
  (20081,'DEMO-AUD-001','Arturo','Banaag','Castillo','2017-09-11','1983-12-01','Lucena City, Quezon','Male','Married',200,'Audit Manager I',2,3,36,'Regular','Full-time','avatar_m.jpg'),
  (20082,'DEMO-AUD-002','Belinda','Corazon','Estrada','2019-04-22','1987-06-14','Tayabas City, Quezon','Female','Single',203,'Audit Supervisor I',2,4,37,'Regular','Full-time','avatar_f.jpg'),
  (20083,'DEMO-AUD-003','Crispin','Dayrit','Flores','2020-10-05','1991-03-27','Candelaria, Quezon','Male','Single',207,'Auditor I',2,5,38,'Regular','Full-time','avatar_m.jpg'),
  (20084,'DEMO-AUD-004','Dolores','Espejo','Gomez','2022-01-18','1993-09-09','Sariaya, Quezon','Female','Single',208,'Auditor II',2,5,39,'Regular','Full-time','avatar_f.jpg'),
  (20085,'DEMO-AUD-005','Edmundo','Flores','Hernandez','2023-07-03','1995-04-22','Lucena City, Quezon','Male','Single',209,'Auditor III',2,5,40,'Regular','Full-time','avatar_m.jpg'),
  (20086,'DEMO-AUD-006','Fedelina','Guzman','Ilustre','2024-09-16','1998-11-07','Pagbilao, Quezon','Female','Single',206,'Auditor on Probation',2,5,41,'Probationary','Full-time','avatar_f.jpg');

REPLACE INTO employee_contacts (employee_id, personal_email, mobile_number, telephone_number) VALUES
  (20081,'arturo.banaag@example.com','09179000081','888-20081'),
  (20082,'belinda.corazon@example.com','09179000082','888-20082'),
  (20083,'crispin.dayrit@example.com','09179000083','888-20083'),
  (20084,'dolores.espejo@example.com','09179000084','888-20084'),
  (20085,'edmundo.flores@example.com','09179000085','888-20085'),
  (20086,'fedelina.guzman@example.com','09179000086','888-20086');

REPLACE INTO employee_details (employee_id, height_m, weight_kg, blood_type, citizenship) VALUES
  (20081,1.71,74.0,'O+','Filipino'),(20082,1.61,57.0,'A+','Filipino'),
  (20083,1.73,71.0,'B+','Filipino'),(20084,1.59,53.0,'AB+','Filipino'),
  (20085,1.72,70.0,'O-','Filipino'),(20086,1.60,54.0,'A-','Filipino');

REPLACE INTO employee_family (employee_id, member_type, surname, first_name, middle_name, occupation) VALUES
  (20081,'Father','Banaag','Felixberto','Castillo','Retired'),
  (20081,'Mother','Banaag','Natividad','Reyes','Homemaker'),
  (20081,'Spouse','Perez','Magdalena','Santos','Accountant'),
  (20082,'Father','Corazon','Deogracias','Estrada','Retired'),
  (20082,'Mother','Corazon','Esperanza','Villanueva','Homemaker'),
  (20083,'Father','Dayrit','Casimiro','Flores','Retired'),
  (20083,'Mother','Dayrit','Guillerma','Perez','Homemaker'),
  (20084,'Father','Espejo','Procopio','Gomez','Retired'),
  (20084,'Mother','Espejo','Teodora','Santos','Homemaker'),
  (20085,'Father','Flores','Serafin','Hernandez','Retired'),
  (20085,'Mother','Flores','Avelina','Torres','Homemaker'),
  (20086,'Father','Guzman','Benedicto','Ilustre','Retired'),
  (20086,'Mother','Guzman','Primitiva','Cruz','Homemaker');

REPLACE INTO employee_education (employee_id, education_level, school_name, degree_course, year_graduated) VALUES
  (20081,'Graduate Studies','University of the Philippines','MS Accountancy','2008'),
  (20082,'College','Ateneo de Manila University','BS Accountancy','2009'),
  (20083,'College','De La Salle University','BS Accountancy','2013'),
  (20084,'College','Far Eastern University','BS Accountancy','2015'),
  (20085,'College','Mapua University','BS Management','2017'),
  (20086,'College','Pamantasan ng Lungsod ng Maynila','BS Accountancy','2020');

REPLACE INTO employee_work_experience (employee_id, date_from, date_to, job_title, company_name, monthly_salary) VALUES
  (20081,'2008-07-01','2017-09-10','Internal Audit Manager','Quezon Cooperative Bank',46000.00),
  (20082,'2009-08-01','2019-04-21','Senior Auditor','Regional Audit Corp.',34000.00),
  (20083,'2013-07-01','2020-10-04','Auditor','National Audit Services',26000.00),
  (20084,'2015-06-01','2022-01-17','Junior Auditor','Provincial Audit Inc.',22000.00),
  (20085,'2017-07-01','2023-07-02','Audit Associate','Metro Auditing Group',20000.00),
  (20086,'2020-06-01','2024-09-15','Audit Trainee','Financial Audit Co.',17000.00);

REPLACE INTO employee_trainings (employee_id, training_title, conducted_by, no_of_hours) VALUES
  (20081,'Internal Audit Standards (IPPF)','Corporate Training Dept',32.0),
  (20082,'Fraud Detection and Prevention','Corporate Training Dept',24.0),
  (20083,'Financial Audit Techniques','Corporate Training Dept',16.0),
  (20084,'Professional Ethics in Workplace','Corporate Training Dept',16.0),
  (20085,'Audit Documentation Best Practices','Corporate Training Dept',16.0),
  (20086,'ISO 9001:2015 Quality Management','Corporate Training Dept',16.0);

REPLACE INTO employee_disclosures (employee_id, is_related_to_company, has_admin_offense, has_criminal_charge) VALUES
  (20081,0,0,0),(20082,0,0,0),(20083,0,0,0),(20084,0,0,0),(20085,0,0,0),(20086,0,0,0);

REPLACE INTO employee_government_ids (employee_id, sss_number, philhealth_number, pagibig_number, tin_number) VALUES
  (20081,'20-1000081-1','20-100008101-1','2001-0081-0081','200-100-081-000'),
  (20082,'20-1000082-2','20-100008202-2','2001-0082-0082','200-100-082-000'),
  (20083,'20-1000083-3','20-100008303-3','2001-0083-0083','200-100-083-000'),
  (20084,'20-1000084-4','20-100008404-4','2001-0084-0084','200-100-084-000'),
  (20085,'20-1000085-5','20-100008505-5','2001-0085-0085','200-100-085-000'),
  (20086,'20-1000086-6','20-100008606-6','2001-0086-0086','200-100-086-000');

REPLACE INTO employee_addresses (employee_id, address_type, barangay, city, province) VALUES
  (20081,'Residential','Barangay 7','Tayabas City','Quezon'),(20081,'Permanent','Barangay 7','Tayabas City','Quezon'),
  (20082,'Residential','Barangay 14','Lucena City','Quezon'),(20082,'Permanent','Barangay 14','Lucena City','Quezon'),
  (20083,'Residential','Barangay 11','Candelaria','Quezon'),(20083,'Permanent','Barangay 11','Candelaria','Quezon'),
  (20084,'Residential','Barangay 6','Sariaya','Quezon'),(20084,'Permanent','Barangay 6','Sariaya','Quezon'),
  (20085,'Residential','Barangay 8','Lucena City','Quezon'),(20085,'Permanent','Barangay 8','Lucena City','Quezon'),
  (20086,'Residential','Barangay 4','Pagbilao','Quezon'),(20086,'Permanent','Barangay 4','Pagbilao','Quezon');

REPLACE INTO employee_emergency_contacts (employee_id, contact_name, relationship, contact_number) VALUES
  (20081,'Magdalena Perez','Spouse','09180000081'),
  (20082,'Deogracias Corazon','Father','09180000082'),
  (20083,'Casimiro Dayrit','Father','09180000083'),
  (20084,'Procopio Espejo','Father','09180000084'),
  (20085,'Serafin Flores','Father','09180000085'),
  (20086,'Benedicto Guzman','Father','09180000086');

REPLACE INTO employee_real_properties (employee_id, description, kind, acquisition_cost) VALUES
  (20081,'Residential House and Lot','Building and Land',2950000.00),
  (20082,'Residential House and Lot','Building and Land',2150000.00),
  (20083,'Residential House and Lot','Building and Land',1900000.00),
  (20084,'Residential House and Lot','Building and Land',1700000.00),
  (20085,'Residential House and Lot','Building and Land',1600000.00),
  (20086,'Residential House and Lot','Building and Land',1420000.00);

REPLACE INTO employee_personal_properties (employee_id, description, acquisition_cost) VALUES
  (20081,'Personal Vehicle and Savings',410000.00),(20082,'Personal Savings',230000.00),
  (20083,'Personal Savings',175000.00),(20084,'Personal Savings',135000.00),
  (20085,'Personal Savings',110000.00),(20086,'Personal Savings',80000.00);

REPLACE INTO employee_liabilities (employee_id, nature_of_liability, creditor_name, outstanding_balance) VALUES
  (20081,'Housing Loan','Metrobank',1050000.00),(20082,'Personal Loan','Bank',82000.00),
  (20083,'Personal Loan','Bank',65000.00),(20084,'Personal Loan','Bank',48000.00),
  (20085,'Personal Loan','Bank',35000.00),(20086,'Personal Loan','Bank',20000.00);

REPLACE INTO employee_references (employee_id, reference_name, reference_address, reference_telephone) VALUES
  (20081,'CPA Arsenio Santos','Lucena City','02-8901-2301'),
  (20082,'Prof. Margarita Torres','Quezon City','02-8901-2302'),
  (20083,'Mr. Bernardo Ocampo','Candelaria, Quezon','02-8901-2303'),
  (20084,'Ms. Consuelo Reyes','Sariaya, Quezon','02-8901-2304'),
  (20085,'Mr. Rodrigo Cruz','Lucena City','02-8901-2305'),
  (20086,'Ms. Presentacion Gomez','Pagbilao, Quezon','02-8901-2306');

-- Evaluations – Audit (all pending) -----------------
REPLACE INTO evaluations
  (evaluation_id, employee_id, template_id, evaluation_type, evaluation_period_start, evaluation_period_end, assigned_by, assigned_at, status, current_position, months_in_position, kra_subtotal, behavior_average, total_score, performance_level)
VALUES
  (2081,20081,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Manager','Audit Manager I',90,3.88,3.75,3.85,'Outstanding'),
  (2082,20082,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending HR Consolidation','Audit Supervisor I',74,3.38,3.25,3.35,'Exceeds Expectations'),
  (2083,20083,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Supervisor','Auditor I',56,3.00,3.00,3.00,'Exceeds Expectations'),
  (2084,20084,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Self-Rating','Auditor II',42,2.75,2.75,2.75,'Exceeds Expectations'),
  (2085,20085,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Supervisor','Auditor III',24,2.88,2.75,2.85,'Exceeds Expectations'),
  (2086,20086,2,'Initial','2024-09-01','2025-02-28',1,NOW(),'Pending Manager','Auditor on Probation',10,3.32,3.00,3.26,'Exceeds Expectations');

REPLACE INTO evaluation_scores (evaluation_id, criterion_id, score_value, weighted_score) VALUES
  (2081,1,4.00,1.00),(2081,2,3.50,0.88),(2081,3,4.00,1.00),(2081,4,4.00,1.00),
  (2081,5,3.50,3.50),(2081,6,4.00,4.00),(2081,7,4.00,4.00),(2081,8,3.50,3.50),
  (2082,1,3.50,0.88),(2082,2,3.00,0.75),(2082,3,3.50,0.88),(2082,4,3.50,0.88),
  (2082,5,3.00,3.00),(2082,6,3.50,3.50),(2082,7,3.50,3.50),(2082,8,3.00,3.00),
  (2083,1,3.00,0.75),(2083,2,3.00,0.75),(2083,3,3.00,0.75),(2083,4,3.00,0.75),
  (2083,5,3.00,3.00),(2083,6,3.00,3.00),(2083,7,3.00,3.00),(2083,8,3.00,3.00),
  (2084,1,2.50,0.63),(2084,2,3.00,0.75),(2084,3,2.50,0.63),(2084,4,3.00,0.75),
  (2084,5,3.00,3.00),(2084,6,2.50,2.50),(2084,7,3.00,3.00),(2084,8,2.50,2.50),
  (2085,1,3.00,0.75),(2085,2,2.50,0.63),(2085,3,3.00,0.75),(2085,4,3.00,0.75),
  (2085,5,2.50,2.50),(2085,6,3.00,3.00),(2085,7,3.00,3.00),(2085,8,2.50,2.50),
  -- Probationary / Initial evaluation (Template 2 – criteria 9-13)
  (2086,9,3.00,1.05),(2086,10,3.50,1.22),(2086,11,3.50,1.05),(2086,12,3.00,3.00),(2086,13,3.00,3.00);

-- Career Movements – Audit (all Pending) ------------
REPLACE INTO career_movements
  (movement_id, employee_id, movement_type, previous_position, new_position,
   previous_branch_id, new_branch_id, effective_date, reason, logged_by, approval_status, is_applied)
VALUES
  (2081,20082,'Promotion','Audit Supervisor I','Audit Supervisor II',102,102,'2025-11-01',
   'Consistent excellence in conducting internal audit assignments and leading audit teams.',1,'Pending',0),
  (2082,20083,'Salary Adjustment','Auditor I','Auditor I',102,102,'2025-10-01',
   'Annual merit-based salary review.',1,'Pending',0),
  (2083,20086,'Regularization','Auditor on Probation','Auditor I',102,102,'2025-03-01',
   'Satisfactorily completed probationary requirements and passed all audit competency assessments.',1,'Pending',0);

-- =====================================================
-- GENERAL SERVICES DEPARTMENT
-- =====================================================

-- Employees (demo) -----------------------------------
REPLACE INTO employees
  (employee_id, employee_code, first_name, last_name, middle_name,
   hire_date, date_of_birth, place_of_birth, gender, civil_status,
   job_title_id, job_title, department_id, rank_category_id, branch_id,
   employment_status, employment_type, profile_picture)
VALUES
  (20091,'DEMO-GS-001','Generoso','Hernandez','Inciong','2016-11-07','1981-05-19','Lucena City, Quezon','Male','Married',600,'VP for General Services',6,1,42,'Regular','Full-time','avatar_m.jpg'),
  (20092,'DEMO-GS-002','Herminia','Ilustre','Joaquin','2018-03-12','1985-10-03','Tayabas City, Quezon','Female','Married',601,'GS Manager I',6,3,43,'Regular','Full-time','avatar_f.jpg'),
  (20093,'DEMO-GS-003','Isidro','Jacinto','Kapunan','2019-08-19','1988-07-21','Candelaria, Quezon','Male','Single',605,'GS Supervisor I',6,4,44,'Regular','Full-time','avatar_m.jpg'),
  (20094,'DEMO-GS-004','Josefa','Kalaw','Lacson','2021-01-04','1992-03-14','Sariaya, Quezon','Female','Single',609,'Driver I',6,5,45,'Regular','Full-time','avatar_f.jpg'),
  (20095,'DEMO-GS-005','Kasimiro','Lazaro','Magno','2021-06-14','1993-11-28','Lucena City, Quezon','Male','Married',614,'Security Monitoring Staff I',6,5,46,'Regular','Full-time','avatar_m.jpg'),
  (20096,'DEMO-GS-006','Leandra','Macapagal','Navarro','2022-04-25','1995-08-07','Pagbilao, Quezon','Female','Single',618,'Facilities Maintenance Staff I',6,5,47,'Regular','Full-time','avatar_f.jpg'),
  (20097,'DEMO-GS-007','Macario','Natividad','Ocampo','2023-02-13','1997-01-15','Lucena City, Quezon','Male','Single',622,'Messenger I',6,5,48,'Regular','Full-time','avatar_m.jpg'),
  (20098,'DEMO-GS-008','Nelida','Ong','Pascual','2024-05-06','1999-06-22','Tayabas City, Quezon','Female','Single',621,'Warehouse Staff I',6,5,49,'Regular','Full-time','avatar_f.jpg');

REPLACE INTO employee_contacts (employee_id, personal_email, mobile_number, telephone_number) VALUES
  (20091,'generoso.hernandez@example.com','09170100091','888-20091'),
  (20092,'herminia.ilustre@example.com','09170100092','888-20092'),
  (20093,'isidro.jacinto@example.com','09170100093','888-20093'),
  (20094,'josefa.kalaw@example.com','09170100094','888-20094'),
  (20095,'kasimiro.lazaro@example.com','09170100095','888-20095'),
  (20096,'leandra.macapagal@example.com','09170100096','888-20096'),
  (20097,'macario.natividad@example.com','09170100097','888-20097'),
  (20098,'nelida.ong@example.com','09170100098','888-20098');

REPLACE INTO employee_details (employee_id, height_m, weight_kg, blood_type, citizenship) VALUES
  (20091,1.72,76.0,'O+','Filipino'),(20092,1.62,59.0,'A+','Filipino'),
  (20093,1.74,73.0,'B+','Filipino'),(20094,1.59,54.0,'AB+','Filipino'),
  (20095,1.71,72.0,'O-','Filipino'),(20096,1.60,55.0,'A-','Filipino'),
  (20097,1.73,70.0,'B+','Filipino'),(20098,1.58,52.0,'A+','Filipino');

REPLACE INTO employee_family (employee_id, member_type, surname, first_name, middle_name, occupation) VALUES
  (20091,'Father','Hernandez','Tranquilino','Inciong','Retired'),
  (20091,'Mother','Hernandez','Concepcion','Santos','Homemaker'),
  (20091,'Spouse','Santos','Leonila','Cruz','Nurse'),
  (20092,'Father','Ilustre','Primitivo','Joaquin','Retired'),
  (20092,'Mother','Ilustre','Anacleta','Perez','Homemaker'),
  (20092,'Spouse','Reyes','Bienvenido','Gomez','Driver'),
  (20093,'Father','Jacinto','Teofilo','Kapunan','Retired'),
  (20093,'Mother','Jacinto','Visitacion','Torres','Homemaker'),
  (20094,'Father','Kalaw','Remigio','Lacson','Retired'),
  (20094,'Mother','Kalaw','Fortunata','Villanueva','Homemaker'),
  (20095,'Father','Lazaro','Bonifacio','Magno','Retired'),
  (20095,'Mother','Lazaro','Pacita','Gomez','Homemaker'),
  (20095,'Spouse','Reyes','Dolores','Santos','Vendor'),
  (20096,'Father','Macapagal','Eulogio','Navarro','Retired'),
  (20096,'Mother','Macapagal','Pilar','Flores','Homemaker'),
  (20097,'Father','Natividad','Gaudencio','Ocampo','Retired'),
  (20097,'Mother','Natividad','Remedios','Cruz','Homemaker'),
  (20098,'Father','Ong','Hermenegildo','Pascual','Retired'),
  (20098,'Mother','Ong','Filomena','Santos','Homemaker');

REPLACE INTO employee_education (employee_id, education_level, school_name, degree_course, year_graduated) VALUES
  (20091,'Graduate Studies','Ateneo de Manila University','MBA','2006'),
  (20092,'College','University of the Philippines','BS Management','2007'),
  (20093,'College','De La Salle University','BS Civil Engineering','2010'),
  (20094,'College','Pamantasan ng Lungsod ng Maynila','AB Communication','2014'),
  (20095,'College','Polytechnic University of the Philippines','BS Management','2015'),
  (20096,'College','Southern Luzon State University','BS Business Administration','2017'),
  (20097,'College','Mapua University','AB Communication','2019'),
  (20098,'College','Far Eastern University','BS Management','2021');

REPLACE INTO employee_work_experience (employee_id, date_from, date_to, job_title, company_name, monthly_salary) VALUES
  (20091,'2006-07-01','2016-11-06','Facilities Manager','National Services Corp.',50000.00),
  (20092,'2007-08-01','2018-03-11','GS Supervisor','Provincial Government Services',36000.00),
  (20093,'2010-07-01','2019-08-18','Facilities Supervisor','Building Management Inc.',28000.00),
  (20094,'2014-06-01','2021-01-03','Driver','Executive Transport Services',20000.00),
  (20095,'2015-07-01','2021-06-13','Security Staff','Protective Services Corp.',19000.00),
  (20096,'2017-07-01','2022-04-24','Maintenance Staff','Facilities Management PH',18000.00),
  (20097,'2019-06-01','2023-02-12','Messenger','Courier and Logistics Co.',16000.00),
  (20098,'2021-07-01','2024-05-05','Warehouse Associate','Storage Solutions Inc.',17000.00);

REPLACE INTO employee_trainings (employee_id, training_title, conducted_by, no_of_hours) VALUES
  (20091,'Facilities and Operations Management','Corporate Training Dept',32.0),
  (20092,'ISO 9001:2015 Quality Management','Corporate Training Dept',24.0),
  (20093,'Occupational Safety and Health','Corporate Training Dept',16.0),
  (20094,'Safe Driving and Transport Protocols','Corporate Training Dept',16.0),
  (20095,'Security Monitoring Fundamentals','Corporate Training Dept',16.0),
  (20096,'Facilities Maintenance Standards','Corporate Training Dept',16.0),
  (20097,'Courier and Document Handling','Corporate Training Dept',8.0),
  (20098,'Warehouse Management Basics','Corporate Training Dept',8.0);

REPLACE INTO employee_disclosures (employee_id, is_related_to_company, has_admin_offense, has_criminal_charge) VALUES
  (20091,0,0,0),(20092,0,0,0),(20093,0,0,0),(20094,0,0,0),
  (20095,0,0,0),(20096,0,0,0),(20097,0,0,0),(20098,0,0,0);

REPLACE INTO employee_government_ids (employee_id, sss_number, philhealth_number, pagibig_number, tin_number) VALUES
  (20091,'20-1000091-1','20-100009101-1','2001-0091-0091','200-100-091-000'),
  (20092,'20-1000092-2','20-100009202-2','2001-0092-0092','200-100-092-000'),
  (20093,'20-1000093-3','20-100009303-3','2001-0093-0093','200-100-093-000'),
  (20094,'20-1000094-4','20-100009404-4','2001-0094-0094','200-100-094-000'),
  (20095,'20-1000095-5','20-100009505-5','2001-0095-0095','200-100-095-000'),
  (20096,'20-1000096-6','20-100009606-6','2001-0096-0096','200-100-096-000'),
  (20097,'20-1000097-7','20-100009707-7','2001-0097-0097','200-100-097-000'),
  (20098,'20-1000098-8','20-100009808-8','2001-0098-0098','200-100-098-000');

REPLACE INTO employee_addresses (employee_id, address_type, barangay, city, province) VALUES
  (20091,'Residential','Barangay 8','Tayabas City','Quezon'),(20091,'Permanent','Barangay 8','Tayabas City','Quezon'),
  (20092,'Residential','Barangay 13','Lucena City','Quezon'),(20092,'Permanent','Barangay 13','Lucena City','Quezon'),
  (20093,'Residential','Barangay 10','Candelaria','Quezon'),(20093,'Permanent','Barangay 10','Candelaria','Quezon'),
  (20094,'Residential','Barangay 2','Sariaya','Quezon'),(20094,'Permanent','Barangay 2','Sariaya','Quezon'),
  (20095,'Residential','Barangay 5','Lucena City','Quezon'),(20095,'Permanent','Barangay 5','Lucena City','Quezon'),
  (20096,'Residential','Barangay 9','Pagbilao','Quezon'),(20096,'Permanent','Barangay 9','Pagbilao','Quezon'),
  (20097,'Residential','Barangay 4','Lucena City','Quezon'),(20097,'Permanent','Barangay 4','Lucena City','Quezon'),
  (20098,'Residential','Barangay 3','Tayabas City','Quezon'),(20098,'Permanent','Barangay 3','Tayabas City','Quezon');

REPLACE INTO employee_emergency_contacts (employee_id, contact_name, relationship, contact_number) VALUES
  (20091,'Leonila Santos','Spouse','09181100091'),
  (20092,'Bienvenido Reyes','Spouse','09181100092'),
  (20093,'Teofilo Jacinto','Father','09181100093'),
  (20094,'Remigio Kalaw','Father','09181100094'),
  (20095,'Dolores Reyes','Spouse','09181100095'),
  (20096,'Eulogio Macapagal','Father','09181100096'),
  (20097,'Gaudencio Natividad','Father','09181100097'),
  (20098,'Hermenegildo Ong','Father','09181100098');

REPLACE INTO employee_real_properties (employee_id, description, kind, acquisition_cost) VALUES
  (20091,'Residential House and Lot','Building and Land',3200000.00),
  (20092,'Residential House and Lot','Building and Land',2500000.00),
  (20093,'Residential House and Lot','Building and Land',2000000.00),
  (20094,'Residential House and Lot','Building and Land',1600000.00),
  (20095,'Residential House and Lot','Building and Land',1700000.00),
  (20096,'Residential House and Lot','Building and Land',1500000.00),
  (20097,'Residential House and Lot','Building and Land',1350000.00),
  (20098,'Residential House and Lot','Building and Land',1300000.00);

REPLACE INTO employee_personal_properties (employee_id, description, acquisition_cost) VALUES
  (20091,'Personal Vehicle and Savings',450000.00),(20092,'Personal Vehicle and Savings',280000.00),
  (20093,'Personal Savings',190000.00),(20094,'Personal Savings',130000.00),
  (20095,'Personal Savings',145000.00),(20096,'Personal Savings',110000.00),
  (20097,'Personal Savings',85000.00),(20098,'Personal Savings',80000.00);

REPLACE INTO employee_liabilities (employee_id, nature_of_liability, creditor_name, outstanding_balance) VALUES
  (20091,'Housing Loan','BDO Unibank',1200000.00),(20092,'Housing Loan','Metrobank',800000.00),
  (20093,'Personal Loan','Bank',75000.00),(20094,'Personal Loan','Bank',45000.00),
  (20095,'Personal Loan','Bank',50000.00),(20096,'Personal Loan','Bank',35000.00),
  (20097,'Personal Loan','Bank',20000.00),(20098,'Personal Loan','Bank',18000.00);

REPLACE INTO employee_references (employee_id, reference_name, reference_address, reference_telephone) VALUES
  (20091,'Engr. Honesto Dela Cruz','Lucena City','02-9012-3401'),
  (20092,'Ms. Rosario Torres','Quezon City','02-9012-3402'),
  (20093,'Mr. Faustino Reyes','Candelaria, Quezon','02-9012-3403'),
  (20094,'Ms. Epifania Santos','Sariaya, Quezon','02-9012-3404'),
  (20095,'Mr. Macario Gomez','Lucena City','02-9012-3405'),
  (20096,'Ms. Concepcion Cruz','Pagbilao, Quezon','02-9012-3406'),
  (20097,'Mr. Procopio Villanueva','Lucena City','02-9012-3407'),
  (20098,'Ms. Florentina Perez','Tayabas City, Quezon','02-9012-3408');

-- Evaluations – General Services (all pending) ------
REPLACE INTO evaluations
  (evaluation_id, employee_id, template_id, evaluation_type, evaluation_period_start, evaluation_period_end, assigned_by, assigned_at, status, current_position, months_in_position, kra_subtotal, behavior_average, total_score, performance_level)
VALUES
  (2091,20091,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Manager','VP for General Services',102,4.00,3.75,3.95,'Outstanding'),
  (2092,20092,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending HR Consolidation','GS Manager I',85,3.50,3.38,3.48,'Exceeds Expectations'),
  (2093,20093,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Supervisor','GS Supervisor I',68,3.25,3.13,3.23,'Exceeds Expectations'),
  (2094,20094,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Self-Rating','Driver I',52,3.00,3.00,3.00,'Exceeds Expectations'),
  (2095,20095,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Supervisor','Security Monitoring Staff I',46,2.88,2.88,2.88,'Exceeds Expectations'),
  (2096,20096,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending HR Consolidation','Facilities Maintenance Staff I',38,2.63,2.75,2.65,'Exceeds Expectations'),
  (2097,20097,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Self-Rating','Messenger I',28,2.88,2.75,2.85,'Exceeds Expectations'),
  (2098,20098,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Manager','Warehouse Staff I',14,2.88,2.88,2.88,'Exceeds Expectations');

REPLACE INTO evaluation_scores (evaluation_id, criterion_id, score_value, weighted_score) VALUES
  (2091,1,4.00,1.00),(2091,2,4.00,1.00),(2091,3,4.00,1.00),(2091,4,4.00,1.00),
  (2091,5,4.00,4.00),(2091,6,3.50,3.50),(2091,7,4.00,4.00),(2091,8,3.50,3.50),
  (2092,1,3.50,0.88),(2092,2,3.50,0.88),(2092,3,3.50,0.88),(2092,4,3.50,0.88),
  (2092,5,3.50,3.50),(2092,6,3.50,3.50),(2092,7,3.50,3.50),(2092,8,3.00,3.00),
  (2093,1,3.00,0.75),(2093,2,3.50,0.88),(2093,3,3.00,0.75),(2093,4,3.50,0.88),
  (2093,5,3.00,3.00),(2093,6,3.00,3.00),(2093,7,3.50,3.50),(2093,8,3.00,3.00),
  (2094,1,3.00,0.75),(2094,2,3.00,0.75),(2094,3,3.00,0.75),(2094,4,3.00,0.75),
  (2094,5,3.00,3.00),(2094,6,3.00,3.00),(2094,7,3.00,3.00),(2094,8,3.00,3.00),
  (2095,1,3.00,0.75),(2095,2,3.00,0.75),(2095,3,2.50,0.63),(2095,4,3.00,0.75),
  (2095,5,3.00,3.00),(2095,6,3.00,3.00),(2095,7,2.50,2.50),(2095,8,3.00,3.00),
  (2096,1,2.50,0.63),(2096,2,3.00,0.75),(2096,3,2.50,0.63),(2096,4,2.50,0.63),
  (2096,5,3.00,3.00),(2096,6,2.50,2.50),(2096,7,3.00,3.00),(2096,8,2.50,2.50),
  (2097,1,3.00,0.75),(2097,2,2.50,0.63),(2097,3,3.00,0.75),(2097,4,3.00,0.75),
  (2097,5,2.50,2.50),(2097,6,3.00,3.00),(2097,7,3.00,3.00),(2097,8,2.50,2.50),
  (2098,1,3.00,0.75),(2098,2,3.00,0.75),(2098,3,3.00,0.75),(2098,4,2.50,0.63),
  (2098,5,3.00,3.00),(2098,6,2.50,2.50),(2098,7,3.00,3.00),(2098,8,3.00,3.00);

-- Career Movements – General Services (all Pending) -
REPLACE INTO career_movements
  (movement_id, employee_id, movement_type, previous_position, new_position,
   previous_branch_id, new_branch_id, effective_date, reason, logged_by, approval_status, is_applied)
VALUES
  (2091,20092,'Promotion','GS Manager I','GS Manager II',102,102,'2025-10-01',
   'Exemplary management of facilities projects and cost-efficiency improvements.',1,'Pending',0),
  (2092,20093,'Promotion','GS Supervisor I','GS Supervisor II',102,102,'2025-11-01',
   'Strong performance overseeing maintenance and logistics operations.',1,'Pending',0),
  (2093,20097,'Salary Adjustment','Messenger I','Messenger I',102,102,'2025-09-01',
   'Annual salary review; merit-based increase.',1,'Pending',0);

-- =====================================================
-- OFFICE OF THE PRESIDENT
-- =====================================================

-- Employees (demo) -----------------------------------
REPLACE INTO employees
  (employee_id, employee_code, first_name, last_name, middle_name,
   hire_date, date_of_birth, place_of_birth, gender, civil_status,
   job_title_id, job_title, department_id, rank_category_id, branch_id,
   employment_status, employment_type, profile_picture)
VALUES
  (20101,'DEMO-OP-001','Olympia','Perez','Quezon','2015-07-06','1978-10-31','Lucena City, Quezon','Female','Married',1100,'President and CEO',10,1,50,'Regular','Full-time','avatar_f.jpg'),
  (20102,'DEMO-OP-002','Patricio','Quiambao','Reyes','2020-03-16','1990-08-14','Tayabas City, Quezon','Male','Single',1101,'Executive Assistant I',10,5,51,'Regular','Full-time','avatar_m.jpg'),
  (20103,'DEMO-OP-003','Quirina','Ramos','Santos','2022-09-05','1994-02-22','Candelaria, Quezon','Female','Single',1102,'Executive Assistant II',10,5,52,'Regular','Full-time','avatar_f.jpg'),
  (20104,'DEMO-OP-004','Rodrigo','Santiago','Torres','2024-01-22','1997-07-04','Lucena City, Quezon','Male','Single',1103,'Executive Assistant III',10,5,53,'Regular','Full-time','avatar_m.jpg');

REPLACE INTO employee_contacts (employee_id, personal_email, mobile_number, telephone_number) VALUES
  (20101,'olympia.perez@example.com','09172100101','888-20101'),
  (20102,'patricio.quiambao@example.com','09172100102','888-20102'),
  (20103,'quirina.ramos@example.com','09172100103','888-20103'),
  (20104,'rodrigo.santiago@example.com','09172100104','888-20104');

REPLACE INTO employee_details (employee_id, height_m, weight_kg, blood_type, citizenship) VALUES
  (20101,1.65,60.0,'A+','Filipino'),(20102,1.75,73.0,'O+','Filipino'),
  (20103,1.60,55.0,'B+','Filipino'),(20104,1.72,70.0,'AB+','Filipino');

REPLACE INTO employee_family (employee_id, member_type, surname, first_name, middle_name, occupation) VALUES
  (20101,'Father','Perez','Honorio','Quezon','Retired'),
  (20101,'Mother','Perez','Perpetua','Santos','Homemaker'),
  (20101,'Spouse','Ocampo','Florencio','Cruz','Businessman'),
  (20102,'Father','Quiambao','Benedicto','Reyes','Retired'),
  (20102,'Mother','Quiambao','Asuncion','Torres','Homemaker'),
  (20103,'Father','Ramos','Toribio','Santos','Retired'),
  (20103,'Mother','Ramos','Dolores','Perez','Homemaker'),
  (20104,'Father','Santiago','Gregorio','Torres','Retired'),
  (20104,'Mother','Santiago','Teresita','Gomez','Homemaker');

REPLACE INTO employee_education (employee_id, education_level, school_name, degree_course, year_graduated) VALUES
  (20101,'Graduate Studies','Harvard University','MBA','2003'),
  (20102,'College','Ateneo de Manila University','BS Business Administration','2012'),
  (20103,'College','University of the Philippines','BS Management','2016'),
  (20104,'College','De La Salle University','BS Business Administration','2019');

REPLACE INTO employee_work_experience (employee_id, date_from, date_to, job_title, company_name, monthly_salary) VALUES
  (20101,'2003-08-01','2015-07-05','CEO / Managing Director','Luzon Ventures Group',120000.00),
  (20102,'2012-07-01','2020-03-15','Executive Secretary','Corporate Holdings Inc.',28000.00),
  (20103,'2016-07-01','2022-09-04','Senior Administrative Assistant','Executive Suites Corp.',24000.00),
  (20104,'2019-06-01','2024-01-21','Administrative Staff','Presidential Secretariat Inc.',20000.00);

REPLACE INTO employee_trainings (employee_id, training_title, conducted_by, no_of_hours) VALUES
  (20101,'Executive Leadership and Governance','Corporate Training Dept',40.0),
  (20102,'Executive Support and Protocol','Corporate Training Dept',24.0),
  (20103,'Advanced Business Communication','Corporate Training Dept',16.0),
  (20104,'Office Management Fundamentals','Corporate Training Dept',16.0);

REPLACE INTO employee_disclosures (employee_id, is_related_to_company, has_admin_offense, has_criminal_charge) VALUES
  (20101,0,0,0),(20102,0,0,0),(20103,0,0,0),(20104,0,0,0);

REPLACE INTO employee_government_ids (employee_id, sss_number, philhealth_number, pagibig_number, tin_number) VALUES
  (20101,'20-1001010-1','20-100010101-1','2001-0101-0101','200-101-001-000'),
  (20102,'20-1001020-2','20-100010202-2','2001-0102-0102','200-101-002-000'),
  (20103,'20-1001030-3','20-100010303-3','2001-0103-0103','200-101-003-000'),
  (20104,'20-1001040-4','20-100010404-4','2001-0104-0104','200-101-004-000');

REPLACE INTO employee_addresses (employee_id, address_type, barangay, city, province) VALUES
  (20101,'Residential','Alabang Hills','Muntinlupa City','Metro Manila'),(20101,'Permanent','San Isidro','Tayabas City','Quezon'),
  (20102,'Residential','Barangay 9','Tayabas City','Quezon'),(20102,'Permanent','Barangay 9','Tayabas City','Quezon'),
  (20103,'Residential','Barangay 15','Lucena City','Quezon'),(20103,'Permanent','Barangay 15','Lucena City','Quezon'),
  (20104,'Residential','Barangay 11','Candelaria','Quezon'),(20104,'Permanent','Barangay 11','Candelaria','Quezon');

REPLACE INTO employee_emergency_contacts (employee_id, contact_name, relationship, contact_number) VALUES
  (20101,'Florencio Ocampo','Spouse','09183100101'),
  (20102,'Benedicto Quiambao','Father','09183100102'),
  (20103,'Toribio Ramos','Father','09183100103'),
  (20104,'Gregorio Santiago','Father','09183100104');

REPLACE INTO employee_real_properties (employee_id, description, kind, acquisition_cost) VALUES
  (20101,'Residential House and Lot','Building and Land',12000000.00),
  (20102,'Residential House and Lot','Building and Land',2400000.00),
  (20103,'Residential House and Lot','Building and Land',1900000.00),
  (20104,'Residential House and Lot','Building and Land',1600000.00);

REPLACE INTO employee_personal_properties (employee_id, description, acquisition_cost) VALUES
  (20101,'Personal Vehicles and Savings',2500000.00),(20102,'Personal Savings',250000.00),
  (20103,'Personal Savings',180000.00),(20104,'Personal Savings',120000.00);

REPLACE INTO employee_liabilities (employee_id, nature_of_liability, creditor_name, outstanding_balance) VALUES
  (20101,'None','N/A',0.00),(20102,'Personal Loan','Bank',90000.00),
  (20103,'Personal Loan','Bank',65000.00),(20104,'Personal Loan','Bank',40000.00);

REPLACE INTO employee_references (employee_id, reference_name, reference_address, reference_telephone) VALUES
  (20101,'Ambassador Ricardo Santos','Makati City','02-9123-4501'),
  (20102,'Prof. Herminio Perez','Quezon City','02-9123-4502'),
  (20103,'Ms. Milagros Reyes','Lucena City','02-9123-4503'),
  (20104,'Mr. Celestino Torres','Candelaria, Quezon','02-9123-4504');

-- Evaluations – Office of the President (all pending)
REPLACE INTO evaluations
  (evaluation_id, employee_id, template_id, evaluation_type, evaluation_period_start, evaluation_period_end, assigned_by, assigned_at, status, current_position, months_in_position, kra_subtotal, behavior_average, total_score, performance_level)
VALUES
  (2101,20101,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending HR Consolidation','President and CEO',120,4.00,4.00,4.00,'Outstanding'),
  (2102,20102,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Supervisor','Executive Assistant I',64,3.50,3.50,3.50,'Exceeds Expectations'),
  (2103,20103,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Self-Rating','Executive Assistant II',34,3.13,3.13,3.13,'Exceeds Expectations'),
  (2104,20104,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Manager','Executive Assistant III',18,3.00,3.00,3.00,'Exceeds Expectations');

REPLACE INTO evaluation_scores (evaluation_id, criterion_id, score_value, weighted_score) VALUES
  (2101,1,4.00,1.00),(2101,2,4.00,1.00),(2101,3,4.00,1.00),(2101,4,4.00,1.00),
  (2101,5,4.00,4.00),(2101,6,4.00,4.00),(2101,7,4.00,4.00),(2101,8,4.00,4.00),
  (2102,1,3.50,0.88),(2102,2,3.50,0.88),(2102,3,3.50,0.88),(2102,4,3.50,0.88),
  (2102,5,3.50,3.50),(2102,6,3.50,3.50),(2102,7,3.50,3.50),(2102,8,3.50,3.50),
  (2103,1,3.00,0.75),(2103,2,3.50,0.88),(2103,3,3.00,0.75),(2103,4,3.00,0.75),
  (2103,5,3.00,3.00),(2103,6,3.50,3.50),(2103,7,3.00,3.00),(2103,8,3.00,3.00),
  (2104,1,3.00,0.75),(2104,2,3.00,0.75),(2104,3,3.00,0.75),(2104,4,3.00,0.75),
  (2104,5,3.00,3.00),(2104,6,3.00,3.00),(2104,7,3.00,3.00),(2104,8,3.00,3.00);

-- Career Movements – Office of the President (Pending)
REPLACE INTO career_movements
  (movement_id, employee_id, movement_type, previous_position, new_position,
   previous_branch_id, new_branch_id, effective_date, reason, logged_by, approval_status, is_applied)
VALUES
  (2101,20102,'Promotion','Executive Assistant I','Executive Assistant II',102,102,'2025-10-01',
   'Demonstrated excellent executive support skills and confidentiality management.',1,'Pending',0);

-- =====================================================
-- ACQUIRED PROPERTIES DEPARTMENT
-- =====================================================

-- Employees (demo) -----------------------------------
REPLACE INTO employees
  (employee_id, employee_code, first_name, last_name, middle_name,
   hire_date, date_of_birth, place_of_birth, gender, civil_status,
   job_title_id, job_title, department_id, rank_category_id, branch_id,
   employment_status, employment_type, profile_picture)
VALUES
  (20111,'DEMO-AP-001','Silvestre','Torres','Umali','2016-04-18','1980-07-25','Lucena City, Quezon','Male','Married',100,'VP for Acquired Properties',1,1,54,'Regular','Full-time','avatar_m.jpg'),
  (20112,'DEMO-AP-002','Teresita','Umali','Valdes','2018-10-29','1984-03-11','Tayabas City, Quezon','Female','Married',101,'AP Manager I',1,3,55,'Regular','Full-time','avatar_f.jpg'),
  (20113,'DEMO-AP-003','Uldarico','Valdes','Yalong','2020-02-03','1988-09-06','Candelaria, Quezon','Male','Single',105,'AP Supervisor I',1,4,56,'Regular','Full-time','avatar_m.jpg'),
  (20114,'DEMO-AP-004','Veronica','Wenceslao','Zamora','2021-07-12','1992-05-17','Sariaya, Quezon','Female','Single',109,'AP Staff I',1,5,57,'Regular','Full-time','avatar_f.jpg'),
  (20115,'DEMO-AP-005','Warlito','Yap','Ablaza','2022-11-21','1994-12-30','Lucena City, Quezon','Male','Married',115,'Sales Associate I',1,5,58,'Regular','Full-time','avatar_m.jpg'),
  (20116,'DEMO-AP-006','Xenia','Zabala','Buenaventura','2023-06-05','1996-08-14','Pagbilao, Quezon','Female','Single',116,'Sales Associate II',1,5,59,'Regular','Full-time','avatar_f.jpg'),
  (20117,'DEMO-AP-007','Yosef','Abalos','Castillo','2024-10-07','1999-03-22','Tayabas City, Quezon','Male','Single',108,'AP Staff on Probation',1,5,60,'Probationary','Full-time','avatar_m.jpg');

REPLACE INTO employee_contacts (employee_id, personal_email, mobile_number, telephone_number) VALUES
  (20111,'silvestre.torres@example.com','09173200111','888-20111'),
  (20112,'teresita.umali@example.com','09173200112','888-20112'),
  (20113,'uldarico.valdes@example.com','09173200113','888-20113'),
  (20114,'veronica.wenceslao@example.com','09173200114','888-20114'),
  (20115,'warlito.yap@example.com','09173200115','888-20115'),
  (20116,'xenia.zabala@example.com','09173200116','888-20116'),
  (20117,'yosef.abalos@example.com','09173200117','888-20117');

REPLACE INTO employee_details (employee_id, height_m, weight_kg, blood_type, citizenship) VALUES
  (20111,1.73,75.0,'O+','Filipino'),(20112,1.62,60.0,'A+','Filipino'),
  (20113,1.74,72.0,'B+','Filipino'),(20114,1.59,54.0,'AB+','Filipino'),
  (20115,1.71,70.0,'O-','Filipino'),(20116,1.60,55.0,'A-','Filipino'),
  (20117,1.72,69.0,'B+','Filipino');

REPLACE INTO employee_family (employee_id, member_type, surname, first_name, middle_name, occupation) VALUES
  (20111,'Father','Torres','Tiburcio','Umali','Retired'),
  (20111,'Mother','Torres','Purificacion','Reyes','Homemaker'),
  (20111,'Spouse','Reyes','Maricel','Santos','Businesswoman'),
  (20112,'Father','Umali','Casimiro','Valdes','Retired'),
  (20112,'Mother','Umali','Florinda','Perez','Homemaker'),
  (20112,'Spouse','Santos','Ernesto','Gomez','Engineer'),
  (20113,'Father','Valdes','Conrado','Yalong','Retired'),
  (20113,'Mother','Valdes','Aniceta','Torres','Homemaker'),
  (20114,'Father','Wenceslao','Evaristo','Zamora','Retired'),
  (20114,'Mother','Wenceslao','Cirila','Villanueva','Homemaker'),
  (20115,'Father','Yap','Segundo','Ablaza','Retired'),
  (20115,'Mother','Yap','Lourdes','Gomez','Homemaker'),
  (20115,'Spouse','Santos','Maricel','Cruz','Teacher'),
  (20116,'Father','Zabala','Augusto','Buenaventura','Retired'),
  (20116,'Mother','Zabala','Carmelita','Flores','Homemaker'),
  (20117,'Father','Abalos','Rogelio','Castillo','Retired'),
  (20117,'Mother','Abalos','Erlinda','Santos','Homemaker');

REPLACE INTO employee_education (employee_id, education_level, school_name, degree_course, year_graduated) VALUES
  (20111,'Graduate Studies','Ateneo de Manila University','MBA Real Estate','2005'),
  (20112,'College','University of the Philippines','BS Real Estate Management','2006'),
  (20113,'College','De La Salle University','BS Business Administration','2010'),
  (20114,'College','Far Eastern University','BS Real Estate Management','2014'),
  (20115,'College','Polytechnic University of the Philippines','BS Business Administration','2016'),
  (20116,'College','Southern Luzon State University','BS Management','2018'),
  (20117,'College','Mapua University','BS Business Administration','2021');

REPLACE INTO employee_work_experience (employee_id, date_from, date_to, job_title, company_name, monthly_salary) VALUES
  (20111,'2005-07-01','2016-04-17','Properties Manager','Nationwide Realty Corp.',52000.00),
  (20112,'2006-08-01','2018-10-28','Properties Supervisor','Metro Real Estate Inc.',38000.00),
  (20113,'2010-07-01','2020-02-02','AP Coordinator','Regional Properties Group',28000.00),
  (20114,'2014-06-01','2021-07-11','Properties Staff','Asset Management Corp.',22000.00),
  (20115,'2016-07-01','2022-11-20','Sales Associate','National Properties Co.',20000.00),
  (20116,'2018-07-01','2023-06-04','Sales Staff','Quezon Real Estate Group',19000.00),
  (20117,'2021-06-01','2024-10-06','Properties Trainee','Provincial Asset Management',17000.00);

REPLACE INTO employee_trainings (employee_id, training_title, conducted_by, no_of_hours) VALUES
  (20111,'Real Estate Investment and Asset Management','Corporate Training Dept',32.0),
  (20112,'Property Sales and Negotiation','Corporate Training Dept',24.0),
  (20113,'AP Field Operations Standards','Corporate Training Dept',16.0),
  (20114,'Customer Service Excellence','Corporate Training Dept',16.0),
  (20115,'Sales Techniques and Closing Strategies','Corporate Training Dept',16.0),
  (20116,'Real Estate Fundamentals','Corporate Training Dept',16.0),
  (20117,'New Employee Orientation','Corporate Training Dept',8.0);

REPLACE INTO employee_disclosures (employee_id, is_related_to_company, has_admin_offense, has_criminal_charge) VALUES
  (20111,0,0,0),(20112,0,0,0),(20113,0,0,0),(20114,0,0,0),(20115,0,0,0),(20116,0,0,0),(20117,0,0,0);

REPLACE INTO employee_government_ids (employee_id, sss_number, philhealth_number, pagibig_number, tin_number) VALUES
  (20111,'20-1001110-1','20-100011101-1','2001-0111-0111','200-110-001-000'),
  (20112,'20-1001120-2','20-100011202-2','2001-0112-0112','200-110-002-000'),
  (20113,'20-1001130-3','20-100011303-3','2001-0113-0113','200-110-003-000'),
  (20114,'20-1001140-4','20-100011404-4','2001-0114-0114','200-110-004-000'),
  (20115,'20-1001150-5','20-100011505-5','2001-0115-0115','200-110-005-000'),
  (20116,'20-1001160-6','20-100011606-6','2001-0116-0116','200-110-006-000'),
  (20117,'20-1001170-7','20-100011707-7','2001-0117-0117','200-110-007-000');

REPLACE INTO employee_addresses (employee_id, address_type, barangay, city, province) VALUES
  (20111,'Residential','Poblacion','Tayabas City','Quezon'),(20111,'Permanent','Poblacion','Tayabas City','Quezon'),
  (20112,'Residential','Barangay 5','Lucena City','Quezon'),(20112,'Permanent','Barangay 5','Lucena City','Quezon'),
  (20113,'Residential','Barangay 7','Candelaria','Quezon'),(20113,'Permanent','Barangay 7','Candelaria','Quezon'),
  (20114,'Residential','Barangay 4','Sariaya','Quezon'),(20114,'Permanent','Barangay 4','Sariaya','Quezon'),
  (20115,'Residential','Barangay 6','Lucena City','Quezon'),(20115,'Permanent','Barangay 6','Lucena City','Quezon'),
  (20116,'Residential','Barangay 3','Pagbilao','Quezon'),(20116,'Permanent','Barangay 3','Pagbilao','Quezon'),
  (20117,'Residential','Barangay 8','Tayabas City','Quezon'),(20117,'Permanent','Barangay 8','Tayabas City','Quezon');

REPLACE INTO employee_emergency_contacts (employee_id, contact_name, relationship, contact_number) VALUES
  (20111,'Maricel Reyes','Spouse','09184200111'),
  (20112,'Ernesto Santos','Spouse','09184200112'),
  (20113,'Conrado Valdes','Father','09184200113'),
  (20114,'Evaristo Wenceslao','Father','09184200114'),
  (20115,'Maricel Santos','Spouse','09184200115'),
  (20116,'Augusto Zabala','Father','09184200116'),
  (20117,'Rogelio Abalos','Father','09184200117');

REPLACE INTO employee_real_properties (employee_id, description, kind, acquisition_cost) VALUES
  (20111,'Residential House and Lot','Building and Land',3500000.00),
  (20112,'Residential House and Lot','Building and Land',2700000.00),
  (20113,'Residential House and Lot','Building and Land',2100000.00),
  (20114,'Residential House and Lot','Building and Land',1850000.00),
  (20115,'Residential House and Lot','Building and Land',1800000.00),
  (20116,'Residential House and Lot','Building and Land',1600000.00),
  (20117,'Residential House and Lot','Building and Land',1400000.00);

REPLACE INTO employee_personal_properties (employee_id, description, acquisition_cost) VALUES
  (20111,'Personal Vehicle and Savings',480000.00),(20112,'Personal Vehicle and Savings',310000.00),
  (20113,'Personal Savings',240000.00),(20114,'Personal Savings',170000.00),
  (20115,'Personal Savings',160000.00),(20116,'Personal Savings',130000.00),
  (20117,'Personal Savings',80000.00);

REPLACE INTO employee_liabilities (employee_id, nature_of_liability, creditor_name, outstanding_balance) VALUES
  (20111,'Housing Loan','BDO Unibank',1400000.00),(20112,'Housing Loan','Metrobank',900000.00),
  (20113,'Personal Loan','Bank',100000.00),(20114,'Personal Loan','Bank',65000.00),
  (20115,'Personal Loan','Bank',60000.00),(20116,'Personal Loan','Bank',42000.00),
  (20117,'Personal Loan','Bank',22000.00);

REPLACE INTO employee_references (employee_id, reference_name, reference_address, reference_telephone) VALUES
  (20111,'Mr. Celestino Aquino','Lucena City','02-9234-5601'),
  (20112,'Ms. Presentacion Santos','Quezon City','02-9234-5602'),
  (20113,'Mr. Adriano Torres','Candelaria, Quezon','02-9234-5603'),
  (20114,'Ms. Iluminada Reyes','Sariaya, Quezon','02-9234-5604'),
  (20115,'Mr. Mariano Gomez','Lucena City','02-9234-5605'),
  (20116,'Ms. Balbina Cruz','Pagbilao, Quezon','02-9234-5606'),
  (20117,'Mr. Cornelio Villanueva','Tayabas City, Quezon','02-9234-5607');

-- Evaluations – Acquired Properties (all pending) ---
REPLACE INTO evaluations
  (evaluation_id, employee_id, template_id, evaluation_type, evaluation_period_start, evaluation_period_end, assigned_by, assigned_at, status, current_position, months_in_position, kra_subtotal, behavior_average, total_score, performance_level)
VALUES
  (2111,20111,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Manager','VP for Acquired Properties',108,3.88,3.75,3.85,'Outstanding'),
  (2112,20112,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending HR Consolidation','AP Manager I',82,3.38,3.38,3.38,'Exceeds Expectations'),
  (2113,20113,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Supervisor','AP Supervisor I',60,3.13,3.13,3.13,'Exceeds Expectations'),
  (2114,20114,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Self-Rating','AP Staff I',48,3.00,3.00,3.00,'Exceeds Expectations'),
  (2115,20115,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending Supervisor','Sales Associate I',32,2.75,2.75,2.75,'Exceeds Expectations'),
  (2116,20116,1,'Annual','2025-01-01','2025-12-31',1,NOW(),'Pending HR Consolidation','Sales Associate II',19,2.75,2.75,2.75,'Exceeds Expectations'),
  (2117,20117,2,'Initial','2024-10-01','2025-03-31',1,NOW(),'Pending Manager','AP Staff on Probation',9,2.83,3.00,2.86,'Exceeds Expectations');

REPLACE INTO evaluation_scores (evaluation_id, criterion_id, score_value, weighted_score) VALUES
  (2111,1,4.00,1.00),(2111,2,3.50,0.88),(2111,3,4.00,1.00),(2111,4,4.00,1.00),
  (2111,5,3.50,3.50),(2111,6,4.00,4.00),(2111,7,3.50,3.50),(2111,8,4.00,4.00),
  (2112,1,3.50,0.88),(2112,2,3.50,0.88),(2112,3,3.00,0.75),(2112,4,3.50,0.88),
  (2112,5,3.50,3.50),(2112,6,3.50,3.50),(2112,7,3.00,3.00),(2112,8,3.50,3.50),
  (2113,1,3.00,0.75),(2113,2,3.50,0.88),(2113,3,3.00,0.75),(2113,4,3.00,0.75),
  (2113,5,3.00,3.00),(2113,6,3.00,3.00),(2113,7,3.50,3.50),(2113,8,3.00,3.00),
  (2114,1,3.00,0.75),(2114,2,3.00,0.75),(2114,3,3.00,0.75),(2114,4,3.00,0.75),
  (2114,5,3.00,3.00),(2114,6,3.00,3.00),(2114,7,3.00,3.00),(2114,8,3.00,3.00),
  (2115,1,2.50,0.63),(2115,2,3.00,0.75),(2115,3,2.50,0.63),(2115,4,3.00,0.75),
  (2115,5,3.00,3.00),(2115,6,2.50,2.50),(2115,7,3.00,3.00),(2115,8,2.50,2.50),
  (2116,1,3.00,0.75),(2116,2,2.50,0.63),(2116,3,3.00,0.75),(2116,4,2.50,0.63),
  (2116,5,2.50,2.50),(2116,6,3.00,3.00),(2116,7,3.00,3.00),(2116,8,2.50,2.50),
  -- Probationary / Initial evaluation (Template 2 – criteria 9-13)
  (2117,9,3.00,1.05),(2117,10,2.50,0.88),(2117,11,3.00,0.90),(2117,12,3.00,3.00),(2117,13,3.00,3.00);

-- Career Movements – Acquired Properties (Pending) --
REPLACE INTO career_movements
  (movement_id, employee_id, movement_type, previous_position, new_position,
   previous_branch_id, new_branch_id, effective_date, reason, logged_by, approval_status, is_applied)
VALUES
  (2111,20112,'Promotion','AP Manager I','AP Manager II',102,102,'2025-11-01',
   'Strong leadership in managing acquired properties portfolio and client relations.',1,'Pending',0),
  (2112,20113,'Salary Adjustment','AP Supervisor I','AP Supervisor I',102,102,'2025-10-01',
   'Merit-based salary increase following annual performance review.',1,'Pending',0),
  (2113,20115,'Role Change','Sales Associate I','Sales Associate II',102,102,'2025-09-15',
   'Expanded sales territory and increased revenue contribution.',1,'Pending',0),
  (2114,20117,'Regularization','AP Staff on Probation','AP Staff I',102,102,'2025-04-07',
   'Successfully completed probationary period.',1,'Pending',0);

-- =====================================================
-- EMPLOYEE PORTAL ACCOUNTS (demo employees)
-- =====================================================
-- Auto-generate Employee-role users from demo employees,
-- using employee_code as username.  Password is "password" (bcrypt).
-- =====================================================
INSERT INTO users
  (employee_id, username, email, password_hash, full_name, role, branch_id, is_active, first_login_completed, created_at)
SELECT
  e.employee_id,
  e.employee_code AS username,
  IFNULL(ec.personal_email, CONCAT(LOWER(e.employee_code), '@raquel.ph')) AS email,
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' AS password_hash,
  TRIM(CONCAT_WS(' ', e.first_name, e.middle_name, e.last_name)) AS full_name,
  'Employee' AS role,
  e.branch_id,
  1 AS is_active,
  0 AS first_login_completed,
  NOW() AS created_at
FROM employees e
LEFT JOIN employee_contacts ec ON ec.employee_id = e.employee_id
WHERE e.employee_id BETWEEN 20001 AND 20199
  AND e.employee_code IS NOT NULL
  AND TRIM(e.employee_code) <> ''
  AND NOT EXISTS (
      SELECT 1 FROM users u WHERE u.employee_id = e.employee_id
  );

-- =====================================================

-- =====================================================
-- HISTORICAL APPROVED EVALUATIONS (for YoY Progression & Score Trends)
-- =====================================================
REPLACE INTO evaluations
  (evaluation_id, employee_id, template_id, evaluation_type, evaluation_period_start, evaluation_period_end, assigned_by, assigned_at, status, current_position, months_in_position, kra_subtotal, behavior_average, total_score, performance_level, approved_date, approved_by)
VALUES
  (30001,20001,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','800',36,3.09,3.14,3.10,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30002,20001,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','800',48,3.31,3.36,3.32,'Exceeds Expectations','2025-12-15 16:00:00',1),
  (30003,20002,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','804',12,2.80,2.75,2.79,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30004,20002,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','804',24,3.02,2.97,3.01,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30005,20002,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','804',36,3.24,3.19,3.23,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30006,20002,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','804',48,3.46,3.41,3.45,'Exceeds Expectations','2025-12-15 16:00:00',1),
  (30007,20003,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','810',12,2.95,3.00,2.96,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30008,20003,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','810',24,3.17,3.22,3.18,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30009,20003,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','810',36,3.39,3.44,3.40,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30010,20003,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','810',48,3.61,3.66,3.62,'Outstanding','2025-12-15 16:00:00',1),
  (30011,20004,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','811',12,2.98,2.93,2.97,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30012,20004,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','811',24,3.20,3.15,3.19,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30013,20004,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','811',36,3.42,3.37,3.41,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30014,20004,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','811',48,3.64,3.59,3.63,'Outstanding','2025-12-15 16:00:00',1),
  (30015,20005,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','816',24,3.35,3.40,3.36,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30016,20005,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','816',36,3.57,3.62,3.58,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30017,20005,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','816',48,3.79,3.84,3.80,'Outstanding','2025-12-15 16:00:00',1),
  (30018,20006,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','809',12,3.28,3.23,3.27,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30019,20006,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','809',24,3.50,3.45,3.49,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30020,20006,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','809',36,3.72,3.67,3.71,'Outstanding','2024-12-15 16:00:00',1),
  (30021,20006,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','809',48,3.94,3.89,3.93,'Outstanding','2025-12-15 16:00:00',1),
  (30022,20011,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','700',12,3.31,3.36,3.32,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30023,20011,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','700',24,3.53,3.58,3.54,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30024,20011,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','700',36,3.75,3.80,3.76,'Outstanding','2024-12-15 16:00:00',1),
  (30025,20011,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','700',48,3.97,4.00,3.98,'Outstanding','2025-12-15 16:00:00',1),
  (30026,20012,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','705',12,2.69,2.64,2.68,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30027,20012,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','705',24,2.91,2.86,2.90,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30028,20012,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','705',36,3.13,3.08,3.12,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30029,20012,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','705',48,3.35,3.30,3.34,'Exceeds Expectations','2025-12-15 16:00:00',1),
  (30030,20013,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','711',24,3.06,3.11,3.07,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30031,20013,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','711',36,3.28,3.33,3.29,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30032,20013,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','711',48,3.50,3.55,3.51,'Exceeds Expectations','2025-12-15 16:00:00',1),
  (30033,20014,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','712',12,2.87,2.82,2.86,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30034,20014,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','712',36,3.31,3.26,3.30,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30035,20014,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','712',48,3.53,3.48,3.52,'Exceeds Expectations','2025-12-15 16:00:00',1),
  (30036,20015,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','710',12,3.02,3.07,3.03,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30037,20015,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','710',24,3.24,3.29,3.25,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30038,20015,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','710',36,3.46,3.51,3.47,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30039,20015,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','710',48,3.68,3.73,3.69,'Outstanding','2025-12-15 16:00:00',1),
  (30040,20021,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','500',12,3.17,3.12,3.16,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30041,20021,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','500',24,3.39,3.34,3.38,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30042,20021,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','500',36,3.61,3.56,3.60,'Outstanding','2024-12-15 16:00:00',1),
  (30043,20021,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','500',48,3.83,3.78,3.82,'Outstanding','2025-12-15 16:00:00',1),
  (30044,20022,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','501',24,3.42,3.47,3.43,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30045,20022,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','501',36,3.64,3.69,3.65,'Outstanding','2024-12-15 16:00:00',1),
  (30046,20022,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','501',48,3.86,3.91,3.87,'Outstanding','2025-12-15 16:00:00',1),
  (30047,20023,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','505',12,3.35,3.30,3.34,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30048,20023,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','505',24,3.57,3.52,3.56,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30049,20023,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','505',36,3.79,3.74,3.78,'Outstanding','2024-12-15 16:00:00',1),
  (30050,20023,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','505',48,4.00,3.95,3.99,'Outstanding','2025-12-15 16:00:00',1),
  (30051,20024,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','511',12,2.73,2.78,2.74,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30052,20024,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','511',24,2.95,3.00,2.96,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30053,20024,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','511',36,3.17,3.22,3.18,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30054,20024,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','511',48,3.39,3.44,3.40,'Exceeds Expectations','2025-12-15 16:00:00',1),
  (30055,20025,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','516',12,2.76,2.71,2.75,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30056,20025,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','516',24,2.98,2.93,2.97,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30057,20025,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','516',36,3.20,3.15,3.19,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30058,20025,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','516',48,3.42,3.37,3.41,'Exceeds Expectations','2025-12-15 16:00:00',1),
  (30059,20026,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','510',24,3.13,3.18,3.14,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30060,20026,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','510',36,3.35,3.40,3.36,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30061,20026,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','510',48,3.57,3.62,3.58,'Exceeds Expectations','2025-12-15 16:00:00',1),
  (30062,20027,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','517',12,3.06,3.01,3.05,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30063,20027,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','517',24,3.28,3.23,3.27,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30064,20027,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','517',36,3.50,3.45,3.49,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30065,20027,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','517',48,3.72,3.67,3.71,'Outstanding','2025-12-15 16:00:00',1),
  (30066,20031,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','900',12,3.09,3.14,3.10,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30067,20031,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','900',36,3.53,3.58,3.54,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30068,20031,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','900',48,3.75,3.80,3.76,'Outstanding','2025-12-15 16:00:00',1),
  (30069,20032,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','902',12,3.24,3.19,3.23,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30070,20032,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','902',24,3.46,3.41,3.45,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30071,20032,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','902',36,3.68,3.63,3.67,'Outstanding','2024-12-15 16:00:00',1),
  (30072,20032,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','902',48,3.90,3.85,3.89,'Outstanding','2025-12-15 16:00:00',1),
  (30073,20033,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','905',24,3.61,3.66,3.62,'Outstanding','2023-12-15 16:00:00',1),
  (30074,20033,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','905',36,3.83,3.88,3.84,'Outstanding','2024-12-15 16:00:00',1),
  (30075,20033,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','905',48,4.00,4.00,4.00,'Outstanding','2025-12-15 16:00:00',1),
  (30076,20034,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','906',12,2.65,2.60,2.64,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30077,20034,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','906',24,2.87,2.82,2.86,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30078,20034,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','906',36,3.09,3.04,3.08,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30079,20034,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','906',48,3.31,3.26,3.30,'Exceeds Expectations','2025-12-15 16:00:00',1),
  (30080,20035,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','904',12,2.80,2.85,2.81,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30081,20035,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','904',24,3.02,3.07,3.03,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30082,20035,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','904',36,3.24,3.29,3.25,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30083,20035,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','904',48,3.46,3.51,3.47,'Exceeds Expectations','2025-12-15 16:00:00',1),
  (30084,20041,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','1000',12,2.95,2.90,2.94,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30085,20041,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','1000',24,3.17,3.12,3.16,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30086,20041,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','1000',36,3.39,3.34,3.38,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30087,20041,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','1000',48,3.61,3.56,3.60,'Outstanding','2025-12-15 16:00:00',1),
  (30088,20042,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','1001',24,3.20,3.25,3.21,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30089,20042,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','1001',36,3.42,3.47,3.43,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30090,20042,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','1001',48,3.64,3.69,3.65,'Outstanding','2025-12-15 16:00:00',1),
  (30091,20043,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','1004',12,3.13,3.08,3.12,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30092,20043,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','1004',24,3.35,3.30,3.34,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30093,20043,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','1004',36,3.57,3.52,3.56,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30094,20043,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','1004',48,3.79,3.74,3.78,'Outstanding','2025-12-15 16:00:00',1),
  (30095,20044,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','1007',12,3.28,3.33,3.29,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30096,20044,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','1007',24,3.50,3.55,3.51,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30097,20044,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','1007',36,3.72,3.77,3.73,'Outstanding','2024-12-15 16:00:00',1),
  (30098,20044,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','1007',48,3.94,3.99,3.95,'Outstanding','2025-12-15 16:00:00',1),
  (30099,20045,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','1013',12,3.31,3.26,3.30,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30100,20045,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','1013',36,3.75,3.70,3.74,'Outstanding','2024-12-15 16:00:00',1),
  (30101,20045,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','1013',48,3.97,3.92,3.96,'Outstanding','2025-12-15 16:00:00',1),
  (30102,20046,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','1014',24,2.91,2.96,2.92,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30103,20046,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','1014',36,3.13,3.18,3.14,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30104,20046,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','1014',48,3.35,3.40,3.36,'Exceeds Expectations','2025-12-15 16:00:00',1),
  (30105,20047,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','1015',12,2.84,2.79,2.83,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30106,20047,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','1015',24,3.06,3.01,3.05,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30107,20047,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','1015',36,3.28,3.23,3.27,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30108,20047,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','1015',48,3.50,3.45,3.49,'Exceeds Expectations','2025-12-15 16:00:00',1),
  (30109,20048,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','1012',12,2.87,2.92,2.88,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30110,20048,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','1012',24,3.09,3.14,3.10,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30111,20048,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','1012',36,3.31,3.36,3.32,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30112,20048,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','1012',48,3.53,3.58,3.54,'Exceeds Expectations','2025-12-15 16:00:00',1),
  (30113,20051,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','1200',12,3.02,2.97,3.01,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30114,20051,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','1200',24,3.24,3.19,3.23,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30115,20051,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','1200',36,3.46,3.41,3.45,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30116,20051,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','1200',48,3.68,3.63,3.67,'Outstanding','2025-12-15 16:00:00',1),
  (30117,20052,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','1202',24,3.39,3.44,3.40,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30118,20052,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','1202',36,3.61,3.66,3.62,'Outstanding','2024-12-15 16:00:00',1),
  (30119,20052,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','1202',48,3.83,3.88,3.84,'Outstanding','2025-12-15 16:00:00',1),
  (30120,20053,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','1202',12,3.20,3.15,3.19,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30121,20053,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','1202',24,3.42,3.37,3.41,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30122,20053,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','1202',36,3.64,3.59,3.63,'Outstanding','2024-12-15 16:00:00',1),
  (30123,20053,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','1202',48,3.86,3.81,3.85,'Outstanding','2025-12-15 16:00:00',1),
  (30124,20054,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','1201',12,3.35,3.40,3.36,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30125,20054,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','1201',24,3.57,3.62,3.58,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30126,20054,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','1201',36,3.79,3.84,3.80,'Outstanding','2024-12-15 16:00:00',1),
  (30127,20054,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','1201',48,4.00,4.00,4.00,'Outstanding','2025-12-15 16:00:00',1),
  (30128,20061,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','400',12,2.73,2.68,2.72,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30129,20061,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','400',24,2.95,2.90,2.94,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30130,20061,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','400',36,3.17,3.12,3.16,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30131,20061,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','400',48,3.39,3.34,3.38,'Exceeds Expectations','2025-12-15 16:00:00',1),
  (30132,20062,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','401',36,3.20,3.25,3.21,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30133,20062,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','401',48,3.42,3.47,3.43,'Exceeds Expectations','2025-12-15 16:00:00',1),
  (30134,20063,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','403',12,2.91,2.86,2.90,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30135,20063,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','403',24,3.13,3.08,3.12,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30136,20063,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','403',36,3.35,3.30,3.34,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30137,20063,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','403',48,3.57,3.52,3.56,'Exceeds Expectations','2025-12-15 16:00:00',1),
  (30138,20064,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','404',12,3.06,3.11,3.07,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30139,20064,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','404',24,3.28,3.33,3.29,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30140,20064,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','404',36,3.50,3.55,3.51,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30141,20064,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','404',48,3.72,3.77,3.73,'Outstanding','2025-12-15 16:00:00',1),
  (30142,20065,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','405',12,3.09,3.04,3.08,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30143,20065,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','405',24,3.31,3.26,3.30,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30144,20065,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','405',36,3.53,3.48,3.52,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30145,20065,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','405',48,3.75,3.70,3.74,'Outstanding','2025-12-15 16:00:00',1),
  (30146,20071,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','300',24,3.46,3.51,3.47,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30147,20071,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','300',36,3.68,3.73,3.69,'Outstanding','2024-12-15 16:00:00',1),
  (30148,20071,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','300',48,3.90,3.95,3.91,'Outstanding','2025-12-15 16:00:00',1),
  (30149,20072,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','302',12,3.39,3.34,3.38,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30150,20072,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','302',24,3.61,3.56,3.60,'Outstanding','2023-12-15 16:00:00',1),
  (30151,20072,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','302',36,3.83,3.78,3.82,'Outstanding','2024-12-15 16:00:00',1),
  (30152,20072,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','302',48,4.00,3.95,3.99,'Outstanding','2025-12-15 16:00:00',1),
  (30153,20073,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','303',12,2.65,2.70,2.66,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30154,20073,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','303',24,2.87,2.92,2.88,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30155,20073,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','303',36,3.09,3.14,3.10,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30156,20073,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','303',48,3.31,3.36,3.32,'Exceeds Expectations','2025-12-15 16:00:00',1),
  (30157,20074,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','301',12,2.80,2.75,2.79,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30158,20074,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','301',24,3.02,2.97,3.01,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30159,20074,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','301',36,3.24,3.19,3.23,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30160,20074,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','301',48,3.46,3.41,3.45,'Exceeds Expectations','2025-12-15 16:00:00',1),
  (30161,20081,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','200',24,3.17,3.22,3.18,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30162,20081,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','200',36,3.39,3.44,3.40,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30163,20081,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','200',48,3.61,3.66,3.62,'Outstanding','2025-12-15 16:00:00',1),
  (30164,20082,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','203',12,2.98,2.93,2.97,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30165,20082,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','203',36,3.42,3.37,3.41,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30166,20082,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','203',48,3.64,3.59,3.63,'Outstanding','2025-12-15 16:00:00',1),
  (30167,20083,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','207',12,3.13,3.18,3.14,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30168,20083,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','207',24,3.35,3.40,3.36,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30169,20083,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','207',36,3.57,3.62,3.58,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30170,20083,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','207',48,3.79,3.84,3.80,'Outstanding','2025-12-15 16:00:00',1),
  (30171,20084,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','208',12,3.28,3.23,3.27,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30172,20084,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','208',24,3.50,3.45,3.49,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30173,20084,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','208',36,3.72,3.67,3.71,'Outstanding','2024-12-15 16:00:00',1),
  (30174,20084,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','208',48,3.94,3.89,3.93,'Outstanding','2025-12-15 16:00:00',1),
  (30175,20085,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','209',24,3.53,3.58,3.54,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30176,20085,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','209',36,3.75,3.80,3.76,'Outstanding','2024-12-15 16:00:00',1),
  (30177,20085,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','209',48,3.97,4.00,3.98,'Outstanding','2025-12-15 16:00:00',1),
  (30178,20086,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','206',12,2.69,2.64,2.68,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30179,20086,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','206',24,2.91,2.86,2.90,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30180,20086,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','206',36,3.13,3.08,3.12,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30181,20086,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','206',48,3.35,3.30,3.34,'Exceeds Expectations','2025-12-15 16:00:00',1),
  (30182,20091,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','600',12,2.84,2.89,2.85,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30183,20091,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','600',24,3.06,3.11,3.07,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30184,20091,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','600',36,3.28,3.33,3.29,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30185,20091,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','600',48,3.50,3.55,3.51,'Exceeds Expectations','2025-12-15 16:00:00',1),
  (30186,20092,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','601',12,2.87,2.82,2.86,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30187,20092,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','601',24,3.09,3.04,3.08,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30188,20092,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','601',36,3.31,3.26,3.30,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30189,20092,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','601',48,3.53,3.48,3.52,'Exceeds Expectations','2025-12-15 16:00:00',1),
  (30190,20093,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','605',24,3.24,3.29,3.25,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30191,20093,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','605',36,3.46,3.51,3.47,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30192,20093,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','605',48,3.68,3.73,3.69,'Outstanding','2025-12-15 16:00:00',1),
  (30193,20094,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','609',12,3.17,3.12,3.16,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30194,20094,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','609',24,3.39,3.34,3.38,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30195,20094,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','609',36,3.61,3.56,3.60,'Outstanding','2024-12-15 16:00:00',1),
  (30196,20094,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','609',48,3.83,3.78,3.82,'Outstanding','2025-12-15 16:00:00',1),
  (30197,20095,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','614',12,3.20,3.25,3.21,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30198,20095,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','614',36,3.64,3.69,3.65,'Outstanding','2024-12-15 16:00:00',1),
  (30199,20095,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','614',48,3.86,3.91,3.87,'Outstanding','2025-12-15 16:00:00',1),
  (30200,20096,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','618',12,3.35,3.30,3.34,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30201,20096,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','618',24,3.57,3.52,3.56,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30202,20096,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','618',36,3.79,3.74,3.78,'Outstanding','2024-12-15 16:00:00',1),
  (30203,20096,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','618',48,4.00,3.95,3.99,'Outstanding','2025-12-15 16:00:00',1),
  (30204,20097,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','622',24,2.95,3.00,2.96,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30205,20097,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','622',36,3.17,3.22,3.18,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30206,20097,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','622',48,3.39,3.44,3.40,'Exceeds Expectations','2025-12-15 16:00:00',1),
  (30207,20098,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','621',12,2.76,2.71,2.75,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30208,20098,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','621',24,2.98,2.93,2.97,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30209,20098,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','621',36,3.20,3.15,3.19,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30210,20098,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','621',48,3.42,3.37,3.41,'Exceeds Expectations','2025-12-15 16:00:00',1),
  (30211,20101,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','1100',12,2.91,2.96,2.92,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30212,20101,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','1100',24,3.13,3.18,3.14,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30213,20101,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','1100',36,3.35,3.40,3.36,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30214,20101,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','1100',48,3.57,3.62,3.58,'Exceeds Expectations','2025-12-15 16:00:00',1),
  (30215,20102,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','1101',12,3.06,3.01,3.05,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30216,20102,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','1101',24,3.28,3.23,3.27,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30217,20102,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','1101',36,3.50,3.45,3.49,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30218,20102,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','1101',48,3.72,3.67,3.71,'Outstanding','2025-12-15 16:00:00',1),
  (30219,20103,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','1102',24,3.31,3.36,3.32,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30220,20103,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','1102',36,3.53,3.58,3.54,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30221,20103,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','1102',48,3.75,3.80,3.76,'Outstanding','2025-12-15 16:00:00',1),
  (30222,20104,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','1103',12,3.24,3.19,3.23,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30223,20104,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','1103',24,3.46,3.41,3.45,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30224,20104,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','1103',36,3.68,3.63,3.67,'Outstanding','2024-12-15 16:00:00',1),
  (30225,20104,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','1103',48,3.90,3.85,3.89,'Outstanding','2025-12-15 16:00:00',1),
  (30226,20111,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','100',12,3.39,3.44,3.40,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30227,20111,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','100',24,3.61,3.66,3.62,'Outstanding','2023-12-15 16:00:00',1),
  (30228,20111,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','100',36,3.83,3.88,3.84,'Outstanding','2024-12-15 16:00:00',1),
  (30229,20111,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','100',48,4.00,4.00,4.00,'Outstanding','2025-12-15 16:00:00',1),
  (30230,20112,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','101',12,2.65,2.60,2.64,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30231,20112,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','101',36,3.09,3.04,3.08,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30232,20112,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','101',48,3.31,3.26,3.30,'Exceeds Expectations','2025-12-15 16:00:00',1),
  (30233,20113,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','105',24,3.02,3.07,3.03,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30234,20113,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','105',36,3.24,3.29,3.25,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30235,20113,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','105',48,3.46,3.51,3.47,'Exceeds Expectations','2025-12-15 16:00:00',1),
  (30236,20114,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','109',12,2.95,2.90,2.94,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30237,20114,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','109',24,3.17,3.12,3.16,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30238,20114,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','109',36,3.39,3.34,3.38,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30239,20114,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','109',48,3.61,3.56,3.60,'Outstanding','2025-12-15 16:00:00',1),
  (30240,20115,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','115',12,2.98,3.03,2.99,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30241,20115,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','115',24,3.20,3.25,3.21,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30242,20115,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','115',36,3.42,3.47,3.43,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30243,20115,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','115',48,3.64,3.69,3.65,'Outstanding','2025-12-15 16:00:00',1),
  (30244,20116,1,'Annual','2022-01-01','2022-12-31',1,'2022-12-15 16:00:00','Approved','116',12,3.13,3.08,3.12,'Exceeds Expectations','2022-12-15 16:00:00',1),
  (30245,20116,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','116',24,3.35,3.30,3.34,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30246,20116,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','116',36,3.57,3.52,3.56,'Exceeds Expectations','2024-12-15 16:00:00',1),
  (30247,20116,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','116',48,3.79,3.74,3.78,'Outstanding','2025-12-15 16:00:00',1),
  (30248,20117,1,'Annual','2023-01-01','2023-12-31',1,'2023-12-15 16:00:00','Approved','108',24,3.50,3.55,3.51,'Exceeds Expectations','2023-12-15 16:00:00',1),
  (30249,20117,1,'Annual','2024-01-01','2024-12-31',1,'2024-12-15 16:00:00','Approved','108',36,3.72,3.77,3.73,'Outstanding','2024-12-15 16:00:00',1),
  (30250,20117,1,'Annual','2025-01-01','2025-12-31',1,'2025-12-15 16:00:00','Approved','108',48,3.94,3.99,3.95,'Outstanding','2025-12-15 16:00:00',1);

REPLACE INTO evaluation_scores (evaluation_id, criterion_id, score_value, weighted_score) VALUES
  (30001,1,2.99,0.75),
  (30001,2,3.19,0.80),
  (30001,3,2.99,0.75),
  (30001,4,3.19,0.80),
  (30001,5,3.09,3.09),
  (30001,6,3.19,3.19),
  (30001,7,3.09,3.09),
  (30001,8,3.19,3.19),
  (30002,1,3.21,0.80),
  (30002,2,3.41,0.85),
  (30002,3,3.21,0.80),
  (30002,4,3.41,0.85),
  (30002,5,3.31,3.31),
  (30002,6,3.41,3.41),
  (30002,7,3.31,3.31),
  (30002,8,3.41,3.41),
  (30003,1,2.70,0.68),
  (30003,2,2.90,0.73),
  (30003,3,2.70,0.68),
  (30003,4,2.90,0.73),
  (30003,5,2.70,2.70),
  (30003,6,2.80,2.80),
  (30003,7,2.70,2.70),
  (30003,8,2.80,2.80),
  (30004,1,2.92,0.73),
  (30004,2,3.12,0.78),
  (30004,3,2.92,0.73),
  (30004,4,3.12,0.78),
  (30004,5,2.92,2.92),
  (30004,6,3.02,3.02),
  (30004,7,2.92,2.92),
  (30004,8,3.02,3.02),
  (30005,1,3.14,0.79),
  (30005,2,3.34,0.84),
  (30005,3,3.14,0.79),
  (30005,4,3.34,0.84),
  (30005,5,3.14,3.14),
  (30005,6,3.24,3.24),
  (30005,7,3.14,3.14),
  (30005,8,3.24,3.24),
  (30006,1,3.36,0.84),
  (30006,2,3.56,0.89),
  (30006,3,3.36,0.84),
  (30006,4,3.56,0.89),
  (30006,5,3.36,3.36),
  (30006,6,3.46,3.46),
  (30006,7,3.36,3.36),
  (30006,8,3.46,3.46),
  (30007,1,2.85,0.71),
  (30007,2,3.05,0.76),
  (30007,3,2.85,0.71),
  (30007,4,3.05,0.76),
  (30007,5,2.95,2.95),
  (30007,6,3.05,3.05),
  (30007,7,2.95,2.95),
  (30007,8,3.05,3.05),
  (30008,1,3.07,0.77),
  (30008,2,3.27,0.82),
  (30008,3,3.07,0.77),
  (30008,4,3.27,0.82),
  (30008,5,3.17,3.17),
  (30008,6,3.27,3.27),
  (30008,7,3.17,3.17),
  (30008,8,3.27,3.27),
  (30009,1,3.29,0.82),
  (30009,2,3.49,0.87),
  (30009,3,3.29,0.82),
  (30009,4,3.49,0.87),
  (30009,5,3.39,3.39),
  (30009,6,3.49,3.49),
  (30009,7,3.39,3.39),
  (30009,8,3.49,3.49),
  (30010,1,3.51,0.88),
  (30010,2,3.71,0.93),
  (30010,3,3.51,0.88),
  (30010,4,3.71,0.93),
  (30010,5,3.61,3.61),
  (30010,6,3.71,3.71),
  (30010,7,3.61,3.61),
  (30010,8,3.71,3.71),
  (30011,1,2.88,0.72),
  (30011,2,3.08,0.77),
  (30011,3,2.88,0.72),
  (30011,4,3.08,0.77),
  (30011,5,2.88,2.88),
  (30011,6,2.98,2.98),
  (30011,7,2.88,2.88),
  (30011,8,2.98,2.98),
  (30012,1,3.10,0.78),
  (30012,2,3.30,0.83),
  (30012,3,3.10,0.78),
  (30012,4,3.30,0.83),
  (30012,5,3.10,3.10),
  (30012,6,3.20,3.20),
  (30012,7,3.10,3.10),
  (30012,8,3.20,3.20),
  (30013,1,3.32,0.83),
  (30013,2,3.52,0.88),
  (30013,3,3.32,0.83),
  (30013,4,3.52,0.88),
  (30013,5,3.32,3.32),
  (30013,6,3.42,3.42),
  (30013,7,3.32,3.32),
  (30013,8,3.42,3.42),
  (30014,1,3.54,0.89),
  (30014,2,3.74,0.94),
  (30014,3,3.54,0.89),
  (30014,4,3.74,0.94),
  (30014,5,3.54,3.54),
  (30014,6,3.64,3.64),
  (30014,7,3.54,3.54),
  (30014,8,3.64,3.64),
  (30015,1,3.25,0.81),
  (30015,2,3.45,0.86),
  (30015,3,3.25,0.81),
  (30015,4,3.45,0.86),
  (30015,5,3.35,3.35),
  (30015,6,3.45,3.45),
  (30015,7,3.35,3.35),
  (30015,8,3.45,3.45),
  (30016,1,3.47,0.87),
  (30016,2,3.67,0.92),
  (30016,3,3.47,0.87),
  (30016,4,3.67,0.92),
  (30016,5,3.57,3.57),
  (30016,6,3.67,3.67),
  (30016,7,3.57,3.57),
  (30016,8,3.67,3.67),
  (30017,1,3.69,0.92),
  (30017,2,3.89,0.97),
  (30017,3,3.69,0.92),
  (30017,4,3.89,0.97),
  (30017,5,3.79,3.79),
  (30017,6,3.89,3.89),
  (30017,7,3.79,3.79),
  (30017,8,3.89,3.89),
  (30018,1,3.18,0.80),
  (30018,2,3.38,0.85),
  (30018,3,3.18,0.80),
  (30018,4,3.38,0.85),
  (30018,5,3.18,3.18),
  (30018,6,3.28,3.28),
  (30018,7,3.18,3.18),
  (30018,8,3.28,3.28),
  (30019,1,3.40,0.85),
  (30019,2,3.60,0.90),
  (30019,3,3.40,0.85),
  (30019,4,3.60,0.90),
  (30019,5,3.40,3.40),
  (30019,6,3.50,3.50),
  (30019,7,3.40,3.40),
  (30019,8,3.50,3.50),
  (30020,1,3.62,0.91),
  (30020,2,3.82,0.96),
  (30020,3,3.62,0.91),
  (30020,4,3.82,0.96),
  (30020,5,3.62,3.62),
  (30020,6,3.72,3.72),
  (30020,7,3.62,3.62),
  (30020,8,3.72,3.72),
  (30021,1,3.84,0.96),
  (30021,2,4.00,1.00),
  (30021,3,3.84,0.96),
  (30021,4,4.00,1.00),
  (30021,5,3.84,3.84),
  (30021,6,3.94,3.94),
  (30021,7,3.84,3.84),
  (30021,8,3.94,3.94),
  (30022,1,3.21,0.80),
  (30022,2,3.41,0.85),
  (30022,3,3.21,0.80),
  (30022,4,3.41,0.85),
  (30022,5,3.31,3.31),
  (30022,6,3.41,3.41),
  (30022,7,3.31,3.31),
  (30022,8,3.41,3.41),
  (30023,1,3.43,0.86),
  (30023,2,3.63,0.91),
  (30023,3,3.43,0.86),
  (30023,4,3.63,0.91),
  (30023,5,3.53,3.53),
  (30023,6,3.63,3.63),
  (30023,7,3.53,3.53),
  (30023,8,3.63,3.63),
  (30024,1,3.65,0.91),
  (30024,2,3.85,0.96),
  (30024,3,3.65,0.91),
  (30024,4,3.85,0.96),
  (30024,5,3.75,3.75),
  (30024,6,3.85,3.85),
  (30024,7,3.75,3.75),
  (30024,8,3.85,3.85),
  (30025,1,3.87,0.97),
  (30025,2,4.00,1.00),
  (30025,3,3.87,0.97),
  (30025,4,4.00,1.00),
  (30025,5,3.95,3.95),
  (30025,6,4.00,4.00),
  (30025,7,3.95,3.95),
  (30025,8,4.00,4.00),
  (30026,1,2.59,0.65),
  (30026,2,2.79,0.70),
  (30026,3,2.59,0.65),
  (30026,4,2.79,0.70),
  (30026,5,2.59,2.59),
  (30026,6,2.69,2.69),
  (30026,7,2.59,2.59),
  (30026,8,2.69,2.69),
  (30027,1,2.81,0.70),
  (30027,2,3.01,0.75),
  (30027,3,2.81,0.70),
  (30027,4,3.01,0.75),
  (30027,5,2.81,2.81),
  (30027,6,2.91,2.91),
  (30027,7,2.81,2.81),
  (30027,8,2.91,2.91),
  (30028,1,3.03,0.76),
  (30028,2,3.23,0.81),
  (30028,3,3.03,0.76),
  (30028,4,3.23,0.81),
  (30028,5,3.03,3.03),
  (30028,6,3.13,3.13),
  (30028,7,3.03,3.03),
  (30028,8,3.13,3.13),
  (30029,1,3.25,0.81),
  (30029,2,3.45,0.86),
  (30029,3,3.25,0.81),
  (30029,4,3.45,0.86),
  (30029,5,3.25,3.25),
  (30029,6,3.35,3.35),
  (30029,7,3.25,3.25),
  (30029,8,3.35,3.35),
  (30030,1,2.96,0.74),
  (30030,2,3.16,0.79),
  (30030,3,2.96,0.74),
  (30030,4,3.16,0.79),
  (30030,5,3.06,3.06),
  (30030,6,3.16,3.16),
  (30030,7,3.06,3.06),
  (30030,8,3.16,3.16),
  (30031,1,3.18,0.80),
  (30031,2,3.38,0.85),
  (30031,3,3.18,0.80),
  (30031,4,3.38,0.85),
  (30031,5,3.28,3.28),
  (30031,6,3.38,3.38),
  (30031,7,3.28,3.28),
  (30031,8,3.38,3.38),
  (30032,1,3.40,0.85),
  (30032,2,3.60,0.90),
  (30032,3,3.40,0.85),
  (30032,4,3.60,0.90),
  (30032,5,3.50,3.50),
  (30032,6,3.60,3.60),
  (30032,7,3.50,3.50),
  (30032,8,3.60,3.60),
  (30033,1,2.77,0.69),
  (30033,2,2.97,0.74),
  (30033,3,2.77,0.69),
  (30033,4,2.97,0.74),
  (30033,5,2.77,2.77),
  (30033,6,2.87,2.87),
  (30033,7,2.77,2.77),
  (30033,8,2.87,2.87),
  (30034,1,3.21,0.80),
  (30034,2,3.41,0.85),
  (30034,3,3.21,0.80),
  (30034,4,3.41,0.85),
  (30034,5,3.21,3.21),
  (30034,6,3.31,3.31),
  (30034,7,3.21,3.21),
  (30034,8,3.31,3.31),
  (30035,1,3.43,0.86),
  (30035,2,3.63,0.91),
  (30035,3,3.43,0.86),
  (30035,4,3.63,0.91),
  (30035,5,3.43,3.43),
  (30035,6,3.53,3.53),
  (30035,7,3.43,3.43),
  (30035,8,3.53,3.53),
  (30036,1,2.92,0.73),
  (30036,2,3.12,0.78),
  (30036,3,2.92,0.73),
  (30036,4,3.12,0.78),
  (30036,5,3.02,3.02),
  (30036,6,3.12,3.12),
  (30036,7,3.02,3.02),
  (30036,8,3.12,3.12),
  (30037,1,3.14,0.79),
  (30037,2,3.34,0.84),
  (30037,3,3.14,0.79),
  (30037,4,3.34,0.84),
  (30037,5,3.24,3.24),
  (30037,6,3.34,3.34),
  (30037,7,3.24,3.24),
  (30037,8,3.34,3.34),
  (30038,1,3.36,0.84),
  (30038,2,3.56,0.89),
  (30038,3,3.36,0.84),
  (30038,4,3.56,0.89),
  (30038,5,3.46,3.46),
  (30038,6,3.56,3.56),
  (30038,7,3.46,3.46),
  (30038,8,3.56,3.56),
  (30039,1,3.58,0.90),
  (30039,2,3.78,0.95),
  (30039,3,3.58,0.90),
  (30039,4,3.78,0.95),
  (30039,5,3.68,3.68),
  (30039,6,3.78,3.78),
  (30039,7,3.68,3.68),
  (30039,8,3.78,3.78),
  (30040,1,3.07,0.77),
  (30040,2,3.27,0.82),
  (30040,3,3.07,0.77),
  (30040,4,3.27,0.82),
  (30040,5,3.07,3.07),
  (30040,6,3.17,3.17),
  (30040,7,3.07,3.07),
  (30040,8,3.17,3.17),
  (30041,1,3.29,0.82),
  (30041,2,3.49,0.87),
  (30041,3,3.29,0.82),
  (30041,4,3.49,0.87),
  (30041,5,3.29,3.29),
  (30041,6,3.39,3.39),
  (30041,7,3.29,3.29),
  (30041,8,3.39,3.39),
  (30042,1,3.51,0.88),
  (30042,2,3.71,0.93),
  (30042,3,3.51,0.88),
  (30042,4,3.71,0.93),
  (30042,5,3.51,3.51),
  (30042,6,3.61,3.61),
  (30042,7,3.51,3.51),
  (30042,8,3.61,3.61),
  (30043,1,3.73,0.93),
  (30043,2,3.93,0.98),
  (30043,3,3.73,0.93),
  (30043,4,3.93,0.98),
  (30043,5,3.73,3.73),
  (30043,6,3.83,3.83),
  (30043,7,3.73,3.73),
  (30043,8,3.83,3.83),
  (30044,1,3.32,0.83),
  (30044,2,3.52,0.88),
  (30044,3,3.32,0.83),
  (30044,4,3.52,0.88),
  (30044,5,3.42,3.42),
  (30044,6,3.52,3.52),
  (30044,7,3.42,3.42),
  (30044,8,3.52,3.52),
  (30045,1,3.54,0.89),
  (30045,2,3.74,0.94),
  (30045,3,3.54,0.89),
  (30045,4,3.74,0.94),
  (30045,5,3.64,3.64),
  (30045,6,3.74,3.74),
  (30045,7,3.64,3.64),
  (30045,8,3.74,3.74),
  (30046,1,3.76,0.94),
  (30046,2,3.96,0.99),
  (30046,3,3.76,0.94),
  (30046,4,3.96,0.99),
  (30046,5,3.86,3.86),
  (30046,6,3.96,3.96),
  (30046,7,3.86,3.86),
  (30046,8,3.96,3.96),
  (30047,1,3.25,0.81),
  (30047,2,3.45,0.86),
  (30047,3,3.25,0.81),
  (30047,4,3.45,0.86),
  (30047,5,3.25,3.25),
  (30047,6,3.35,3.35),
  (30047,7,3.25,3.25),
  (30047,8,3.35,3.35),
  (30048,1,3.47,0.87),
  (30048,2,3.67,0.92),
  (30048,3,3.47,0.87),
  (30048,4,3.67,0.92),
  (30048,5,3.47,3.47),
  (30048,6,3.57,3.57),
  (30048,7,3.47,3.47),
  (30048,8,3.57,3.57),
  (30049,1,3.69,0.92),
  (30049,2,3.89,0.97),
  (30049,3,3.69,0.92),
  (30049,4,3.89,0.97),
  (30049,5,3.69,3.69),
  (30049,6,3.79,3.79),
  (30049,7,3.69,3.69),
  (30049,8,3.79,3.79),
  (30050,1,3.90,0.98),
  (30050,2,4.00,1.00),
  (30050,3,3.90,0.98),
  (30050,4,4.00,1.00),
  (30050,5,3.90,3.90),
  (30050,6,4.00,4.00),
  (30050,7,3.90,3.90),
  (30050,8,4.00,4.00),
  (30051,1,2.63,0.66),
  (30051,2,2.83,0.71),
  (30051,3,2.63,0.66),
  (30051,4,2.83,0.71),
  (30051,5,2.73,2.73),
  (30051,6,2.83,2.83),
  (30051,7,2.73,2.73),
  (30051,8,2.83,2.83),
  (30052,1,2.85,0.71),
  (30052,2,3.05,0.76),
  (30052,3,2.85,0.71),
  (30052,4,3.05,0.76),
  (30052,5,2.95,2.95),
  (30052,6,3.05,3.05),
  (30052,7,2.95,2.95),
  (30052,8,3.05,3.05),
  (30053,1,3.07,0.77),
  (30053,2,3.27,0.82),
  (30053,3,3.07,0.77),
  (30053,4,3.27,0.82),
  (30053,5,3.17,3.17),
  (30053,6,3.27,3.27),
  (30053,7,3.17,3.17),
  (30053,8,3.27,3.27),
  (30054,1,3.29,0.82),
  (30054,2,3.49,0.87),
  (30054,3,3.29,0.82),
  (30054,4,3.49,0.87),
  (30054,5,3.39,3.39),
  (30054,6,3.49,3.49),
  (30054,7,3.39,3.39),
  (30054,8,3.49,3.49),
  (30055,1,2.66,0.67),
  (30055,2,2.86,0.72),
  (30055,3,2.66,0.67),
  (30055,4,2.86,0.72),
  (30055,5,2.66,2.66),
  (30055,6,2.76,2.76),
  (30055,7,2.66,2.66),
  (30055,8,2.76,2.76),
  (30056,1,2.88,0.72),
  (30056,2,3.08,0.77),
  (30056,3,2.88,0.72),
  (30056,4,3.08,0.77),
  (30056,5,2.88,2.88),
  (30056,6,2.98,2.98),
  (30056,7,2.88,2.88),
  (30056,8,2.98,2.98),
  (30057,1,3.10,0.78),
  (30057,2,3.30,0.83),
  (30057,3,3.10,0.78),
  (30057,4,3.30,0.83),
  (30057,5,3.10,3.10),
  (30057,6,3.20,3.20),
  (30057,7,3.10,3.10),
  (30057,8,3.20,3.20),
  (30058,1,3.32,0.83),
  (30058,2,3.52,0.88),
  (30058,3,3.32,0.83),
  (30058,4,3.52,0.88),
  (30058,5,3.32,3.32),
  (30058,6,3.42,3.42),
  (30058,7,3.32,3.32),
  (30058,8,3.42,3.42),
  (30059,1,3.03,0.76),
  (30059,2,3.23,0.81),
  (30059,3,3.03,0.76),
  (30059,4,3.23,0.81),
  (30059,5,3.13,3.13),
  (30059,6,3.23,3.23),
  (30059,7,3.13,3.13),
  (30059,8,3.23,3.23),
  (30060,1,3.25,0.81),
  (30060,2,3.45,0.86),
  (30060,3,3.25,0.81),
  (30060,4,3.45,0.86),
  (30060,5,3.35,3.35),
  (30060,6,3.45,3.45),
  (30060,7,3.35,3.35),
  (30060,8,3.45,3.45),
  (30061,1,3.47,0.87),
  (30061,2,3.67,0.92),
  (30061,3,3.47,0.87),
  (30061,4,3.67,0.92),
  (30061,5,3.57,3.57),
  (30061,6,3.67,3.67),
  (30061,7,3.57,3.57),
  (30061,8,3.67,3.67),
  (30062,1,2.96,0.74),
  (30062,2,3.16,0.79),
  (30062,3,2.96,0.74),
  (30062,4,3.16,0.79),
  (30062,5,2.96,2.96),
  (30062,6,3.06,3.06),
  (30062,7,2.96,2.96),
  (30062,8,3.06,3.06),
  (30063,1,3.18,0.80),
  (30063,2,3.38,0.85),
  (30063,3,3.18,0.80),
  (30063,4,3.38,0.85),
  (30063,5,3.18,3.18),
  (30063,6,3.28,3.28),
  (30063,7,3.18,3.18),
  (30063,8,3.28,3.28),
  (30064,1,3.40,0.85),
  (30064,2,3.60,0.90),
  (30064,3,3.40,0.85),
  (30064,4,3.60,0.90),
  (30064,5,3.40,3.40),
  (30064,6,3.50,3.50),
  (30064,7,3.40,3.40),
  (30064,8,3.50,3.50),
  (30065,1,3.62,0.91),
  (30065,2,3.82,0.96),
  (30065,3,3.62,0.91),
  (30065,4,3.82,0.96),
  (30065,5,3.62,3.62),
  (30065,6,3.72,3.72),
  (30065,7,3.62,3.62),
  (30065,8,3.72,3.72),
  (30066,1,2.99,0.75),
  (30066,2,3.19,0.80),
  (30066,3,2.99,0.75),
  (30066,4,3.19,0.80),
  (30066,5,3.09,3.09),
  (30066,6,3.19,3.19),
  (30066,7,3.09,3.09),
  (30066,8,3.19,3.19),
  (30067,1,3.43,0.86),
  (30067,2,3.63,0.91),
  (30067,3,3.43,0.86),
  (30067,4,3.63,0.91),
  (30067,5,3.53,3.53),
  (30067,6,3.63,3.63),
  (30067,7,3.53,3.53),
  (30067,8,3.63,3.63),
  (30068,1,3.65,0.91),
  (30068,2,3.85,0.96),
  (30068,3,3.65,0.91),
  (30068,4,3.85,0.96),
  (30068,5,3.75,3.75),
  (30068,6,3.85,3.85),
  (30068,7,3.75,3.75),
  (30068,8,3.85,3.85),
  (30069,1,3.14,0.79),
  (30069,2,3.34,0.84),
  (30069,3,3.14,0.79),
  (30069,4,3.34,0.84),
  (30069,5,3.14,3.14),
  (30069,6,3.24,3.24),
  (30069,7,3.14,3.14),
  (30069,8,3.24,3.24),
  (30070,1,3.36,0.84),
  (30070,2,3.56,0.89),
  (30070,3,3.36,0.84),
  (30070,4,3.56,0.89),
  (30070,5,3.36,3.36),
  (30070,6,3.46,3.46),
  (30070,7,3.36,3.36),
  (30070,8,3.46,3.46),
  (30071,1,3.58,0.90),
  (30071,2,3.78,0.95),
  (30071,3,3.58,0.90),
  (30071,4,3.78,0.95),
  (30071,5,3.58,3.58),
  (30071,6,3.68,3.68),
  (30071,7,3.58,3.58),
  (30071,8,3.68,3.68),
  (30072,1,3.80,0.95),
  (30072,2,4.00,1.00),
  (30072,3,3.80,0.95),
  (30072,4,4.00,1.00),
  (30072,5,3.80,3.80),
  (30072,6,3.90,3.90),
  (30072,7,3.80,3.80),
  (30072,8,3.90,3.90),
  (30073,1,3.51,0.88),
  (30073,2,3.71,0.93),
  (30073,3,3.51,0.88),
  (30073,4,3.71,0.93),
  (30073,5,3.61,3.61),
  (30073,6,3.71,3.71),
  (30073,7,3.61,3.61),
  (30073,8,3.71,3.71),
  (30074,1,3.73,0.93),
  (30074,2,3.93,0.98),
  (30074,3,3.73,0.93),
  (30074,4,3.93,0.98),
  (30074,5,3.83,3.83),
  (30074,6,3.93,3.93),
  (30074,7,3.83,3.83),
  (30074,8,3.93,3.93),
  (30075,1,3.90,0.98),
  (30075,2,4.00,1.00),
  (30075,3,3.90,0.98),
  (30075,4,4.00,1.00),
  (30075,5,3.95,3.95),
  (30075,6,4.00,4.00),
  (30075,7,3.95,3.95),
  (30075,8,4.00,4.00),
  (30076,1,2.55,0.64),
  (30076,2,2.75,0.69),
  (30076,3,2.55,0.64),
  (30076,4,2.75,0.69),
  (30076,5,2.55,2.55),
  (30076,6,2.65,2.65),
  (30076,7,2.55,2.55),
  (30076,8,2.65,2.65),
  (30077,1,2.77,0.69),
  (30077,2,2.97,0.74),
  (30077,3,2.77,0.69),
  (30077,4,2.97,0.74),
  (30077,5,2.77,2.77),
  (30077,6,2.87,2.87),
  (30077,7,2.77,2.77),
  (30077,8,2.87,2.87),
  (30078,1,2.99,0.75),
  (30078,2,3.19,0.80),
  (30078,3,2.99,0.75),
  (30078,4,3.19,0.80),
  (30078,5,2.99,2.99),
  (30078,6,3.09,3.09),
  (30078,7,2.99,2.99),
  (30078,8,3.09,3.09),
  (30079,1,3.21,0.80),
  (30079,2,3.41,0.85),
  (30079,3,3.21,0.80),
  (30079,4,3.41,0.85),
  (30079,5,3.21,3.21),
  (30079,6,3.31,3.31),
  (30079,7,3.21,3.21),
  (30079,8,3.31,3.31),
  (30080,1,2.70,0.68),
  (30080,2,2.90,0.73),
  (30080,3,2.70,0.68),
  (30080,4,2.90,0.73),
  (30080,5,2.80,2.80),
  (30080,6,2.90,2.90),
  (30080,7,2.80,2.80),
  (30080,8,2.90,2.90),
  (30081,1,2.92,0.73),
  (30081,2,3.12,0.78),
  (30081,3,2.92,0.73),
  (30081,4,3.12,0.78),
  (30081,5,3.02,3.02),
  (30081,6,3.12,3.12),
  (30081,7,3.02,3.02),
  (30081,8,3.12,3.12),
  (30082,1,3.14,0.79),
  (30082,2,3.34,0.84),
  (30082,3,3.14,0.79),
  (30082,4,3.34,0.84),
  (30082,5,3.24,3.24),
  (30082,6,3.34,3.34),
  (30082,7,3.24,3.24),
  (30082,8,3.34,3.34),
  (30083,1,3.36,0.84),
  (30083,2,3.56,0.89),
  (30083,3,3.36,0.84),
  (30083,4,3.56,0.89),
  (30083,5,3.46,3.46),
  (30083,6,3.56,3.56),
  (30083,7,3.46,3.46),
  (30083,8,3.56,3.56),
  (30084,1,2.85,0.71),
  (30084,2,3.05,0.76),
  (30084,3,2.85,0.71),
  (30084,4,3.05,0.76),
  (30084,5,2.85,2.85),
  (30084,6,2.95,2.95),
  (30084,7,2.85,2.85),
  (30084,8,2.95,2.95),
  (30085,1,3.07,0.77),
  (30085,2,3.27,0.82),
  (30085,3,3.07,0.77),
  (30085,4,3.27,0.82),
  (30085,5,3.07,3.07),
  (30085,6,3.17,3.17),
  (30085,7,3.07,3.07),
  (30085,8,3.17,3.17),
  (30086,1,3.29,0.82),
  (30086,2,3.49,0.87),
  (30086,3,3.29,0.82),
  (30086,4,3.49,0.87),
  (30086,5,3.29,3.29),
  (30086,6,3.39,3.39),
  (30086,7,3.29,3.29),
  (30086,8,3.39,3.39),
  (30087,1,3.51,0.88),
  (30087,2,3.71,0.93),
  (30087,3,3.51,0.88),
  (30087,4,3.71,0.93),
  (30087,5,3.51,3.51),
  (30087,6,3.61,3.61),
  (30087,7,3.51,3.51),
  (30087,8,3.61,3.61),
  (30088,1,3.10,0.78),
  (30088,2,3.30,0.83),
  (30088,3,3.10,0.78),
  (30088,4,3.30,0.83),
  (30088,5,3.20,3.20),
  (30088,6,3.30,3.30),
  (30088,7,3.20,3.20),
  (30088,8,3.30,3.30),
  (30089,1,3.32,0.83),
  (30089,2,3.52,0.88),
  (30089,3,3.32,0.83),
  (30089,4,3.52,0.88),
  (30089,5,3.42,3.42),
  (30089,6,3.52,3.52),
  (30089,7,3.42,3.42),
  (30089,8,3.52,3.52),
  (30090,1,3.54,0.89),
  (30090,2,3.74,0.94),
  (30090,3,3.54,0.89),
  (30090,4,3.74,0.94),
  (30090,5,3.64,3.64),
  (30090,6,3.74,3.74),
  (30090,7,3.64,3.64),
  (30090,8,3.74,3.74),
  (30091,1,3.03,0.76),
  (30091,2,3.23,0.81),
  (30091,3,3.03,0.76),
  (30091,4,3.23,0.81),
  (30091,5,3.03,3.03),
  (30091,6,3.13,3.13),
  (30091,7,3.03,3.03),
  (30091,8,3.13,3.13),
  (30092,1,3.25,0.81),
  (30092,2,3.45,0.86),
  (30092,3,3.25,0.81),
  (30092,4,3.45,0.86),
  (30092,5,3.25,3.25),
  (30092,6,3.35,3.35),
  (30092,7,3.25,3.25),
  (30092,8,3.35,3.35),
  (30093,1,3.47,0.87),
  (30093,2,3.67,0.92),
  (30093,3,3.47,0.87),
  (30093,4,3.67,0.92),
  (30093,5,3.47,3.47),
  (30093,6,3.57,3.57),
  (30093,7,3.47,3.47),
  (30093,8,3.57,3.57),
  (30094,1,3.69,0.92),
  (30094,2,3.89,0.97),
  (30094,3,3.69,0.92),
  (30094,4,3.89,0.97),
  (30094,5,3.69,3.69),
  (30094,6,3.79,3.79),
  (30094,7,3.69,3.69),
  (30094,8,3.79,3.79),
  (30095,1,3.18,0.80),
  (30095,2,3.38,0.85),
  (30095,3,3.18,0.80),
  (30095,4,3.38,0.85),
  (30095,5,3.28,3.28),
  (30095,6,3.38,3.38),
  (30095,7,3.28,3.28),
  (30095,8,3.38,3.38),
  (30096,1,3.40,0.85),
  (30096,2,3.60,0.90),
  (30096,3,3.40,0.85),
  (30096,4,3.60,0.90),
  (30096,5,3.50,3.50),
  (30096,6,3.60,3.60),
  (30096,7,3.50,3.50),
  (30096,8,3.60,3.60),
  (30097,1,3.62,0.91),
  (30097,2,3.82,0.96),
  (30097,3,3.62,0.91),
  (30097,4,3.82,0.96),
  (30097,5,3.72,3.72),
  (30097,6,3.82,3.82),
  (30097,7,3.72,3.72),
  (30097,8,3.82,3.82),
  (30098,1,3.84,0.96),
  (30098,2,4.00,1.00),
  (30098,3,3.84,0.96),
  (30098,4,4.00,1.00),
  (30098,5,3.94,3.94),
  (30098,6,4.00,4.00),
  (30098,7,3.94,3.94),
  (30098,8,4.00,4.00),
  (30099,1,3.21,0.80),
  (30099,2,3.41,0.85),
  (30099,3,3.21,0.80),
  (30099,4,3.41,0.85),
  (30099,5,3.21,3.21),
  (30099,6,3.31,3.31),
  (30099,7,3.21,3.21),
  (30099,8,3.31,3.31),
  (30100,1,3.65,0.91),
  (30100,2,3.85,0.96),
  (30100,3,3.65,0.91),
  (30100,4,3.85,0.96),
  (30100,5,3.65,3.65),
  (30100,6,3.75,3.75),
  (30100,7,3.65,3.65),
  (30100,8,3.75,3.75),
  (30101,1,3.87,0.97),
  (30101,2,4.00,1.00),
  (30101,3,3.87,0.97),
  (30101,4,4.00,1.00),
  (30101,5,3.87,3.87),
  (30101,6,3.97,3.97),
  (30101,7,3.87,3.87),
  (30101,8,3.97,3.97),
  (30102,1,2.81,0.70),
  (30102,2,3.01,0.75),
  (30102,3,2.81,0.70),
  (30102,4,3.01,0.75),
  (30102,5,2.91,2.91),
  (30102,6,3.01,3.01),
  (30102,7,2.91,2.91),
  (30102,8,3.01,3.01),
  (30103,1,3.03,0.76),
  (30103,2,3.23,0.81),
  (30103,3,3.03,0.76),
  (30103,4,3.23,0.81),
  (30103,5,3.13,3.13),
  (30103,6,3.23,3.23),
  (30103,7,3.13,3.13),
  (30103,8,3.23,3.23),
  (30104,1,3.25,0.81),
  (30104,2,3.45,0.86),
  (30104,3,3.25,0.81),
  (30104,4,3.45,0.86),
  (30104,5,3.35,3.35),
  (30104,6,3.45,3.45),
  (30104,7,3.35,3.35),
  (30104,8,3.45,3.45),
  (30105,1,2.74,0.69),
  (30105,2,2.94,0.74),
  (30105,3,2.74,0.69),
  (30105,4,2.94,0.74),
  (30105,5,2.74,2.74),
  (30105,6,2.84,2.84),
  (30105,7,2.74,2.74),
  (30105,8,2.84,2.84),
  (30106,1,2.96,0.74),
  (30106,2,3.16,0.79),
  (30106,3,2.96,0.74),
  (30106,4,3.16,0.79),
  (30106,5,2.96,2.96),
  (30106,6,3.06,3.06),
  (30106,7,2.96,2.96),
  (30106,8,3.06,3.06),
  (30107,1,3.18,0.80),
  (30107,2,3.38,0.85),
  (30107,3,3.18,0.80),
  (30107,4,3.38,0.85),
  (30107,5,3.18,3.18),
  (30107,6,3.28,3.28),
  (30107,7,3.18,3.18),
  (30107,8,3.28,3.28),
  (30108,1,3.40,0.85),
  (30108,2,3.60,0.90),
  (30108,3,3.40,0.85),
  (30108,4,3.60,0.90),
  (30108,5,3.40,3.40),
  (30108,6,3.50,3.50),
  (30108,7,3.40,3.40),
  (30108,8,3.50,3.50),
  (30109,1,2.77,0.69),
  (30109,2,2.97,0.74),
  (30109,3,2.77,0.69),
  (30109,4,2.97,0.74),
  (30109,5,2.87,2.87),
  (30109,6,2.97,2.97),
  (30109,7,2.87,2.87),
  (30109,8,2.97,2.97),
  (30110,1,2.99,0.75),
  (30110,2,3.19,0.80),
  (30110,3,2.99,0.75),
  (30110,4,3.19,0.80),
  (30110,5,3.09,3.09),
  (30110,6,3.19,3.19),
  (30110,7,3.09,3.09),
  (30110,8,3.19,3.19),
  (30111,1,3.21,0.80),
  (30111,2,3.41,0.85),
  (30111,3,3.21,0.80),
  (30111,4,3.41,0.85),
  (30111,5,3.31,3.31),
  (30111,6,3.41,3.41),
  (30111,7,3.31,3.31),
  (30111,8,3.41,3.41),
  (30112,1,3.43,0.86),
  (30112,2,3.63,0.91),
  (30112,3,3.43,0.86),
  (30112,4,3.63,0.91),
  (30112,5,3.53,3.53),
  (30112,6,3.63,3.63),
  (30112,7,3.53,3.53),
  (30112,8,3.63,3.63),
  (30113,1,2.92,0.73),
  (30113,2,3.12,0.78),
  (30113,3,2.92,0.73),
  (30113,4,3.12,0.78),
  (30113,5,2.92,2.92),
  (30113,6,3.02,3.02),
  (30113,7,2.92,2.92),
  (30113,8,3.02,3.02),
  (30114,1,3.14,0.79),
  (30114,2,3.34,0.84),
  (30114,3,3.14,0.79),
  (30114,4,3.34,0.84),
  (30114,5,3.14,3.14),
  (30114,6,3.24,3.24),
  (30114,7,3.14,3.14),
  (30114,8,3.24,3.24),
  (30115,1,3.36,0.84),
  (30115,2,3.56,0.89),
  (30115,3,3.36,0.84),
  (30115,4,3.56,0.89),
  (30115,5,3.36,3.36),
  (30115,6,3.46,3.46),
  (30115,7,3.36,3.36),
  (30115,8,3.46,3.46),
  (30116,1,3.58,0.90),
  (30116,2,3.78,0.95),
  (30116,3,3.58,0.90),
  (30116,4,3.78,0.95),
  (30116,5,3.58,3.58),
  (30116,6,3.68,3.68),
  (30116,7,3.58,3.58),
  (30116,8,3.68,3.68),
  (30117,1,3.29,0.82),
  (30117,2,3.49,0.87),
  (30117,3,3.29,0.82),
  (30117,4,3.49,0.87),
  (30117,5,3.39,3.39),
  (30117,6,3.49,3.49),
  (30117,7,3.39,3.39),
  (30117,8,3.49,3.49),
  (30118,1,3.51,0.88),
  (30118,2,3.71,0.93),
  (30118,3,3.51,0.88),
  (30118,4,3.71,0.93),
  (30118,5,3.61,3.61),
  (30118,6,3.71,3.71),
  (30118,7,3.61,3.61),
  (30118,8,3.71,3.71),
  (30119,1,3.73,0.93),
  (30119,2,3.93,0.98),
  (30119,3,3.73,0.93),
  (30119,4,3.93,0.98),
  (30119,5,3.83,3.83),
  (30119,6,3.93,3.93),
  (30119,7,3.83,3.83),
  (30119,8,3.93,3.93),
  (30120,1,3.10,0.78),
  (30120,2,3.30,0.83),
  (30120,3,3.10,0.78),
  (30120,4,3.30,0.83),
  (30120,5,3.10,3.10),
  (30120,6,3.20,3.20),
  (30120,7,3.10,3.10),
  (30120,8,3.20,3.20),
  (30121,1,3.32,0.83),
  (30121,2,3.52,0.88),
  (30121,3,3.32,0.83),
  (30121,4,3.52,0.88),
  (30121,5,3.32,3.32),
  (30121,6,3.42,3.42),
  (30121,7,3.32,3.32),
  (30121,8,3.42,3.42),
  (30122,1,3.54,0.89),
  (30122,2,3.74,0.94),
  (30122,3,3.54,0.89),
  (30122,4,3.74,0.94),
  (30122,5,3.54,3.54),
  (30122,6,3.64,3.64),
  (30122,7,3.54,3.54),
  (30122,8,3.64,3.64),
  (30123,1,3.76,0.94),
  (30123,2,3.96,0.99),
  (30123,3,3.76,0.94),
  (30123,4,3.96,0.99),
  (30123,5,3.76,3.76),
  (30123,6,3.86,3.86),
  (30123,7,3.76,3.76),
  (30123,8,3.86,3.86),
  (30124,1,3.25,0.81),
  (30124,2,3.45,0.86),
  (30124,3,3.25,0.81),
  (30124,4,3.45,0.86),
  (30124,5,3.35,3.35),
  (30124,6,3.45,3.45),
  (30124,7,3.35,3.35),
  (30124,8,3.45,3.45),
  (30125,1,3.47,0.87),
  (30125,2,3.67,0.92),
  (30125,3,3.47,0.87),
  (30125,4,3.67,0.92),
  (30125,5,3.57,3.57),
  (30125,6,3.67,3.67),
  (30125,7,3.57,3.57),
  (30125,8,3.67,3.67),
  (30126,1,3.69,0.92),
  (30126,2,3.89,0.97),
  (30126,3,3.69,0.92),
  (30126,4,3.89,0.97),
  (30126,5,3.79,3.79),
  (30126,6,3.89,3.89),
  (30126,7,3.79,3.79),
  (30126,8,3.89,3.89),
  (30127,1,3.90,0.98),
  (30127,2,4.00,1.00),
  (30127,3,3.90,0.98),
  (30127,4,4.00,1.00),
  (30127,5,3.95,3.95),
  (30127,6,4.00,4.00),
  (30127,7,3.95,3.95),
  (30127,8,4.00,4.00),
  (30128,1,2.63,0.66),
  (30128,2,2.83,0.71),
  (30128,3,2.63,0.66),
  (30128,4,2.83,0.71),
  (30128,5,2.63,2.63),
  (30128,6,2.73,2.73),
  (30128,7,2.63,2.63),
  (30128,8,2.73,2.73),
  (30129,1,2.85,0.71),
  (30129,2,3.05,0.76),
  (30129,3,2.85,0.71),
  (30129,4,3.05,0.76),
  (30129,5,2.85,2.85),
  (30129,6,2.95,2.95),
  (30129,7,2.85,2.85),
  (30129,8,2.95,2.95),
  (30130,1,3.07,0.77),
  (30130,2,3.27,0.82),
  (30130,3,3.07,0.77),
  (30130,4,3.27,0.82),
  (30130,5,3.07,3.07),
  (30130,6,3.17,3.17),
  (30130,7,3.07,3.07),
  (30130,8,3.17,3.17),
  (30131,1,3.29,0.82),
  (30131,2,3.49,0.87),
  (30131,3,3.29,0.82),
  (30131,4,3.49,0.87),
  (30131,5,3.29,3.29),
  (30131,6,3.39,3.39),
  (30131,7,3.29,3.29),
  (30131,8,3.39,3.39),
  (30132,1,3.10,0.78),
  (30132,2,3.30,0.83),
  (30132,3,3.10,0.78),
  (30132,4,3.30,0.83),
  (30132,5,3.20,3.20),
  (30132,6,3.30,3.30),
  (30132,7,3.20,3.20),
  (30132,8,3.30,3.30),
  (30133,1,3.32,0.83),
  (30133,2,3.52,0.88),
  (30133,3,3.32,0.83),
  (30133,4,3.52,0.88),
  (30133,5,3.42,3.42),
  (30133,6,3.52,3.52),
  (30133,7,3.42,3.42),
  (30133,8,3.52,3.52),
  (30134,1,2.81,0.70),
  (30134,2,3.01,0.75),
  (30134,3,2.81,0.70),
  (30134,4,3.01,0.75),
  (30134,5,2.81,2.81),
  (30134,6,2.91,2.91),
  (30134,7,2.81,2.81),
  (30134,8,2.91,2.91),
  (30135,1,3.03,0.76),
  (30135,2,3.23,0.81),
  (30135,3,3.03,0.76),
  (30135,4,3.23,0.81),
  (30135,5,3.03,3.03),
  (30135,6,3.13,3.13),
  (30135,7,3.03,3.03),
  (30135,8,3.13,3.13),
  (30136,1,3.25,0.81),
  (30136,2,3.45,0.86),
  (30136,3,3.25,0.81),
  (30136,4,3.45,0.86),
  (30136,5,3.25,3.25),
  (30136,6,3.35,3.35),
  (30136,7,3.25,3.25),
  (30136,8,3.35,3.35),
  (30137,1,3.47,0.87),
  (30137,2,3.67,0.92),
  (30137,3,3.47,0.87),
  (30137,4,3.67,0.92),
  (30137,5,3.47,3.47),
  (30137,6,3.57,3.57),
  (30137,7,3.47,3.47),
  (30137,8,3.57,3.57),
  (30138,1,2.96,0.74),
  (30138,2,3.16,0.79),
  (30138,3,2.96,0.74),
  (30138,4,3.16,0.79),
  (30138,5,3.06,3.06),
  (30138,6,3.16,3.16),
  (30138,7,3.06,3.06),
  (30138,8,3.16,3.16),
  (30139,1,3.18,0.80),
  (30139,2,3.38,0.85),
  (30139,3,3.18,0.80),
  (30139,4,3.38,0.85),
  (30139,5,3.28,3.28),
  (30139,6,3.38,3.38),
  (30139,7,3.28,3.28),
  (30139,8,3.38,3.38),
  (30140,1,3.40,0.85),
  (30140,2,3.60,0.90),
  (30140,3,3.40,0.85),
  (30140,4,3.60,0.90),
  (30140,5,3.50,3.50),
  (30140,6,3.60,3.60),
  (30140,7,3.50,3.50),
  (30140,8,3.60,3.60),
  (30141,1,3.62,0.91),
  (30141,2,3.82,0.96),
  (30141,3,3.62,0.91),
  (30141,4,3.82,0.96),
  (30141,5,3.72,3.72),
  (30141,6,3.82,3.82),
  (30141,7,3.72,3.72),
  (30141,8,3.82,3.82),
  (30142,1,2.99,0.75),
  (30142,2,3.19,0.80),
  (30142,3,2.99,0.75),
  (30142,4,3.19,0.80),
  (30142,5,2.99,2.99),
  (30142,6,3.09,3.09),
  (30142,7,2.99,2.99),
  (30142,8,3.09,3.09),
  (30143,1,3.21,0.80),
  (30143,2,3.41,0.85),
  (30143,3,3.21,0.80),
  (30143,4,3.41,0.85),
  (30143,5,3.21,3.21),
  (30143,6,3.31,3.31),
  (30143,7,3.21,3.21),
  (30143,8,3.31,3.31),
  (30144,1,3.43,0.86),
  (30144,2,3.63,0.91),
  (30144,3,3.43,0.86),
  (30144,4,3.63,0.91),
  (30144,5,3.43,3.43),
  (30144,6,3.53,3.53),
  (30144,7,3.43,3.43),
  (30144,8,3.53,3.53),
  (30145,1,3.65,0.91),
  (30145,2,3.85,0.96),
  (30145,3,3.65,0.91),
  (30145,4,3.85,0.96),
  (30145,5,3.65,3.65),
  (30145,6,3.75,3.75),
  (30145,7,3.65,3.65),
  (30145,8,3.75,3.75),
  (30146,1,3.36,0.84),
  (30146,2,3.56,0.89),
  (30146,3,3.36,0.84),
  (30146,4,3.56,0.89),
  (30146,5,3.46,3.46),
  (30146,6,3.56,3.56),
  (30146,7,3.46,3.46),
  (30146,8,3.56,3.56),
  (30147,1,3.58,0.90),
  (30147,2,3.78,0.95),
  (30147,3,3.58,0.90),
  (30147,4,3.78,0.95),
  (30147,5,3.68,3.68),
  (30147,6,3.78,3.78),
  (30147,7,3.68,3.68),
  (30147,8,3.78,3.78),
  (30148,1,3.80,0.95),
  (30148,2,4.00,1.00),
  (30148,3,3.80,0.95),
  (30148,4,4.00,1.00),
  (30148,5,3.90,3.90),
  (30148,6,4.00,4.00),
  (30148,7,3.90,3.90),
  (30148,8,4.00,4.00),
  (30149,1,3.29,0.82),
  (30149,2,3.49,0.87),
  (30149,3,3.29,0.82),
  (30149,4,3.49,0.87),
  (30149,5,3.29,3.29),
  (30149,6,3.39,3.39),
  (30149,7,3.29,3.29),
  (30149,8,3.39,3.39),
  (30150,1,3.51,0.88),
  (30150,2,3.71,0.93),
  (30150,3,3.51,0.88),
  (30150,4,3.71,0.93),
  (30150,5,3.51,3.51),
  (30150,6,3.61,3.61),
  (30150,7,3.51,3.51),
  (30150,8,3.61,3.61),
  (30151,1,3.73,0.93),
  (30151,2,3.93,0.98),
  (30151,3,3.73,0.93),
  (30151,4,3.93,0.98),
  (30151,5,3.73,3.73),
  (30151,6,3.83,3.83),
  (30151,7,3.73,3.73),
  (30151,8,3.83,3.83),
  (30152,1,3.90,0.98),
  (30152,2,4.00,1.00),
  (30152,3,3.90,0.98),
  (30152,4,4.00,1.00),
  (30152,5,3.90,3.90),
  (30152,6,4.00,4.00),
  (30152,7,3.90,3.90),
  (30152,8,4.00,4.00),
  (30153,1,2.55,0.64),
  (30153,2,2.75,0.69),
  (30153,3,2.55,0.64),
  (30153,4,2.75,0.69),
  (30153,5,2.65,2.65),
  (30153,6,2.75,2.75),
  (30153,7,2.65,2.65),
  (30153,8,2.75,2.75),
  (30154,1,2.77,0.69),
  (30154,2,2.97,0.74),
  (30154,3,2.77,0.69),
  (30154,4,2.97,0.74),
  (30154,5,2.87,2.87),
  (30154,6,2.97,2.97),
  (30154,7,2.87,2.87),
  (30154,8,2.97,2.97),
  (30155,1,2.99,0.75),
  (30155,2,3.19,0.80),
  (30155,3,2.99,0.75),
  (30155,4,3.19,0.80),
  (30155,5,3.09,3.09),
  (30155,6,3.19,3.19),
  (30155,7,3.09,3.09),
  (30155,8,3.19,3.19),
  (30156,1,3.21,0.80),
  (30156,2,3.41,0.85),
  (30156,3,3.21,0.80),
  (30156,4,3.41,0.85),
  (30156,5,3.31,3.31),
  (30156,6,3.41,3.41),
  (30156,7,3.31,3.31),
  (30156,8,3.41,3.41),
  (30157,1,2.70,0.68),
  (30157,2,2.90,0.73),
  (30157,3,2.70,0.68),
  (30157,4,2.90,0.73),
  (30157,5,2.70,2.70),
  (30157,6,2.80,2.80),
  (30157,7,2.70,2.70),
  (30157,8,2.80,2.80),
  (30158,1,2.92,0.73),
  (30158,2,3.12,0.78),
  (30158,3,2.92,0.73),
  (30158,4,3.12,0.78),
  (30158,5,2.92,2.92),
  (30158,6,3.02,3.02),
  (30158,7,2.92,2.92),
  (30158,8,3.02,3.02),
  (30159,1,3.14,0.79),
  (30159,2,3.34,0.84),
  (30159,3,3.14,0.79),
  (30159,4,3.34,0.84),
  (30159,5,3.14,3.14),
  (30159,6,3.24,3.24),
  (30159,7,3.14,3.14),
  (30159,8,3.24,3.24),
  (30160,1,3.36,0.84),
  (30160,2,3.56,0.89),
  (30160,3,3.36,0.84),
  (30160,4,3.56,0.89),
  (30160,5,3.36,3.36),
  (30160,6,3.46,3.46),
  (30160,7,3.36,3.36),
  (30160,8,3.46,3.46),
  (30161,1,3.07,0.77),
  (30161,2,3.27,0.82),
  (30161,3,3.07,0.77),
  (30161,4,3.27,0.82),
  (30161,5,3.17,3.17),
  (30161,6,3.27,3.27),
  (30161,7,3.17,3.17),
  (30161,8,3.27,3.27),
  (30162,1,3.29,0.82),
  (30162,2,3.49,0.87),
  (30162,3,3.29,0.82),
  (30162,4,3.49,0.87),
  (30162,5,3.39,3.39),
  (30162,6,3.49,3.49),
  (30162,7,3.39,3.39),
  (30162,8,3.49,3.49),
  (30163,1,3.51,0.88),
  (30163,2,3.71,0.93),
  (30163,3,3.51,0.88),
  (30163,4,3.71,0.93),
  (30163,5,3.61,3.61),
  (30163,6,3.71,3.71),
  (30163,7,3.61,3.61),
  (30163,8,3.71,3.71),
  (30164,1,2.88,0.72),
  (30164,2,3.08,0.77),
  (30164,3,2.88,0.72),
  (30164,4,3.08,0.77),
  (30164,5,2.88,2.88),
  (30164,6,2.98,2.98),
  (30164,7,2.88,2.88),
  (30164,8,2.98,2.98),
  (30165,1,3.32,0.83),
  (30165,2,3.52,0.88),
  (30165,3,3.32,0.83),
  (30165,4,3.52,0.88),
  (30165,5,3.32,3.32),
  (30165,6,3.42,3.42),
  (30165,7,3.32,3.32),
  (30165,8,3.42,3.42),
  (30166,1,3.54,0.89),
  (30166,2,3.74,0.94),
  (30166,3,3.54,0.89),
  (30166,4,3.74,0.94),
  (30166,5,3.54,3.54),
  (30166,6,3.64,3.64),
  (30166,7,3.54,3.54),
  (30166,8,3.64,3.64),
  (30167,1,3.03,0.76),
  (30167,2,3.23,0.81),
  (30167,3,3.03,0.76),
  (30167,4,3.23,0.81),
  (30167,5,3.13,3.13),
  (30167,6,3.23,3.23),
  (30167,7,3.13,3.13),
  (30167,8,3.23,3.23),
  (30168,1,3.25,0.81),
  (30168,2,3.45,0.86),
  (30168,3,3.25,0.81),
  (30168,4,3.45,0.86),
  (30168,5,3.35,3.35),
  (30168,6,3.45,3.45),
  (30168,7,3.35,3.35),
  (30168,8,3.45,3.45),
  (30169,1,3.47,0.87),
  (30169,2,3.67,0.92),
  (30169,3,3.47,0.87),
  (30169,4,3.67,0.92),
  (30169,5,3.57,3.57),
  (30169,6,3.67,3.67),
  (30169,7,3.57,3.57),
  (30169,8,3.67,3.67),
  (30170,1,3.69,0.92),
  (30170,2,3.89,0.97),
  (30170,3,3.69,0.92),
  (30170,4,3.89,0.97),
  (30170,5,3.79,3.79),
  (30170,6,3.89,3.89),
  (30170,7,3.79,3.79),
  (30170,8,3.89,3.89),
  (30171,1,3.18,0.80),
  (30171,2,3.38,0.85),
  (30171,3,3.18,0.80),
  (30171,4,3.38,0.85),
  (30171,5,3.18,3.18),
  (30171,6,3.28,3.28),
  (30171,7,3.18,3.18),
  (30171,8,3.28,3.28),
  (30172,1,3.40,0.85),
  (30172,2,3.60,0.90),
  (30172,3,3.40,0.85),
  (30172,4,3.60,0.90),
  (30172,5,3.40,3.40),
  (30172,6,3.50,3.50),
  (30172,7,3.40,3.40),
  (30172,8,3.50,3.50),
  (30173,1,3.62,0.91),
  (30173,2,3.82,0.96),
  (30173,3,3.62,0.91),
  (30173,4,3.82,0.96),
  (30173,5,3.62,3.62),
  (30173,6,3.72,3.72),
  (30173,7,3.62,3.62),
  (30173,8,3.72,3.72),
  (30174,1,3.84,0.96),
  (30174,2,4.00,1.00),
  (30174,3,3.84,0.96),
  (30174,4,4.00,1.00),
  (30174,5,3.84,3.84),
  (30174,6,3.94,3.94),
  (30174,7,3.84,3.84),
  (30174,8,3.94,3.94),
  (30175,1,3.43,0.86),
  (30175,2,3.63,0.91),
  (30175,3,3.43,0.86),
  (30175,4,3.63,0.91),
  (30175,5,3.53,3.53),
  (30175,6,3.63,3.63),
  (30175,7,3.53,3.53),
  (30175,8,3.63,3.63),
  (30176,1,3.65,0.91),
  (30176,2,3.85,0.96),
  (30176,3,3.65,0.91),
  (30176,4,3.85,0.96),
  (30176,5,3.75,3.75),
  (30176,6,3.85,3.85),
  (30176,7,3.75,3.75),
  (30176,8,3.85,3.85),
  (30177,1,3.87,0.97),
  (30177,2,4.00,1.00),
  (30177,3,3.87,0.97),
  (30177,4,4.00,1.00),
  (30177,5,3.95,3.95),
  (30177,6,4.00,4.00),
  (30177,7,3.95,3.95),
  (30177,8,4.00,4.00),
  (30178,1,2.59,0.65),
  (30178,2,2.79,0.70),
  (30178,3,2.59,0.65),
  (30178,4,2.79,0.70),
  (30178,5,2.59,2.59),
  (30178,6,2.69,2.69),
  (30178,7,2.59,2.59),
  (30178,8,2.69,2.69),
  (30179,1,2.81,0.70),
  (30179,2,3.01,0.75),
  (30179,3,2.81,0.70),
  (30179,4,3.01,0.75),
  (30179,5,2.81,2.81),
  (30179,6,2.91,2.91),
  (30179,7,2.81,2.81),
  (30179,8,2.91,2.91),
  (30180,1,3.03,0.76),
  (30180,2,3.23,0.81),
  (30180,3,3.03,0.76),
  (30180,4,3.23,0.81),
  (30180,5,3.03,3.03),
  (30180,6,3.13,3.13),
  (30180,7,3.03,3.03),
  (30180,8,3.13,3.13),
  (30181,1,3.25,0.81),
  (30181,2,3.45,0.86),
  (30181,3,3.25,0.81),
  (30181,4,3.45,0.86),
  (30181,5,3.25,3.25),
  (30181,6,3.35,3.35),
  (30181,7,3.25,3.25),
  (30181,8,3.35,3.35),
  (30182,1,2.74,0.69),
  (30182,2,2.94,0.74),
  (30182,3,2.74,0.69),
  (30182,4,2.94,0.74),
  (30182,5,2.84,2.84),
  (30182,6,2.94,2.94),
  (30182,7,2.84,2.84),
  (30182,8,2.94,2.94),
  (30183,1,2.96,0.74),
  (30183,2,3.16,0.79),
  (30183,3,2.96,0.74),
  (30183,4,3.16,0.79),
  (30183,5,3.06,3.06),
  (30183,6,3.16,3.16),
  (30183,7,3.06,3.06),
  (30183,8,3.16,3.16),
  (30184,1,3.18,0.80),
  (30184,2,3.38,0.85),
  (30184,3,3.18,0.80),
  (30184,4,3.38,0.85),
  (30184,5,3.28,3.28),
  (30184,6,3.38,3.38),
  (30184,7,3.28,3.28),
  (30184,8,3.38,3.38),
  (30185,1,3.40,0.85),
  (30185,2,3.60,0.90),
  (30185,3,3.40,0.85),
  (30185,4,3.60,0.90),
  (30185,5,3.50,3.50),
  (30185,6,3.60,3.60),
  (30185,7,3.50,3.50),
  (30185,8,3.60,3.60),
  (30186,1,2.77,0.69),
  (30186,2,2.97,0.74),
  (30186,3,2.77,0.69),
  (30186,4,2.97,0.74),
  (30186,5,2.77,2.77),
  (30186,6,2.87,2.87),
  (30186,7,2.77,2.77),
  (30186,8,2.87,2.87),
  (30187,1,2.99,0.75),
  (30187,2,3.19,0.80),
  (30187,3,2.99,0.75),
  (30187,4,3.19,0.80),
  (30187,5,2.99,2.99),
  (30187,6,3.09,3.09),
  (30187,7,2.99,2.99),
  (30187,8,3.09,3.09),
  (30188,1,3.21,0.80),
  (30188,2,3.41,0.85),
  (30188,3,3.21,0.80),
  (30188,4,3.41,0.85),
  (30188,5,3.21,3.21),
  (30188,6,3.31,3.31),
  (30188,7,3.21,3.21),
  (30188,8,3.31,3.31),
  (30189,1,3.43,0.86),
  (30189,2,3.63,0.91),
  (30189,3,3.43,0.86),
  (30189,4,3.63,0.91),
  (30189,5,3.43,3.43),
  (30189,6,3.53,3.53),
  (30189,7,3.43,3.43),
  (30189,8,3.53,3.53),
  (30190,1,3.14,0.79),
  (30190,2,3.34,0.84),
  (30190,3,3.14,0.79),
  (30190,4,3.34,0.84),
  (30190,5,3.24,3.24),
  (30190,6,3.34,3.34),
  (30190,7,3.24,3.24),
  (30190,8,3.34,3.34),
  (30191,1,3.36,0.84),
  (30191,2,3.56,0.89),
  (30191,3,3.36,0.84),
  (30191,4,3.56,0.89),
  (30191,5,3.46,3.46),
  (30191,6,3.56,3.56),
  (30191,7,3.46,3.46),
  (30191,8,3.56,3.56),
  (30192,1,3.58,0.90),
  (30192,2,3.78,0.95),
  (30192,3,3.58,0.90),
  (30192,4,3.78,0.95),
  (30192,5,3.68,3.68),
  (30192,6,3.78,3.78),
  (30192,7,3.68,3.68),
  (30192,8,3.78,3.78),
  (30193,1,3.07,0.77),
  (30193,2,3.27,0.82),
  (30193,3,3.07,0.77),
  (30193,4,3.27,0.82),
  (30193,5,3.07,3.07),
  (30193,6,3.17,3.17),
  (30193,7,3.07,3.07),
  (30193,8,3.17,3.17),
  (30194,1,3.29,0.82),
  (30194,2,3.49,0.87),
  (30194,3,3.29,0.82),
  (30194,4,3.49,0.87),
  (30194,5,3.29,3.29),
  (30194,6,3.39,3.39),
  (30194,7,3.29,3.29),
  (30194,8,3.39,3.39),
  (30195,1,3.51,0.88),
  (30195,2,3.71,0.93),
  (30195,3,3.51,0.88),
  (30195,4,3.71,0.93),
  (30195,5,3.51,3.51),
  (30195,6,3.61,3.61),
  (30195,7,3.51,3.51),
  (30195,8,3.61,3.61),
  (30196,1,3.73,0.93),
  (30196,2,3.93,0.98),
  (30196,3,3.73,0.93),
  (30196,4,3.93,0.98),
  (30196,5,3.73,3.73),
  (30196,6,3.83,3.83),
  (30196,7,3.73,3.73),
  (30196,8,3.83,3.83),
  (30197,1,3.10,0.78),
  (30197,2,3.30,0.83),
  (30197,3,3.10,0.78),
  (30197,4,3.30,0.83),
  (30197,5,3.20,3.20),
  (30197,6,3.30,3.30),
  (30197,7,3.20,3.20),
  (30197,8,3.30,3.30),
  (30198,1,3.54,0.89),
  (30198,2,3.74,0.94),
  (30198,3,3.54,0.89),
  (30198,4,3.74,0.94),
  (30198,5,3.64,3.64),
  (30198,6,3.74,3.74),
  (30198,7,3.64,3.64),
  (30198,8,3.74,3.74),
  (30199,1,3.76,0.94),
  (30199,2,3.96,0.99),
  (30199,3,3.76,0.94),
  (30199,4,3.96,0.99),
  (30199,5,3.86,3.86),
  (30199,6,3.96,3.96),
  (30199,7,3.86,3.86),
  (30199,8,3.96,3.96),
  (30200,1,3.25,0.81),
  (30200,2,3.45,0.86),
  (30200,3,3.25,0.81),
  (30200,4,3.45,0.86),
  (30200,5,3.25,3.25),
  (30200,6,3.35,3.35),
  (30200,7,3.25,3.25),
  (30200,8,3.35,3.35),
  (30201,1,3.47,0.87),
  (30201,2,3.67,0.92),
  (30201,3,3.47,0.87),
  (30201,4,3.67,0.92),
  (30201,5,3.47,3.47),
  (30201,6,3.57,3.57),
  (30201,7,3.47,3.47),
  (30201,8,3.57,3.57),
  (30202,1,3.69,0.92),
  (30202,2,3.89,0.97),
  (30202,3,3.69,0.92),
  (30202,4,3.89,0.97),
  (30202,5,3.69,3.69),
  (30202,6,3.79,3.79),
  (30202,7,3.69,3.69),
  (30202,8,3.79,3.79),
  (30203,1,3.90,0.98),
  (30203,2,4.00,1.00),
  (30203,3,3.90,0.98),
  (30203,4,4.00,1.00),
  (30203,5,3.90,3.90),
  (30203,6,4.00,4.00),
  (30203,7,3.90,3.90),
  (30203,8,4.00,4.00),
  (30204,1,2.85,0.71),
  (30204,2,3.05,0.76),
  (30204,3,2.85,0.71),
  (30204,4,3.05,0.76),
  (30204,5,2.95,2.95),
  (30204,6,3.05,3.05),
  (30204,7,2.95,2.95),
  (30204,8,3.05,3.05),
  (30205,1,3.07,0.77),
  (30205,2,3.27,0.82),
  (30205,3,3.07,0.77),
  (30205,4,3.27,0.82),
  (30205,5,3.17,3.17),
  (30205,6,3.27,3.27),
  (30205,7,3.17,3.17),
  (30205,8,3.27,3.27),
  (30206,1,3.29,0.82),
  (30206,2,3.49,0.87),
  (30206,3,3.29,0.82),
  (30206,4,3.49,0.87),
  (30206,5,3.39,3.39),
  (30206,6,3.49,3.49),
  (30206,7,3.39,3.39),
  (30206,8,3.49,3.49),
  (30207,1,2.66,0.67),
  (30207,2,2.86,0.72),
  (30207,3,2.66,0.67),
  (30207,4,2.86,0.72),
  (30207,5,2.66,2.66),
  (30207,6,2.76,2.76),
  (30207,7,2.66,2.66),
  (30207,8,2.76,2.76),
  (30208,1,2.88,0.72),
  (30208,2,3.08,0.77),
  (30208,3,2.88,0.72),
  (30208,4,3.08,0.77),
  (30208,5,2.88,2.88),
  (30208,6,2.98,2.98),
  (30208,7,2.88,2.88),
  (30208,8,2.98,2.98),
  (30209,1,3.10,0.78),
  (30209,2,3.30,0.83),
  (30209,3,3.10,0.78),
  (30209,4,3.30,0.83),
  (30209,5,3.10,3.10),
  (30209,6,3.20,3.20),
  (30209,7,3.10,3.10),
  (30209,8,3.20,3.20),
  (30210,1,3.32,0.83),
  (30210,2,3.52,0.88),
  (30210,3,3.32,0.83),
  (30210,4,3.52,0.88),
  (30210,5,3.32,3.32),
  (30210,6,3.42,3.42),
  (30210,7,3.32,3.32),
  (30210,8,3.42,3.42),
  (30211,1,2.81,0.70),
  (30211,2,3.01,0.75),
  (30211,3,2.81,0.70),
  (30211,4,3.01,0.75),
  (30211,5,2.91,2.91),
  (30211,6,3.01,3.01),
  (30211,7,2.91,2.91),
  (30211,8,3.01,3.01),
  (30212,1,3.03,0.76),
  (30212,2,3.23,0.81),
  (30212,3,3.03,0.76),
  (30212,4,3.23,0.81),
  (30212,5,3.13,3.13),
  (30212,6,3.23,3.23),
  (30212,7,3.13,3.13),
  (30212,8,3.23,3.23),
  (30213,1,3.25,0.81),
  (30213,2,3.45,0.86),
  (30213,3,3.25,0.81),
  (30213,4,3.45,0.86),
  (30213,5,3.35,3.35),
  (30213,6,3.45,3.45),
  (30213,7,3.35,3.35),
  (30213,8,3.45,3.45),
  (30214,1,3.47,0.87),
  (30214,2,3.67,0.92),
  (30214,3,3.47,0.87),
  (30214,4,3.67,0.92),
  (30214,5,3.57,3.57),
  (30214,6,3.67,3.67),
  (30214,7,3.57,3.57),
  (30214,8,3.67,3.67),
  (30215,1,2.96,0.74),
  (30215,2,3.16,0.79),
  (30215,3,2.96,0.74),
  (30215,4,3.16,0.79),
  (30215,5,2.96,2.96),
  (30215,6,3.06,3.06),
  (30215,7,2.96,2.96),
  (30215,8,3.06,3.06),
  (30216,1,3.18,0.80),
  (30216,2,3.38,0.85),
  (30216,3,3.18,0.80),
  (30216,4,3.38,0.85),
  (30216,5,3.18,3.18),
  (30216,6,3.28,3.28),
  (30216,7,3.18,3.18),
  (30216,8,3.28,3.28),
  (30217,1,3.40,0.85),
  (30217,2,3.60,0.90),
  (30217,3,3.40,0.85),
  (30217,4,3.60,0.90),
  (30217,5,3.40,3.40),
  (30217,6,3.50,3.50),
  (30217,7,3.40,3.40),
  (30217,8,3.50,3.50),
  (30218,1,3.62,0.91),
  (30218,2,3.82,0.96),
  (30218,3,3.62,0.91),
  (30218,4,3.82,0.96),
  (30218,5,3.62,3.62),
  (30218,6,3.72,3.72),
  (30218,7,3.62,3.62),
  (30218,8,3.72,3.72),
  (30219,1,3.21,0.80),
  (30219,2,3.41,0.85),
  (30219,3,3.21,0.80),
  (30219,4,3.41,0.85),
  (30219,5,3.31,3.31),
  (30219,6,3.41,3.41),
  (30219,7,3.31,3.31),
  (30219,8,3.41,3.41),
  (30220,1,3.43,0.86),
  (30220,2,3.63,0.91),
  (30220,3,3.43,0.86),
  (30220,4,3.63,0.91),
  (30220,5,3.53,3.53),
  (30220,6,3.63,3.63),
  (30220,7,3.53,3.53),
  (30220,8,3.63,3.63),
  (30221,1,3.65,0.91),
  (30221,2,3.85,0.96),
  (30221,3,3.65,0.91),
  (30221,4,3.85,0.96),
  (30221,5,3.75,3.75),
  (30221,6,3.85,3.85),
  (30221,7,3.75,3.75),
  (30221,8,3.85,3.85),
  (30222,1,3.14,0.79),
  (30222,2,3.34,0.84),
  (30222,3,3.14,0.79),
  (30222,4,3.34,0.84),
  (30222,5,3.14,3.14),
  (30222,6,3.24,3.24),
  (30222,7,3.14,3.14),
  (30222,8,3.24,3.24),
  (30223,1,3.36,0.84),
  (30223,2,3.56,0.89),
  (30223,3,3.36,0.84),
  (30223,4,3.56,0.89),
  (30223,5,3.36,3.36),
  (30223,6,3.46,3.46),
  (30223,7,3.36,3.36),
  (30223,8,3.46,3.46),
  (30224,1,3.58,0.90),
  (30224,2,3.78,0.95),
  (30224,3,3.58,0.90),
  (30224,4,3.78,0.95),
  (30224,5,3.58,3.58),
  (30224,6,3.68,3.68),
  (30224,7,3.58,3.58),
  (30224,8,3.68,3.68),
  (30225,1,3.80,0.95),
  (30225,2,4.00,1.00),
  (30225,3,3.80,0.95),
  (30225,4,4.00,1.00),
  (30225,5,3.80,3.80),
  (30225,6,3.90,3.90),
  (30225,7,3.80,3.80),
  (30225,8,3.90,3.90),
  (30226,1,3.29,0.82),
  (30226,2,3.49,0.87),
  (30226,3,3.29,0.82),
  (30226,4,3.49,0.87),
  (30226,5,3.39,3.39),
  (30226,6,3.49,3.49),
  (30226,7,3.39,3.39),
  (30226,8,3.49,3.49),
  (30227,1,3.51,0.88),
  (30227,2,3.71,0.93),
  (30227,3,3.51,0.88),
  (30227,4,3.71,0.93),
  (30227,5,3.61,3.61),
  (30227,6,3.71,3.71),
  (30227,7,3.61,3.61),
  (30227,8,3.71,3.71),
  (30228,1,3.73,0.93),
  (30228,2,3.93,0.98),
  (30228,3,3.73,0.93),
  (30228,4,3.93,0.98),
  (30228,5,3.83,3.83),
  (30228,6,3.93,3.93),
  (30228,7,3.83,3.83),
  (30228,8,3.93,3.93),
  (30229,1,3.90,0.98),
  (30229,2,4.00,1.00),
  (30229,3,3.90,0.98),
  (30229,4,4.00,1.00),
  (30229,5,3.95,3.95),
  (30229,6,4.00,4.00),
  (30229,7,3.95,3.95),
  (30229,8,4.00,4.00),
  (30230,1,2.55,0.64),
  (30230,2,2.75,0.69),
  (30230,3,2.55,0.64),
  (30230,4,2.75,0.69),
  (30230,5,2.55,2.55),
  (30230,6,2.65,2.65),
  (30230,7,2.55,2.55),
  (30230,8,2.65,2.65),
  (30231,1,2.99,0.75),
  (30231,2,3.19,0.80),
  (30231,3,2.99,0.75),
  (30231,4,3.19,0.80),
  (30231,5,2.99,2.99),
  (30231,6,3.09,3.09),
  (30231,7,2.99,2.99),
  (30231,8,3.09,3.09),
  (30232,1,3.21,0.80),
  (30232,2,3.41,0.85),
  (30232,3,3.21,0.80),
  (30232,4,3.41,0.85),
  (30232,5,3.21,3.21),
  (30232,6,3.31,3.31),
  (30232,7,3.21,3.21),
  (30232,8,3.31,3.31),
  (30233,1,2.92,0.73),
  (30233,2,3.12,0.78),
  (30233,3,2.92,0.73),
  (30233,4,3.12,0.78),
  (30233,5,3.02,3.02),
  (30233,6,3.12,3.12),
  (30233,7,3.02,3.02),
  (30233,8,3.12,3.12),
  (30234,1,3.14,0.79),
  (30234,2,3.34,0.84),
  (30234,3,3.14,0.79),
  (30234,4,3.34,0.84),
  (30234,5,3.24,3.24),
  (30234,6,3.34,3.34),
  (30234,7,3.24,3.24),
  (30234,8,3.34,3.34),
  (30235,1,3.36,0.84),
  (30235,2,3.56,0.89),
  (30235,3,3.36,0.84),
  (30235,4,3.56,0.89),
  (30235,5,3.46,3.46),
  (30235,6,3.56,3.56),
  (30235,7,3.46,3.46),
  (30235,8,3.56,3.56),
  (30236,1,2.85,0.71),
  (30236,2,3.05,0.76),
  (30236,3,2.85,0.71),
  (30236,4,3.05,0.76),
  (30236,5,2.85,2.85),
  (30236,6,2.95,2.95),
  (30236,7,2.85,2.85),
  (30236,8,2.95,2.95),
  (30237,1,3.07,0.77),
  (30237,2,3.27,0.82),
  (30237,3,3.07,0.77),
  (30237,4,3.27,0.82),
  (30237,5,3.07,3.07),
  (30237,6,3.17,3.17),
  (30237,7,3.07,3.07),
  (30237,8,3.17,3.17),
  (30238,1,3.29,0.82),
  (30238,2,3.49,0.87),
  (30238,3,3.29,0.82),
  (30238,4,3.49,0.87),
  (30238,5,3.29,3.29),
  (30238,6,3.39,3.39),
  (30238,7,3.29,3.29),
  (30238,8,3.39,3.39),
  (30239,1,3.51,0.88),
  (30239,2,3.71,0.93),
  (30239,3,3.51,0.88),
  (30239,4,3.71,0.93),
  (30239,5,3.51,3.51),
  (30239,6,3.61,3.61),
  (30239,7,3.51,3.51),
  (30239,8,3.61,3.61),
  (30240,1,2.88,0.72),
  (30240,2,3.08,0.77),
  (30240,3,2.88,0.72),
  (30240,4,3.08,0.77),
  (30240,5,2.98,2.98),
  (30240,6,3.08,3.08),
  (30240,7,2.98,2.98),
  (30240,8,3.08,3.08),
  (30241,1,3.10,0.78),
  (30241,2,3.30,0.83),
  (30241,3,3.10,0.78),
  (30241,4,3.30,0.83),
  (30241,5,3.20,3.20),
  (30241,6,3.30,3.30),
  (30241,7,3.20,3.20),
  (30241,8,3.30,3.30),
  (30242,1,3.32,0.83),
  (30242,2,3.52,0.88),
  (30242,3,3.32,0.83),
  (30242,4,3.52,0.88),
  (30242,5,3.42,3.42),
  (30242,6,3.52,3.52),
  (30242,7,3.42,3.42),
  (30242,8,3.52,3.52),
  (30243,1,3.54,0.89),
  (30243,2,3.74,0.94),
  (30243,3,3.54,0.89),
  (30243,4,3.74,0.94),
  (30243,5,3.64,3.64),
  (30243,6,3.74,3.74),
  (30243,7,3.64,3.64),
  (30243,8,3.74,3.74),
  (30244,1,3.03,0.76),
  (30244,2,3.23,0.81),
  (30244,3,3.03,0.76),
  (30244,4,3.23,0.81),
  (30244,5,3.03,3.03),
  (30244,6,3.13,3.13),
  (30244,7,3.03,3.03),
  (30244,8,3.13,3.13),
  (30245,1,3.25,0.81),
  (30245,2,3.45,0.86),
  (30245,3,3.25,0.81),
  (30245,4,3.45,0.86),
  (30245,5,3.25,3.25),
  (30245,6,3.35,3.35),
  (30245,7,3.25,3.25),
  (30245,8,3.35,3.35),
  (30246,1,3.47,0.87),
  (30246,2,3.67,0.92),
  (30246,3,3.47,0.87),
  (30246,4,3.67,0.92),
  (30246,5,3.47,3.47),
  (30246,6,3.57,3.57),
  (30246,7,3.47,3.47),
  (30246,8,3.57,3.57),
  (30247,1,3.69,0.92),
  (30247,2,3.89,0.97),
  (30247,3,3.69,0.92),
  (30247,4,3.89,0.97),
  (30247,5,3.69,3.69),
  (30247,6,3.79,3.79),
  (30247,7,3.69,3.69),
  (30247,8,3.79,3.79),
  (30248,1,3.40,0.85),
  (30248,2,3.60,0.90),
  (30248,3,3.40,0.85),
  (30248,4,3.60,0.90),
  (30248,5,3.50,3.50),
  (30248,6,3.60,3.60),
  (30248,7,3.50,3.50),
  (30248,8,3.60,3.60),
  (30249,1,3.62,0.91),
  (30249,2,3.82,0.96),
  (30249,3,3.62,0.91),
  (30249,4,3.82,0.96),
  (30249,5,3.72,3.72),
  (30249,6,3.82,3.82),
  (30249,7,3.72,3.72),
  (30249,8,3.82,3.82),
  (30250,1,3.84,0.96),
  (30250,2,4.00,1.00),
  (30250,3,3.84,0.96),
  (30250,4,4.00,1.00),
  (30250,5,3.94,3.94),
  (30250,6,4.00,4.00),
  (30250,7,3.94,3.94),
  (30250,8,4.00,4.00);

-- END OF DEMO SEED
-- =====================================================
SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- QUICK SUMMARY
-- =====================================================
-- Departments covered  : 12 (IT, HR, Finance, Marketing, Operations,
--                             Purchasing, Compliance, Business Development,
--                             Audit, General Services, Office of the President,
--                             Acquired Properties)
-- Demo employees added : ~75 (IDs 20001–20117)
-- Evaluations created  : 68 (all in pending states)
--   Status distribution:
--     Pending Self-Rating        : ~18
--     Pending Supervisor         : ~18
--     Pending HR Consolidation   : ~18
--     Pending Manager            : ~14
-- Career movements created : 30 (all Pending, 0 approved)
--   Types: Promotion, Salary Adjustment, Transfer,
--          Role Change, Regularization
-- Evaluation templates : 2 (Annual & Initial/Probationary)
-- Evaluation criteria  : 13 (8 for Annual, 5 for Initial)
-- =====================================================
