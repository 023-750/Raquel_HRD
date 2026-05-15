<?php
$page_title = 'Career Movements';
require_once '../includes/session-check.php';
checkRole(['HR Manager']);
require_once '../includes/functions.php';

$movement_ready = ensureCareerProgressionMovements($conn);
if ($movement_ready) {
    applyDueCareerProgressionMovements($conn);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['movement_action'])) {
    if (!$movement_ready) {
        redirectWith(BASE_URL . '/manager/career-movements.php', 'danger', 'Career Movements could not be initialized.');
    }

    $movement_id = (int) ($_POST['movement_id'] ?? 0);
    $action = trim($_POST['movement_action'] ?? '');
    $comments = trim($_POST['manager_comments'] ?? '');

    if ($movement_id <= 0 || !in_array($action, ['Approve', 'Reject'], true)) {
        redirectWith(BASE_URL . '/manager/career-movements.php', 'danger', 'Invalid career movement request.');
    }

    $stmt = $conn->prepare("
        SELECT cm.*, CONCAT(e.first_name, ' ', e.last_name) AS employee_name
        FROM career_movements cm
        JOIN employees e ON cm.employee_id = e.employee_id
        WHERE cm.movement_id = ? AND cm.approval_status = 'Pending'
        LIMIT 1
    ");
    $stmt->bind_param("i", $movement_id);
    $stmt->execute();
    $movement = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$movement) {
        redirectWith(BASE_URL . '/manager/career-movements.php', 'danger', 'Career movement not found or already processed.');
    }

    $status = $action === 'Approve' ? 'Approved' : 'Rejected';
    $manager_id = (int) $_SESSION['user_id'];
    $update = $conn->prepare("
        UPDATE career_movements
        SET approval_status = ?, approved_by = ?, decision_date = NOW(), manager_comments = ?, is_applied = 0
        WHERE movement_id = ?
    ");
    $update->bind_param("sisi", $status, $manager_id, $comments, $movement_id);
    $updated = $update->execute();
    $update->close();

    if (!$updated) {
        redirectWith(BASE_URL . '/manager/career-movements.php', 'danger', 'Unable to process the career movement.');
    }

    if ($status === 'Approved' && $movement['effective_date'] <= date('Y-m-d')) {
        $employee_id = (int) $movement['employee_id'];
        if (!empty($movement['new_branch_id'])) {
            $new_branch_id = (int) $movement['new_branch_id'];
            $emp_update = $conn->prepare("UPDATE employees SET job_title = ?, branch_id = ? WHERE employee_id = ?");
            $emp_update->bind_param("sii", $movement['new_position'], $new_branch_id, $employee_id);
        } else {
            $emp_update = $conn->prepare("UPDATE employees SET job_title = ? WHERE employee_id = ?");
            $emp_update->bind_param("si", $movement['new_position'], $employee_id);
        }
        $emp_update->execute();
        $emp_update->close();

        $mark = $conn->prepare("UPDATE career_movements SET is_applied = 1 WHERE movement_id = ?");
        $mark->bind_param("i", $movement_id);
        $mark->execute();
        $mark->close();
    }

    logAudit(
        $conn,
        $_SESSION['user_id'],
        strtoupper($action),
        'Career Movement',
        $movement_id,
        "{$action}d {$movement['movement_type']} for {$movement['employee_name']}."
    );

    if (!empty($movement['logged_by'])) {
        createNotification(
            $conn,
            (int) $movement['logged_by'],
            "Career Movement {$status}",
            "The {$movement['movement_type']} for {$movement['employee_name']} has been {$status}.",
            BASE_URL . '/supervisor/career-movements.php'
        );
    }

    $employee_user_id = getPreferredLinkedUserId($conn, $movement['employee_id'], 'employee_portal');
    if ($employee_user_id) {
        createNotification(
            $conn,
            $employee_user_id,
            "Career Movement {$status}",
            "Your career movement ({$movement['movement_type']}) has been {$status}.",
            BASE_URL . '/employee/notifications.php'
        );
    }

    $message = $status === 'Approved'
        ? 'Career movement approved. It will apply on the effective date.'
        : 'Career movement rejected.';
    redirectWith(BASE_URL . '/manager/career-movements.php', $status === 'Approved' ? 'success' : 'warning', $message);

}

require_once '../includes/header.php';

$movements = [];
$counts = ['Pending' => 0, 'Approved' => 0, 'Rejected' => 0, 'Applied' => 0];

if ($movement_ready) {
    $result = $conn->query("
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
        ORDER BY cm.created_at DESC
    ");

    while ($row = $result->fetch_assoc()) {
        $movements[] = $row;
        if (isset($counts[$row['approval_status']])) {
            $counts[$row['approval_status']]++;
        }
        if ((int) ($row['is_applied'] ?? 0) === 1) {
            $counts['Applied']++;
        }
    }
}

function careerProgressionMovementTypeClass($type)
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

function careerProgressionStatusClass($status)
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
                Manager &middot; Career</div>
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
                <div class="stat-value"><?php echo $counts['Pending']; ?></div>
                <div class="stat-label">Pending Movements</div>
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
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-value"><?php echo $counts['Rejected']; ?></div>
                <div class="stat-label">Rejected</div>
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
        <span class="badge bg-light text-dark border px-3 py-2">Manager Review</span>
    </div>
</div>

<?php if (!$movement_ready): ?>
    <div class="alert alert-danger">Career Movements could not be initialized. Please check the database connection and
        table permissions.</div>
<?php endif; ?>

<div class="chart-card fadeup">
    <div class="cc-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <h5 class="mb-0"><i class="fas fa-route me-2"></i>Career Movement Requests</h5>
        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="form-control form-control-sm" id="movementSearch"
                placeholder="Search movements...">
        </div>
    </div>
    <div class="cc-body p-0">
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
                        <th>Logged By</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($movements)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
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
                                        class="badge <?php echo careerProgressionMovementTypeClass($movement['movement_type']); ?>"><?php echo e($movement['movement_type']); ?></span>
                                </td>
                                <td>
                                    <div class="small text-muted"><?php echo e($movement['previous_position'] ?: 'N/A'); ?>
                                    </div>
                                    <div class="fw-semibold"><?php echo e($movement['new_position']); ?></div>
                                </td>
                                <td>
                                    <?php if (!empty($movement['new_branch_id'])): ?>
                                        <div class="small text-muted"><?php echo e($movement['previous_branch_name'] ?: 'N/A'); ?>
                                        </div>
                                        <div class="fw-semibold"><?php echo e($movement['new_branch_name'] ?: 'N/A'); ?></div>
                                    <?php else: ?>
                                        <span class="text-muted small">No branch change</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="small"><?php echo formatDate($movement['effective_date']); ?></span></td>
                                <td>
                                    <?php if ($movement['request_source'] === 'Employee Portal'): ?>
                                        <span class="badge bg-info text-dark">Employee Portal</span>
                                        <?php if (!empty($movement['initiated_by_name'])): ?>
                                            <div class="small text-muted mt-1">by <?php echo e($movement['initiated_by_name']); ?>
                                                (<?php echo e($movement['initiated_by_role'] ?? 'Area Supervisor'); ?>)</div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">HR Portal</span>
                                        <div class="small text-muted mt-1">by
                                            <?php echo e($movement['logged_by_name'] ?: 'HR Supervisor'); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span
                                        class="badge <?php echo careerProgressionStatusClass($movement['approval_status']); ?>"><?php echo e($movement['approval_status']); ?></span>
                                    <?php if ($movement['approval_status'] === 'Approved' && (int) $movement['is_applied'] === 1): ?>
                                        <span class="badge bg-success">Applied</span>
                                    <?php elseif ($movement['approval_status'] === 'Approved'): ?>
                                        <span class="badge bg-secondary">Scheduled</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="small text-muted"><?php echo e($movement['logged_by_name'] ?: 'N/A'); ?></span>
                                </td>
                                <td class="text-end">
                                    <?php if ($movement['approval_status'] === 'Pending'): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="movement_id"
                                                value="<?php echo (int) $movement['movement_id']; ?>">
                                            <input type="hidden" name="movement_action" value="Approve">
                                            <button type="submit" class="btn btn-sm btn-success"
                                                onclick="return confirm('Approve this career movement?');">
                                                <i class="fas fa-check me-1"></i>Approve
                                            </button>
                                        </form>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="movement_id"
                                                value="<?php echo (int) $movement['movement_id']; ?>">
                                            <input type="hidden" name="movement_action" value="Reject">
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Reject this career movement?');">
                                                <i class="fas fa-times me-1"></i>Reject
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span
                                            class="small text-muted"><?php echo e($movement['approved_by_name'] ?: 'Processed'); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php if (!empty($movement['reason']) || !empty($movement['manager_comments'])): ?>
                                <tr class="bg-light">
                                    <td colspan="9" class="small text-muted">
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
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('movementSearch');
        const rows = document.querySelectorAll('#movementTable tbody tr');

        if (!searchInput) return;

        searchInput.addEventListener('input', function () {
            const term = this.value.trim().toLowerCase();
            rows.forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
            });
        });
    });
</script>

<?php require_once '../includes/footer.php'; ?>