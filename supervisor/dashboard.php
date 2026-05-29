<?php
$page_title = 'Supervisor Dashboard';
require_once '../includes/session-check.php';
checkRole(['HR Supervisor']);
require_once '../includes/header.php';

// Fetch stats
$pending_validations = $conn->query("SELECT COUNT(*) as c FROM evaluations WHERE status = 'Pending HR Consolidation'")->fetch_assoc()['c'];

$validated_month = $conn->query("SELECT COUNT(*) as c FROM evaluations WHERE endorsed_by = {$_SESSION['user_id']} AND MONTH(endorsed_date) = MONTH(CURRENT_DATE()) AND YEAR(endorsed_date) = YEAR(CURRENT_DATE())")->fetch_assoc()['c'];

$total_employees = $conn->query("SELECT COUNT(*) as c FROM employees WHERE is_active = 1 AND employee_id NOT IN (SELECT employee_id FROM users WHERE role = 'Admin' AND employee_id IS NOT NULL)")->fetch_assoc()['c'];

$pending_rows = [];
$pending_result = $conn->query("SELECT ev.*, CONCAT(e.first_name, ' ', e.last_name) as employee_name,
    e.job_title, e.profile_picture, et.template_name,
    u.full_name as submitted_by_name
    FROM evaluations ev
    LEFT JOIN employees e ON ev.employee_id = e.employee_id
    LEFT JOIN users u ON ev.submitted_by = u.user_id
    LEFT JOIN evaluation_templates et ON ev.template_id = et.template_id
    WHERE ev.status = 'Pending HR Consolidation'
    ORDER BY ev.submitted_date DESC");
while ($row = $pending_result->fetch_assoc()) {
    $pending_rows[] = $row;
}

$pending_groups = [];
foreach ($pending_rows as $row) {
    $employeeId = (int) ($row['employee_id'] ?? 0);
    if ($employeeId <= 0) {
        continue;
    }
    if (!isset($pending_groups[$employeeId])) {
        $pending_groups[$employeeId] = [
            'employee_id' => $employeeId,
            'employee_name' => $row['employee_name'] ?? '',
            'profile_picture' => $row['profile_picture'] ?? '',
            'submitted_by_name' => $row['submitted_by_name'] ?? '',
            'evaluations' => [],
        ];
    }
    $pending_groups[$employeeId]['evaluations'][] = $row;
}
$pending_groups = array_values($pending_groups);
$queue_preview_groups = array_slice($pending_groups, 0, 5);
$queue_employee_count = count($pending_groups);
?>

<style>
    .supervisor-dashboard .approval-list {
        padding: 15px;
    }

    .supervisor-dashboard .approval-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 15px;
        background: #fff;
        border: 1px solid #f0f0f0;
        border-radius: 12px;
        margin-bottom: 12px;
        transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .supervisor-dashboard .approval-item:hover {
        transform: translateX(5px);
        border-color: var(--primary-light);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .supervisor-dashboard .approval-item .emp-info {
        display: flex;
        align-items: center;
        gap: 12px;
        flex: 1;
        min-width: 0;
    }

    .supervisor-dashboard .approval-item .avatar-circle {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: rgba(41, 67, 6, 0.05);
        color: var(--primary-blue);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        flex-shrink: 0;
    }

    .supervisor-dashboard .approval-item .details {
        min-width: 0;
    }

    .supervisor-dashboard .approval-item .details h6 {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 700;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .supervisor-dashboard .approval-item .details span {
        color: var(--text-muted);
        display: block;
        font-size: 0.75rem;
    }

    .supervisor-dashboard .approval-item .score-meter {
        width: 140px;
        flex-shrink: 0;
    }

    .supervisor-dashboard .approval-item .score-val {
        display: block;
        font-size: 0.85rem;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .supervisor-dashboard .approval-item .status-meta {
        color: var(--text-muted);
        flex-shrink: 0;
        font-size: 0.75rem;
        text-align: right;
    }

    .supervisor-dashboard .approval-item .btn-review {
        border-radius: 20px;
        flex-shrink: 0;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 6px 18px;
    }

    .supervisor-dashboard .approval-group-wrap {
        background: #fff;
        border: 1px solid #f0f0f0;
        border-radius: 12px;
        margin-bottom: 12px;
        overflow: hidden;
    }

    .supervisor-dashboard .approval-group-header {
        align-items: center;
        cursor: pointer;
        display: flex;
        gap: 16px;
        justify-content: space-between;
        padding: 15px;
        transition: background 0.2s ease;
    }

    .supervisor-dashboard .approval-group-header:hover {
        background: #fbfcf8;
    }

    .supervisor-dashboard .approval-group-header[aria-expanded="true"] .group-chevron {
        transform: rotate(180deg);
    }

    .supervisor-dashboard .group-chevron {
        transition: transform 0.2s ease;
    }

    .supervisor-dashboard .approval-group-entries {
        background: #f8faf5;
        border-top: 1px solid #eef2e8;
        padding: 0 15px 12px;
    }

    .supervisor-dashboard .approval-group-entry {
        align-items: center;
        background: #fff;
        border: 1px solid #eef2e8;
        border-radius: 10px;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        justify-content: space-between;
        margin-top: 10px;
        padding: 12px;
    }

    .supervisor-dashboard .approval-group-entry .entry-template {
        font-size: 0.82rem;
        font-weight: 700;
        min-width: 0;
    }

    .supervisor-dashboard .empty-state-card {
        color: var(--text-muted);
        padding: 42px 20px;
        text-align: center;
    }

    .supervisor-dashboard .empty-state-card i {
        display: block;
        font-size: 2.6rem;
        margin-bottom: 14px;
        opacity: 0.2;
    }

    .supervisor-dashboard .workflow-card {
        min-height: 100%;
    }

    .supervisor-dashboard .workflow-step {
        display: grid;
        grid-template-columns: 38px minmax(0, 1fr);
        gap: 12px;
        padding: 12px 0;
    }

    .supervisor-dashboard .workflow-step + .workflow-step {
        border-top: 1px solid #f0f4eb;
    }

    .supervisor-dashboard .workflow-step .step-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(41, 67, 6, 0.08);
        color: var(--primary-blue);
    }

    @media (max-width: 768px) {
        .supervisor-dashboard .approval-item,
        .supervisor-dashboard .approval-group-header {
            align-items: stretch;
            flex-direction: column;
        }

        .supervisor-dashboard .approval-item .status-meta,
        .supervisor-dashboard .approval-group-header .status-meta {
            text-align: left;
        }

        .supervisor-dashboard .approval-item .btn-review,
        .supervisor-dashboard .approval-group-header .btn-review {
            width: 100%;
            text-align: center;
        }

        .supervisor-dashboard .approval-group-entry {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>

<div class="supervisor-dashboard">
<div class="page-hero fadeup">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
        <div>
            <div class="mb-1" style="color:#FFD97D;font-size:.88rem;font-weight:600;letter-spacing:.3px;"><?php echo getGreeting($_SESSION['full_name'] ?? ''); ?></div>
            <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.55);">HR Supervisor · Dashboard</div>
            <h4 class="text-white fw-bold mb-0 mt-1"><i class="fas fa-check-double me-2" style="color:#BD9414;"></i>Supervisor Overview</h4>
        </div>
        <div style="color:rgba(255,255,255,.6);font-size:.8rem;">
            <i class="fas fa-sync-alt me-1"></i>Data as of <?php echo date('F d, Y'); ?>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?php echo $pending_validations; ?></div>
                        <div class="stat-label">Pending Validations</div>
                    </div>
                    <i class="fas fa-clipboard-check stat-icon" style="color:#ffc107;"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?php echo $validated_month; ?></div>
                        <div class="stat-label">Validated This Month</div>
                    </div>
                    <i class="fas fa-check-circle stat-icon" style="color:#28a745;"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?php echo $total_employees; ?></div>
                        <div class="stat-label">Total Employees</div>
                    </div>
                    <i class="fas fa-users stat-icon text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="chart-card h-100">
            <div class="cc-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Validation Queue</h5>
                <div class="d-flex align-items-center gap-2">
                    <?php if ($queue_employee_count > 0): ?>
                        <span class="badge bg-light text-muted border"><?php echo number_format($queue_employee_count); ?> employee<?php echo $queue_employee_count === 1 ? '' : 's'; ?></span>
                    <?php endif; ?>
                    <a href="<?php echo BASE_URL; ?>/supervisor/pending-endorsements.php" class="btn btn-sm btn-outline-primary rounded-pill px-3">View All</a>
                </div>
            </div>
            <div class="cc-body p-0">
                <div class="approval-list">
                    <?php if (empty($queue_preview_groups)): ?>
                        <div class="empty-state-card">
                            <i class="fas fa-clipboard-check"></i>
                            <p class="mb-0">All validations have been processed.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($queue_preview_groups as $group):
                            $evaluations = $group['evaluations'];
                            $evalCount = count($evaluations);
                            $name_parts = preg_split('/\s+/', trim($group['employee_name'] ?? ''));
                            $initials = strtoupper(substr($name_parts[0] ?? 'U', 0, 1) . substr($name_parts[1] ?? '', 0, 1));
                            $groupCollapseId = 'dashQueue' . (int) $group['employee_id'];
                        ?>
                            <?php if ($evalCount === 1):
                                $row = $evaluations[0];
                                $score = (float) ($row['total_score'] ?? 0);
                            ?>
                                <div class="approval-item">
                                    <div class="emp-info">
                                        <div class="avatar-circle"><?php echo e($initials ?: 'U'); ?></div>
                                        <div class="details">
                                            <h6><?php echo e($group['employee_name']); ?></h6>
                                            <span><?php echo e($row['template_name'] ?? 'Evaluation'); ?> · Submitted by <?php echo e($row['submitted_by_name']); ?></span>
                                        </div>
                                    </div>
                                    <div class="score-meter d-none d-md-block">
                                        <span class="score-val"><?php echo $row['total_score']; ?>% Score</span>
                                        <div class="progress" style="height: 4px;">
                                            <div class="progress-bar <?php echo ($score >= 80) ? 'bg-success' : (($score >= 60) ? 'bg-primary' : 'bg-warning'); ?>" style="width: <?php echo min(100, max(0, $score)); ?>%;"></div>
                                        </div>
                                    </div>
                                    <div class="status-meta d-none d-sm-block">
                                        <div class="fw-bold text-dark"><?php echo formatDate($row['submitted_date']); ?></div>
                                        <div class="x-small">Pending Validation</div>
                                    </div>
                                    <a href="<?php echo BASE_URL; ?>/supervisor/pending-endorsements.php" class="btn btn-primary btn-review">Review</a>
                                </div>
                            <?php else: ?>
                                <div class="approval-group-wrap">
                                    <div class="approval-group-header"
                                         role="button"
                                         tabindex="0"
                                         data-bs-toggle="collapse"
                                         data-bs-target="#<?php echo e($groupCollapseId); ?>"
                                         aria-expanded="false"
                                         aria-controls="<?php echo e($groupCollapseId); ?>">
                                        <div class="emp-info">
                                            <div class="avatar-circle"><?php echo e($initials ?: 'U'); ?></div>
                                            <div class="details">
                                                <h6><?php echo e($group['employee_name']); ?></h6>
                                                <span><span class="badge bg-primary-subtle text-primary border border-primary-subtle"><?php echo $evalCount; ?> evaluations pending</span></span>
                                            </div>
                                        </div>
                                        <div class="status-meta d-none d-sm-block">
                                            <div class="fw-bold text-dark">Pending Validation</div>
                                            <div class="x-small">Click to expand</div>
                                        </div>
                                        <button type="button" class="btn btn-outline-primary btn-review" data-bs-toggle="collapse" data-bs-target="#<?php echo e($groupCollapseId); ?>" aria-expanded="false" onclick="event.stopPropagation();">
                                            <i class="fas fa-chevron-down group-chevron me-1"></i>Show
                                        </button>
                                    </div>
                                    <div class="collapse approval-group-entries" id="<?php echo e($groupCollapseId); ?>">
                                        <?php foreach ($evaluations as $row):
                                            $score = (float) ($row['total_score'] ?? 0);
                                        ?>
                                            <div class="approval-group-entry">
                                                <div class="flex-grow-1 min-w-0">
                                                    <div class="entry-template"><?php echo e($row['template_name'] ?? 'Evaluation'); ?></div>
                                                    <div class="small text-muted">
                                                        <?php echo e($row['evaluation_type'] ?? 'Annual'); ?> ·
                                                        Submitted <?php echo formatDate($row['submitted_date']); ?> ·
                                                        <?php echo e($row['submitted_by_name']); ?>
                                                    </div>
                                                </div>
                                                <div class="score-meter">
                                                    <span class="score-val"><?php echo $row['total_score']; ?> / 4</span>
                                                    <div class="progress" style="height: 4px;">
                                                        <div class="progress-bar <?php echo ($score >= 3) ? 'bg-success' : (($score >= 2) ? 'bg-primary' : 'bg-warning'); ?>" style="width: <?php echo min(100, max(0, ($score / 4) * 100)); ?>%;"></div>
                                                    </div>
                                                </div>
                                                <a href="<?php echo BASE_URL; ?>/supervisor/pending-endorsements.php" class="btn btn-primary btn-sm btn-review">Review</a>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <div class="text-center pb-3">
                            <a href="<?php echo BASE_URL; ?>/supervisor/pending-endorsements.php" class="text-decoration-none small text-muted hover-primary">
                                View all pending validations <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="chart-card workflow-card">
            <div class="cc-header">
                <h5 class="mb-0"><i class="fas fa-route me-2"></i>Supervisor Workflow</h5>
            </div>
            <div class="cc-body">
                <div class="workflow-step">
                    <span class="step-icon"><i class="fas fa-inbox"></i></span>
                    <div>
                        <div class="fw-bold">Receive staff submissions</div>
                        <small class="text-muted">Check self-ratings and supporting details.</small>
                    </div>
                </div>
                <div class="workflow-step">
                    <span class="step-icon"><i class="fas fa-comments"></i></span>
                    <div>
                        <div class="fw-bold">Validate with comments</div>
                        <small class="text-muted">Endorse clean records or return revisions.</small>
                    </div>
                </div>
                <div class="workflow-step">
                    <span class="step-icon"><i class="fas fa-share-square"></i></span>
                    <div>
                        <div class="fw-bold">Forward to HR Manager</div>
                        <small class="text-muted">Approved validations move to manager review.</small>
                    </div>
                </div>
                <a href="<?php echo BASE_URL; ?>/supervisor/pending-endorsements.php" class="btn btn-primary w-100 rounded-pill mt-3">
                    <i class="fas fa-clipboard-check me-2"></i>Open Queue
                </a>
            </div>
        </div>
    </div>
</div>
</div>

<?php require_once '../includes/footer.php'; ?>
