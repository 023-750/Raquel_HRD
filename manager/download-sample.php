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

// PDS column order. Section names are kept here for maintenance; the CSV still has one header row for importing.
$pds_sections = [
    'Basic Identity' => ['First Name', 'Last Name', 'Middle Name', 'Extension'],
    'Birth & Status' => ['Birthday', 'Birthplace', 'Gender', 'Civil Status'],
    'Physical & Citizenship' => ['Height (m)', 'Weight (kg)', 'Blood Type', 'Citizenship'],
    'Government IDs' => ['SSS No', 'PhilHealth No', 'Pag-IBIG No', 'TIN No'],
    'Permanent Address' => ['Permanent Region', 'Permanent House No', 'Permanent Street', 'Permanent Subdivision', 'Permanent Barangay', 'Permanent City', 'Permanent Province', 'Permanent Zip Code', 'Residential Region', 'Residential House No', 'Residential Street', 'Residential Subdivision', 'Residential Barangay', 'Residential City', 'Residential Province', 'Residential Zip Code'],
    'Contact Information' => ['Telephone No', 'Mobile No', 'Email'],
    'Spouse Information' => ['Spouse Name', 'Spouse Occupation'],
    "Father's Information" => ['Father Surname', 'Father First Name', 'Father Middle Name', 'Father Extension', 'Father Occupation'],
    "Mother's Maiden Name" => ['Mother Maiden Surname', 'Mother First Name', 'Mother Middle Name', 'Mother Occupation'],
    'Children' => ['Child 1 Surname', 'Child 1 First Name', 'Child 1 Middle Name', 'Child 1 Birthday'],
    'Siblings' => ['Sibling 1 Surname', 'Sibling 1 First Name', 'Sibling 1 Middle Name', 'Sibling 1 Birthday'],
    'Educational Background' => ['Elementary School', 'Elementary Year Graduated', 'High School', 'High School Year Graduated', 'College School', 'College Degree/Course', 'College Year Graduated'],
    'Service Eligibility / Licenses' => ['Eligibility License Title 1', 'Eligibility License No 1'],
    'Special Skills & Hobbies' => ['Skill/Hobby 1'],
    'Non-Academic Distinctions / Recognition' => ['Recognition 1 Title'],
    'Membership in Organizations' => ['Membership 1 Organization'],
    'Real Properties' => ['Real Property 1 Description', 'Real Property 1 Market Value'],
    'Personal Properties' => ['Personal Property 1 Description', 'Personal Property 1 Cost'],
    'Liabilities' => ['Liability 1 Nature', 'Liability 1 Outstanding Balance'],
    'Employment-Related Disclosures' => ['Related to Company (Yes/No)', 'Related Details', 'Admin Offense (Yes/No)', 'Admin Offense Details', 'Criminal Charge (Yes/No)', 'Criminal Charge Details', 'PWD Status (Yes/No)', 'Solo Parent Status (Yes/No)'],
    'Character References (3 persons not related)' => ['Reference 1 Name', 'Reference 1 Address', 'Reference 1 Contact Number', 'Reference 2 Name', 'Reference 2 Address', 'Reference 2 Contact Number', 'Reference 3 Name', 'Reference 3 Address', 'Reference 3 Contact Number'],
    'Employment Details' => ['Company ID', 'Hire Date', 'Job Title', 'Rank', 'Department', 'Branch', 'Employment Status', 'Employment Type', 'Contract Start Date', 'Contract End Date', 'Previous Company 1', 'Previous Position 1', 'Previous Monthly Salary 1', 'Previous Employment Period 1', 'Previous Reason for Leaving 1', 'Training Title 1', 'Training Conducted By 1', 'Training Hours 1'],
    'Emergency Contacts' => ['Emergency Contact Name', 'Emergency Contact Relationship', 'Emergency Contact Number'],
];
$headers = array_merge(...array_values($pds_sections));
fputcsv($output, $headers);

// Values are mapped by header so their order always matches the PDS layout above.
$sample = [
    'First Name' => 'Juan', 'Last Name' => 'Dela Cruz', 'Middle Name' => 'Protacio', 'Birthday' => '06/19/1990', 'Birthplace' => 'Calamba, Laguna', 'Gender' => 'Male', 'Civil Status' => 'Single',
    'Height (m)' => '1.75', 'Weight (kg)' => '70', 'Blood Type' => 'O', 'Citizenship' => 'Filipino', 'SSS No' => '01-2345678-9', 'PhilHealth No' => '12-345678901-2', 'Pag-IBIG No' => '1234-5678-9012', 'TIN No' => '123-456-789-000',
    'Permanent Region' => 'Region IV-A (CALABARZON)', 'Permanent House No' => '123', 'Permanent Street' => 'Rizal Street', 'Permanent Subdivision' => 'Villa Flora', 'Permanent Barangay' => 'Barangay 1', 'Permanent City' => 'Calamba', 'Permanent Province' => 'Laguna', 'Permanent Zip Code' => '4027',
    'Residential Region' => 'Region IV-A (CALABARZON)', 'Residential House No' => '123', 'Residential Street' => 'Rizal Street', 'Residential Subdivision' => 'Villa Flora', 'Residential Barangay' => 'Barangay 1', 'Residential City' => 'Calamba', 'Residential Province' => 'Laguna', 'Residential Zip Code' => '4027',
    'Telephone No' => '(049) 123-4567', 'Mobile No' => '09171234567', 'Email' => 'juan.delacruz@example.com', 'Spouse Name' => '', 'Spouse Occupation' => '',
    'Father Surname' => 'Dela Cruz', 'Father First Name' => 'Jose', 'Father Occupation' => 'Engineer', 'Mother Maiden Surname' => 'Alonzo', 'Mother First Name' => 'Teodora', 'Mother Occupation' => 'Teacher',
    'Child 1 Surname' => 'Dela Cruz', 'Child 1 First Name' => 'Juan Jr.', 'Child 1 Birthday' => '08/14/2015', 'Sibling 1 Surname' => 'Dela Cruz', 'Sibling 1 First Name' => 'Ana', 'Sibling 1 Birthday' => '03/22/1995',
    'Elementary School' => 'Calamba Elementary School', 'Elementary Year Graduated' => '2002', 'High School' => 'Calamba National High School', 'High School Year Graduated' => '2006', 'College School' => 'University of the Philippines', 'College Degree/Course' => 'BS Information Technology', 'College Year Graduated' => '2010',
    'Eligibility License Title 1' => 'Civil Service Professional', 'Eligibility License No 1' => '1234567', 'Skill/Hobby 1' => 'Web Development', 'Recognition 1 Title' => 'Employee Innovation Award', 'Membership 1 Organization' => 'Philippine Computer Society',
    'Real Property 1 Description' => 'Residential lot', 'Real Property 1 Market Value' => '1500000', 'Personal Property 1 Description' => 'Motorcycle', 'Personal Property 1 Cost' => '85000', 'Liability 1 Nature' => 'Housing loan', 'Liability 1 Outstanding Balance' => '500000',
    'Related to Company (Yes/No)' => 'No', 'Admin Offense (Yes/No)' => 'No', 'Criminal Charge (Yes/No)' => 'No', 'PWD Status (Yes/No)' => 'No', 'Solo Parent Status (Yes/No)' => 'No',
    'Reference 1 Name' => 'Pedro Reyes', 'Reference 1 Address' => 'Calamba, Laguna', 'Reference 1 Contact Number' => '09181234567', 'Reference 2 Name' => 'Liza Santos', 'Reference 2 Address' => 'Los Baños, Laguna', 'Reference 2 Contact Number' => '09182345678', 'Reference 3 Name' => 'Marco Lim', 'Reference 3 Address' => 'Sta. Rosa, Laguna', 'Reference 3 Contact Number' => '09183456789',
    'Company ID' => 'EMP-2026-0001', 'Hire Date' => '05/27/2026', 'Job Title' => 'IT Supervisor', 'Rank' => 'Supervisor', 'Department' => 'Information Technology', 'Branch' => 'Raquel Pawnshop Main Office', 'Employment Status' => 'Regular', 'Employment Type' => 'Full-time', 'Contract Start Date' => '', 'Contract End Date' => '',
    'Previous Company 1' => 'ABC Technologies Inc.', 'Previous Position 1' => 'Systems Analyst', 'Previous Monthly Salary 1' => '35000', 'Previous Employment Period 1' => '2018-2022', 'Previous Reason for Leaving 1' => 'Career growth', 'Training Title 1' => 'Data Privacy Fundamentals', 'Training Conducted By 1' => 'ABC Training Institute', 'Training Hours 1' => '16',
    'Emergency Contact Name' => 'Maria Dela Cruz', 'Emergency Contact Relationship' => 'Mother', 'Emergency Contact Number' => '09187654321',
];
fputcsv($output, array_map(fn($header) => $sample[$header] ?? '', $headers));

fclose($output);
exit();
