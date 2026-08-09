<?php
$page_title = 'Career Movements';
require_once '../includes/session-check.php';
checkRole(['HR Manager']);
require_once '../includes/functions.php';

$movement_ready  = ensureCareerProgressionMovements($conn);
if ($movement_ready) {
    applyDueCareerProgressionMovements($conn);
}
$current_user_id = (int) ($_SESSION['user_id'] ?? 0);

// ── POST: Approve / Reject ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['movement_action'])) {
    verifyCsrfToken();
    if (!$movement_ready) {
        redirectWith(BASE_URL . '/manager/career-movements.php', 'danger', 'Career Movements could not be initialized.');
    }
    $movement_id = (int) ($_POST['movement_id'] ?? 0);
    $action      = trim($_POST['movement_action'] ?? '');

    if ($movement_id <= 0 || !in_array($action, ['Approve', 'Reject'], true)) {
        redirectWith(BASE_URL . '/manager/career-movements.php', 'danger', 'Invalid career movement request.');
    }

    $stmt = $conn->prepare("
        SELECT cm.*, CONCAT(e.first_name,' ',e.last_name) AS employee_name
        FROM career_movements cm
        JOIN employees e ON cm.employee_id = e.employee_id
        WHERE cm.movement_id = ? AND cm.approval_status = 'Pending' LIMIT 1
    ");
    $stmt->bind_param("i", $movement_id);
    $stmt->execute();
    $movement = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$movement) {
        redirectWith(BASE_URL . '/manager/career-movements.php', 'danger', 'Career movement not found or already processed.');
    }

    $status   = $action === 'Approve' ? 'Approved' : 'Rejected';
    $comments = trim($_POST['manager_comments'] ?? '');

    $upd = $conn->prepare("UPDATE career_movements SET approval_status=?, approved_by=?, decision_date=NOW(), manager_comments=?, is_applied=0 WHERE movement_id=?");
    $upd->bind_param("sisi", $status, $current_user_id, $comments, $movement_id);
    $upd->execute();
    $upd->close();

    if ($status === 'Approved' && $movement['effective_date'] <= date('Y-m-d')) {
        executeCareerMovementApplication($conn, $movement, $movement_id);
    }

    if (!empty($movement['logged_by'])) {
        createNotification($conn, (int) $movement['logged_by'],
            "Career Movement {$status}",
            "The {$movement['movement_type']} for {$movement['employee_name']} has been {$status}.",
            BASE_URL . '/supervisor/career-movements.php');
    }
    $emp_user = getPreferredLinkedUserId($conn, $movement['employee_id'], 'employee_portal');
    if ($emp_user) {
        createNotification($conn, $emp_user,
            "Career Movement {$status}",
            "Your career movement ({$movement['movement_type']}) has been {$status}.",
            BASE_URL . '/employee/notifications.php');
    }

    logAudit($conn, $current_user_id, strtoupper($action), 'Career Movement', $movement_id,
        "{$action}d {$movement['movement_type']} for {$movement['employee_name']}.");

    $msg = $status === 'Approved' ? 'Career movement approved. It will apply on the effective date.' : 'Career movement rejected.';
    redirectWith(BASE_URL . '/manager/career-movements.php', $status === 'Approved' ? 'success' : 'warning', $msg);
}

// ── Fetch data ────────────────────────────────────────────────────────────────
require_once '../includes/header.php';

$movements = [];
$counts    = ['total' => 0, 'Pending' => 0, 'Approved' => 0, 'Rejected' => 0, 'Applied' => 0];

if ($movement_ready) {
    $stmt = $conn->prepare("
        SELECT cm.*,
            e.employee_code,
            e.job_title AS current_job_title,
            CONCAT(e.last_name, ', ', e.first_name) AS employee_name,
            d.department_name,
            pb.branch_name AS previous_branch_name,
            nb.branch_name AS new_branch_name,
            u1.full_name   AS logged_by_name,
            u2.full_name   AS approved_by_name
        FROM career_movements cm
        JOIN employees e       ON cm.employee_id        = e.employee_id
        LEFT JOIN departments d ON e.department_id      = d.department_id
        LEFT JOIN branches pb   ON cm.previous_branch_id = pb.branch_id
        LEFT JOIN branches nb   ON cm.new_branch_id      = nb.branch_id
        LEFT JOIN users u1      ON cm.logged_by          = u1.user_id
        LEFT JOIN users u2      ON cm.approved_by        = u2.user_id
        ORDER BY FIELD(cm.approval_status,'Pending','Approved','Rejected'), cm.created_at DESC
    ");
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $movements[] = $row;
        $counts['total']++;
        if (isset($counts[$row['approval_status']])) $counts[$row['approval_status']]++;
        if ((int) ($row['is_applied'] ?? 0) === 1) $counts['Applied']++;
    }
    $stmt->close();
}

$pending_movements  = array_filter($movements, fn($m) => $m['approval_status'] === 'Pending');
$decided_movements  = array_filter($movements, fn($m) => $m['approval_status'] !== 'Pending');

function mgrCmTypeClass($t)  { return match($t) { 'Promotion' => 'bg-success', 'Transfer' => 'bg-info text-dark', 'Demotion' => 'bg-danger', 'Role Change' => 'bg-primary', default => 'bg-secondary' }; }
function mgrCmStatusClass($s){ return match($s) { 'Approved' => 'bg-success', 'Rejected' => 'bg-danger', default => 'bg-warning text-dark' }; }
?>

<div class="page-hero fadeup">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
        <div>
            <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.55);">HR Manager &middot; Career</div>
            <h4 class="text-white fw-bold mb-0 mt-1"><i class="fas fa-route me-2" style="color:#BD9414;"></i>Career Movements</h4>
            <p class="text-white-50 small mb-0 mt-2">Review and decide on employee promotions, transfers, role changes, and other career progression requests.</p>
        </div>
        <a href="career-progression.php" class="btn btn-sm px-3 fw-semibold" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.25);border-radius:20px;font-size:.78rem;backdrop-filter:blur(4px);">
            <i class="fas fa-chart-line me-1"></i>Career Progression
        </a>
    </div>
    <div class="row g-3">
        <div class="col-6 col-md-3"><div class="stat-card"><div class="stat-value"><?php echo $counts['total']; ?></div><div class="stat-label">Total</div></div></div>
        <div class="col-6 col-md-3"><div class="stat-card"><div class="stat-value" style="<?php echo $counts['Pending'] > 0 ? 'color:#ffc107;' : ''; ?>"><?php echo $counts['Pending']; ?></div><div class="stat-label">Pending</div></div></div>
        <div class="col-6 col-md-3"><div class="stat-card"><div class="stat-value" style="color:#28a745;"><?php echo $counts['Approved']; ?></div><div class="stat-label">Approved</div></div></div>
        <div class="col-6 col-md-3"><div class="stat-card"><div class="stat-value"><?php echo $counts['Applied']; ?></div><div class="stat-label">Applied</div></div></div>
    </div>
</div>

<?php if (!$movement_ready): ?>
    <div class="alert alert-danger">Career Movements could not be initialized. Please check the database.</div>
<?php endif; ?>

<div class="chart-card fadeup">
    <div class="cc-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <ul class="nav nav-tabs cc-header-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#mgrAllTab" type="button" role="tab">
                    <i class="fas fa-list me-1"></i>All Movements
                    <span class="badge bg-secondary ms-1" style="font-size:.65rem;"><?php echo count($decided_movements); ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#mgrPendingTab" type="button" role="tab">
                    <i class="fas fa-clock me-1"></i>Pending Approval
                    <?php if ($counts['Pending'] > 0): ?>
                        <span class="badge bg-warning text-dark ms-1" style="font-size:.65rem;"><?php echo $counts['Pending']; ?></span>
                    <?php endif; ?>
                </button>
            </li>
        </ul>
        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="form-control form-control-sm" id="mgrMovSearch" placeholder="Search movements...">
        </div>
    </div>

    <div class="cc-body p-0">
        <div class="tab-content">

            <!-- ALL MOVEMENTS TAB -->
            <div class="tab-pane fade show active" id="mgrAllTab" role="tabpanel">
                <div class="table-responsive">
                    <table class="table modern-table align-middle mb-0" id="mgrMovTable">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Type</th>
                                <th>Position Change</th>
                                <th>Branch Change</th>
                                <th>Effective</th>
                                <th>Source</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($decided_movements)): ?>
                                <tr><td colspan="8" class="text-center py-5 text-muted"><i class="fas fa-route d-block mb-2" style="font-size:2rem;opacity:.2;"></i>No decided movements yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($decided_movements as $mv):
                                    $is_hr_staff_req = ($mv['request_source'] === 'HR Portal' && ($mv['initiated_by_role'] ?? '') === 'HR Staff');
                                ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?php echo e($mv['employee_name']); ?></div>
                                        <small class="text-muted"><?php echo e(getEmployeeDisplayId($mv)); ?> &middot; <?php echo e($mv['current_job_title']); ?></small>
                                        <?php if (!empty($mv['department_name'])): ?>
                                            <div><span class="badge bg-light text-dark border" style="font-size:.68rem;"><?php echo e($mv['department_name']); ?></span></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge <?php echo mgrCmTypeClass($mv['movement_type']); ?>"><?php echo e($mv['movement_type']); ?></span></td>
                                    <td>
                                        <div class="small text-muted"><?php echo e($mv['previous_position'] ?: $mv['current_job_title'] ?: '—'); ?></div>
                                        <div class="fw-semibold"><i class="fas fa-arrow-right text-success me-1" style="font-size:.75rem;"></i><?php echo e($mv['new_position']); ?></div>
                                    </td>
                                    <td>
                                        <?php if (!empty($mv['new_branch_id'])): ?>
                                            <div class="small text-muted"><?php echo e($mv['previous_branch_name'] ?: 'N/A'); ?></div>
                                            <div class="fw-semibold"><?php echo e($mv['new_branch_name'] ?: 'N/A'); ?></div>
                                        <?php else: ?>
                                            <span class="text-muted small">No branch change</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="small"><?php echo formatDate($mv['effective_date']); ?></span></td>
                                    <td>
                                        <?php if ($is_hr_staff_req): ?>
                                            <span class="badge bg-warning text-dark"><i class="fas fa-user-shield me-1"></i>HR Staff Requisition</span>
                                            <div class="small text-muted mt-1">Leaderless Branch</div>
                                        <?php elseif ($mv['request_source'] === 'Employee Portal'): ?>
                                            <span class="badge bg-info text-dark"><i class="fas fa-user-tie me-1"></i>Branch Head Requisition</span>
                                            <?php if (!empty($mv['initiated_by_name'])): ?>
                                                <div class="small text-muted mt-1">by <?php echo e($mv['initiated_by_name']); ?> (<?php echo e($mv['initiated_by_role'] ?? 'Immediate Head'); ?>)</div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><i class="fas fa-building me-1"></i>HR Portal</span>
                                            <div class="small text-muted mt-1">by <?php echo e($mv['logged_by_name'] ?: 'HRD'); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo mgrCmStatusClass($mv['approval_status']); ?>"><?php echo e($mv['approval_status']); ?></span>
                                        <?php if ($mv['approval_status'] === 'Approved' && (int) $mv['is_applied'] === 1): ?>
                                            <span class="badge bg-success ms-1">Applied</span>
                                        <?php elseif ($mv['approval_status'] === 'Approved'): ?>
                                            <span class="badge bg-secondary ms-1">Scheduled</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <span class="small text-muted"><?php echo e($mv['approved_by_name'] ?: 'Processed'); ?></span>
                                    </td>
                                </tr>
                                <?php if (!empty($mv['reason']) || !empty($mv['manager_comments']) || $mv['approval_status'] !== 'Pending'): ?>
                                <tr class="bg-light">
                                    <td colspan="8" class="small py-2">
                                        <div class="d-flex flex-wrap gap-3 align-items-start">
                                            <?php if (!empty($mv['reason'])): ?>
                                                <span><span class="fw-semibold text-muted">Reason:</span> <?php echo e($mv['reason']); ?></span>
                                            <?php endif; ?>
                                            <?php if ($mv['approval_status'] !== 'Pending'): ?>
                                                <span>
                                                    <span class="fw-semibold text-muted">Decision by:</span>
                                                    <?php echo e($mv['approved_by_name'] ?: 'HR Manager'); ?>
                                                    <?php if (!empty($mv['decision_date'])): ?>
                                                        &middot; <span class="text-muted"><?php echo formatDate($mv['decision_date'], 'M d, Y'); ?></span>
                                                    <?php endif; ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if (!empty($mv['manager_comments'])): ?>
                                                <span><span class="fw-semibold text-muted">Decision Notes:</span> <?php echo e($mv['manager_comments']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PENDING TAB -->
            <div class="tab-pane fade" id="mgrPendingTab" role="tabpanel">
                <div class="table-responsive">
                    <table class="table modern-table align-middle mb-0" id="mgrPendingTable">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Type</th>
                                <th>Position Change</th>
                                <th>Branch Change</th>
                                <th>Effective</th>
                                <th>Source</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pending_movements)): ?>
                                <tr><td colspan="7" class="text-center py-5 text-muted"><i class="fas fa-check-circle d-block mb-2" style="font-size:2rem;opacity:.2;color:#28a745;"></i>No pending movements — all caught up.</td></tr>
                            <?php else: ?>
                                <?php foreach ($pending_movements as $mv):
                                    $is_hr_staff_req = ($mv['request_source'] === 'HR Portal' && ($mv['initiated_by_role'] ?? '') === 'HR Staff');
                                ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?php echo e($mv['employee_name']); ?></div>
                                        <small class="text-muted"><?php echo e(getEmployeeDisplayId($mv)); ?> &middot; <?php echo e($mv['current_job_title']); ?></small>
                                        <?php if (!empty($mv['department_name'])): ?>
                                            <div><span class="badge bg-light text-dark border" style="font-size:.68rem;"><?php echo e($mv['department_name']); ?></span></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge <?php echo mgrCmTypeClass($mv['movement_type']); ?>"><?php echo e($mv['movement_type']); ?></span></td>
                                    <td>
                                        <div class="small text-muted"><?php echo e($mv['previous_position'] ?: $mv['current_job_title'] ?: '—'); ?></div>
                                        <div class="fw-semibold"><i class="fas fa-arrow-right text-success me-1" style="font-size:.75rem;"></i><?php echo e($mv['new_position']); ?></div>
                                    </td>
                                    <td>
                                        <?php if (!empty($mv['new_branch_id'])): ?>
                                            <div class="small text-muted"><?php echo e($mv['previous_branch_name'] ?: 'N/A'); ?></div>
                                            <div class="fw-semibold"><?php echo e($mv['new_branch_name'] ?: 'N/A'); ?></div>
                                        <?php else: ?>
                                            <span class="text-muted small">No branch change</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="small"><?php echo formatDate($mv['effective_date']); ?></span></td>
                                    <td>
                                        <?php if ($is_hr_staff_req): ?>
                                            <span class="badge bg-warning text-dark"><i class="fas fa-user-shield me-1"></i>HR Staff Requisition</span>
                                            <div class="small text-muted mt-1">Leaderless Branch</div>
                                        <?php elseif ($mv['request_source'] === 'Employee Portal'): ?>
                                            <span class="badge bg-info text-dark"><i class="fas fa-user-tie me-1"></i>Branch Head Requisition</span>
                                            <?php if (!empty($mv['initiated_by_name'])): ?>
                                                <div class="small text-muted mt-1">by <?php echo e($mv['initiated_by_name']); ?> (<?php echo e($mv['initiated_by_role'] ?? 'Immediate Head'); ?>)</div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><i class="fas fa-building me-1"></i>HR Portal</span>
                                            <div class="small text-muted mt-1">by <?php echo e($mv['logged_by_name'] ?: 'HRD'); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <form method="POST" class="d-inline">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="movement_id" value="<?php echo (int) $mv['movement_id']; ?>">
                                            <input type="hidden" name="movement_action" value="Approve">
                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve this career movement?');"><i class="fas fa-check me-1"></i>Approve</button>
                                        </form>
                                        <button class="btn btn-sm btn-outline-danger ms-1"
                                            data-bs-toggle="modal" data-bs-target="#mgrRejectModal"
                                            data-mvid="<?php echo (int) $mv['movement_id']; ?>"
                                            data-empname="<?php echo e($mv['employee_name']); ?>">
                                            <i class="fas fa-times me-1"></i>Reject
                                        </button>
                                    </td>
                                </tr>
                                <?php if (!empty($mv['reason'])): ?>
                                <tr class="bg-light">
                                    <td colspan="7" class="small text-muted py-2">
                                        <span class="fw-semibold">Reason:</span> <?php echo e($mv['reason']); ?>
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
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="mgrRejectModal" tabindex="-1" aria-labelledby="mgrRejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mgrRejectModalLabel"><i class="fas fa-times-circle text-danger me-2"></i>Reject Career Movement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <?php echo csrfField(); ?>
                <input type="hidden" name="movement_id" id="mgrRejectMvId">
                <input type="hidden" name="movement_action" value="Reject">
                <div class="modal-body">
                    <p class="mb-2">Rejecting movement for: <strong id="mgrRejectEmpName"></strong></p>
                    <label class="form-label fw-semibold">Reason for Rejection <span class="text-muted small">(optional)</span></label>
                    <textarea class="form-control" name="manager_comments" rows="3" placeholder="Provide a brief reason..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-times me-1"></i>Confirm Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Search filters both tables simultaneously
    const searchInput   = document.getElementById('mgrMovSearch');
    const allRows       = document.querySelectorAll('#mgrMovTable tbody tr');
    const pendingRows   = document.querySelectorAll('#mgrPendingTable tbody tr');

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const term = this.value.trim().toLowerCase();
            [allRows, pendingRows].forEach(rows => {
                rows.forEach(r => { r.style.display = r.textContent.toLowerCase().includes(term) ? '' : 'none'; });
            });
            if (typeof applyZebraStriping === 'function') {
                applyZebraStriping('#mgrMovTable');
                applyZebraStriping('#mgrPendingTable');
            }
        });
    }

    // Reject modal
    const rejectModal = document.getElementById('mgrRejectModal');
    if (rejectModal) {
        rejectModal.addEventListener('show.bs.modal', function (e) {
            const btn = e.relatedTarget;
            document.getElementById('mgrRejectMvId').value          = btn.dataset.mvid;
            document.getElementById('mgrRejectEmpName').textContent = btn.dataset.empname;
        });
    }

    // Auto-open Pending tab if there are pending items and URL says so
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('tab') === 'pending') {
        const pendingTabBtn = document.querySelector('[data-bs-target="#mgrPendingTab"]');
        if (pendingTabBtn) bootstrap.Tab.getOrCreateInstance(pendingTabBtn).show();
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
