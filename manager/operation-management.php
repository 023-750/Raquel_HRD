<?php
$page_title = 'Operation Management';
require_once '../includes/session-check.php';
checkRole(['HR Manager']);
require_once '../includes/functions.php';

function parseDelimitedList(?string $value, string $delimiter = '||'): array
{
    if ($value === null || $value === '') {
        return [];
    }

    return array_values(array_filter(array_map('trim', explode($delimiter, $value)), static function ($item) {
        return $item !== '';
    }));
}

function jsonAttributeValue($value): string
{
    return htmlspecialchars(json_encode($value), ENT_QUOTES, 'UTF-8');
}

$conn->query("
    CREATE TABLE IF NOT EXISTS organization_structure_entries (
        entry_id INT AUTO_INCREMENT PRIMARY KEY,
        division_name VARCHAR(150) NOT NULL DEFAULT 'Operation Management',
        region_name VARCHAR(150) NOT NULL,
        area_name VARCHAR(150) NULL,
        branch_no VARCHAR(30) NULL,
        branch_id INT NOT NULL,
        area_supervisor_employee_id INT NULL,
        regional_manager_employee_id INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT fk_org_structure_branch
            FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE CASCADE,
        CONSTRAINT fk_org_structure_area_supervisor
            FOREIGN KEY (area_supervisor_employee_id) REFERENCES employees(employee_id) ON DELETE SET NULL,
        CONSTRAINT fk_org_structure_regional_manager
            FOREIGN KEY (regional_manager_employee_id) REFERENCES employees(employee_id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

$conn->query("
    CREATE TABLE IF NOT EXISTS organization_structure_entry_branches (
        entry_id INT NOT NULL,
        branch_id INT NOT NULL,
        PRIMARY KEY (entry_id, branch_id),
        CONSTRAINT fk_org_structure_entry_branch_entry
            FOREIGN KEY (entry_id) REFERENCES organization_structure_entries(entry_id) ON DELETE CASCADE,
        CONSTRAINT fk_org_structure_entry_branch_branch
            FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

// Preserve existing single-branch rows by mirroring them into the branch link table.
$conn->query("
    INSERT IGNORE INTO organization_structure_entry_branches (entry_id, branch_id)
    SELECT entry_id, branch_id
    FROM organization_structure_entries
    WHERE branch_id IS NOT NULL
");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_structure' || $action === 'edit_structure') {
        $division_name = 'Operation Management';
        $region_name = trim($_POST['region_name'] ?? '');
        $area_name = trim($_POST['area_name'] ?? '');
        $branch_no = '';
        $submittedBranchIds = $_POST['branch_ids'] ?? [];
        if (!is_array($submittedBranchIds) && !empty($_POST['branch_id'])) {
            $submittedBranchIds = [$_POST['branch_id']];
        }
        $branch_ids = array_values(array_unique(array_filter(array_map('intval', $submittedBranchIds))));
        $primary_branch_id = $branch_ids[0] ?? 0;
        $area_supervisor_employee_id = !empty($_POST['area_supervisor_employee_id']) ? (int) $_POST['area_supervisor_employee_id'] : null;
        $regional_manager_employee_id = !empty($_POST['regional_manager_employee_id']) ? (int) $_POST['regional_manager_employee_id'] : null;

        if ($region_name === '' || empty($branch_ids)) {
            redirectWith(BASE_URL . '/manager/operation-management.php', 'danger', 'Region and at least one branch are required for the organization form.');
        }

        if ($action === 'add_structure') {
            $conn->begin_transaction();
            $stmt = $conn->prepare("
                INSERT INTO organization_structure_entries
                (division_name, region_name, area_name, branch_no, branch_id, area_supervisor_employee_id, regional_manager_employee_id)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param(
                "ssssiii",
                $division_name,
                $region_name,
                $area_name,
                $branch_no,
                $primary_branch_id,
                $area_supervisor_employee_id,
                $regional_manager_employee_id
            );
            $stmt->execute();
            $entry_id = $stmt->insert_id;
            $stmt->close();
            $branchStmt = $conn->prepare("INSERT INTO organization_structure_entry_branches (entry_id, branch_id) VALUES (?, ?)");
            foreach ($branch_ids as $branch_id) {
                $branchStmt->bind_param("ii", $entry_id, $branch_id);
                $branchStmt->execute();
            }
            $branchStmt->close();
            $conn->commit();
            logAudit($conn, $_SESSION['user_id'], 'CREATE', 'Organization Structure', $entry_id, "Added operation management row for region: $region_name");
            redirectWith(BASE_URL . '/manager/operation-management.php', 'success', 'Organization row added successfully.');
        }

        $entry_id = (int) ($_POST['entry_id'] ?? 0);
        if ($entry_id <= 0) {
            redirectWith(BASE_URL . '/manager/operation-management.php', 'danger', 'Invalid organization row selected.');
        }

        $conn->begin_transaction();
        $stmt = $conn->prepare("
            UPDATE organization_structure_entries
            SET division_name = ?, region_name = ?, area_name = ?, branch_no = ?, branch_id = ?, area_supervisor_employee_id = ?, regional_manager_employee_id = ?
            WHERE entry_id = ?
        ");
        $stmt->bind_param(
            "ssssiiii",
            $division_name,
            $region_name,
            $area_name,
            $branch_no,
            $primary_branch_id,
            $area_supervisor_employee_id,
            $regional_manager_employee_id,
            $entry_id
        );
        $stmt->execute();
        $stmt->close();
        $conn->query("DELETE FROM organization_structure_entry_branches WHERE entry_id = " . (int) $entry_id);
        $branchStmt = $conn->prepare("INSERT INTO organization_structure_entry_branches (entry_id, branch_id) VALUES (?, ?)");
        foreach ($branch_ids as $branch_id) {
            $branchStmt->bind_param("ii", $entry_id, $branch_id);
            $branchStmt->execute();
        }
        $branchStmt->close();
        $conn->commit();
        logAudit($conn, $_SESSION['user_id'], 'UPDATE', 'Organization Structure', $entry_id, "Updated operation management row for region: $region_name");
        redirectWith(BASE_URL . '/manager/operation-management.php', 'success', 'Organization row updated successfully.');
    }
}

if (isset($_GET['delete_structure']) && is_numeric($_GET['delete_structure'])) {
    $entry_id = (int) $_GET['delete_structure'];
    $stmt = $conn->prepare("DELETE FROM organization_structure_entries WHERE entry_id = ? AND division_name = 'Operation Management'");
    $stmt->bind_param("i", $entry_id);
    $stmt->execute();
    $stmt->close();
    logAudit($conn, $_SESSION['user_id'], 'DELETE', 'Organization Structure', $entry_id, 'Deleted operation management organization row.');
    redirectWith(BASE_URL . '/manager/operation-management.php', 'success', 'Organization row deleted successfully.');
}

require_once '../includes/header.php';

$branches = $conn->query("SELECT branch_id, branch_name, location FROM branches ORDER BY branch_name")->fetch_all(MYSQLI_ASSOC);

$areaSupervisors = $conn->query("
    SELECT e.employee_id,
           CONCAT(e.first_name, ' ', e.last_name) AS employee_name,
           COALESCE(jt.job_title, e.job_title) AS position_name,
           b.branch_name
    FROM employees e
    LEFT JOIN job_titles jt ON e.job_title_id = jt.job_title_id
    LEFT JOIN branches b ON e.branch_id = b.branch_id
    WHERE e.is_active = 1
      AND LOWER(COALESCE(jt.job_title, e.job_title, '')) LIKE '%area supervisor%'
    ORDER BY employee_name
")->fetch_all(MYSQLI_ASSOC);

$regionalManagers = $conn->query("
    SELECT e.employee_id,
           CONCAT(e.first_name, ' ', e.last_name) AS employee_name,
           COALESCE(jt.job_title, e.job_title) AS position_name,
           b.branch_name
    FROM employees e
    LEFT JOIN job_titles jt ON e.job_title_id = jt.job_title_id
    LEFT JOIN branches b ON e.branch_id = b.branch_id
    WHERE e.is_active = 1
      AND LOWER(COALESCE(jt.job_title, e.job_title, '')) LIKE '%regional manager%'
    ORDER BY employee_name
")->fetch_all(MYSQLI_ASSOC);

$orgEntries = $conn->query("
    SELECT ose.*,
           CONCAT(ae.first_name, ' ', ae.last_name) AS area_supervisor_name,
           CONCAT(re.first_name, ' ', re.last_name) AS regional_manager_name,
           GROUP_CONCAT(DISTINCT b.branch_name ORDER BY b.branch_name SEPARATOR '||') AS branch_names,
           GROUP_CONCAT(DISTINCT COALESCE(b.location, '') ORDER BY b.branch_name SEPARATOR '||') AS branch_locations,
           GROUP_CONCAT(DISTINCT osb.branch_id ORDER BY b.branch_name) AS branch_ids
    FROM organization_structure_entries ose
    LEFT JOIN organization_structure_entry_branches osb ON ose.entry_id = osb.entry_id
    LEFT JOIN branches b ON osb.branch_id = b.branch_id
    LEFT JOIN employees ae ON ose.area_supervisor_employee_id = ae.employee_id
    LEFT JOIN employees re ON ose.regional_manager_employee_id = re.employee_id
    WHERE ose.division_name = 'Operation Management'
    GROUP BY ose.entry_id
    ORDER BY ose.region_name, ose.area_name
");

$orgEntryCount = $orgEntries->num_rows;
$orgBranchCount = (int) $conn->query("
    SELECT COUNT(DISTINCT osb.branch_id) AS cnt
    FROM organization_structure_entries ose
    LEFT JOIN organization_structure_entry_branches osb ON ose.entry_id = osb.entry_id
    WHERE ose.division_name = 'Operation Management'
")->fetch_assoc()['cnt'];
$orgRegionCount = (int) $conn->query("SELECT COUNT(DISTINCT region_name) AS cnt FROM organization_structure_entries WHERE division_name = 'Operation Management'")->fetch_assoc()['cnt'];
?>

<style>
    @media (max-width: 768px) {
        .table-responsive {
            border: none;
        }

        .responsive-org-table thead {
            display: none;
        }

        .responsive-org-table tr {
            display: block;
            background: #fff;
            border-radius: 15px;
            margin-bottom: 18px;
            padding: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #f0f0f0;
        }

        .responsive-org-table td {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: center;
            border: none;
            padding: 9px 0;
            text-align: right;
            font-size: 0.9rem;
        }

        .responsive-org-table td::before {
            content: attr(data-label);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.7rem;
            color: var(--text-muted);
            text-align: left;
        }

        .responsive-org-table td:first-child::before,
        .responsive-org-table td:last-child::before {
            display: none;
        }
    }
</style>

<div class="page-hero fadeup">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
        <div>
            <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.55);">HR Manager · Organization</div>
            <h4 class="text-white fw-bold mb-0 mt-1"><i class="fas fa-project-diagram me-2" style="color:#BD9414;"></i>Operation Management</h4>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?php echo BASE_URL; ?>/manager/print-organization.php" target="_blank" class="btn btn-outline-light btn-sm">
                <i class="fas fa-print me-1"></i>Print Organization Form
            </a>
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addStructureModal">
                <i class="fas fa-plus-circle me-1"></i>Add Organization Row
            </button>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-6 col-md-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?php echo $orgEntryCount; ?></div>
                        <div class="stat-label">Org Form Rows</div>
                    </div>
                    <i class="fas fa-list stat-icon text-white-50"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?php echo $orgRegionCount; ?></div>
                        <div class="stat-label">Regions</div>
                    </div>
                    <i class="fas fa-map-marked-alt stat-icon" style="color:#BD9414;"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?php echo $orgBranchCount; ?></div>
                        <div class="stat-label">Branches Linked</div>
                    </div>
                    <i class="fas fa-building stat-icon" style="color:#17a2b8;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="chart-card fadeup-1 mb-4">
    <div class="cc-header">
        <div>
            <h5><i class="fas fa-project-diagram me-2"></i>Organization Printable Form</h5>
            <small class="text-muted">Build the `Region | Area Supervisor | Regional Manager | Area | Branches` rows here.</small>
        </div>
        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="form-control form-control-sm" id="searchStructure" placeholder="Search form rows...">
        </div>
    </div>
    <div class="cc-body p-0">
        <div class="table-responsive">
            <table class="table table-hover responsive-org-table" id="structureTable">
                <thead>
                    <tr>
                        <th>Region</th>
                        <th>Area Supervisor</th>
                        <th>Regional Manager</th>
                        <th>Area</th>
                        <th>Branches</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($orgEntryCount === 0): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-print fa-2x mb-2 d-block" style="opacity:0.3;"></i>
                                No organization form rows yet. Add rows to build the printable structure.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php while ($entry = $orgEntries->fetch_assoc()): ?>
                            <?php
                                $entryBranchNames = parseDelimitedList($entry['branch_names'] ?? '');
                                $entryBranchLocations = parseDelimitedList($entry['branch_locations'] ?? '');
                                $entryBranchIds = array_values(array_filter(array_map('intval', explode(',', $entry['branch_ids'] ?? ''))));
                                $entryBranchLabels = [];
                                foreach ($entryBranchNames as $index => $branchName) {
                                    $branchLabel = $branchName;
                                    if (!empty($entryBranchLocations[$index])) {
                                        $branchLabel .= ' - ' . $entryBranchLocations[$index];
                                    }
                                    $entryBranchLabels[] = $branchLabel;
                                }
                                $branchSummary = !empty($entryBranchLabels) ? implode(', ', $entryBranchLabels) : 'No branch assigned';
                            ?>
                            <tr>
                                <td data-label="Region"><?php echo e($entry['region_name']); ?></td>
                                <td data-label="Area Supervisor"><?php echo e($entry['area_supervisor_name'] ?: 'Not assigned'); ?></td>
                                <td data-label="Regional Manager"><?php echo e($entry['regional_manager_name'] ?: 'Not assigned'); ?></td>
                                <td data-label="Area"><?php echo e($entry['area_name'] ?: 'N/A'); ?></td>
                                <td data-label="Branches">
                                    <?php if (!empty($entryBranchLabels)): ?>
                                        <?php foreach ($entryBranchLabels as $index => $branchLabel): ?>
                                            <div class="fw-semibold">
                                                <?php echo e(($index + 1) . '. ' . $branchLabel); ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted">No branch assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Actions">
                                    <button class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editStructureModal"
                                        onclick="openEditStructureModal(
                                            <?php echo (int) $entry['entry_id']; ?>,
                                            <?php echo jsonAttributeValue($entry['region_name']); ?>,
                                            <?php echo jsonAttributeValue($entry['area_name'] ?? ''); ?>,
                                            <?php echo jsonAttributeValue($entryBranchIds); ?>,
                                            <?php echo (int) ($entry['area_supervisor_employee_id'] ?? 0); ?>,
                                            <?php echo (int) ($entry['regional_manager_employee_id'] ?? 0); ?>
                                        )">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteStructureModal"
                                        onclick="setDeleteStructureTarget(
                                            <?php echo (int) $entry['entry_id']; ?>,
                                            <?php echo jsonAttributeValue($entry['region_name']); ?>,
                                            <?php echo jsonAttributeValue($branchSummary); ?>
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

<div class="modal fade" id="addStructureModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="action" value="add_structure">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Add Organization Row</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Region <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="region_name" placeholder="e.g. Region 1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Area</label>
                            <input type="text" class="form-control" name="area_name" placeholder="e.g. North Luzon">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Branches <span class="text-danger">*</span></label>
                            <select class="form-select" name="branch_ids[]" multiple size="6" required>
                                <?php foreach ($branches as $branch): ?>
                                    <option value="<?php echo (int) $branch['branch_id']; ?>">
                                        <?php echo e($branch['branch_name'] . ($branch['location'] ? ' - ' . $branch['location'] : '')); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Hold Ctrl or Cmd to select multiple branches. The No. column is numbered automatically.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Area Supervisor</label>
                            <select class="form-select" name="area_supervisor_employee_id">
                                <option value="">Select Area Supervisor</option>
                                <?php foreach ($areaSupervisors as $employee): ?>
                                    <option value="<?php echo (int) $employee['employee_id']; ?>">
                                        <?php echo e($employee['employee_name'] . ' - ' . $employee['position_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Options come from active employees with an Area Supervisor position.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Regional Manager</label>
                            <select class="form-select" name="regional_manager_employee_id">
                                <option value="">Select Regional Manager</option>
                                <?php foreach ($regionalManagers as $employee): ?>
                                    <option value="<?php echo (int) $employee['employee_id']; ?>">
                                        <?php echo e($employee['employee_name'] . ' - ' . $employee['position_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Options come from active employees with a Regional Manager position.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Add Row</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editStructureModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="action" value="edit_structure">
                <input type="hidden" name="entry_id" id="editStructureId">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Organization Row</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Region <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="region_name" id="editRegionName" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Area</label>
                            <input type="text" class="form-control" name="area_name" id="editAreaName">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Branches <span class="text-danger">*</span></label>
                            <select class="form-select" name="branch_ids[]" id="editBranchIds" multiple size="6" required>
                                <?php foreach ($branches as $branch): ?>
                                    <option value="<?php echo (int) $branch['branch_id']; ?>">
                                        <?php echo e($branch['branch_name'] . ($branch['location'] ? ' - ' . $branch['location'] : '')); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Hold Ctrl or Cmd to select multiple branches. The No. column is numbered automatically.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Area Supervisor</label>
                            <select class="form-select" name="area_supervisor_employee_id" id="editAreaSupervisor">
                                <option value="">Select Area Supervisor</option>
                                <?php foreach ($areaSupervisors as $employee): ?>
                                    <option value="<?php echo (int) $employee['employee_id']; ?>">
                                        <?php echo e($employee['employee_name'] . ' - ' . $employee['position_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Regional Manager</label>
                            <select class="form-select" name="regional_manager_employee_id" id="editRegionalManager">
                                <option value="">Select Regional Manager</option>
                                <?php foreach ($regionalManagers as $employee): ?>
                                    <option value="<?php echo (int) $employee['employee_id']; ?>">
                                        <?php echo e($employee['employee_name'] . ' - ' . $employee['position_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteStructureModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-trash me-2"></i>Delete Organization Row</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p>Delete organization row for <strong id="deleteStructureRegion"></strong>?</p>
                <div class="text-muted small" id="deleteStructureBranch"></div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="deleteStructureConfirmBtn" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>

<script>
    function setMultiSelectValues(selectId, values) {
        const select = document.getElementById(selectId);
        if (!select) return;

        const selectedValues = (values || []).map(String);
        Array.from(select.options).forEach((option) => {
            option.selected = selectedValues.includes(option.value);
        });
    }

    function openEditStructureModal(id, regionName, areaName, branchIds, areaSupervisorId, regionalManagerId) {
        document.getElementById('editStructureId').value = id;
        document.getElementById('editRegionName').value = regionName;
        document.getElementById('editAreaName').value = areaName;
        setMultiSelectValues('editBranchIds', branchIds);
        document.getElementById('editAreaSupervisor').value = areaSupervisorId || '';
        document.getElementById('editRegionalManager').value = regionalManagerId || '';
    }

    function setDeleteStructureTarget(id, regionName, branchName) {
        document.getElementById('deleteStructureRegion').textContent = regionName;
        document.getElementById('deleteStructureBranch').textContent = branchName ? `Branch: ${branchName}` : '';
        document.getElementById('deleteStructureConfirmBtn').href = '?delete_structure=' + id;
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

    document.addEventListener('DOMContentLoaded', function () {
        attachTableSearch('searchStructure', 'structureTable');
    });
</script>

<?php require_once '../includes/footer.php'; ?>
