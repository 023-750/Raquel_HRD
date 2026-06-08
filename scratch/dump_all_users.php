<?php
require_once dirname(__DIR__) . '/config/database.php';

echo "=== USERS ===\n";
$res_users = $conn->query("SELECT user_id, username, employee_id, role, is_active FROM users");
while ($row = $res_users->fetch_assoc()) {
    echo "UserID: {$row['user_id']} | Username: {$row['username']} | EmpID: {$row['employee_id']} | Role: {$row['role']} | Active: {$row['is_active']}\n";
}

echo "\n=== EMPLOYEES ===\n";
$res_emps = $conn->query("SELECT employee_id, first_name, last_name, job_title, department_id, reports_to, is_active FROM employees");
while ($row = $res_emps->fetch_assoc()) {
    echo "EmpID: {$row['employee_id']} | Name: {$row['first_name']} {$row['last_name']} | Title: {$row['job_title']} | DeptID: {$row['department_id']} | ReportsTo: " . ($row['reports_to'] ?? 'NULL') . " | Active: {$row['is_active']}\n";
}
