<?php
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$res = $conn->query("
    SELECT e.employee_id, e.employee_code, e.first_name, e.last_name, e.job_title, e.department_id, e.branch_id, e.reports_to, e.is_active,
           d.department_name, u.user_id, u.role
    FROM employees e
    LEFT JOIN departments d ON e.department_id = d.department_id
    LEFT JOIN users u ON e.employee_id = u.employee_id
    WHERE d.department_name LIKE '%Marketing%' OR u.role LIKE '%Manager%' OR u.role LIKE '%Supervisor%'
");

while ($row = $res->fetch_assoc()) {
    echo "EmpID: {$row['employee_id']} | Code: {$row['employee_code']} | Name: {$row['first_name']} {$row['last_name']} | Title: {$row['job_title']} | Dept: {$row['department_name']} | ReportsTo: {$row['reports_to']} | UserID: {$row['user_id']} | UserRole: {$row['role']} | Active: {$row['is_active']}\n";
}
