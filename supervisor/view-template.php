<?php
$page_title = 'View Template';
require_once '../includes/session-check.php';
checkRole(['HR Supervisor']);
require_once '../includes/functions.php';

$branch_id = (int) ($_SESSION['branch_id'] ?? 0);
$tid = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($tid <= 0) {
    redirectWith(BASE_URL . '/supervisor/templates.php', 'danger', 'Invalid template ID.');
}

function supervisorViewTemplateRows(mysqli $conn, string $sql, string $types = '', array $params = []): array
{
    $stmt = $conn->prepare($sql);
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function supervisorViewTemplateMethodLabel(string $method): array
{
    if ($method === 'Scale_1_10') {
        return ['icon' => 'fa-list-ol', 'label' => 'Scale 1-10'];
    }
    if ($method === 'Percentage') {
        return ['icon' => 'fa-percent', 'label' => 'Percentage'];
    }
    if ($method === 'Scale_1_5') {
        return ['icon' => 'fa-star-half-alt', 'label' => 'Scale 1-5'];
    }
    return ['icon' => 'fa-star', 'label' => 'Scale 1-4'];
}

$template_rows = supervisorViewTemplateRows(
    $conn,
    "SELECT et.*, u.full_name AS created_by_name,
            (SELECT COUNT(*)
             FROM evaluations ev
             INNER JOIN employees e ON ev.employee_id = e.employee_id
             WHERE ev.template_id = et.template_id
               AND ev.deleted_at IS NULL
               AND e.branch_id = ?) AS branch_usage_count
     FROM evaluation_templates et
     LEFT JOIN users u ON et.created_by = u.user_id
     WHERE et.template_id = ?
       AND et.status = 'Active'
       AND et.deleted_at IS NULL
     LIMIT 1",
    "ii",
    [$branch_id, $tid]
);
$template = $template_rows[0] ?? null;

if (!$template) {
    redirectWith(BASE_URL . '/supervisor/templates.php', 'danger', 'Template not found or is no longer active.');
}

$criteria = supervisorViewTemplateRows(
    $conn,
    "SELECT *
     FROM evaluation_criteria
     WHERE template_id = ?
     ORDER BY section = 'Behavior', sort_order, criterion_id",
    "i",
    [$tid]
);

$kra_criteria = [];
$behavior_criteria = [];
foreach ($criteria as $criterion) {
    if (($criterion['section'] ?? '') === 'Behavior') {
        $behavior_criteria[] = $criterion;
    } else {
        $kra_criteria[] = $criterion;
    }
}

$total_criteria = count($criteria);
$kra_total_weight = array_sum(array_map(fn($row) => (float) $row['weight'], $kra_criteria));
$behavior_total_weight = array_sum(array_map(fn($row) => (float) $row['weight'], $behavior_criteria));
$kra_weight = (float) ($template['kra_weight'] ?? 80);
$behavior_weight = (float) ($template['behavior_weight'] ?? 20);
$target_position = $template['target_position'] ?: 'General / All Positions';
$form_code = $template['form_code'] ?: 'Not specified';

require_once '../includes/header.php';
?>

<style>
    .supervisor-template-detail .template-info-card,
    .supervisor-template-detail .scoring-guide-card,
    .supervisor-template-detail .criteria-card {
        border: 1px solid #eef2e8;
        border-radius: 14px;
        box-shadow: 0 8px 22px rgba(12, 32, 8, 0.05);
        overflow: hidden;
    }

    .supervisor-template-detail .meta-grid {
        display: grid;
        gap: 10px;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .supervisor-template-detail .meta-item {
        background: #f8faf7;
        border: 1px solid #eef2e8;
        border-radius: 10px;
        padding: 12px;
    }

    .supervisor-template-detail .meta-item span {
        color: var(--text-muted);
        display: block;
        font-size: 0.68rem;
        font-weight: 800;
        margin-bottom: 4px;
        text-transform: uppercase;
    }

    .supervisor-template-detail .meta-item strong {
        color: #1f2f12;
        font-size: 0.9rem;
    }

    .supervisor-template-detail .weight-split {
        display: flex;
        gap: 5px;
        height: 8px;
    }

    .supervisor-template-detail .weight-split span {
        border-radius: 999px;
        display: block;
    }

    .supervisor-template-detail .weight-split .kra { background: #294306; }
    .supervisor-template-detail .weight-split .behavior { background: #BD9414; }

    .supervisor-template-detail .criteria-section-title {
        align-items: center;
        display: flex;
        gap: 10px;
        margin-bottom: 12px;
    }

    .supervisor-template-detail .section-icon {
        align-items: center;
        background: rgba(41, 67, 6, 0.08);
        border-radius: 10px;
        color: var(--primary-blue);
        display: inline-flex;
        height: 36px;
        justify-content: center;
        width: 36px;
    }

    .supervisor-template-detail .criteria-list {
        display: grid;
        gap: 12px;
    }

    .supervisor-template-detail .criteria-item {
        border: 1px solid #eef2e8;
        border-radius: 12px;
        padding: 14px;
    }

    .supervisor-template-detail .criteria-item-header {
        align-items: flex-start;
        display: flex;
        gap: 12px;
        justify-content: space-between;
    }

    .supervisor-template-detail .criteria-number {
        align-items: center;
        background: #f3f6ef;
        border-radius: 999px;
        color: #294306;
        display: inline-flex;
        flex: 0 0 30px;
        font-size: 0.78rem;
        font-weight: 800;
        height: 30px;
        justify-content: center;
        width: 30px;
    }

    .supervisor-template-detail .guide-row {
        align-items: center;
        border-bottom: 1px solid #eef2e8;
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
    }

    .supervisor-template-detail .guide-row:last-child {
        border-bottom: 0;
    }

    .supervisor-template-detail .bg-primary-subtle { background-color: #e7f1ff; }
    .supervisor-template-detail .bg-info-subtle { background-color: #e0f7fa; }
    .supervisor-template-detail .bg-success-subtle { background-color: #e8f5e9; }
    .supervisor-template-detail .bg-warning-subtle { background-color: #fff9c4; }
    .supervisor-template-detail .bg-danger-subtle { background-color: #ffebee; }
    .supervisor-template-detail .bg-secondary-subtle { background-color: #f5f5f5; }

    @media (max-width: 768px) {
        .supervisor-template-detail .meta-grid {
            grid-template-columns: 1fr;
        }

        .supervisor-template-detail .criteria-item-header {
            display: grid;
        }

        .supervisor-template-detail .criteria-item-header .d-flex {
            min-width: 0;
        }
    }
</style>

<div class="supervisor-template-detail">
    <div class="page-hero fadeup">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
            <div>
                <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:0;color:rgba(255,255,255,.55);">HR Supervisor · Template Detail</div>
                <h4 class="text-white fw-bold mb-0 mt-1"><i class="fas fa-file-signature me-2" style="color:#BD9414;"></i><?php echo e($template['template_name']); ?></h4>
            </div>
            <div class="d-flex gap-2 flex-wrap justify-content-end">
                <a href="<?php echo BASE_URL; ?>/supervisor/templates.php" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left me-2"></i>Templates
                </a>
            </div>
        </div>
        <p class="text-white-50 small mb-0"><i class="fas fa-lock me-1"></i>Read-only criteria, scoring methods, and form metadata.</p>

        <div class="row g-3 mt-4">
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-value"><?php echo number_format($total_criteria); ?></div>
                            <div class="stat-label">Criteria</div>
                        </div>
                        <i class="fas fa-list-check stat-icon text-white-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-value"><?php echo number_format(count($kra_criteria)); ?></div>
                            <div class="stat-label">KRA Items</div>
                        </div>
                        <i class="fas fa-bullseye stat-icon" style="color:#BD9414;"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-value"><?php echo number_format(count($behavior_criteria)); ?></div>
                            <div class="stat-label">Behavior Items</div>
                        </div>
                        <i class="fas fa-heart stat-icon" style="color:#17a2b8;"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-value"><?php echo number_format((int) $template['branch_usage_count']); ?></div>
                            <div class="stat-label">Branch Use</div>
                        </div>
                        <i class="fas fa-clipboard-list stat-icon" style="color:#28a745;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="content-card template-info-card mb-4 fadeup fadeup-1">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-circle-info me-2 text-primary"></i>Template Information</h5>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold mb-2"><?php echo e($template['template_name']); ?></h6>
                    <p class="text-muted small mb-3"><?php echo nl2br(e($template['description'] ?: 'No description provided.')); ?></p>

                    <div class="meta-grid mb-3">
                        <div class="meta-item">
                            <span>Target Position</span>
                            <strong><?php echo e($target_position); ?></strong>
                        </div>
                        <div class="meta-item">
                            <span>Evaluation Type</span>
                            <strong><?php echo e($template['evaluation_type'] ?? 'Annual'); ?></strong>
                        </div>
                        <div class="meta-item">
                            <span>Form Code</span>
                            <strong><?php echo e($form_code); ?></strong>
                        </div>
                        <div class="meta-item">
                            <span>Created By</span>
                            <strong><?php echo e($template['created_by_name'] ?: 'N/A'); ?></strong>
                        </div>
                        <div class="meta-item">
                            <span>Revision Date</span>
                            <strong><?php echo !empty($template['revision_date']) ? formatDate($template['revision_date']) : 'N/A'; ?></strong>
                        </div>
                        <div class="meta-item">
                            <span>Effective Date</span>
                            <strong><?php echo !empty($template['effective_date_form']) ? formatDate($template['effective_date_form']) : 'N/A'; ?></strong>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2 small text-muted">
                        <span>KRA <?php echo number_format($kra_weight, 0); ?>%</span>
                        <span>Behavior <?php echo number_format($behavior_weight, 0); ?>%</span>
                    </div>
                    <div class="weight-split mb-3">
                        <span class="kra" style="flex: <?php echo max(1, $kra_weight); ?>;"></span>
                        <span class="behavior" style="flex: <?php echo max(1, $behavior_weight); ?>;"></span>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge <?php echo abs($kra_total_weight - 100) < 0.01 ? 'bg-success-subtle text-success border-success-subtle' : 'bg-warning-subtle text-warning border-warning-subtle'; ?> border px-2">
                            KRA criteria total <?php echo number_format($kra_total_weight, 2); ?>%
                        </span>
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2">
                            Behavior criteria <?php echo number_format($behavior_total_weight, 2); ?>%
                        </span>
                    </div>
                </div>
            </div>

            <div class="content-card scoring-guide-card fadeup fadeup-2">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-gauge-high me-2 text-primary"></i>Scoring Guide</h5>
                </div>
                <div class="card-body">
                    <div class="guide-row">
                        <div>
                            <div class="fw-bold text-success">Outstanding</div>
                            <small class="text-muted">Consistently exceeds requirements.</small>
                        </div>
                        <span class="badge bg-success">3.60 - 4.00</span>
                    </div>
                    <div class="guide-row">
                        <div>
                            <div class="fw-bold text-info">Exceeds Expectations</div>
                            <small class="text-muted">Often performs above standard.</small>
                        </div>
                        <span class="badge bg-info">2.60 - 3.59</span>
                    </div>
                    <div class="guide-row">
                        <div>
                            <div class="fw-bold text-warning">Meets Expectations</div>
                            <small class="text-muted">Meets role requirements.</small>
                        </div>
                        <span class="badge bg-warning text-dark">2.00 - 2.59</span>
                    </div>
                    <div class="guide-row">
                        <div>
                            <div class="fw-bold text-danger">Needs Improvement</div>
                            <small class="text-muted">Requires close follow-up.</small>
                        </div>
                        <span class="badge bg-danger">Below 2.00</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="content-card criteria-card fadeup fadeup-1 mb-4">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <h5 class="mb-0"><i class="fas fa-bullseye me-2 text-primary"></i>KRA Criteria</h5>
                    <span class="badge bg-light text-muted border"><?php echo number_format($kra_total_weight, 2); ?>% total</span>
                </div>
                <div class="card-body">
                    <?php if (empty($kra_criteria)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-bullseye fa-2x mb-2 d-block opacity-25"></i>
                            No KRA criteria defined.
                        </div>
                    <?php else: ?>
                        <div class="criteria-list">
                            <?php foreach ($kra_criteria as $index => $criterion): ?>
                                <?php $method = supervisorViewTemplateMethodLabel($criterion['scoring_method'] ?? 'Scale_1_4'); ?>
                                <div class="criteria-item">
                                    <div class="criteria-item-header">
                                        <div class="d-flex gap-3">
                                            <span class="criteria-number"><?php echo $index + 1; ?></span>
                                            <div>
                                                <div class="fw-bold"><?php echo e($criterion['criterion_name']); ?></div>
                                                <?php if (!empty($criterion['description'])): ?>
                                                    <div class="text-muted small mt-1"><?php echo e($criterion['description']); ?></div>
                                                <?php endif; ?>
                                                <?php if (!empty($criterion['kpi_description'])): ?>
                                                    <div class="small mt-2"><span class="fw-semibold">KPI:</span> <?php echo e($criterion['kpi_description']); ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-wrap gap-1 justify-content-end">
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle"><?php echo number_format((float) $criterion['weight'], 2); ?>%</span>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="fas <?php echo e($method['icon']); ?> me-1"></i><?php echo e($method['label']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="content-card criteria-card fadeup fadeup-2">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <h5 class="mb-0"><i class="fas fa-heart me-2 text-primary"></i>Behavior Criteria</h5>
                    <span class="badge bg-light text-muted border"><?php echo number_format(count($behavior_criteria)); ?> item<?php echo count($behavior_criteria) === 1 ? '' : 's'; ?></span>
                </div>
                <div class="card-body">
                    <?php if (empty($behavior_criteria)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-heart fa-2x mb-2 d-block opacity-25"></i>
                            No behavior criteria defined.
                        </div>
                    <?php else: ?>
                        <div class="criteria-list">
                            <?php foreach ($behavior_criteria as $index => $criterion): ?>
                                <?php $method = supervisorViewTemplateMethodLabel($criterion['scoring_method'] ?? 'Scale_1_4'); ?>
                                <div class="criteria-item">
                                    <div class="criteria-item-header">
                                        <div class="d-flex gap-3">
                                            <span class="criteria-number"><?php echo $index + 1; ?></span>
                                            <div>
                                                <div class="fw-bold"><?php echo e($criterion['criterion_name']); ?></div>
                                                <?php if (!empty($criterion['description'])): ?>
                                                    <div class="text-muted small mt-1"><?php echo e($criterion['description']); ?></div>
                                                <?php endif; ?>
                                                <?php if (!empty($criterion['kpi_description'])): ?>
                                                    <div class="small mt-2"><span class="fw-semibold">KPI:</span> <?php echo e($criterion['kpi_description']); ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-wrap gap-1 justify-content-end">
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle"><?php echo number_format((float) $criterion['weight'], 2); ?>%</span>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="fas <?php echo e($method['icon']); ?> me-1"></i><?php echo e($method['label']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
