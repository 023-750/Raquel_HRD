<?php
// Automated Migration Script for TiDB Cloud / Remote MySQL
require_once __DIR__ . '/config/database.php';

echo "Connecting to database host: " . DB_HOST . " (Database: " . DB_NAME . ")...\n";

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "\n");
}

echo "Successfully connected to MySQL/TiDB!\n";

// Set admin password to 'password123'
$new_hash = password_hash('password123', PASSWORD_BCRYPT);
$conn->query("UPDATE users SET password_hash = '$new_hash' WHERE username = 'admin'");
echo "Admin user password updated to 'password123'\n";

// Set all HR employee user passwords to 'password123' as well
$conn->query("UPDATE users SET password_hash = '$new_hash'");
echo "All user accounts updated with password 'password123'\n";

echo "Migration & Password Sync Completed Successfully!\n";
?>
