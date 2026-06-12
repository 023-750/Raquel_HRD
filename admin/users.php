<?php
$page_title = 'User Management';
require_once '../includes/session-check.php';
checkRole(['Admin']);
require_once '../includes/functions.php';

// ── Handle toggle active status ──────────────────────────────────────────────
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $uid = (int) $_GET['toggle'];
    if ($uid !== (int) $_SESSION['user_id']) {
        $conn->query("UPDATE users SET is_active = NOT is_active WHERE user_id = $uid");
        logAudit($conn, $_SESSION['user_id'], 'UPDATE', 'User', $uid, 'Toggled user active status');
        redirectWith(BASE_URL . '/admin/users.php', 'success', 'User status updated successfully.');
    }
}

// ── Handle delete ─────────────────────────────────────────────────────────────
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $uid = (int) $_GET['delete'];
    if ($uid !== (int) $_SESSION['user_id']) {
        $conn->query("DELETE FROM users WHERE user_id = $uid");
        logAudit($conn, $_SESSION['user_id'], 'DELETE', 'User', $uid, 'Deleted user account');
        redirectWith(BASE_URL . '/admin/users.php', 'success', 'User deleted successfully.');
    } else {
        redirectWith(BASE_URL . '/admin/users.php', 'danger', 'You cannot delete your own account.');
    }
}

require_once '../includes/header.php';

// Pagination settings
$per_page = 10;
$current_page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$offset = ($current_page - 1) * $per_page;

// Count only HR system users (Admin or valid HR employees only)
$total_users_result = $conn->query("
    SELECT COUNT(*) AS total
    FROM users u
    LEFT JOIN employees e ON u.employee_id = e.employee_id
    LEFT JOIN departments d ON e.department_id = d.department_id
    WHERE (u.role = 'Admin')
       OR (u.role IN ('HR Manager', 'HR Supervisor', 'HR Staff')
           AND e.employee_id IS NOT NULL
           AND d.department_name = 'Human Resources')
");
$total_users = (int) ($total_users_result->fetch_assoc()['total'] ?? 0);
$total_pages = max(1, (int) ceil($total_users / $per_page));

if ($current_page > $total_pages) {
    $current_page = $total_pages;
    $offset = ($current_page - 1) * $per_page;
}

// Fetch HR system users
$users = $conn->query("
    SELECT u.*, b.branch_name, e.profile_picture, e.job_title, rc.rank_name
    FROM users u
    LEFT JOIN branches b ON u.branch_id = b.branch_id
    LEFT JOIN employees e ON u.employee_id = e.employee_id
    LEFT JOIN departments d ON e.department_id = d.department_id
    LEFT JOIN rank_categories rc ON e.rank_category_id = rc.rank_category_id
    WHERE (u.role = 'Admin')
       OR (u.role IN ('HR Manager', 'HR Supervisor', 'HR Staff')
           AND e.employee_id IS NOT NULL
           AND d.department_name = 'Human Resources')
    ORDER BY u.created_at DESC
    LIMIT $per_page OFFSET $offset
");

// Fetch branches for the add form
$branches = $conn->query("SELECT * FROM branches ORDER BY branch_name");

// Fetch active HR employees who don't have an HR/admin account yet
$eligible_employees = $conn->query("
    SELECT e.employee_id, e.employee_code, e.first_name, e.last_name, e.job_title, rc.rank_name, ec.personal_email
    FROM employees e
    JOIN departments d ON e.department_id = d.department_id
    LEFT JOIN rank_categories rc ON e.rank_category_id = rc.rank_category_id
    LEFT JOIN employee_contacts ec ON e.employee_id = ec.employee_id
    LEFT JOIN users u ON e.employee_id = u.employee_id AND u.role != 'Employee'
    WHERE u.user_id IS NULL
      AND e.is_active = 1
      AND d.department_name = 'Human Resources'
    ORDER BY e.last_name, e.first_name
");

// Grab and clear any pending credential slip
$new_creds = $_SESSION['new_employee_credentials'] ?? null;
unset($_SESSION['new_employee_credentials']);
?>

<?php if ($new_creds): ?>
<!-- Credential Slip Modal — shown once after creating any account -->
<div class="modal fade" id="credentialSlipModal" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
    <div class="modal-content creds-modal-content">
      <div class="creds-modal-header">
        <div class="creds-icon-ring">
          <i class="fas fa-shield-alt"></i>
        </div>
        <h5 class="modal-title">Account Credentials</h5>
      </div>
      <div class="modal-body text-center px-4 pt-3 pb-4">
        <p class="text-muted small mb-1">Account created successfully for:</p>
        <h5 class="fw-bold mb-3" style="color: var(--primary-blue);"><?php echo e($new_creds['full_name']); ?></h5>
        
        <div class="creds-card-box">
          <div class="creds-field-group">
            <div class="creds-field-label">Username</div>
            <div class="creds-input-wrapper">
              <div class="creds-value-display" id="display_username"><?php echo e($new_creds['username']); ?></div>
              <button type="button" class="creds-copy-btn" onclick="copyCredValue(this, '<?php echo e(addslashes($new_creds['username'])); ?>')" title="Copy Username">
                <i class="far fa-copy"></i>
              </button>
            </div>
          </div>
          
          <div class="creds-field-group">
            <div class="creds-field-label">Temporary Password</div>
            <div class="creds-input-wrapper">
              <div class="creds-value-display" id="display_password"><?php echo e($new_creds['password']); ?></div>
              <button type="button" class="creds-copy-btn" onclick="copyCredValue(this, '<?php echo e(addslashes($new_creds['password'])); ?>')" title="Copy Password">
                <i class="far fa-copy"></i>
              </button>
            </div>
          </div>
        </div>

        <div class="creds-warning-callout mb-3">
          <i class="fas fa-exclamation-circle"></i>
          <div>Save and hand these credentials securely to the user. This slip will not be shown again.</div>
        </div>
        
        <button type="button" class="btn btn-primary w-100 py-2.5 mt-2 fw-bold" style="border-radius: 10px; background: linear-gradient(135deg, var(--primary-blue), var(--primary-dark)); border: none;" data-bs-dismiss="modal">
          I've Noted the Credentials
        </button>
      </div>
    </div>
  </div>
</div>
<script>
function copyCredValue(btn, text) {
    navigator.clipboard.writeText(text).then(() => {
        const icon = btn.querySelector('i');
        icon.className = 'fas fa-check';
        btn.classList.add('copied');
        setTimeout(() => {
            icon.className = 'far fa-copy';
            btn.classList.remove('copied');
        }, 1500);
    });
}
document.addEventListener('DOMContentLoaded', () => new bootstrap.Modal(document.getElementById('credentialSlipModal')).show());
</script>
<?php endif; ?>

<!-- Page Header -->
<div class="page-hero fadeup">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-3">
        <div>
            <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.55);">System Admin · Access Control</div>
            <h4 class="text-white fw-bold mb-0 mt-1"><i class="fas fa-users-cog me-2" style="color:var(--primary-light);"></i>User Management</h4>
        </div>
        <div class="d-flex gap-2 flex-wrap justify-content-end">
            <button class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                <i class="fas fa-user-shield me-2"></i>Add New Admin
            </button>
            <button class="btn btn-light text-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fas fa-plus me-2"></i>Add New User
            </button>
        </div>
    </div>
    <p class="text-white-50 small mb-0"><i class="fas fa-lock me-1"></i>Manage system user accounts, roles, and HR access permissions.</p>
</div>

<div class="d-none d-md-flex justify-content-between align-items-center mb-4" data-flash-toast-anchor>
    <p class="notification_placeholder text-muted mb-0">All USERS OF HRIS SYSTEM</p>
</div>

<!-- Users Table -->
<div class="content-card fadeup-1">
    <div class="card-header">
        <h5><i class="fas fa-users me-2"></i>All Users</h5>
        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="form-control form-control-sm" id="searchUsers" placeholder="Search users..."
                onkeyup="filterTable('searchUsers', 'usersTable')">
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive d-none d-md-block">
            <table class="table table-hover" id="usersTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Photo</th>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Position</th>
                        <th>Branch</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $row_number = $offset + 1; ?>
                    <?php while ($user = $users->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo $row_number++; ?></strong></td>
                            <td>
                                <img src="<?php echo getEmployeeAvatar($user['profile_picture']); ?>?v=<?php echo time(); ?>"
                                    alt="Profile" class="rounded-circle"
                                    style="width: 32px; height: 32px; object-fit: cover;">
                            </td>
                            <td><strong><?php echo e($user['username']); ?></strong></td>
                            <td><?php echo e($user['full_name']); ?></td>
                            <td><?php echo e($user['email']); ?></td>
                            <td><span class="badge bg-primary"><?php echo e($user['role']); ?></span></td>
                            <td>
                                <?php if (!empty($user['job_title'])): ?>
                                    <div><?php echo e($user['job_title']); ?></div>
                                    <?php if (!empty($user['rank_name'])): ?>
                                        <small class="text-muted"><?php echo e($user['rank_name']); ?></small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted small">Standalone account</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($user['branch_name'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="badge <?php echo $user['is_active'] ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                    <!-- Edit -->
                                    <a href="<?php echo BASE_URL; ?>/admin/edit-user.php?id=<?php echo $user['user_id']; ?>"
                                        class="btn btn-sm btn-outline-primary" title="Edit User">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <!-- Toggle Status -->
                                    <a href="?toggle=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-outline-warning"
                                        title="Toggle Active/Inactive">
                                        <i class="fas fa-power-off"></i>
                                    </a>
                                    <!-- Delete -->
                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Delete User"
                                        onclick="setDeleteTarget(<?php echo $user['user_id']; ?>, '<?php echo e(addslashes($user['username'])); ?>')"
                                        data-bs-toggle="modal" data-bs-target="#deleteModal">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php else: ?>
                                    <small class="text-muted">Current User</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile view (Student Check-in Style) -->
        <div class="mobile-list-view d-block d-md-none">
            <div class="student-list">
                <?php $row_number_mob = $offset + 1; ?>
                <?php if ($users->num_rows === 0): ?>
                    <div class="text-center py-4 text-muted">No users found.</div>
                <?php else: ?>
                    <?php 
                    $users->data_seek(0);
                    while ($user = $users->fetch_assoc()): 
                        $avatar_num = ($row_number_mob % 6) + 1;
                        // Split full name to get initials
                        $name_parts = explode(' ', trim($user['full_name'] ?? ''));
                        $first_part = $name_parts[0] ?? '';
                        $last_part = end($name_parts);
                        if ($first_part === $last_part) {
                            $initials = strtoupper(substr($first_part, 0, 2));
                        } else {
                            $initials = strtoupper(substr($first_part, 0, 1) . substr($last_part, 0, 1));
                        }
                        if (empty($initials)) $initials = 'US';
                    ?>
                        <div class="student-item">
                            <div class="student-avatar">
                                <img src="<?php echo getEmployeeAvatar($user['profile_picture']); ?>?v=<?php echo time(); ?>" alt="Profile" class="avatar-img">
                            </div>
                            <div class="student-info">
                                <div class="student-name">
                                    <?php echo e($user['full_name']); ?>
                                </div>
                                <div class="student-meta">
                                    Username: <code><?php echo e($user['username']); ?></code> &bull; <span class="badge bg-primary"><?php echo e($user['role']); ?></span>
                                </div>
                                <div class="student-meta" style="margin-top: 2px;">
                                    <?php if (!empty($user['job_title'])): ?>
                                        <span><?php echo e($user['job_title']); ?></span>
                                        <?php if (!empty($user['rank_name'])): ?>
                                            <span class="text-muted small">(<?php echo e($user['rank_name']); ?>)</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted small">Standalone Account</span>
                                    <?php endif; ?>
                                </div>
                                <div class="student-meta" style="margin-top: 2px;">
                                    <span>Branch: <?php echo e($user['branch_name'] ?? 'N/A'); ?></span> &bull; <span><?php echo e($user['email']); ?></span>
                                </div>
                                <div class="student-meta" style="margin-top: 4px;">
                                    Status: 
                                    <span class="badge <?php echo $user['is_active'] ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="ms-auto text-end d-flex flex-column gap-2 justify-content-center">
                                <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                    <div class="d-flex gap-1">
                                        <!-- Edit -->
                                        <a href="<?php echo BASE_URL; ?>/admin/edit-user.php?id=<?php echo $user['user_id']; ?>"
                                            class="btn btn-sm btn-outline-primary" title="Edit User" style="padding: 5px 8px; border-radius: 6px;">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <!-- Toggle Status -->
                                        <a href="?toggle=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-outline-warning"
                                            title="Toggle Active/Inactive" style="padding: 5px 8px; border-radius: 6px;">
                                            <i class="fas fa-power-off"></i>
                                        </a>
                                        <!-- Delete -->
                                        <button type="button" class="btn btn-sm btn-outline-danger" title="Delete User"
                                            onclick="setDeleteTarget(<?php echo $user['user_id']; ?>, '<?php echo e(addslashes($user['username'])); ?>')"
                                            data-bs-toggle="modal" data-bs-target="#deleteModal" style="padding: 5px 8px; border-radius: 6px;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <small class="text-muted fw-bold">You</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php 
                        $row_number_mob++;
                    endwhile; 
                    ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php if ($total_pages > 1): ?>
        <div class="card-footer bg-white border-0 pt-0">
            <nav aria-label="Users pagination">
                <ul class="pagination pagination-sm justify-content-end mb-0">
                    <?php $query_params = $_GET; unset($query_params['page']); ?>
                    <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?<?php echo http_build_query(array_merge($query_params, ['page' => $current_page - 1])); ?>">Previous</a>
                    </li>
                    <?php 
                    $start_page = max(1, $current_page - 2);
                    $end_page = min($total_pages, $start_page + 4);
                    if ($end_page - $start_page < 4) {
                        $start_page = max(1, $end_page - 4);
                    }

                    if ($start_page > 1) {
                        ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($query_params, ['page' => 1])); ?>">1</a>
                        </li>
                        <?php if ($start_page > 2): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                        <?php
                    }

                    for ($p = $start_page; $p <= $end_page; $p++) {
                        ?>
                        <li class="page-item <?php echo $p === $current_page ? 'active' : ''; ?>">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($query_params, ['page' => $p])); ?>"><?php echo $p; ?></a>
                        </li>
                        <?php
                    }

                    if ($end_page < $total_pages) {
                        if ($end_page < $total_pages - 1): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($query_params, ['page' => $total_pages])); ?>"><?php echo $total_pages; ?></a>
                        </li>
                        <?php
                    }
                    ?>
                    <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?<?php echo http_build_query(array_merge($query_params, ['page' => $current_page + 1])); ?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

<!-- ── Add User Modal ─────────────────────────────────────────────────────── -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo BASE_URL; ?>/admin/add-user.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Employee <span class="text-danger">*</span></label>
                        <select class="form-select" name="employee_id" required onchange="prefillUserInfo(this)">
                            <option value="">Select Employee</option>
                            <?php while ($emp = $eligible_employees->fetch_assoc()): ?>
                                <option value="<?php echo $emp['employee_id']; ?>"
                                    data-name="<?php echo e($emp['first_name'] . ' ' . $emp['last_name']); ?>"
                                    data-email="<?php echo e($emp['personal_email']); ?>"
                                    data-rank="<?php echo e($emp['rank_name'] ?? ''); ?>">
                                    <?php echo e($emp['last_name'] . ', ' . $emp['first_name']); ?>
                                    <?php if (!empty($emp['job_title'])): ?> - <?php echo e($emp['job_title']); ?><?php endif; ?>
                                    <?php if (!empty($emp['employee_code'])): ?> (<?php echo e($emp['employee_code']); ?>)<?php endif; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="username" id="new_username" required>
                        <div class="form-text">Use a custom HR username. Do not use the Employee ID.</div>
                    </div>
                    <input type="hidden" name="full_name" id="new_full_name">
                    <input type="hidden" name="email" id="new_email">
                    <input type="hidden" name="redirect" value="users.php">
                    <div class="mb-3">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select class="form-select" name="role" required>
                            <option value="">Select Role</option>
                            <option value="HR Staff">HR Staff</option>
                            <option value="HR Supervisor">HR Supervisor</option>
                            <option value="HR Manager">HR Manager / Manager Level</option>
                        </select>
                        <div class="form-text">Use this access role for any HR manager-level employee, including HR Manager I-V and OIC HR Manager.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Branch</label>
                        <select class="form-select" name="branch_id">
                            <option value="">None (Admin)</option>
                            <?php $branches->data_seek(0); while ($branch = $branches->fetch_assoc()): ?>
                                <option value="<?php echo $branch['branch_id']; ?>"><?php echo e($branch['branch_name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Profile Picture</label>
                        <input type="file" class="form-control" name="profile_picture" accept="image/*">
                        <div class="form-text">Optional. Max 2MB (JPG, PNG, WebP).</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="password" required minlength="6">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Add Admin Modal ────────────────────────────────────────────────────── -->
<div class="modal fade" id="addAdminModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-shield me-2"></i>Add New Admin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo BASE_URL; ?>/admin/add-user.php">
                <div class="modal-body">
                    <div class="alert alert-info py-2 small">
                        <i class="fas fa-info-circle me-1"></i>Admin accounts are standalone and are not linked to employee records.
                    </div>
                    <input type="hidden" name="role" value="Admin">
                    <input type="hidden" name="redirect" value="users.php">
                    <div class="mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="full_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="username" required>
                        <div class="form-text">Use a custom admin username.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="password" required minlength="6">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Create Admin</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Delete Confirmation Modal ──────────────────────────────────────────── -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Delete User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p>Are you sure you want to delete user <strong id="deleteUserName"></strong>?</p>
                <p class="text-danger small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="deleteConfirmBtn" class="btn btn-danger">
                    <i class="fas fa-trash me-1"></i>Delete
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    function setDeleteTarget(userId, username) {
        document.getElementById('deleteUserName').textContent = username;
        document.getElementById('deleteConfirmBtn').href = '?delete=' + userId;
    }

    function prefillUserInfo(select) {
        const option = select.options[select.selectedIndex];
        if (!option.value) return;

        document.getElementById('new_full_name').value = option.getAttribute('data-name');
        document.getElementById('new_email').value = option.getAttribute('data-email') || '';

        const roleSelect = document.querySelector('#addUserModal [name="role"]');
        const usernameField = document.getElementById('new_username');
        const rank = (option.getAttribute('data-rank') || '').toLowerCase();

        if (!usernameField.value || usernameField.value === option.value) {
            const name = option.getAttribute('data-name').toLowerCase().replace(/\s+/g, '.');
            usernameField.value = name;
        }

        if (roleSelect && !roleSelect.value) {
            if (rank.includes('manager')) {
                roleSelect.value = 'HR Manager';
            } else if (rank.includes('supervisor')) {
                roleSelect.value = 'HR Supervisor';
            } else {
                roleSelect.value = 'HR Staff';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const empSelect = document.querySelector('#addUserModal [name="employee_id"]');
        const usernameField = document.getElementById('new_username');
        if (empSelect && usernameField) {
            empSelect.addEventListener('change', function () {
                const opt = empSelect.options[empSelect.selectedIndex];
                if (!opt || !opt.value) return;
                if (!usernameField.value || usernameField.value === opt.value || /^\d+$/.test(usernameField.value)) {
                    usernameField.value = opt.getAttribute('data-name')?.toLowerCase().replace(/\s+/g, '.') || '';
                }
            });
        }
    });
</script>

<?php require_once '../includes/footer.php'; ?>
