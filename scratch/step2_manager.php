<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['user_id'] = 8; // Elena Delgado (HR Manager)
$_SESSION['role'] = 'HR Manager';
$_SESSION['full_name'] = 'Elena Delgado';

require_once 'config/database.php';
$evaluation_id = 6;
$score_ids = [];
$scores_res = $conn->query("SELECT score_id FROM evaluation_scores WHERE evaluation_id = $evaluation_id LIMIT 2");
while ($row = $scores_res->fetch_assoc()) {
    $score_ids[] = (int)$row['score_id'];
}

$_POST['evaluation_id'] = $evaluation_id;
$_POST['ratings'] = [
    $score_ids[0] => 4.00,
    $score_ids[1] => 4.00
];
$_SERVER['REQUEST_METHOD'] = 'POST';

chdir('manager/ajax');
require 'save-rating.php';
