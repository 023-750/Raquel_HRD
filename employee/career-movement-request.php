<?php
/**
 * Employee Portal - Career Movement Request
 * Branch Supervisors (rank_category_id = 4) submit Transfer-only requests
 * for employees in their branch. Four-step approval chain:
 *   Branch Supervisor → Branch Manager → HR Supervisor → HR Manager
 *
 * Tasks implemented: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 4.8
 */
$page_title = 'Career Movement Request';
require_once '../includes/session-check.php';
checkRole(['Employee']);
require_once '../includes/functions.php';

$supervisor_employee_id = (int) ($_SESSION['employee_id'] ?? 0);
$supervisor_branch_id   = (int) ($_SESSION['branch_id']   ?? 0);
$user_id                = (int) ($_SESSION['user_id']      ?? 0);
$full_name              = trim($_SESSION['full_name'] ?? '');
$job_title_session      = trim($_SESSION['job_title'] ?? 'Branch Supervisor');

// ── Ensure portal schema columns exist before any queries reference them ─────
ensureCareerProgressionMovements($conn);

// ── Fetch supervisor's own employee record for hero display ──────────────────
$sup_stmt = $conn->prepare(
    "SELECT e.first_name, e.last_name, e.job_title, e.profile_picture,
            e.rank_category_id, e.branch_id, e.department_id, b.branch_name
     FROM employees e
     LEFT JOIN branches b ON e.branch_id = b.branch_id
     WHERE e.employee_id = ? LIMIT 1"
);
$sup_stmt->bind_param("i", $supervisor_employee_id);
$sup_stmt->execute();
$sup_emp = $sup_stmt->get_result()->fetch_assoc() ?? [];
$sup_stmt->close();

// ── Task 4.1 — Access guard: rank_category_id = 4 AND at least one branch employee ──
$is_branch_supervisor = ($sup_emp && (int) ($sup_emp['rank_category_id'] ?? 0) === 4);

// Use branch_id and department_id from the DB record — session values may be stale or 0
$sup_branch_fetch = $conn->prepare("SELECT branch_id, department_id FROM employees WHERE employee_id = ? LIMIT 1");
$sup_branch_fetch->bind_param("i", $supervisor_employee_id);
$sup_branch_fetch->execute();
$sup_branch_row = $sup_branch_fetch->get_result()->fetch_assoc();
$sup_branch_fetch->close();
if ($sup_branch_row) {
    if ((int)$sup_branch_row['branch_id'] > 0) {
        $supervisor_branch_id = (int)$sup_branch_row['branch_id'];
    }
    $supervisor_department_id = (int)($sup_branch_row['department_id'] ?? 0);
} else {
    $supervisor_department_id = 0;
}

// Only bother fetching branch employees when rank check passes
$branch_employees = [];
$no_employees_in_branch = false;
if ($is_branch_supervisor) {
    $branch_employees = getBranchEmployeesForDropdown(
        $conn,
        $supervisor_employee_id,
        $supervisor_branch_id,
        $supervisor_department_id
    );
    if (empty($branch_employees)) {
        $no_employees_in_branch = true;
    }
}

$errors   = [];
$success  = false;

// ── Task 4.5 — POST handler ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();

    if ($is_branch_supervisor && !$no_employees_in_branch) {
        $employee_id    = (int)   ($_POST['employee_id']    ?? 0);
        $movement_type  = trim(    $_POST['movement_type']   ?? '');
        $new_position   = trim(    $_POST['new_position']    ?? '');
        $new_branch_id  = !empty($_POST['new_branch_id']) ? (int) $_POST['new_branch_id'] : null;
        $effective_date = trim(    $_POST['effective_date']  ?? '');
        $reason         = trim(    $_POST['reason']          ?? '');

        // Hard-enforce Transfer only (requirement 1.2)
        if ($movement_type !== 'Transfer') {
            $errors[] = 'Only Transfer requests may be submitted through the Employee Portal.';
        }

        // Require destination branch
        if (empty($new_branch_id)) {
            $errors[] = 'A different destination branch must be selected.';
        }

        // Require effective date
        if (empty($effective_date)) {
            $errors[] = 'Please enter an effective date.';
        }

        // Run the business validation function
        if (empty($errors)) {
            $validation_error = validateTransferSubmission(
                $conn,
                $supervisor_branch_id,
                $supervisor_employee_id,
                $employee_id,
                $new_branch_id
            );
            if ($validation_error !== null) {
                $errors[] = $validation_error;
            }
        }

        // ── Task 4.6 — Insertion with portal_workflow_stage ──────────────────
        if (empty($errors)) {
            ensureCareerProgressionMovements($conn);

            // Fetch target employee's current position and branch
            $emp_stmt = $conn->prepare(
                "SELECT job_title, branch_id FROM employees WHERE employee_id = ? LIMIT 1"
            );
            $emp_stmt->bind_param("i", $employee_id);
            $emp_stmt->execute();
            $emp_row = $emp_stmt->get_result()->fetch_assoc();
            $emp_stmt->close();

            $previous_position  = $emp_row['job_title']  ?? null;
            $previous_branch_id = (int) ($emp_row['branch_id'] ?? $supervisor_branch_id);

            // Build initiated_by_name from session or employee record
            $initiated_by_name = $full_name;
            if (empty($initiated_by_name)) {
                $initiated_by_name = trim(
                    ($sup_emp['first_name'] ?? '') . ' ' . ($sup_emp['last_name'] ?? '')
                );
            }
            $initiated_by_role = $sup_emp['job_title'] ?? $job_title_session;

            // ── Look up Branch Manager for the submitting branch ──────────
            $bm_stmt = $conn->prepare(
                "SELECT employee_id FROM employees
                 WHERE branch_id = ? AND rank_category_id = 3 AND is_active = 1
                 LIMIT 1"
            );
            $bm_stmt->bind_param("i", $supervisor_branch_id);
            $bm_stmt->execute();
            $bm_row = $bm_stmt->get_result()->fetch_assoc();
            $bm_stmt->close();

            $bm_user_id = null;
            if ($bm_row) {
                $bm_emp_id  = (int) $bm_row['employee_id'];
                $bm_user_id = getPreferredLinkedUserId($conn, $bm_emp_id, 'employee_portal');
            }

            // If Branch Manager exists, stage starts at Pending_Branch_Manager.
            // If no Branch Manager is assigned/active, directly route to Pending_HR_Supervisor.
            $initial_stage = $bm_user_id ? 'Pending_Branch_Manager' : 'Pending_HR_Supervisor';

            $insert = $conn->prepare(
                "INSERT INTO career_movements
                    (employee_id, movement_type, previous_position, new_position,
                     previous_branch_id, new_branch_id, effective_date, reason,
                     logged_by, initiated_by_name, initiated_by_role,
                     request_source, approval_status, portal_workflow_stage,
                     created_at)
                 VALUES (?, 'Transfer', ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Employee Portal', 'Pending',
                         ?, NOW())"
            );
            $insert->bind_param(
                "issiississs",
                $employee_id,        // i - int
                $previous_position,  // s - string
                $new_position,       // s - string
                $previous_branch_id, // i - int
                $new_branch_id,      // i - int
                $effective_date,     // s - string
                $reason,             // s - string
                $user_id,            // i - int
                $initiated_by_name,  // s - string
                $initiated_by_role,  // s - string
                $initial_stage       // s - string
            );

            if ($insert->execute()) {
                $movement_id = (int) $conn->insert_id;
                $insert->close();

                // Fetch target employee name for notification messages
                $tgt_stmt = $conn->prepare(
                    "SELECT first_name, last_name FROM employees WHERE employee_id = ? LIMIT 1"
                );
                $tgt_stmt->bind_param("i", $employee_id);
                $tgt_stmt->execute();
                $tgt_row = $tgt_stmt->get_result()->fetch_assoc();
                $tgt_stmt->close();
                $target_emp_name = trim(
                    ($tgt_row['first_name'] ?? '') . ' ' . ($tgt_row['last_name'] ?? '')
                );

                if ($bm_user_id) {
                    // BM found — notify BM; stage is Pending_Branch_Manager
                    createNotification(
                        $conn,
                        $bm_user_id,
                        'Transfer Request Pending Your Approval',
                        $initiated_by_name . ' has submitted a Transfer request for ' . $target_emp_name . '.',
                        BASE_URL . '/employee/branch-manager-approvals.php'
                    );
                } else {
                    // No BM user — routed directly to HR Supervisor
                    $hr_sup_res = $conn->query(
                        "SELECT user_id FROM users WHERE role = 'HR Supervisor' AND is_active = 1"
                    );
                    if ($hr_sup_res) {
                        while ($hr_row = $hr_sup_res->fetch_assoc()) {
                            createNotification(
                                $conn,
                                (int) $hr_row['user_id'],
                                'Transfer Request Pending Your Approval',
                                $initiated_by_name . ' submitted a Transfer request for ' .
                                    $target_emp_name . ' (no Branch Manager found for branch).',
                                BASE_URL . '/supervisor/career-movements.php'
                            );
                        }
                        $hr_sup_res->free();
                    } else {
                        error_log(
                            "Career movement notification skipped: no active HR Supervisor users found."
                        );
                    }
                }

                // Audit log
                logAudit(
                    $conn,
                    $user_id,
                    'CREATE',
                    'Career Movement',
                    $movement_id,
                    'Portal Transfer request submitted for employee_id=' . $employee_id . ' (Initial Stage: ' . $initial_stage . ')',
                    ['module' => 'Career Progression', 'target_employee_id' => $employee_id,
                     'branch_id' => $supervisor_branch_id]
                );

                // Confirmation and redirect
                $_SESSION['career_confirmation'] = [
                    'ref'       => str_pad($movement_id, 6, '0', STR_PAD_LEFT),
                    'employee'  => $target_emp_name,
                    'effective' => $effective_date,
                    'type'      => 'Transfer',
                ];

                header('Location: ' . $_SERVER['PHP_SELF']);
                exit();

            } else {
                $insert->close();
                $errors[] = 'A database error occurred while saving your request. Please try again.';
            }
        }

    } // end if $is_branch_supervisor
} // end POST

// Pop confirmation from session (for display after redirect)
$career_confirmation = null;
if (!empty($_SESSION['career_confirmation'])) {
    $career_confirmation = $_SESSION['career_confirmation'];
    unset($_SESSION['career_confirmation']);
}

// ── Load active job titles for the New Position dropdown ─────────────────────
$active_positions = [];
$pos_res = $conn->query(
    "SELECT job_title FROM job_titles WHERE is_active = 1 ORDER BY job_title ASC"
);
if ($pos_res) {
    while ($pos_row = $pos_res->fetch_assoc()) {
        $active_positions[] = $pos_row['job_title'];
    }
    $pos_res->free();
}

// ── Task 4.4 — Load active branches (excluding supervisor's own branch) ──────
$dest_branches = [];
$br_stmt = $conn->prepare(
    "SELECT branch_id, branch_name FROM branches
     WHERE is_active = 1 AND branch_id != ?
     ORDER BY branch_name"
);
$br_stmt->bind_param("i", $supervisor_branch_id);
$br_stmt->execute();
$br_res = $br_stmt->get_result();
while ($br_row = $br_res->fetch_assoc()) {
    $dest_branches[] = $br_row;
}
$br_stmt->close();

// ── Task 4.8 — Status table query ────────────────────────────────────────────
$my_requests = [];
$status_stmt = $conn->prepare(
    "SELECT cm.movement_id, cm.portal_workflow_stage, cm.approval_status,
            cm.effective_date, cm.created_at,
            cm.branch_manager_comments, cm.hr_supervisor_comments, cm.manager_comments,
            e.first_name, e.last_name,
            b.branch_name AS dest_branch_name
     FROM career_movements cm
     JOIN employees e ON cm.employee_id = e.employee_id
     LEFT JOIN branches b ON cm.new_branch_id = b.branch_id
     WHERE cm.request_source = 'Employee Portal'
       AND cm.logged_by = ?
       AND cm.portal_workflow_stage IS NOT NULL
     ORDER BY cm.created_at DESC
     LIMIT 20"
);
$status_stmt->bind_param("i", $user_id);
$status_stmt->execute();
$status_res = $status_stmt->get_result();
while ($sr = $status_res->fetch_assoc()) {
    $my_requests[] = $sr;
}
$status_stmt->close();

require_once '../includes/header.php';
?>

<!-- ── Page Hero ─────────────────────────────────────────────────────────── -->
<div class="page-hero fadeup">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-0 gap-4">
        <div class="d-flex align-items-center gap-4 flex-wrap">
            <img src="<?php echo getEmployeeAvatar($sup_emp['profile_picture'] ?? ''); ?>"
                 loading="lazy"
                 alt="Profile photo"
                 style="width:100px;height:100px;border-radius:50%;object-fit:cover;border:4px solid rgba(255,255,255,.3);box-shadow:0 4px 15px rgba(0,0,0,.2);">
            <div>
                <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.55);">
                    Employee Portal &middot; Career Management
                </div>
                <h2 class="text-white fw-bold mb-1 mt-1">Career Movement Request</h2>
                <p class="mb-2 text-white-50 small">
                    <i class="fas fa-briefcase me-1"></i><?php echo e($sup_emp['job_title'] ?? '—'); ?>
                    &bull; <?php echo e($sup_emp['branch_name'] ?? '—'); ?>
                </p>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size:.78rem;">
                        <li class="breadcrumb-item">
                            <a href="<?php echo BASE_URL; ?>/employee/dashboard.php"
                               class="text-white-50 text-decoration-none">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item text-white active" aria-current="page">
                            Career Movement Request
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="d-none d-md-block text-end">
            <a href="<?php echo BASE_URL; ?>/employee/dashboard.php"
               class="btn btn-outline-light btn-sm rounded-pill px-3">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>
</div>

<!-- ── Mobile back button ──────────────────────────────────────────────────── -->
<div class="d-md-none mt-3 mb-2 fadeup" style="animation-delay:.1s;">
    <a href="<?php echo BASE_URL; ?>/employee/dashboard.php"
       class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
    </a>
</div>

<div class="container-fluid px-3 py-4">

    <?php if ($career_confirmation): ?>
    <!-- ── Success confirmation panel ───────────────────────────────────────── -->
    <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
        <div class="d-flex align-items-start gap-3">
            <i class="fas fa-check-circle fa-2x text-success mt-1"></i>
            <div>
                <h6 class="alert-heading mb-1">Transfer Request Submitted</h6>
                <p class="mb-1">
                    Your request for <strong><?php echo e($career_confirmation['employee']); ?></strong>
                    has been submitted successfully.
                </p>
                <p class="mb-0 small text-muted">
                    Reference #: <strong><?php echo e($career_confirmation['ref']); ?></strong>
                    &bull; Effective Date: <strong><?php echo formatDate($career_confirmation['effective']); ?></strong>
                </p>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <?php if (!$is_branch_supervisor): ?>
    <!-- ── Task 4.1: Access restricted message ──────────────────────────────── -->
    <div class="row justify-content-center">
        <div class="col-12 col-lg-7">
            <div class="alert alert-info shadow-sm" role="alert">
                <div class="d-flex align-items-start gap-3">
                    <i class="fas fa-info-circle fa-2x text-info mt-1"></i>
                    <div>
                        <h6 class="alert-heading mb-1">Feature Restricted</h6>
                        <p class="mb-0">
                            Career Movement Requests through the Employee Portal are available only to
                            <strong>Branch Supervisors</strong> (rank level 4). If you believe this is
                            an error, please contact your HR department.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php elseif ($no_employees_in_branch): ?>
    <!-- ── Task 4.3: No eligible employees message ──────────────────────────── -->
    <div class="row justify-content-center">
        <div class="col-12 col-lg-7">
            <div class="alert alert-info shadow-sm" role="alert">
                <div class="d-flex align-items-start gap-3">
                    <i class="fas fa-users fa-2x text-info mt-1"></i>
                    <div>
                        <h6 class="alert-heading mb-1">No Eligible Employees</h6>
                        <p class="mb-0">
                            There are currently no active employees in your branch available for
                            Transfer. Please contact HR if you believe this is incorrect.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php else: ?>
    <!-- ── Main content: form + status table ─────────────────────────────────── -->
    <div class="row g-4">

        <!-- ── Submission Form ─────────────────────────────────────────────── -->
        <div class="col-12 col-xl-5">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold text-dark">
                        <i class="fas fa-paper-plane me-2 text-primary"></i>Submit Transfer Request
                    </h6>
                </div>
                <div class="card-body">

                    <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong><i class="fas fa-exclamation-triangle me-1"></i>Please fix the following:</strong>
                        <ul class="mb-0 mt-2 ps-3">
                            <?php foreach ($errors as $err): ?>
                                <li><?php echo e($err); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>"
                          novalidate>
                        <?php echo csrfField(); ?>

                        <!-- Task 4.2: Transfer-only movement type (hidden, fixed) -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Movement Type</label>
                            <select name="movement_type" class="form-select" required aria-required="true">
                                <option value="Transfer" selected>Transfer</option>
                            </select>
                        </div>

                        <!-- Task 4.3: Branch-scoped employee dropdown -->
                        <div class="mb-3">
                            <label for="employee_id" class="form-label fw-semibold">Employee <span class="text-danger">*</span></label>
                            <select name="employee_id" id="employee_id" class="form-select" required aria-required="true">
                                <option value="">— Select Employee —</option>
                                <?php foreach ($branch_employees as $be): ?>
                                    <option value="<?php echo (int) $be['employee_id']; ?>"
                                        <?php echo (isset($_POST['employee_id']) && (int) $_POST['employee_id'] === (int) $be['employee_id']) ? 'selected' : ''; ?>>
                                        <?php echo e($be['last_name'] . ', ' . $be['first_name'] . ' — ' . ($be['job_title'] ?? '')); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Task 4.4: Destination Branch (required) -->
                        <div class="mb-3">
                            <label for="new_branch_id" class="form-label fw-semibold">Destination Branch <span class="text-danger">*</span></label>
                            <select name="new_branch_id" id="new_branch_id" class="form-select" required aria-required="true">
                                <option value="">— Select Destination Branch —</option>
                                <?php foreach ($dest_branches as $db): ?>
                                    <option value="<?php echo (int) $db['branch_id']; ?>"
                                        <?php echo (isset($_POST['new_branch_id']) && (int) $_POST['new_branch_id'] === (int) $db['branch_id']) ? 'selected' : ''; ?>>
                                        <?php echo e($db['branch_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">The employee's current branch is excluded.</div>
                        </div>

                        <!-- New Position (optional dropdown) -->
                        <div class="mb-3">
                            <label for="new_position" class="form-label fw-semibold">New Position / Role <span class="text-muted fw-normal">(optional)</span></label>
                            <select name="new_position" id="new_position" class="form-select">
                                <option value="">— Keep current position —</option>
                                <?php foreach ($active_positions as $pos): ?>
                                    <option value="<?php echo e($pos); ?>"
                                        <?php echo (isset($_POST['new_position']) && $_POST['new_position'] === $pos) ? 'selected' : ''; ?>>
                                        <?php echo e($pos); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Leave blank if the position stays the same after transfer.</div>
                        </div>

                        <!-- Effective Date -->
                        <div class="mb-3">
                            <label for="effective_date" class="form-label fw-semibold">Effective Date <span class="text-danger">*</span></label>
                            <input type="date" name="effective_date" id="effective_date"
                                   class="form-control" required aria-required="true"
                                   min="<?php echo date('Y-m-d'); ?>"
                                   value="<?php echo e($_POST['effective_date'] ?? ''); ?>">
                        </div>

                        <!-- Reason -->
                        <div class="mb-4">
                            <label for="reason" class="form-label fw-semibold">Reason / Justification <span class="text-muted fw-normal">(optional)</span></label>
                            <textarea name="reason" id="reason" class="form-control" rows="3"
                                      maxlength="1000"
                                      placeholder="Briefly explain the reason for this transfer…"><?php echo e($_POST['reason'] ?? ''); ?></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary fw-semibold">
                                <i class="fas fa-paper-plane me-2"></i>Submit Transfer Request
                            </button>
                        </div>
                    </form>

                </div><!-- /.card-body -->
            </div><!-- /.card -->
        </div><!-- /.col (form) -->

        <!-- ── Task 4.8: Status table ────────────────────────────────────────── -->
        <div class="col-12 col-xl-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-semibold text-dark">
                        <i class="fas fa-list-alt me-2 text-primary"></i>My Submitted Requests
                    </h6>
                    <span class="badge bg-primary rounded-pill"><?php echo count($my_requests); ?></span>
                </div>
                <div class="card-body p-0">

                    <?php if (empty($my_requests)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i>
                        <p class="mb-0">You have not submitted any Transfer requests yet.</p>
                    </div>

                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size:.85rem;">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Ref #</th>
                                    <th>Employee</th>
                                    <th>Destination</th>
                                    <th>Effective</th>
                                    <th>Submitted</th>
                                    <th>Stage</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($my_requests as $req): ?>
                                <?php
                                // Distinct Badge CSS and Icons for each stage
                                $stage = $req['portal_workflow_stage'] ?? '';
                                $badge_class = match($stage) {
                                    'Pending_Branch_Manager' => 'badge bg-warning text-dark',
                                    'Pending_HR_Supervisor'  => 'badge bg-primary text-white',
                                    'Pending_HR_Manager'     => 'badge bg-info text-dark',
                                    'Approved'               => 'badge bg-success',
                                    'Rejected'               => 'badge bg-danger',
                                    default                  => 'badge bg-secondary',
                                };
                                $stage_icon = match($stage) {
                                    'Pending_Branch_Manager' => 'fa-user-tie',
                                    'Pending_HR_Supervisor'  => 'fa-user-shield',
                                    'Pending_HR_Manager'     => 'fa-paper-plane',
                                    'Approved'               => 'fa-check-circle',
                                    'Rejected'               => 'fa-times-circle',
                                    default                  => 'fa-clock',
                                };

                                // Rejection reason: first non-null of bm, hr_sup, manager comments
                                $rejection_reason = null;
                                if ($stage === 'Rejected') {
                                    foreach (['branch_manager_comments', 'hr_supervisor_comments', 'manager_comments'] as $fld) {
                                        if (!empty($req[$fld])) {
                                            $rejection_reason = $req[$fld];
                                            break;
                                        }
                                    }
                                    if ($rejection_reason === null) {
                                        $rejection_reason = 'No reason provided';
                                    }
                                }
                                ?>
                                <tr>
                                    <td class="ps-3 fw-semibold text-muted" style="white-space:nowrap;">
                                        #<?php echo str_pad($req['movement_id'], 6, '0', STR_PAD_LEFT); ?>
                                    </td>
                                    <td>
                                        <?php echo e(trim(($req['last_name'] ?? '') . ', ' . ($req['first_name'] ?? ''))); ?>
                                    </td>
                                    <td><?php echo e($req['dest_branch_name'] ?? '—'); ?></td>
                                    <td style="white-space:nowrap;">
                                        <?php echo formatDate($req['effective_date'] ?? ''); ?>
                                    </td>
                                    <td style="white-space:nowrap;">
                                        <?php echo formatDate($req['created_at'] ?? ''); ?>
                                    </td>
                                    <td>
                                        <span class="<?php echo $badge_class; ?>" style="font-size:.75rem;">
                                            <i class="fas <?php echo $stage_icon; ?> me-1"></i><?php echo e(getPortalStageLabel($stage) ?: ($req['approval_status'] ?? 'Pending')); ?>
                                        </span>
                                        <?php if ($rejection_reason !== null): ?>
                                        <div class="mt-1 text-danger small" style="font-size:.72rem;max-width:180px;">
                                            <i class="fas fa-comment-alt me-1"></i><?php echo e($rejection_reason); ?>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>

                </div><!-- /.card-body -->
            </div><!-- /.card -->
        </div><!-- /.col (status table) -->

    </div><!-- /.row -->
    <?php endif; // end $is_branch_supervisor && !$no_employees_in_branch ?>

</div><!-- /.container-fluid -->

<?php require_once '../includes/footer.php'; ?>
