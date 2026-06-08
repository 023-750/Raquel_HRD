<?php
$page_title = 'Current Evaluation Status';
require_once '../includes/session-check.php';
checkRole(['Employee']);
require_once '../includes/functions.php';

$employee_id = (int)($_SESSION['employee_id'] ?? 0);
ensureEmployeeEvaluationStatusViewSchema($conn);
markEmployeeEvaluationStatusViewed($conn, $employee_id);

$evaluations = $conn->query("
    SELECT e.evaluation_id, e.evaluation_type, e.status, e.evaluation_period_start, e.evaluation_period_end,
           et.template_name, e.total_score, e.performance_level, e.submitted_date, e.updated_at
    FROM evaluations e
    LEFT JOIN evaluation_templates et ON e.template_id = et.template_id
    WHERE e.employee_id = $employee_id
      AND e.status IN (
        'Draft',
        'Pending Self-Rating',
        'Returned',
        'Pending Dept Supervisor',
        'Pending Supervisor',
        'Pending Dept Manager',
        'Supervisor Confirmed',
        'Pending HR Consolidation',
        'Pending Manager',
        'Approved',
        'Rejected'
      )
      AND e.deleted_at IS NULL
    ORDER BY COALESCE(e.submitted_date, e.updated_at, e.created_at) DESC, e.evaluation_id DESC
");
$evaluation_rows = $evaluations ? $evaluations->fetch_all(MYSQLI_ASSOC) : [];

// Determine this employee's workflow path for the card progress strip.
ensureEmployeesReportsTo($conn);
$my_supervisor = getEmployeeSupervisor($conn, $employee_id);
$has_dept_supervisor = ($my_supervisor !== null && !empty($my_supervisor['user_id']));
$my_dept_manager = getDeptManagerOfEmployee($conn, $employee_id);
$has_dept_manager = ($my_dept_manager !== null && !empty($my_dept_manager['user_id']));
$hr_role = getEmployeeHRRole($conn, $employee_id);

$employee_stmt = $conn->prepare("
    SELECT e.job_title, e.rank_category_id
    FROM employees e
    WHERE e.employee_id = ?
    LIMIT 1
");
$employee_stmt->bind_param("i", $employee_id);
$employee_stmt->execute();
$employee_info = $employee_stmt->get_result()->fetch_assoc();
$employee_stmt->close();

$is_supervisor_level_employee = isSupervisorLevelEmployee($employee_info);
$is_main_office_hr_employee = isMainOfficeHumanResourcesEmployee($conn, $employee_id);

if ($hr_role === 'HR Manager') {
    $wf_labels = ['Self-Rating', 'HR Supervisor', 'Approved'];
    $wf_map = [
        'Draft' => 0,
        'Returned' => 0,
        'Pending Self-Rating' => 0,
        'Pending Dept Supervisor' => 1,
        'Pending Supervisor' => 1,
        'Approved' => 2,
        'Rejected' => 2,
    ];
} elseif ($hr_role === 'HR Supervisor') {
    $wf_labels = ['Self-Rating', 'HR Manager', 'Approved'];
    $wf_map = [
        'Draft' => 0,
        'Returned' => 0,
        'Pending Self-Rating' => 0,
        'Pending Manager' => 1,
        'Approved' => 2,
        'Rejected' => 2,
    ];
} elseif ($hr_role === 'HR Staff' || ($hr_role === null && $is_main_office_hr_employee)) {
    $wf_labels = ['Self-Rating', 'HR Supervisor', 'HR Manager', 'Approved'];
    $wf_map = [
        'Draft' => 0,
        'Returned' => 0,
        'Pending Self-Rating' => 0,
        'Pending Dept Supervisor' => 1,
        'Pending Supervisor' => 1,
        'Pending Manager' => 2,
        'Approved' => 3,
        'Rejected' => 3,
    ];
} elseif ($is_supervisor_level_employee && $has_dept_manager) {
    $wf_labels = ['Self-Rating', 'Dept Manager', 'HR Consolidation', 'HR Manager', 'Approved'];
    $wf_map = [
        'Draft' => 0,
        'Returned' => 0,
        'Pending Self-Rating' => 0,
        'Pending Dept Manager' => 1,
        'Supervisor Confirmed' => 2,
        'Pending HR Consolidation' => 2,
        'Pending Manager' => 3,
        'Approved' => 4,
        'Rejected' => 4,
    ];
} elseif ($has_dept_supervisor && $has_dept_manager) {
    $wf_labels = ['Self-Rating', 'Dept Supervisor', 'Dept Manager', 'HR Consolidation', 'HR Manager', 'Approved'];
    $wf_map = [
        'Draft' => 0,
        'Returned' => 0,
        'Pending Self-Rating' => 0,
        'Pending Dept Supervisor' => 1,
        'Pending Supervisor' => 1,
        'Pending Dept Manager' => 2,
        'Supervisor Confirmed' => 3,
        'Pending HR Consolidation' => 3,
        'Pending Manager' => 4,
        'Approved' => 5,
        'Rejected' => 5,
    ];
} elseif ($has_dept_supervisor) {
    $wf_labels = ['Self-Rating', 'Dept Supervisor', 'HR Consolidation', 'HR Manager', 'Approved'];
    $wf_map = [
        'Draft' => 0,
        'Returned' => 0,
        'Pending Self-Rating' => 0,
        'Pending Dept Supervisor' => 1,
        'Pending Supervisor' => 1,
        'Pending Dept Manager' => 1,
        'Supervisor Confirmed' => 2,
        'Pending HR Consolidation' => 2,
        'Pending Manager' => 3,
        'Approved' => 4,
        'Rejected' => 4,
    ];
} else {
    $wf_labels = ['Self-Rating', 'HR Consolidation', 'HR Manager', 'Approved'];
    $wf_map = [
        'Draft' => 0,
        'Returned' => 0,
        'Pending Self-Rating' => 0,
        'Pending Dept Supervisor' => 1,
        'Pending Supervisor' => 1,
        'Pending Dept Manager' => 1,
        'Supervisor Confirmed' => 1,
        'Pending HR Consolidation' => 1,
        'Pending Manager' => 2,
        'Approved' => 3,
        'Rejected' => 3,
    ];
}

function renderWorkflowStrip(string $status, array $labels, array $map): string
{
    $total = count($labels);
    $idx = $map[$status] ?? 0;
    $rejected = ($status === 'Rejected');
    $returned = ($status === 'Returned');
    $active_color = $rejected ? '#dc3545' : ($returned ? '#f59e0b' : '#0d6efd');
    $html = '<div class="eval-progress-strip">';

    foreach ($labels as $i => $label) {
        $done = ($i < $idx);
        $current = ($i === $idx);
        $dot_class = $done ? 'is-done' : ($current ? 'is-current' : '');
        $line_class = $done ? 'is-done' : '';

        $html .= '<div class="eval-progress-step">';
        $html .= '<div class="eval-progress-dot ' . $dot_class . '" style="--active-color:' . $active_color . ';">';
        if ($done) {
            $html .= '<i class="fas fa-check"></i>';
        } elseif ($current && $rejected) {
            $html .= '<i class="fas fa-times"></i>';
        } elseif ($current && $returned) {
            $html .= '<i class="fas fa-rotate-left"></i>';
        } else {
            $html .= '<span>' . ($i + 1) . '</span>';
        }
        $html .= '</div>';
        $html .= '<div class="eval-progress-label" title="' . e($label) . '">' . e($label) . '</div>';
        $html .= '</div>';

        if ($i < $total - 1) {
            $html .= '<div class="eval-progress-line ' . $line_class . '" style="--active-color:' . $active_color . ';"></div>';
        }
    }

    $html .= '</div>';
    return $html;
}

require_once '../includes/header.php';
?>

<style>
.eval-status-page .eval-status-cards {
    display: grid;
    gap: 16px;
    padding: 12px;
}

.eval-status-page .eval-status-card {
    background: #f3f5f0;
    border: 1px solid var(--border-color, #e8ece3);
    border-radius: 10px;
    box-shadow: 0 6px 18px rgba(12, 32, 8, 0.05);
    overflow: hidden;
}

.eval-status-page .eval-status-card-header {
    align-items: flex-start;
    display: flex;
    gap: 12px;
    justify-content: space-between;
    padding: 22px 20px 8px;
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
    font-size: 0.78rem;
    margin-top: 8px;
}

.eval-status-page .eval-status-card-body {
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    padding: 0 20px 14px;
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
    background: transparent;
    padding: 0 20px 20px;
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

.eval-status-page .eval-status-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.eval-status-page .eval-status-tabs .btn {
    border-radius: 999px;
    font-weight: 700;
}

.eval-status-page .eval-score-summary {
    align-items: flex-start;
    display: flex;
    flex-direction: column;
    grid-column: 1 / -1;
    gap: 5px;
}

.eval-status-page .eval-score-value {
    color: #ef4444;
    font-size: 2rem;
    font-weight: 800;
    line-height: 1;
}

.eval-status-page .eval-progress-wrap {
    background: #fff;
    border-top: 1px solid #e3e9dc;
    padding: 14px 0 0;
}

.eval-status-page .eval-progress-strip {
    align-items: flex-start;
    display: flex;
    width: 100%;
}

.eval-status-page .eval-progress-step {
    align-items: center;
    display: flex;
    flex-direction: column;
    flex: 0 0 58px;
    min-width: 58px;
    z-index: 1;
}

.eval-status-page .eval-progress-dot {
    align-items: center;
    background: #f5f9f2;
    border: 2px solid #dbe3d4;
    border-radius: 50%;
    color: #94a3b8;
    display: flex;
    font-size: 0.72rem;
    font-weight: 800;
    height: 28px;
    justify-content: center;
    width: 28px;
}

.eval-status-page .eval-progress-dot.is-done,
.eval-status-page .eval-progress-dot.is-current {
    background: #08220f;
    border-color: #08220f;
    color: #fff;
}

.eval-status-page .eval-progress-dot.is-current {
    box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.12);
}

.eval-status-page .eval-progress-label {
    color: #66746b;
    font-size: 0.66rem;
    font-weight: 700;
    line-height: 1.1;
    margin-top: 6px;
    max-width: 62px;
    overflow: hidden;
    text-align: center;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.eval-status-page .eval-progress-line {
    background: #dbe3e0;
    flex: 1 1 24px;
    height: 2px;
    margin-top: 14px;
    min-width: 18px;
}

.eval-status-page .eval-progress-line.is-done {
    background: var(--active-color);
}

.eval-status-page .eval-progress-bar {
    background: #e4e8e3;
    border-radius: 999px;
    height: 5px;
    margin-top: 10px;
    overflow: hidden;
}

.eval-status-page .eval-progress-bar-fill {
    background: #0d6efd;
    border-radius: inherit;
    display: block;
    height: 100%;
}
</style>

<div class="page-hero fadeup">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-3">
        <div>
            <h4 class="text-white fw-bold mb-0 mt-1"><i class="fas fa-clipboard-check me-2" style="color:var(--primary-light);"></i>Current Evaluation Status</h4>
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
                <div>
                    <h5 class="mb-2"><i class="fas fa-list me-2"></i>Current Evaluation Status</h5>
                </div>
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
                <div class="eval-status-cards">
                    <?php foreach ($evaluation_rows as $eval): ?>
                        <?php
                        $can_edit = in_array($eval['status'], ['Draft', 'Returned', 'Pending Self-Rating'], true);
                        $view_url = BASE_URL . '/employee/self-rating.php' . ($can_edit ? '?edit=' : '?view=') . (int) $eval['evaluation_id'];
                        $period_start = formatDate($eval['evaluation_period_start'] ?? '');
                        $period_end = formatDate($eval['evaluation_period_end'] ?? '');
                        $period_label = ($period_start && $period_end) ? $period_start . ' – ' . $period_end : ($period_start ?: $period_end ?: '—');
                        $status = $eval['status'] ?? '';
                        $step_idx = $wf_map[$status] ?? 0;
                        $last_step_idx = max(count($wf_labels) - 1, 1);
                        $progress_percent = min(100, max(0, ($step_idx / $last_step_idx) * 100));
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
                                <?php
                                $cr_level = $eval['performance_level'] ?? '';
                                $cr_badge = $cr_level ? getPerformanceLevelBadgeClass($cr_level) : 'bg-secondary';
                                ?>
                                <div class="eval-score-summary">
                                    <div class="eval-score-value"><?php echo e($eval['total_score'] ?? '—'); ?></div>
                                    <?php if ($cr_level): ?>
                                        <span class="badge <?php echo $cr_badge; ?>"><?php echo e($cr_level); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="eval-status-field" style="grid-column: 1 / -1;">
                                    <span class="eval-status-field-label">Last Updated</span>
                                    <span class="eval-status-field-value"><?php echo formatDateTime($eval['updated_at'] ?? $eval['submitted_date'] ?? ''); ?></span>
                                </div>
                                <div class="eval-status-field" style="grid-column: 1 / -1;">
                                    <span class="eval-status-field-label">Progression of Form</span>
                                    <div class="eval-progress-wrap">
                                        <?php echo renderWorkflowStrip($status, $wf_labels, $wf_map); ?>
                                        <div class="eval-progress-bar" aria-hidden="true">
                                            <span class="eval-progress-bar-fill" style="width: <?php echo number_format($progress_percent, 2); ?>%;"></span>
                                        </div>
                                    </div>
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
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
