<?php
$page_title = 'Admin Dashboard';
require_once '../includes/session-check.php';
checkRole(['Admin']);
require_once '../includes/header.php';

// Fetch stats - only count valid HR system users (Admin and HR roles), exclude orphaned accounts
$total_users = $conn->query("
    SELECT COUNT(*) as c 
    FROM users u
    LEFT JOIN employees e ON u.employee_id = e.employee_id
    LEFT JOIN departments d ON e.department_id = d.department_id
    WHERE (u.role = 'Admin' AND u.employee_id IS NULL)
       OR (u.role IN ('HR Manager', 'HR Supervisor', 'HR Staff') 
           AND e.employee_id IS NOT NULL 
           AND d.department_name = 'Human Resources')
")->fetch_assoc()['c'];
$active_users = $conn->query("
    SELECT COUNT(*) as c 
    FROM users u
    LEFT JOIN employees e ON u.employee_id = e.employee_id
    LEFT JOIN departments d ON e.department_id = d.department_id
    WHERE u.is_active = 1
      AND ((u.role = 'Admin' AND u.employee_id IS NULL)
       OR (u.role IN ('HR Manager', 'HR Supervisor', 'HR Staff') 
           AND e.employee_id IS NOT NULL 
           AND d.department_name = 'Human Resources'))
")->fetch_assoc()['c'];
$total_branches = $conn->query("SELECT COUNT(*) as c FROM branches WHERE deleted_at IS NULL")->fetch_assoc()['c'];
$recent_logs = $conn->query("SELECT COUNT(*) as c FROM audit_logs WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)")->fetch_assoc()['c'];

// 1. Employee Account Issuance Stats
$total_employees = $conn->query("SELECT COUNT(*) as c FROM employees WHERE is_active = 1")->fetch_assoc()['c'];
$employees_with_accounts = $conn->query("SELECT COUNT(DISTINCT employee_id) as c FROM users WHERE employee_id IS NOT NULL")->fetch_assoc()['c'];
$employees_no_accounts = max(0, $total_employees - $employees_with_accounts);

// 2. Authorized Personnel (HR Staff, HR Supervisor, HR Manager) by Branch
// Only counts valid HR system users, excludes orphaned accounts
$auth_personnel_res = $conn->query("
    SELECT 
        b.branch_name,
        SUM(CASE WHEN u.role = 'HR Staff' THEN 1 ELSE 0 END) as staff_count,
        SUM(CASE WHEN u.role = 'HR Supervisor' THEN 1 ELSE 0 END) as supervisor_count,
        SUM(CASE WHEN u.role = 'HR Manager' THEN 1 ELSE 0 END) as manager_count
    FROM branches b
    LEFT JOIN users u ON b.branch_id = u.branch_id AND u.is_active = 1
    LEFT JOIN employees e ON u.employee_id = e.employee_id
    LEFT JOIN departments d ON e.department_id = d.department_id
    WHERE b.deleted_at IS NULL
      AND (u.user_id IS NULL OR 
           (u.role IN ('HR Manager', 'HR Supervisor', 'HR Staff') 
            AND e.employee_id IS NOT NULL 
            AND d.department_name = 'Human Resources'))
    GROUP BY b.branch_id, b.branch_name
    ORDER BY b.branch_name
");
$branch_auth_stats = [];
while ($row = $auth_personnel_res->fetch_assoc()) {
    $branch_auth_stats[] = $row;
}

// --- SYSTEM ANALYTICS ---

// 1. User Roles Distribution - only valid HR system users, excludes orphaned accounts
$roles_res = $conn->query("
    SELECT u.role, COUNT(*) as count 
    FROM users u
    LEFT JOIN employees e ON u.employee_id = e.employee_id
    LEFT JOIN departments d ON e.department_id = d.department_id
    WHERE (u.role = 'Admin' AND u.employee_id IS NULL)
       OR (u.role IN ('HR Manager', 'HR Supervisor', 'HR Staff') 
           AND e.employee_id IS NOT NULL 
           AND d.department_name = 'Human Resources')
    GROUP BY u.role
");
$role_labels = [];
$role_counts = [];
while ($row = $roles_res->fetch_assoc()) {
    $role_labels[] = $row['role'];
    $role_counts[] = (int) $row['count'];
}

// 2. Account Status Breakdown - only valid HR system users
$status_res = $conn->query("
    SELECT u.is_active, COUNT(*) as count 
    FROM users u
    LEFT JOIN employees e ON u.employee_id = e.employee_id
    LEFT JOIN departments d ON e.department_id = d.department_id
    WHERE (u.role = 'Admin' AND u.employee_id IS NULL)
       OR (u.role IN ('HR Manager', 'HR Supervisor', 'HR Staff') 
           AND e.employee_id IS NOT NULL 
           AND d.department_name = 'Human Resources')
    GROUP BY u.is_active
");
$status_labels = ['Inactive', 'Active'];
$status_counts = [0, 0];
while ($row = $status_res->fetch_assoc()) {
    $status_counts[(int) $row['is_active']] = (int) $row['count'];
}

// 3. System Activity (Last 7 Days)
$activity_res = $conn->query("SELECT DATE_FORMAT(timestamp, '%b %d') as label, COUNT(*) as count 
                              FROM audit_logs 
                              WHERE timestamp >= DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY) 
                              GROUP BY DATE(timestamp), DATE_FORMAT(timestamp, '%b %d') 
                              ORDER BY DATE(timestamp) ASC");
$activity_labels = [];
$activity_counts = [];
if ($activity_res) {
    while ($row = $activity_res->fetch_assoc()) {
        $activity_labels[] = $row['label'];
        $activity_counts[] = (int) $row['count'];
    }
}

// Branch data for table
$branches_res = $conn->query("SELECT * FROM branches WHERE deleted_at IS NULL ORDER BY branch_name");
$branches = [];
while ($row = $branches_res->fetch_assoc()) {
    $branches[] = $row;
}
$selected_branch_id = isset($_GET['branch_id']) ? (int) $_GET['branch_id'] : (count($branches) > 0 ? $branches[0]['branch_id'] : null);
$branch_active_users = [];
if ($selected_branch_id) {
    $stmt = $conn->prepare("
        SELECT u.*, b.branch_name 
        FROM users u 
        LEFT JOIN branches b ON u.branch_id = b.branch_id 
        LEFT JOIN employees e ON u.employee_id = e.employee_id
        LEFT JOIN departments d ON e.department_id = d.department_id
        WHERE u.branch_id = ? 
          AND u.is_active = 1
          AND ((u.role = 'Admin' AND u.employee_id IS NULL)
            OR (u.role IN ('HR Manager', 'HR Supervisor', 'HR Staff') 
                AND e.employee_id IS NOT NULL 
                AND d.department_name = 'Human Resources'))
        ORDER BY u.full_name ASC
    ");
    $stmt->bind_param("i", $selected_branch_id);
    $stmt->execute();
    $branch_active_users = $stmt->get_result();
    $stmt->close();
}

$audit_logs = $conn->query("SELECT al.*, u.full_name FROM audit_logs al LEFT JOIN users u ON al.user_id = u.user_id ORDER BY al.timestamp DESC LIMIT 10");
?>

<div class="page-hero fadeup">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-3">
        <div>
            <div class="mb-1" style="color:#FFD97D;font-size:.88rem;font-weight:600;letter-spacing:.3px;"><?php echo getGreeting($_SESSION['full_name'] ?? ''); ?></div>
            <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.55);">
                System Admin · Control Center</div>
            <h4 class="text-white fw-bold mb-0 mt-1"><i class="fas fa-shield-alt me-2"
                    style="color:var(--primary-light);"></i>System Overview</h4>
        </div>
        <div style="color:rgba(255,255,255,.6);font-size:.8rem;">
            <i class="fas fa-sync-alt me-1"></i>Last refresh: <?php echo date('H:i'); ?>
        </div>
    </div>
    <p class="text-white-50 small mb-0"><i class="fas fa-lock me-1"></i>Security oversight and user management control
        center for Raquel Pawnshop HRIS.</p>
</div>

<!-- Statistics Cards -->
<div class="row g-3 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-users-cog"></i></div>
            <div class="stat-info">
                <h3><?php echo $total_users; ?></h3>
                <p>Total User Accounts</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-user-check"></i></div>
            <div class="stat-info">
                <h3><?php echo $active_users; ?></h3>
                <p>Active Sessions</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fas fa-building"></i></div>
            <div class="stat-info">
                <h3><?php echo $total_branches; ?></h3>
                <p>Registered Branches</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon gold"><i class="fas fa-file-invoice"></i></div>
            <div class="stat-info">
                <h3><?php echo $recent_logs; ?></h3>
                <p>Logs (Last 24h)</p>
            </div>
        </div>
    </div>
</div>

<!-- Account Issuance and Auth Personnel Summary -->
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="content-card h-100">
            <div class="card-header">
                <h5><i class="fas fa-id-badge me-2"></i>Employee Account Issuance</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 border-end">
                        <div class="p-2">
                            <h2 class="fw-bold text-success mb-0"><?php echo $employees_with_accounts; ?></h2>
                            <p class="text-muted small mb-0">Issued Accounts</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2">
                            <h2 class="fw-bold text-warning mb-0"><?php echo $employees_no_accounts; ?></h2>
                            <p class="text-muted small mb-0">No Accounts Yet</p>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="progress" style="height: 10px;">
                        <?php
                        $issued_pct = ($total_employees > 0) ? ($employees_with_accounts / $total_employees) * 100 : 0;
                        ?>
                        <div class="progress-bar bg-success" role="progressbar"
                            style="width: <?php echo $issued_pct; ?>%" aria-valuenow="<?php echo $issued_pct; ?>"
                            aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <small class="text-muted"><?php echo round($issued_pct, 1); ?>% Coverage</small>
                        <small class="text-muted">Total: <?php echo $total_employees; ?> Employees</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="content-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-user-shield me-2"></i>Authorized Personnel per Branch</h5>
                <small class="text-muted" id="authPersonnelInfo"></small>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0" style="font-size: 0.85rem;">
                        <thead class="table-light">
                            <tr>
                                <th>Branch</th>
                                <th class="text-center">Staff</th>
                                <th class="text-center">Supervisor</th>
                                <th class="text-center">Manager</th>
                            </tr>
                        </thead>
                        <tbody id="authPersonnelBody">
                            <?php foreach ($branch_auth_stats as $stat): ?>
                                <tr>
                                    <td><strong><?php echo e($stat['branch_name']); ?></strong></td>
                                    <td class="text-center"><?php echo $stat['staff_count']; ?></td>
                                    <td class="text-center"><?php echo $stat['supervisor_count']; ?></td>
                                    <td class="text-center"><?php echo $stat['manager_count']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top" style="background:#fafbfc;">
                    <small class="text-muted" id="authPageLabel">Page 1</small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0 gap-1" id="authPersonnelPagination"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- System Analytics -->
<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="content-card h-100">
            <div class="card-header">
                <h5><i class="fas fa-user-tag me-2"></i>User Roles Distribution</h5>
            </div>
            <div class="card-body">
                <canvas id="rolesChart" style="max-height: 250px;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="content-card h-100">
            <div class="card-header">
                <h5><i class="fas fa-toggle-on me-2"></i>Account Status</h5>
            </div>
            <div class="card-body">
                <canvas id="statusChart" style="max-height: 250px;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="content-card h-100">
            <div class="card-header">
                <h5><i class="fas fa-chart-line me-2"></i>Activity Trend (7 Days)</h5>
            </div>
            <div class="card-body">
                <canvas id="activityChart" style="max-height: 250px;"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Active Users by Branch -->
    <div class="col-lg-6">
        <div class="content-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-shield-alt me-2"></i>User Access by Branch</h5>
                <form method="GET" class="d-flex align-items-center" style="max-width: 200px;">
                    <select name="branch_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <?php foreach ($branches as $b): ?>
                            <option value="<?php echo $b['branch_id']; ?>" <?php echo $selected_branch_id == $b['branch_id'] ? 'selected' : ''; ?>>
                                <?php echo e($b['branch_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>System User</th>
                                <th>Access Role</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$branch_active_users || $branch_active_users->num_rows === 0): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">No active users in selected branch.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php while ($u = $branch_active_users->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong><?php echo e($u['full_name']); ?></strong></td>
                                        <td><span class="badge bg-primary"><?php echo e($u['role']); ?></span></td>
                                        <td><span class="badge bg-success">Active</span></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Security Activity -->
    <div class="col-lg-6">
        <div class="content-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-history me-2"></i>Recent Security Activity</h5>
                <a href="<?php echo BASE_URL; ?>/admin/audit-trail.php" class="btn btn-sm btn-outline-primary">View Full
                    Trail</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Operation</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($audit_logs->num_rows === 0): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">No security logs recorded.</td>
                                </tr>
                            <?php else: ?>
                                <?php while ($log = $audit_logs->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo e($log['full_name'] ?? 'System Process'); ?></td>
                                        <td><span class="badge bg-secondary"><?php echo e($log['action_type']); ?></span></td>
                                        <td><small><?php echo formatDateTime($log['timestamp']); ?></small></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 15 } }
            }
        };

        // 1. Roles Chart (Pie)
        new Chart(document.getElementById('rolesChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($role_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($role_counts); ?>,
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e'],
                    hoverOffset: 4
                }]
            },
            options: commonOptions
        });

        // 2. Status Chart (Doughnut)
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($status_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($status_counts); ?>,
                    backgroundColor: ['#e74a3b', '#1cc88a'],
                    hoverOffset: 4
                }]
            },
            options: commonOptions
        });

        // 3. Activity Trend (Line)
        new Chart(document.getElementById('activityChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode($activity_labels); ?>,
                datasets: [{
                    label: 'Audit Events',
                    data: <?php echo json_encode($activity_counts); ?>,
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.05)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                ...commonOptions,
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });

        // --- Authorized Personnel Pagination ---
        (function() {
            const tbody = document.getElementById('authPersonnelBody');
            const pagination = document.getElementById('authPersonnelPagination');
            const pageLabel = document.getElementById('authPageLabel');
            const infoLabel = document.getElementById('authPersonnelInfo');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const perPage = 5;
            const totalPages = Math.ceil(rows.length / perPage);
            let currentPage = 1;

            infoLabel.textContent = rows.length + ' branches';

            function render(page) {
                currentPage = page;
                const start = (page - 1) * perPage;
                const end = start + perPage;

                rows.forEach((row, i) => {
                    row.style.display = (i >= start && i < end) ? '' : 'none';
                });

                pageLabel.textContent = `Page ${page} of ${totalPages}`;

                // Build pagination buttons
                let html = '';
                html += `<li class="page-item ${page === 1 ? 'disabled' : ''}">
                            <a class="page-link" href="#" data-page="${page - 1}" style="border-radius:8px;">&lsaquo;</a>
                         </li>`;

                // Smart page range (show max 5 page buttons)
                let rangeStart = Math.max(1, page - 2);
                let rangeEnd = Math.min(totalPages, rangeStart + 4);
                if (rangeEnd - rangeStart < 4) rangeStart = Math.max(1, rangeEnd - 4);

                for (let i = rangeStart; i <= rangeEnd; i++) {
                    html += `<li class="page-item ${i === page ? 'active' : ''}">
                                <a class="page-link" href="#" data-page="${i}" style="border-radius:8px;">${i}</a>
                             </li>`;
                }

                html += `<li class="page-item ${page === totalPages ? 'disabled' : ''}">
                            <a class="page-link" href="#" data-page="${page + 1}" style="border-radius:8px;">&rsaquo;</a>
                         </li>`;

                pagination.innerHTML = html;

                // Bind click events
                pagination.querySelectorAll('a[data-page]').forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const p = parseInt(this.dataset.page);
                        if (p >= 1 && p <= totalPages) render(p);
                    });
                });
            }

            if (totalPages > 1) {
                render(1);
            } else {
                pageLabel.textContent = `Page 1 of 1`;
            }
        })();
    });
</script>

<?php require_once '../includes/footer.php'; ?>