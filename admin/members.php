<?php
$page_title = 'Member List';
require_once '../includes/session-check.php';
checkRole(['Admin']);
require_once '../includes/functions.php';

require_once '../includes/header.php';

// Pagination settings
$per_page = 10;
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($current_page - 1) * $per_page;
$selected_department = isset($_GET['department']) && $_GET['department'] !== '' ? max(0, (int)$_GET['department']) : 0;

$department_options = $conn->query("
    SELECT department_id, department_name
    FROM departments
    WHERE is_active = 1 AND deleted_at IS NULL
    ORDER BY department_name
");

$member_where = "
    WHERE e.employee_id NOT IN (
        SELECT employee_id
        FROM users
        WHERE role = 'Admin' AND employee_id IS NOT NULL
    )
";

if ($selected_department > 0) {
    $member_where .= " AND e.department_id = $selected_department";
}

// Count all employees (excluding strictly Admin accounts)
$total_members_result = $conn->query("
    SELECT COUNT(*) AS total
    FROM employees e
    $member_where
");
$total_members = (int)($total_members_result->fetch_assoc()['total'] ?? 0);
$total_pages = max(1, (int)ceil($total_members / $per_page));

if ($current_page > $total_pages) {
    $current_page = $total_pages;
    $offset = ($current_page - 1) * $per_page;
}

// Fetch paginated employees with branch info (excluding strictly Admin accounts)
$employees = $conn->query("
    SELECT e.employee_id, e.employee_code, e.first_name, e.last_name, e.middle_name, e.job_title,
           b.branch_name, d.department_name, e.profile_picture, e.is_active
    FROM employees e 
    LEFT JOIN branches b ON e.branch_id = b.branch_id 
    LEFT JOIN departments d ON e.department_id = d.department_id
    $member_where
    ORDER BY e.last_name, e.first_name
    LIMIT $per_page OFFSET $offset
");

?>

<style>
    .admin-member-toolbar {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: flex-end;
    }

    .admin-member-toolbar .form-select {
        min-width: 190px;
    }

    @media (max-width: 768px) {
        .admin-member-toolbar,
        .admin-member-toolbar form,
        .admin-member-toolbar .search-box {
            width: 100%;
        }

        .admin-member-toolbar .form-select,
        .admin-member-toolbar .search-box input {
            width: 100%;
        }
    }
</style>

<div class="page-hero fadeup">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-3">
        <div>
            <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.55);">System Admin · Member Directory</div>
            <h4 class="text-white fw-bold mb-0 mt-1"><i class="fas fa-id-card me-2" style="color:var(--primary-light);"></i>Member List</h4>
        </div>
        <div style="color:rgba(255,255,255,.6);font-size:.8rem;">
            <i class="fas fa-users me-1"></i><?php echo $total_members; ?> registered members
        </div>
    </div>
    <p class="text-white-50 small mb-0"><i class="fas fa-address-book me-1"></i>Full list of registered employees in the HRIS system.</p>
</div>

<div class="content-card fadeup-1">
    <div class="card-header">
        <h5><i class="fas fa-id-card me-2"></i>Employee Members</h5>
        <div class="admin-member-toolbar">
            <form method="GET" class="d-flex align-items-center gap-2">
                <select name="department" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="Filter members by department">
                    <option value="">All Departments</option>
                    <?php while ($department = $department_options->fetch_assoc()): ?>
                        <option value="<?php echo (int) $department['department_id']; ?>" <?php echo $selected_department === (int) $department['department_id'] ? 'selected' : ''; ?>>
                            <?php echo e($department['department_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </form>
            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="form-control form-control-sm" id="searchMembers" placeholder="Search members..." onkeyup="filterTable('searchMembers', 'membersTable')">
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover" id="membersTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Employee ID</th>
                        <th>Photo</th>
                        <th>Full Name</th>
                        <th>Department</th>
                        <th>Branch</th>
                        <th>Position</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $row_number = $offset + 1; ?>
                    <?php if ($employees->num_rows === 0): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No members found for the selected department.</td>
                        </tr>
                    <?php else: ?>
                        <?php while ($emp = $employees->fetch_assoc()): ?>
                            <tr>
                                <td data-label="#"><strong><?php echo $row_number++; ?></strong></td>
                                <td data-label="Employee ID"><strong class="company-id-value"><?php echo e(getEmployeeDisplayId($emp)); ?></strong></td>
                                <td data-label="Photo">
                                    <img src="<?php echo getEmployeeAvatar($emp['profile_picture']); ?>?v=<?php echo time(); ?>"
                                         alt="Profile" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
                                </td>
                                <td data-label="Full Name"><strong><?php echo e($emp['last_name'] . ', ' . $emp['first_name'] . ' ' . $emp['middle_name']); ?></strong></td>
                                <td data-label="Department"><?php echo e($emp['department_name'] ?? 'N/A'); ?></td>
                                <td data-label="Branch"><?php echo e($emp['branch_name'] ?? 'N/A'); ?></td>
                                <td data-label="Position"><?php echo e($emp['job_title'] ?? 'N/A'); ?></td>
                                <td data-label="Status">
                                    <span class="badge <?php echo $emp['is_active'] ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo $emp['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($total_pages > 1): ?>
        <div class="card-footer bg-white border-0 pt-0">
            <nav aria-label="Members pagination">
                <ul class="pagination pagination-sm justify-content-end mb-0">
                    <?php $query_params = $_GET; unset($query_params['page']); ?>
                    <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?<?php echo http_build_query(array_merge($query_params, ['page' => $current_page - 1])); ?>">Previous</a>
                    </li>
                    <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                        <li class="page-item <?php echo $p === $current_page ? 'active' : ''; ?>">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($query_params, ['page' => $p])); ?>"><?php echo $p; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?<?php echo http_build_query(array_merge($query_params, ['page' => $current_page + 1])); ?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
