<?php
$page_title = 'Career Movements';
require_once '../includes/session-check.php';
checkRole(['HR Manager']);
require_once '../includes/functions.php';

$movement_ready = ensureCareerProgressionMovements($conn);
if ($movement_ready) {
    applyDueCareerProgressionMovements($conn);
}

$current_user_id = (int)($_SESSION['user_id'] ?? 0);

// ── POST: Approve / Reject (HR Manager has full access — no self-approval restriction) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['movement_action'])) {
    if (!$movement_ready) {
        redirectWith(BASE_URL . '/manager/career-movements.php', 'danger', 'Career Movements could not be initialized.');
    }
    $movement_id = (int)($_POST['movement_id'] ?? 0);
    $action      = trim($_POST['movement_action'] ?? '');

    if ($movement_id <= 0 || !in_array($action, ['Approve','Reject'], true)) {
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
    $upd->execute(); $upd->close();

    // Apply immediately if effective date has passed
    if ($status === 'Approved' && $movement['effective_date'] <= date('Y-m-d')) {
        $eid = (int)$movement['employee_id'];
        if (!empty($movement['new_branch_id'])) {
            $nbid = (int)$movement['new_branch_id'];
            $eu = $conn->prepare("UPDATE employees SET job_title=?, branch_id=? WHERE employee_id=?");
            $eu->bind_param("sii", $movement['new_position'], $nbid, $eid);
        } else {
            $eu = $conn->prepare("UPDATE employees SET job_title=? WHERE employee_id=?");
            $eu->bind_param("si", $movement['new_position'], $eid);
        }
        $eu->execute(); $eu->close();
        $mk = $conn->prepare("UPDATE career_movements SET is_applied=1 WHERE movement_id=?");
        $mk->bind_param("i", $movement_id); $mk->execute(); $mk->close();
    }

    // Notify the original requester
    if (!empty($movement['logged_by'])) {
        createNotification($conn, (int)$movement['logged_by'],
            "Career Movement {$status}",
            "The {$movement['movement_type']} for {$movement['employee_name']} has been {$status}.",
            BASE_URL . '/supervisor/career-movements.php');
    }
    // Notify employee portal user
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

// ── Fetch display data ────────────────────────────────────────────────────────
require_once '../includes/header.php';

$movements = [];
$counts    = ['Pending'=>0,'Approved'=>0,'Rejected'=>0,'Applied'=>0];

if ($movement_ready) {
    $result = $conn->query("
        SELECT cm.*,
            e.employee_code,
            e.job_title AS current_job_title,
            CONCAT(e.last_name, ', ', e.first_name) AS employee_name,
            pb.branch_name AS previous_branch_name,
            nb.branch_name AS new_branch_name,
            u1.full_name   AS logged_by_name,
            u2.full_name   AS approved_by_name
        FROM career_movements cm
        JOIN employees  e  ON cm.employee_id       = e.employee_id
        LEFT JOIN branches pb ON cm.previous_branch_id = pb.branch_id
        LEFT JOIN branches nb ON cm.new_branch_id       = nb.branch_id
        LEFT JOIN users   u1 ON cm.logged_by            = u1.user_id
        LEFT JOIN users   u2 ON cm.approved_by          = u2.user_id
        ORDER BY cm.created_at DESC
    ");
    while ($row = $result->fetch_assoc()) {
        $movements[] = $row;
        if (isset($counts[$row['approval_status']])) $counts[$row['approval_status']]++;
        if ((int)($row['is_applied']??0)===1) $counts['Applied']++;
    }
}

function mgrCmTypeClass($t){return match($t){'Promotion'=>'bg-success','Transfer'=>'bg-info text-dark','Demotion'=>'bg-danger','Role Change'=>'bg-primary',default=>'bg-secondary'};}
function mgrCmStatusClass($s){return match($s){'Approved'=>'bg-success','Rejected'=>'bg-danger',default=>'bg-warning text-dark'};}
?>

<div class="page-hero fadeup">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
        <div>
            <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.55);">HR Manager &middot; Career</div>
            <h4 class="text-white fw-bold mb-0 mt-1"><i class="fas fa-route me-2" style="color:#BD9414;"></i>Career Movements</h4>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-6 col-md-3"><div class="stat-card"><div class="stat-value"><?php echo $counts['Pending']; ?></div><div class="stat-label">Pending</div></div></div>
        <div class="col-6 col-md-3"><div class="stat-card"><div class="stat-value"><?php echo $counts['Approved']; ?></div><div class="stat-label">Approved</div></div></div>
        <div class="col-6 col-md-3"><div class="stat-card"><div class="stat-value"><?php echo $counts['Applied']; ?></div><div class="stat-label">Applied</div></div></div>
        <div class="col-6 col-md-3"><div class="stat-card"><div class="stat-value"><?php echo $counts['Rejected']; ?></div><div class="stat-label">Rejected</div></div></div>
    </div>
</div>

<?php if (!$movement_ready): ?>
    <div class="alert alert-danger">Career Movements could not be initialized. Please check the database.</div>
<?php endif; ?>

<div class="chart-card fadeup">
    <div class="cc-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <h5 class="mb-0"><i class="fas fa-route me-2"></i>Career Movement Requests</h5>
        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="form-control form-control-sm" id="mgrMovSearch" placeholder="Search movements...">
        </div>
    </div>
    <div class="cc-body p-0">
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
                    <?php if (empty($movements)): ?>
                        <tr><td colspan="8" class="text-center py-5 text-muted"><i class="fas fa-route d-block mb-2" style="font-size:2rem;opacity:.2;"></i>No career movements have been submitted yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($movements as $mv):
                            $is_pending = $mv['approval_status'] === 'Pending';
                            $is_hr_staff_req = ($mv['request_source']==='HR Portal' && ($mv['initiated_by_role']??'')==='HR Staff');
                        ?>
                        <tr>
                            <td>
                                <div class="fw-bold"><?php echo e($mv['employee_name']); ?></div>
                                <small class="text-muted"><?php echo e(getEmployeeDisplayId($mv)); ?> &middot; <?php echo e($mv['current_job_title']); ?></small>
                            </td>
                            <td><span class="badge <?php echo mgrCmTypeClass($mv['movement_type']); ?>"><?php echo e($mv['movement_type']); ?></span></td>
                            <td>
                                <div class="small text-muted"><?php echo e($mv['previous_position']?:'N/A'); ?></div>
                                <div class="fw-semibold"><?php echo e($mv['new_position']); ?></div>
                            </td>
                            <td>
                                <?php if (!empty($mv['new_branch_id'])): ?>
                                    <div class="small text-muted"><?php echo e($mv['previous_branch_name']?:'N/A'); ?></div>
                                    <div class="fw-semibold"><?php echo e($mv['new_branch_name']?:'N/A'); ?></div>
                                <?php else: ?>
                                    <span class="text-muted small">No branch change</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="small"><?php echo formatDate($mv['effective_date']); ?></span></td>
                            <td>
                                <?php if ($is_hr_staff_req): ?>
                                    <span class="badge bg-warning text-dark"><i class="fas fa-user-shield me-1"></i>HR Staff Requisition</span>
                                    <div class="small text-muted mt-1">Leaderless Branch</div>
                                <?php elseif ($mv['request_source']==='Employee Portal'): ?>
                                    <span class="badge bg-info text-dark"><i class="fas fa-user-tie me-1"></i>Branch Head Requisition</span>
                                    <?php if (!empty($mv['initiated_by_name'])): ?>
                                        <div class="small text-muted mt-1">by <?php echo e($mv['initiated_by_name']); ?> (<?php echo e($mv['initiated_by_role']??'Immediate Head'); ?>)</div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><i class="fas fa-building me-1"></i>HR Portal</span>
                                    <div class="small text-muted mt-1">by <?php echo e($mv['logged_by_name']?:'HRD'); ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?php echo mgrCmStatusClass($mv['approval_status']); ?>"><?php echo e($mv['approval_status']); ?></span>
                                <?php if ($mv['approval_status']==='Approved' && (int)$mv['is_applied']===1): ?>
                                    <span class="badge bg-success ms-1">Applied</span>
                                <?php elseif ($mv['approval_status']==='Approved'): ?>
                                    <span class="badge bg-secondary ms-1">Scheduled</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if ($is_pending): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="movement_id" value="<?php echo (int)$mv['movement_id']; ?>">
                                        <input type="hidden" name="movement_action" value="Approve">
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve this career movement?');"><i class="fas fa-check me-1"></i>Approve</button>
                                    </form>
                                    <button class="btn btn-sm btn-outline-danger ms-1"
                                        data-bs-toggle="modal" data-bs-target="#mgrRejectModal"
                                        data-mvid="<?php echo (int)$mv['movement_id']; ?>"
                                        data-empname="<?php echo e($mv['employee_name']); ?>">
                                        <i class="fas fa-times me-1"></i>Reject
                                    </button>
                                <?php else: ?>
                                    <span class="small text-muted"><?php echo e($mv['approved_by_name']?:'Processed'); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php if (!empty($mv['reason'])||!empty($mv['manager_comments'])): ?>
                        <tr class="bg-light">
                            <td colspan="8" class="small text-muted py-2">
                                <?php if (!empty($mv['reason'])): ?><span class="fw-semibold">Reason:</span> <?php echo e($mv['reason']); ?><?php endif; ?>
                                <?php if (!empty($mv['manager_comments'])): ?><span class="ms-3 fw-semibold">Decision Notes:</span> <?php echo e($mv['manager_comments']); ?><?php endif; ?>
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

<!-- Reject Modal -->
<div class="modal fade" id="mgrRejectModal" tabindex="-1" aria-labelledby="mgrRejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mgrRejectModalLabel"><i class="fas fa-times-circle text-danger me-2"></i>Reject Career Movement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
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
    const searchInput = document.getElementById('mgrMovSearch');
    const tableRows   = document.querySelectorAll('#mgrMovTable tbody tr');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const term = this.value.trim().toLowerCase();
            tableRows.forEach(r => { r.style.display = r.textContent.toLowerCase().includes(term) ? '' : 'none'; });
            if (typeof applyZebraStriping === 'function') applyZebraStriping('#mgrMovTable');
        });
    }

    const rejectModal = document.getElementById('mgrRejectModal');
    if (rejectModal) {
        rejectModal.addEventListener('show.bs.modal', function (e) {
            const btn = e.relatedTarget;
            document.getElementById('mgrRejectMvId').value          = btn.dataset.mvid;
            document.getElementById('mgrRejectEmpName').textContent = btn.dataset.empname;
        });
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
