<?php
require_once dirname(__DIR__) . '/config/database.php';
$res = $conn->query("SELECT * FROM evaluations WHERE evaluation_id = 2");
print_r($res->fetch_assoc());
