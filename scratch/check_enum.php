<?php
require_once 'includes/functions.php';
$res = $conn->query("SHOW COLUMNS FROM employees LIKE 'employment_status'");
$row = $res->fetch_assoc();
echo $row['Type'];
?>
