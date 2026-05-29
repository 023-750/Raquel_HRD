<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: text/plain');

$result = $conn->query("SELECT employee_id, first_name, last_name, job_title, department_id, reports_to FROM employees");
echo "EMPLOYEES TABLE HIERARCHY:\n";
while ($row = $result->fetch_assoc()) {
    echo "ID: {$row['employee_id']} | Name: {$row['first_name']} {$row['last_name']} | Job: {$row['job_title']} | Dept: {$row['department_id']} | Reports To: " . ($row['reports_to'] ?? 'NULL') . "\n";
}

echo "\nJOB TITLES TABLE HIERARCHY:\n";
$jt_result = $conn->query("SELECT job_title_id, job_title, reports_to FROM job_titles");
while ($row = $jt_result->fetch_assoc()) {
    echo "ID: {$row['job_title_id']} | Job: {$row['job_title']} | Reports To: " . ($row['reports_to'] ?? 'NULL') . "\n";
}
?>
