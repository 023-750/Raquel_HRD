<?php
// AJAX endpoint to check evaluation status in real-time
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['employee_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$evaluation_id = isset($_GET['evaluation_id']) ? (int)$_GET['evaluation_id'] : 0;
if ($evaluation_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid evaluation ID']);
    exit;
}

// Fetch the evaluation status and employee_id
$stmt = $conn->prepare("SELECT status, employee_id FROM evaluations WHERE evaluation_id = ? AND deleted_at IS NULL LIMIT 1");
$stmt->bind_param("i", $evaluation_id);
$stmt->execute();
$eval = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$eval) {
    echo json_encode(['success' => false, 'error' => 'Evaluation not found']);
    exit;
}

$viewer_user_id = (int)$_SESSION['user_id'];
$employee_id = (int)$eval['employee_id'];

// Check authorization: viewer must be the employee themselves, the supervisor, or the department manager
$is_authorized = false;
if (isSupervisorOfEmployee($conn, $viewer_user_id, $employee_id) || isDeptManagerOfEmployee($conn, $viewer_user_id, $employee_id)) {
    $is_authorized = true;
} else {
    // Check if the viewer is the employee themselves
    $emp_user = $conn->query("SELECT user_id FROM users WHERE employee_id = " . $employee_id . " AND role = 'Employee' LIMIT 1")->fetch_assoc();
    if ($emp_user && (int)$emp_user['user_id'] === $viewer_user_id) {
        $is_authorized = true;
    }
}

if (!$is_authorized) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

echo json_encode(['success' => true, 'status' => $eval['status']]);
exit;
