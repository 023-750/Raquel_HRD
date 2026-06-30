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

// ── Per-status counts for filter badges ─────────────────────────────────
$esf_counts = ['all'=>0,'approved'=>0,'pending'=>0,'rejected'=>0,'returned'=>0,'draft'=>0];
foreach ($evaluation_rows as $r) {
    $esf_counts['all']++;
    $s = $r['status'] ?? '';
    if ($s === 'Approved')             $esf_counts['approved']++;
    elseif ($s === 'Rejected')         $esf_counts['rejected']++;
    elseif ($s === 'Returned')         $esf_counts['returned']++;
    elseif ($s === 'Draft')            $esf_counts['draft']++;
    elseif (str_starts_with($s,'Pending') || $s === 'Supervisor Confirmed') $esf_counts['pending']++;
}

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
            <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.55);">Employee Portal · Evaluation Status</div>
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
            <div class="card-header d-block">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Current Evaluation Status</h5>
                    <?php if (!empty($evaluation_rows)): ?>
                        <span class="badge bg-light text-muted border"><?php echo count($evaluation_rows); ?> total</span>
                    <?php endif; ?>
                </div>

                <?php if (!empty($evaluation_rows)): ?>
                <!-- ── Status Filter Tabs (matches sample_design.html) ── -->
                <div class="status-filter-bar">
                    <div class="status-filter-label">
                        <i class="fas fa-filter" style="margin-right:5px;"></i>
                        Current Evaluation Status
                    </div>
                    <div class="status-filter-tabs">
                        <button class="sf-tab active" data-filter="all" onclick="filterStatus('all',this)">
                            <i class="fas fa-border-all"></i> All
                            <span class="sf-count"><?php echo $esf_counts['all']; ?></span>
                        </button>
                        <button class="sf-tab" data-filter="complete" onclick="filterStatus('complete',this)">
                            <i class="fas fa-circle-check"></i> Complete
                            <span class="sf-count"><?php echo $esf_counts['approved']; ?></span>
                        </button>
                        <?php if ($esf_counts['pending'] > 0): ?>
                        <button class="sf-tab" data-filter="pending" onclick="filterStatus('pending',this)">
                            <i class="fas fa-hourglass-half"></i> Pending
                            <span class="sf-count"><?php echo $esf_counts['pending']; ?></span>
                        </button>
                        <?php endif; ?>
                        <button class="sf-tab" data-filter="reject" onclick="filterStatus('reject',this)">
                            <i class="fas fa-circle-xmark"></i> Reject
                            <span class="sf-count"><?php echo $esf_counts['rejected']; ?></span>
                        </button>
                        <button class="sf-tab" data-filter="return" onclick="filterStatus('return',this)">
                            <i class="fas fa-rotate-left"></i> Return
                            <span class="sf-count"><?php echo $esf_counts['returned']; ?></span>
                        </button>
                        <button class="sf-tab" data-filter="draft" onclick="filterStatus('draft',this)">
                            <i class="fas fa-file-pen"></i> Draft
                            <span class="sf-count"><?php echo $esf_counts['draft']; ?></span>
                        </button>
                    </div>
                </div>
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
                <!-- Evaluation list (matches sample_design.html .evaluation-card layout) -->
                <div id="evaluation-list" class="p-3">
                    <?php foreach ($evaluation_rows as $eval): ?>
                        <?php
                        $can_edit = in_array($eval['status'], ['Draft', 'Returned', 'Pending Self-Rating'], true);
                        $view_url = BASE_URL . '/employee/self-rating.php' . ($can_edit ? '?edit=' : '?view=') . (int) $eval['evaluation_id'];
                        $period_start = formatDate($eval['evaluation_period_start'] ?? '');
                        $period_end   = formatDate($eval['evaluation_period_end'] ?? '');
                        $period_label = ($period_start && $period_end) ? $period_start . ' – ' . $period_end : ($period_start ?: $period_end ?: '');
                        $submitted    = !empty($eval['submitted_date']) ? 'Submitted on ' . formatDate($eval['submitted_date']) : ($period_label ?: 'Saved');
                        $status       = $eval['status'] ?? '';

                        // Map to filter key (data-status on card)
                        if ($status === 'Approved')                                              $ds = 'complete';
                        elseif ($status === 'Rejected')                                          $ds = 'reject';
                        elseif ($status === 'Returned')                                          $ds = 'return';
                        elseif ($status === 'Draft')                                             $ds = 'draft';
                        elseif (str_starts_with($status, 'Pending') || $status === 'Supervisor Confirmed') $ds = 'pending';
                        else                                                                     $ds = 'other';

                        // Status pill class
                        $pill = match(true) {
                            $status === 'Approved'  => 'status completed',
                            $status === 'Rejected'  => 'status rejected',
                            $status === 'Returned'  => 'status returned',
                            $status === 'Draft'     => 'status draft',
                            default                 => 'status pending-pill',
                        };

                        $cr_level = $eval['performance_level'] ?? '';
                        $cr_badge = $cr_level ? getPerformanceLevelBadgeClass($cr_level) : '';
                        ?>
                        <div class="evaluation-card" data-status="<?php echo $ds; ?>">
                            <div>
                                <h3><?php echo e($eval['template_name'] ?? 'Evaluation'); ?></h3>
                                <p>
                                    <?php if (!empty($eval['evaluation_type'])): ?>
                                    <i class="fas fa-tag me-1"></i><?php echo e($eval['evaluation_type']); ?> &nbsp;&middot;&nbsp;
                                    <?php endif; ?>
                                    <?php echo $submitted; ?>
                                    <?php if (!empty($eval['total_score'])): ?>
                                    &nbsp;&middot;&nbsp; <strong><?php echo number_format((float)$eval['total_score'], 2); ?></strong>
                                    <?php if ($cr_level): ?><span class="badge <?php echo $cr_badge; ?> ms-1" style="font-size:.65rem;"><?php echo e($cr_level); ?></span><?php endif; ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="d-flex flex-column align-items-end gap-2">
                                <span class="<?php echo $pill; ?>"><?php echo e($status); ?></span>
                                <a href="<?php echo $view_url; ?>" class="btn btn-<?php echo $can_edit ? 'primary' : 'outline-secondary'; ?> btn-sm rounded-pill" style="font-size:.72rem;padding:3px 12px;">
                                    <i class="fas fa-<?php echo $can_edit ? 'edit' : 'eye'; ?> me-1"></i><?php echo $can_edit ? 'Continue' : 'View'; ?>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="no-results" id="no-results">
                    <i class="fas fa-inbox"></i>
                    No evaluations found for this status.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* ── Evaluation card — matches sample_design.html ──────────────────── */
#evaluation-list {
    display: grid;
    gap: 12px;
}
.evaluation-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 18px 20px;
    border: 1px solid #e5e5e5;
    border-radius: 10px;
    background: #fff;
    transition: box-shadow .2s;
    animation: evalFadeIn .25s ease;
    gap: 12px;
}
.evaluation-card:hover {
    box-shadow: 0 3px 10px rgba(0,0,0,.08);
}
@keyframes evalFadeIn {
    from { opacity:0; transform:translateY(4px); }
    to   { opacity:1; transform:translateY(0); }
}
.evaluation-card h3 {
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 5px;
    color: var(--text-dark, #111);
}
.evaluation-card p {
    color: #777;
    font-size: 13px;
    margin: 0;
}

/* Status pills — matches sample_design.html */
.status {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    white-space: nowrap;
    flex-shrink: 0;
}
.status.completed   { background: #e8f8ee; color: #218c4c; }
.status.draft       { background: #fff4dd; color: #c58300; }
.status.rejected    { background: #fdeaea; color: #d63939; }
.status.returned    { background: #f1ecff; color: #7c3aed; }
.status.pending-pill { background: #e8f0ff; color: #1f6dff; }

/* No-results block */
.no-results {
    text-align: center;
    padding: 40px 20px;
    color: #aaa;
    font-size: 14px;
    display: none;
}
.no-results i {
    font-size: 36px;
    margin-bottom: 10px;
    display: block;
}

/* ── Filter bar — matches sample_design.html ────────────────────── */
.status-filter-bar {
    margin-bottom: 0;
    padding-bottom: 4px;
}
.status-filter-label {
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .06em;
    color: #888;
    text-transform: uppercase;
    margin-bottom: 10px;
}
.status-filter-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.sf-tab {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 16px;
    border-radius: 20px;
    border: 1.5px solid #e0e0e0;
    background: #fafafa;
    font-size: 13px;
    font-weight: 600;
    color: #555;
    cursor: pointer;
    transition: all .2s;
    user-select: none;
    line-height: 1.3;
}
.sf-tab:hover { border-color: #aaa; background: #f0f0f0; }
.sf-tab.active[data-filter="all"]      { background: #1f6dff; border-color: #1f6dff; color: #fff; }
.sf-tab.active[data-filter="complete"] { background: #218c4c; border-color: #218c4c; color: #fff; }
.sf-tab.active[data-filter="pending"]  { background: #d97706; border-color: #d97706; color: #fff; }
.sf-tab.active[data-filter="reject"]   { background: #d63939; border-color: #d63939; color: #fff; }
.sf-tab.active[data-filter="return"]   { background: #7c3aed; border-color: #7c3aed; color: #fff; }
.sf-tab.active[data-filter="draft"]    { background: #c58300; border-color: #c58300; color: #fff; }
.sf-count {
    font-size: 11px;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 10px;
    background: rgba(0,0,0,.08);
}
.sf-tab.active .sf-count { background: rgba(255,255,255,.25); }
</style>

<script>
function filterStatus(status, button) {
    document.querySelectorAll('.sf-tab').forEach(t => t.classList.remove('active'));
    button.classList.add('active');

    const cards = document.querySelectorAll('#evaluation-list .evaluation-card');
    let visible = 0;
    cards.forEach(card => {
        const match = status === 'all' || card.dataset.status === status;
        card.style.display = match ? 'flex' : 'none';
        if (match) visible++;
    });

    document.getElementById('no-results').style.display = visible === 0 ? 'block' : 'none';
}
</script>

<?php require_once '../includes/footer.php'; ?>
