<?php
$conn = new mysqli('localhost', 'root', 'admin', 'raquel_hris');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Check if columns already exist
$check = $conn->query("SHOW COLUMNS FROM employee_recognitions LIKE 'issued_by'");
if ($check->num_rows == 0) {
    $res = $conn->query("ALTER TABLE employee_recognitions ADD COLUMN issued_by VARCHAR(255) NULL, ADD COLUMN date_awarded DATE NULL;");
    if ($res) echo "Success: Columns added.";
    else echo "Error: " . $conn->error;
} else {
    echo "Columns already exist.";
}
$conn->close();
?>
