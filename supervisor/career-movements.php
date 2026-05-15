<?php
$page_title = 'Career Movements';
require_once '../includes/session-check.php';
checkRole(['HR Supervisor']);
require_once '../includes/functions.php';

$movement_ready = ensureCareerProgressionMovements($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_movement'])) {
    if (!$movement_ready) {
        redirectWith(BASE_URL . '/supervisor/career-movements.php', 'danger', 'Career Movements could not be initialized.');
    }

    $employee_id = (int) ($_POST['employee_id'] ?? 0);
    $movement_type = trim($_POST['movement_type'] ?? '');
    $new_position = trim($_POST['new_position'] ?? '');
    $new_branch_id = ($_POST['new_branch_id'] ?? '') !== '' ? (int) $_POST['new_branch_id'] : null;
    $effective_date = trim($_POST['effective_date'] ?? '');
    $reason = trim($_POST['reason'] ?? '');
    $allowed_types = ['Promotion', 'Transfer', 'Demotion', 'Role Change'];

    if ($employee_id <= 0 || !in_array($movement_type, $allowed_types, true) || $new_position === '' || $effective_date === '' || $reason === '') {
        redirectWith(BASE_URL . '/supervisor/career-movements.php', 'danger', 'Please complete all required Career Movement fields.');
    }

    $emp_stmt = $conn->prepare("
        SELECT employee_id, employee_code, first_name, last_name, job_title, branch_id
        FROM employees
        WHERE employee_id = ? AND is_active = 1
        LIMIT 1
    ");
    $emp_stmt->bind_param("i", $employee_id);
    $emp_stmt->execute();
    $employee = $emp_stmt->get_result()->fetch_assoc();
    $emp_stmt->close();

    if (!$employee) {
        redirectWith(BASE_URL . '/supervisor/career-movements.php', 'danger', 'Selected employee is not available.');
    }

    $previous_position = $employee['job_title'] ?? '';
    $previous_branch_id = !empty($employee['branch_id']) ? (int) $employee['branch_id'] : null;
    if ($new_branch_id === $previous_branch_id) {
        $new_branch_id = null;
    }

    $logged_by = (int) $_SESSION['user_id'];
    $insert = $conn->prepare("
        INSERT INTO career_movements
            (employee_id, movement_type, previous_position, new_position, previous_branch_id, new_branch_id, effective_date, reason, logged_by, approval_status, initiated_by_name, initiated_by_role, initiated_via, request_source)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', 'HR Supervisor', 'HR Supervisor', 'HR Portal', 'HR Portal')
    ");
    $insert->bind_param(
        "isssiissi",
        $employee_id,
        $movement_type,
        $previous_position,
        $new_position,
        $previous_branch_id,
        $new_branch_id,
        $effective_date,
        $reason,
        $logged_by
    );

    if (!$insert->execute()) {
        $insert->close();
        redirectWith(BASE_URL . '/supervisor/career-movements.php', 'danger', 'Unable to create the career movement.');
    }

    $movement_id = $insert->insert_id;
    $insert->close();

    $employee_name = trim(($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? ''));
    $managers = $conn->query("SELECT user_id FROM users WHERE role = 'HR Manager' AND is_active = 1");
    while ($manager = $managers->fetch_assoc()) {
        createNotification(
            $conn,
            (int) $manager['user_id'],
            'Career Movement Submitted',
            "A {$movement_type} has been submitted for {$employee_name}.",
            BASE_URL . '/manager/career-movements.php'
        );
    }

    logAudit($conn, $logged_by, 'CREATE', 'Career Movement', $movement_id, "Submitted {$movement_type} for {$employee_name}.");
    redirectWith(BASE_URL . '/supervisor/career-movements.php', 'success', 'Career movement submitted for HR Manager review.');

}

require_once '../includes/header.php';

$branch_id = (int) ($_SESSION['branch_id'] ?? 0);
$supervisor_id = (int) ($_SESSION['user_id'] ?? 0);
$employees = [];
$branches = [];
$branch_names = [];
$movements = [];
$counts = ['Submitted' => 0, 'Pending' => 0, 'Approved' => 0, 'Rejected' => 0, 'Applied' => 0];

$employee_result = $conn->query("
    SELECT employee_id, employee_code, first_name, last_name, job_title, branch_id
    FROM employees
    WHERE is_active = 1
      AND employee_id NOT IN (SELECT employee_id FROM users WHERE role = 'Admin' AND employee_id IS NOT NULL)
    ORDER BY last_name, first_name
");
while ($row = $employee_result->fetch_assoc()) {
    $employees[] = $row;
}

$branch_result = $conn->query("SELECT branch_id, branch_name FROM branches ORDER BY branch_name");
while ($row = $branch_result->fetch_assoc()) {
    $branches[] = $row;
    $branch_names[(string) $row['branch_id']] = $row['branch_name'];
}

if ($movement_ready) {
    $stmt = $conn->prepare("
        SELECT cm.*,
            e.employee_code,
            e.job_title AS current_job_title,
            CONCAT(e.last_name, ', ', e.first_name) AS employee_name,
            pb.branch_name AS previous_branch_name,
            nb.branch_name AS new_branch_name,
            u1.full_name AS logged_by_name,
            u2.full_name AS approved_by_name
        FROM career_movements cm
        JOIN employees e ON cm.employee_id = e.employee_id
        LEFT JOIN branches pb ON cm.previous_branch_id = pb.branch_id
        LEFT JOIN branches nb ON cm.new_branch_id = nb.branch_id
        LEFT JOIN users u1 ON cm.logged_by = u1.user_id
        LEFT JOIN users u2 ON cm.approved_by = u2.user_id
        WHERE cm.logged_by = ? OR e.branch_id = ? OR cm.request_source = 'Employee Portal'
        ORDER BY cm.created_at DESC
    ");
    $stmt->bind_param("ii", $supervisor_id, $branch_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $movements[] = $row;
        $counts['Submitted']++;
        if (isset($counts[$row['approval_status']])) {
            $counts[$row['approval_status']]++;
        }
        if ((int) ($row['is_applied'] ?? 0) === 1) {
            $counts['Applied']++;
        }
    }
    $stmt->close();
}

function supervisorCareerMovementTypeClass($type)
{
    if ($type === 'Promotion')
        return 'bg-success';
    if ($type === 'Transfer')
        return 'bg-info text-dark';
    if ($type === 'Demotion')
        return 'bg-danger';
    if ($type === 'Role Change')
        return 'bg-primary';
    return 'bg-secondary';
}

function supervisorCareerMovementStatusClass($status)
{
    if ($status === 'Approved')
        return 'bg-success';
    if ($status === 'Rejected')
        return 'bg-danger';
    return 'bg-warning text-dark';
}
?>

<div class="page-hero fadeup">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
        <div>
            <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.55);">HR
                Supervisor &middot; Career</div>
            <h4 class="text-white fw-bold mb-0 mt-1"><i class="fas fa-route me-2" style="color:#BD9414;"></i>Career
                Movements</h4>
        </div>
        <div style="color:rgba(255,255,255,.6);font-size:.8rem;">
            <i class="fas fa-wrench me-1"></i>In development
        </div>
    </div>
    <div class="row g-3">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-value"><?php echo $counts['Submitted']; ?></div>
                <div class="stat-label">Submitted</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-value"><?php echo $counts['Pending']; ?></div>
                <div class="stat-label">Pending</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-value"><?php echo $counts['Approved']; ?></div>
                <div class="stat-label">Approved</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-value"><?php echo $counts['Applied']; ?></div>
                <div class="stat-label">Applied</div>
            </div>
        </div>
    </div>
</div>

<div class="chart-card fadeup mb-4">
    <div class="cc-body d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <div class="text-uppercase text-muted fw-bold" style="font-size:.72rem;letter-spacing:1px;">Career</div>
            <h5 class="fw-bold mb-1">Movements</h5>
            <p class="text-muted mb-0">this section is currently on development</p>
        </div>
        <span class="badge bg-light text-dark border px-3 py-2">Supervisor Submission</span>
    </div>
</div>

<?php if (!$movement_ready): ?>
    <div class="alert alert-danger">Career Movements could not be initialized. Please check the database connection and
        table permissions.</div>
<?php endif; ?>

<div class="chart-card fadeup">
    <div class="cc-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <ul class="nav nav-tabs cc-header-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#movementList" type="button"
                    role="tab">
                    <i class="fas fa-route me-1"></i>Career Movements
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#movementCreate" type="button" role="tab">
                    <i class="fas fa-plus me-1"></i>Create Movement
                </button>
            </li>
        </ul>
        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="form-control form-control-sm" id="movementSearch"
                placeholder="Search movements...">
        </div>
    </div>
    <div class="cc-body p-0">
        <div class="tab-content">
            <div class="tab-pane fade show active" id="movementList" role="tabpanel">
                <div class="table-responsive">
                    <table class="table modern-table align-middle mb-0" id="movementTable">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Type</th>
                                <th>Position</th>
                                <th>Branch</th>
                                <th>Effective</th>
                                <th>Source</th>
                                <th>Status</th>
                                <th>Decision</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($movements)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fas fa-route d-block mb-2" style="font-size:2rem;opacity:.2;"></i>
                                        No career movements have been submitted yet.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($movements as $movement): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold"><?php echo e($movement['employee_name']); ?></div>
                                            <small class="text-muted"><?php echo e(getEmployeeDisplayId($movement)); ?> &middot;
                                                <?php echo e($movement['current_job_title']); ?></small>
                                        </td>
                                        <td><span
                                                class="badge <?php echo supervisorCareerMovementTypeClass($movement['movement_type']); ?>"><?php echo e($movement['movement_type']); ?></span>
                                        </td>
                                        <td>
                                            <div class="small text-muted">
                                                <?php echo e($movement['previous_position'] ?: 'N/A'); ?></div>
                                            <div class="fw-semibold"><?php echo e($movement['new_position']); ?></div>
                                        </td>
                                        <td>
                                            <?php if (!empty($movement['new_branch_id'])): ?>
                                                <div class="small text-muted">
                                                    <?php echo e($movement['previous_branch_name'] ?: 'N/A'); ?></div>
                                                <div class="fw-semibold"><?php echo e($movement['new_branch_name'] ?: 'N/A'); ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted small">No branch change</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="small"><?php echo formatDate($movement['effective_date']); ?></span>
                                        </td>
                                        <td>
                                            <?php if ($movement['request_source'] === 'Employee Portal'): ?>
                                                <span class="badge bg-info text-dark">Employee Portal</span>
                                                <?php if (!empty($movement['initiated_by_name'])): ?>
                                                    <div class="small text-muted mt-1">by
                                                        <?php echo e($movement['initiated_by_name']); ?></div>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">HR Portal</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span
                                                class="badge <?php echo supervisorCareerMovementStatusClass($movement['approval_status']); ?>"><?php echo e($movement['approval_status']); ?></span>
                                            <?php if ($movement['approval_status'] === 'Approved' && (int) $movement['is_applied'] === 1): ?>
                                                <span class="badge bg-success">Applied</span>
                                            <?php elseif ($movement['approval_status'] === 'Approved'): ?>
                                                <span class="badge bg-secondary">Scheduled</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span
                                                class="small text-muted"><?php echo e($movement['approved_by_name'] ?: 'Awaiting HR Manager'); ?></span>
                                        </td>
                                    </tr>
                                    <?php if (!empty($movement['reason']) || !empty($movement['manager_comments'])): ?>
                                        <tr class="bg-light">
                                            <td colspan="7" class="small text-muted">
                                                <?php if (!empty($movement['reason'])): ?>
                                                    <span class="fw-semibold">Reason:</span> <?php echo e($movement['reason']); ?>
                                                <?php endif; ?>
                                                <?php if (!empty($movement['manager_comments'])): ?>
                                                    <span class="ms-3 fw-semibold">Manager Notes:</span>
                                                    <?php echo e($movement['manager_comments']); ?>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="movementCreate" role="tabpanel">
                <div class="p-4">
                    <form method="POST">
                        <input type="hidden" name="create_movement" value="1">
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <label class="form-label fw-semibold">Employee <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" name="employee_id" id="employeeSelect" required <?php echo !$movement_ready ? 'disabled' : ''; ?>>
                                    <option value="">Select employee</option>
                                    <?php foreach ($employees as $employee): ?>
                                        <option value="<?php echo (int) $employee['employee_id']; ?>"
                                            data-jobtitle="<?php echo e($employee['job_title']); ?>"
                                            data-branch="<?php echo (int) $employee['branch_id']; ?>">
                                            <?php echo e($employee['last_name'] . ', ' . $employee['first_name'] . ' - ' . getEmployeeDisplayId($employee)); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-lg-6">
                                <div class="p-3 rounded border bg-light h-100" id="employeeInfo" style="display:none;">
                                    <div class="small text-muted">Current Assignment</div>
                                    <div class="fw-bold" id="currentPosition"></div>
                                    <div class="small text-muted" id="currentBranch"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Movement Type <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" name="movement_type" required <?php echo !$movement_ready ? 'disabled' : ''; ?>>
                                    <option value="">Select type</option>
                                    <option value="Promotion">Promotion</option>
                                    <option value="Transfer">Transfer</option>
                                    <option value="Demotion">Demotion</option>
                                    <option value="Role Change">Role Change</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">New Position <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="new_position"
                                    placeholder="e.g. Branch Operations Supervisor" required <?php echo !$movement_ready ? 'disabled' : ''; ?>>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">New Branch</label>
                                <select class="form-select" name="new_branch_id" <?php echo !$movement_ready ? 'disabled' : ''; ?>>
                                    <option value="">No branch change</option>
                                    <?php foreach ($branches as $branch): ?>
                                        <option value="<?php echo (int) $branch['branch_id']; ?>">
                                            <?php echo e($branch['branch_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Effective Date <span
                                        class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="effective_date" required <?php echo !$movement_ready ? 'disabled' : ''; ?>>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Reason <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="reason" rows="3"
                                    placeholder="Enter the justification for this career movement." required <?php echo !$movement_ready ? 'disabled' : ''; ?>></textarea>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" <?php echo !$movement_ready ? 'disabled' : ''; ?>>
                                <i class="fas fa-paper-plane me-1"></i>Submit for Review
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const branchNames = <?php echo json_encode($branch_names, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
        const employeeSelect = document.getElementById('employeeSelect');
        const employeeInfo = document.getElementById('employeeInfo');
        const currentPosition = document.getElementById('currentPosition');
        const currentBranch = document.getElementById('currentBranch');
        const searchInput = document.getElementById('movementSearch');
        const rows = document.querySelectorAll('#movementTable tbody tr');

        if (employeeSelect) {
            employeeSelect.addEventListener('change', function () {
                const option = this.options[this.selectedIndex];
                if (!option.value) {
                    employeeInfo.style.display = 'none';
                    return;
                }

                currentPosition.textContent = option.dataset.jobtitle || 'N/A';
                currentBranch.textContent = branchNames[option.dataset.branch] || 'N/A';
                employeeInfo.style.display = 'block';
            });
        }

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const term = this.value.trim().toLowerCase();
                rows.forEach(row => {
                    row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
                });
            });
        }
    });
</script>

<?php require_once '../includes/footer.php'; ?>