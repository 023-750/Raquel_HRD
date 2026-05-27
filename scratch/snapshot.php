<?php
/**
 * Snapshot viewer: shows score_id, score_value, supervisor_override_score,
 * manager_override_score for evaluation_id=6
 */
require_once 'config/database.php';

$evaluation_id = 6;
$eval = $conn->query("SELECT total_score, kra_subtotal, behavior_average, performance_level, status FROM evaluations WHERE evaluation_id = $evaluation_id")->fetch_assoc();
echo "=== EVALUATION TOTALS ===\n";
echo "Status        : {$eval['status']}\n";
echo "Total Score   : {$eval['total_score']}\n";
echo "KRA Subtotal  : {$eval['kra_subtotal']}\n";
echo "Beh Average   : {$eval['behavior_average']}\n";
echo "Perf Level    : {$eval['performance_level']}\n\n";

$scores = $conn->query("SELECT es.score_id, ec.criterion_name, es.score_value,
                               es.supervisor_override_score, es.supervisor_override_by,
                               es.manager_override_score, es.manager_override_by
                        FROM evaluation_scores es
                        JOIN evaluation_criteria ec ON es.criterion_id = ec.criterion_id
                        WHERE es.evaluation_id = $evaluation_id
                        ORDER BY es.score_id");

echo "=== INDIVIDUAL SCORES ===\n";
printf("%-8s %-35s %-10s %-12s %-12s\n", "ScoreID", "Criterion", "Original", "SupOverride", "MgrOverride");
echo str_repeat("-", 80) . "\n";
while ($row = $scores->fetch_assoc()) {
    printf("%-8s %-35s %-10s %-12s %-12s\n",
        $row['score_id'],
        substr($row['criterion_name'], 0, 34),
        $row['score_value'],
        $row['supervisor_override_score'] ?? 'NULL',
        $row['manager_override_score'] ?? 'NULL'
    );
}
