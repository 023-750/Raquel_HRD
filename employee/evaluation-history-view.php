<?php
$page_title = 'Evaluation Details';
require_once '../includes/session-check.php';
require_once '../includes/functions.php';

ensureOrganizationEvaluationPackageSchema($conn);
$user_id = (int) ($_SESSION['user_id'] ?? 0);
$evaluation_id = (int) ($_GET['evaluation_id'] ?? 0);
if ($evaluation_id <= 0) redirectWith(BASE_URL . '/employee/evaluation-history.php', 'danger', 'Choose an evaluation to view.');

$evaluation_stmt = $conn->prepare("SELECT ev.*, et.template_name, et.kra_weight, et.behavior_weight, ep.package_id, ep.status AS package_status, ep.shared_behavior_score
    FROM evaluations ev
    JOIN users u ON u.employee_id = ev.employee_id AND u.user_id = ?
    JOIN evaluation_templates et ON et.template_id = ev.template_id
    LEFT JOIN evaluation_package_members pm ON pm.evaluation_id = ev.evaluation_id
    LEFT JOIN evaluation_packages ep ON ep.package_id = pm.package_id
    WHERE ev.evaluation_id = ? AND ev.deleted_at IS NULL LIMIT 1");
$evaluation_stmt->bind_param('ii', $user_id, $evaluation_id); $evaluation_stmt->execute();
$evaluation = $evaluation_stmt->get_result()->fetch_assoc(); $evaluation_stmt->close();
if (!$evaluation) redirectWith(BASE_URL . '/employee/evaluation-history.php', 'danger', 'That evaluation is unavailable.');

$criteria_stmt = $conn->prepare("SELECT ec.section, ec.criterion_name, ec.description, ec.weight, es.score_value,
        es.dept_manager_override_score, es.supervisor_override_score, es.manager_override_score,
        COALESCE(es.manager_override_score, es.supervisor_override_score, es.dept_manager_override_score, es.score_value) AS reviewed_score
    FROM evaluation_scores es JOIN evaluation_criteria ec ON ec.criterion_id = es.criterion_id
    WHERE es.evaluation_id = ? ORDER BY ec.section, ec.sort_order");
$criteria_stmt->bind_param('i', $evaluation_id); $criteria_stmt->execute();
$criteria = $criteria_stmt->get_result()->fetch_all(MYSQLI_ASSOC); $criteria_stmt->close();
$kra = array_values(array_filter($criteria, static fn($criterion) => $criterion['section'] === 'KRA'));
$behavior = array_values(array_filter($criteria, static fn($criterion) => $criterion['section'] !== 'KRA'));
$remarks = array_filter([
    $evaluation['supervisor_comments'] ?? '',
    $evaluation['dept_manager_comments'] ?? '',
    $evaluation['evaluator_comments'] ?? '',
    $evaluation['manager_comments'] ?? '',
], static fn($remark) => trim((string) $remark) !== '');

require_once '../includes/header.php';
?>
<main class="evaluation-packages container-fluid py-4">
    <section class="package-hero">
        <a class="history-back-link" href="<?php echo BASE_URL; ?>/employee/evaluation-history.php"><i class="fas fa-arrow-left me-1"></i>Back to history</a>
        <p class="mb-1 small">Read-only evaluation record</p>
        <h1 class="h4 mb-2"><?php echo e($evaluation['template_name']); ?></h1>
        <p class="mb-0"><?php echo e($evaluation['evaluation_period_start']); ?> to <?php echo e($evaluation['evaluation_period_end']); ?> · <?php echo e($evaluation['status']); ?></p>
    </section>

    <section class="package-card"><div class="package-card__body"><div class="row g-3"><div class="col-sm-3 package-stat"><strong><?php echo $evaluation['kra_subtotal'] !== null ? number_format((float) $evaluation['kra_subtotal'], 2) : '—'; ?></strong>KRA subtotal</div><div class="col-sm-3 package-stat"><strong><?php echo $evaluation['behavior_average'] !== null ? number_format((float) $evaluation['behavior_average'], 2) : '—'; ?></strong>Final Behavior</div><div class="col-sm-3 package-stat"><strong><?php echo $evaluation['total_score'] !== null ? number_format((float) $evaluation['total_score'], 2) : '—'; ?></strong>Overall score</div><div class="col-sm-3 package-stat"><strong><?php echo e($evaluation['performance_level'] ?: '—'); ?></strong>Performance level</div></div><?php if (!empty($evaluation['package_id'])): ?><div class="package-note mt-4"><i class="fas fa-users me-1"></i>Core Behaviors &amp; Values was finalized as a shared department score: <strong><?php echo $evaluation['shared_behavior_score'] !== null ? number_format((float) $evaluation['shared_behavior_score'], 2) : 'Pending'; ?></strong>.</div><?php endif; ?></div></section>

    <?php foreach (['KRA' => $kra, 'Core Behaviors & Values' => $behavior] as $section_name => $section_criteria): ?>
        <?php if ($section_criteria): ?><section class="package-card"><header class="package-card__header"><h2 class="h5 mb-0"><?php echo e($section_name); ?></h2></header><div class="package-card__body"><div class="table-responsive"><table class="table package-table align-middle mb-0"><thead><tr><th>Criterion</th><th>Description</th><th class="text-end">Self-rating</th><th class="text-end">Reviewed rating</th></tr></thead><tbody><?php foreach ($section_criteria as $criterion): ?><?php $adjusted = abs((float) $criterion['reviewed_score'] - (float) $criterion['score_value']) > 0.001; ?><tr><td><strong><?php echo e($criterion['criterion_name']); ?></strong><?php if ($adjusted): ?><div class="history-adjusted"><i class="fas fa-pen me-1"></i>Adjusted during review</div><?php endif; ?></td><td class="small text-muted"><?php echo e($criterion['description']); ?></td><td class="text-end tabular-nums"><?php echo number_format((float) $criterion['score_value'], 2); ?></td><td class="text-end tabular-nums fw-semibold"><?php echo number_format((float) $criterion['reviewed_score'], 2); ?></td></tr><?php endforeach; ?></tbody></table></div></div></section><?php endif; ?>
    <?php endforeach; ?>

    <?php if ($remarks): ?><section class="package-card"><header class="package-card__header"><h2 class="h5 mb-0">Review remarks</h2></header><div class="package-card__body"><div class="history-remarks"><?php foreach ($remarks as $remark): ?><p><?php echo nl2br(e($remark)); ?></p><?php endforeach; ?></div></div></section><?php endif; ?>
</main>
<?php require_once '../includes/footer.php'; ?>
