<?php
require_once __DIR__ . '/../config/database.php';
$res = $conn->query("SHOW COLUMNS FROM evaluations LIKE 'status'");
$row = $res->fetch_assoc();
echo "Status Column Type: " . $row['Type'] . "\n";
