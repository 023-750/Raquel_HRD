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
$plans = isset($_POST['plans']) ? $_POST['plans'] : [];

if (!$evaluation_id) {
    echo json_encode(['success' => false, 'message' => 'Evaluation ID is required.']);
    exit;
}

// Fetch evaluation details and check if evaluation exists
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
    echo json_encode(['success' => false, 'message' => 'Developmental plan can only be edited for evaluations pending your review.']);
    exit;
}

$conn->begin_transaction();
try {
    // Delete existing dev plan items
    $conn->query("DELETE FROM evaluation_dev_plans WHERE evaluation_id = $evaluation_id");

    // Insert new dev plan items
    if (!empty($plans) && is_array($plans)) {
        $stmt = $conn->prepare("INSERT INTO evaluation_dev_plans (evaluation_id, improvement_area, support_needed, time_frame, sort_order) VALUES (?, ?, ?, ?, ?)");
        $sort_order = 0;
        foreach ($plans as $plan) {
            $improvement_area = trim($plan['improvement_area'] ?? '');
            $support_needed = trim($plan['support_needed'] ?? '');
            $time_frame = trim($plan['time_frame'] ?? '');

            // Skip empty rows
            if ($improvement_area === '' && $support_needed === '' && $time_frame === '') {
                continue;
            }

            $stmt->bind_param("isssi", $evaluation_id, $improvement_area, $support_needed, $time_frame, $sort_order);
            $stmt->execute();
            $sort_order++;
        }
        $stmt->close();
    }

    // Log audit trail
    logAudit($conn, (int)$_SESSION['user_id'], 'UPDATE', 'Evaluation', $evaluation_id, 'Manager updated developmental plan for ' . $eval['employee_name']);

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Developmental plan updated successfully.']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
