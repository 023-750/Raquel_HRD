<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['user_id'] = 9; // Patricia Gomez (HR Supervisor)
$_SESSION['role'] = 'HR Supervisor';
$_SESSION['full_name'] = 'Patricia Gomez';

require_once 'config/database.php';
$evaluation_id = 6;
$score_ids = [];
$scores_res = $conn->query("SELECT score_id FROM evaluation_scores WHERE evaluation_id = $evaluation_id LIMIT 2");
while ($row = $scores_res->fetch_assoc()) {
    $score_ids[] = (int)$row['score_id'];
}

$_POST['evaluation_id'] = $evaluation_id;
$_POST['ratings'] = [
    $score_ids[0] => 3.50,
    $score_ids[1] => 3.50
];
$_SERVER['REQUEST_METHOD'] = 'POST';

chdir('supervisor/ajax');
require 'save-rating.php';
