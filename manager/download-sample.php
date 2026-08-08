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
    'Father Surname', 'Father First Name', 'Father Middle Name', 'Father Extension', 'Father Occupation',
    'Mother Maiden Surname', 'Mother First Name', 'Mother Middle Name', 'Mother Occupation',
    'Child 1 Surname', 'Child 1 First Name', 'Child 1 Middle Name', 'Child 1 Birthday',
    'Sibling 1 Surname', 'Sibling 1 First Name', 'Sibling 1 Middle Name', 'Sibling 1 Birthday',
    'Elementary School', 'Elementary Year Graduated', 'High School', 'High School Year Graduated',
    'College School', 'College Degree/Course', 'College Year Graduated',
    'Previous Company 1', 'Previous Position 1', 'Previous Monthly Salary 1', 'Previous Employment Period 1', 'Previous Reason for Leaving 1',
    'Training Title 1', 'Training Conducted By 1', 'Training Hours 1',
    'Eligibility License Title 1', 'Eligibility License No 1',
    'Related to Company (Yes/No)', 'Related Details', 'Admin Offense (Yes/No)', 'Admin Offense Details',
    'Criminal Charge (Yes/No)', 'Criminal Charge Details', 'PWD Status (Yes/No)', 'Solo Parent Status (Yes/No)',
    'Real Property 1 Description', 'Real Property 1 Market Value',
    'Personal Property 1 Description', 'Personal Property 1 Cost',
    'Liability 1 Nature', 'Liability 1 Outstanding Balance',
    'Reference 1 Name', 'Reference 1 Address', 'Reference 1 Contact Number',
    'Reference 2 Name', 'Reference 2 Address', 'Reference 2 Contact Number'
]);

// Write sample row
fputcsv($output, [
    'Juan', 'Dela Cruz', 'Protacio', '', '06/19/1990', 'Calamba, Laguna', 'Male', 'Single',
    '1.75', '70', 'O', 'Filipino', '01-2345678-9', '12-345678901-2', '1234-5678-9012', '123-456-789-000',
    '123', 'Rizal Street', 'Villa Flora', 'Barangay 1', 'Calamba', 'Laguna', '4027',
    '123', 'Rizal Street', 'Villa Flora', 'Barangay 1', 'Calamba', 'Laguna', '4027',
    '(049) 123-4567', '09171234567', 'juan.delacruz@example.com', '05/27/2026', 'IT Supervisor', 'Information Technology',
    'Raquel Pawnshop Main Office', 'Regular', 'Full-time', 'EMP-2026-0001', 'Maria Dela Cruz', 'Mother',
    '09187654321', '', '', 'Dela Cruz', 'Jose', '', '', 'Engineer',
    'Alonzo', 'Teodora', '', 'Teacher',
    'Dela Cruz', 'Juan Jr.', '', '08/14/2015',
    'Dela Cruz', 'Ana', '', '03/22/1995',
    'Calamba Elementary School', '2002', 'Calamba National High School', '2006',
    'University of the Philippines', 'BS Information Technology', '2010',
    'ABC Technologies Inc.', 'Systems Analyst', '35000', '2018-2022', 'Career growth',
    'Data Privacy Fundamentals', 'ABC Training Institute', '16',
    'Civil Service Professional', '1234567',
    'No', '', 'No', '', 'No', '', 'No', 'No',
    'Residential lot', '1500000', 'Motorcycle', '85000', 'Housing loan', '500000',
    'Pedro Reyes', 'Calamba, Laguna', '09181234567',
    'Liza Santos', 'Los Baños, Laguna', '09182345678'
]);

fclose($output);
exit();
