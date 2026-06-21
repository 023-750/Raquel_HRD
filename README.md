# Raquel Pawnshop HRD System

A web-based **Human Resource Department (HRD) Management System** built for Raquel Pawnshop. The system centralizes employee records, performance evaluations, and career movement workflows across the organization's branches and departments, with dedicated portals for each user role.

## Overview

The system is a PHP/MySQL application that supports the full HR lifecycle:

- **Employee records management** with Personal Data Sheets (PDS)
- **Performance evaluation** using configurable templates and criteria
- **Career movement and progression** requests, endorsements, and approvals
- **Organization management** for branches, departments, and positions
- **Notifications, audit trails, analytics, and printable reports**

## User Roles & Portals

Each role has its own portal with role-specific features:

### Admin (`/admin`)
- Manage system users and employee portal accounts (add, edit, deactivate)
- View members and employee accounts
- System configuration and database backup
- Audit trail and notifications

### HR Manager (`/manager`)
- Full employee management (add, edit, view employees)
- Manage organization structure: branches, departments, and positions
- Create, edit, and archive evaluation templates
- Review PDS submissions and pending approvals
- Track career movements and career progression
- Analytics dashboard, evaluation history, and exportable/printable reports (evaluation and organization printouts, sample downloads)
- Operation management, audit trail, and notifications

### Supervisor (`/supervisor`)
- View and edit employees within their scope
- Handle pending endorsements for career movements
- Track career movements and progression
- View evaluation templates and evaluation history
- Analytics, reports, roster export, audit trail, and notifications

### HR Staff (`/staff`)
- Search and view employee records
- View evaluation templates and criteria
- Track career movements and evaluation history
- Notifications and profile settings

### Employee (`/employee`)
- Personal dashboard with notifications
- Fill out and update Personal Data Sheet via a step-by-step **PDS wizard**
- View employment details ("My Employment")
- Perform **self-rating** evaluations and confirm ratings
- View completed ratings and department manager reviews
- Submit career movement requests
- Profile settings

## Performance Evaluation Workflow

1. HR Manager creates an evaluation template with criteria.
2. Employee completes a self-rating.
3. Supervisor/Department Manager reviews and rates the employee.
4. Employee confirms the final rating.
5. Results are stored in evaluation history and available in reports and printouts.

## Project Structure

```
├── admin/            # Admin portal (users, accounts, backup, audit trail)
├── manager/          # HR Manager portal (employees, org structure, templates, reports)
├── supervisor/       # Supervisor portal (endorsements, evaluations, rosters)
├── staff/            # HR Staff portal (employee search, templates, history)
├── employee/         # Employee portal (PDS, self-rating, career requests)
├── new_login_page/   # Redesigned login page
├── includes/         # Shared PHP includes (auth, helpers, layout)
├── config/           # Configuration (database connection)
├── database/         # SQL setup and seed scripts
├── assets/           # CSS, JavaScript, images
├── index.php         # Entry point / login
└── logout.php        # Session logout
```

## Database Setup

Run the SQL scripts in `database/` in order:

1. `1st_setup_tables.sql` – creates all tables
2. `2nd_seed_organization.sql` – seeds branches, departments, and positions
3. `3rd_HRD_ADMIN.sql` – creates the HRD admin account
4. `4th_seed_accounts_.sql` – seeds user accounts
5. `5th_seed_it_agdangan.sql` – seeds IT Agdangan data
6. `seed_evaluation.sql` – seeds evaluation templates and criteria

## Tech Stack

- **Backend:** PHP
- **Database:** MySQL / MariaDB
- **Frontend:** HTML, CSS, JavaScript (AJAX for dynamic portal interactions)

## Getting Started

1. Clone the repository into your web server directory (e.g., XAMPP `htdocs`).
2. Create a MySQL database and run the scripts in `database/` in the order listed above.
3. Update the database credentials in `config/database.php`.
4. Open the app in your browser and log in with the seeded admin account.




---