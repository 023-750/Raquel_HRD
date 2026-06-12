<?php
require_once '../includes/session-check.php';
checkRole(['Employee']);
require_once '../includes/functions.php';

header('Content-Type: application/json');

$user_id = (int)($_SESSION['user_id'] ?? 0);
$manager_employee_id = (int)($_SESSION['employee_id'] ?? 0);

if (!isDeptManagerRole($conn, $manager_employee_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$evaluation_id = (int)($_REQUEST['evaluation_id'] ?? 0);
$action = $_REQUEST['action'] ?? '';

if ($evaluation_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid evaluation ID']);
    exit;
}

// Fetch evaluation to check authorization
$stmt = $conn->prepare("SELECT employee_id, status FROM evaluations WHERE evaluation_id = ? LIMIT 1");
$stmt->bind_param("i", $evaluation_id);
$stmt->execute();
$eval = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$eval) {
    echo json_encode(['status' => 'error', 'message' => 'Evaluation not found']);
    exit;
}

if (!isDeptManagerOfEmployee($conn, $user_id, (int)$eval['employee_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($action === 'heartbeat') {
    // Try to extend the manager lock
    $stmt = $conn->prepare("
        UPDATE evaluations 
        SET manager_lock_expires = DATE_ADD(NOW(), INTERVAL 15 MINUTE) 
        WHERE evaluation_id = ? AND manager_lock_user_id = ?
    ");
    $stmt->bind_param("ii", $evaluation_id, $user_id);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected > 0) {
        echo json_encode(['status' => 'success', 'locked' => true]);
    } else {
        // Check if the lock was already extended or is held by the same user
        $stmt = $conn->prepare("SELECT manager_lock_user_id FROM evaluations WHERE evaluation_id = ?");
        $stmt->bind_param("i", $evaluation_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        $current_holder = $res ? (int)$res['manager_lock_user_id'] : 0;
        if ($current_holder === $user_id) {
            echo json_encode(['status' => 'success', 'locked' => true]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Lock lost or not held']);
        }
    }
    exit;
} elseif ($action === 'release') {
    // Release the manager lock
    $stmt = $conn->prepare("
        UPDATE evaluations 
        SET manager_lock_user_id = NULL, manager_lock_expires = NULL 
        WHERE evaluation_id = ? AND manager_lock_user_id = ?
    ");
    $stmt->bind_param("ii", $evaluation_id, $user_id);
    $stmt->execute();
    $stmt->close();
    
    echo json_encode(['status' => 'released']);
    exit;
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    exit;
}
