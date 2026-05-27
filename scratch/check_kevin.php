<?php
require 'config/database.php';
$r = $conn->query("
    SELECT ev.evaluation_id, ev.status, ev.employee_id, ev.submitted_by,
           CONCAT(e.first_name, ' ', e.last_name) as employee_name,
           u.role as submitter_role, u.full_name as submitter_name
    FROM evaluations ev
    JOIN employees e ON ev.employee_id = e.employee_id
    LEFT JOIN users u ON ev.submitted_by = u.user_id
");
while ($row = $r->fetch_assoc()) {
    print_r($row);
}
