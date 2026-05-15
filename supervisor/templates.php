<?php
$page_title = 'Template Viewing';
require_once '../includes/session-check.php';
checkRole(['HR Supervisor']);
require_once '../includes/functions.php';

$branch_id = (int) ($_SESSION['branch_id'] ?? 0);

function supervisorTemplateRows(mysqli $conn, string $sql, string $types = '', array $params = []): array
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

function supervisorTemplateQueryString(array $params): string
{
    $clean = [];
    foreach ($params as $key => $value) {
        if ($value !== '' && $value !== null) {
            $clean[$key] = $value;
        }
    }
    return http_build_query($clean);
}

function supervisorTemplateWeightClass(float $weight): string
{
    return abs($weight - 100) < 0.01
        ? 'bg-success-subtle text-success border-success-subtle'
        : 'bg-warning-subtle text-warning border-warning-subtle';
}

$allowed_eval_types = ['Initial', 'Final', 'Quarterly', 'Annual'];
$filter_search = trim($_GET['q'] ?? '');
if (strlen($filter_search) > 80) {
    $filter_search = substr($filter_search, 0, 80);
}
$filter_type = in_array($_GET['evaluation_type'] ?? '', $allowed_eval_types, true) ? $_GET['evaluation_type'] : '';
$filter_target = trim($_GET['target_position'] ?? '');
if (strlen($filter_target) > 100) {
    $filter_target = substr($filter_target, 0, 100);
}

$target_options = supervisorTemplateRows(
    $conn,
    "SELECT DISTINCT COALESCE(NULLIF(target_position, ''), 'General') AS target_position
     FROM evaluation_templates
     WHERE status = 'Active' AND deleted_at IS NULL
     ORDER BY target_position"
);

$where = "WHERE et.status = 'Active' AND et.deleted_at IS NULL";
$types = "i";
$params = [$branch_id];

if ($filter_search !== '') {
    $like = '%' . $filter_search . '%';
    $where .= " AND (et.template_name LIKE ? OR et.description LIKE ? OR et.target_position LIKE ? OR et.form_code LIKE ?)";
    $types .= "ssss";
    array_push($params, $like, $like, $like, $like);
}

if ($filter_type !== '') {
    $where .= " AND et.evaluation_type = ?";
    $types .= "s";
    $params[] = $filter_type;
}

if ($filter_target !== '') {
    if ($filter_target === 'General') {
        $where .= " AND (et.target_position IS NULL OR et.target_position = '')";
    } else {
        $where .= " AND et.target_position = ?";
        $types .= "s";
        $params[] = $filter_target;
    }
}

$templates = supervisorTemplateRows(
    $conn,
    "SELECT et.*, u.full_name AS created_by_name,
            (SELECT COUNT(*) FROM evaluation_criteria ec WHERE ec.template_id = et.template_id) AS criteria_count,
            (SELECT COUNT(*) FROM evaluation_criteria ec WHERE ec.template_id = et.template_id AND ec.section = 'KRA') AS kra_count,
            (SELECT COUNT(*) FROM evaluation_criteria ec WHERE ec.template_id = et.template_id AND ec.section = 'Behavior') AS behavior_count,
            (SELECT COALESCE(SUM(ec.weight), 0) FROM evaluation_criteria ec WHERE ec.template_id = et.template_id AND ec.section = 'KRA') AS kra_total_weight,
            (SELECT COUNT(*)
             FROM evaluations ev
             INNER JOIN employees e ON ev.employee_id = e.employee_id
             WHERE ev.template_id = et.template_id
               AND ev.deleted_at IS NULL
               AND e.branch_id = ?) AS branch_usage_count
     FROM evaluation_templates et
     LEFT JOIN users u ON et.created_by = u.user_id
     $where
     ORDER BY et.template_name ASC",
    $types,
    $params
);

$total_active = (int) supervisorTemplateRows(
    $conn,
    "SELECT COUNT(*) AS total FROM evaluation_templates WHERE status = 'Active' AND deleted_at IS NULL"
)[0]['total'];

$total_criteria = (int) supervisorTemplateRows(
    $conn,
    "SELECT COUNT(*) AS total
     FROM evaluation_criteria ec
     INNER JOIN evaluation_templates et ON ec.template_id = et.template_id
     WHERE et.status = 'Active' AND et.deleted_at IS NULL"
)[0]['total'];

$branch_used_templates = (int) supervisorTemplateRows(
    $conn,
    "SELECT COUNT(DISTINCT ev.template_id) AS total
     FROM evaluations ev
     INNER JOIN employees e ON ev.employee_id = e.employee_id
     INNER JOIN evaluation_templates et ON ev.template_id = et.template_id
     WHERE e.branch_id = ?
       AND ev.deleted_at IS NULL
       AND et.status = 'Active'
       AND et.deleted_at IS NULL",
    "i",
    [$branch_id]
)[0]['total'];

$filter_params = [
    'q' => $filter_search,
    'evaluation_type' => $filter_type,
    'target_position' => $filter_target,
];
$active_filter_count = 0;
foreach ($filter_params as $value) {
    if ($value !== '' && $value !== null) {
        $active_filter_count++;
    }
}

require_once '../includes/header.php';
?>

<style>
    .supervisor-template-page .template-filter-card {
        background: #fff;
        border: 1px solid #eef2e8;
        border-radius: 14px;
        box-shadow: 0 8px 22px rgba(12, 32, 8, 0.06);
        margin-bottom: 18px;
        padding: 16px;
    }

    .supervisor-template-page .template-filter-card .form-label {
        color: var(--text-muted);
        font-size: 0.72rem;
        font-weight: 800;
        margin-bottom: 6px;
        text-transform: uppercase;
    }

    .supervisor-template-page .template-card {
        border: 1px solid #eef2e8;
        border-radius: 14px;
        box-shadow: 0 8px 22px rgba(12, 32, 8, 0.05);
        height: 100%;
        overflow: hidden;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .supervisor-template-page .template-card:hover {
        box-shadow: 0 12px 28px rgba(12, 32, 8, 0.09);
        transform: translateY(-3px);
    }

    .supervisor-template-page .template-icon {
        align-items: center;
        background: rgba(41, 67, 6, 0.08);
        border-radius: 12px;
        color: var(--primary-blue);
        display: inline-flex;
        height: 44px;
        justify-content: center;
        width: 44px;
    }

    .supervisor-template-page .template-description {
        color: var(--text-muted);
        font-size: 0.84rem;
        line-height: 1.5;
        min-height: 48px;
    }

    .supervisor-template-page .template-metric {
        background: #f8faf7;
        border: 1px solid #eef2e8;
        border-radius: 10px;
        padding: 10px;
    }

    .supervisor-template-page .template-metric span {
        color: var(--text-muted);
        display: block;
        font-size: 0.66rem;
        font-weight: 800;
        margin-bottom: 2px;
        text-transform: uppercase;
    }

    .supervisor-template-page .template-metric strong {
        color: #1f2f12;
        font-size: 0.92rem;
    }

    .supervisor-template-page .weight-bar {
        display: flex;
        gap: 4px;
        height: 7px;
        overflow: hidden;
    }

    .supervisor-template-page .weight-bar span {
        border-radius: 999px;
        display: block;
    }

    .supervisor-template-page .weight-bar .kra {
        background: #294306;
    }

    .supervisor-template-page .weight-bar .behavior {
        background: #BD9414;
    }

    .supervisor-template-page .bg-primary-subtle { background-color: #e7f1ff; }
    .supervisor-template-page .bg-info-subtle { background-color: #e0f7fa; }
    .supervisor-template-page .bg-success-subtle { background-color: #e8f5e9; }
    .supervisor-template-page .bg-warning-subtle { background-color: #fff9c4; }
    .supervisor-template-page .bg-secondary-subtle { background-color: #f5f5f5; }

    @media (max-width: 768px) {
        .supervisor-template-page .template-filter-card .btn,
        .supervisor-template-page .template-filter-card a.btn {
            width: 100%;
        }

        .supervisor-template-page .template-description {
            min-height: 0;
        }
    }
</style>

<div class="supervisor-template-page">
    <div class="page-hero fadeup">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-3">
            <div>
                <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:0;color:rgba(255,255,255,.55);">HR Supervisor · Template Library</div>
                <h4 class="text-white fw-bold mb-0 mt-1"><i class="fas fa-file-alt me-2" style="color:#BD9414;"></i>Template Viewing</h4>
            </div>
            <div class="badge bg-white text-dark border-0 py-2 px-3" style="border-radius:20px;font-size:.75rem;box-shadow:0 4px 10px rgba(0,0,0,.1);">
                <i class="fas fa-lock me-1 text-primary"></i>Read-only access
            </div>
        </div>
        <p class="text-white-50 small mb-0"><i class="fas fa-eye me-1"></i>Browse active evaluation templates, criteria, scoring methods, and target positions.</p>

        <div class="row g-3 mt-4">
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-value"><?php echo number_format($total_active); ?></div>
                            <div class="stat-label">Active Templates</div>
                        </div>
                        <i class="fas fa-layer-group stat-icon text-white-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-value"><?php echo number_format(count($templates)); ?></div>
                            <div class="stat-label">Filtered View</div>
                        </div>
                        <i class="fas fa-filter stat-icon" style="color:#17a2b8;"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-value"><?php echo number_format($total_criteria); ?></div>
                            <div class="stat-label">Criteria</div>
                        </div>
                        <i class="fas fa-list-check stat-icon" style="color:#28a745;"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-value"><?php echo number_format($branch_used_templates); ?></div>
                            <div class="stat-label">Used by Branch</div>
                        </div>
                        <i class="fas fa-clipboard-list stat-icon" style="color:#BD9414;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="template-filter-card fadeup fadeup-1">
        <form method="GET" action="" class="row g-3 align-items-end">
            <div class="col-lg-5 col-md-6">
                <label class="form-label">Search</label>
                <input type="search" class="form-control form-control-sm" name="q" value="<?php echo e($filter_search); ?>" placeholder="Template name, form code, description, target">
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label">Evaluation Type</label>
                <select class="form-select form-select-sm" name="evaluation_type">
                    <option value="">All Types</option>
                    <?php foreach ($allowed_eval_types as $type): ?>
                        <option value="<?php echo e($type); ?>" <?php echo $filter_type === $type ? 'selected' : ''; ?>><?php echo e($type); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label">Target Position</label>
                <select class="form-select form-select-sm" name="target_position">
                    <option value="">All Targets</option>
                    <?php foreach ($target_options as $target): ?>
                        <option value="<?php echo e($target['target_position']); ?>" <?php echo $filter_target === $target['target_position'] ? 'selected' : ''; ?>>
                            <?php echo e($target['target_position']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-2 d-flex flex-wrap gap-2 justify-content-lg-end">
                <button type="submit" class="btn btn-primary btn-sm px-3"><i class="fas fa-filter me-1"></i>Apply</button>
                <a href="templates.php" class="btn btn-outline-secondary btn-sm px-3"><i class="fas fa-rotate-left me-1"></i>Reset</a>
            </div>
            <div class="col-12">
                <div class="small fw-semibold text-muted">
                    <?php echo $active_filter_count > 0 ? number_format($active_filter_count) . ' filters active' : 'No filters active'; ?>
                    &middot; <?php echo number_format(count($templates)); ?> template<?php echo count($templates) === 1 ? '' : 's'; ?> shown
                </div>
            </div>
        </form>
    </div>

    <?php if (empty($templates)): ?>
        <div class="content-card fadeup fadeup-2">
            <div class="card-body text-center py-5">
                <i class="fas fa-file-alt fa-3x mb-3 text-muted opacity-25"></i>
                <h5 class="text-muted">No Templates Found</h5>
                <p class="text-muted small mb-0">Adjust the filters to view active templates.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4 fadeup fadeup-2">
            <?php foreach ($templates as $template): ?>
                <?php
                $kra_weight = (float) ($template['kra_weight'] ?? 80);
                $behavior_weight = (float) ($template['behavior_weight'] ?? 20);
                $kra_total_weight = (float) ($template['kra_total_weight'] ?? 0);
                $target_position = $template['target_position'] ?: 'General';
                $description = trim((string) ($template['description'] ?? ''));
                ?>
                <div class="col-md-6 col-xl-4">
                    <div class="content-card template-card">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                <span class="template-icon"><i class="fas fa-file-alt"></i></span>
                                <div class="d-flex flex-wrap gap-1 justify-content-end">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><?php echo e($template['evaluation_type'] ?? 'Annual'); ?></span>
                                    <span class="badge bg-info-subtle text-info border border-info-subtle"><?php echo e($target_position); ?></span>
                                </div>
                            </div>

                            <h6 class="fw-bold mb-2"><?php echo e($template['template_name']); ?></h6>
                            <p class="template-description mb-3"><?php echo e($description !== '' ? substr($description, 0, 130) . (strlen($description) > 130 ? '...' : '') : 'No description provided.'); ?></p>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <div class="template-metric">
                                        <span>KRA Criteria</span>
                                        <strong><?php echo number_format((int) $template['kra_count']); ?></strong>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="template-metric">
                                        <span>Behavior</span>
                                        <strong><?php echo number_format((int) $template['behavior_count']); ?></strong>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="template-metric">
                                        <span>KRA Weight</span>
                                        <strong><?php echo number_format($kra_total_weight, 2); ?>%</strong>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="template-metric">
                                        <span>Branch Use</span>
                                        <strong><?php echo number_format((int) $template['branch_usage_count']); ?></strong>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2 small text-muted">
                                <span>KRA <?php echo number_format($kra_weight, 0); ?>%</span>
                                <span>Behavior <?php echo number_format($behavior_weight, 0); ?>%</span>
                            </div>
                            <div class="weight-bar mb-3">
                                <span class="kra" style="flex: <?php echo max(1, $kra_weight); ?>;"></span>
                                <span class="behavior" style="flex: <?php echo max(1, $behavior_weight); ?>;"></span>
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge <?php echo supervisorTemplateWeightClass($kra_total_weight); ?> border px-2">
                                    <i class="fas fa-balance-scale me-1"></i>KRA total <?php echo number_format($kra_total_weight, 2); ?>%
                                </span>
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2">
                                    <i class="fas fa-list-ul me-1"></i><?php echo number_format((int) $template['criteria_count']); ?> criteria
                                </span>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 p-4 pt-0">
                            <a href="<?php echo BASE_URL; ?>/supervisor/view-template.php?id=<?php echo (int) $template['template_id']; ?>" class="btn btn-outline-primary w-100">
                                <i class="fas fa-eye me-2"></i>View Criteria
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
