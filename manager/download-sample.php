<?php
require_once '../includes/session-check.php';
checkRole(['HR Manager', 'HR Supervisor']);

// Clear any previous output buffers
if (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=sample_employees.csv');

$output = fopen('php://output', 'w');

// Write headers
fputcsv($output, [
    'First Name', 'Last Name', 'Middle Name', 'Extension', 'Birthday', 'Birthplace', 'Gender', 'Civil Status',
    'Height (m)', 'Weight (kg)', 'Blood Type', 'Citizenship', 'SSS No', 'PhilHealth No', 'Pag-IBIG No', 'TIN No',
    'Residential House No', 'Residential Street', 'Residential Subdivision', 'Residential Barangay', 'Residential City',
    'Residential Province', 'Residential Zip Code', 'Permanent House No', 'Permanent Street', 'Permanent Subdivision',
    'Permanent Barangay', 'Permanent City', 'Permanent Province', 'Permanent Zip Code', 'Telephone No', 'Mobile No',
    'Email', 'Hire Date', 'Job Title', 'Department', 'Branch', 'Employment Status', 'Employment Type', 'Company ID',
    'Emergency Contact Name', 'Emergency Contact Relationship', 'Emergency Contact Number', 'Spouse Name', 'Spouse Occupation',
    'Father Name', 'Father Occupation', 'Mother Maiden Name', 'Mother Occupation'
]);

// Write sample row
fputcsv($output, [
    'Juan', 'Dela Cruz', 'Protacio', '', '06/19/1990', 'Calamba, Laguna', 'Male', 'Single',
    '1.75', '70', 'O', 'Filipino', '01-2345678-9', '12-345678901-2', '1234-5678-9012', '123-456-789-000',
    '123', 'Rizal Street', 'Villa Flora', 'Barangay 1', 'Calamba', 'Laguna', '4027',
    '123', 'Rizal Street', 'Villa Flora', 'Barangay 1', 'Calamba', 'Laguna', '4027',
    '(049) 123-4567', '09171234567', 'juan.delacruz@example.com', '05/27/2026', 'IT Supervisor', 'Information Technology',
    'Raquel Pawnshop Main Office', 'Regular', 'Full-time', 'EMP-2026-0001', 'Maria Dela Cruz', 'Mother',
    '09187654321', '', '', 'Jose Dela Cruz', 'Engineer', 'Teodora Alonzo', 'Teacher'
]);

fclose($output);
exit();
