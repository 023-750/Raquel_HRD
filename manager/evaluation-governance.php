<?php
$page_title = 'Evaluation Governance';
require_once '../includes/session-check.php';
checkRole(['HR Manager']);
require_once '../includes/functions.php';
ensureOrganizationEvaluationPackageSchema($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $type = $_POST['governance_type'] ?? '';
    $reviewer_user_id = (int) ($_POST['reviewer_user_id'] ?? 0);
    $department_id = (int) ($_POST['department_id'] ?? 0);
    if (!in_array($type, ['Board of Directors', 'Audit Committee'], true) || $reviewer_user_id <= 0) {
        redirectWith(BASE_URL . '/manager/evaluation-governance.php', 'danger', 'Choose a governance group and an active Manager-level user.');
    }
    $eligible_stmt = $conn->prepare("SELECT u.user_id FROM users u JOIN employees e ON e.employee_id = u.employee_id
        WHERE u.user_id = ? AND u.is_active = 1 AND e.is_active = 1 AND e.deleted_at IS NULL
          AND e.rank_category_id IS NOT NULL AND e.rank_category_id <= 3
          AND (? = 0 OR e.department_id = ?) LIMIT 1");
    $eligible_stmt->bind_param('iii', $reviewer_user_id, $department_id, $department_id); $eligible_stmt->execute();
    $eligible = $eligible_stmt->get_result()->fetch_assoc(); $eligible_stmt->close();
    if (!$eligible) {
        redirectWith(BASE_URL . '/manager/evaluation-governance.php', 'danger', 'Authorized users must hold a Manager rank or higher.');
    }
    if ($type === 'Board of Directors') {
        $employee_stmt = $conn->prepare('SELECT employee_id FROM users WHERE user_id = ? LIMIT 1');
        $employee_stmt->bind_param('i', $reviewer_user_id); $employee_stmt->execute();
        $employee = $employee_stmt->get_result()->fetch_assoc(); $employee_stmt->close();
        if (!empty($employee['employee_id']) && isEmployeeInOrganizationReviewHierarchy($conn, (int) $employee['employee_id'])) {
            redirectWith(BASE_URL . '/manager/evaluation-governance.php', 'danger', 'This user is already part of the employee-portal review hierarchy and cannot also approve as the Board. Choose an independent Manager-level user.');
        }
    }
    $stmt = $conn->prepare('INSERT INTO evaluation_governance_approvers (governance_type, user_id, is_active) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE is_active = 1');
    $stmt->bind_param('si', $type, $reviewer_user_id); $stmt->execute(); $stmt->close();
    logAudit($conn, (int) $_SESSION['user_id'], 'CREATE', 'Evaluation Governance', $reviewer_user_id, "Assigned $type approver");
    redirectWith(BASE_URL . '/manager/evaluation-governance.php', 'success', 'Governance approver assigned. New packages will include this approver in their route.');
}

if (isset($_GET['disable']) && is_numeric($_GET['disable'])) {
    $id = (int) $_GET['disable'];
    $stmt = $conn->prepare('UPDATE evaluation_governance_approvers SET is_active = 0 WHERE governance_approver_id = ?');
    $stmt->bind_param('i', $id); $stmt->execute(); $stmt->close();
    redirectWith(BASE_URL . '/manager/evaluation-governance.php', 'success', 'Governance approver disabled. Existing package routes remain unchanged.');
}

require_once '../includes/header.php';
$users = $conn->query("SELECT u.user_id, u.full_name, u.role, e.job_title, e.rank_category_id
    , e.department_id FROM users u JOIN employees e ON e.employee_id = u.employee_id
    WHERE u.is_active = 1 AND e.is_active = 1 AND e.deleted_at IS NULL
      AND e.rank_category_id IS NOT NULL AND e.rank_category_id <= 3
    ORDER BY u.full_name")->fetch_all(MYSQLI_ASSOC);
$departments = $conn->query("SELECT department_id, department_name FROM departments WHERE is_active = 1 ORDER BY department_name")->fetch_all(MYSQLI_ASSOC);
$approvers = $conn->query("SELECT ega.*, u.full_name, u.role, e.job_title FROM evaluation_governance_approvers ega JOIN users u ON u.user_id = ega.user_id LEFT JOIN employees e ON e.employee_id = u.employee_id ORDER BY ega.governance_type, u.full_name")->fetch_all(MYSQLI_ASSOC);
?>
<main class="evaluation-packages container-fluid py-4">
    <section class="package-hero"><p class="mb-1 small">Organization-driven evaluation workflow</p><h1 class="h4 mb-2">Evaluation Governance</h1><p class="mb-0">Assign a Manager-level or higher authorized user to perform Board of Directors or Audit Committee approval.</p></section>
    <section class="package-card"><div class="package-card__body"><form method="post" class="row g-3 align-items-end"><?php echo csrfField(); ?><div class="col-md-3"><label class="form-label" for="governance-type">Governance group</label><select class="form-select" id="governance-type" name="governance_type" required><option value="">Select group</option><option>Board of Directors</option><option>Audit Committee</option></select></div><div class="col-md-3"><label class="form-label" for="governance-department">Department</label><select class="form-select" id="governance-department" name="department_id"><option value="0">All departments</option><?php foreach ($departments as $department): ?><option value="<?php echo (int)$department['department_id']; ?>"><?php echo e($department['department_name']); ?></option><?php endforeach; ?></select></div><div class="col-md-4"><label class="form-label" for="governance-user">Authorized user</label><select class="form-select" id="governance-user" name="reviewer_user_id" required><option value="">Select Manager-level user</option><?php foreach ($users as $user): ?><option value="<?php echo (int)$user['user_id']; ?>" data-department-id="<?php echo (int)$user['department_id']; ?>"><?php echo e($user['full_name'] . ' — ' . ($user['job_title'] ?: $user['role'])); ?></option><?php endforeach; ?></select></div><div class="col-md-2"><button class="btn btn-primary w-100" type="submit">Assign approver</button></div></form></div></section>
    <section class="package-card"><header class="package-card__header"><h2 class="h5 mb-0">Configured approvers</h2></header><div class="package-card__body"><div class="table-responsive"><table class="table package-table align-middle mb-0"><thead><tr><th>Group</th><th>User</th><th>Position / role</th><th>Status</th><th>Action</th></tr></thead><tbody><?php foreach ($approvers as $approver): ?><tr><td><?php echo e($approver['governance_type']); ?></td><td><?php echo e($approver['full_name']); ?></td><td><?php echo e($approver['job_title'] ?: $approver['role']); ?></td><td><?php echo $approver['is_active'] ? 'Active' : 'Disabled'; ?></td><td><?php if ($approver['is_active']): ?><a class="btn btn-sm btn-outline-secondary" href="?disable=<?php echo (int)$approver['governance_approver_id']; ?>">Disable</a><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div></div></section>
</main>
<script src="<?php echo BASE_URL; ?>/assets/js/evaluation-governance.js"></script>
<?php require_once '../includes/footer.php'; ?>
