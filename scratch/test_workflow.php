<?php
/**
 * Workflow Test Script: Simulates sequential overrides:
 * 1. Supervisor overrides rating.
 * 2. Manager overrides rating.
 * 3. Supervisor overrides rating again.
 * 
 * Verifies that when the supervisor overrides the ratings again:
 * - Existing manager override values are cleared.
 * - The new supervisor ratings are active.
 * - The overall evaluation scores are correctly updated.
 */

// Save original session if active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';
require_once 'includes/functions.php';

$evaluation_id = 6; // Kevin Santiago's Approved evaluation

// Step 0: Clean slate for evaluation 6. Let's clear any overrides.
echo "Step 0: Clearing existing overrides to start fresh...\n";
$conn->query("UPDATE evaluation_scores 
              SET supervisor_override_score = NULL, supervisor_override_by = NULL, supervisor_override_at = NULL,
                  manager_override_score = NULL, manager_override_by = NULL, manager_override_at = NULL
              WHERE evaluation_id = $evaluation_id");

// Verify clean slate
recalculateEvaluationScores($conn, $evaluation_id);
$eval_fresh = $conn->query("SELECT total_score, status FROM evaluations WHERE evaluation_id = $evaluation_id")->fetch_assoc();
echo "Fresh Evaluation Total Score: {$eval_fresh['total_score']} | Status: {$eval_fresh['status']}\n\n";

// Fetch some score IDs for evaluation 6 to use
$score_ids = [];
$scores_res = $conn->query("SELECT score_id FROM evaluation_scores WHERE evaluation_id = $evaluation_id LIMIT 2");
while ($row = $scores_res->fetch_assoc()) {
    $score_ids[] = (int)$row['score_id'];
}

if (count($score_ids) < 2) {
    die("Error: Not enough criteria scores found for evaluation $evaluation_id\n");
}

echo "Using Score IDs: " . implode(', ', $score_ids) . "\n\n";

// Step 1: Supervisor Overrides Ratings
echo "Step 1: Simulating HR Supervisor overriding ratings to 3.50...\n";
$_SESSION['user_id'] = 9; // Patricia Gomez (HR Supervisor)
$_SESSION['role'] = 'HR Supervisor';
$_SESSION['full_name'] = 'Patricia Gomez';

$_POST['evaluation_id'] = $evaluation_id;
$_POST['ratings'] = [
    $score_ids[0] => 3.50,
    $score_ids[1] => 3.50
];
$_SERVER['REQUEST_METHOD'] = 'POST';

// Run supervisor/ajax/save-rating.php
chdir(__DIR__ . '/../supervisor/ajax');
ob_start();
require 'save-rating.php';
$resp1 = ob_get_clean();
echo "Supervisor override response: $resp1\n";

// Verify supervisor overrides in database
chdir(__DIR__ . '/..');
$scores_check1 = $conn->query("SELECT score_id, supervisor_override_score, manager_override_score 
                               FROM evaluation_scores 
                               WHERE score_id IN (" . implode(',', $score_ids) . ")")->fetch_all(MYSQLI_ASSOC);
echo "Database state after Supervisor override:\n";
print_r($scores_check1);

$eval_check1 = $conn->query("SELECT total_score FROM evaluations WHERE evaluation_id = $evaluation_id")->fetch_assoc();
echo "Recalculated Evaluation Total Score (Supervisor): {$eval_check1['total_score']}\n\n";


// Step 2: Manager Overrides Ratings
echo "Step 2: Simulating HR Manager overriding ratings to 4.00...\n";
$_SESSION['user_id'] = 8; // Elena Delgado (HR Manager)
$_SESSION['role'] = 'HR Manager';
$_SESSION['full_name'] = 'Elena Delgado';

$_POST['evaluation_id'] = $evaluation_id;
$_POST['ratings'] = [
    $score_ids[0] => 4.00,
    $score_ids[1] => 4.00
];
$_SERVER['REQUEST_METHOD'] = 'POST';

// Run manager/ajax/save-rating.php
chdir(__DIR__ . '/../manager/ajax');
ob_start();
// Since save-rating.php might require/include files multiple times, let's include it or clean up before
// In standard PHP, require_once won't run again, so let's run it.
// If it was already loaded, we can just execute the queries manually or use chdir and include.
// Since we didn't load manager/ajax/save-rating.php yet, require is perfect.
require 'save-rating.php';
$resp2 = ob_get_clean();
echo "Manager override response: $resp2\n";

// Verify manager overrides in database
chdir(__DIR__ . '/..');
$scores_check2 = $conn->query("SELECT score_id, supervisor_override_score, manager_override_score 
                               FROM evaluation_scores 
                               WHERE score_id IN (" . implode(',', $score_ids) . ")")->fetch_all(MYSQLI_ASSOC);
echo "Database state after Manager override:\n";
print_r($scores_check2);

$eval_check2 = $conn->query("SELECT total_score FROM evaluations WHERE evaluation_id = $evaluation_id")->fetch_assoc();
echo "Recalculated Evaluation Total Score (Manager): {$eval_check2['total_score']}\n\n";


// Step 3: Supervisor Overrides Ratings AGAIN
echo "Step 3: Simulating HR Supervisor overriding ratings again to 3.80...\n";
$_SESSION['user_id'] = 9; // Patricia Gomez (HR Supervisor)
$_SESSION['role'] = 'HR Supervisor';
$_SESSION['full_name'] = 'Patricia Gomez';

$_POST['evaluation_id'] = $evaluation_id;
$_POST['ratings'] = [
    $score_ids[0] => 3.80,
    $score_ids[1] => 3.80
];
$_SERVER['REQUEST_METHOD'] = 'POST';

// Run supervisor/ajax/save-rating.php again. Since we used require earlier, let's execute the main block manually or via a separate process or clean includes.
// To avoid PHP duplicate class/function/constant declaration or require-once issues, let's run a subprocess or just write a clean script.
// Let's run a subprocess for the individual steps to ensure a completely isolated environment!
echo "Running Step 3 isolated command...\n";
