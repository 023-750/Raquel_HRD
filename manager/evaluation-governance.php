<?php
$page_title = 'Evaluation Governance';
require_once '../includes/session-check.php';
checkRole(['HR Manager']);
require_once '../includes/functions.php';
ensureOrganizationEvaluationPackageSchema($conn);

// --- Handle Form Submissions (Assign & Batch Actions) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    
    // Handle Batch Actions (Batch Delete, Batch Enable, Batch Disable)
    if (isset($_POST['action']) && !empty($_POST['action'])) {
        $action = $_POST['action'];
        $approver_ids = $_POST['approver_ids'] ?? [];
        
        if (!is_array($approver_ids) || empty($approver_ids)) {
            redirectWith(BASE_URL . '/manager/evaluation-governance.php', 'warning', 'Please select at least one approver to perform this action.');
        }

        $ids = array_map('intval', $approver_ids);
        $ids = array_filter($ids, fn($id) => $id > 0);
        
        if (!empty($ids)) {
            $in_clause = implode(',', $ids);
            
            if ($action === 'batch_delete') {
                $conn->query("DELETE FROM evaluation_governance_approvers WHERE governance_approver_id IN ($in_clause)");
                syncPendingOrganizationPackageGovernanceApprovers($conn);
                logAudit($conn, (int) $_SESSION['user_id'], 'DELETE', 'Evaluation Governance', 0, "Batch deleted governance approver IDs: $in_clause");
                redirectWith(BASE_URL . '/manager/evaluation-governance.php', 'success', count($ids) . ' governance approver(s) deleted successfully.');
            } elseif ($action === 'batch_disable') {
                $conn->query("UPDATE evaluation_governance_approvers SET is_active = 0 WHERE governance_approver_id IN ($in_clause)");
                syncPendingOrganizationPackageGovernanceApprovers($conn);
                logAudit($conn, (int) $_SESSION['user_id'], 'UPDATE', 'Evaluation Governance', 0, "Batch disabled governance approver IDs: $in_clause");
                redirectWith(BASE_URL . '/manager/evaluation-governance.php', 'success', count($ids) . ' governance approver(s) disabled.');
            } elseif ($action === 'batch_enable') {
                $conn->query("UPDATE evaluation_governance_approvers SET is_active = 1 WHERE governance_approver_id IN ($in_clause)");
                syncPendingOrganizationPackageGovernanceApprovers($conn);
                logAudit($conn, (int) $_SESSION['user_id'], 'UPDATE', 'Evaluation Governance', 0, "Batch enabled governance approver IDs: $in_clause");
                redirectWith(BASE_URL . '/manager/evaluation-governance.php', 'success', count($ids) . ' governance approver(s) enabled.');
            }
        }
        redirectWith(BASE_URL . '/manager/evaluation-governance.php', 'warning', 'No valid approver IDs selected.');
    }

    // Handle Assign Single Approver
    $type = $_POST['governance_type'] ?? '';
    $reviewer_user_id = (int) ($_POST['reviewer_user_id'] ?? 0);
    $department_id = (int) ($_POST['department_id'] ?? 0);
    
    if (!in_array($type, ['Board of Directors', 'Audit Committee'], true) || $reviewer_user_id <= 0) {
        redirectWith(BASE_URL . '/manager/evaluation-governance.php', 'danger', 'Choose a governance group and an active user.');
    }
    
    $eligible_stmt = $conn->prepare("SELECT u.user_id FROM users u JOIN employees e ON e.employee_id = u.employee_id
        WHERE u.user_id = ? AND u.is_active = 1 AND e.is_active = 1 AND e.deleted_at IS NULL
          AND (? = 0 OR e.department_id = ?) LIMIT 1");
    $eligible_stmt->bind_param('iii', $reviewer_user_id, $department_id, $department_id); 
    $eligible_stmt->execute();
    $eligible = $eligible_stmt->get_result()->fetch_assoc(); 
    $eligible_stmt->close();
    
    if (!$eligible) {
        redirectWith(BASE_URL . '/manager/evaluation-governance.php', 'danger', 'Selected user is not an active employee.');
    }
    
    if ($type === 'Board of Directors' || $type === 'Audit Committee') {
        $employee_stmt = $conn->prepare('SELECT employee_id FROM users WHERE user_id = ? LIMIT 1');
        $employee_stmt->bind_param('i', $reviewer_user_id); 
        $employee_stmt->execute();
        $employee = $employee_stmt->get_result()->fetch_assoc(); 
        $employee_stmt->close();
        
        if (!empty($employee['employee_id']) && isEmployeeInOrganizationReviewHierarchy($conn, (int) $employee['employee_id'])) {
            redirectWith(BASE_URL . '/manager/evaluation-governance.php', 'danger', 'This user is already part of the employee-portal review hierarchy and cannot also approve as ' . $type . '. Choose an independent Manager-level user.');
        }
    }
    
    $stmt = $conn->prepare('INSERT INTO evaluation_governance_approvers (governance_type, user_id, is_active) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE is_active = 1');
    $stmt->bind_param('si', $type, $reviewer_user_id); 
    $stmt->execute(); 
    $stmt->close();
    
    syncPendingOrganizationPackageGovernanceApprovers($conn);
    logAudit($conn, (int) $_SESSION['user_id'], 'CREATE', 'Evaluation Governance', $reviewer_user_id, "Assigned $type approver");
    redirectWith(BASE_URL . '/manager/evaluation-governance.php', 'success', 'Governance approver assigned and active packages synced.');
}

// --- Handle GET Actions (Disable, Enable, Delete Single) ---
if (isset($_GET['disable']) && is_numeric($_GET['disable'])) {
    $id = (int) $_GET['disable'];
    $stmt = $conn->prepare('UPDATE evaluation_governance_approvers SET is_active = 0 WHERE governance_approver_id = ?');
    $stmt->bind_param('i', $id); 
    $stmt->execute(); 
    $stmt->close();
    
    syncPendingOrganizationPackageGovernanceApprovers($conn);
    logAudit($conn, (int) $_SESSION['user_id'], 'UPDATE', 'Evaluation Governance', $id, "Disabled governance approver ID $id");
    redirectWith(BASE_URL . '/manager/evaluation-governance.php', 'success', 'Governance approver disabled. Existing package routes remain unchanged.');
}

if (isset($_GET['enable']) && is_numeric($_GET['enable'])) {
    $id = (int) $_GET['enable'];
    $stmt = $conn->prepare('UPDATE evaluation_governance_approvers SET is_active = 1 WHERE governance_approver_id = ?');
    $stmt->bind_param('i', $id); 
    $stmt->execute(); 
    $stmt->close();
    
    syncPendingOrganizationPackageGovernanceApprovers($conn);
    logAudit($conn, (int) $_SESSION['user_id'], 'UPDATE', 'Evaluation Governance', $id, "Enabled governance approver ID $id");
    redirectWith(BASE_URL . '/manager/evaluation-governance.php', 'success', 'Governance approver enabled.');
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $conn->prepare('DELETE FROM evaluation_governance_approvers WHERE governance_approver_id = ?');
    $stmt->bind_param('i', $id); 
    $stmt->execute(); 
    $stmt->close();
    
    syncPendingOrganizationPackageGovernanceApprovers($conn);
    logAudit($conn, (int) $_SESSION['user_id'], 'DELETE', 'Evaluation Governance', $id, "Deleted governance approver ID $id");
    redirectWith(BASE_URL . '/manager/evaluation-governance.php', 'success', 'Governance approver deleted successfully.');
}

require_once '../includes/header.php';

$users = $conn->query("SELECT u.user_id, u.full_name, u.role, e.job_title, e.rank_category_id
    , e.department_id, rc.rank_name, rc.level_order
    FROM users u
    JOIN employees e ON e.employee_id = u.employee_id
    LEFT JOIN rank_categories rc ON rc.rank_category_id = e.rank_category_id
    WHERE u.is_active = 1 AND e.is_active = 1 AND e.deleted_at IS NULL
    ORDER BY COALESCE(rc.level_order, 99), u.full_name")->fetch_all(MYSQLI_ASSOC);

$departments = $conn->query("SELECT department_id, department_name FROM departments WHERE is_active = 1 ORDER BY department_name")->fetch_all(MYSQLI_ASSOC);

$approvers = $conn->query("SELECT ega.*, u.full_name, u.role, e.job_title FROM evaluation_governance_approvers ega JOIN users u ON u.user_id = ega.user_id LEFT JOIN employees e ON e.employee_id = u.employee_id ORDER BY ega.governance_type, u.full_name")->fetch_all(MYSQLI_ASSOC);
?>
<style>
    /* Ensure Checkboxes in Table are Perfect Square Boxes */
    .package-table input[type="checkbox"].form-check-input,
    .approver-checkbox,
    #selectAllApprovers {
        width: 1.25rem !important;
        height: 1.25rem !important;
        min-width: 1.25rem !important;
        min-height: 1.25rem !important;
        max-width: 1.25rem !important;
        max-height: 1.25rem !important;
        margin: 0 auto !important;
        display: inline-block !important;
        vertical-align: middle !important;
        cursor: pointer !important;
        border-radius: 4px !important;
        border: 2px solid #94a3b8 !important;
        aspect-ratio: 1 / 1 !important;
        box-sizing: border-box !important;
        padding: 0 !important;
    }

    .package-table input[type="checkbox"].form-check-input:checked,
    .approver-checkbox:checked,
    #selectAllApprovers:checked {
        background-color: var(--rp-forest-green, #082E06) !important;
        border-color: var(--rp-forest-green, #082E06) !important;
    }
</style>

<main class="evaluation-packages container-fluid py-4">
    <!-- Hero Header -->
    <section class="package-hero fadeup">
        <p class="mb-1 small text-uppercase tracking-wider opacity-75">Organization-driven evaluation workflow</p>
        <h1 class="h4 mb-2 fw-bold"><i class="fas fa-landmark me-2 text-warning"></i>Evaluation Governance</h1>
        <p class="mb-0">Assign authorized users for Audit Committee and Board of Directors. When both are active, new package routes are: hierarchy reviewers &rarr; Audit Committee &rarr; Board of Directors (locks and applies). Existing in-progress routes are not rewritten.</p>
    </section>

    <!-- Assign Approver Card -->
    <section class="package-card fadeup-1">
        <header class="package-card__header">
            <h2 class="h5 mb-0 fw-bold"><i class="fas fa-user-plus me-2 text-primary"></i>Assign Authorized Governance Approver</h2>
        </header>
        <div class="package-card__body p-4">
            <form method="post" class="row g-3 align-items-end">
                <?php echo csrfField(); ?>
                <div class="col-md-3">
                    <label class="form-label fw-semibold" for="governance-type">Governance Group <span class="text-danger">*</span></label>
                    <select class="form-select" id="governance-type" name="governance_type" required>
                        <option value="">Select group</option>
                        <option value="Board of Directors">Board of Directors</option>
                        <option value="Audit Committee">Audit Committee</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold" for="governance-department">Department</label>
                    <select class="form-select" id="governance-department" name="department_id">
                        <option value="0">All departments</option>
                        <?php foreach ($departments as $department): ?>
                            <option value="<?php echo (int)$department['department_id']; ?>"><?php echo e($department['department_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold" for="governance-user">Authorized User <span class="text-danger">*</span></label>
                    <select class="form-select" id="governance-user" name="reviewer_user_id" required>
                        <option value="">Select user</option>
                        <?php $prevRank = null; foreach ($users as $user): $rankLabel = $user['rank_name'] ?? 'Unclassified'; if ($rankLabel !== $prevRank): ?>
                            <option disabled style="font-weight:600;color:#6c757d;background:#f8f9fa;">── <?php echo e($rankLabel); ?> ──</option>
                            <?php $prevRank = $rankLabel; endif; ?>
                            <option value="<?php echo (int)$user['user_id']; ?>" data-department-id="<?php echo (int)$user['department_id']; ?>" data-rank="<?php echo e($rankLabel); ?>"><?php echo e($user['full_name'] . ' — ' . ($user['job_title'] ?: $user['role'])); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100 rounded-pill shadow-sm" type="submit">
                        <i class="fas fa-check me-1"></i>Assign Approver
                    </button>
                </div>
            </form>
        </div>
    </section>

    <!-- Configured Approvers Card -->
    <section class="package-card fadeup-2">
        <header class="package-card__header d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <h2 class="h5 mb-0 fw-bold"><i class="fas fa-user-shield me-2 text-primary"></i>Configured Approvers</h2>
                <span class="badge bg-secondary-subtle text-secondary border px-3 py-1"><?php echo count($approvers); ?> Total</span>
            </div>

            <!-- Multiple Selection Toolbar -->
            <div id="batchActionToolbar" class="d-flex align-items-center gap-2 d-none">
                <span class="small fw-semibold text-dark me-2" id="selectedCountText">0 selected</span>
                <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="submitBatchForm('batch_enable')">
                    <i class="fas fa-check-circle me-1"></i>Enable Selected
                </button>
                <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3" onclick="submitBatchForm('batch_disable')">
                    <i class="fas fa-ban me-1"></i>Disable Selected
                </button>
                <button type="button" class="btn btn-sm btn-danger rounded-pill px-3 shadow-sm" onclick="submitBatchForm('batch_delete')">
                    <i class="fas fa-trash-alt me-1"></i>Delete Selected
                </button>
            </div>
        </header>

        <div class="package-card__body p-0">
            <form method="post" action="" id="batchApproversForm">
                <?php echo csrfField(); ?>
                <input type="hidden" name="action" id="batchActionInput" value="">
                
                <div class="table-responsive">
                    <table class="table package-table align-middle mb-0">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th style="width: 44px;" class="text-center">
                                    <input type="checkbox" class="form-check-input" id="selectAllApprovers" title="Select All Approvers">
                                </th>
                                <th style="width: 22%;">Governance Group</th>
                                <th style="width: 25%;">User</th>
                                <th>Position / Role</th>
                                <th style="width: 140px;">Status</th>
                                <th style="width: 180px;" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($approvers)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <i class="fas fa-user-slash fa-2x mb-2 d-block text-black-50"></i>
                                        No governance approvers configured yet.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($approvers as $approver): ?>
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" class="form-check-input approver-checkbox" name="approver_ids[]" value="<?php echo (int)$approver['governance_approver_id']; ?>">
                                        </td>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1">
                                                <i class="fas fa-users-cog me-1"></i><?php echo e($approver['governance_type']); ?>
                                            </span>
                                        </td>
                                        <td class="fw-bold text-dark"><?php echo e($approver['full_name']); ?></td>
                                        <td class="text-muted small"><?php echo e($approver['job_title'] ?: $approver['role']); ?></td>
                                        <td>
                                            <?php if ($approver['is_active']): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1">
                                                    <i class="fas fa-check-circle me-1"></i>Active
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-1">
                                                    <i class="fas fa-ban me-1"></i>Disabled
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-1">
                                                <?php if ($approver['is_active']): ?>
                                                    <a class="btn btn-sm btn-outline-warning rounded-pill px-3" href="?disable=<?php echo (int)$approver['governance_approver_id']; ?>" title="Disable Approver">
                                                        <i class="fas fa-ban me-1"></i>Disable
                                                    </a>
                                                    <a class="btn btn-sm btn-outline-danger rounded-pill px-2" href="?delete=<?php echo (int)$approver['governance_approver_id']; ?>" onclick="return confirm('Are you sure you want to delete this approver?');" title="Delete Approver">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <a class="btn btn-sm btn-outline-success rounded-pill px-3" href="?enable=<?php echo (int)$approver['governance_approver_id']; ?>" title="Enable Approver">
                                                        <i class="fas fa-check-circle me-1"></i>Enable
                                                    </a>
                                                    <a class="btn btn-sm btn-outline-danger rounded-pill px-3" href="?delete=<?php echo (int)$approver['governance_approver_id']; ?>" onclick="return confirm('Are you sure you want to delete this disabled approver?');" title="Delete Approver">
                                                        <i class="fas fa-trash-alt me-1"></i>Delete
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </section>
</main>

<script src="<?php echo BASE_URL; ?>/assets/js/evaluation-governance.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAllApprovers');
    const checkboxes = document.querySelectorAll('.approver-checkbox');
    const toolbar = document.getElementById('batchActionToolbar');
    const selectedCountText = document.getElementById('selectedCountText');

    function updateToolbar() {
        const checked = document.querySelectorAll('.approver-checkbox:checked');
        const count = checked.length;
        if (count > 0) {
            toolbar.classList.remove('d-none');
            selectedCountText.textContent = count + ' selected';
        } else {
            toolbar.classList.add('d-none');
        }
        if (selectAll) {
            selectAll.checked = checkboxes.length > 0 && count === checkboxes.length;
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
            updateToolbar();
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateToolbar);
    });
});

function submitBatchForm(action) {
    const checked = document.querySelectorAll('.approver-checkbox:checked');
    if (checked.length === 0) {
        alert('Please select at least one approver.');
        return;
    }
    
    let confirmMsg = '';
    if (action === 'batch_delete') {
        confirmMsg = `Are you sure you want to DELETE the ${checked.length} selected approver(s)?`;
    } else if (action === 'batch_disable') {
        confirmMsg = `Disable the ${checked.length} selected approver(s)?`;
    } else if (action === 'batch_enable') {
        confirmMsg = `Enable the ${checked.length} selected approver(s)?`;
    }
    
    if (confirmMsg && !confirm(confirmMsg)) {
        return;
    }
    
    document.getElementById('batchActionInput').value = action;
    document.getElementById('batchApproversForm').submit();
}
</script>

<?php require_once '../includes/footer.php'; ?>
