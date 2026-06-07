<?php
$page_title = 'Evaluation Status';
require_once '../includes/session-check.php';
checkRole(['Employee']);
require_once '../includes/functions.php';

$employee_id = (int)($_SESSION['employee_id'] ?? 0);

$evaluations = $conn->query("
    SELECT e.evaluation_id, e.evaluation_type, e.status, e.evaluation_period_start, e.evaluation_period_end,
           et.template_name, e.total_score, e.performance_level, e.submitted_date, e.updated_at
    FROM evaluations e
    LEFT JOIN evaluation_templates et ON e.template_id = et.template_id
    WHERE e.employee_id = $employee_id
      AND e.deleted_at IS NULL
    ORDER BY COALESCE(e.submitted_date, e.updated_at, e.created_at) DESC, e.evaluation_id DESC
");
$evaluation_rows = $evaluations ? $evaluations->fetch_all(MYSQLI_ASSOC) : [];

require_once '../includes/header.php';
?>

<style>
.eval-status-page .eval-status-cards {
    display: grid;
    gap: 12px;
    padding: 12px;
}

.eval-status-page .eval-status-card {
    background: #fff;
    border: 1px solid var(--border-color, #e8ece3);
    border-radius: 14px;
    box-shadow: 0 6px 18px rgba(12, 32, 8, 0.05);
    overflow: hidden;
}

.eval-status-page .eval-status-card-header {
    align-items: flex-start;
    border-bottom: 1px solid #f0f4eb;
    display: flex;
    gap: 12px;
    justify-content: space-between;
    padding: 14px 14px 12px;
}

.eval-status-page .eval-status-card-title {
    color: var(--text-dark, #1a2e05);
    font-size: 0.92rem;
    font-weight: 700;
    line-height: 1.35;
    margin: 0;
}

.eval-status-page .eval-status-card-type {
    color: var(--text-muted);
    font-size: 0.75rem;
    margin-top: 4px;
}

.eval-status-page .eval-status-card-body {
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    padding: 12px 14px;
}

.eval-status-page .eval-status-field {
    min-width: 0;
}

.eval-status-page .eval-status-field-label {
    color: var(--text-muted);
    display: block;
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.03em;
    margin-bottom: 3px;
    text-transform: uppercase;
}

.eval-status-page .eval-status-field-value {
    color: var(--text-dark, #1a2e05);
    font-size: 0.82rem;
    font-weight: 600;
    line-height: 1.35;
}

.eval-status-page .eval-status-field-value .badge {
    font-size: 0.68rem;
    vertical-align: middle;
}

.eval-status-page .eval-status-card-footer {
    background: #fafcf8;
    border-top: 1px solid #f0f4eb;
    padding: 12px 14px;
}

.eval-status-page .eval-status-card-footer .btn {
    border-radius: 999px;
    font-size: 0.82rem;
    font-weight: 700;
    padding: 0.55rem 1rem;
    width: 100%;
}

.eval-status-page .eval-status-table-wrap {
    padding: 0;
}

.eval-status-page .eval-status-table-wrap .table thead th {
    background: #f8faf5;
    border-bottom: 1px solid #eef2e8;
    color: var(--text-muted);
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    white-space: nowrap;
}
</style>

<div class="page-hero fadeup">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-3">
        <div>
            <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.55);">Employee Portal · Evaluation</div>
            <h4 class="text-white fw-bold mb-0 mt-1"><i class="fas fa-clipboard-check me-2" style="color:var(--primary-light);"></i>Evaluation Status</h4>
        </div>
        <div class="d-none d-md-block text-end">
            <a href="<?php echo BASE_URL; ?>/employee/dashboard.php" class="btn btn-outline-light btn-sm rounded-pill px-3 mb-1">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>
    <p class="text-white-50 small mb-0 d-none d-md-block"><i class="fas fa-info-circle me-1"></i>Track each evaluation and where it stands in the review process.</p>
</div>

<!-- Mobile-only section -->
<div class="d-md-none d-flex justify-content-between align-items-center mt-3 mb-4 flex-wrap gap-3 fadeup" style="animation-delay: 0.1s;">
    <a href="<?php echo BASE_URL; ?>/employee/dashboard.php" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
        <i class="fas fa-arrow-left me-2"></i>Back to My Dashboard
    </a>
    <div class="alert alert-light border-0 shadow-sm py-2 px-3 mb-0" style="border-radius: 10px; font-size: 0.85rem; background: #fff;">
        <i class="fas fa-info-circle me-2 text-primary"></i>
        <span class="text-muted fw-500">Track your evaluation status.</span>
    </div>
</div>

<div class="row g-4 eval-status-page">
    <div class="col-12">
        <div class="content-card">
            <div class="card-header">
                <h5><i class="fas fa-list me-2"></i>My Evaluations</h5>
                <?php if (!empty($evaluation_rows)): ?>
                    <span class="badge bg-light text-muted border"><?php echo count($evaluation_rows); ?> total</span>
                <?php endif; ?>
            </div>
            <?php if (empty($evaluation_rows)): ?>
                <div class="card-body">
                    <div class="empty-state py-5">
                        <i class="fas fa-inbox d-block"></i>
                        <p class="mb-0">No evaluations yet.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="eval-status-cards d-md-none">
                    <?php foreach ($evaluation_rows as $eval): ?>
                        <?php
                        $can_edit = in_array($eval['status'], ['Draft', 'Returned', 'Pending Self-Rating'], true);
                        $view_url = BASE_URL . '/employee/self-rating.php' . ($can_edit ? '?edit=' : '?view=') . (int) $eval['evaluation_id'];
                        $period_start = formatDate($eval['evaluation_period_start'] ?? '');
                        $period_end = formatDate($eval['evaluation_period_end'] ?? '');
                        $period_label = ($period_start && $period_end) ? $period_start . ' – ' . $period_end : ($period_start ?: $period_end ?: '—');
                        ?>
                        <article class="eval-status-card">
                            <div class="eval-status-card-header">
                                <div class="min-w-0">
                                    <h6 class="eval-status-card-title"><?php echo e($eval['template_name'] ?? 'Evaluation'); ?></h6>
                                    <div class="eval-status-card-type"><?php echo e($eval['evaluation_type'] ?? 'Evaluation'); ?></div>
                                </div>
                                <span class="badge <?php echo getStatusBadgeClass($eval['status']); ?> flex-shrink-0"><?php echo e($eval['status']); ?></span>
                            </div>
                            <div class="eval-status-card-body">
                                <div class="eval-status-field">
                                    <span class="eval-status-field-label">Period</span>
                                    <span class="eval-status-field-value"><?php echo e($period_label); ?></span>
                                </div>
                                <div class="eval-status-field">
                                    <span class="eval-status-field-label">Score</span>
                                    <span class="eval-status-field-value">
                                        <?php echo e($eval['total_score'] ?? '0.00'); ?>
                                        <?php if (!empty($eval['performance_level'])): ?>
                                            <span class="badge bg-primary ms-1"><?php echo e($eval['performance_level']); ?></span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="eval-status-field" style="grid-column: 1 / -1;">
                                    <span class="eval-status-field-label">Last Updated</span>
                                    <span class="eval-status-field-value"><?php echo formatDateTime($eval['updated_at'] ?? $eval['submitted_date'] ?? ''); ?></span>
                                </div>
                            </div>
                            <div class="eval-status-card-footer">
                                <a href="<?php echo $view_url; ?>" class="btn btn-<?php echo $can_edit ? 'primary' : 'outline-primary'; ?>">
                                    <i class="fas fa-<?php echo $can_edit ? 'edit' : 'eye'; ?> me-1"></i><?php echo $can_edit ? 'Continue Evaluation' : 'View Evaluation'; ?>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <div class="card-body p-0 eval-status-table-wrap d-none d-md-block">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3">Status</th>
                                    <th>Template</th>
                                    <th>Type</th>
                                    <th>Period</th>
                                    <th>Score</th>
                                    <th>Last Updated</th>
                                    <th class="text-end pe-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($evaluation_rows as $eval): ?>
                                    <?php
                                    $can_edit = in_array($eval['status'], ['Draft', 'Returned', 'Pending Self-Rating'], true);
                                    $view_url = BASE_URL . '/employee/self-rating.php' . ($can_edit ? '?edit=' : '?view=') . (int) $eval['evaluation_id'];
                                    ?>
                                    <tr>
                                        <td class="ps-3"><span class="badge <?php echo getStatusBadgeClass($eval['status']); ?>"><?php echo e($eval['status']); ?></span></td>
                                        <td><?php echo e($eval['template_name'] ?? '—'); ?></td>
                                        <td><div class="fw-semibold"><?php echo e($eval['evaluation_type']); ?></div></td>
                                        <td>
                                            <?php echo formatDate($eval['evaluation_period_start'] ?? ''); ?> –<br>
                                            <?php echo formatDate($eval['evaluation_period_end'] ?? ''); ?>
                                        </td>
                                        <td>
                                            <strong><?php echo e($eval['total_score'] ?? '0.00'); ?></strong>
                                            <?php if (!empty($eval['performance_level'])): ?>
                                                <span class="badge bg-primary"><?php echo e($eval['performance_level']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo formatDateTime($eval['updated_at'] ?? $eval['submitted_date'] ?? ''); ?></td>
                                        <td class="text-end pe-3">
                                            <a href="<?php echo $view_url; ?>" class="btn btn-sm btn-outline-<?php echo $can_edit ? 'primary' : 'info'; ?>">
                                                <i class="fas fa-<?php echo $can_edit ? 'edit' : 'eye'; ?> me-1"></i><?php echo $can_edit ? 'Continue' : 'View'; ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
