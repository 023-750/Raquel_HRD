<?php
require_once __DIR__ . '/config/database.php';

echo "Connected to " . DB_HOST . " (Database: " . DB_NAME . ")...\n";

$conn->query("SET FOREIGN_KEY_CHECKS = 0;");

// Insert default branch
$conn->query("REPLACE INTO branches (branch_id, branch_name, location, is_active) VALUES (1, 'Head Office', 'Main Branch', 1)");
echo "Branch 1 created/updated!\n";

// Insert Admin account
$admin_pass = password_hash('password123', PASSWORD_BCRYPT);
$sql_admin = "REPLACE INTO users (user_id, employee_id, username, email, full_name, password_hash, role, branch_id, is_active, first_login_completed, created_at) VALUES 
(1, NULL, 'admin', 'admin@company.com', 'System Admin', '$admin_pass', 'Admin', 1, 1, 1, NOW())";

if ($conn->query($sql_admin)) {
    echo "Admin user created successfully!\n";
} else {
    echo "Error inserting admin user: " . $conn->error . "\n";
}

// Insert HR Manager account
$sql_hrm = "REPLACE INTO users (user_id, employee_id, username, email, full_name, password_hash, role, branch_id, is_active, first_login_completed, created_at) VALUES 
(2, 101, 'HRD-001', 'elena.delgado@example.com', 'Elena Delgado', '$admin_pass', 'HR Manager', 1, 1, 1, NOW())";
$conn->query($sql_hrm);

// Insert HR Supervisor account
$sql_hrs = "REPLACE INTO users (user_id, employee_id, username, email, full_name, password_hash, role, branch_id, is_active, first_login_completed, created_at) VALUES 
(3, 301, 'HRD-002', 'patricia.gomez@example.com', 'Patricia Gomez', '$admin_pass', 'HR Supervisor', 1, 1, 1, NOW())";
$conn->query($sql_hrs);

// Insert HR Staff account
$sql_hrstaff = "REPLACE INTO users (user_id, employee_id, username, email, full_name, password_hash, role, branch_id, is_active, first_login_completed, created_at) VALUES 
(4, 302, 'HRD-003', 'miguel.torres@example.com', 'Miguel Torres', '$admin_pass', 'HR Staff', 1, 1, 1, NOW())";
$conn->query($sql_hrstaff);

$conn->query("SET FOREIGN_KEY_CHECKS = 1;");

echo "\n--- VERIFYING USERS IN DATABASE ---\n";
$res = $conn->query("SELECT user_id, username, email, role, is_active FROM users");
while ($row = $res->fetch_assoc()) {
    echo "ID: {$row['user_id']} | Username: {$row['username']} | Role: {$row['role']} | Active: {$row['is_active']}\n";
}

echo "\nUser seeding complete!\n";
?>
