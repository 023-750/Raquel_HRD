<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$res = $conn->query("
    SELECT ev.evaluation_id, ev.employee_id, e.first_name, e.last_name, ev.status,
           ev.dept_supervisor_confirmed_by, ev.dept_supervisor_confirmed_date,
           ev.dept_manager_endorsed_by, ev.dept_manager_endorsed_date,
           ev.total_score, ev.performance_level
    FROM evaluations ev
    LEFT JOIN employees e ON ev.employee_id = e.employee_id
    WHERE ev.employee_id = 503
");

echo "Kenneth Losloso (503) Evaluations:\n";
while ($row = $res->fetch_assoc()) {
    echo json_encode($row, JSON_PRETTY_PRINT) . "\n";
}
