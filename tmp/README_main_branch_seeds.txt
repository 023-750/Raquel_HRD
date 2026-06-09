============================================
MAIN BRANCH SEEDS - EMPLOYEE DATA SUMMARY
============================================

FILE: main_branch_seeds.sql
LOCATION: c:\xampp\htdocs\Raquel_HRD_System\tmp\

OVERVIEW:
This SQL file contains comprehensive fictional employee data for all departments 
and positions at Raquel Pawnshop Main Office (Branch ID: 102).

============================================
EMPLOYEE COUNT BY DEPARTMENT:
============================================

1. Office of the President (Dept 10) - 4 employees
   - President and CEO (1)
   - Executive Assistants (3)

2. Acquired Properties (Dept 1) - 17 employees
   - VP level (1)
   - Managers (4)
   - Supervisors (3)
   - Staff & Sales Associates (9)

3. Audit (Dept 2) - 12 employees
   - Managers (3)
   - Supervisors (3)
   - Auditors (6)

4. Business Development (Dept 3) - 4 employees
   - Business Development Officer (1)
   - Staff (3)

5. Compliance (Dept 4) - 6 employees
   - Supervisors (3)
   - Staff (3)

6. Finance (Dept 5) - 18 employees
   - VP for Finance (1)
   - Accounting Supervisors (4)
   - Treasury Supervisors (5)
   - Accounting Staff (6)
   - Treasury Staff (2)

7. General Services (Dept 6) - 24 employees
   - VP for General Services (1)
   - Managers (4)
   - Supervisors (4)
   - Drivers (5)
   - Security Monitoring Staff (4)
   - Facilities Maintenance Staff (3)
   - Warehouse Staff (1)
   - Messengers (2)

8. Human Resources (Dept 7) - 16 employees
   - Managers (5)
   - Supervisors (5)
   - Staff (6)

9. Information Technology (Dept 8) - 19 employees
   - Managers (4)
   - Supervisors (5)
   - Programmers (2)
   - Technical Support Staff (5)
   - Helpdesk Assistants (3)

10. Marketing (Dept 9) - 7 employees
    - Managers (2)
    - Supervisors (2)
    - Staff (3)

11. Operations (Dept 11) - 18 employees
    - VP for Operations (1)
    - Regional Managers (2)
    - Area Coordinators (4)
    - Focal Persons (5)
    - Branch Staff (6)

12. Purchasing (Dept 12) - 3 employees
    - Purchasing Supervisors (2)
    - Purchasing Staff (1)

TOTAL EMPLOYEES: 148

============================================
DATA COMPLETENESS:
============================================

Each employee record includes:

✓ PERSONAL INFORMATION
  - Full name, date of birth, place of birth
  - Gender, civil status
  - Physical attributes (height, weight, blood type)
  - Citizenship

✓ CONTACT & ADDRESS
  - Personal email, mobile, telephone
  - Residential address (complete)
  - Permanent address (complete)

✓ FAMILY INFORMATION
  - Father, Mother, Spouse details (where applicable)
  - Children information (for married employees)
  - Siblings information

✓ EDUCATION BACKGROUND
  - Elementary, Secondary, College levels
  - Graduate Studies (for management positions)
  - Degree courses and graduation years
  - Honors received (for top performers)

✓ WORK EXPERIENCE
  - Previous employment history (1-3 companies)
  - Job titles and responsibilities
  - Employment dates and salaries

✓ TRAINING & PROFESSIONAL DEVELOPMENT
  - Relevant seminars and training programs
  - Number of hours completed
  - Conducting organizations

✓ VOLUNTARY WORK
  - Community service organizations
  - Positions held and hours contributed

✓ ELIGIBILITY & LICENSES
  - Professional licenses (CPA, Lawyer, etc.)
  - License numbers and exam details
  - Professional certifications

✓ SKILLS & HOBBIES
  - Core competencies
  - Technical skills
  - Professional skills

✓ RECOGNITIONS
  - Awards and achievements
  - Industry recognitions
  - Excellence awards

✓ MEMBERSHIPS
  - Professional associations
  - Industry organizations
  - Business groups

✓ PERSONAL DISCLOSURES
  - All standard disclosure questions
  - All employees have clean records

✓ GOVERNMENT IDs & EMPLOYMENT
  - SSS Number
  - PhilHealth Number
  - Pag-IBIG Number
  - TIN Number

✓ ASSETS, PROPERTIES & LIABILITIES (SALN)
  - Real properties (houses, lots, condos)
  - Personal properties (vehicles)
  - Outstanding liabilities (loans)

✓ CHARACTER REFERENCES
  - 2-3 references per employee
  - Complete contact information

✓ EMERGENCY CONTACTS
  - Family member contacts
  - Relationship and phone numbers

============================================
NOTABLE FEATURES:
============================================

1. DIVERSE PROFILES
   - Mix of male and female employees
   - Various age groups and experience levels
   - Different civil status (single, married)
   - Varied educational backgrounds

2. REALISTIC DATA
   - Appropriate salaries for positions
   - Logical career progressions
   - Relevant work experience
   - Industry-appropriate training

3. FICTIONAL CHARACTERS
   - No anime characters used
   - Western and Filipino names
   - Real-world professions and backgrounds

4. COMPLETE COVERAGE
   - All positions filled
   - All departments represented
   - Full organizational hierarchy

5. PDS COMPLIANT
   - Follows Philippine Civil Service Form 212
   - All required sections included
   - Proper data formatting

============================================
USAGE INSTRUCTIONS:
============================================

1. Ensure database 'raquel_hris' exists
2. Run schema: 1st_schema_tables.sql
3. Run organization data: 2nd_seed_organization.sql
4. Run this file: main_branch_seeds.sql

Command:
  mysql -u root -p raquel_hris < main_branch_seeds.sql

Or using phpMyAdmin:
  - Select raquel_hris database
  - Go to Import tab
  - Choose main_branch_seeds.sql
  - Click Go

============================================
NOTES:
============================================

- All employees are assigned to Branch 102 (Main Office)
- Employee IDs are structured by department:
  * 1100-1199: Office of the President
  * 2100-2199: Acquired Properties
  * 3200-3299: Audit
  * 4300-4399: Business Development
  * 5400-5499: Compliance
  * 6500-6599: Finance
  * 7600-7699: General Services
  * 8700-8799: Human Resources
  * 9800-9899: Information Technology
  * 10900-10999: Marketing
  * 11000-11099: Operations
  * 12200-12299: Purchasing

- Profile pictures are NULL (can be updated later)
- All dates are realistic and internally consistent
- REPLACE INTO is used to prevent duplicate key errors

============================================
Generated: June 9, 2026
System: Raquel HRD System
Database: raquel_hris
============================================
