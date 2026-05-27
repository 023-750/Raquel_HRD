<?php
// Mock session
session_start();
$_SESSION['user_id'] = 10; // Patricia Gomez (HR Supervisor)
$_SESSION['role'] = 'HR Supervisor';
$_SESSION['full_name'] = 'Patricia Gomez';

// Mock post data
$_POST['evaluation_id'] = 2; // Kevin Santiago's approved evaluation
$_POST['ratings'] = [
    14 => 3.50,
    15 => 3.75
];

// Run the script
$_SERVER['REQUEST_METHOD'] = 'POST';

chdir(dirname(__DIR__) . '/supervisor/ajax');
ob_start();
require_once 'save-rating.php';
$output = ob_get_clean();

echo "Response:\n";
echo $output . "\n";
