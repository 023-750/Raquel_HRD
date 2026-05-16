<?php
$page_title = 'Position Management';
require_once '../includes/session-check.php';
checkRole(['HR Manager']);
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_position' || $action === 'edit_position') {
        $position_name = trim($_POST['position_name'] ?? '');
        $department_id = !empty($_POST['department_id']) ? (int) $_POST['department_id'] : null;
        $rank_category_id = !empty($_POST['rank_category_id']) ? (int) $_POST['rank_category_id'] : null;
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $is_head = isset($_POST['is_head']) ? 1 : 0;
        $reports_to = !empty($_POST['reports_to']) ? (int) $_POST['reports_to'] : null;

        if ($position_name === '') {
            redirectWith(BASE_URL . '/manager/positions.php', 'danger', 'Position name is required.');
        }

        $dupSql = "SELECT job_title_id FROM job_titles WHERE job_title = ? AND ";
        $dupSql .= $department_id === null ? "department_id IS NULL" : "department_id = ?";
        if ($action === 'edit_position') {
            $dupSql .= " AND job_title_id != ?";
        }
        $dupStmt = $conn->prepare($dupSql);
        if ($department_id === null && $action === 'edit_position') {
            $position_id = (int) ($_POST['position_id'] ?? 0);
            $dupStmt->bind_param("si", $position_name, $position_id);
        } elseif ($department_id === null) {
            $dupStmt->bind_param("s", $position_name);
        } elseif ($action === 'edit_position') {
            $position_id = (int) ($_POST['position_id'] ?? 0);
            $dupStmt->bind_param("sii", $position_name, $department_id, $position_id);
        } else {
            $dupStmt->bind_param("si", $position_name, $department_id);
        }
        $dupStmt->execute();
        if ($dupStmt->get_result()->num_rows > 0) {
            $dupStmt->close();
            redirectWith(BASE_URL . '/manager/positions.php', 'danger', 'That position already exists for the selected department.');
        }
        $dupStmt->close();

        if ($action === 'add_position') {
            $stmt = $conn->prepare("INSERT INTO job_titles (job_title, department_id, rank_category_id, is_active, is_head, reports_to) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("siiiii", $position_name, $department_id, $rank_category_id, $is_active, $is_head, $reports_to);
            $stmt->execute();
            $new_id = $stmt->insert_id;
            $stmt->close();
            logAudit($conn, $_SESSION['user_id'], 'CREATE', 'Position', $new_id, "Added position: $position_name");
            redirectWith(BASE_URL . '/manager/positions.php', 'success', "Position \"$position_name\" added successfully.");
        }

        $position_id = (int) ($_POST['position_id'] ?? 0);
        if ($position_id <= 0) {
            redirectWith(BASE_URL . '/manager/positions.php', 'danger', 'Invalid position selected.');
        }

        $stmt = $conn->prepare("UPDATE job_titles SET job_title = ?, department_id = ?, rank_category_id = ?, is_active = ?, is_head = ?, reports_to = ? WHERE job_title_id = ?");
        $stmt->bind_param("siiiiii", $position_name, $department_id, $rank_category_id, $is_active, $is_head, $reports_to, $position_id);
        $stmt->execute();
        $stmt->close();
        logAudit($conn, $_SESSION['user_id'], 'UPDATE', 'Position', $position_id, "Updated position: $position_name");
        redirectWith(BASE_URL . '/manager/positions.php', 'success', "Position \"$position_name\" updated successfully.");
    }
}

if (isset($_GET['delete_position']) && is_numeric($_GET['delete_position'])) {
    $position_id = (int) $_GET['delete_position'];
    $stmt = $conn->prepare("SELECT job_title FROM job_titles WHERE job_title_id = ?");
    $stmt->bind_param("i", $position_id);
    $stmt->execute();
    $position = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$position) {
        redirectWith(BASE_URL . '/manager/positions.php', 'danger', 'Position not found.');
    }

    $position_name = $position['job_title'];
    $check = $conn->prepare("
        SELECT COUNT(*) AS cnt
        FROM employees
        WHERE job_title_id = ?
           OR (job_title = ? AND (job_title_id IS NULL OR job_title_id = 0))
    ");
    $check->bind_param("is", $position_id, $position_name);
    $check->execute();
    $assigned_count = (int) $check->get_result()->fetch_assoc()['cnt'];
    $check->close();

    if ($assigned_count > 0) {
        redirectWith(BASE_URL . '/manager/positions.php', 'danger', "Cannot delete \"$position_name\" because $assigned_count employee(s) are using it.");
    }

    $stmt = $conn->prepare("DELETE FROM job_titles WHERE job_title_id = ?");
    $stmt->bind_param("i", $position_id);
    $stmt->execute();
    $stmt->close();
    logAudit($conn, $_SESSION['user_id'], 'DELETE', 'Position', $position_id, "Deleted position: $position_name");
    redirectWith(BASE_URL . '/manager/positions.php', 'success', "Position \"$position_name\" deleted successfully.");
}

require_once '../includes/header.php';

$departments = $conn->query("SELECT department_id, department_name FROM departments ORDER BY department_name")->fetch_all(MYSQLI_ASSOC);
$rankCategories = $conn->query("SELECT rank_category_id, rank_name FROM rank_categories WHERE is_active = 1 ORDER BY level_order, rank_name")->fetch_all(MYSQLI_ASSOC);

$positions = $conn->query("
    SELECT jt.*,
           d.department_name,
           rc.rank_name,
           parent.job_title AS reports_to_title,
           (
               SELECT COUNT(*)
               FROM employees e
               WHERE e.job_title_id = jt.job_title_id
                  OR (e.job_title = jt.job_title AND (e.job_title_id IS NULL OR e.job_title_id = 0))
           ) AS employee_count
    FROM job_titles jt
    LEFT JOIN departments d ON jt.department_id = d.department_id
    LEFT JOIN rank_categories rc ON jt.rank_category_id = rc.rank_category_id
    LEFT JOIN job_titles parent ON jt.reports_to = parent.job_title_id
    ORDER BY jt.job_title
");

$positionCount = $positions->num_rows;
$activePositionCount = (int) $conn->query("SELECT COUNT(*) AS cnt FROM job_titles WHERE is_active = 1")->fetch_assoc()['cnt'];
$inactivePositionCount = max(0, $positionCount - $activePositionCount);
$employeesWithManagedPositions = (int) $conn->query("SELECT COUNT(*) AS cnt FROM employees WHERE job_title_id IS NOT NULL")->fetch_assoc()['cnt'];
?>

<style>
    .btn-head {
        background: linear-gradient(135deg, #BD9414 0%, #E2B11B 100%);
        color: #fff;
        border: none;
        padding: 0.5rem 1.25rem;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-head:hover {
        background: linear-gradient(135deg, #A17F11 0%, #C49B17 100%);
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(189, 148, 20, 0.4);
    }

    .btn-staff {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        color: #fff;
        border: none;
        padding: 0.5rem 1.25rem;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-staff:hover {
        background: linear-gradient(135deg, #2e59d9 0%, #1e3bb3 100%);
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(78, 115, 223, 0.4);
    }

    .modal-header-head {
        background: linear-gradient(to right, #BD9414, #E2B11B) !important;
        color: #fff !important;
        border-bottom: none;
    }

    .modal-header-staff {
        background: linear-gradient(to right, #4e73df, #224abe) !important;
        color: #fff !important;
        border-bottom: none;
    }

    @media (max-width: 768px) {
        .table-responsive {
            border: none;
        }

        .responsive-position-table thead {
            display: none;
        }

        .responsive-position-table tr {
            display: block;
            background: #fff;
            border-radius: 15px;
            margin-bottom: 18px;
            padding: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #f0f0f0;
        }

        .responsive-position-table td {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: center;
            border: none;
            padding: 9px 0;
            text-align: right;
            font-size: 0.9rem;
        }

        .responsive-position-table td::before {
            content: attr(data-label);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.7rem;
            color: var(--text-muted);
            text-align: left;
        }

        .responsive-position-table td:first-child::before,
        .responsive-position-table td:last-child::before {
            display: none;
        }
    }
</style>

<div class="page-hero fadeup">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
        <div>
            <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.55);">HR
                Manager · Organization</div>
            <h4 class="text-white fw-bold mb-0 mt-1"><i class="fas fa-briefcase me-2"
                    style="color:#BD9414;"></i>Position Management</h4>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <div class="d-flex gap-2">
                <button class="btn btn-head" onclick="openAddPositionModal('general')">
                    <i class="fas fa-plus me-1"></i>Add Position
                </button>
                <button class="btn btn-staff" onclick="openAddPositionModal('staff')">
                    <i class="fas fa-users me-1"></i>Add to Direct Report
                </button>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?php echo $positionCount; ?></div>
                        <div class="stat-label">Positions</div>
                    </div>
                    <i class="fas fa-briefcase stat-icon text-white-50"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?php echo $activePositionCount; ?></div>
                        <div class="stat-label">Active Positions</div>
                    </div>
                    <i class="fas fa-check-circle stat-icon" style="color:#28a745;"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?php echo $inactivePositionCount; ?></div>
                        <div class="stat-label">Inactive Positions</div>
                    </div>
                    <i class="fas fa-ban stat-icon" style="color:#6c757d;"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?php echo $employeesWithManagedPositions; ?></div>
                        <div class="stat-label">Employees Linked</div>
                    </div>
                    <i class="fas fa-users stat-icon" style="color:#17a2b8;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="fadeup-2 mb-4">
    <div class="chart-card">
        <div class="cc-header">
            <div>
                <h5><i class="fas fa-briefcase me-2"></i>All Positions</h5>
                <small class="text-muted">These positions feed the employee job title dropdowns.</small>
            </div>
            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="form-control form-control-sm" id="searchPosition"
                    placeholder="Search positions...">
            </div>
        </div>
        <div class="cc-body p-0">
            <div class="table-responsive">
                <table class="table table-hover responsive-position-table" id="positionTable">
                    <thead>
                        <tr>
                            <th>Position</th>
                            <th>Department</th>
                            <th>Rank</th>
                            <th>Head</th>
                            <th>Direct Report</th>
                            <th>Employees</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($positionCount === 0): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-briefcase fa-2x mb-2 d-block" style="opacity:0.3;"></i>
                                    No positions found. Add the missing positions here so they can be used in employee
                                    records.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php while ($position = $positions->fetch_assoc()): ?>
                                <tr>
                                    <td data-label="Position">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle me-2 text-white d-flex align-items-center justify-content-center"
                                                style="width:32px;height:32px;background:var(--primary-blue);font-size:0.8rem;">
                                                <i class="fas fa-id-badge" style="font-size:0.75rem;"></i>
                                            </div>
                                            <strong><?php echo e($position['job_title']); ?></strong>
                                        </div>
                                    </td>
                                    <td data-label="Department"><?php echo e($position['department_name'] ?: 'Unassigned'); ?>
                                    </td>
                                    <td data-label="Rank"><?php echo e($position['rank_name'] ?: 'Unassigned'); ?></td>
                                    <td data-label="Head">
                                        <?php if ((int) $position['is_head'] === 1): ?>
                                            <span class="badge bg-primary"><i class="fas fa-crown me-1"></i>Head</span>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Direct Report">
                                        <small><?php echo e($position['reports_to_title'] ?: 'None'); ?></small>
                                    </td>
                                    <td data-label="Employees"><span
                                            class="badge bg-info"><?php echo (int) $position['employee_count']; ?></span></td>
                                    <td data-label="Status">
                                        <span
                                            class="badge <?php echo (int) $position['is_active'] === 1 ? 'bg-success' : 'bg-secondary'; ?>">
                                            <?php echo (int) $position['is_active'] === 1 ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td data-label="Actions">
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#editPositionModal" onclick="openEditPositionModal(
                                                <?php echo (int) $position['job_title_id']; ?>,
                                                '<?php echo e(addslashes($position['job_title'])); ?>',
                                                '<?php echo (int) ($position['department_id'] ?? 0); ?>',
                                                '<?php echo (int) ($position['rank_category_id'] ?? 0); ?>',
                                                '<?php echo (int) $position['is_active']; ?>',
                                                '<?php echo (int) $position['is_head']; ?>',
                                                '<?php echo (int) ($position['reports_to'] ?? 0); ?>'
                                            )">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                            data-bs-target="#deletePositionModal" onclick="setDeletePositionTarget(
                                                <?php echo (int) $position['job_title_id']; ?>,
                                                '<?php echo e(addslashes($position['job_title'])); ?>',
                                                <?php echo (int) $position['employee_count']; ?>
                                            )">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addPositionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="action" value="add_position">
                <div class="modal-header bg-primary text-white" id="addPositionModalHeader">
                    <h5 class="modal-title" id="addPositionModalTitle"><i class="fas fa-plus me-2"></i>Add Position</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Position Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="position_name" required
                            placeholder="e.g. Regional Manager">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <select class="form-select" name="department_id" id="addPositionDepartment">
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $department): ?>
                                <option value="<?php echo (int) $department['department_id']; ?>">
                                    <?php echo e($department['department_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rank</label>
                        <select class="form-select" name="rank_category_id">
                            <option value="">Select Rank</option>
                            <?php foreach ($rankCategories as $rank): ?>
                                <option value="<?php echo (int) $rank['rank_category_id']; ?>">
                                    <?php echo e($rank['rank_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3" id="addPositionReportsToContainer">
                        <label class="form-label">Direct Report (Reports To)</label>
                        <select class="form-select" name="reports_to" id="addPositionReportsTo">
                            <option value="">No Reporting Line</option>
                            <?php
                            $positions->data_seek(0);
                            while ($p = $positions->fetch_assoc()): ?>
                                <option value="<?php echo (int) $p['job_title_id']; ?>"
                                    data-department="<?php echo (int) ($p['department_id'] ?? 0); ?>">
                                    <?php echo e($p['job_title']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="d-flex gap-3">
                        <div class="form-check form-switch" id="addPositionHeadContainer">
                            <input class="form-check-input" type="checkbox" name="is_head" id="addPositionHead"
                                value="1">
                            <label class="form-check-label" for="addPositionHead">Is Head?</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="addPositionActive"
                                checked>
                            <label class="form-check-label" for="addPositionActive">Active Position</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="addPositionSubmitBtn">Add Position</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editPositionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="action" value="edit_position">
                <input type="hidden" name="position_id" id="editPositionId">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Position</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Position Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="position_name" id="editPositionName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <select class="form-select" name="department_id" id="editPositionDepartment">
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $department): ?>
                                <option value="<?php echo (int) $department['department_id']; ?>">
                                    <?php echo e($department['department_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rank</label>
                        <select class="form-select" name="rank_category_id" id="editPositionRank">
                            <option value="">Select Rank</option>
                            <?php foreach ($rankCategories as $rank): ?>
                                <option value="<?php echo (int) $rank['rank_category_id']; ?>">
                                    <?php echo e($rank['rank_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Direct Report (Reports To)</label>
                        <select class="form-select" name="reports_to" id="editPositionReportsTo">
                            <option value="">No Reporting Line</option>
                            <?php
                            $positions->data_seek(0);
                            while ($p = $positions->fetch_assoc()): ?>
                                <option value="<?php echo (int) $p['job_title_id']; ?>"
                                    data-department="<?php echo (int) ($p['department_id'] ?? 0); ?>">
                                    <?php echo e($p['job_title']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="d-flex gap-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_head" id="editPositionHead">
                            <label class="form-check-label" for="editPositionHead">Is Head?</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="editPositionActive">
                            <label class="form-check-label" for="editPositionActive">Active Position</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary w-100">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deletePositionModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-trash me-2"></i>Delete Position</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p>Delete <strong id="deletePositionName"></strong>?</p>
                <div id="deletePositionWarning" class="text-danger small"></div>
            </div>
            <div class="modal-footer justify-content-center">
                <a href="#" id="deletePositionConfirmBtn" class="btn btn-danger w-100">Delete</a>
            </div>
        </div>
    </div>
</div>

<script>
    function openEditPositionModal(id, name, departmentId, rankId, isActive, isHead, reportsTo) {
        document.getElementById('editPositionId').value = id;
        document.getElementById('editPositionName').value = name;
        document.getElementById('editPositionDepartment').value = departmentId || '';
        document.getElementById('editPositionRank').value = rankId || '';
        document.getElementById('editPositionActive').checked = Number(isActive) === 1;
        document.getElementById('editPositionHead').checked = Number(isHead) === 1;

        // Filter reports to based on department before setting value
        filterReportsTo('editPositionDepartment', 'editPositionReportsTo');
        document.getElementById('editPositionReportsTo').value = reportsTo || '';
    }

    function openAddPositionModal(type) {
        const modal = new bootstrap.Modal(document.getElementById('addPositionModal'));
        const title = document.getElementById('addPositionModalTitle');
        const header = document.getElementById('addPositionModalHeader');
        const submitBtn = document.getElementById('addPositionSubmitBtn');
        const isHeadCheckbox = document.getElementById('addPositionHead');
        const isHeadContainer = document.getElementById('addPositionHeadContainer');
        const reportsToContainer = document.getElementById('addPositionReportsToContainer');
        const reportsToSelect = document.getElementById('addPositionReportsTo');

        // Reset form
        document.getElementById('addPositionModal').querySelector('form').reset();

        if (type === 'general') {
            title.innerHTML = '<i class="fas fa-plus me-2"></i>Add Position';
            header.className = 'modal-header modal-header-head';
            submitBtn.className = 'btn btn-head w-100';
            isHeadCheckbox.checked = false;
            isHeadContainer.style.display = 'block';
            reportsToContainer.style.display = 'block';
        } else {
            title.innerHTML = '<i class="fas fa-users me-2"></i>Add to Direct Report';
            header.className = 'modal-header modal-header-staff';
            submitBtn.className = 'btn btn-staff w-100';
            isHeadCheckbox.checked = false;
            isHeadContainer.style.display = 'none';
            // Show "Reports To" for staff
            reportsToContainer.style.display = 'block';
        }

        modal.show();
    }

    function setDeletePositionTarget(id, name, employeeCount) {
        document.getElementById('deletePositionName').textContent = name;
        const warning = document.getElementById('deletePositionWarning');
        const button = document.getElementById('deletePositionConfirmBtn');

        if (employeeCount > 0) {
            warning.innerHTML = `<i class="fas fa-exclamation-triangle me-1"></i>Cannot delete because ${employeeCount} employee(s) are using this position.`;
            button.classList.add('disabled');
            button.href = '#';
        } else {
            warning.textContent = 'This action cannot be undone.';
            button.classList.remove('disabled');
            button.href = '?delete_position=' + id;
        }
    }

    function attachTableSearch(inputId, tableId) {
        const input = document.getElementById(inputId);
        const table = document.getElementById(tableId);
        if (!input || !table) return;

        input.addEventListener('input', function () {
            const filter = this.value.toLowerCase().trim();
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach((row) => {
                row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
            });
        });
    }

    function filterReportsTo(deptSelectId, reportsToSelectId) {
        const deptSelect = document.getElementById(deptSelectId);
        const reportsToSelect = document.getElementById(reportsToSelectId);
        if (!deptSelect || !reportsToSelect) return;

        const selectedDept = deptSelect.value;
        const options = reportsToSelect.querySelectorAll('option');

        options.forEach(option => {
            if (option.value === "") {
                option.style.display = ""; // Always show "No Reporting Line"
                return;
            }

            const optDept = option.getAttribute('data-department');
            if (selectedDept === "" || optDept === selectedDept) {
                option.style.display = "";
            } else {
                option.style.display = "none";
                if (reportsToSelect.value === option.value) {
                    reportsToSelect.value = "";
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        attachTableSearch('searchPosition', 'positionTable');

        const addDept = document.getElementById('addPositionDepartment');
        if (addDept) {
            addDept.addEventListener('change', () => filterReportsTo('addPositionDepartment', 'addPositionReportsTo'));
        }

        const editDept = document.getElementById('editPositionDepartment');
        if (editDept) {
            editDept.addEventListener('change', () => filterReportsTo('editPositionDepartment', 'editPositionReportsTo'));
        }
    });
</script>

<?php require_once '../includes/footer.php'; ?>