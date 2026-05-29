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

        // Insert the career movement request
        $insert = $conn->prepare("
            INSERT INTO career_movements 
            (employee_id, movement_type, previous_position, new_position, previous_branch_id, new_branch_id, 
             effective_date, reason, logged_by, approval_status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())
        ");
        $insert->bind_param(
            "issssisi",
            $employee_id,
            $movement_type,
            $previous_position,
            $new_position,
            $previous_branch_id,
            $new_branch_id,
            $effective_date,
            $reason,
            $user_id
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

            $_SESSION['flash_message'] = 'Career movement request submitted successfully. HR Supervisor has been notified.';
            $_SESSION['flash_type'] = 'success';
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
    <!-- Supervisor View - Request Form -->
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="content-card fadeup-1">
                <div class="card-header">
                    <h5><i class="fas fa-plus-circle me-2"></i>New Career Movement Request</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row g-3">
                            <!-- Employee Selection -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Select Employee <span
                                        class="text-danger">*</span></label>
                                <select name="employee_id" class="form-select" required>
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
                                <label class="form-label fw-semibold">Movement Type <span
                                        class="text-danger">*</span></label>
                                <select name="movement_type" class="form-select" required>
                                    <option value="">Select type...</option>
                                    <option value="Promotion">Promotion</option>
                                    <option value="Transfer">Transfer</option>
                                    <option value="Demotion">Demotion</option>
                                    <option value="Role Change">Role Change</option>
                                </select>
                            </div>

                            <!-- New Position -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">New Position <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="new_position" class="form-control" placeholder="e.g. Senior Teller"
                                    required>
                            </div>

                            <!-- New Branch (Optional) -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">New Branch</label>
                                <select name="new_branch_id" class="form-select">
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
                                <label class="form-label fw-semibold">Effective Date <span
                                        class="text-danger">*</span></label>
                                <input type="date" name="effective_date" class="form-control" required
                                    min="<?php echo date('Y-m-d'); ?>">
                            </div>

                            <!-- Reason -->
                            <div class="col-12">
                                <label class="form-label fw-semibold">Reason / Justification</label>
                                <textarea name="reason" class="form-control" rows="3"
                                    placeholder="Provide reason for this career movement..."></textarea>
                            </div>

                            <!-- Submit Button -->
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
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

    <!-- My Submitted Requests -->
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
                                    <td><?php echo e($req['first_name'] . ' ' . $req['last_name']); ?></td>
                                    <td><?php echo e($req['movement_type']); ?></td>
                                    <td><?php echo e($req['new_position']); ?></td>
                                    <td><?php echo formatDate($req['effective_date']); ?></td>
                                    <td>
                                        <?php
                                        $status_class = [
                                            'Pending' => 'bg-warning',
                                            'Approved' => 'bg-success',
                                            'Rejected' => 'bg-danger'
                                        ][$req['approval_status']] ?? 'bg-secondary';
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>">
                                            <?php echo e($req['approval_status']); ?>
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

<?php require_once '../includes/footer.php'; ?>