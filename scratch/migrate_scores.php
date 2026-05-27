<?php
require_once dirname(__DIR__) . '/config/database.php';

// Add supervisor review tracking columns to evaluation_scores
$columns_to_add = [
    'supervisor_override_score' => "DECIMAL(5,2) NULL DEFAULT NULL COMMENT 'Score overridden by HR Supervisor'",
    'supervisor_override_by' => "INT NULL DEFAULT NULL COMMENT 'User ID of HR Supervisor who overrode'",
    'supervisor_override_at' => "DATETIME NULL DEFAULT NULL COMMENT 'When override was made'",
    'manager_override_score' => "DECIMAL(5,2) NULL DEFAULT NULL COMMENT 'Score overridden by HR Manager'",
    'manager_override_by' => "INT NULL DEFAULT NULL COMMENT 'User ID of HR Manager who overrode'",
    'manager_override_at' => "DATETIME NULL DEFAULT NULL COMMENT 'When manager override was made'",
];

foreach ($columns_to_add as $col => $definition) {
    // Check if column exists
    $result = $conn->query("SHOW COLUMNS FROM evaluation_scores LIKE '$col'");
    if ($result && $result->num_rows > 0) {
        echo "Column '$col' already exists. Skipping.\n";
    } else {
        $sql = "ALTER TABLE evaluation_scores ADD COLUMN $col $definition";
        if ($conn->query($sql)) {
            echo "Successfully added column '$col'.\n";
        } else {
            echo "ERR adding column '$col': " . $conn->error . "\n";
        }
    }
}
echo "Done.\n";
