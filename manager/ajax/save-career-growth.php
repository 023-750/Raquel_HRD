<?php
header('Content-Type: application/json');
require_once '../../includes/session-check.php';
checkRole(['HR Manager']);
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check if it is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

verifyCsrfToken();

$evaluation_id = isset($_POST['evaluation_id']) ? (int)$_POST['evaluation_id'] : 0;
$career_growth_suited = isset($_POST['career_growth_suited']) ? (int)$_POST['career_growth_suited'] : 0;
$desired_position = isset($_POST['desired_position']) ? trim($_POST['desired_position']) : '';
$target_date = isset($_POST['target_date']) && $_POST['target_date'] !== '' ? $_POST['target_date'] : null;
$career_growth_details = isset($_POST['career_growth_details']) ? trim($_POST['career_growth_details']) : '';

if (!$evaluation_id) {
    echo json_encode(['success' => false, 'message' => 'Evaluation ID is required.']);
    exit;
}

// Fetch evaluation details
$eval_q = $conn->query("SELECT ev.*, CONCAT(e.first_name, ' ', e.last_name) as employee_name 
                        FROM evaluations ev 
                        JOIN employees e ON ev.employee_id = e.employee_id 
                        WHERE ev.evaluation_id = $evaluation_id");

if (!$eval_q || $eval_q->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Evaluation record not found.']);
    exit;
}

$eval = $eval_q->fetch_assoc();

// Check if the evaluation is in a pending review state for manager
if (!in_array($eval['status'], ['Pending Manager', 'Pending HR Consolidation', 'Pending Dept Manager'], true)) {
    echo json_encode(['success' => false, 'message' => 'Evaluation details can only be edited for records pending your review.']);
    exit;
}

// Update evaluations table
$stmt = $conn->prepare("UPDATE evaluations 
                        SET career_growth_suited = ?, desired_position = ?, target_date = ?, career_growth_details = ?, updated_at = NOW() 
                        WHERE evaluation_id = ?");
$stmt->bind_param("isssi", $career_growth_suited, $desired_position, $target_date, $career_growth_details, $evaluation_id);

if ($stmt->execute()) {
    $stmt->close();
    // Log audit trail
    logAudit($conn, (int)$_SESSION['user_id'], 'UPDATE', 'Evaluation', $evaluation_id, 'Manager updated career growth details for ' . $eval['employee_name']);
    echo json_encode(['success' => true, 'message' => 'Career growth details updated successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update career growth details.']);
}
