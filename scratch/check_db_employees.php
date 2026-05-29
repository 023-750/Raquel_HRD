<?php
require_once dirname(__DIR__) . '/config/database.php';

$res = $conn->query("SELECT employee_id, employee_code, first_name, last_name, job_title, job_title_id, department_id, reports_to FROM employees");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        echo json_encode($row) . "\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}
