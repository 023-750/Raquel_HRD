<?php
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$res = $conn->query("SELECT es.*, ec.criterion_name
                     FROM evaluation_scores es
                     JOIN evaluation_criteria ec ON es.criterion_id = ec.criterion_id
                     WHERE es.evaluation_id = 2");

echo "=== EVALUATION 2 SCORES AFTER OVERRIDE ===\n";
while ($row = $res->fetch_assoc()) {
    echo "Score ID: {$row['score_id']} | Criterion: {$row['criterion_name']} | Value: {$row['score_value']} | Weighted: {$row['weighted_score']} | Sup Override: {$row['supervisor_override_score']} | Mgr Override: {$row['manager_override_score']}\n";
}
