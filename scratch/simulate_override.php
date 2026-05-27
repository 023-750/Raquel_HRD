<?php
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$res = $conn->query("SELECT user_id, username, role, full_name, employee_id FROM users");
echo "=== USERS ===\n";
while ($row = $res->fetch_assoc()) {
    echo "User ID: {$row['user_id']} | Username: {$row['username']} | Role: {$row['role']} | Name: {$row['full_name']} | Emp ID: {$row['employee_id']}\n";
}
