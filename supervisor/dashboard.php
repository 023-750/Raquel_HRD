<?php
$page_title = 'Supervisor Dashboard';
require_once '../includes/session-check.php';
checkRole(['HR Supervisor']);
require_once '../includes/header.php';

// Fetch stats
$pending_validations = $conn->query("SELECT COUNT(*) as c FROM evaluations WHERE status = 'Pending Supervisor'")->fetch_assoc()['c'];

$validated_month = $conn->query("SELECT COUNT(*) as c FROM evaluations WHERE endorsed_by = {$_SESSION['user_id']} AND MONTH(endorsed_date) = MONTH(CURRENT_DATE()) AND YEAR(endorsed_date) = YEAR(CURRENT_DATE())")->fetch_assoc()['c'];

$total_employees = $conn->query("SELECT COUNT(*) as c FROM employees WHERE is_active = 1 AND employee_id NOT IN (SELECT employee_id FROM users WHERE role = 'Admin' AND employee_id IS NOT NULL)")->fetch_assoc()['c'];

// Fetch recent pending validations
$pending = $conn->query("SELECT ev.*, CONCAT(e.first_name, ' ', e.last_name) as employee_name,
    u.full_name as submitted_by_name
    FROM evaluations ev
    LEFT JOIN employees e ON ev.employee_id = e.employee_id
    LEFT JOIN users u ON ev.submitted_by = u.user_id
    WHERE ev.status = 'Pending Supervisor'
    ORDER BY ev.submitted_date DESC LIMIT 5");
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
        .supervisor-dashboard .approval-item {
            align-items: stretch;
            flex-direction: column;
        }

        .supervisor-dashboard .approval-item .status-meta {
            text-align: left;
        }

        .supervisor-dashboard .approval-item .btn-review {
            width: 100%;
            text-align: center;
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
            <div class="cc-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Validation Queue</h5>
                <a href="<?php echo BASE_URL; ?>/supervisor/pending-endorsements.php" class="btn btn-sm btn-outline-primary rounded-pill px-3">View All</a>
            </div>
            <div class="cc-body p-0">
                <div class="approval-list">
                    <?php if ($pending->num_rows === 0): ?>
                        <div class="empty-state-card">
                            <i class="fas fa-clipboard-check"></i>
                            <p class="mb-0">All validations have been processed.</p>
                        </div>
                    <?php else: ?>
                        <?php while ($row = $pending->fetch_assoc()):
                            $name_parts = preg_split('/\s+/', trim($row['employee_name'] ?? ''));
                            $initials = strtoupper(substr($name_parts[0] ?? 'U', 0, 1) . substr($name_parts[1] ?? '', 0, 1));
                            $score = (float) ($row['total_score'] ?? 0);
                        ?>
                            <div class="approval-item">
                                <div class="emp-info">
                                    <div class="avatar-circle"><?php echo e($initials ?: 'U'); ?></div>
                                    <div class="details">
                                        <h6><?php echo e($row['employee_name']); ?></h6>
                                        <span>Submitted by <?php echo e($row['submitted_by_name']); ?></span>
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
                                    <div class="x-small">Pending Supervisor</div>
                                </div>
                                <a href="<?php echo BASE_URL; ?>/supervisor/pending-endorsements.php" class="btn btn-primary btn-review">Review</a>
                            </div>
                        <?php endwhile; ?>
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
