<?php
$page_title = 'Review Team Member Evaluation';
require_once '../includes/session-check.php';
require_once '../includes/functions.php';
ensureOrganizationEvaluationPackageSchema($conn);

$user_id = (int) $_SESSION['user_id'];
$package_id = (int) ($_GET['package_id'] ?? $_POST['package_id'] ?? 0);
$evaluation_id = (int) ($_GET['evaluation_id'] ?? $_POST['evaluation_id'] ?? 0);

$access = $conn->prepare("SELECT rs.step_label, rs.step_type, ep.department_id, ep.status FROM evaluation_package_route_steps rs JOIN evaluation_packages ep ON ep.package_id = rs.package_id JOIN evaluation_package_members pm ON pm.package_id = ep.package_id WHERE ep.package_id = ? AND pm.evaluation_id = ? AND rs.reviewer_user_id = ? AND rs.action_status = 'Pending' LIMIT 1");
$access->bind_param('iii', $package_id, $evaluation_id, $user_id); $access->execute(); $review_step = $access->get_result()->fetch_assoc(); $access->close();
if (!$review_step) redirectWith(BASE_URL . '/employee/team-evaluation-packages.php', 'danger', 'This team-member evaluation is not assigned to you for review.');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $ratings = $_POST['rating'] ?? [];
    $score_stmt = $conn->prepare("SELECT es.score_id, es.score_value, es.supervisor_override_score, ec.criterion_id, ec.criterion_name FROM evaluation_scores es JOIN evaluation_criteria ec ON ec.criterion_id = es.criterion_id WHERE es.evaluation_id = ?");
    $score_stmt->bind_param('i', $evaluation_id); $score_stmt->execute(); $scores = $score_stmt->get_result()->fetch_all(MYSQLI_ASSOC); $score_stmt->close();
    $changes = [];
    foreach ($scores as $score) {
        $score_id = (int) $score['score_id'];
        if (!isset($ratings[$score_id]) || $ratings[$score_id] === '') continue;
        $new_rating = (float) $ratings[$score_id];
        if ($new_rating < 1 || $new_rating > 4) redirectWith($_SERVER['REQUEST_URI'], 'danger', 'Every adjusted rating must be between 1 and 4.');
        $current = $score['supervisor_override_score'] !== null ? (float) $score['supervisor_override_score'] : (float) $score['score_value'];
        if (abs($new_rating - $current) < 0.001) continue;
        $update = $conn->prepare('UPDATE evaluation_scores SET supervisor_override_score = ?, supervisor_override_by = ?, supervisor_override_at = NOW() WHERE score_id = ?');
        $update->bind_param('dii', $new_rating, $user_id, $score_id); $update->execute(); $update->close();
        $changes[] = $score['criterion_name'] . ': ' . number_format($current, 2) . ' → ' . number_format($new_rating, 2);
    }
    if ($changes) {
        recalculateEvaluationScores($conn, $evaluation_id);
        recalculateOrganizationPackageBehaviorScore($conn, $package_id);
        $remarks = 'Adjusted ' . implode('; ', $changes);
        $audit = $conn->prepare("INSERT INTO evaluation_package_audit (package_id, user_id, action, remarks) VALUES (?, ?, 'MEMBER_SCORES_ADJUSTED', ?)");
        $audit->bind_param('iis', $package_id, $user_id, $remarks); $audit->execute(); $audit->close();
        redirectWith(BASE_URL . '/employee/package-member-review.php?package_id=' . $package_id . '&evaluation_id=' . $evaluation_id, 'success', 'Individual ratings and the shared Behavior score were recalculated.');
    }
    redirectWith(BASE_URL . '/employee/package-member-review.php?package_id=' . $package_id . '&evaluation_id=' . $evaluation_id, 'info', 'No rating changes were made.');
}

require_once '../includes/header.php';
$evaluation_stmt = $conn->prepare("SELECT ev.*, emp.first_name, emp.last_name, emp.job_title, et.template_name, ep.shared_behavior_score FROM evaluations ev JOIN evaluation_package_members pm ON pm.evaluation_id = ev.evaluation_id JOIN evaluation_packages ep ON ep.package_id = pm.package_id JOIN employees emp ON emp.employee_id = ev.employee_id JOIN evaluation_templates et ON et.template_id = ev.template_id WHERE ev.evaluation_id = ? AND ep.package_id = ? LIMIT 1");
$evaluation_stmt->bind_param('ii', $evaluation_id, $package_id); $evaluation_stmt->execute(); $evaluation = $evaluation_stmt->get_result()->fetch_assoc(); $evaluation_stmt->close();
$criteria_stmt = $conn->prepare("SELECT es.score_id, es.score_value, es.supervisor_override_score, ec.section, ec.criterion_name, ec.description, ec.weight FROM evaluation_scores es JOIN evaluation_criteria ec ON ec.criterion_id = es.criterion_id WHERE es.evaluation_id = ? ORDER BY ec.section, ec.sort_order");
$criteria_stmt->bind_param('i', $evaluation_id); $criteria_stmt->execute(); $criteria = $criteria_stmt->get_result()->fetch_all(MYSQLI_ASSOC); $criteria_stmt->close();
?>
<main class="evaluation-packages container-fluid py-4">
    <section class="package-hero"><p class="mb-1 small"><?php echo e($review_step['step_label']); ?></p><h1 class="h4 mb-2">Review <?php echo e($evaluation['first_name'] . ' ' . $evaluation['last_name']); ?></h1><p class="mb-0"><?php echo e($evaluation['job_title']); ?> · <?php echo e($evaluation['template_name']); ?></p></section>
    <section class="package-card"><div class="package-card__body"><div class="row g-3"><div class="col-sm-4 package-stat"><strong class="tabular-nums"><?php echo number_format((float)$evaluation['kra_subtotal'], 2); ?></strong>Current individual KRA</div><div class="col-sm-4 package-stat"><strong class="tabular-nums"><?php echo number_format((float)$evaluation['behavior_average'], 2); ?></strong>Current individual Behavior</div><div class="col-sm-4 package-stat"><strong class="tabular-nums"><?php echo number_format((float)$evaluation['shared_behavior_score'], 2); ?></strong>Current shared team Behavior</div></div></div></section>
    <form method="post" class="package-card"><div class="package-card__body"><input type="hidden" name="package_id" value="<?php echo $package_id; ?>"><input type="hidden" name="evaluation_id" value="<?php echo $evaluation_id; ?>"><?php echo csrfField(); ?><p class="text-muted small">The original self-rating is retained. Saving a different value records your adjustment and recalculates this employee’s totals. Changes to Behavior ratings also automatically recalculate the shared team Behavior score.</p>
        <?php foreach (['KRA' => 'KRA Ratings', 'Behavior' => 'Core Behaviors & Values'] as $section => $label): ?><h2 class="h5 mt-4 mb-3"><?php echo $label; ?></h2><div class="table-responsive"><table class="table package-table align-middle"><thead><tr><th>Criterion</th><?php if ($section === 'KRA'): ?><th>Weight</th><?php endif; ?><th>Self-rating</th><th>Reviewer rating</th></tr></thead><tbody><?php foreach ($criteria as $criterion): ?><?php if ($criterion['section'] !== $section) continue; $effective = $criterion['supervisor_override_score'] !== null ? (float)$criterion['supervisor_override_score'] : (float)$criterion['score_value']; ?><tr><td><strong><?php echo e($criterion['criterion_name']); ?></strong><?php if ($criterion['description']): ?><div class="small text-muted"><?php echo e($criterion['description']); ?></div><?php endif; ?></td><?php if ($section === 'KRA'): ?><td class="tabular-nums"><?php echo number_format((float)$criterion['weight'], 2); ?>%</td><?php endif; ?><td class="tabular-nums"><?php echo number_format((float)$criterion['score_value'], 2); ?></td><td><input class="form-control" name="rating[<?php echo (int)$criterion['score_id']; ?>]" type="number" min="1" max="4" step="0.01" value="<?php echo e(number_format($effective, 2, '.', '')); ?>" aria-label="Reviewer rating for <?php echo e($criterion['criterion_name']); ?>"></td></tr><?php endforeach; ?></tbody></table></div><?php endforeach; ?><div class="d-flex flex-wrap gap-2 mt-4"><button class="btn btn-primary" type="submit">Save individual adjustments</button><a class="btn btn-outline-secondary" href="<?php echo BASE_URL; ?>/employee/team-evaluation-packages.php">Back to package</a></div></div></form>
</main>
<?php require_once '../includes/footer.php'; ?>
