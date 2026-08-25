<?php
$page_title = 'Evaluation History & Audit Trail';
require_once '../includes/session-check.php';
require_once '../includes/functions.php';

ensureEvaluationWorkflowSchema($conn);
ensureOrganizationEvaluationPackageSchema($conn);
$user_id = (int) ($_SESSION['user_id'] ?? 0);
$employee_stmt = $conn->prepare('SELECT employee_id FROM users WHERE user_id = ? LIMIT 1');
$employee_stmt->bind_param('i', $user_id);
$employee_stmt->execute();
$employee = $employee_stmt->get_result()->fetch_assoc();
$employee_stmt->close();
$employee_id = (int) ($employee['employee_id'] ?? 0);

$evaluations = [];
if ($employee_id > 0) {
    $history_stmt = $conn->prepare("SELECT ev.evaluation_id, ev.evaluation_type, ev.evaluation_period_start, ev.evaluation_period_end,
            ev.kra_subtotal, ev.behavior_average, ev.total_score, ev.performance_level, ev.status, ev.submitted_date,
            et.template_name, ep.package_id, ep.status AS package_status, ep.shared_behavior_score,
            EXISTS(SELECT 1 FROM evaluation_scores es WHERE es.evaluation_id = ev.evaluation_id AND (es.supervisor_override_score IS NOT NULL OR es.dept_manager_override_score IS NOT NULL OR es.manager_override_score IS NOT NULL)) AS has_adjustments
        FROM evaluations ev
        JOIN evaluation_templates et ON et.template_id = ev.template_id
        LEFT JOIN evaluation_package_members pm ON pm.evaluation_id = ev.evaluation_id
        LEFT JOIN evaluation_packages ep ON ep.package_id = pm.package_id
        WHERE ev.employee_id = ? AND ev.deleted_at IS NULL
        ORDER BY COALESCE(ev.submitted_date, ev.created_at) DESC, ev.evaluation_id DESC");
    $history_stmt->bind_param('i', $employee_id);
    $history_stmt->execute();
    $evaluations = $history_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $history_stmt->close();
}

require_once '../includes/header.php';
?>
<main class="evaluation-packages container-fluid py-4">
    <section class="package-hero">
        <p class="mb-1 text-uppercase fw-bold" style="letter-spacing:1px; color:var(--rp-primary-gold-light); font-size:0.85rem;">
            Performance Audit Trail &amp; Revision Log
        </p>
        <h1 class="h3 mb-2 fw-bold">My Evaluation History &amp; Audit Trail</h1>
        <p class="mb-0">
            Audit and review your past and active evaluation cycles. Transparently inspect your original submitted self-ratings alongside supervisor adjustments, reviewer remarks, and sequential workflow progress.
        </p>
    </section>

    <?php if (!$evaluations): ?>
        <section class="package-empty">
            <i class="fas fa-history fa-3x text-muted mb-3" style="opacity:0.4;"></i>
            <h2 class="h5 fw-bold">No evaluation history found</h2>
            <p class="mb-0 text-muted">Your submitted evaluations and audit logs will appear here.</p>
        </section>
    <?php else: ?>
        <section class="package-card" role="region" aria-label="Evaluation History Audit Table">
            <header class="package-card__header">
                <h2 class="h5 mb-0 fw-bold"><i class="fas fa-list-alt me-2"></i>Submitted Evaluation Cycles (<?php echo count($evaluations); ?>)</h2>
                <div class="small text-muted">Original self-ratings and officer adjustments are tracked for every cycle.</div>
            </header>
            <div class="package-card__body">
                <div class="table-responsive">
                    <table class="package-table table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Template &amp; Type</th>
                                <th>Period</th>
                                <th class="text-end">Individual KRA</th>
                                <th class="text-end">Behavior Rating</th>
                                <th class="text-end">Total Score</th>
                                <th>Workflow &amp; Audit Stage</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($evaluations as $evaluation): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark" style="font-size:1.05rem;"><?php echo e($evaluation['template_name']); ?></div>
                                        <div class="small text-muted"><i class="fas fa-tag me-1"></i><?php echo e($evaluation['evaluation_type']); ?></div>
                                        <?php if (!empty($evaluation['has_adjustments'])): ?>
                                            <span class="audit-chip audit-chip--adjusted">
                                                <i class="fas fa-pen-fancy me-1"></i>Evaluator Score Adjustments Recorded
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="tabular-nums" style="font-size:0.95rem;">
                                        <?php echo e($evaluation['evaluation_period_start']); ?> &ndash; <?php echo e($evaluation['evaluation_period_end']); ?>
                                        <?php if (!empty($evaluation['submitted_date'])): ?>
                                            <div class="small text-muted">Submitted: <?php echo date('M d, Y', strtotime($evaluation['submitted_date'])); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end tabular-nums fw-semibold" style="font-size:1.05rem;">
                                        <?php echo $evaluation['kra_subtotal'] !== null ? number_format((float) $evaluation['kra_subtotal'], 2) : '&mdash;'; ?>
                                    </td>
                                    <td class="text-end tabular-nums fw-semibold" style="font-size:1.05rem;">
                                        <?php echo $evaluation['behavior_average'] !== null ? number_format((float) $evaluation['behavior_average'], 2) : '&mdash;'; ?>
                                    </td>
                                    <td class="text-end tabular-nums fw-bold text-dark" style="font-size:1.15rem;">
                                        <?php echo $evaluation['total_score'] !== null ? number_format((float) $evaluation['total_score'], 2) : '&mdash;'; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($evaluation['package_id'])): ?>
                                            <?php echo renderOrganizationPipelineBadge($conn, (int)$evaluation['package_id']); ?>
                                        <?php else: ?>
                                            <span class="badge <?php echo $evaluation['status'] === 'Approved' ? 'bg-success' : 'bg-secondary'; ?>">
                                                <?php echo e($evaluation['status']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a class="btn-action-view btn btn-sm" href="<?php echo BASE_URL; ?>/employee/evaluation-history-view.php?evaluation_id=<?php echo (int) $evaluation['evaluation_id']; ?>" title="Inspect original vs adjusted ratings and audit trail">
                                            <i class="fas fa-search-plus me-1"></i>Audit Details
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>
<?php require_once '../includes/footer.php'; ?>
