<?php
require_once '../includes/session-check.php';
checkRole(['Employee']);
require_once '../includes/functions.php';
redirectWith(BASE_URL . '/employee/dashboard.php', 'info', 'Personal Data Sheet is no longer part of the Employee Portal.');

// ── Load existing employee data ───────────────────────────────────────────────
$emp_q = $conn->prepare("
    SELECT e.*,
           ed.height_m, ed.weight_kg, ed.blood_type, ed.citizenship,
           eg.sss_number, eg.philhealth_number, eg.pagibig_number, eg.tin_number,
           ec.telephone_number, ec.mobile_number AS contact_number, ec.personal_email AS email
    FROM employees e
    LEFT JOIN employee_details ed ON e.employee_id = ed.employee_id
    LEFT JOIN employee_government_ids eg ON e.employee_id = eg.employee_id
    LEFT JOIN employee_contacts ec ON e.employee_id = ec.employee_id
    WHERE e.employee_id = ?
");
$emp_q->bind_param("i", $employee_id);
$emp_q->execute();
$emp_raw = $emp_q->get_result()->fetch_assoc() ?? [];
$emp_q->close();

// Address flattening
$addr_q = $conn->prepare("SELECT * FROM employee_addresses WHERE employee_id=?");
$addr_q->bind_param("i", $employee_id);
$addr_q->execute();
$addr_res = $addr_q->get_result();
$addr_q->close();
$res_addr = []; $perm_addr = [];
while ($a = $addr_res->fetch_assoc()) {
    if ($a['address_type'] === 'Residential') $res_addr = $a;
    else $perm_addr = $a;
}
$emp = array_merge($emp_raw, [
    'res_house_no'     => $res_addr['house_no']    ?? '',
    'res_street'       => $res_addr['street']      ?? '',
    'res_subdivision'  => $res_addr['subdivision'] ?? '',
    'res_barangay'     => $res_addr['barangay']    ?? '',
    'res_city'         => $res_addr['city']        ?? '',
    'res_province'     => $res_addr['province']    ?? '',
    'res_zip_code'     => $res_addr['zip_code']    ?? '',
    'perm_house_no'    => $perm_addr['house_no']   ?? '',
    'perm_street'      => $perm_addr['street']     ?? '',
    'perm_subdivision' => $perm_addr['subdivision']?? '',
    'perm_barangay'    => $perm_addr['barangay']   ?? '',
    'perm_city'        => $perm_addr['city']       ?? '',
    'perm_province'    => $perm_addr['province']   ?? '',
    'perm_zip_code'    => $perm_addr['zip_code']   ?? '',
]);

// Emergency contact
$ec_q = $conn->prepare("SELECT * FROM employee_emergency_contacts WHERE employee_id=? LIMIT 1");
$ec_q->bind_param("i", $employee_id);
$ec_q->execute();
$emergencyContact = $ec_q->get_result()->fetch_assoc() ?? [];
$ec_q->close();
$emp['emergency_contact_name']   = $emergencyContact['contact_name']   ?? '';
$emp['emergency_relationship']   = $emergencyContact['relationship']   ?? '';
$emp['emergency_contact_number'] = $emergencyContact['contact_number'] ?? '';

// Family
$fam_q = $conn->prepare("SELECT * FROM employee_family WHERE employee_id=?");
$fam_q->bind_param("i", $employee_id); $fam_q->execute();
$fam_res = $fam_q->get_result(); $fam_q->close();
while ($f = $fam_res->fetch_assoc()) {
    $mt = strtolower($f['member_type']);
    $emp[$mt.'_surname']    = $f['surname']    ?? '';
    $emp[$mt.'_first_name'] = $f['first_name'] ?? '';
    if ($mt === 'spouse') {
        $emp['spouse_middle_name'] = $f['middle_name']     ?? '';
        $emp['spouse_name_ext']    = $f['name_extension']  ?? '';
        $emp['spouse_occupation']  = $f['occupation']      ?? '';
    } elseif ($mt === 'father') {
        $emp['father_middle_name'] = $f['middle_name']    ?? '';
        $emp['father_name_ext']    = $f['name_extension'] ?? '';
        $emp['father_occupation']  = $f['occupation']     ?? '';
    } elseif ($mt === 'mother') {
        $emp['mother_maiden_surname'] = $f['surname']     ?? '';
        $emp['mother_middle_name']    = $f['middle_name'] ?? '';
        $emp['mother_occupation']     = $f['occupation']  ?? '';
    }
}

// Sub-tables
function fetchRows($conn, $table, $eid) {
    $s = $conn->prepare("SELECT * FROM $table WHERE employee_id=? ORDER BY 1");
    $s->bind_param("i", $eid); $s->execute();
    $r = $s->get_result(); $s->close();
    return $r->fetch_all(MYSQLI_ASSOC);
}
$employeeChildren    = fetchRows($conn, 'employee_children',          $employee_id);
$employeeSiblings    = fetchRows($conn, 'employee_siblings',          $employee_id);
$employeeEducation   = fetchRows($conn, 'employee_education',         $employee_id);
$employeeWork        = fetchRows($conn, 'employee_work_experience',   $employee_id);
$employeeTrainings   = fetchRows($conn, 'employee_trainings',         $employee_id);
$employeeVoluntary   = fetchRows($conn, 'employee_voluntary_work',    $employee_id);
$employeeEligibility = fetchRows($conn, 'employee_eligibility',       $employee_id);
$employeeSkills      = fetchRows($conn, 'employee_skills',            $employee_id);
$employeeRecognitions= fetchRows($conn, 'employee_recognitions',      $employee_id);
$employeeMemberships = fetchRows($conn, 'employee_memberships',       $employee_id);
$employeeRealProps   = fetchRows($conn, 'employee_real_properties',   $employee_id);
$employeePersonalProps=fetchRows($conn, 'employee_personal_properties',$employee_id);
$employeeLiabilities = fetchRows($conn, 'employee_liabilities',       $employee_id);
$employeeReferences  = fetchRows($conn, 'employee_references',        $employee_id);

$disc_q = $conn->prepare("SELECT * FROM employee_disclosures WHERE employee_id=? LIMIT 1");
$disc_q->bind_param("i", $employee_id); $disc_q->execute();
$disc = $disc_q->get_result()->fetch_assoc() ?? [];
$disc_q->close();
$emp = array_merge($emp, $disc);

$branches = $conn->query("SELECT * FROM branches ORDER BY branch_name");
$isEdit   = true;  // always edit mode for employee wizard

// Pre-render HR notes block for JS injection (PHP cannot run inside JS template literals)
$hr_notes_block = '';
if ($current_sub && $current_sub['status'] === 'Changes Requested' && !empty($current_sub['hr_notes'])) {
    $hr_notes_block = '<div class="alert alert-danger mb-4"><strong><i class="fas fa-exclamation-circle me-2"></i>HR has requested changes:</strong><br>' . nl2br(e($current_sub['hr_notes'])) . '</div>';
}

require_once '../includes/header.php';
?>

<!-- Wizard Header & Progress -->
<div class="content-card mb-4 shadow-sm border-0">
  <div class="card-body">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <h5 class="mb-0 fw-bold"><i class="fas fa-file-signature me-2 text-primary"></i>Personal Data Sheet Wizard</h5>
        <div id="saveStatus" class="badge bg-light text-success border" style="display:none; font-weight: 500;">
          <i class="fas fa-cloud-upload-alt me-1"></i>Draft Saved
        </div>
    </div>
    
    <!-- Progress Bar -->
    <div class="pds-progress-container mb-3">
      <div class="pds-progress-wrapper">
        <div id="pdsProgressBar" class="pds-progress-bar" style="width: 8.33%;"></div>
      </div>
      <div class="pds-progress-percent" id="pdsProgressPercent">8%</div>
    </div>

    <!-- Portal Tabs -->
    <div class="portal-tabs">
      <div class="portal-tab active" id="portal-tab-1" onclick="showPortal(1)">
        <div class="portal-num">1</div>
        <div class="portal-label-wrapper">
          <span class="portal-label">Core Identity</span>
          <div class="portal-sub-steps" id="portal-sub-1">
            <!-- Dots injected/updated by JS -->
          </div>
        </div>
      </div>
      <div class="portal-tab" id="portal-tab-2" onclick="showPortal(2)">
        <div class="portal-num">2</div>
        <div class="portal-label-wrapper">
          <span class="portal-label">Background</span>
          <div class="portal-sub-steps" id="portal-sub-2">
            <!-- Dots injected/updated by JS -->
          </div>
        </div>
      </div>
      <div class="portal-tab" id="portal-tab-3" onclick="showPortal(3)">
        <div class="portal-num">3</div>
        <div class="portal-label-wrapper">
          <span class="portal-label">Qualifications</span>
          <div class="portal-sub-steps" id="portal-sub-3">
            <!-- Dots injected/updated by JS -->
          </div>
        </div>
      </div>
      <div class="portal-tab" id="portal-tab-4" onclick="showPortal(4)">
        <div class="portal-num">4</div>
        <div class="portal-label-wrapper">
          <span class="portal-label">Final</span>
          <div class="portal-sub-steps" id="portal-sub-4">
            <!-- Dots injected/updated by JS -->
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="content-card shadow-sm border-0">
  <div class="card-body">
    <form method="POST" action="" enctype="multipart/form-data" id="pdsWizardForm">
      <input type="hidden" name="action" id="pds_action" value="">
      <?php include '../includes/employee-form-steps.php'; ?>

      <!-- Sticky Wizard Footer -->
      <div class="wizard-footer mt-4">
        <button type="button" id="prevBtn" onclick="prevStep()" class="btn btn-outline-secondary px-4 shadow-sm">
          <i class="fas fa-arrow-left me-2"></i> Back
        </button>
        <div class="text-muted small d-none d-md-block" id="wizardProgressLabel">
          Portal 1 of 4 · Step 1 of 12
        </div>
        <div class="d-flex gap-2">
          <button type="button" id="nextBtn" onclick="nextStep()" class="btn btn-primary px-4 shadow-sm">
            Next Step <i class="fas fa-arrow-right ms-2"></i>
          </button>
          <button type="button" id="submitBtn" onclick="submitPDS()" class="btn btn-success px-4 shadow-sm" style="display:none;">
            <i class="fas fa-check-double me-2"></i> Submit for Review
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Mini Back-to-Top Button -->
<button type="button" id="backToTopBtn" class="btn btn-primary btn-sm shadow" aria-label="Go to top"
        style="position: fixed; right: 18px; bottom: 18px; z-index: 1050; border-radius: 999px; width: 36px; height: 36px; padding: 0; display: none;">
    <i class="fas fa-arrow-up"></i>
</button>

<!-- Override Step 12 with Employee-friendly Review & Submit panel -->
<script src="<?php echo BASE_URL; ?>/assets/js/employee-form.js?v=<?php echo time(); ?>"></script>
<script>
// ── Auto-save via AJAX ────────────────────────────────────────────────────────
function autoSaveDraft(callback) {
    const saveStatus = document.getElementById('saveStatus');
    const form = document.getElementById('pdsWizardForm');
    const fd = new FormData(form);
    fd.append('employee_id', '<?php echo $employee_id; ?>');
    
    fetch('<?php echo BASE_URL; ?>/employee/ajax/save-pds-section.php', {
        method: 'POST', body: fd
    }).then(r => r.json()).then(() => {
        if (saveStatus) {
            saveStatus.style.display = 'inline-block';
            setTimeout(() => { saveStatus.style.display = 'none'; }, 2000);
        }
        if (typeof callback === 'function') callback();
    }).catch(() => { if (typeof callback === 'function') callback(); });
}

// ── Final submit ──────────────────────────────────────────────────────────────
function submitPDS() {
    if (!confirm('Submit your PDS for HR Manager review? You cannot edit it until HR responds.')) return;
    
    const btn = document.getElementById('submitBtn');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Submitting...';
    }

    // Set action before saving/submitting
    const actionInput = document.getElementById('pds_action');
    if (actionInput) actionInput.value = 'submit_pds';

    autoSaveDraft(() => {
        const form = document.getElementById('pdsWizardForm');
        if (form) {
            form.submit();
        } else {
            alert('Error: Form not found. Please refresh and try again.');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check-double me-2"></i> Submit for Review';
            }
        }
    });
}

function updatePDSSummary() {
    const getValue = (name, fallback = '<span class="text-muted small">Blank</span>') => {
        const el = document.querySelector(`[name="${name}"]`);
        return el && el.value ? el.value : fallback;
    };
    
    // Core Identity
    const firstName = getValue('first_name');
    const middleName = getValue('middle_name', '');
    const lastName = getValue('last_name');
    const nameExt = getValue('name_extension', '');
    const name = `${firstName} ${middleName ? middleName + ' ' : ''}${lastName}${nameExt ? ' ' + nameExt : ''}`;
    
    document.getElementById('sum-name').innerHTML = name;
    document.getElementById('sum-dob').innerHTML = getValue('date_of_birth');
    document.getElementById('sum-gender').innerHTML = getValue('gender');
    document.getElementById('sum-civil').innerHTML = getValue('civil_status');
    document.getElementById('sum-mobile').innerHTML = getValue('contact_number');
    document.getElementById('sum-email').innerHTML = getValue('email');
    
    // Government IDs
    document.getElementById('sum-sss').innerHTML = getValue('sss_number');
    document.getElementById('sum-philhealth').innerHTML = getValue('philhealth_number');
    document.getElementById('sum-pagibig').innerHTML = getValue('pagibig_number');
    document.getElementById('sum-tin').innerHTML = getValue('tin_number');
    
    // Background (counts)
    const countRepeaterEntries = (containerId) => {
        const container = document.getElementById(containerId);
        return container ? container.querySelectorAll('.repeater-row').length : 0;
    };
    document.getElementById('sum-children').innerHTML = countRepeaterEntries('childrenContainer') + ' child(ren)';
    document.getElementById('sum-siblings').innerHTML = countRepeaterEntries('siblingsContainer') + ' sibling(s)';
    document.getElementById('sum-education').innerHTML = countRepeaterEntries('educationContainer') + ' education entry(ies)';
    document.getElementById('sum-work').innerHTML = countRepeaterEntries('workContainer') + ' job entry(ies)';
    
    // Qualifications (counts)
    document.getElementById('sum-eligibility').innerHTML = countRepeaterEntries('eligibilityContainer') + ' license(s)';
    document.getElementById('sum-skills').innerHTML = countRepeaterEntries('skillsContainer') + ' skill(s)';
    document.getElementById('sum-recognitions').innerHTML = countRepeaterEntries('recognitionsContainer') + ' recognition(s)';
    document.getElementById('sum-properties').innerHTML = (countRepeaterEntries('realPropContainer') + countRepeaterEntries('personalPropContainer')) + ' asset(s)';
    
    // Disclosures count
    let activeDisclosures = 0;
    document.querySelectorAll('#step10 input[type="checkbox"]').forEach(cb => {
        if (cb.checked) activeDisclosures++;
    });
    document.getElementById('sum-disclosures').innerHTML = activeDisclosures + ' active declaration(s)';
}

document.addEventListener('DOMContentLoaded', function() {
    // Replace step 12 content with a read-only review + submit screen
    const step12 = document.getElementById('step12');
    if (step12) {
        step12.innerHTML = `
        <div class="text-center py-3 mb-4">
            <i class="fas fa-clipboard-check fa-3x mb-3 text-success"></i>
            <h4 class="fw-bold text-success">Review &amp; Submit</h4>
            <p class="text-muted mb-0">Your PDS has been auto-saved. Please review and submit for HR Manager approval.</p>
        </div>

        <div class="alert alert-info mb-4 py-2 px-3 small border-0 shadow-sm" style="border-radius: 8px;">
            <i class="fas fa-info-circle me-2"></i>
            <strong>What happens after submission?</strong> Your HR Manager will review your Personal Data Sheet and will approve, request changes, or reject your submission. You will receive a notification with the outcome.
        </div>

        <div class="alert alert-warning mb-4 py-2 px-3 small border-0 shadow-sm text-dark" style="border-radius: 8px; background: rgba(189, 148, 20, 0.1);">
            <i class="fas fa-lock me-2 text-warning"></i>
            Once submitted, you <strong>cannot edit</strong> your PDS until HR responds.
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card h-100 border border-light shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                        <span class="fw-bold text-dark"><i class="fas fa-user-circle me-2 text-primary"></i>Core Identity</span>
                        <button type="button" onclick="showStep(1)" class="btn btn-sm btn-link text-decoration-none p-0"><i class="fas fa-edit me-1"></i>Edit</button>
                    </div>
                    <div class="card-body py-3">
                        <div class="row g-2 small">
                            <div class="col-5 text-muted">Full Name:</div><div class="col-7 fw-bold" id="sum-name">--</div>
                            <div class="col-5 text-muted">Date of Birth:</div><div class="col-7 fw-bold" id="sum-dob">--</div>
                            <div class="col-5 text-muted">Gender:</div><div class="col-7 fw-bold" id="sum-gender">--</div>
                            <div class="col-5 text-muted">Civil Status:</div><div class="col-7 fw-bold" id="sum-civil">--</div>
                            <div class="col-5 text-muted">Mobile No:</div><div class="col-7 fw-bold" id="sum-mobile">--</div>
                            <div class="col-5 text-muted">Email Address:</div><div class="col-7 fw-bold text-truncate" id="sum-email">--</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card h-100 border border-light shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                        <span class="fw-bold text-dark"><i class="fas fa-id-card me-2 text-primary"></i>Government IDs</span>
                        <button type="button" onclick="showStep(1)" class="btn btn-sm btn-link text-decoration-none p-0"><i class="fas fa-edit me-1"></i>Edit</button>
                    </div>
                    <div class="card-body py-3">
                        <div class="row g-2 small">
                            <div class="col-5 text-muted">SSS No:</div><div class="col-7 fw-bold" id="sum-sss">--</div>
                            <div class="col-5 text-muted">PhilHealth No:</div><div class="col-7 fw-bold" id="sum-philhealth">--</div>
                            <div class="col-5 text-muted">Pag-IBIG No:</div><div class="col-7 fw-bold" id="sum-pagibig">--</div>
                            <div class="col-5 text-muted">TIN No:</div><div class="col-7 fw-bold" id="sum-tin">--</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card h-100 border border-light shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                        <span class="fw-bold text-dark"><i class="fas fa-history me-2 text-primary"></i>Background</span>
                        <button type="button" onclick="showStep(2)" class="btn btn-sm btn-link text-decoration-none p-0"><i class="fas fa-edit me-1"></i>Edit</button>
                    </div>
                    <div class="card-body py-3">
                        <div class="row g-2 small">
                            <div class="col-5 text-muted">Children:</div><div class="col-7 fw-bold" id="sum-children">--</div>
                            <div class="col-5 text-muted">Siblings:</div><div class="col-7 fw-bold" id="sum-siblings">--</div>
                            <div class="col-5 text-muted">Education:</div><div class="col-7 fw-bold" id="sum-education">--</div>
                            <div class="col-5 text-muted">Work History:</div><div class="col-7 fw-bold" id="sum-work">--</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card h-100 border border-light shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                        <span class="fw-bold text-dark"><i class="fas fa-certificate me-2 text-primary"></i>Qualifications</span>
                        <button type="button" onclick="showStep(7)" class="btn btn-sm btn-link text-decoration-none p-0"><i class="fas fa-edit me-1"></i>Edit</button>
                    </div>
                    <div class="card-body py-3">
                        <div class="row g-2 small">
                            <div class="col-5 text-muted">Eligibility/PRC:</div><div class="col-7 fw-bold" id="sum-eligibility">--</div>
                            <div class="col-5 text-muted">Skills:</div><div class="col-7 fw-bold" id="sum-skills">--</div>
                            <div class="col-5 text-muted">Recognitions:</div><div class="col-7 fw-bold" id="sum-recognitions">--</div>
                            <div class="col-5 text-muted">Properties/Assets:</div><div class="col-7 fw-bold" id="sum-properties">--</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card border border-light shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                        <span class="fw-bold text-dark"><i class="fas fa-exclamation-triangle me-2 text-primary"></i>Disclosures & Declarations</span>
                        <button type="button" onclick="showStep(10)" class="btn btn-sm btn-link text-decoration-none p-0"><i class="fas fa-edit me-1"></i>Edit</button>
                    </div>
                    <div class="card-body py-3">
                        <div class="row g-2 small">
                            <div class="col-5 text-muted">Active Declarations:</div><div class="col-7 fw-bold" id="sum-disclosures">--</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        ${<?php echo json_encode($hr_notes_block); ?>}
    `;
    }

    // Show the mini "Back to Top" button after scrolling down.
    const backToTopBtn = document.getElementById('backToTopBtn');
    const toggleBackToTop = () => {
        if (!backToTopBtn) return;
        backToTopBtn.style.display = window.scrollY > 200 ? 'inline-flex' : 'none';
        backToTopBtn.style.alignItems = 'center';
        backToTopBtn.style.justifyContent = 'center';
    };

    if (backToTopBtn) {
        backToTopBtn.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    window.addEventListener('scroll', toggleBackToTop);
    toggleBackToTop();
});
</script>

<?php require_once '../includes/footer.php'; ?>
