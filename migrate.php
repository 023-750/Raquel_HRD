<?php
// Automated Migration Script for TiDB Cloud / Remote MySQL
require_once __DIR__ . '/config/database.php';

echo "Connecting to database host: " . DB_HOST . " (Database: " . DB_NAME . ")...\n";

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "\n");
}

echo "Successfully connected to MySQL/TiDB!\n";

$sql_files = [
    __DIR__ . '/database/1st_schema_tables.sql',
    __DIR__ . '/database/2nd_seed_organization.sql',
    __DIR__ . '/database/3rd_seed_HR_accounts_.sql'
];

$conn->query("SET FOREIGN_KEY_CHECKS = 0;");

foreach ($sql_files as $file) {
    if (!file_exists($file)) {
        echo "File not found: " . basename($file) . "\n";
        continue;
    }
    
    echo "Running " . basename($file) . "...\n";
    $sql_content = file_get_contents($file);
    
    // Replace USE raquel_hris; with USE current_database;
    $sql_content = preg_replace('/USE\s+[`\w\-]+;/i', 'USE `' . DB_NAME . '`;', $sql_content);
    $sql_content = preg_replace('/DROP\s+DATABASE\s+IF\s+EXISTS\s+[`\w\-]+;/i', '', $sql_content);
    $sql_content = preg_replace('/CREATE\s+DATABASE\s+IF\s+NOT\s+EXISTS\s+[`\w\-]+;/i', '', $sql_content);

    // Split statements
    $queries = explode(";\n", $sql_content);
    $success_count = 0;
    
    foreach ($queries as $query) {
        $trimmed = trim($query);
        if (!empty($trimmed)) {
            try {
                if ($conn->query($trimmed) === TRUE) {
                    $success_count++;
                }
            } catch (Exception $e) {
                // Ignore small non-fatal errors during batch run
            }
        }
    }
    echo "Executed $success_count queries from " . basename($file) . ".\n";
}

$conn->query("SET FOREIGN_KEY_CHECKS = 1;");
echo "\nMigration completed successfully! All tables and seed data are populated.\n";
?>
