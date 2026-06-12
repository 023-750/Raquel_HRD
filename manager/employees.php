<?php
$page_title = 'Employees';
require_once '../includes/session-check.php';
checkRole(['HR Manager']);
require_once '../includes/functions.php';

// Resolve the user's assigned branch name for auto-filtering
$user_assigned_branch_name = '';
$branch_id = $_SESSION['branch_id'];
if (!empty($branch_id)) {
    $br_stmt = $conn->prepare("SELECT branch_name FROM branches WHERE branch_id = ? LIMIT 1");
    $br_stmt->bind_param("i", $branch_id);
    $br_stmt->execute();
    $br_res = $br_stmt->get_result();
    if ($br_row = $br_res->fetch_assoc()) {
        $user_assigned_branch_name = $br_row['branch_name'];
    }
    $br_stmt->close();
}

// Handle activate/deactivate
if (isset($_GET['deactivate']) && is_numeric($_GET['deactivate'])) {
    $eid = (int) $_GET['deactivate'];
    $status = $_GET['status'] ?? 'Separated';
    
    $stmt = $conn->prepare("UPDATE employees SET is_active = 0, employment_status = ? WHERE employee_id = ?");
    $stmt->bind_param("si", $status, $eid);
    
    if ($stmt->execute()) {
        logAudit($conn, $_SESSION['user_id'], 'UPDATE', 'Employee', $eid, "Deactivated employee with status: $status");
        redirectWith(BASE_URL . '/manager/employees.php', 'success', 'Employee deactivated successfully.');
    }
    $stmt->close();
}
if (isset($_GET['activate']) && is_numeric($_GET['activate'])) {
    $eid = (int) $_GET['activate'];
    $conn->query("UPDATE employees SET is_active = 1, employment_status = 'Regular' WHERE employee_id = $eid");
    logAudit($conn, $_SESSION['user_id'], 'UPDATE', 'Employee', $eid, 'Reactivated employee');
    redirectWith(BASE_URL . '/manager/employees.php', 'success', 'Employee reactivated successfully.');
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $eid = (int) $_GET['delete'];
    // Delete the user account associated with this employee (if any) BEFORE deleting employee
    // This prevents orphaned user accounts from appearing in User Management
    $conn->query("DELETE FROM users WHERE employee_id = $eid");
    // Delete the employee and all normalized sub-tables
    $tables = [
        'employee_details',
        'employee_government_ids',
        'employee_addresses',
        'employee_contacts',
        'employee_emergency_contacts',
        'employee_disclosures',
        'employee_family',
        'employee_children',
        'employee_siblings',
        'employee_education',
        'employee_work_experience',
        'employee_trainings',
        'employee_voluntary_work',
        'employee_eligibility',
        'employee_skills',
        'employee_recognitions',
        'employee_memberships',
        'employee_real_properties',
        'employee_personal_properties',
        'employee_liabilities',
        'employee_references'
    ];
    foreach ($tables as $tbl) {
        $conn->query("DELETE FROM $tbl WHERE employee_id = $eid");
    }

    $conn->query("DELETE FROM employees WHERE employee_id = $eid");
    logAudit($conn, $_SESSION['user_id'], 'DELETE', 'Employee', $eid, 'Permanently deleted employee');
    redirectWith(BASE_URL . '/manager/employees.php', 'success', 'Employee deleted permanently.');
}

require_once '../includes/header.php';

// Fetch employees
$employees = $conn->query("
    SELECT e.*, b.branch_name, d.department_name 
    FROM employees e 
    LEFT JOIN branches b ON e.branch_id = b.branch_id 
    LEFT JOIN departments d ON e.department_id = d.department_id 
    WHERE e.employee_id NOT IN (SELECT employee_id FROM users WHERE role = 'Admin' AND employee_id IS NOT NULL)
    ORDER BY e.last_name, e.first_name
");
$employee_total = (int) $employees->num_rows;
$employee_active = (int) $conn->query("
    SELECT COUNT(*) as cnt
    FROM employees e
    WHERE e.is_active = 1
      AND e.employee_id NOT IN (SELECT employee_id FROM users WHERE role = 'Admin' AND employee_id IS NOT NULL)
")->fetch_assoc()['cnt'];
$employee_inactive = max(0, $employee_total - $employee_active);

// Fetch distinct values for filter dropdowns
$job_titles_res = $conn->query("
    SELECT 
        jt.job_title_id,
        jt.job_title,
        d.department_id,
        d.department_name,
        rc.rank_name,
        parent.job_title AS reports_to_title,
        (
            SELECT COUNT(*) 
            FROM employees e 
            WHERE e.job_title_id = jt.job_title_id
        ) AS employee_count
    FROM job_titles jt
    LEFT JOIN departments d 
        ON jt.department_id = d.department_id
    LEFT JOIN rank_categories rc 
        ON jt.rank_category_id = rc.rank_category_id
    LEFT JOIN job_titles parent 
        ON jt.reports_to = parent.job_title_id
    WHERE jt.job_title IS NOT NULL 
      AND jt.job_title != ''
    ORDER BY d.department_name ASC, jt.job_title ASC
");
$job_titles = [];
$job_titles_by_dept = []; // Organize by department
while ($r = $job_titles_res->fetch_assoc()) {
    $job_titles[] = $r;
    $dept_name = $r['department_name'] ?? 'Unassigned';
    if (!isset($job_titles_by_dept[$dept_name])) {
        $job_titles_by_dept[$dept_name] = [];
    }
    $job_titles_by_dept[$dept_name][] = $r;
}

$departments_res = $conn->query("SELECT d.department_name FROM departments d ORDER BY d.department_name ASC");
$departments = [];
while ($r = $departments_res->fetch_assoc())
    $departments[] = $r['department_name'];

$branches_res = $conn->query("SELECT b.branch_name FROM branches b ORDER BY b.branch_name ASC");
$branches = [];
while ($r = $branches_res->fetch_assoc())
    $branches[] = $r['branch_name'];

$statuses = ['OJT', 'Probationary', 'Project Based', 'Project-Based', 'Regular', 'Separated', 'Trainee', 'AWOL', 'Retirement', 'Death', 'Permanent of Total Disability', 'Resignation', 'Failed in Training', 'Termination for Cause'];
$selected_branch = $_GET['branch'] ?? $user_assigned_branch_name;
?>

<style>
    /* Filter Toolbar */
    .filter-toolbar {
        padding: 16px 20px;
        background: linear-gradient(135deg, #f8f9fc 0%, #f1f3f8 100%);
        border-bottom: 1px solid #e8ecf1;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
    }

    .filter-group {
        position: relative;
        min-width: 180px;
        flex: 1;
    }

    .filter-group label {
        display: block;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #8094ae;
        margin-bottom: 4px;
    }

    .filter-group select {
        width: 100%;
        padding: 8px 32px 8px 12px;
        border: 1px solid #dce3ed;
        border-radius: 8px;
        background: #fff;
        font-size: 0.85rem;
        font-weight: 500;
        color: #344357;
        transition: all 0.2s ease;
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%238094ae' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        cursor: pointer;
    }

    .filter-group select:focus {
        outline: none;
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb, 59, 130, 246), 0.1);
    }

    .filter-group select.active-filter {
        border-color: var(--primary-blue);
        background-color: #eef4ff;
        color: var(--primary-blue);
        font-weight: 600;
    }

    .filter-actions {
        display: flex;
        align-items: flex-end;
        gap: 8px;
        padding-bottom: 1px;
    }

    .filter-summary {
        padding: 8px 20px;
        background: #fff;
        border-bottom: 1px solid #e8ecf1;
        display: none;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .filter-summary.has-filters {
        display: flex;
    }

    .filter-summary .filter-label {
        font-size: 0.78rem;
        font-weight: 600;
        color: #8094ae;
        margin-right: 4px;
    }

    .filter-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 3px 10px;
        background: #eef4ff;
        border: 1px solid #d0dfff;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--primary-blue);
        animation: chipIn 0.2s ease;
    }

    .filter-chip .chip-category {
        font-weight: 400;
        color: #8094ae;
    }

    .filter-chip .remove-chip {
        cursor: pointer;
        opacity: 0.6;
        transition: opacity 0.15s;
        font-size: 0.65rem;
    }

    .filter-chip .remove-chip:hover {
        opacity: 1;
    }

    .btn-clear-filters {
        font-size: 0.75rem;
        font-weight: 600;
        color: #dc3545;
        background: none;
        border: none;
        padding: 3px 8px;
        cursor: pointer;
        transition: all 0.15s;
        border-radius: 6px;
    }

    .btn-clear-filters:hover {
        background: #fff5f5;
    }

    @keyframes chipIn {
        from {
            transform: scale(0.85);
            opacity: 0;
        }

        to {
            transform: scale(1);
            opacity: 1;
        }
    }

    @media (max-width: 768px) {
        .filter-group {
            min-width: 140px;
        }
        #paginationWrapper {
            flex-direction: column;
            gap: 15px;
            text-align: center;
        }
    }
</style>


<div class="page-hero fadeup mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
        <div>
            <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.55);">HR Manager · Employees</div>
            <h4 class="text-white fw-bold mb-0 mt-1"><i class="fas fa-users me-2" style="color:#BD9414;"></i>All Employees</h4>
        </div>
        <div style="color:rgba(255,255,255,.6);font-size:.8rem;">
            <i class="fas fa-sync-alt me-1"></i>Data as of <?php echo date('F d, Y'); ?>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?php echo $employee_total; ?></div>
                        <div class="stat-label">Total Employees</div>
                    </div>
                    <i class="fas fa-id-badge stat-icon text-white-50"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?php echo $employee_active; ?></div>
                        <div class="stat-label">Active</div>
                    </div>
                    <i class="fas fa-check-circle stat-icon" style="color:#28a745;"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?php echo $employee_inactive; ?></div>
                        <div class="stat-label">Inactive</div>
                    </div>
                    <i class="fas fa-pause-circle stat-icon" style="color:#BD9414;"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value">4</div>
                        <div class="stat-label">Filters</div>
                    </div>
                    <i class="fas fa-filter stat-icon" style="color:#17a2b8;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="chart-card fadeup">
    <div class="cc-header">
        <h5 class="d-none d-md-block"><i class="fas fa-users me-2"></i>All Employees</h5>
        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="form-control form-control-sm" id="customSearchEmp"
                placeholder="Search employees...">
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="filter-toolbar" id="filterToolbar">
        <div class="filter-group">
            <label><i class="fas fa-briefcase me-1"></i>Job Title</label>
            <select id="filterJobTitle">
                <option value="">All Titles</option>
                <?php foreach ($job_titles_by_dept as $dept_name => $titles): ?>
                    <optgroup label="<?php echo e($dept_name); ?>">
                        <?php foreach ($titles as $jt): ?>
                            <option value="<?php echo e($jt['job_title']); ?>" 
                                    data-job-title-id="<?php echo $jt['job_title_id']; ?>" 
                                    data-department="<?php echo e($jt['department_name'] ?? ''); ?>">
                                <?php echo e($jt['job_title']); ?>
                                <?php if ($jt['employee_count'] > 0): ?>
                                    (<?php echo $jt['employee_count']; ?>)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label><i class="fas fa-sitemap me-1"></i>Department</label>
            <select id="filterDepartment">
                <option value="">All Departments</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?php echo e($dept); ?>"><?php echo e($dept); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label><i class="fas fa-building me-1"></i>Branch</label>
            <select id="filterBranch">
                <option value="">All Branches</option>
                <?php foreach ($branches as $br): ?>
                    <option value="<?php echo e($br); ?>" <?php echo ($selected_branch === $br) ? 'selected' : ''; ?>>
                        <?php echo e($br); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label><i class="fas fa-user-tag me-1"></i>Status</label>
            <select id="filterStatus">
                <option value="">All Statuses</option>
                <?php foreach ($statuses as $st): ?>
                    <option value="<?php echo e($st); ?>"><?php echo e($st); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Active Filter Chips -->
    <div class="filter-summary" id="filterSummary">
        <span class="filter-label"><i class="fas fa-filter me-1"></i>Filters:</span>
        <div id="filterChips"></div>
        <button class="btn-clear-filters" id="clearAllFilters" title="Clear all filters">
            <i class="fas fa-times me-1"></i>Clear All
        </button>
    </div>
    <div class="cc-body p-0">
        <!-- Desktop Table (hidden on mobile) -->
        <div class="table-responsive d-none d-md-block">
            <table class="table table-hover" id="empTable">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Name</th>
                        <th>Job Title</th>
                        <th>Department</th>
                        <th>Branch</th>
                        <th>Status</th>
                        <th>Hire Date</th>
                        <th style="min-width: 170px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $count = 1;
                    while ($emp = $employees->fetch_assoc()): ?>
                        <tr data-jobtitle="<?php echo e($emp['job_title']); ?>"
                            data-department="<?php echo e($emp['department_name'] ?? 'N/A'); ?>"
                            data-branch="<?php echo e($emp['branch_name'] ?? 'N/A'); ?>"
                            data-status="<?php echo e($emp['employment_status']); ?>"
                            style="display: none;">
                            <td data-label="#"><strong><?php echo $count++; ?></strong></td>
                            <td data-label="Name">
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo getEmployeeAvatar($emp['profile_picture']); ?>" alt="Profile"
                                        class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">
                                    <strong><?php echo e($emp['last_name'] . ', ' . $emp['first_name']); ?></strong>
                                </div>
                            </td>
                            <td data-label="Job Title"><?php echo e($emp['job_title']); ?></td>
                            <td data-label="Department"><?php echo e($emp['department_name'] ?? 'N/A'); ?></td>
                            <td data-label="Branch"><?php echo e($emp['branch_name'] ?? 'N/A'); ?></td>
                            <td data-label="Status">
                                <span class="badge <?php echo $emp['is_active'] ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo $emp['employment_status']; ?>
                                </span>
                            </td>
                            <td data-label="Hire Date"><small><?php echo formatDate($emp['hire_date']); ?></small></td>
                            <td data-label="Actions">
                                <div class="d-flex gap-1 align-items-center flex-nowrap">
                                    <a href="<?php echo BASE_URL; ?>/manager/view-employee.php?id=<?php echo $emp['employee_id']; ?>"
                                        class="btn btn-sm btn-outline-info" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/manager/edit-employee.php?id=<?php echo $emp['employee_id']; ?>"
                                        class="btn btn-sm btn-outline-primary employee-edit-link" data-base-href="<?php echo BASE_URL; ?>/manager/edit-employee.php?id=<?php echo $emp['employee_id']; ?>"
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($emp['is_active']): ?>
                                        <button type="button" class="btn btn-sm btn-outline-warning" title="Deactivate"
                                            onclick="setDeactivateTarget(<?php echo $emp['employee_id']; ?>, '<?php echo e(addslashes($emp['first_name'] . ' ' . $emp['last_name'])); ?>')"
                                            data-bs-toggle="modal" data-bs-target="#deactivateModal">
                                            <i class="fas fa-user-slash"></i>
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-sm btn-outline-success" title="Activate"
                                            onclick="setActivateTarget(<?php echo $emp['employee_id']; ?>, '<?php echo e(addslashes($emp['first_name'] . ' ' . $emp['last_name'])); ?>')"
                                            data-bs-toggle="modal" data-bs-target="#activateModal">
                                            <i class="fas fa-user-check"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Delete Permanently"
                                        onclick="setDeleteTarget(<?php echo $emp['employee_id']; ?>, '<?php echo e(addslashes($emp['first_name'] . ' ' . $emp['last_name'])); ?>')"
                                        data-bs-toggle="modal" data-bs-target="#deleteModal">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Card List (student check-in style, visible only on mobile) -->
        <div class="mobile-list-view d-block d-md-none p-3">
            <div class="student-list">
                <?php
                $employees->data_seek(0);
                $mob_count = 0;
                while ($emp = $employees->fetch_assoc()):
                    $initials = strtoupper(substr($emp['first_name'] ?? '', 0, 1) . substr($emp['last_name'] ?? '', 0, 1));
                    $avatar_num = ($mob_count % 6) + 1;
                    $mob_count++;
                ?>
                <div class="student-item"
                     data-jobtitle="<?php echo e($emp['job_title']); ?>"
                     data-department="<?php echo e($emp['department_name'] ?? 'N/A'); ?>"
                     data-branch="<?php echo e($emp['branch_name'] ?? 'N/A'); ?>"
                     data-status="<?php echo e($emp['employment_status']); ?>"
                     style="display: none;">
                        <div class="student-avatar">
                            <img src="<?php echo getEmployeeAvatar($emp['profile_picture']); ?>" alt="Profile" class="avatar-img">
                        </div>
                    <div class="student-info" style="flex:1;min-width:0;overflow:hidden;">
                        <div class="student-name"><?php echo e($emp['last_name'] . ', ' . $emp['first_name']); ?></div>
                        <div class="student-meta">
                            <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:100%;"><?php echo e($emp['job_title'] ?? 'N/A'); ?></span>
                            &bull; <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo e($emp['department_name'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="student-meta" style="margin-top:2px;">
                            <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo e($emp['branch_name'] ?? 'N/A'); ?></span>
                            &bull; <small><?php echo formatDate($emp['hire_date']); ?></small>
                        </div>
                    </div>
                    <div class="d-flex flex-column align-items-end gap-1" style="flex-shrink:0;margin-left:8px;">
                        <span class="badge <?php echo $emp['is_active'] ? 'bg-success' : 'bg-danger'; ?>" style="white-space:nowrap;font-size:0.68rem;">
                            <?php echo $emp['employment_status']; ?>
                        </span>
                        <div class="d-flex gap-1 flex-wrap justify-content-end">
                            <a href="<?php echo BASE_URL; ?>/manager/view-employee.php?id=<?php echo $emp['employee_id']; ?>" class="btn btn-xs btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                            <a href="<?php echo BASE_URL; ?>/manager/edit-employee.php?id=<?php echo $emp['employee_id']; ?>" class="btn btn-xs btn-outline-primary employee-edit-link" data-base-href="<?php echo BASE_URL; ?>/manager/edit-employee.php?id=<?php echo $emp['employee_id']; ?>" title="Edit"><i class="fas fa-edit"></i></a>
                            <?php if ($emp['is_active']): ?>
                            <button class="btn btn-xs btn-outline-warning" title="Deactivate"
                                onclick="setDeactivateTarget(<?php echo $emp['employee_id']; ?>, '<?php echo e(addslashes($emp['first_name'] . ' ' . $emp['last_name'])); ?>')"
                                data-bs-toggle="modal" data-bs-target="#deactivateModal"><i class="fas fa-user-slash"></i></button>
                            <?php else: ?>
                            <button class="btn btn-xs btn-outline-success" title="Activate"
                                onclick="setActivateTarget(<?php echo $emp['employee_id']; ?>, '<?php echo e(addslashes($emp['first_name'] . ' ' . $emp['last_name'])); ?>')"
                                data-bs-toggle="modal" data-bs-target="#activateModal"><i class="fas fa-user-check"></i></button>
                            <?php endif; ?>
                            <button class="btn btn-xs btn-outline-danger" title="Delete"
                                onclick="setDeleteTarget(<?php echo $emp['employee_id']; ?>, '<?php echo e(addslashes($emp['first_name'] . ' ' . $emp['last_name'])); ?>')"
                                data-bs-toggle="modal" data-bs-target="#deleteModal"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Pagination Controls -->
        <div class="d-flex justify-content-between align-items-center p-3 border-top" id="paginationWrapper">
            <div id="paginationInfo" class="text-muted small"></div>
            <ul class="pagination pagination-sm mb-0" id="paginationNumbers">
            </ul>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

<!-- Separation (Deactivate) Confirmation Modal -->
<div class="modal fade" id="deactivateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-user-slash me-2"></i>Employee Separation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <p>Select separation reason for <strong id="deactivateEmpName"></strong>:</p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Reason for Separation</label>
                    <select id="separationReason" class="form-select">
                        <option value="Regular">Regular (Temporary Deactivation)</option>
                        <option value="Separated">Separated (General)</option>
                        <option value="AWOL">AWOL</option>
                        <option value="Retirement">Retirement</option>
                        <option value="Death">Death</option>
                        <option value="Permanent of Total Disability">Permanent of Total Disability</option>
                        <option value="Resignation">Resignation</option>
                        <option value="Failed in Training">Failed in Training</option>
                        <option value="Termination for Cause">Termination for Cause</option>
                    </select>
                </div>
                <p class="text-muted small text-center"><i class="fas fa-info-circle me-1"></i>This will mark the employee as inactive and update their status.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="deactivateConfirmBtn" class="btn btn-warning">
                    <i class="fas fa-user-slash me-1"></i>Confirm Separation
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Activate Confirmation Modal -->
<div class="modal fade" id="activateModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-user-check me-2"></i>Activate Employee</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p>Reactivate <strong id="activateEmpName"></strong>?</p>
                <p class="text-muted small">This will mark them as an active employee again and reset their status to <strong>Regular</strong>.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="activateConfirmBtn" class="btn btn-success"><i
                        class="fas fa-user-check me-1"></i>Activate</a>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Delete Employee</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p>Permanently delete <strong id="deleteEmpName"></strong>?</p>
                <p class="text-danger small"><i class="fas fa-exclamation-circle me-1"></i>This will remove all their
                    records including evaluations. This cannot be undone!</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="deleteConfirmBtn" class="btn btn-danger"><i class="fas fa-trash me-1"></i>Delete
                    Permanently</a>
            </div>
        </div>
    </div>
</div>

<script>
    let deactivateTargetId = null;
    function setDeactivateTarget(id, name) {
        deactivateTargetId = id;
        document.getElementById('deactivateEmpName').textContent = name;
    }

    document.getElementById('deactivateConfirmBtn').addEventListener('click', function() {
        const reason = document.getElementById('separationReason').value;
        if (deactivateTargetId) {
            window.location.href = '?deactivate=' + deactivateTargetId + '&status=' + encodeURIComponent(reason);
        }
    });
    function setActivateTarget(id, name) {
        document.getElementById('activateEmpName').textContent = name;
        document.getElementById('activateConfirmBtn').href = '?activate=' + id;
    }
    function setDeleteTarget(id, name) {
        document.getElementById('deleteEmpName').textContent = name;
        document.getElementById('deleteConfirmBtn').href = '?delete=' + id;
    }

    // State Variables
    let currentPage = 1;
    const ITEMS_PER_PAGE = 10;

    document.getElementById('customSearchEmp').addEventListener('input', function () {
        currentPage = 1;
        syncFiltersToUrl();
        renderTable();
    });

    // --- Dropdown Filter Logic ---
    const filterSelects = ['filterJobTitle', 'filterDepartment', 'filterBranch', 'filterStatus'];
    const filterLabels = { filterJobTitle: 'Job Title', filterDepartment: 'Department', filterBranch: 'Branch', filterStatus: 'Status' };
    const filterParams = { filterJobTitle: 'job_title', filterDepartment: 'department', filterBranch: 'branch', filterStatus: 'status' };

    function applyFiltersFromUrl() {
        const params = new URLSearchParams(window.location.search);
        document.getElementById('customSearchEmp').value = params.get('search') || '';
        filterSelects.forEach(id => {
            const el = document.getElementById(id);
            const value = params.get(filterParams[id]) || '';
            el.value = value;
            el.classList.toggle('active-filter', value !== '');
        });
        
        // Apply department filter to job title dropdown on page load
        const selectedDept = params.get('department') || '';
        if (selectedDept !== '') {
            const jobTitleSelect = document.getElementById('filterJobTitle');
            const optgroups = jobTitleSelect.querySelectorAll('optgroup');
            optgroups.forEach(group => {
                group.style.display = group.label === selectedDept ? '' : 'none';
            });
        }
    }

    function syncFiltersToUrl() {
        const params = new URLSearchParams();
        const search = document.getElementById('customSearchEmp').value.trim();
        if (search !== '') params.set('search', search);

        filterSelects.forEach(id => {
            const value = document.getElementById(id).value;
            if (value !== '') params.set(filterParams[id], value);
        });

        const query = params.toString();
        const nextUrl = window.location.pathname + (query ? '?' + query : '');
        history.replaceState(null, '', nextUrl);
        updateEmployeeActionLinks();
    }

    function updateEmployeeActionLinks() {
        const returnUrl = window.location.pathname + window.location.search;
        document.querySelectorAll('.employee-edit-link').forEach(link => {
            link.href = link.dataset.baseHref + '&return=' + encodeURIComponent(returnUrl);
        });
    }

    // Special handler for Department filter to update Job Title options
    document.getElementById('filterDepartment').addEventListener('change', function () {
        const selectedDept = this.value;
        const jobTitleSelect = document.getElementById('filterJobTitle');
        const optgroups = jobTitleSelect.querySelectorAll('optgroup');
        
        optgroups.forEach(group => {
            if (selectedDept === '') {
                // Show all optgroups
                group.style.display = '';
            } else {
                // Show only matching department optgroup
                group.style.display = group.label === selectedDept ? '' : 'none';
            }
        });
        
        // Clear job title filter when department changes
        jobTitleSelect.value = '';
        jobTitleSelect.classList.remove('active-filter');
        
        currentPage = 1;
        this.classList.toggle('active-filter', this.value !== '');
        syncFiltersToUrl();
        renderTable();
        updateFilterChips();
    });

    // Regular handlers for other filters
    ['filterJobTitle', 'filterBranch', 'filterStatus'].forEach(id => {
        document.getElementById(id).addEventListener('change', function () {
            currentPage = 1;
            this.classList.toggle('active-filter', this.value !== '');
            syncFiltersToUrl();
            renderTable();
            updateFilterChips();
        });
    });

    function updateFilterChips() {
        const chipsContainer = document.getElementById('filterChips');
        const summary = document.getElementById('filterSummary');
        let html = '';
        let hasAny = false;

        filterSelects.forEach(id => {
            const el = document.getElementById(id);
            if (el.value !== '') {
                hasAny = true;
                html += `<span class="filter-chip"><span class="chip-category">${filterLabels[id]}:</span> ${el.value} <i class="fas fa-times remove-chip" data-filter="${id}"></i></span>`;
            }
        });

        chipsContainer.innerHTML = html;
        summary.classList.toggle('has-filters', hasAny);

        // Bind remove chip clicks
        chipsContainer.querySelectorAll('.remove-chip').forEach(btn => {
            btn.addEventListener('click', function () {
                const filterId = this.dataset.filter;
                const select = document.getElementById(filterId);
                select.value = '';
                select.classList.remove('active-filter');
                
                // If removing Department filter, show all Job Title optgroups
                if (filterId === 'filterDepartment') {
                    const jobTitleSelect = document.getElementById('filterJobTitle');
                    const optgroups = jobTitleSelect.querySelectorAll('optgroup');
                    optgroups.forEach(group => {
                        group.style.display = '';
                    });
                }
                
                currentPage = 1;
                syncFiltersToUrl();
                renderTable();
                updateFilterChips();
            });
        });
    }

    document.getElementById('clearAllFilters').addEventListener('click', function () {
        filterSelects.forEach(id => {
            const el = document.getElementById(id);
            el.value = '';
            el.classList.remove('active-filter');
        });
        
        // Show all job title optgroups again
        const jobTitleSelect = document.getElementById('filterJobTitle');
        const optgroups = jobTitleSelect.querySelectorAll('optgroup');
        optgroups.forEach(group => {
            group.style.display = '';
        });
        
        currentPage = 1;
        syncFiltersToUrl();
        renderTable();
        updateFilterChips();
    });

    function goToPage(page) {
        currentPage = page;
        renderTable();
    }

    function renderTable() {
        const tbody = document.querySelector("#empTable tbody");
        const allRows = Array.from(tbody.querySelectorAll("tr:not(.no-results-row)"));
        const filterInput = document.getElementById('customSearchEmp').value.toLowerCase().trim();

        // Get dropdown filter values
        const fJobTitle = document.getElementById('filterJobTitle').value;
        const fDepartment = document.getElementById('filterDepartment').value;
        const fBranch = document.getElementById('filterBranch').value;
        const fStatus = document.getElementById('filterStatus').value;

        let visibleRows = [];

        // 1. Filter desktop rows (text search + dropdown filters)
        allRows.forEach(row => {
            const cells = Array.from(row.querySelectorAll("td"));
            if (cells.length > 1) {
                // Text search
                const rowText = cells.slice(0, 6).map(td => td.textContent.trim().replace(/\s+/g, ' ')).join(' ').toLowerCase();
                const textMatch = filterInput === "" || rowText.includes(filterInput);

                // Dropdown filters
                const dropdownMatch =
                    (fJobTitle === '' || row.dataset.jobtitle === fJobTitle) &&
                    (fDepartment === '' || row.dataset.department === fDepartment) &&
                    (fBranch === '' || row.dataset.branch === fBranch) &&
                    (fStatus === '' || row.dataset.status === fStatus);

                if (textMatch && dropdownMatch) {
                    visibleRows.push(row);
                    row.classList.remove('filtered-out');
                } else {
                    row.classList.add('filtered-out');
                    row.style.display = "none";
                }
            }
        });

        // Filter mobile card items
        const mobileList = document.querySelector(".mobile-list-view .student-list");
        const allCards = mobileList ? Array.from(mobileList.querySelectorAll(".student-item")) : [];
        let visibleCards = [];
        allCards.forEach(card => {
            const cardText = card.textContent.toLowerCase();
            const textMatch = filterInput === "" || cardText.includes(filterInput);
            const dropdownMatch =
                (fJobTitle === '' || card.dataset.jobtitle === fJobTitle) &&
                (fDepartment === '' || card.dataset.department === fDepartment) &&
                (fBranch === '' || card.dataset.branch === fBranch) &&
                (fStatus === '' || card.dataset.status === fStatus);

            if (textMatch && dropdownMatch) {
                visibleCards.push(card);
            } else {
                card.style.display = "none";
            }
        });

        // 2. Paginate
        const totalPages = Math.ceil(visibleRows.length / ITEMS_PER_PAGE);
        if (currentPage > totalPages && totalPages > 0) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        const startIdx = (currentPage - 1) * ITEMS_PER_PAGE;
        const endIdx = startIdx + ITEMS_PER_PAGE;

        // Paginate desktop rows
        let visibleCount = 0;
        visibleRows.forEach((row, index) => {
            if (index >= startIdx && index < endIdx) {
                row.style.display = "";
                row.classList.remove('odd-row', 'even-row');
                if (visibleCount % 2 === 0) {
                    row.classList.add('odd-row');
                } else {
                    row.classList.add('even-row');
                }
                visibleCount++;
            } else {
                row.style.display = "none";
            }
        });

        // Paginate mobile cards
        visibleCards.forEach((card, index) => {
            if (index >= startIdx && index < endIdx) {
                card.style.display = "";
            } else {
                card.style.display = "none";
            }
        });

        updatePaginationUI(visibleRows.length, totalPages);
        handleNoResults(visibleRows.length, filterInput, tbody);
    }

    function updatePaginationUI(totalItems, totalPages) {
        const info = document.getElementById("paginationInfo");
        const digits = document.getElementById("paginationNumbers");
        if (!info || !digits) return;

        if (totalItems === 0) {
            info.innerHTML = "Showing 0 entries";
            digits.innerHTML = "";
            return;
        }

        const start = (currentPage - 1) * ITEMS_PER_PAGE + 1;
        const end = Math.min(currentPage * ITEMS_PER_PAGE, totalItems);
        info.innerHTML = `Showing ${start} to ${end} of ${totalItems} entries`;

        let html = "";

        // Previous Button
        html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <button class="page-link" onclick="goToPage(${currentPage - 1})">Previous</button>
             </li>`;

        // Page Numbers (Show max 5 pagination buttons for cleaner look if many pages)
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, startPage + 4);
        if (endPage - startPage < 4) {
            startPage = Math.max(1, endPage - 4);
        }

        if (startPage > 1) {
            html += `<li class="page-item"><button class="page-link" onclick="goToPage(1)">1</button></li>`;
            if (startPage > 2) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }
        for (let i = startPage; i <= endPage; i++) {
            html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                    <button class="page-link" onclick="goToPage(${i})">${i}</button>
                 </li>`;
        }
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
            html += `<li class="page-item"><button class="page-link" onclick="goToPage(${totalPages})">${totalPages}</button></li>`;
        }

        // Next Button
        html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <button class="page-link" onclick="goToPage(${currentPage + 1})">Next</button>
             </li>`;

        digits.innerHTML = html;
    }

    function handleNoResults(totalItems, filterInput, tbody) {
        const hasDropdownFilter = filterSelects.some(id => document.getElementById(id).value !== '');
        let noResultsRow = tbody.querySelector('.no-results-row');
        if (totalItems === 0 && (filterInput !== "" || hasDropdownFilter)) {
            if (!noResultsRow) {
                noResultsRow = document.createElement('tr');
                noResultsRow.className = 'no-results-row text-center';
                tbody.appendChild(noResultsRow);
            }
            let msg = 'No employees match the current filters.';
            if (filterInput !== '') msg = `No employees found matching "<strong>${filterInput}</strong>"`;
            noResultsRow.innerHTML = `<td colspan="8" class="py-4 text-muted"><i class="fas fa-filter fa-2x mb-3 d-block" style="opacity:0.2;"></i>${msg}</td>`;
            noResultsRow.style.display = '';
        } else if (noResultsRow) {
            noResultsRow.remove();
        }
    }

    // Initial Render on Load
    document.addEventListener("DOMContentLoaded", function () {
        applyFiltersFromUrl();
        updateEmployeeActionLinks();
        renderTable();
        updateFilterChips();
    });
</script>
