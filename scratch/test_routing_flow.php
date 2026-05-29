<?php
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';

echo "=== Testing isDeptManagerRole ===\n";
echo "Raymond Santos (501) isDeptManagerRole: " . (isDeptManagerRole($conn, 501) ? "YES" : "NO") . "\n";
echo "Marie Cruz (502) isDeptManagerRole: " . (isDeptManagerRole($conn, 502) ? "YES" : "NO") . "\n";

echo "\n=== Testing getEmployeeSupervisor ===\n";
$sup_kenneth = getEmployeeSupervisor($conn, 503);
echo "Kenneth (503) Supervisor: " . ($sup_kenneth ? $sup_kenneth['first_name'] . ' ' . $sup_kenneth['last_name'] . ' (User ID: ' . $sup_kenneth['user_id'] . ')' : 'None') . "\n";

$sup_marie = getEmployeeSupervisor($conn, 502);
echo "Marie (502) Supervisor: " . ($sup_marie ? $sup_marie['first_name'] . ' ' . $sup_marie['last_name'] . ' (User ID: ' . $sup_marie['user_id'] . ')' : 'None') . "\n";

$sup_raymond = getEmployeeSupervisor($conn, 501);
echo "Raymond (501) Supervisor: " . ($sup_raymond ? $sup_raymond['first_name'] . ' ' . $sup_raymond['last_name'] . ' (User ID: ' . $sup_raymond['user_id'] . ')' : 'None') . "\n";

echo "\n=== Testing getDeptManagerOfEmployee ===\n";
$mgr_kenneth = getDeptManagerOfEmployee($conn, 503);
echo "Kenneth (503) Dept Manager: " . ($mgr_kenneth ? $mgr_kenneth['first_name'] . ' ' . $mgr_kenneth['last_name'] . ' (User ID: ' . $mgr_kenneth['user_id'] . ')' : 'None') . "\n";

$mgr_marie = getDeptManagerOfEmployee($conn, 502);
echo "Marie (502) Dept Manager: " . ($mgr_marie ? $mgr_marie['first_name'] . ' ' . $mgr_marie['last_name'] . ' (User ID: ' . $mgr_marie['user_id'] . ')' : 'None') . "\n";
