<?php
$page_title = 'View Employee';
require_once '../includes/session-check.php';
checkRole(['HR Staff']);
require_once '../includes/functions.php';

$eid = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($eid <= 0) redirectWith(BASE_URL . '/staff/search-employees.php', 'danger', 'Invalid employee ID.');

// Fetch employee details (no branch restriction for staff — read-only access to all)
$stmt = $conn->prepare("SELECT e.*, b.branch_name, d.department_name,
    ed.height_m, ed.weight_kg, ed.blood_type, ed.citizenship,
    eg.sss_number, eg.philhealth_number, eg.pagibig_number, eg.tin_number,
    ec.telephone_number, ec.mobile_number, ec.personal_email
    FROM employees e 
    LEFT JOIN branches b ON e.branch_id = b.branch_id 
    LEFT JOIN departments d ON e.department_id = d.department_id
    LEFT JOIN employee_details ed ON e.employee_id = ed.employee_id
    LEFT JOIN employee_government_ids eg ON e.employee_id = eg.employee_id
    LEFT JOIN employee_contacts ec ON e.employee_id = ec.employee_id
    WHERE e.employee_id = ? AND e.is_active = 1");
$stmt->bind_param("i", $eid);
$stmt->execute();
$emp = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$emp) redirectWith(BASE_URL . '/staff/search-employees.php', 'danger', 'Employee not found.');

// Load Addresses
$res_addr = $conn->query("SELECT * FROM employee_addresses WHERE employee_id=$eid AND address_type='Residential'")->fetch_assoc();
$perm_addr = $conn->query("SELECT * FROM employee_addresses WHERE employee_id=$eid AND address_type='Permanent'")->fetch_assoc();

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

// Load Education, Work, Skills, Trainings (Read Only)
$education = $conn->query("SELECT * FROM employee_education WHERE employee_id=$eid ORDER BY education_id")->fetch_all(MYSQLI_ASSOC);
$work = $conn->query("SELECT * FROM employee_work_experience WHERE employee_id=$eid ORDER BY work_id")->fetch_all(MYSQLI_ASSOC);
$skills = $conn->query("SELECT * FROM employee_skills WHERE employee_id=$eid")->fetch_all(MYSQLI_ASSOC);
$trainings = $conn->query("SELECT * FROM employee_trainings WHERE employee_id=$eid ORDER BY training_id")->fetch_all(MYSQLI_ASSOC);

require_once '../includes/header.php';

// Helper for UI
function field($label, $value, $escape = true) {
    $is_company_id = strcasecmp($label, 'Company ID') === 0;
    $val = !empty($value) ? ($escape ? e($value) : $value) : '<span class="text-muted">N/A</span>';
    $label_class = $is_company_id ? 'company-id-text detail-label' : 'detail-label';
    $value_class = $is_company_id ? 'company-id-value detail-value' : 'detail-value';
    return "<div class='detail-item'><div class='$label_class'>$label</div><div class='$value_class'>$val</div></div>";
}
?>

<?php
$fullName = $emp['first_name'] . ' ' . ($emp['middle_name'] ? $emp['middle_name'] . ' ' : '') . $emp['last_name'];
$resAddr = trim(implode(', ', array_filter([$emp['res_house_no'], $emp['res_street'], $emp['res_subdivision'] ?? '', $emp['res_barangay'], $emp['res_city'], $emp['res_province'], $emp['res_zip_code'] ?? ''])));
$permAddr = trim(implode(', ', array_filter([$emp['perm_house_no'], $emp['perm_street'], $emp['perm_subdivision'] ?? '', $emp['perm_barangay'], $emp['perm_city'], $emp['perm_province'], $emp['perm_zip_code'] ?? ''])));
?>

<style>
@media (min-width: 992px) {
    .profile-sticky-col {
        position: sticky;
        top: calc(var(--header-height) + 18px);
        align-self: flex-start;
    }
}

.employee-page-title {
    font-size: 1.65rem;
    font-weight: 700;
    color: var(--text-dark);
}

.employee-profile-card,
.employee-section-card {
    border: 1px solid rgba(15, 23, 42, 0.08);
    box-shadow: 0 14px 30px rgba(15, 23, 42, 0.06);
    overflow: hidden;
}

.employee-section-header {
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

.employee-section-card .card-body,
.employee-profile-card .card-body {
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
        <p class="text-muted mb-1"><i class="fas fa-lock me-1"></i>Employee Profile (Read Only)</p>
        <h1 class="employee-page-title mb-0">Employee Information</h1>
    </div>
    <a href="<?php echo BASE_URL; ?>/staff/search-employees.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Search
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-4 col-xl-3 profile-sticky-col">
        <div class="content-card employee-profile-card h-100 text-center">
            <div class="card-body py-4">
                <img src="<?php echo getEmployeeAvatar($emp['profile_picture']); ?>" class="rounded-circle img-thumbnail shadow-sm mb-3" style="width:120px;height:120px;object-fit:cover;">
                <h5 class="mb-1"><?php echo e($fullName); ?></h5>
                <p class="text-muted mb-2"><?php echo e($emp['job_title']); ?></p>
                <p class="company-id-text small mb-3">Company ID: <span class="company-id-value"><?php echo e($emp['employee_code'] ?: 'N/A'); ?></span></p>
                <div class="d-flex justify-content-center flex-wrap gap-2 mb-3">
                    <span class="badge bg-success px-3 py-2">Active</span>
                    <?php if (!empty($emp['employment_status'])): ?>
                        <span class="badge bg-primary px-3 py-2"><?php echo e($emp['employment_status']); ?></span>
                    <?php endif; ?>
                </div>
                <div class="profile-meta-list">
                    <div class="profile-meta-item">
                        <span class="profile-meta-icon"><i class="fas fa-envelope"></i></span>
                        <div>
                            <span class="profile-meta-label">Email</span>
                            <span class="profile-meta-value"><?php echo e($emp['personal_email'] ?: 'N/A'); ?></span>
                        </div>
                    </div>
                    <div class="profile-meta-item">
                        <span class="profile-meta-icon"><i class="fas fa-phone"></i></span>
                        <div>
                            <span class="profile-meta-label">Mobile</span>
                            <span class="profile-meta-value"><?php echo e($emp['mobile_number'] ?: 'N/A'); ?></span>
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
                        <span class="profile-meta-icon"><i class="fas fa-sitemap"></i></span>
                        <div>
                            <span class="profile-meta-label">Department</span>
                            <span class="profile-meta-value"><?php echo e($emp['department_name'] ?: 'N/A'); ?></span>
                        </div>
                    </div>
                </div>
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
                            echo field('Full Name', $fullName);
                            echo field('Date of Birth', formatDate($emp['date_of_birth']));
                            echo field('Place of Birth', $emp['place_of_birth']);
                            echo field('Gender', $emp['gender']);
                            echo field('Civil Status', $emp['civil_status']);
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
                                echo field('Email', $emp['personal_email']);
                                echo field('Mobile', $emp['mobile_number']);
                                echo field('Telephone', $emp['telephone_number']);
                                ?>
                            </div>
                        </div>
                        <div class="employee-subsection">
                            <div class="employee-subsection-title">Residential Address</div>
                            <div class="detail-grid"><?php echo field('Address', $resAddr); ?></div>
                        </div>
                        <div class="employee-subsection">
                            <div class="employee-subsection-title">Permanent Address</div>
                            <div class="detail-grid"><?php echo field('Address', $permAddr); ?></div>
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

            <div class="col-xl-6">
                <div class="content-card employee-section-card h-100">
                    <div class="employee-section-header">
                        <div>
                            <div class="employee-section-kicker"><i class="fas fa-briefcase"></i>Employment</div>
                            <h5 class="mb-0">Employment Details</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="employee-subsection">
                            <div class="employee-subsection-title">Employment Profile</div>
                            <div class="detail-grid">
                                <?php
                                echo field('Company ID', $emp['employee_code']);
                                echo field('Department', $emp['department_name']);
                                echo field('Job Title', $emp['job_title']);
                                echo field('Branch', $emp['branch_name']);
                                echo field('Employment Status', $emp['employment_status']);
                                echo field('Employment Type', $emp['employment_type']);
                                echo field('Date Hired', formatDate($emp['hire_date']));
                                ?>
                            </div>
                        </div>
                        <div class="employee-subsection">
                            <div class="employee-subsection-title">Government IDs</div>
                            <div class="detail-grid">
                                <?php
                                echo field('SSS Number', $emp['sss_number'] ?? '');
                                echo field('PhilHealth Number', $emp['philhealth_number'] ?? '');
                                echo field('Pag-IBIG Number', $emp['pagibig_number'] ?? '');
                                echo field('TIN Number', $emp['tin_number'] ?? '');
                                ?>
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
                        <?php if (empty($education)): ?>
                            <div class="empty-state">
                                <i class="fas fa-graduation-cap d-block"></i>
                                <p>No education records available.</p>
                            </div>
                        <?php else: ?>
                            <div class="employee-table-wrap">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Level</th>
                                            <th>School</th>
                                            <th>Degree</th>
                                            <th>Year</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($education as $ed): ?>
                                            <tr>
                                                <td data-label="Level"><?php echo e($ed['education_level']); ?></td>
                                                <td data-label="School"><?php echo e($ed['school_name']); ?></td>
                                                <td data-label="Degree"><?php echo e($ed['degree_course'] ?: 'N/A'); ?></td>
                                                <td data-label="Year"><?php echo e($ed['year_graduated'] ?: 'N/A'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="content-card employee-section-card">
                    <div class="employee-section-header">
                        <div>
                            <div class="employee-section-kicker"><i class="fas fa-history"></i>Work</div>
                            <h5 class="mb-0">Work History</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($work)): ?>
                            <div class="empty-state">
                                <i class="fas fa-briefcase d-block"></i>
                                <p>No work history records available.</p>
                            </div>
                        <?php else: ?>
                            <div class="employee-table-wrap">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Period</th>
                                            <th>Position</th>
                                            <th>Company</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($work as $w): ?>
                                            <tr>
                                                <td data-label="Period"><?php echo formatDate($w['date_from'], 'Y') . ' - ' . ($w['date_to'] ? formatDate($w['date_to'], 'Y') : 'Present'); ?></td>
                                                <td data-label="Position"><?php echo e($w['job_title']); ?></td>
                                                <td data-label="Company"><?php echo e($w['company_name']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="content-card employee-section-card">
                    <div class="employee-section-header">
                        <div>
                            <div class="employee-section-kicker"><i class="fas fa-certificate"></i>Development</div>
                            <h5 class="mb-0">Skills & Training</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-lg-5">
                                <div class="employee-subsection h-100">
                                    <div class="employee-subsection-title">Skills</div>
                                    <?php if (empty($skills)): ?>
                                        <div class="empty-state">
                                            <i class="fas fa-star d-block"></i>
                                            <p>No skills recorded.</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="badge-cloud">
                                            <?php foreach($skills as $sk): ?>
                                                <span class="badge bg-info"><?php echo e($sk['skill_name']); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <div class="employee-subsection h-100">
                                    <div class="employee-subsection-title">Trainings & Seminars</div>
                                    <?php if (empty($trainings)): ?>
                                        <div class="empty-state">
                                            <i class="fas fa-chalkboard-teacher d-block"></i>
                                            <p>No training records available.</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="employee-table-wrap">
                                            <table class="table table-sm align-middle mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Training</th>
                                                        <th>Date</th>
                                                        <th>Type</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach($trainings as $tr): ?>
                                                        <tr>
                                                            <td data-label="Training"><?php echo e($tr['training_title']); ?></td>
                                                            <td data-label="Date"><?php echo formatDate($tr['date_from']); ?></td>
                                                            <td data-label="Type"><?php echo e($tr['training_type'] ?: 'General'); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
