<?php
$page_title = 'Team Evaluation History';
require_once '../includes/session-check.php';
require_once '../includes/functions.php';

ensureOrganizationEvaluationPackageSchema($conn);
$user_id = (int) ($_SESSION['user_id'] ?? 0);
$packages_stmt = $conn->prepare("SELECT DISTINCT ep.package_id, ep.status, ep.period_start, ep.period_end, ep.shared_behavior_score,
        d.department_name, et.template_name
    FROM evaluation_packages ep
    JOIN evaluation_package_route_steps rs ON rs.package_id = ep.package_id
    JOIN departments d ON d.department_id = ep.department_id
    JOIN evaluation_templates et ON et.template_id = ep.template_id
    WHERE rs.reviewer_user_id = ?
    ORDER BY ep.updated_at DESC");
$packages_stmt->bind_param('i', $user_id); $packages_stmt->execute();
$packages = $packages_stmt->get_result()->fetch_all(MYSQLI_ASSOC); $packages_stmt->close();

require_once '../includes/header.php';
?>
<main class="evaluation-packages container-fluid py-4">
    <section class="package-hero"><a class="history-back-link" href="<?php echo BASE_URL; ?>/employee/team-evaluation-packages.php"><i class="fas fa-arrow-left me-1"></i>Back to assigned packages</a><p class="mb-1 small">Organization-driven evaluation workflow</p><h1 class="h4 mb-2">Team Evaluation History</h1><p class="mb-0">View the completed and previously assigned team packages in which you are a reviewer.</p></section>
    <?php if (!$packages): ?><section class="package-empty"><h2 class="h5">No team evaluation history yet</h2><p class="mb-0">Packages will appear here once you are included in their organization approval route.</p></section><?php endif; ?>
    <?php foreach ($packages as $package): ?>
        <?php $members_stmt = $conn->prepare("SELECT e.evaluation_id, emp.first_name, emp.last_name, emp.job_title, e.kra_subtotal, e.behavior_average, e.total_score, e.status FROM evaluation_package_members pm JOIN evaluations e ON e.evaluation_id = pm.evaluation_id JOIN employees emp ON emp.employee_id = e.employee_id WHERE pm.package_id = ? ORDER BY emp.last_name, emp.first_name"); $members_stmt->bind_param('i', $package['package_id']); $members_stmt->execute(); $members = $members_stmt->get_result()->fetch_all(MYSQLI_ASSOC); $members_stmt->close(); ?>
        <section class="package-card"><header class="package-card__header"><div><h2 class="h5 mb-1"><?php echo e($package['department_name']); ?> — <?php echo e($package['template_name']); ?></h2><p class="mb-0 small text-muted"><?php echo e($package['period_start']); ?> to <?php echo e($package['period_end']); ?></p></div><span class="package-status <?php echo $package['status'] === 'Approved and Applied' ? 'package-status--complete' : 'package-status--review'; ?>"><?php echo e($package['status']); ?></span></header><div class="package-card__body"><p class="small text-muted">Shared Behavior score: <strong><?php echo $package['shared_behavior_score'] !== null ? number_format((float) $package['shared_behavior_score'], 2) : 'Pending'; ?></strong></p><div class="table-responsive"><table class="table package-table align-middle mb-0"><thead><tr><th>Employee</th><th>Position</th><th class="text-end">KRA</th><th class="text-end">Behavior</th><th class="text-end">Total</th><th>Action</th></tr></thead><tbody><?php foreach ($members as $member): ?><tr><td><?php echo e($member['first_name'] . ' ' . $member['last_name']); ?></td><td><?php echo e($member['job_title']); ?></td><td class="text-end tabular-nums"><?php echo number_format((float) $member['kra_subtotal'], 2); ?></td><td class="text-end tabular-nums"><?php echo number_format((float) $member['behavior_average'], 2); ?></td><td class="text-end tabular-nums"><?php echo number_format((float) $member['total_score'], 2); ?></td><td><a class="btn btn-sm btn-outline-primary" href="<?php echo BASE_URL; ?>/employee/package-member-view.php?package_id=<?php echo (int) $package['package_id']; ?>&evaluation_id=<?php echo (int) $member['evaluation_id']; ?>">View employee evaluation</a></td></tr><?php endforeach; ?></tbody></table></div></div></section>
    <?php endforeach; ?>
</main>
<?php require_once '../includes/footer.php'; ?>
