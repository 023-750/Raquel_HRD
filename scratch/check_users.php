<?php
require_once dirname(__DIR__) . '/config/database.php';

$res = $conn->query("
    SELECT u.user_id, u.username, u.role, u.employee_id, e.first_name, e.last_name 
    FROM users u 
    LEFT JOIN employees e ON u.employee_id = e.employee_id
");
while ($row = $res->fetch_assoc()) {
    echo json_encode($row) . "\n";
}
