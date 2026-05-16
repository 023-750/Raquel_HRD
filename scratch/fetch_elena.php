<?php
require_once 'includes/functions.php';

$query = "SELECT e.*, 
          d.height_m, d.weight_kg, d.blood_type, d.citizenship,
          g.sss_number, g.philhealth_number, g.pagibig_number, g.tin_number,
          c.telephone_number, c.mobile_number, c.personal_email,
          ec.contact_name as emergency_contact_name, ec.relationship as emergency_contact_relationship, ec.contact_number as emergency_contact_number,
          dept.department_name,
          b.branch_name
          FROM employees e
          LEFT JOIN employee_details d ON e.employee_id = d.employee_id
          LEFT JOIN employee_government_ids g ON e.employee_id = g.employee_id
          LEFT JOIN employee_contacts c ON e.employee_id = c.employee_id
          LEFT JOIN employee_emergency_contacts ec ON e.employee_id = ec.employee_id
          LEFT JOIN departments dept ON e.department_id = dept.department_id
          LEFT JOIN branches b ON e.branch_id = b.branch_id
          WHERE e.first_name = 'Elena' AND e.last_name = 'Delgado'
          LIMIT 1";

$result = $conn->query($query);

if ($result && $row = $result->fetch_assoc()) {
    // Get addresses
    $addr_query = "SELECT * FROM employee_addresses WHERE employee_id = " . $row['employee_id'];
    $addr_result = $conn->query($addr_query);
    $addresses = [];
    while ($addr = $addr_result->fetch_assoc()) {
        $addresses[$addr['address_type']] = $addr;
    }
    $row['addresses'] = $addresses;
    
    echo json_encode($row, JSON_PRETTY_PRINT);
} else {
    echo "Employee not found.";
}
?>
