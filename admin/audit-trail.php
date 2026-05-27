<?php
$page_title = 'Audit Trail';
require_once '../includes/session-check.php';
checkRole(['Admin']);
require_once '../includes/header.php';

$role_filters = [
    'all' => [
        'label' => 'All Roles',
        'roles' => [],
        'icon' => 'fas fa-layer-group',
    ],
    'staff' => [
        'label' => 'Staff',
        'roles' => ['HR Staff', 'Staff'],
        'icon' => 'fas fa-user-edit',
    ],
    'supervisor' => [
        'label' => 'Supervisor',
        'roles' => ['HR Supervisor', 'Supervisor'],
        'icon' => 'fas fa-user-check',
    ],
    'manager' => [
        'label' => 'HR Manager',
        'roles' => ['HR Manager', 'Manager'],
        'icon' => 'fas fa-user-tie',
    ],
    'admin' => [
        'label' => 'Admin',
        'roles' => ['Admin'],
        'icon' => 'fas fa-user-shield',
    ],
];

$selected_role = $_GET['role'] ?? 'all';
if (!array_key_exists($selected_role, $role_filters)) {
    $selected_role = 'all';
}

$where = '';
if (!empty($role_filters[$selected_role]['roles'])) {
    $role_values = array_map(function ($role) use ($conn) {
        return "'" . $conn->real_escape_string($role) . "'";
    }, $role_filters[$selected_role]['roles']);
    $where = 'WHERE u.role IN (' . implode(',', $role_values) . ')';
}

// Fetch audit logs with user name and role.
$sql = "
    SELECT al.*, u.full_name, u.role
    FROM audit_logs al
    LEFT JOIN users u ON al.user_id = u.user_id
    $where
    ORDER BY al.timestamp DESC
    LIMIT 100
";

$audit_logs = $conn->query($sql);
?>

<div class="page-hero fadeup">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-3">
        <div>
            <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.55);">System Admin · Activity Monitor</div>
            <h4 class="text-white fw-bold mb-0 mt-1"><i class="fas fa-clipboard-list me-2" style="color:var(--primary-light);"></i>Audit Trail</h4>
        </div>
        <div style="color:rgba(255,255,255,.6);font-size:.8rem;">
            <i class="<?php echo e($role_filters[$selected_role]['icon']); ?> me-1"></i><?php echo e($role_filters[$selected_role]['label']); ?> · Latest 100 records
        </div>
    </div>
    <p class="text-white-50 small mb-0"><i class="fas fa-search me-1"></i>Review login, account, and system configuration activity across the HRIS.</p>
</div>

<div class="content-card fadeup-1">
    <div class="card-header">
        <h5><i class="fas fa-clipboard-list me-2"></i>System Audit Trail</h5>
        <div class="d-flex align-items-center justify-content-end gap-2 flex-wrap ms-auto" style="flex: 1 1 460px;">
            <form method="GET" class="mb-0">
                <select class="form-select form-select-sm" name="role" onchange="this.form.submit()" aria-label="Filter audit trail by role" style="width: 190px;">
                    <?php foreach ($role_filters as $key => $filter): ?>
                        <option value="<?php echo e($key); ?>" <?php echo $selected_role === $key ? 'selected' : ''; ?>>
                            <?php echo e($filter['label']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
            <div class="search-box" style="min-width: 220px; flex: 1 1 220px; max-width: 320px;">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="form-control form-control-sm" id="searchAudit" placeholder="Search logs..." onkeyup="filterTable('searchAudit', 'auditTable')">
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive d-none d-md-block">
            <table class="table table-hover" id="auditTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Timestamp</th>
                        <th>User</th>
                        <th>Role</th>
                        <th>Action</th>
                        <th>Entity Type</th>
                        <th>Details</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($audit_logs->num_rows === 0): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">No audit logs found for <?php echo e($role_filters[$selected_role]['label']); ?>.</td></tr>
                    <?php else: ?>
                        <?php while ($log = $audit_logs->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $log['log_id']; ?></td>
                                <td><small><?php echo formatDateTime($log['timestamp']); ?></small></td>
                                <td><?php echo e($log['full_name'] ?? 'System'); ?></td>
                                <td><span class="badge bg-secondary"><?php echo e($log['role'] ?? 'System'); ?></span></td>
                                <td>
                                    <?php
                                    $badge_class = 'bg-secondary';
                                    switch ($log['action_type']) {
                                        case 'CREATE': $badge_class = 'bg-success'; break;
                                        case 'UPDATE': $badge_class = 'bg-info'; break;
                                        case 'DELETE': $badge_class = 'bg-danger'; break;
                                        case 'LOGIN': $badge_class = 'bg-primary'; break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>"><?php echo e($log['action_type']); ?></span>
                                </td>
                                <td><?php echo e($log['entity_type']); ?></td>
                                <td><small><?php echo e($log['details']); ?></small></td>
                                <td><small class="text-muted"><?php echo e($log['ip_address']); ?></small></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile view (Student Check-in Style) -->
        <div class="mobile-list-view d-block d-md-none">
            <div class="student-list">
                <?php if ($audit_logs->num_rows === 0): ?>
                    <div class="text-center py-4 text-muted">No audit logs found for <?php echo e($role_filters[$selected_role]['label']); ?>.</div>
                <?php else: ?>
                    <?php 
                    $audit_logs->data_seek(0);
                    while ($log = $audit_logs->fetch_assoc()): 
                        $avatar_class = 'bg-secondary';
                        $icon = 'fa-info-circle';
                        $badge_class = 'bg-secondary';
                        
                        switch ($log['action_type']) {
                            case 'CREATE': 
                                $avatar_class = 'bg-success'; 
                                $icon = 'fa-plus'; 
                                $badge_class = 'bg-success';
                                break;
                            case 'UPDATE': 
                                $avatar_class = 'bg-info'; 
                                $icon = 'fa-edit'; 
                                $badge_class = 'bg-info';
                                break;
                            case 'DELETE': 
                                $avatar_class = 'bg-danger'; 
                                $icon = 'fa-trash'; 
                                $badge_class = 'bg-danger';
                                break;
                            case 'LOGIN': 
                                $avatar_class = 'bg-primary'; 
                                $icon = 'fa-sign-in-alt'; 
                                $badge_class = 'bg-primary';
                                break;
                        }
                    ?>
                        <div class="student-item" style="align-items: flex-start;">
                            <div class="student-avatar">
                                <div class="avatar-placeholder d-flex align-items-center justify-content-center <?php echo $avatar_class; ?>" style="width: 42px; height: 42px; border-radius: 12px; font-size: 15px; color: white;">
                                    <i class="fas <?php echo $icon; ?>"></i>
                                </div>
                            </div>
                            <div class="student-info">
                                <div class="student-name" style="font-size: 0.9rem; margin-bottom: 2px;">
                                    <span class="badge <?php echo $badge_class; ?>"><?php echo e($log['action_type']); ?></span>
                                    <strong class="ms-1"><?php echo e($log['entity_type']); ?></strong>
                                    <span class="text-muted" style="font-size: 0.72rem;">#<?php echo $log['log_id']; ?></span>
                                </div>
                                <div class="student-meta" style="color: var(--text-dark); font-size: 0.82rem; margin-bottom: 4px; line-height: 1.3;">
                                    <?php echo e($log['details']); ?>
                                </div>
                                <div class="student-meta" style="font-size: 0.78rem;">
                                    <span>By: <strong><?php echo e($log['full_name'] ?? 'System'); ?></strong> (<?php echo e($log['role'] ?? 'System'); ?>)</span>
                                </div>
                                <div class="student-meta" style="font-size: 0.74rem; margin-top: 2px; color: var(--text-muted);">
                                    <span><i class="far fa-clock me-1"></i><?php echo formatDateTime($log['timestamp']); ?></span>
                                    &bull; <span><i class="fas fa-desktop me-1"></i><?php echo e($log['ip_address']); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php 
                    endwhile; 
                    ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
