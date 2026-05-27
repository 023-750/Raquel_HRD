<?php
require 'config/database.php';
$eval = $conn->query("SELECT * FROM evaluations WHERE evaluation_id = 6")->fetch_assoc();
print_r($eval);
