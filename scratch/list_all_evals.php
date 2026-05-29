<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$res = $conn->query("
    SELECT ev.evaluation_id, ev.employee_id, e.first_name, e.last_name, ev.status,
           ev.total_score, ev.performance_level
    FROM evaluations ev
    LEFT JOIN employees e ON ev.employee_id = e.employee_id
");

echo "All Evaluations:\n";
while ($row = $res->fetch_assoc()) {
    echo json_encode($row, JSON_PRETTY_PRINT) . "\n";
}
