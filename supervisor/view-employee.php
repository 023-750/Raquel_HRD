<?php
$page_title = 'View Employee';
require_once '../includes/session-check.php';
checkRole(['HR Supervisor']);
require_once '../includes/functions.php';

$eid = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$return_to = $_GET['return'] ?? (BASE_URL . '/supervisor/employees.php');
if (strpos($return_to, BASE_URL . '/supervisor/employees.php') !== 0 && strpos($return_to, '/supervisor/employees.php') !== 0) {
    $return_to = BASE_URL . '/supervisor/employees.php';
}
if ($eid <= 0)
    redirectWith(BASE_URL . '/supervisor/employees.php', 'danger', 'Invalid employee ID.');

$stmt = $conn->prepare("SELECT e.*, b.branch_name, d.department_name, jt.job_title as job_title_display, rc.rank_name,
    ed.height_m, ed.weight_kg, ed.blood_type, ed.citizenship,
    eg.sss_number, eg.philhealth_number, eg.pagibig_number, eg.tin_number,
    ec.telephone_number, ec.mobile_number, ec.personal_email,
    edi.is_related_to_company, edi.related_details, edi.has_admin_offense, edi.admin_offense_details,
    edi.has_criminal_charge, edi.criminal_charge_details, edi.has_criminal_conviction, edi.criminal_conviction_details,
    edi.has_been_separated, edi.separation_details, edi.is_pwd, edi.pwd_details,
    edi.is_solo_parent, edi.solo_parent_details, edi.has_recent_hospital, edi.hospital_details,
    edi.has_current_treatment, edi.treatment_details
    FROM employees e LEFT JOIN job_titles jt ON e.job_title_id = jt.job_title_id
    LEFT JOIN branches b ON e.branch_id = b.branch_id
    LEFT JOIN departments d ON e.department_id = d.department_id
    LEFT JOIN rank_categories rc ON e.rank_category_id = rc.rank_category_id
    LEFT JOIN employee_details ed ON e.employee_id = ed.employee_id
    LEFT JOIN employee_government_ids eg ON e.employee_id = eg.employee_id
    LEFT JOIN employee_contacts ec ON e.employee_id = ec.employee_id
    LEFT JOIN employee_disclosures edi ON e.employee_id = edi.employee_id
    WHERE e.employee_id = ?");
$stmt->bind_param("i", $eid);
$stmt->execute();
$emp = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$emp)
    redirectWith(BASE_URL . '/supervisor/employees.php', 'danger', 'Employee not found.');

// Strictly prevent viewing of System Admin profiles in the employee management module
$adminCheck = $conn->prepare("SELECT user_id FROM users WHERE employee_id = ? AND role = 'Admin'");
$adminCheck->bind_param("i", $eid);
$adminCheck->execute();
if ($adminCheck->get_result()->num_rows > 0) {
    $adminCheck->close();
    redirectWith(BASE_URL . '/supervisor/employees.php', 'danger', 'Access denied to system administrator profiles.');
}
$adminCheck->close();

// Map contacts/email to legacy fields for UI compatibility
$emp['email'] = $emp['personal_email'];
$emp['contact_number'] = $emp['mobile_number'];

// Load Residential and Permanent Addresses
$res_addr = $conn->query("SELECT * FROM employee_addresses WHERE employee_id=$eid AND address_type='Residential'")->fetch_assoc();
$perm_addr = $conn->query("SELECT * FROM employee_addresses WHERE employee_id=$eid AND address_type='Permanent'")->fetch_assoc();

// Flatten address fields into $emp for legacy UI compatibility
$addr_fields = ['house_no', 'street', 'subdivision', 'barangay', 'city', 'province', 'zip_code'];
foreach ($addr_fields as $f) {
    $emp['res_' . $f] = $res_addr[$f] ?? '';
    $emp['perm_' . $f] = $perm_addr[$f] ?? '';
}

// Load Emergency Contacts
$emerg = $conn->query("SELECT * FROM employee_emergency_contacts WHERE employee_id=$eid LIMIT 1")->fetch_assoc();
if ($emerg) {
    $emp['emergency_contact_name'] = $emerg['contact_name'];
    $emp['emergency_contact_relationship'] = $emerg['relationship'];
    $emp['emergency_contact_number'] = $emerg['contact_number'];
} else {
    $emp['emergency_contact_name'] = $emp['emergency_contact_relationship'] = $emp['emergency_contact_number'] = '';
}

// Initialize family fields to avoid warnings
$family_presets = ['spouse', 'father', 'mother_maiden'];
foreach ($family_presets as $pf) {
    $emp[$pf . '_surname'] = $emp[$pf . '_first_name'] = $emp[$pf . '_middle_name'] = $emp[$pf . '_occupation'] = '';
    if ($pf !== 'mother_maiden')
        $emp[$pf . '_name_ext'] = '';
}

// Load Family (Spouse, Father, Mother)
$family = $conn->query("SELECT * FROM employee_family WHERE employee_id=$eid")->fetch_all(MYSQLI_ASSOC);
foreach ($family as $member) {
    $pre = strtolower($member['member_type']);
    if ($pre === 'mother') {
        $emp['mother_maiden_surname'] = $member['surname'];
        $emp['mother_first_name'] = $member['first_name'];
        $emp['mother_middle_name'] = $member['middle_name'];
        $emp['mother_occupation'] = $member['occupation'];
    } else {
        $emp[$pre . '_surname'] = $member['surname'];
        $emp[$pre . '_first_name'] = $member['first_name'];
        $emp[$pre . '_middle_name'] = $member['middle_name'];
        if (isset($member['name_extension']))
            $emp[$pre . '_name_ext'] = $member['name_extension'];
        $emp[$pre . '_occupation'] = $member['occupation'];
    }
}

// Load child data
$children = $conn->query("SELECT * FROM employee_children WHERE employee_id=$eid ORDER BY child_id")->fetch_all(MYSQLI_ASSOC);
$siblings = $conn->query("SELECT * FROM employee_siblings WHERE employee_id=$eid ORDER BY sibling_id")->fetch_all(MYSQLI_ASSOC);
$education = $conn->query("SELECT * FROM employee_education WHERE employee_id=$eid ORDER BY education_id")->fetch_all(MYSQLI_ASSOC);
$work = $conn->query("SELECT * FROM employee_work_experience WHERE employee_id=$eid ORDER BY work_id")->fetch_all(MYSQLI_ASSOC);
$trainings = $conn->query("SELECT * FROM employee_trainings WHERE employee_id=$eid ORDER BY training_id")->fetch_all(MYSQLI_ASSOC);
$voluntary = $conn->query("SELECT * FROM employee_voluntary_work WHERE employee_id=$eid ORDER BY voluntary_id")->fetch_all(MYSQLI_ASSOC);
$eligibility = $conn->query("SELECT * FROM employee_eligibility WHERE employee_id=$eid ORDER BY eligibility_id")->fetch_all(MYSQLI_ASSOC);
$skills = $conn->query("SELECT * FROM employee_skills WHERE employee_id=$eid")->fetch_all(MYSQLI_ASSOC);
$recognitions = $conn->query("SELECT * FROM employee_recognitions WHERE employee_id=$eid")->fetch_all(MYSQLI_ASSOC);
$memberships = $conn->query("SELECT * FROM employee_memberships WHERE employee_id=$eid")->fetch_all(MYSQLI_ASSOC);
$real_props = $conn->query("SELECT * FROM employee_real_properties WHERE employee_id=$eid")->fetch_all(MYSQLI_ASSOC);
$personal_props = $conn->query("SELECT * FROM employee_personal_properties WHERE employee_id=$eid")->fetch_all(MYSQLI_ASSOC);
$liabilities = $conn->query("SELECT * FROM employee_liabilities WHERE employee_id=$eid")->fetch_all(MYSQLI_ASSOC);
$refs = $conn->query("SELECT * FROM employee_references WHERE employee_id=$eid ORDER BY reference_id")->fetch_all(MYSQLI_ASSOC);

// Build formatted address strings for the template
function buildAddress(array $emp, string $prefix): string {
    $parts = array_filter([
        $emp[$prefix . 'house_no'] ?? '',
        $emp[$prefix . 'street'] ?? '',
        $emp[$prefix . 'subdivision'] ?? '',
        $emp[$prefix . 'barangay'] ?? '',
        $emp[$prefix . 'city'] ?? '',
        $emp[$prefix . 'province'] ?? '',
        $emp[$prefix . 'zip_code'] ?? '',
    ]);
    return implode(', ', $parts);
}
$resAddr  = buildAddress($emp, 'res_');
$permAddr = buildAddress($emp, 'perm_');

// Build full name strings for family members
function buildFullName(array $emp, string $prefix, string $surnameKey = ''): string {
    $surname    = $emp[($surnameKey ?: $prefix . 'surname')] ?? '';
    $firstName  = $emp[$prefix . 'first_name'] ?? '';
    $middleName = $emp[$prefix . 'middle_name'] ?? '';
    $ext        = $emp[$prefix . 'name_ext'] ?? '';
    return trim(implode(' ', array_filter([$firstName, $middleName, $surname, $ext])));
}
$spouseName = buildFullName($emp, 'spouse_');
$fatherName = buildFullName($emp, 'father_');
$motherName = trim(implode(' ', array_filter([
    $emp['mother_first_name'] ?? '',
    $emp['mother_middle_name'] ?? '',
    $emp['mother_maiden_surname'] ?? '',
])));

// Disclosure list: [flag_field, details_field, label]
$discList = [
    ['is_related_to_company',    'related_details',             'Related to anyone in the company?'],
    ['has_admin_offense',        'admin_offense_details',       'Found guilty of administrative offense?'],
    ['has_criminal_charge',      'criminal_charge_details',     'Charged with a criminal case?'],
    ['has_criminal_conviction',  'criminal_conviction_details', 'Convicted of a criminal offense?'],
    ['has_been_separated',       'separation_details',          'Previously separated from service?'],
    ['is_pwd',                   'pwd_details',                 'Person with Disability (PWD)?'],
    ['is_solo_parent',           'solo_parent_details',         'Solo Parent?'],
    ['has_recent_hospital',      'hospital_details',            'Hospitalized in the last 5 years?'],
    ['has_current_treatment',    'treatment_details',           'Currently under treatment/medication?'],
];

require_once '../includes/header.php';

// Helper
function field($label, $value, $escape = true)
{
    $val = !empty($value) ? ($escape ? e($value) : $value) : '<span class="text-muted">N/A</span>';
    return "<div class='detail-item'><div class='detail-label'>$label</div><div class='detail-value'>$val</div></div>";
}
function yn($v)
{
    return $v ? '<span class="badge bg-warning text-dark">Yes</span>' : '<span class="badge bg-secondary">No</span>';
}

$rankBadgeClassMap = [
    'Executives' => 'rank-badge-executives',
    'Management Team' => 'rank-badge-management',
    'Manager' => 'rank-badge-manager',
    'R&F' => 'rank-badge-rf',
    'Supervisor' => 'rank-badge-supervisor',
];
$rankBadgeClass = $rankBadgeClassMap[$emp['rank_name'] ?? ''] ?? 'rank-badge-default';
?>

<style>
    @media (min-width: 992px) {
        .profile-sticky-col {
            position: sticky;
            top: calc(var(--header-height) + 18px);
            align-self: flex-start;
        }
    }

    .cursor-pointer {
        cursor: pointer;
    }

    .hover-zoom:hover {
        transform: scale(1.05);
    }

    .employee-page-title {
        font-size: 1.65rem;
        font-weight: 700;
        color: var(--text-dark);
    }

    .employee-card-grid {
        display: grid;
        gap: 1.5rem;
    }

    .employee-section-card,
    .employee-profile-card {
        border: 1px solid rgba(15, 23, 42, 0.08);
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .employee-section-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        padding: 1.25rem 1.5rem 0;
    }

    .employee-section-kicker {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--primary-blue);
        margin-bottom: 0.4rem;
    }

    .employee-section-card .card-body {
        padding: 1.5rem;
    }

    .employee-subsection {
        border: 1px solid #edf2f7;
        border-radius: 16px;
        background: #fbfcfe;
        padding: 1.15rem;
        margin-bottom: 1rem;
    }

    .employee-subsection:last-child {
        margin-bottom: 0;
    }

    .employee-subsection-title {
        font-size: 0.82rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--text-muted);
        margin-bottom: 0.9rem;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 0.9rem;
    }

    .detail-item {
        padding: 0.9rem 1rem;
        border-radius: 14px;
        border: 1px solid #edf2f7;
        background: #fff;
    }

    .detail-label {
        color: var(--text-muted);
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        margin-bottom: 0.4rem;
    }

    .detail-value {
        color: var(--text-dark);
        font-size: 0.95rem;
        font-weight: 600;
        line-height: 1.45;
        word-break: break-word;
    }

    .profile-meta-list {
        display: grid;
        gap: 0.85rem;
    }

    .profile-meta-item {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        text-align: left;
        padding: 0.8rem 0.9rem;
        border-radius: 14px;
        background: #f8fafc;
        border: 1px solid #edf2f7;
    }

    .profile-meta-icon {
        width: 2rem;
        height: 2rem;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(13, 110, 253, 0.12);
        color: var(--primary-blue);
        flex-shrink: 0;
    }

    .profile-meta-label {
        display: block;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-muted);
    }

    .profile-meta-value {
        display: block;
        font-size: 0.92rem;
        font-weight: 600;
        color: var(--text-dark);
        line-height: 1.4;
        word-break: break-word;
    }

    .rank-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        padding: 0.45rem 0.7rem;
        border-radius: 999px;
    }

    .rank-badge-executives {
        background: #6f42c1;
        color: #fff;
    }

    .rank-badge-management {
        background: #0d6efd;
        color: #fff;
    }

    .rank-badge-manager {
        background: #198754;
        color: #fff;
    }

    .rank-badge-rf {
        background: #fd7e14;
        color: #fff;
    }

    .rank-badge-supervisor {
        background: #20c997;
        color: #073b35;
    }

    .rank-badge-default {
        background: #6c757d;
        color: #fff;
    }

    .badge-cloud {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .employee-table-wrap {
        border: 1px solid #edf2f7;
        border-radius: 16px;
        overflow-x: auto;
        overflow-y: hidden;
        background: #fff;
        -webkit-overflow-scrolling: touch;
    }

    .employee-table-wrap .table {
        margin-bottom: 0;
    }

    .employee-table-wrap thead th {
        border-bottom: 1px solid #edf2f7;
        background: #f8fafc;
        color: var(--text-muted);
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .employee-table-wrap tbody td {
        vertical-align: top;
    }

    .empty-state {
        border: 1px dashed #d7e0ea;
        border-radius: 16px;
        padding: 2rem 1.25rem;
        text-align: center;
        color: var(--text-muted);
        background: #fbfcfe;
    }

    .empty-state i {
        font-size: 1.9rem;
        opacity: 0.35;
        margin-bottom: 0.75rem;
    }

    .empty-state p {
        margin-bottom: 0;
    }

    .disclosure-list {
        display: grid;
        gap: 0.85rem;
    }

    .disclosure-item {
        padding: 1rem 1.1rem;
        border-radius: 16px;
        border: 1px solid #edf2f7;
        background: #fbfcfe;
    }

    .recognition-list .list-group-item {
        border-left: 0;
        border-right: 0;
    }

    .recognition-list .list-group-item:first-child {
        border-top: 0;
    }

    .recognition-list .list-group-item:last-child {
        border-bottom: 0;
    }

    @media (max-width: 767.98px) {
        .employee-section-header {
            padding: 1.1rem 1.1rem 0;
        }

        .employee-section-card .card-body,
        .employee-profile-card .card-body {
            padding: 1.1rem;
        }

        .employee-table-wrap,
        .employee-table-wrap table,
        .employee-table-wrap tbody,
        .employee-table-wrap tr,
        .employee-table-wrap td {
            display: block;
            width: 100%;
        }

        .employee-table-wrap thead {
            display: none;
        }

        .employee-table-wrap tr {
            padding: 1rem;
            border-bottom: 1px solid #edf2f7;
        }

        .employee-table-wrap tr:last-child {
            border-bottom: none;
        }

        .employee-table-wrap td {
            border: none !important;
            padding: 0.45rem 0;
        }

        .employee-table-wrap td::before {
            content: attr(data-label);
            display: block;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            margin-bottom: 0.2rem;
        }
    }
</style>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <p class="text-muted mb-1">Employee Personal Data Sheet</p>
        <h1 class="employee-page-title mb-0">Employee Information</h1>
    </div>
    <a href="<?php echo htmlspecialchars($return_to, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-4 col-xl-3 profile-sticky-col">
        <div class="content-card employee-profile-card text-center">
            <div class="card-body py-4">
                <div class="position-relative d-inline-block cursor-pointer mb-3"
                    onclick="viewFullImage('<?php echo getEmployeeAvatar($emp['profile_picture']); ?>', '<?php echo e($emp['first_name'] . ' ' . $emp['last_name']); ?>')">
                    <img src="<?php echo getEmployeeAvatar($emp['profile_picture']); ?>"
                        class="rounded-circle img-thumbnail shadow-sm hover-zoom"
                        style="width:120px;height:120px;object-fit:cover; transition: transform 0.2s;">
                    <div class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle p-1 border border-white"
                        style="width: 28px; height: 28px; font-size: 0.75rem; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-search-plus"></i>
                    </div>
                </div>

                <h5 class="mb-1">
                    <?php echo e($emp['first_name'] . ' ' . ($emp['middle_name'] ? $emp['middle_name'] . ' ' : '') . $emp['last_name'] . ($emp['name_extension'] ? ' ' . $emp['name_extension'] : '')); ?>
                </h5>
                <p class="text-muted mb-2"><?php echo e($emp['job_title']); ?></p>

                <?php if (!empty($emp['rank_name'])): ?>
                    <div class="mb-2">
                        <span class="rank-badge <?php echo $rankBadgeClass; ?>">
                            <i class="fas fa-layer-group"></i>Rank: <?php echo e($emp['rank_name']); ?>
                        </span>
                    </div>
                <?php endif; ?>

                <p class="company-id-text small mb-3">Company ID:
                    <span class="company-id-value"><?php echo e(getEmployeeDisplayId($emp)); ?></span>
                </p>

                <div class="d-flex justify-content-center flex-wrap gap-2 mb-3">
                    <span class="badge <?php echo $emp['is_active'] ? 'bg-success' : 'bg-danger'; ?> px-3 py-2">
                        <?php echo $emp['is_active'] ? 'Active' : 'Inactive'; ?>
                    </span>
                    <span class="badge bg-primary px-3 py-2"><?php echo e($emp['employment_status']); ?></span>
                </div>

                <?php if (in_array($emp['employment_status'], ['OJT', 'Probationary', 'Project Based', 'Project-Based', 'Trainee'], true)): ?>
                    <div class="alert alert-info text-start py-2 px-3 mt-2 mb-3 border-start border-4 border-info shadow-sm">
                        <div class="fw-bold text-info mb-2"><i class="fas fa-clock me-1"></i>Contract Period</div>
                        <div class="d-flex justify-content-between gap-3 small">
                            <span class="text-muted">Start</span>
                            <span class="fw-bold"><?php echo formatDate($emp['contract_start_date']); ?></span>
                        </div>
                        <div class="d-flex justify-content-between gap-3 small">
                            <span class="text-muted">End</span>
                            <span class="fw-bold"><?php echo formatDate($emp['contract_end_date']); ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="profile-meta-list mb-3">
                    <div class="profile-meta-item">
                        <span class="profile-meta-icon"><i class="fas fa-envelope"></i></span>
                        <div>
                            <span class="profile-meta-label">Email</span>
                            <span class="profile-meta-value"><?php echo e($emp['email'] ?: 'N/A'); ?></span>
                        </div>
                    </div>
                    <div class="profile-meta-item">
                        <span class="profile-meta-icon"><i class="fas fa-phone"></i></span>
                        <div>
                            <span class="profile-meta-label">Mobile</span>
                            <span class="profile-meta-value"><?php echo e($emp['contact_number'] ?: 'N/A'); ?></span>
                        </div>
                    </div>
                    <div class="profile-meta-item">
                        <span class="profile-meta-icon"><i class="fas fa-building"></i></span>
                        <div>
                            <span class="profile-meta-label">Branch</span>
                            <span class="profile-meta-value"><?php echo e($emp['branch_name'] ?: 'N/A'); ?></span>
                        </div>
                    </div>
                    <div class="profile-meta-item">
                        <span class="profile-meta-icon"><i class="fas fa-calendar"></i></span>
                        <div>
                            <span class="profile-meta-label">Hire Date</span>
                            <span class="profile-meta-value"><?php echo formatDate($emp['hire_date']); ?></span>
                        </div>
                    </div>
                </div>

                <a href="<?php echo BASE_URL; ?>/supervisor/edit-employee.php?id=<?php echo $eid; ?>&return=<?php echo urlencode($return_to); ?>"
                    class="btn btn-primary w-100">
                    <i class="fas fa-edit me-2"></i>Edit
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-8 col-xl-9">
        <div class="row g-4">
            <div class="col-xl-6">
                <div class="content-card employee-section-card h-100">
                    <div class="employee-section-header">
                        <div>
                            <div class="employee-section-kicker"><i class="fas fa-user"></i>Personal</div>
                            <h5 class="mb-0">Personal Information</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="detail-grid">
                            <?php
                            echo field('Surname', $emp['last_name']);
                            echo field('First Name', $emp['first_name']);
                            echo field('Middle Name', $emp['middle_name']);
                            echo field('Name Extension', $emp['name_extension']);
                            echo field('Date of Birth', $emp['date_of_birth'] ? formatDate($emp['date_of_birth']) : '');
                            echo field('Place of Birth', $emp['place_of_birth']);
                            echo field('Gender', $emp['gender']);
                            echo field('Civil Status', $emp['civil_status']);
                            echo field('Height', $emp['height_m'] ? $emp['height_m'] . ' m' : '');
                            echo field('Weight', $emp['weight_kg'] ? $emp['weight_kg'] . ' kg' : '');
                            echo field('Blood Type', $emp['blood_type']);
                            echo field('Citizenship', $emp['citizenship']);
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="content-card employee-section-card h-100">
                    <div class="employee-section-header">
                        <div>
                            <div class="employee-section-kicker"><i class="fas fa-address-card"></i>Contact</div>
                            <h5 class="mb-0">Contact & Address</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="employee-subsection">
                            <div class="employee-subsection-title">Contact Channels</div>
                            <div class="detail-grid">
                                <?php
                                echo field('Telephone', $emp['telephone_number']);
                                echo field('Mobile', $emp['contact_number']);
                                echo field('Email', $emp['email']);
                                ?>
                            </div>
                        </div>
                        <div class="employee-subsection">
                            <div class="employee-subsection-title">Residential Address</div>
                            <div class="detail-grid">
                                <?php echo field('Address', $resAddr); ?>
                            </div>
                        </div>
                        <div class="employee-subsection">
                            <div class="employee-subsection-title">Permanent Address</div>
                            <div class="detail-grid">
                                <?php echo field('Address', $permAddr); ?>
                            </div>
                        </div>
                        <div class="employee-subsection">
                            <div class="employee-subsection-title">Emergency Contacts</div>
                            <?php 
                            $emergContacts = $conn->query("SELECT * FROM employee_emergency_contacts WHERE employee_id=$eid ORDER BY is_primary DESC, emergency_id ASC")->fetch_all(MYSQLI_ASSOC);
                            if (!empty($emergContacts)):
                                foreach ($emergContacts as $c):
                            ?>
                                <div class="detail-grid mb-3 pb-2 <?php echo $c['is_primary'] ? 'border-start border-3 border-warning ps-2' : ''; ?>" style="grid-gap: 8px;">
                                    <?php
                                    echo field('Name', e($c['contact_name']) . ($c['is_primary'] ? ' <span class="badge bg-warning text-dark ms-1" style="font-size:0.68rem; padding: 2px 6px;"><i class="fas fa-star"></i> Primary</span>' : ''), false);
                                    echo field('Relationship', e($c['relationship']));
                                    echo field('Number', e($c['contact_number']));
                                    ?>
                                </div>
                            <?php 
                                endforeach;
                            else:
                                echo '<p class="text-muted small">No emergency contacts listed.</p>';
                            endif;
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="content-card employee-section-card">
                    <div class="employee-section-header">
                        <div>
                            <div class="employee-section-kicker"><i class="fas fa-heart"></i>Family</div>
                            <h5 class="mb-0">Family Information</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-lg-4">
                                <div class="employee-subsection h-100">
                                    <div class="employee-subsection-title">Spouse</div>
                                    <div class="detail-grid">
                                        <?php
                                        echo field('Name', $spouseName);
                                        echo field('Occupation', $emp['spouse_occupation']);
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="employee-subsection h-100">
                                    <div class="employee-subsection-title">Father</div>
                                    <div class="detail-grid">
                                        <?php
                                        echo field('Name', $fatherName);
                                        echo field('Occupation', $emp['father_occupation']);
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="employee-subsection h-100">
                                    <div class="employee-subsection-title">Mother (Maiden)</div>
                                    <div class="detail-grid">
                                        <?php
                                        echo field('Name', $motherName);
                                        echo field('Occupation', $emp['mother_occupation'] ?? '');
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-xl-6">
                                <div class="employee-subsection h-100">
                                    <div class="employee-subsection-title">Children<?php echo !empty($children) ? ' (' . count($children) . ')' : ''; ?></div>
                                    <?php if (!empty($children)): ?>
                                        <div class="employee-table-wrap">
                                            <table class="table table-sm align-middle mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Name</th>
                                                        <th>Date of Birth</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($children as $ch): ?>
                                                        <tr>
                                                            <td data-label="Name"><?php echo e(trim($ch['first_name'] . ' ' . $ch['middle_name'] . ' ' . $ch['surname'])); ?></td>
                                                            <td data-label="Date of Birth"><?php echo $ch['date_of_birth'] ? formatDate($ch['date_of_birth']) : 'N/A'; ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="empty-state"><i class="fas fa-child d-block"></i><p>No children recorded.</p></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="col-xl-6">
                                <div class="employee-subsection h-100">
                                    <div class="employee-subsection-title">Siblings<?php echo !empty($siblings) ? ' (' . count($siblings) . ')' : ''; ?></div>
                                    <?php if (!empty($siblings)): ?>
                                        <div class="employee-table-wrap">
                                            <table class="table table-sm align-middle mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Name</th>
                                                        <th>Date of Birth</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($siblings as $sb): ?>
                                                        <tr>
                                                            <td data-label="Name"><?php echo e(trim($sb['first_name'] . ' ' . $sb['middle_name'] . ' ' . $sb['surname'])); ?></td>
                                                            <td data-label="Date of Birth"><?php echo $sb['date_of_birth'] ? formatDate($sb['date_of_birth']) : 'N/A'; ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="empty-state"><i class="fas fa-users d-block"></i><p>No siblings recorded.</p></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="content-card employee-section-card h-100">
                    <div class="employee-section-header">
                        <div>
                            <div class="employee-section-kicker"><i class="fas fa-graduation-cap"></i>Education</div>
                            <h5 class="mb-0">Education Background</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($education)): ?>
                            <div class="employee-table-wrap">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Level</th>
                                            <th>School</th>
                                            <th>Degree</th>
                                            <th>Period</th>
                                            <th>Year Grad</th>
                                            <th>Honors</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($education as $ed): ?>
                                            <tr>
                                                <td data-label="Level"><span class="badge bg-info"><?php echo e($ed['education_level']); ?></span></td>
                                                <td data-label="School" class="fw-bold"><?php echo e($ed['school_name']); ?></td>
                                                <td data-label="Degree"><?php echo e($ed['degree_course'] ?: 'N/A'); ?></td>
                                                <td data-label="Period"><?php echo ($ed['period_from'] ? formatDate($ed['period_from'], 'Y') : '') . ' - ' . ($ed['period_to'] ? formatDate($ed['period_to'], 'Y') : ''); ?></td>
                                                <td data-label="Year Grad"><?php echo e($ed['year_graduated'] ?: 'N/A'); ?></td>
                                                <td data-label="Honors"><?php echo e($ed['honors_received'] ?: 'N/A'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state"><i class="fas fa-graduation-cap d-block"></i><p>No education records.</p></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="content-card employee-section-card h-100">
                    <div class="employee-section-header">
                        <div>
                            <div class="employee-section-kicker"><i class="fas fa-briefcase"></i>Work</div>
                            <h5 class="mb-0">Work Experience</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($work)): ?>
                            <div class="employee-table-wrap">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Period</th>
                                            <th>Position & Company</th>
                                            <th>Details</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($work as $w): ?>
                                            <tr>
                                                <td data-label="Period">
                                                    <div class="fw-bold text-primary"><?php echo ($w['date_from'] ? formatDate($w['date_from'], 'Y') : 'N/A'); ?></div>
                                                    <div class="small text-muted">to</div>
                                                    <div class="fw-bold"><?php echo ($w['date_to'] ? formatDate($w['date_to'], 'Y') : 'Present'); ?></div>
                                                </td>
                                                <td data-label="Position & Company">
                                                    <div class="fw-bold mb-1"><?php echo e($w['job_title']); ?></div>
                                                    <div class="text-muted small"><i class="fas fa-building me-1"></i><?php echo e($w['company_name']); ?></div>
                                                </td>
                                                <td data-label="Details">
                                                    <div class="small"><strong>Salary:</strong> <?php echo $w['monthly_salary'] ? '₱' . number_format($w['monthly_salary'], 2) : 'N/A'; ?></div>
                                                    <div class="small"><strong>Status:</strong> <?php echo e($w['appointment_status'] ?: 'N/A'); ?></div>
                                                    <?php if ($w['reason_for_leaving']): ?>
                                                        <div class="small text-danger"><strong>Leaving:</strong> <?php echo e($w['reason_for_leaving']); ?></div>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state"><i class="fas fa-briefcase d-block"></i><p>No work experience recorded.</p></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="content-card employee-section-card">
                    <div class="employee-section-header">
                        <div>
                            <div class="employee-section-kicker"><i class="fas fa-certificate"></i>Training</div>
                            <h5 class="mb-0">Training, Eligibility & Professional Development</h5>
                        </div>
                    </div>
                    <div class="card-body employee-card-grid">
                        <div class="employee-subsection">
                            <div class="employee-subsection-title">Training Programs</div>
                            <?php if (!empty($trainings)): ?>
                                <div class="employee-table-wrap">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Period</th>
                                                <th>Training Details</th>
                                                <th>Hours & Conducted By</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($trainings as $t): ?>
                                                <tr>
                                                    <td data-label="Period">
                                                        <div class="fw-bold text-primary"><?php echo ($t['date_from'] ? formatDate($t['date_from'], 'Y') : 'N/A'); ?></div>
                                                        <div class="small text-muted">to</div>
                                                        <div class="fw-bold"><?php echo ($t['date_to'] ? formatDate($t['date_to'], 'Y') : 'N/A'); ?></div>
                                                    </td>
                                                    <td data-label="Training Details">
                                                        <div class="fw-bold mb-1"><?php echo e($t['training_title']); ?></div>
                                                        <div class="badge bg-secondary small"><?php echo e($t['training_type'] ?: 'General'); ?></div>
                                                    </td>
                                                    <td data-label="Hours & Conducted By">
                                                        <div class="small"><strong>Duration:</strong> <?php echo $t['no_of_hours'] ? (float) $t['no_of_hours'] . ' hrs' : 'N/A'; ?></div>
                                                        <div class="small"><strong>Conducted By:</strong> <?php echo e($t['conducted_by'] ?: 'N/A'); ?></div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="empty-state"><i class="fas fa-chalkboard-teacher d-block"></i><p>No training records.</p></div>
                            <?php endif; ?>
                        </div>

                        <div class="employee-subsection">
                            <div class="employee-subsection-title">Voluntary Work</div>
                            <?php if (!empty($voluntary)): ?>
                                <div class="employee-table-wrap">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Period</th>
                                                <th>Organization & Position</th>
                                                <th>Details</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($voluntary as $vol): ?>
                                                <tr>
                                                    <td data-label="Period">
                                                        <div class="fw-bold text-primary"><?php echo ($vol['date_from'] ? formatDate($vol['date_from'], 'Y') : 'N/A'); ?></div>
                                                        <div class="small text-muted">to</div>
                                                        <div class="fw-bold"><?php echo ($vol['date_to'] ? formatDate($vol['date_to'], 'Y') : 'N/A'); ?></div>
                                                    </td>
                                                    <td data-label="Organization & Position">
                                                        <div class="fw-bold mb-1"><?php echo e($vol['organization_name']); ?></div>
                                                        <div class="small text-muted"><i class="fas fa-user-tag me-1"></i><?php echo e($vol['position_nature'] ?: 'N/A'); ?></div>
                                                    </td>
                                                    <td data-label="Details">
                                                        <div class="small"><strong>Total Hours:</strong> <?php echo $vol['no_of_hours'] ? (float) $vol['no_of_hours'] . ' hrs' : 'N/A'; ?></div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="empty-state"><i class="fas fa-hands-helping d-block"></i><p>No voluntary work records.</p></div>
                            <?php endif; ?>
                        </div>

                        <div class="employee-subsection">
                            <div class="employee-subsection-title">Eligibility & Licenses</div>
                            <?php if (!empty($eligibility)): ?>
                                <div class="employee-table-wrap">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Title & License</th>
                                                <th>Validity</th>
                                                <th>Exam Info</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($eligibility as $el): ?>
                                                <tr>
                                                    <td data-label="Title & License">
                                                        <div class="fw-bold text-primary mb-1"><?php echo e($el['license_title']); ?></div>
                                                        <div class="small text-muted">No: <?php echo e($el['license_number'] ?: 'N/A'); ?></div>
                                                    </td>
                                                    <td data-label="Validity">
                                                        <div class="fw-bold text-primary"><?php echo ($el['date_from'] ? formatDate($el['date_from'], 'Y') : 'N/A'); ?></div>
                                                        <div class="small text-muted">to</div>
                                                        <div class="fw-bold"><?php echo ($el['date_to'] ? formatDate($el['date_to'], 'Y') : 'N/A'); ?></div>
                                                    </td>
                                                    <td data-label="Exam Info">
                                                        <div class="small"><strong>Date:</strong> <?php echo $el['date_of_exam'] ? formatDate($el['date_of_exam']) : 'N/A'; ?></div>
                                                        <div class="small"><strong>Place:</strong> <?php echo e($el['place_of_exam'] ?: 'N/A'); ?></div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="empty-state"><i class="fas fa-id-badge d-block"></i><p>No eligibility or license records.</p></div>
                            <?php endif; ?>
                        </div>

                        <div class="row g-3">
                            <div class="col-lg-4">
                                <div class="employee-subsection h-100">
                                    <div class="employee-subsection-title">Skills & Hobbies</div>
                                    <?php if (!empty($skills)): ?>
                                        <div class="badge-cloud">
                                            <?php foreach ($skills as $sk): ?>
                                                <span class="badge bg-info"><?php echo e($sk['skill_name']); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="empty-state"><i class="fas fa-star d-block"></i><p>No skills recorded.</p></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="employee-subsection h-100">
                                    <div class="employee-subsection-title">Recognitions</div>
                                    <?php if (!empty($recognitions)): ?>
                                        <div class="list-group list-group-flush border rounded-3 recognition-list">
                                            <?php foreach ($recognitions as $rc): ?>
                                                <div class="list-group-item">
                                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                                        <h6 class="mb-1 fw-bold"><?php echo e($rc['recognition_title']); ?></h6>
                                                        <?php if (!empty($rc['date_awarded'])): ?>
                                                            <small class="text-muted"><?php echo formatDate($rc['date_awarded'], 'M d, Y'); ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php if (!empty($rc['issued_by'])): ?>
                                                        <small class="text-primary"><?php echo e($rc['issued_by']); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="empty-state"><i class="fas fa-award d-block"></i><p>No recognitions recorded.</p></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="employee-subsection h-100">
                                    <div class="employee-subsection-title">Memberships</div>
                                    <?php if (!empty($memberships)): ?>
                                        <div class="badge-cloud">
                                            <?php foreach ($memberships as $mb): ?>
                                                <span class="badge bg-secondary"><?php echo e($mb['organization_name']); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="empty-state"><i class="fas fa-users-cog d-block"></i><p>No memberships recorded.</p></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="content-card employee-section-card h-100">
                    <div class="employee-section-header">
                        <div>
                            <div class="employee-section-kicker"><i class="fas fa-clipboard-list"></i>Disclosures</div>
                            <h5 class="mb-0">Personal Disclosures</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="disclosure-list">
                            <?php foreach ($discList as $d): ?>
                                <div class="disclosure-item">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <?php echo yn($emp[$d[0]]); ?>
                                        <span class="fw-semibold"><?php echo $d[2]; ?></span>
                                    </div>
                                    <?php if (!empty($emp[$d[1]])): ?>
                                        <div class="small text-muted"><?php echo e($emp[$d[1]]); ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="content-card employee-section-card h-100">
                    <div class="employee-section-header">
                        <div>
                            <div class="employee-section-kicker"><i class="fas fa-id-card"></i>Employment</div>
                            <h5 class="mb-0">Government IDs & Employment</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="employee-subsection">
                            <div class="employee-subsection-title">Government IDs</div>
                            <div class="detail-grid">
                                <?php
                                echo field('SSS Number', $emp['sss_number']);
                                echo field('PhilHealth Number', $emp['philhealth_number']);
                                echo field('Pag-IBIG Number', $emp['pagibig_number']);
                                echo field('TIN Number', $emp['tin_number']);
                                ?>
                            </div>
                        </div>
                        <div class="employee-subsection">
                            <div class="employee-subsection-title">Employment Details</div>
                            <div class="detail-grid">
                                <?php
                                echo field('Department', $emp['department_name']);
                                echo field('Job Title', $emp['job_title']);
                                echo field('Branch', $emp['branch_name']);
                                echo field('Hire Date', formatDate($emp['hire_date']));
                                echo field('Employment Status', $emp['employment_status']);
                                echo field('Employment Type', $emp['employment_type']);
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="content-card employee-section-card">
                    <div class="employee-section-header">
                        <div>
                            <div class="employee-section-kicker"><i class="fas fa-file-invoice-dollar"></i>SALN</div>
                            <h5 class="mb-0">Assets, Properties & Liabilities</h5>
                        </div>
                    </div>
                    <div class="card-body employee-card-grid">
                        <div class="employee-subsection">
                            <div class="employee-subsection-title">Real Properties</div>
                            <?php if (!empty($real_props)): ?>
                                <div class="employee-table-wrap">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Description & Kind</th>
                                                <th>Location</th>
                                                <th>Values</th>
                                                <th>Acquisition</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($real_props as $rp): ?>
                                                <tr>
                                                    <td data-label="Description & Kind">
                                                        <div class="fw-bold"><?php echo e($rp['description']); ?></div>
                                                        <div class="small text-muted"><?php echo e($rp['kind']); ?></div>
                                                    </td>
                                                    <td data-label="Location"><?php echo e($rp['exact_location']); ?></td>
                                                    <td data-label="Values">
                                                        <div class="small">Assessed: ₱<?php echo number_format($rp['assessed_value'], 2); ?></div>
                                                        <div class="small">Market: ₱<?php echo number_format($rp['market_value'], 2); ?></div>
                                                    </td>
                                                    <td data-label="Acquisition">
                                                        <div class="small"><?php echo e($rp['acquisition_year_mode']); ?></div>
                                                        <div class="small fw-bold">Cost: ₱<?php echo number_format($rp['acquisition_cost'], 2); ?></div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="empty-state"><i class="fas fa-home d-block"></i><p>No real properties recorded.</p></div>
                            <?php endif; ?>
                        </div>

                        <div class="employee-subsection">
                            <div class="employee-subsection-title">Personal Properties</div>
                            <?php if (!empty($personal_props)): ?>
                                <div class="employee-table-wrap">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Description</th>
                                                <th>Year Acquired</th>
                                                <th>Acquisition Cost</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($personal_props as $pp): ?>
                                                <tr>
                                                    <td data-label="Description"><?php echo e($pp['description']); ?></td>
                                                    <td data-label="Year Acquired"><?php echo e($pp['year_acquired']); ?></td>
                                                    <td data-label="Acquisition Cost">₱<?php echo number_format($pp['acquisition_cost'], 2); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="empty-state"><i class="fas fa-car d-block"></i><p>No personal properties recorded.</p></div>
                            <?php endif; ?>
                        </div>

                        <div class="employee-subsection">
                            <div class="employee-subsection-title">Liabilities</div>
                            <?php if (!empty($liabilities)): ?>
                                <div class="employee-table-wrap">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Nature of Liability</th>
                                                <th>Name of Creditor</th>
                                                <th>Outstanding Balance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($liabilities as $liab): ?>
                                                <tr>
                                                    <td data-label="Nature of Liability"><?php echo e($liab['nature_of_liability']); ?></td>
                                                    <td data-label="Name of Creditor"><?php echo e($liab['creditor_name']); ?></td>
                                                    <td data-label="Outstanding Balance">₱<?php echo number_format($liab['outstanding_balance'], 2); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="empty-state"><i class="fas fa-file-invoice-dollar d-block"></i><p>No liabilities recorded.</p></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="content-card employee-section-card">
                    <div class="employee-section-header">
                        <div>
                            <div class="employee-section-kicker"><i class="fas fa-address-book"></i>References</div>
                            <h5 class="mb-0">Character References</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($refs)): ?>
                            <div class="employee-table-wrap">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Full Name</th>
                                            <th>Address</th>
                                            <th>Contact Number</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($refs as $rf): ?>
                                            <tr>
                                                <td data-label="Full Name" class="fw-bold"><?php echo e($rf['reference_name']); ?></td>
                                                <td data-label="Address"><?php echo e($rf['reference_address'] ?: 'N/A'); ?></td>
                                                <td data-label="Contact Number"><?php echo e($rf['reference_telephone'] ?: 'N/A'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state"><i class="fas fa-address-book d-block"></i><p>No character references recorded.</p></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Full Image View Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-transparent border-0 shadow-lg position-relative">
            <button type="button" class="btn-close btn-close-white p-2 rounded-circle shadow position-absolute" 
                style="top: 15px; right: 15px; z-index: 1100; background-color: rgba(0,0,0,0.6); border: none;"
                data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="modal-body p-0 text-center">
                <img id="fullImage" src="" class="img-fluid rounded shadow" style="max-height: 85vh; background-color: #ffffff;">
                <h6 id="fullImageName" class="text-white mt-3 fw-bold"></h6>
            </div>
        </div>
    </div>
</div>

<script>
    function viewFullImage(src, name) {
        const modal = new bootstrap.Modal(document.getElementById('imageModal'));
        document.getElementById('fullImage').src = src;
        document.getElementById('fullImageName').textContent = name;
        modal.show();
    }
</script>

<?php require_once '../includes/footer.php'; ?>
