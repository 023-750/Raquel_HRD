<?php
$page_title = 'My Evaluation History';
require_once '../includes/session-check.php';
require_once '../includes/functions.php';

ensureOrganizationEvaluationPackageSchema($conn);
$user_id = (int) ($_SESSION['user_id'] ?? 0);
$employee_stmt = $conn->prepare('SELECT employee_id FROM users WHERE user_id = ? LIMIT 1');
$employee_stmt->bind_param('i', $user_id); $employee_stmt->execute();
$employee = $employee_stmt->get_result()->fetch_assoc(); $employee_stmt->close();
$employee_id = (int) ($employee['employee_id'] ?? 0);

$evaluations = [];
if ($employee_id > 0) {
    $history_stmt = $conn->prepare("SELECT ev.evaluation_id, ev.evaluation_type, ev.evaluation_period_start, ev.evaluation_period_end,
            ev.kra_subtotal, ev.behavior_average, ev.total_score, ev.performance_level, ev.status, ev.submitted_date,
            et.template_name, ep.package_id, ep.status AS package_status, ep.shared_behavior_score
        FROM evaluations ev
        JOIN evaluation_templates et ON et.template_id = ev.template_id
        LEFT JOIN evaluation_package_members pm ON pm.evaluation_id = ev.evaluation_id
        LEFT JOIN evaluation_packages ep ON ep.package_id = pm.package_id
        WHERE ev.employee_id = ? AND ev.deleted_at IS NULL
        ORDER BY COALESCE(ev.submitted_date, ev.created_at) DESC, ev.evaluation_id DESC");
    $history_stmt->bind_param('i', $employee_id); $history_stmt->execute();
    $evaluations = $history_stmt->get_result()->fetch_all(MYSQLI_ASSOC); $history_stmt->close();
}

require_once '../includes/header.php';
?>
<main class="evaluation-packages container-fluid py-4">
    <section class="package-hero">
        <p class="mb-1 small">Personal evaluation record</p>
        <h1 class="h4 mb-2">My Evaluation History</h1>
        <p class="mb-0">Review your submitted, in-progress, and approved evaluations. Ratings marked as adjusted were changed during the organization review process.</p>
    </section>

    <?php if (!$evaluations): ?>
        <section class="package-empty"><h2 class="h5">No evaluations yet</h2><p class="mb-0">Your submitted evaluations will appear here.</p></section>
    <?php else: ?>
        <section class="package-card"><div class="package-card__body"><div class="table-responsive"><table class="table package-table align-middle mb-0"><thead><tr><th>Template</th><th>Period</th><th class="text-end">KRA</th><th class="text-end">Behavior</th><th class="text-end">Total</th><th>Workflow status</th><th>Action</th></tr></thead><tbody><?php foreach ($evaluations as $evaluation): ?><tr><td><strong><?php echo e($evaluation['template_name']); ?></strong><div class="small text-muted"><?php echo e($evaluation['evaluation_type']); ?></div></td><td><?php echo e($evaluation['evaluation_period_start']); ?> to <?php echo e($evaluation['evaluation_period_end']); ?></td><td class="text-end tabular-nums"><?php echo $evaluation['kra_subtotal'] !== null ? number_format((float) $evaluation['kra_subtotal'], 2) : '—'; ?></td><td class="text-end tabular-nums"><?php echo $evaluation['behavior_average'] !== null ? number_format((float) $evaluation['behavior_average'], 2) : '—'; ?></td><td class="text-end tabular-nums"><?php echo $evaluation['total_score'] !== null ? number_format((float) $evaluation['total_score'], 2) : '—'; ?></td><td><strong><?php echo e($evaluation['status']); ?></strong><?php if (!empty($evaluation['package_id'])): ?><div class="small text-muted">Package: <?php echo e($evaluation['package_status']); ?></div><?php endif; ?></td><td><a class="btn btn-sm btn-outline-primary" href="<?php echo BASE_URL; ?>/employee/evaluation-history-view.php?evaluation_id=<?php echo (int) $evaluation['evaluation_id']; ?>">View details</a></td></tr><?php endforeach; ?></tbody></table></div></div></section>
    <?php endif; ?>
</main>
<?php require_once '../includes/footer.php'; ?>
