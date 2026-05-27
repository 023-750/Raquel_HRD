<?php
// Mock session
session_start();
$_SESSION['user_id'] = 2; // Elena Delgado (HR Manager)
$_SESSION['role'] = 'HR Manager';
$_SESSION['full_name'] = 'Elena Delgado';

// Mock post data
$_POST['evaluation_id'] = 5; // Kevin Santiago's evaluation (Pending Manager)
$_POST['ratings'] = [
    79 => 3.50, // original: 2.00, sup override: 2.00
    84 => 3.80  // original: 2.00, sup override: 2.00
];

// Run the script
$_SERVER['REQUEST_METHOD'] = 'POST';

chdir(dirname(__DIR__) . '/manager/ajax');
ob_start();
require_once 'save-rating.php';
$output = ob_get_clean();

echo "Response:\n";
echo $output . "\n";
