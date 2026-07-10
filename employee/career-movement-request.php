<?php
/**
 * Employee Portal - Career Movement Request
 * For Area Supervisors/Immediate Heads to submit career movement requests for their subordinates
 */
$page_title = 'Career Movement Request';
require_once '../includes/session-check.php';
checkRole(['Employee']);
require_once '../includes/functions.php';

$supervisor_employee_id = (int) ($_SESSION['employee_id'] ?? 0);
$user_id = (int) ($_SESSION['user_id'] ?? 0);

// Check if this employee has subordinates (is a supervisor)
$is_supervisor = hasEmployeeSubordinates($conn, $supervisor_employee_id);
$subordinates = $is_supervisor ? getEmployeeSubordinates($conn, $supervisor_employee_id) : [];

// Fetch branches for dropdown
$branches = $conn->query("SELECT branch_id, branch_name FROM branches WHERE is_active = 1 ORDER BY branch_name");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_supervisor) {
    $employee_id = (int) ($_POST['employee_id'] ?? 0);
    $movement_type = trim($_POST['movement_type'] ?? '');
    $new_position = trim($_POST['new_position'] ?? '');
    $new_branch_id = !empty($_POST['new_branch_id']) ? (int) $_POST['new_branch_id'] : null;
    $effective_date = trim($_POST['effective_date'] ?? '');
    $reason = trim($_POST['reason'] ?? '');

    // Validation
    $errors = [];
    if ($employee_id <= 0) {
        $errors[] = 'Please select an employee.';
    }
    if (empty($movement_type)) {
        $errors[] = 'Please select a movement type.';
    }
    if (empty($new_position)) {
        $errors[] = 'Please enter the new position.';
    }
    if (empty($effective_date)) {
        $errors[] = 'Please select an effective date.';
    }

    // Verify the selected employee is actually a subordinate
    $is_valid_subordinate = false;
    foreach ($subordinates as $sub) {
        if ($sub['employee_id'] == $employee_id) {
            $is_valid_subordinate = true;
            break;
        }
    }
    if (!$is_valid_subordinate) {
        $errors[] = 'Invalid employee selection.';
    }

    if (empty($errors)) {
        // Ensure career_movements table has new columns
        ensureCareerProgressionMovements($conn);

        // Get current employee details
        $emp_stmt = $conn->prepare("SELECT job_title, branch_id FROM employees WHERE employee_id = ? LIMIT 1");
        $emp_stmt->bind_param("i", $employee_id);
        $emp_stmt->execute();
        $emp_data = $emp_stmt->get_result()->fetch_assoc();
        $emp_stmt->close();

        $previous_position = $emp_data['job_title'] ?? '';
        $previous_branch_id = $emp_data['branch_id'] ?? null;

        // Get supervisor info for initiated_by fields
        $supervisor_name = $_SESSION['full_name'] ?? 'Unknown';

        // Fetch supervisor's job title
        $sup_stmt = $conn->prepare("SELECT job_title FROM employees WHERE employee_id = ? LIMIT 1");
        $sup_stmt->bind_param("i", $supervisor_employee_id);
        $sup_stmt->execute();
        $sup_data = $sup_stmt->get_result()->fetch_assoc();
        $sup_stmt->close();
        
        $supervisor_title = $sup_data['job_title'] ?? 'Immediate Head';

        // Insert the career movement request
        $insert = $conn->prepare("
            INSERT INTO career_movements 
            (employee_id, movement_type, previous_position, new_position, previous_branch_id, new_branch_id, 
             effective_date, reason, logged_by, approval_status, initiated_by_name, initiated_by_role, initiated_via, request_source, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?, ?, 'Employee Portal', 'Employee Portal', NOW())
        ");
        $insert->bind_param(
            "isssiississ",
            $employee_id,
            $movement_type,
            $previous_position,
            $new_position,
            $previous_branch_id,
            $new_branch_id,
            $effective_date,
            $reason,
            $user_id,
            $supervisor_name,
            $supervisor_title
        );

        if ($insert->execute()) {
            $movement_id = $insert->insert_id;
            $insert->close();

            // Notify HR Supervisor
            $hr_supervisors = $conn->query("SELECT user_id FROM users WHERE role = 'HR Supervisor' AND is_active = 1");
            while ($hr_sup = $hr_supervisors->fetch_assoc()) {
                createNotification(
                    $conn,
                    $hr_sup['user_id'],
                    'New Career Movement Request',
                    $supervisor_name . ' has submitted a ' . $movement_type . ' request for ' . getEmployeeNameById($conn, $employee_id),
                    BASE_URL . '/supervisor/career-movements.php'
                );
            }

            // Log audit
            logAudit($conn, $user_id, 'CREATE', 'CareerMovement', $movement_id, 'Career movement request submitted via Employee Portal by ' . $supervisor_name);

            // Store confirmation details for post-submit display (Req 11.4)
            $emp_name = getEmployeeNameById($conn, $employee_id);
            $_SESSION['career_confirmation'] = [
                'movement_id'    => $movement_id,
                'movement_type'  => $movement_type,
                'new_position'   => $new_position,
                'effective_date' => $effective_date,
                'employee_name'  => $emp_name,
                'submitted_at'   => date('Y-m-d H:i:s'),
            ];
            header("Location: " . BASE_URL . "/employee/career-movement-request.php");
            exit();
        } else {
            $insert->close();
            $errors[] = 'Failed to submit request. Please try again.';
        }
    }

    if (!empty($errors)) {
        $_SESSION['flash_message'] = implode(' ', $errors);
        $_SESSION['flash_type'] = 'danger';
    }
}

// Fetch my submitted requests
$my_requests = [];
if ($is_supervisor) {
    $req_stmt = $conn->prepare("
        SELECT cm.*, e.first_name, e.last_name, e.job_title, b.branch_name
        FROM career_movements cm
        JOIN employees e ON cm.employee_id = e.employee_id
        LEFT JOIN branches b ON cm.new_branch_id = b.branch_id
        WHERE cm.logged_by = ?
        ORDER BY cm.created_at DESC
        LIMIT 10
    ");
    $req_stmt->bind_param("i", $user_id);
    $req_stmt->execute();
    $my_requests = $req_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $req_stmt->close();
}

// Check for a just-submitted confirmation to show (stored in session)
$confirmation_data = null;
if (!empty($_SESSION['career_confirmation'])) {
    $confirmation_data = $_SESSION['career_confirmation'];
    unset($_SESSION['career_confirmation']);
}

require_once '../includes/header.php';
?>

<div class="page-hero fadeup">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-0 gap-4">
        <div>
            <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.55);">
                Employee Portal</div>
            <h2 class="text-white fw-bold mb-1 mt-1">Career Movement Request</h2>
            <p class="mb-0 text-white-50 small">
                <i class="fas fa-route me-1"></i>Submit transfer, promotion, or role change requests for your team
                members
            </p>
        </div>
    </div>
</div>

<!-- Breadcrumb navigation -->
<nav aria-label="Breadcrumb" class="breadcrumb-nav" style="margin-top: 1rem;">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/employee/dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Career Movement Request</li>
    </ol>
</nav>

<?php if (!$is_supervisor): ?>
    <!-- Not a supervisor - informational message -->
    <div class="content-card fadeup-1">
        <div class="card-body text-center py-5">
            <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">Supervisor Access Only</h5>
            <p class="text-muted mb-0">
                This feature is available for Area Supervisors and Immediate Heads only.<br>
                If you believe you should have access, please contact HRD.
            </p>
        </div>
    </div>
<?php else: ?>

    <!-- ============================================================
         TASK 16.3 — Confirmation Panel (shown after successful submit)
         Req 11.4: confirmation screen with request number and expected timeline
    ============================================================ -->
    <?php if ($confirmation_data): ?>
    <div class="content-card fadeup-1 mb-4" id="submission-confirmation" role="region" aria-label="Submission Confirmation" style="border-left: 4px solid var(--color-success);">
        <div class="card-header" style="background: rgba(15,107,46,0.07);">
            <h5 class="mb-0" style="color: var(--color-success);">
                <i class="fas fa-check-circle me-2" aria-hidden="true"></i>Request Submitted Successfully
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-start">
                <div class="col-md-8">
                    <dl class="row mb-0" style="font-size: 1rem;">
                        <dt class="col-sm-5 text-muted">Request Reference #</dt>
                        <dd class="col-sm-7 fw-bold"><?php echo str_pad($confirmation_data['movement_id'], 6, '0', STR_PAD_LEFT); ?></dd>

                        <dt class="col-sm-5 text-muted">Employee</dt>
                        <dd class="col-sm-7"><?php echo e($confirmation_data['employee_name']); ?></dd>

                        <dt class="col-sm-5 text-muted">Movement Type</dt>
                        <dd class="col-sm-7"><?php echo e($confirmation_data['movement_type']); ?></dd>

                        <dt class="col-sm-5 text-muted">New Position</dt>
                        <dd class="col-sm-7"><?php echo e($confirmation_data['new_position']); ?></dd>

                        <dt class="col-sm-5 text-muted">Effective Date</dt>
                        <dd class="col-sm-7"><?php echo formatDate($confirmation_data['effective_date']); ?></dd>

                        <dt class="col-sm-5 text-muted">Submitted At</dt>
                        <dd class="col-sm-7"><?php echo date('M d, Y h:i A', strtotime($confirmation_data['submitted_at'])); ?></dd>

                        <dt class="col-sm-5 text-muted">Current Status</dt>
                        <dd class="col-sm-7">
                            <span class="badge bg-warning text-dark">
                                <i class="fas fa-clock me-1" aria-hidden="true"></i>Pending HRD Review
                            </span>
                        </dd>
                    </dl>
                </div>
                <div class="col-md-4">
                    <div class="p-3 rounded" style="background: var(--color-bg-secondary); border: 1px solid var(--color-border-light);">
                        <h6 class="fw-bold mb-2"><i class="fas fa-calendar-alt me-1" aria-hidden="true"></i>Expected Timeline</h6>
                        <ul class="list-unstyled mb-0 small" style="font-size: 0.95rem; line-height: 1.8;">
                            <li><i class="fas fa-circle text-success me-2" style="font-size:.5rem;vertical-align:middle;" aria-hidden="true"></i>HRD Review: 1–3 business days</li>
                            <li><i class="fas fa-circle text-warning me-2" style="font-size:.5rem;vertical-align:middle;" aria-hidden="true"></i>Management Approval: 3–5 business days</li>
                            <li><i class="fas fa-circle text-info me-2" style="font-size:.5rem;vertical-align:middle;" aria-hidden="true"></i>Final Decision: Up to 7 business days</li>
                        </ul>
                        <p class="mt-2 mb-0 small text-muted">You will be notified when the status changes.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Supervisor View - Request Form -->
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="content-card fadeup-1">
                <div class="card-header">
                    <h5><i class="fas fa-plus-circle me-2"></i>New Career Movement Request</h5>
                </div>
                <div class="card-body">

                    <!-- ============================================================
                         TASK 16.2 — Progress Indicator (Req 11.1, 11.3)
                         Shows step-by-step guidance and eligibility info
                    ============================================================ -->
                    <div class="progress-indicator mb-4" role="list" aria-label="Form steps">
                        <div class="progress-step completed" role="listitem" aria-label="Step 1: Select Employee - completed">
                            <div class="progress-step-number"><i class="fas fa-check" aria-hidden="true"></i></div>
                            <div class="progress-step-label">Select<br>Employee</div>
                        </div>
                        <div class="progress-line" id="prog-line-1"></div>
                        <div class="progress-step active" role="listitem" id="prog-step-2" aria-label="Step 2: Request Details - current step">
                            <div class="progress-step-number">2</div>
                            <div class="progress-step-label">Request<br>Details</div>
                        </div>
                        <div class="progress-line" id="prog-line-2"></div>
                        <div class="progress-step" role="listitem" id="prog-step-3" aria-label="Step 3: Review &amp; Submit - upcoming">
                            <div class="progress-step-number">3</div>
                            <div class="progress-step-label">Review &amp;<br>Submit</div>
                        </div>
                    </div>

                    <!-- ============================================================
                         TASK 16.2 — Eligibility Requirements Info (Req 11.3)
                         Shown before submission, context-aware
                    ============================================================ -->
                    <div id="eligibility-info" class="alert alert-info d-flex align-items-start gap-2 mb-4" role="note" style="font-size: 0.97rem; border-left: 4px solid var(--color-info);">
                        <i class="fas fa-info-circle mt-1 flex-shrink-0" aria-hidden="true" style="color: var(--color-info);"></i>
                        <div>
                            <strong>Before You Submit</strong>
                            <ul class="mb-0 mt-1 ps-3" id="eligibility-list">
                                <li>The employee must have completed their probationary period.</li>
                                <li>A minimum of 6 months tenure in the current position is typically required for <strong>Promotion</strong>.</li>
                                <li>Branch transfers require coordination with the receiving branch head.</li>
                                <li>Attach supporting documentation or justification in the Reason field.</li>
                                <li>All requests are subject to HRD and management approval.</li>
                            </ul>
                        </div>
                    </div>

                    <form method="POST" action="" id="career-request-form" data-validate novalidate>
                        <div class="row g-3">
                            <!-- Employee Selection -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="employee_id_select">Select Employee <span
                                        class="text-danger" aria-label="required">*</span></label>
                                <select name="employee_id" id="employee_id_select" class="form-select" required
                                    onchange="onEmployeeSelected(this)">
                                    <option value="">Choose employee...</option>
                                    <?php foreach ($subordinates as $sub): ?>
                                        <option value="<?php echo $sub['employee_id']; ?>">
                                            <?php echo e($sub['last_name'] . ', ' . $sub['first_name'] . ' - ' . $sub['job_title']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Select the team member for this career movement</div>
                            </div>

                            <!-- Movement Type -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="movement_type_select">Movement Type <span class="text-danger" aria-label="required">*</span></label>
                                <select name="movement_type" id="movement_type_select" class="form-select" required>
                                    <option value="">-- Select type --</option>
                                    <option value="Transfer"  <?php if (!empty($_POST['movement_type']) && $_POST['movement_type']==='Transfer')  echo 'selected'; ?>>Transfer</option>
                                    <option value="Promotion" <?php if (!empty($_POST['movement_type']) && $_POST['movement_type']==='Promotion') echo 'selected'; ?>>Promotion</option>
                                    <option value="Demotion"  <?php if (!empty($_POST['movement_type']) && $_POST['movement_type']==='Demotion')  echo 'selected'; ?>>Demotion</option>
                                    <option value="Role Change" <?php if (!empty($_POST['movement_type']) && $_POST['movement_type']==='Role Change') echo 'selected'; ?>>Role Change</option>
                                </select>
                                <div class="form-text">Select <strong>Transfer</strong> for branch re-assignment.</div>
                            </div>

                            <!-- New Position -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="new_position">New Position <span
                                        class="text-danger" aria-label="required">*</span></label>
                                <input type="text" name="new_position" id="new_position" class="form-control" placeholder="e.g. Senior Teller"
                                    required>
                            </div>

                            <!-- New Branch (Optional) -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="new_branch_id">New Branch</label>
                                <select name="new_branch_id" id="new_branch_id" class="form-select">
                                    <option value="">Same branch (no transfer)</option>
                                    <?php $branches->data_seek(0);
                                    while ($branch = $branches->fetch_assoc()): ?>
                                        <option value="<?php echo $branch['branch_id']; ?>">
                                            <?php echo e($branch['branch_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <div class="form-text">Leave blank if not a branch transfer</div>
                            </div>

                            <!-- Effective Date -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="effective_date">Effective Date <span
                                        class="text-danger" aria-label="required">*</span></label>
                                <input type="date" name="effective_date" id="effective_date" class="form-control" required
                                    min="<?php echo date('Y-m-d'); ?>">
                            </div>

                            <!-- Reason -->
                            <div class="col-12">
                                <label class="form-label fw-semibold" for="reason">Reason / Justification</label>
                                <textarea name="reason" id="reason" class="form-control" rows="3"
                                    placeholder="Provide reason for this career movement..."></textarea>
                            </div>

                            <!-- Submit Button -->
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary" id="submit-btn">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Request to HRD
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- My Team Summary -->
            <div class="content-card fadeup-1">
                <div class="card-header">
                    <h5><i class="fas fa-users me-2"></i>My Team</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">You can submit career movement requests for these team members:</p>
                    <div class="list-group list-group-flush">
                        <?php foreach ($subordinates as $sub): ?>
                            <div class="list-group-item px-0 py-2">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold"><?php echo e($sub['first_name'] . ' ' . $sub['last_name']); ?>
                                        </div>
                                        <div class="small text-muted"><?php echo e($sub['job_title']); ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================
         TASK 16.3 — My Submitted Requests with Visual Status Badges
         Req 11.5: status tracking with visual indicators for approval stages
    ============================================================ -->
    <?php if (!empty($my_requests)): ?>
        <div class="content-card fadeup-2 mt-4">
            <div class="card-header">
                <h5><i class="fas fa-history me-2"></i>My Submitted Requests</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Ref #</th>
                                <th>Employee</th>
                                <th>Type</th>
                                <th>New Position</th>
                                <th>Effective Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($my_requests as $req): ?>
                                <tr>
                                    <td class="text-muted small"><?php echo str_pad($req['movement_id'], 6, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo e($req['first_name'] . ' ' . $req['last_name']); ?></td>
                                    <td><?php echo e($req['movement_type']); ?></td>
                                    <td><?php echo e($req['new_position']); ?></td>
                                    <td><?php echo formatDate($req['effective_date']); ?></td>
                                    <td>
                                        <?php
                                        // TASK 16.3 — Visual status badges: icon + color + text (Req 4.6, 11.5)
                                        $status_map = [
                                            'Pending'  => ['icon' => 'fa-clock',        'class' => 'bg-warning', 'label' => 'Pending Review'],
                                            'Approved' => ['icon' => 'fa-check-circle',  'class' => 'bg-success', 'label' => 'Approved'],
                                            'Rejected' => ['icon' => 'fa-times-circle',  'class' => 'bg-danger',  'label' => 'Rejected'],
                                        ];
                                        $s = $status_map[$req['approval_status']] ?? ['icon' => 'fa-circle', 'class' => 'bg-secondary', 'label' => $req['approval_status']];
                                        ?>
                                        <span class="badge <?php echo $s['class']; ?>" title="<?php echo e($req['approval_status']); ?>">
                                            <i class="fas <?php echo $s['icon']; ?> me-1" aria-hidden="true"></i><?php echo e($s['label']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>


<script>
document.addEventListener('DOMContentLoaded', function () {

    // Progress indicator — driven by employee + movement type dropdowns
    var empSel  = document.getElementById('employee_id_select');
    var typeSel = document.getElementById('movement_type_select');

    function updateProgress() {
        var step2 = document.getElementById('prog-step-2');
        var step3 = document.getElementById('prog-step-3');
        var line2 = document.getElementById('prog-line-2');

        var empOk  = empSel  && empSel.value  !== '';
        var typeOk = typeSel && typeSel.value !== '';

        if (empOk && !typeOk) {
            if (step2) { step2.classList.remove('completed'); step2.classList.add('active'); }
        }
        if (empOk && typeOk) {
            if (step2) {
                step2.classList.remove('active');
                step2.classList.add('completed');
                var n = step2.querySelector('.progress-step-number');
                if (n) n.innerHTML = '<i class="fas fa-check"></i>';
            }
            if (line2) line2.classList.add('completed');
            if (step3) step3.classList.add('active');
        } else {
            if (step2 && step2.classList.contains('completed')) {
                step2.classList.remove('completed');
                step2.classList.add('active');
                var n2 = step2.querySelector('.progress-step-number');
                if (n2) n2.innerHTML = '2';
            }
            if (line2) line2.classList.remove('completed');
            if (step3) step3.classList.remove('active');
        }
    }

    if (empSel)  empSel.addEventListener('change',  updateProgress);
    if (typeSel) typeSel.addEventListener('change', updateProgress);
    updateProgress();

    // Prevent double-submit
    var form = document.getElementById('career-request-form');
    if (form) {
        form.addEventListener('submit', function (e) {
            var btn = document.getElementById('submit-btn');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Submitting...';
            }
        });
    }

    // Auto-scroll to confirmation after successful submit
    var conf = document.getElementById('submission-confirmation');
    if (conf) {
        conf.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>