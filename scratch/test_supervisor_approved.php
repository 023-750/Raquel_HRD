<?php
// Mock session
session_start();
$_SESSION['user_id'] = 10; // Patricia Gomez (HR Supervisor)
$_SESSION['role'] = 'HR Supervisor';
$_SESSION['full_name'] = 'Patricia Gomez';

// Mock post data
$_POST['evaluation_id'] = 6; // Kevin Santiago's approved evaluation
$_POST['ratings'] = [
    // Let's find real score IDs for evaluation 6 first
];

require 'config/database.php';
$scores_q = $conn->query("SELECT score_id, score_value, supervisor_override_score, manager_override_score FROM evaluation_scores WHERE evaluation_id = 6");
while ($row = $scores_q->fetch_assoc()) {
    $_POST['ratings'][$row['score_id']] = 3.50; // set all to 3.50
}

// Run the script
$_SERVER['REQUEST_METHOD'] = 'POST';

chdir(dirname(__DIR__) . '/supervisor/ajax');
ob_start();
require_once 'save-rating.php';
$output = ob_get_clean();

echo "Response:\n";
echo $output . "\n";
