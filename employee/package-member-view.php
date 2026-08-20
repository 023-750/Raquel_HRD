<?php
$page_title = 'Team Member Evaluation';
require_once '../includes/session-check.php';
require_once '../includes/functions.php';

ensureOrganizationEvaluationPackageSchema($conn);
$user_id = (int) ($_SESSION['user_id'] ?? 0);
$package_id = (int) ($_GET['package_id'] ?? 0);
$evaluation_id = (int) ($_GET['evaluation_id'] ?? 0);
if ($package_id <= 0 || $evaluation_id <= 0) redirectWith(BASE_URL . '/employee/team-evaluation-packages.php', 'danger', 'Choose a package member to view.');

$access_stmt = $conn->prepare('SELECT 1 FROM evaluation_package_route_steps WHERE package_id = ? AND reviewer_user_id = ? LIMIT 1');
$access_stmt->bind_param('ii', $package_id, $user_id); $access_stmt->execute();
$has_access = $access_stmt->get_result()->fetch_assoc(); $access_stmt->close();
if (!$has_access) redirectWith(BASE_URL . '/employee/team-evaluation-packages.php', 'danger', 'You are not a reviewer for this package.');

$evaluation_stmt = $conn->prepare("SELECT ev.*, emp.first_name, emp.last_name, emp.job_title, et.template_name, ep.shared_behavior_score, ep.status AS package_status
    FROM evaluations ev
    JOIN evaluation_package_members pm ON pm.evaluation_id = ev.evaluation_id
    JOIN evaluation_packages ep ON ep.package_id = pm.package_id
    JOIN employees emp ON emp.employee_id = ev.employee_id
    JOIN evaluation_templates et ON et.template_id = ev.template_id
    WHERE pm.package_id = ? AND ev.evaluation_id = ? AND ev.deleted_at IS NULL LIMIT 1");
$evaluation_stmt->bind_param('ii', $package_id, $evaluation_id); $evaluation_stmt->execute();
$evaluation = $evaluation_stmt->get_result()->fetch_assoc(); $evaluation_stmt->close();
if (!$evaluation) redirectWith(BASE_URL . '/employee/team-evaluation-packages.php', 'danger', 'That package member evaluation is unavailable.');

$criteria_stmt = $conn->prepare("SELECT ec.section, ec.criterion_name, ec.description, es.score_value,
        COALESCE(es.manager_override_score, es.supervisor_override_score, es.dept_manager_override_score, es.score_value) AS reviewed_score
    FROM evaluation_scores es JOIN evaluation_criteria ec ON ec.criterion_id = es.criterion_id
    WHERE es.evaluation_id = ? ORDER BY ec.section, ec.sort_order");
$criteria_stmt->bind_param('i', $evaluation_id); $criteria_stmt->execute();
$criteria = $criteria_stmt->get_result()->fetch_all(MYSQLI_ASSOC); $criteria_stmt->close();
$kra = array_values(array_filter($criteria, static fn($criterion) => $criterion['section'] === 'KRA'));
$behavior = array_values(array_filter($criteria, static fn($criterion) => $criterion['section'] !== 'KRA'));

require_once '../includes/header.php';
?>
<main class="evaluation-packages container-fluid py-4">
    <section class="package-hero"><a class="history-back-link" href="<?php echo BASE_URL; ?>/employee/team-evaluation-packages.php"><i class="fas fa-arrow-left me-1"></i>Back to packages</a><p class="mb-1 small">Read-only team member evaluation</p><h1 class="h4 mb-2"><?php echo e($evaluation['first_name'] . ' ' . $evaluation['last_name']); ?></h1><p class="mb-0"><?php echo e($evaluation['job_title']); ?> · <?php echo e($evaluation['template_name']); ?> · <?php echo e($evaluation['evaluation_period_start']); ?> to <?php echo e($evaluation['evaluation_period_end']); ?></p></section>
    <section class="package-card"><div class="package-card__body"><div class="row g-3"><div class="col-sm-3 package-stat"><strong><?php echo number_format((float) $evaluation['kra_subtotal'], 2); ?></strong>KRA subtotal</div><div class="col-sm-3 package-stat"><strong><?php echo number_format((float) $evaluation['behavior_average'], 2); ?></strong>Behavior average</div><div class="col-sm-3 package-stat"><strong><?php echo number_format((float) $evaluation['total_score'], 2); ?></strong>Overall score</div><div class="col-sm-3 package-stat"><strong><?php echo e($evaluation['status']); ?></strong>Evaluation status</div></div><div class="package-note mt-4"><i class="fas fa-users me-1"></i>Current shared Core Behaviors &amp; Values score: <strong><?php echo $evaluation['shared_behavior_score'] !== null ? number_format((float) $evaluation['shared_behavior_score'], 2) : 'Pending'; ?></strong>.</div></div></section>
    <?php foreach (['KRA' => $kra, 'Core Behaviors & Values' => $behavior] as $section_name => $section_criteria): ?><?php if ($section_criteria): ?><section class="package-card"><header class="package-card__header"><h2 class="h5 mb-0"><?php echo e($section_name); ?></h2></header><div class="package-card__body"><div class="table-responsive"><table class="table package-table align-middle mb-0"><thead><tr><th>Criterion</th><th>Description</th><th class="text-end">Self-rating</th><th class="text-end">Reviewed rating</th></tr></thead><tbody><?php foreach ($section_criteria as $criterion): ?><?php $adjusted = abs((float) $criterion['reviewed_score'] - (float) $criterion['score_value']) > 0.001; ?><tr><td><strong><?php echo e($criterion['criterion_name']); ?></strong><?php if ($adjusted): ?><div class="history-adjusted"><i class="fas fa-pen me-1"></i>Adjusted during review</div><?php endif; ?></td><td class="small text-muted"><?php echo e($criterion['description']); ?></td><td class="text-end tabular-nums"><?php echo number_format((float) $criterion['score_value'], 2); ?></td><td class="text-end tabular-nums fw-semibold"><?php echo number_format((float) $criterion['reviewed_score'], 2); ?></td></tr><?php endforeach; ?></tbody></table></div></div></section><?php endif; ?><?php endforeach; ?>
</main>
<?php require_once '../includes/footer.php'; ?>
