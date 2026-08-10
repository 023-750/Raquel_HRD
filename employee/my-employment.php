<?php
$page_title = 'My Employment';
require_once '../includes/session-check.php';
checkRole(['Employee']);
require_once '../includes/functions.php';

$employee_id = (int) ($_SESSION['employee_id'] ?? 0);

$emp_stmt = $conn->prepare("
    SELECT e.*,
           d.department_name,
           b.branch_name,
           rc.rank_name,
           ed.height_m, ed.weight_kg, ed.blood_type, ed.citizenship,
           ec.mobile_number, ec.telephone_number, ec.personal_email,
           eg.sss_number, eg.philhealth_number, eg.pagibig_number, eg.tin_number
    FROM employees e
    LEFT JOIN departments d ON e.department_id = d.department_id
    LEFT JOIN branches b ON e.branch_id = b.branch_id
    LEFT JOIN rank_categories rc ON e.rank_category_id = rc.rank_category_id
    LEFT JOIN employee_details ed ON e.employee_id = ed.employee_id
    LEFT JOIN employee_contacts ec ON e.employee_id = ec.employee_id
    LEFT JOIN employee_government_ids eg ON e.employee_id = eg.employee_id
    WHERE e.employee_id = ?
    LIMIT 1
");
$emp_stmt->bind_param("i", $employee_id);
$emp_stmt->execute();
$emp = $emp_stmt->get_result()->fetch_assoc() ?? [];
$emp_stmt->close();

if (!$emp) {
    redirectWith(BASE_URL . '/employee/dashboard.php', 'danger', 'No employment record was found for your account.');
}

// Fetch Addresses
$addr_stmt = $conn->prepare("SELECT * FROM employee_addresses WHERE employee_id = ?");
$addr_stmt->bind_param("i", $employee_id);
$addr_stmt->execute();
$addr_res = $addr_stmt->get_result();
$addresses = [];
while ($row = $addr_res->fetch_assoc()) {
    $addresses[$row['address_type']] = $row;
}
$addr_stmt->close();

// Fetch Emergency Contacts
$emerg_stmt = $conn->prepare("SELECT * FROM employee_emergency_contacts WHERE employee_id = ? ORDER BY is_primary DESC, emergency_id ASC");
$emerg_stmt->bind_param("i", $employee_id);
$emerg_stmt->execute();
$emerg_res = $emerg_stmt->get_result();
$emergency_contacts = [];
while ($row = $emerg_res->fetch_assoc()) {
    $emergency_contacts[] = $row;
}
$emerg_stmt->close();

if (!function_exists('formatAddress')) {
    function formatAddress($addr) {
        if (!$addr) return '—';
        $parts = [];
        if (!empty($addr['house_no'])) $parts[] = $addr['house_no'];
        if (!empty($addr['street'])) $parts[] = $addr['street'];
        if (!empty($addr['subdivision'])) $parts[] = $addr['subdivision'];
        if (!empty($addr['barangay'])) $parts[] = 'Brgy. ' . $addr['barangay'];
        if (!empty($addr['city'])) $parts[] = $addr['city'];
        if (!empty($addr['province'])) $parts[] = $addr['province'];
        if (!empty($addr['zip_code'])) $parts[] = $addr['zip_code'];
        return implode(', ', $parts);
    }
}

if (!function_exists('maskGovId')) {
    function maskGovId($val) {
        $trimmed = trim((string)$val);
        if (empty($trimmed) || $trimmed === '—') return '—';
        return '••••••••••••';
    }
}

require_once '../includes/header.php';
?>

<div class="page-hero fadeup">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-0 gap-4">
        <div class="d-flex align-items-center gap-4 flex-wrap">
            <img src="<?php echo getEmployeeAvatar($emp['profile_picture'] ?? ''); ?>?v=<?php echo time(); ?>"
                onclick="viewFullImage('<?php echo getEmployeeAvatar($emp['profile_picture'] ?? ''); ?>', '<?php echo e(($emp['first_name'] ?? '') . ' ' . ($emp['last_name'] ?? '')); ?>')"
                class="cursor-pointer"
                loading="lazy"
                alt="Profile photo of <?php echo e(trim(($emp['first_name'] ?? '') . ' ' . ($emp['last_name'] ?? ''))); ?>"
                style="width:100px;height:100px;border-radius:50%;object-fit:cover;border:4px solid rgba(255,255,255,.3); box-shadow: 0 4px 15px rgba(0,0,0,0.2); transition: transform 0.2s; background-color: #ffffff;">
            <div>
                <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.55);">
                    Employee Portal · Welcome Back</div>
                <h2 class="text-white fw-bold mb-1 mt-1">
                    Hello, <?php echo e(trim(($emp['first_name'] ?? '') . ' ' . ($emp['last_name'] ?? ''))); ?>!</h2>
                <p class="mb-2 text-white-50 small">
                    <i class="fas fa-briefcase me-1"></i><?php echo e($emp['job_title'] ?? '—'); ?> &bull;
                    <?php echo e($emp['department_name'] ?? '—'); ?>
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge bg-white text-dark py-1 px-2" style="font-size: 0.7rem;"><i
                            class="fas fa-building me-1 text-primary"></i><?php echo e($emp['branch_name'] ?? 'N/A'); ?></span>
                    <span class="badge bg-white text-dark py-1 px-2 d-none d-md-inline" style="font-size: 0.7rem;"><i
                            class="fas fa-calendar-alt me-1 text-primary"></i>Hired:
                        <?php echo formatDate($emp['hire_date'] ?? ''); ?></span>
                    <span class="badge bg-white text-dark py-1 px-2" style="font-size: 0.7rem;"><i
                            class="fas fa-user-check me-1 text-primary"></i><?php echo e($emp['employment_status'] ?? '—'); ?></span>
                </div>
            </div>
        </div>
        <div class="d-none d-md-block text-end">
            <a href="<?php echo BASE_URL; ?>/employee/dashboard.php" class="btn btn-outline-light btn-sm rounded-pill px-3 mb-2">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
            <div class="text-white-50 x-small"><i class="fas fa-info-circle me-1"></i>Employment info is read-only. Contact HR for updates.</div>
        </div>
    </div>
</div>

<!-- Mobile-only section -->
<div class="d-md-none d-flex justify-content-between align-items-center mt-3 mb-4 flex-wrap gap-3 fadeup" style="animation-delay: 0.1s;">
    <a href="<?php echo BASE_URL; ?>/employee/dashboard.php" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
        <i class="fas fa-arrow-left me-2"></i>Back to My Dashboard
    </a>
    <div class="alert alert-light border-0 shadow-sm py-2 px-3 mb-0" style="border-radius: 10px; font-size: 0.85rem; background: #fff;">
        <i class="fas fa-info-circle me-2 text-primary"></i>
        <span class="text-muted fw-500">Read-only. Contact HR for updates.</span>
    </div>
</div>



<div class="pds-info-grid">
    <div class="pds-card fadeup-1">
        <div class="pds-card-title"><i class="fas fa-briefcase"></i>Employment Details</div>
        <div class="pds-data-row"><span class="label company-id-text">Company ID</span><span
                class="value company-id-value"><?php echo e(getEmployeeDisplayId($emp)); ?></span></div>
        <div class="pds-data-row"><span class="label">Rank</span><span
                class="value"><span class="rank-badge"><?php echo e($emp['rank_name'] ?? '—'); ?></span></span></div>
        <div class="pds-data-row"><span class="label">Full Name</span><span
                class="value"><?php echo e(trim(($emp['first_name'] ?? '') . ' ' . ($emp['last_name'] ?? ''))); ?></span>
        </div>
        <div class="pds-data-row"><span class="label">Position</span><span
                class="value"><?php echo e($emp['job_title'] ?? '—'); ?></span></div>
        <div class="pds-data-row"><span class="label">Department</span><span
                class="value"><?php echo e($emp['department_name'] ?? '—'); ?></span></div>
        <div class="pds-data-row"><span class="label">Branch</span><span
                class="value"><?php echo e($emp['branch_name'] ?? '—'); ?></span></div>
        <div class="pds-data-row"><span class="label">Hire Date</span><span
                class="value"><?php echo formatDate($emp['hire_date'] ?? ''); ?></span></div>
        <div class="pds-data-row"><span class="label">Employment Status</span><span
                class="value"><?php echo e($emp['employment_status'] ?? '—'); ?></span></div>
        <div class="pds-data-row"><span class="label">Employment Type</span><span
                class="value"><?php echo e($emp['employment_type'] ?? '—'); ?></span></div>
    </div>

    <div class="pds-card fadeup-2">
        <div class="pds-card-title"><i class="fas fa-phone"></i>Contact Information</div>
        <div class="pds-data-row"><span class="label">Mobile Number</span><span
                class="value"><?php echo e($emp['mobile_number'] ?? '—'); ?></span></div>
        <div class="pds-data-row"><span class="label">Telephone</span><span
                class="value"><?php echo e($emp['telephone_number'] ?? '—'); ?></span></div>
        <div class="pds-data-row"><span class="label">Personal Email</span><span
                class="value"><?php echo e($emp['personal_email'] ?? '—'); ?></span></div>
        <div class="pds-data-row"><span class="label">Citizenship</span><span
                class="value"><?php echo e($emp['citizenship'] ?? '—'); ?></span></div>
        <div class="pds-data-row"><span class="label">Civil Status</span><span
                class="value"><?php echo e($emp['civil_status'] ?? '—'); ?></span></div>
        <div class="pds-data-row"><span class="label">Date of Birth</span><span
                class="value"><?php 
                    $dob_text = '';
                    if (!empty($emp['date_of_birth'])) {
                        $dob_text = formatDate($emp['date_of_birth']);
                        $dob = new DateTime($emp['date_of_birth']);
                        $today = new DateTime();
                        $age = $today->diff($dob)->y;
                        $dob_text .= " ($age years old)";
                    }
                    echo $dob_text ?: '—';
                ?></span></div>
    </div>

    <div class="pds-card fadeup-3">
        <div class="pds-card-title d-flex align-items-center justify-content-between">
            <span><i class="fas fa-id-badge me-2"></i>Government IDs</span>
            <button type="button" class="btn btn-sm btn-light border py-1 px-2 rounded-pill shadow-none" id="toggleGovIdsBtn" onclick="toggleGovIds()" style="font-size: 0.75rem; font-weight: 600; color: #475569; background: #f8fafc; transition: all 0.2s ease;" title="Toggle Confidential IDs">
                <i class="fas fa-eye me-1" id="govIdsEyeIcon" style="color: #64748b;"></i><span id="govIdsBtnText">Show IDs</span>
            </button>
        </div>
        
        <?php 
        $gov_ids = [
            'SSS Number' => $emp['sss_number'] ?? '',
            'PhilHealth Number' => $emp['philhealth_number'] ?? '',
            'Pag-IBIG Number' => $emp['pagibig_number'] ?? '',
            'TIN Number' => $emp['tin_number'] ?? '',
        ];
        foreach ($gov_ids as $label => $raw_val):
            $has_val = !empty(trim((string)$raw_val));
            $masked_val = maskGovId($raw_val);
            $raw_val_clean = $has_val ? e(trim($raw_val)) : '—';
        ?>
            <div class="pds-data-row">
                <span class="label"><?php echo $label; ?></span>
                <span class="value d-flex align-items-center gap-2">
                    <span class="gov-id-val" data-raw="<?php echo $raw_val_clean; ?>" data-masked="<?php echo $masked_val; ?>"><?php echo $masked_val; ?></span>
                    <?php if ($has_val): ?>
                        <i class="fas fa-eye text-muted cursor-pointer single-id-toggle ms-auto" onclick="toggleSingleId(this)" title="Toggle <?php echo $label; ?>" style="font-size:0.82rem; opacity: 0.55; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.55'"></i>
                    <?php endif; ?>
                </span>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="pds-card fadeup-4">
        <div class="pds-card-title"><i class="fas fa-user"></i>Profile Summary</div>
        <div class="pds-data-row"><span class="label">Gender</span><span
                class="value"><?php echo e($emp['gender'] ?? '—'); ?></span></div>
        <div class="pds-data-row"><span class="label">Place of Birth</span><span
                class="value"><?php echo e($emp['place_of_birth'] ?? '—'); ?></span></div>
        <div class="pds-data-row"><span class="label">Height</span><span
                class="value"><?php echo !empty($emp['height_m']) ? e($emp['height_m']) . ' m' : '—'; ?></span></div>
        <div class="pds-data-row"><span class="label">Weight</span><span
                class="value"><?php echo !empty($emp['weight_kg']) ? e($emp['weight_kg']) . ' kg' : '—'; ?></span></div>
        <div class="pds-data-row"><span class="label">Blood Type</span><span
                class="value"><?php echo e($emp['blood_type'] ?? '—'); ?></span></div>
        <div class="pds-data-row"><span class="label">Account Status</span><span
                class="value"><?php echo !empty($emp['is_active']) ? 'Active' : 'Inactive'; ?></span></div>
    </div>

    <div class="pds-card fadeup-5">
        <div class="pds-card-title"><i class="fas fa-map-marker-alt"></i>Addresses</div>
        <div class="pds-data-row flex-column align-items-start mb-3">
            <span class="label mb-1" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Residential Address</span>
            <span class="value text-start fw-semibold" style="text-align: left; color: #1e293b; font-size: 0.88rem; line-height: 1.4;">
                <?php echo e(formatAddress($addresses['Residential'] ?? null)); ?>
            </span>
        </div>
        <hr style="border-top: 1px solid #eee; margin: 12px 0;">
        <div class="pds-data-row flex-column align-items-start mb-0">
            <span class="label mb-1" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Permanent Address</span>
            <span class="value text-start fw-semibold" style="text-align: left; color: #1e293b; font-size: 0.88rem; line-height: 1.4;">
                <?php echo e(formatAddress($addresses['Permanent'] ?? null)); ?>
            </span>
        </div>
    </div>

    <div class="pds-card fadeup-6">
        <div class="pds-card-title"><i class="fas fa-exclamation-circle"></i>Emergency Contacts</div>
        <?php if (empty($emergency_contacts)): ?>
            <div class="text-muted small text-center py-3">No emergency contacts listed.</div>
        <?php else: ?>
            <?php foreach ($emergency_contacts as $idx => $contact): ?>
                <?php if ($idx > 0): ?>
                    <hr class="my-2" style="border-top: 1px dashed #eee; margin-top: 10px; margin-bottom: 10px;">
                <?php endif; ?>
                <div class="pds-data-row">
                    <span class="label">Contact Person</span>
                    <span class="value">
                        <?php echo e($contact['contact_name']); ?>
                        <?php if ($contact['is_primary']): ?>
                            <span class="badge bg-success-light text-success ms-1" style="font-size: 0.65rem; background-color: #e6fcf5; color: #0ca678 !important; padding: 2px 5px; border-radius: 4px; font-weight: bold; border: 1px solid rgba(12, 166, 120, 0.15);">Primary</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="pds-data-row">
                    <span class="label">Relationship</span>
                    <span class="value"><?php echo e($contact['relationship'] ?? '—'); ?></span>
                </div>
                <div class="pds-data-row">
                    <span class="label">Contact Number</span>
                    <span class="value"><?php echo e($contact['contact_number'] ?? '—'); ?></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div><!-- /.pds-card Emergency Contacts -->

    <!-- Career Movement History Card -->
    <?php
    $cm_history = [];
    $cm_check = $conn->query("SHOW TABLES LIKE 'career_movements'");
    if ($cm_check && $cm_check->num_rows > 0) {
        $cm_stmt = $conn->prepare("
            SELECT cm.*,
                pb.branch_name AS from_branch_name,
                nb.branch_name AS to_branch_name
            FROM career_movements cm
            LEFT JOIN branches pb ON cm.previous_branch_id = pb.branch_id
            LEFT JOIN branches nb ON cm.new_branch_id       = nb.branch_id
            WHERE cm.employee_id = ? AND cm.approval_status = 'Approved'
            ORDER BY cm.effective_date DESC, cm.created_at DESC
        ");
        $cm_stmt->bind_param("i", $employee_id);
        $cm_stmt->execute();
        $cm_history = $cm_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $cm_stmt->close();
    }
    ?>
    <div class="pds-card fadeup-7" style="grid-column: 1 / -1;">
        <div class="pds-card-title"><i class="fas fa-route"></i>Career Movement History</div>
        <?php if (empty($cm_history)): ?>
            <div class="text-muted small text-center py-3"><i class="fas fa-route d-block mb-1" style="font-size:1.5rem;opacity:.3;"></i>No career movements recorded yet.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm modern-table align-middle mb-0" style="font-size:.85rem;">
                    <thead>
                        <tr>
                            <th>Effective Date</th>
                            <th>Type</th>
                            <th>Previous Position</th>
                            <th>New Position</th>
                            <th>Branch Change</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cm_history as $cm):
                            $typeBadge = match($cm['movement_type']) {
                                'Promotion'   => 'bg-success',
                                'Transfer'    => 'bg-info text-dark',
                                'Demotion'    => 'bg-danger',
                                'Role Change' => 'bg-primary',
                                default       => 'bg-secondary',
                            };
                        ?>
                        <tr>
                            <td class="fw-semibold"><?php echo formatDate($cm['effective_date']); ?></td>
                            <td><span class="badge <?php echo $typeBadge; ?>"><?php echo e($cm['movement_type']); ?></span></td>
                            <td class="text-muted"><?php echo e($cm['previous_position'] ?: '—'); ?></td>
                            <td class="fw-bold text-success"><?php echo e($cm['new_position']); ?></td>
                            <td>
                                <?php if (!empty($cm['new_branch_id'])): ?>
                                    <?php echo e($cm['from_branch_name'] ?: '—'); ?> &rarr; <strong><?php echo e($cm['to_branch_name'] ?: '—'); ?></strong>
                                <?php else: ?>
                                    <span class="text-muted">Same Branch</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($cm['reason'] ?: '—'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
let govIdsVisible = false;

function toggleGovIds() {
    govIdsVisible = !govIdsVisible;
    const btnText = document.getElementById('govIdsBtnText');
    const eyeIcon = document.getElementById('govIdsEyeIcon');
    const elements = document.querySelectorAll('.gov-id-val');
    
    if (govIdsVisible) {
        if (btnText) btnText.textContent = 'Hide IDs';
        if (eyeIcon) eyeIcon.className = 'fas fa-eye-slash me-1';
        elements.forEach(el => {
            const raw = el.getAttribute('data-raw');
            if (raw && raw !== '—') {
                el.textContent = raw;
                el.classList.add('fw-bold');
            }
        });
        document.querySelectorAll('.single-id-toggle').forEach(icon => {
            icon.className = 'fas fa-eye-slash text-primary cursor-pointer single-id-toggle ms-auto';
        });
    } else {
        if (btnText) btnText.textContent = 'Show IDs';
        if (eyeIcon) eyeIcon.className = 'fas fa-eye me-1';
        elements.forEach(el => {
            const masked = el.getAttribute('data-masked');
            if (masked) {
                el.textContent = masked;
                el.classList.remove('fw-bold');
            }
        });
        document.querySelectorAll('.single-id-toggle').forEach(icon => {
            icon.className = 'fas fa-eye text-muted cursor-pointer single-id-toggle ms-auto';
        });
    }
}

function toggleSingleId(iconEl) {
    const parent = iconEl.closest('.value');
    const valEl = parent ? parent.querySelector('.gov-id-val') : null;
    if (!valEl) return;
    
    const raw = valEl.getAttribute('data-raw');
    const masked = valEl.getAttribute('data-masked');
    
    if (valEl.textContent === masked) {
        valEl.textContent = raw;
        valEl.classList.add('fw-bold');
        iconEl.className = 'fas fa-eye-slash text-primary cursor-pointer single-id-toggle ms-auto';
    } else {
        valEl.textContent = masked;
        valEl.classList.remove('fw-bold');
        iconEl.className = 'fas fa-eye text-muted cursor-pointer single-id-toggle ms-auto';
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>
