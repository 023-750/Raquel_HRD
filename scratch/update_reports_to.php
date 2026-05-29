<?php
require_once dirname(__DIR__) . '/config/database.php';

$conn->query("UPDATE employees SET reports_to = 502 WHERE employee_id = 503");
$conn->query("UPDATE employees SET reports_to = 501 WHERE employee_id = 502");
$conn->query("UPDATE employees SET reports_to = 301 WHERE employee_id = 501");
$conn->query("UPDATE employees SET reports_to = 101 WHERE employee_id = 301");

echo "reports_to values updated successfully in the database.\n";

$res = $conn->query("SELECT employee_id, first_name, last_name, reports_to FROM employees WHERE employee_id IN (503, 502, 501, 301, 101)");
while ($row = $res->fetch_assoc()) {
    echo "Employee ID: {$row['employee_id']} | Name: {$row['first_name']} {$row['last_name']} | Reports To: {$row['reports_to']}\n";
}
