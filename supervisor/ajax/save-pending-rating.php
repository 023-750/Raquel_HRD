<?php
header('Content-Type: application/json');
require_once '../../includes/session-check.php';
checkRole(['HR Supervisor']);
require_once '../../config/database.php';
require_once '../../includes/functions.php';
ensureEvaluationWorkflowSchema($conn);

// Check if it is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$evaluation_id = isset($_POST['evaluation_id']) ? (int)$_POST['evaluation_id'] : 0;
$ratings = isset($_POST['ratings']) ? $_POST['ratings'] : [];

if (!$evaluation_id || empty($ratings)) {
    echo json_encode(['success' => false, 'message' => 'Evaluation ID and ratings are required.']);
    exit;
}

// Fetch evaluation details and check if employee is Rank & File (rank_category_id = 5)
$eval_q = $conn->query("SELECT ev.*, e.rank_category_id, CONCAT(e.first_name, ' ', e.last_name) as employee_name, e.employee_id 
                        FROM evaluations ev 
                        JOIN employees e ON ev.employee_id = e.employee_id 
                        WHERE ev.evaluation_id = $evaluation_id");

if (!$eval_q || $eval_q->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Evaluation record not found.']);
    exit;
}

$eval = $eval_q->fetch_assoc();
$employee_id = (int)$eval['employee_id'];
$employee_name = $eval['employee_name'];
$rank_category_id = (int)$eval['rank_category_id'];



if ($rank_category_id !== 5) {
    echo json_encode(['success' => false, 'message' => 'Rating adjustments are only permitted for Rank & File (R&F) employees.']);
    exit;
}

if (!in_array($eval['status'], ['Pending Supervisor', 'Pending HR Consolidation'], true)) {
    echo json_encode(['success' => false, 'message' => 'Evaluation scores can only be edited while pending supervisor review or HR consolidation.']);
    exit;
}

$conn->begin_transaction();
try {
    // Save overrides and clear manager overrides so they don't block supervisor's edits
    $stmt = $conn->prepare("UPDATE evaluation_scores 
                            SET supervisor_override_score = ?, supervisor_override_by = ?, supervisor_override_at = NOW(),
                                manager_override_score = NULL, manager_override_by = NULL, manager_override_at = NULL
                            WHERE score_id = ? AND evaluation_id = ?");
    
    foreach ($ratings as $score_id => $rating_val) {
        $score_id = (int)$score_id;
        $rating_val = floatval($rating_val);
        if ($rating_val < 1.00) $rating_val = 1.00;
        if ($rating_val > 4.00) $rating_val = 4.00;

        $user_id = (int)$_SESSION['user_id'];
        $stmt->bind_param("diii", $rating_val, $user_id, $score_id, $evaluation_id);
        $stmt->execute();
    }
    $stmt->close();

    // Mark that supervisor altered the scores
    $conn->query("UPDATE evaluations SET supervisor_altered_scores = 1 WHERE evaluation_id = $evaluation_id");

    // Recalculate evaluation scores
    $recalc = recalculateEvaluationScores($conn, $evaluation_id);
    if (!$recalc) {
        throw new Exception("Failed to recalculate evaluation scores.");
    }

    // Log audit trail
    logAudit($conn, (int)$_SESSION['user_id'], 'UPDATE', 'Evaluation', $evaluation_id, 'Supervisor override of pending rating scores for ' . $employee_name);

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Ratings successfully updated and recalculated.']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
