<?php
require 'config/database.php';
$res = $conn->query("SHOW COLUMNS FROM evaluation_scores");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
