<?php
require_once dirname(__DIR__) . '/config/database.php';

echo "=== rank_categories ===\n";
$r = $conn->query('SHOW COLUMNS FROM rank_categories');
if ($r) { while ($row = $r->fetch_assoc()) echo $row['Field'].' '.$row['Type']."\n"; } else { echo $conn->error."\n"; }

echo "\n=== rank_categories data ===\n";
$r2 = $conn->query('SELECT * FROM rank_categories LIMIT 10');
if ($r2) { while ($row = $r2->fetch_assoc()) echo implode(' | ', $row)."\n"; } else { echo $conn->error."\n"; }

echo "\n=== employees columns ===\n";
$r3 = $conn->query('SHOW COLUMNS FROM employees');
if ($r3) { while ($row = $r3->fetch_assoc()) echo $row['Field'].' '.$row['Type']."\n"; }

echo "\n=== evaluation_scores columns ===\n";
$r4 = $conn->query('SHOW COLUMNS FROM evaluation_scores');
if ($r4) { while ($row = $r4->fetch_assoc()) echo $row['Field'].' '.$row['Type']."\n"; } else { echo $conn->error."\n"; }

echo "\n=== evaluations columns ===\n";
$r5 = $conn->query('SHOW COLUMNS FROM evaluations');
if ($r5) { while ($row = $r5->fetch_assoc()) echo $row['Field'].' '.$row['Type']."\n"; } else { echo $conn->error."\n"; }
