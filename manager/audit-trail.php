<?php
$page_title = 'Audit Trail';
require_once '../includes/session-check.php';
checkRole(['HR Manager']);
require_once '../includes/functions.php';

// ── Filters ────────────────────────────────────────────────
$action_filter  = $_GET['action']   ?? 'all';
$date_from      = $_GET['date_from'] ?? '';
$date_to        = $_GET['date_to']   ?? '';

$allowed_actions = ['all', 'CREATE', 'UPDATE', 'DELETE', 'LOGIN', 'VIEW'];
if (!in_array($action_filter, $allowed_actions)) {
    $action_filter = 'all';
}

// ── Build WHERE clause ──────────────────────────────────────
// HR Manager sees all HR-side roles
$hr_roles = ['HR Manager', 'HR Supervisor', 'HR Staff'];
$role_placeholders = implode(',', array_fill(0, count($hr_roles), '?'));

$conditions = ["u.role IN ($role_placeholders)"];
$params     = $hr_roles;
$types      = str_repeat('s', count($hr_roles));

if ($action_filter !== 'all') {
    $conditions[] = 'al.action_type = ?';
    $params[]     = $action_filter;
    $types       .= 's';
}

if ($date_from !== '') {
    $conditions[] = 'DATE(al.timestamp) >= ?';
    $params[]     = $date_from;
    $types       .= 's';
}

if ($date_to !== '') {
    $conditions[] = 'DATE(al.timestamp) <= ?';
    $params[]     = $date_to;
    $types       .= 's';
}

$where_sql = 'WHERE ' . implode(' AND ', $conditions);

$sql = "
    SELECT al.*, u.full_name, u.role
    FROM audit_logs al
    LEFT JOIN users u ON al.user_id = u.user_id
    $where_sql
    ORDER BY al.timestamp DESC
    LIMIT 200
";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$audit_logs = $stmt->get_result();
$stmt->close();

// ── Counts for stat cards ────────────────────────────────────
$total_logs   = (int) $conn->query("SELECT COUNT(*) FROM audit_logs al LEFT JOIN users u ON al.user_id = u.user_id WHERE u.role IN ('HR Manager','HR Supervisor','HR Staff')")->fetch_row()[0];
$today_logs   = (int) $conn->query("SELECT COUNT(*) FROM audit_logs al LEFT JOIN users u ON al.user_id = u.user_id WHERE u.role IN ('HR Manager','HR Supervisor','HR Staff') AND DATE(al.timestamp) = CURDATE()")->fetch_row()[0];
$create_count = (int) $conn->query("SELECT COUNT(*) FROM audit_logs al LEFT JOIN users u ON al.user_id = u.user_id WHERE u.role IN ('HR Manager','HR Supervisor','HR Staff') AND al.action_type = 'CREATE'")->fetch_row()[0];
$delete_count = (int) $conn->query("SELECT COUNT(*) FROM audit_logs al LEFT JOIN users u ON al.user_id = u.user_id WHERE u.role IN ('HR Manager','HR Supervisor','HR Staff') AND al.action_type = 'DELETE'")->fetch_row()[0];

require_once '../includes/header.php';
?>

<style>
    .audit-action-btns .btn { min-width: 90px; }
    .student-list { display: flex; flex-direction: column; gap: 12px; }
    .student-item { background: #fff; border-radius: 12px; padding: 15px; display: flex; align-items: flex-start; gap: 12px; border: 1px solid #eee; box-shadow: 0 2px 8px rgba(0,0,0,.03); }
    .student-name { font-weight: 700; color: var(--primary-blue); margin-bottom: 2px; }
    .student-meta { font-size: .85rem; color: #666; }
    .date-filter-group { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
    .date-filter-group label { font-size: .8rem; color: var(--text-muted); white-space: nowrap; margin: 0; }
</style>

<div class="page-hero fadeup">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-3">
        <div>
            <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.55);">HR Manager · Activity Monitor</div>
            <h4 class="text-white fw-bold mb-0 mt-1"><i class="fas fa-clipboard-list me-2" style="color:var(--primary-light);"></i>Audit Trail</h4>
        </div>
        <div style="color:rgba(255,255,255,.6);font-size:.8rem;">
            <i class="fas fa-users me-1"></i> HR Manager · HR Supervisor · HR Staff &nbsp;·&nbsp; Latest 200 records
        </div>
    </div>
    <p class="text-white-50 small mb-0"><i class="fas fa-search me-1"></i>Review all HR-side actions: employee updates, approvals, logins, and more.</p>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4 fadeup-1">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-clipboard-list"></i></div>
            <div class="stat-info">
                <h3><?php echo number_format($total_logs); ?></h3>
                <p>Total HR Logs</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon gold"><i class="fas fa-calendar-day"></i></div>
            <div class="stat-info">
                <h3><?php echo number_format($today_logs); ?></h3>
                <p>Today's Actions</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(40,167,69,.1);color:var(--success);"><i class="fas fa-plus-circle"></i></div>
            <div class="stat-info">
                <h3><?php echo number_format($create_count); ?></h3>
                <p>Create Actions</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon red"><i class="fas fa-trash-alt"></i></div>
            <div class="stat-info">
                <h3><?php echo number_format($delete_count); ?></h3>
                <p>Delete Actions</p>
            </div>
        </div>
    </div>
</div>

<div class="content-card fadeup-2">
    <div class="card-header flex-wrap gap-2">
        <h5><i class="fas fa-clipboard-list me-2"></i>Activity Logs</h5>
        <div class="d-flex align-items-center gap-2 flex-wrap ms-auto">

            <!-- Action Type Filter -->
            <form method="GET" id="auditFilterForm" class="mb-0 d-flex align-items-center gap-2 flex-wrap">
                <select class="form-select form-select-sm" name="action" onchange="document.getElementById('auditFilterForm').submit()" style="width:150px;" aria-label="Filter by action type">
                    <option value="all"   <?php echo $action_filter === 'all'    ? 'selected' : ''; ?>>All Actions</option>
                    <option value="CREATE" <?php echo $action_filter === 'CREATE' ? 'selected' : ''; ?>>CREATE</option>
                    <option value="UPDATE" <?php echo $action_filter === 'UPDATE' ? 'selected' : ''; ?>>UPDATE</option>
                    <option value="DELETE" <?php echo $action_filter === 'DELETE' ? 'selected' : ''; ?>>DELETE</option>
                    <option value="LOGIN"  <?php echo $action_filter === 'LOGIN'  ? 'selected' : ''; ?>>LOGIN</option>
                    <option value="VIEW"   <?php echo $action_filter === 'VIEW'   ? 'selected' : ''; ?>>VIEW</option>
                </select>

                <!-- Date Range -->
                <div class="date-filter-group">
                    <label for="date_from">From</label>
                    <input type="date" id="date_from" name="date_from" class="form-control form-control-sm" value="<?php echo htmlspecialchars($date_from); ?>" style="width:145px;">
                    <label for="date_to">To</label>
                    <input type="date" id="date_to" name="date_to" class="form-control form-control-sm" value="<?php echo htmlspecialchars($date_to); ?>" style="width:145px;">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i>Filter</button>
                    <?php if ($action_filter !== 'all' || $date_from !== '' || $date_to !== ''): ?>
                        <a href="audit-trail.php" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times me-1"></i>Clear</a>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Search -->
            <div class="search-box" style="min-width:200px;flex:1 1 200px;max-width:280px;">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="form-control form-control-sm" id="searchAudit" placeholder="Search logs..." onkeyup="filterTable('searchAudit','auditTable')">
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        <!-- Desktop Table -->
        <div class="table-responsive d-none d-md-block">
            <table class="table table-hover" id="auditTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Timestamp</th>
                        <th>User</th>
                        <th>Role</th>
                        <th>Action</th>
                        <th>Entity</th>
                        <th>Details</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($audit_logs->num_rows === 0): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-inbox me-2"></i>No audit logs found for the selected filters.</td></tr>
                    <?php else: ?>
                        <?php while ($log = $audit_logs->fetch_assoc()):
                            $badge = 'bg-secondary';
                            switch ($log['action_type']) {
                                case 'CREATE': $badge = 'bg-success'; break;
                                case 'UPDATE': $badge = 'bg-info';    break;
                                case 'DELETE': $badge = 'bg-danger';  break;
                                case 'LOGIN':  $badge = 'bg-primary'; break;
                                case 'VIEW':   $badge = 'bg-warning text-dark'; break;
                            }
                        ?>
                        <tr>
                            <td><small class="text-muted"><?php echo $log['log_id']; ?></small></td>
                            <td><small><?php echo formatDateTime($log['timestamp']); ?></small></td>
                            <td><strong><?php echo e($log['full_name'] ?? 'System'); ?></strong></td>
                            <td><span class="badge bg-secondary"><?php echo e($log['role'] ?? '—'); ?></span></td>
                            <td><span class="badge <?php echo $badge; ?>"><?php echo e($log['action_type']); ?></span></td>
                            <td><?php echo e($log['entity_type'] ?? '—'); ?></td>
                            <td><small><?php echo e($log['details'] ?? '—'); ?></small></td>
                            <td><small class="text-muted"><?php echo e($log['ip_address'] ?? '—'); ?></small></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="mobile-list-view d-block d-md-none p-3">
            <div class="student-list">
                <?php if ($audit_logs->num_rows === 0): ?>
                    <div class="text-center py-4 text-muted"><i class="fas fa-inbox me-2"></i>No audit logs found.</div>
                <?php else: ?>
                    <?php
                    $audit_logs->data_seek(0);
                    while ($log = $audit_logs->fetch_assoc()):
                        $avatar_class = 'bg-secondary';
                        $icon         = 'fa-info-circle';
                        $badge        = 'bg-secondary';
                        switch ($log['action_type']) {
                            case 'CREATE': $avatar_class = 'bg-success'; $icon = 'fa-plus';         $badge = 'bg-success'; break;
                            case 'UPDATE': $avatar_class = 'bg-info';    $icon = 'fa-edit';         $badge = 'bg-info';    break;
                            case 'DELETE': $avatar_class = 'bg-danger';  $icon = 'fa-trash';        $badge = 'bg-danger';  break;
                            case 'LOGIN':  $avatar_class = 'bg-primary'; $icon = 'fa-sign-in-alt';  $badge = 'bg-primary'; break;
                            case 'VIEW':   $avatar_class = 'bg-warning'; $icon = 'fa-eye';          $badge = 'bg-warning text-dark'; break;
                        }
                    ?>
                    <div class="student-item">
                        <div class="student-avatar">
                            <div class="avatar-placeholder d-flex align-items-center justify-content-center <?php echo $avatar_class; ?>" style="width:42px;height:42px;border-radius:12px;font-size:15px;color:#fff;">
                                <i class="fas <?php echo $icon; ?>"></i>
                            </div>
                        </div>
                        <div class="student-info" style="flex:1;min-width:0;">
                            <div class="student-name" style="font-size:.9rem;margin-bottom:2px;">
                                <span class="badge <?php echo $badge; ?>"><?php echo e($log['action_type']); ?></span>
                                <strong class="ms-1"><?php echo e($log['entity_type'] ?? '—'); ?></strong>
                                <span class="text-muted" style="font-size:.72rem;">#<?php echo $log['log_id']; ?></span>
                            </div>
                            <div class="student-meta" style="color:var(--text-dark);font-size:.82rem;margin-bottom:4px;line-height:1.3;"><?php echo e($log['details'] ?? '—'); ?></div>
                            <div class="student-meta">By: <strong><?php echo e($log['full_name'] ?? 'System'); ?></strong> · <span class="badge bg-secondary" style="font-size:.7rem;"><?php echo e($log['role'] ?? '—'); ?></span></div>
                            <div class="student-meta" style="font-size:.74rem;margin-top:2px;color:var(--text-muted);">
                                <i class="far fa-clock me-1"></i><?php echo formatDateTime($log['timestamp']); ?>
                                &bull; <i class="fas fa-desktop me-1"></i><?php echo e($log['ip_address'] ?? '—'); ?>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof applyZebraStriping === 'function') {
        applyZebraStriping('#auditTable');
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
