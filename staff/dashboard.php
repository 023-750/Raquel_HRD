<?php
$page_title = 'Staff Dashboard';
require_once '../includes/session-check.php';
checkRole(['HR Staff']);
require_once '../includes/header.php';

$uid = (int) $_SESSION['user_id'];

// Fetch stats
$draft_count = $conn->query("SELECT COUNT(*) as c FROM evaluations WHERE submitted_by = $uid AND status = 'Draft'")->fetch_assoc()['c'];
$submitted_month = $conn->query("SELECT COUNT(*) as c FROM evaluations WHERE submitted_by = $uid AND MONTH(submitted_date) = MONTH(CURRENT_DATE()) AND YEAR(submitted_date) = YEAR(CURRENT_DATE())")->fetch_assoc()['c'];
$approved_count = $conn->query("SELECT COUNT(*) as c FROM evaluations WHERE submitted_by = $uid AND status = 'Approved'")->fetch_assoc()['c'];
$returned_count = $conn->query("SELECT COUNT(*) as c FROM evaluations WHERE submitted_by = $uid AND status = 'Returned'")->fetch_assoc()['c'];
$confirmed_count = $conn->query("SELECT COUNT(*) as c FROM evaluations WHERE status IN ('Pending HR Consolidation', 'Supervisor Confirmed') AND supervisor_confirmed_date IS NOT NULL")->fetch_assoc()['c'];

// Recent submissions
$recent = $conn->query("SELECT ev.*, CONCAT(e.first_name, ' ', e.last_name) as employee_name, et.template_name
    FROM evaluations ev
    LEFT JOIN employees e ON ev.employee_id = e.employee_id
    LEFT JOIN evaluation_templates et ON ev.template_id = et.template_id
    WHERE ev.submitted_by = $uid
    ORDER BY ev.created_at DESC LIMIT 5");
?>

<style>
    .staff-dashboard .quick-action-grid {
        display: grid;
        gap: 14px;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .staff-dashboard .quick-action-card {
        align-items: center;
        background: #fff;
        border: 1px solid #eef2e8;
        border-radius: 14px;
        color: inherit;
        display: grid;
        gap: 12px;
        grid-template-columns: 44px minmax(0, 1fr) 18px;
        min-height: 92px;
        padding: 16px;
        text-decoration: none;
        transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .staff-dashboard .quick-action-card:hover {
        border-color: var(--primary-light);
        box-shadow: 0 10px 24px rgba(12, 32, 8, 0.08);
        transform: translateY(-2px);
    }

    .staff-dashboard .quick-action-card .qa-icon {
        align-items: center;
        border-radius: 12px;
        display: inline-flex;
        height: 44px;
        justify-content: center;
        width: 44px;
    }

    .staff-dashboard .quick-action-card.primary .qa-icon {
        background: rgba(41, 67, 6, 0.09);
        color: var(--primary-blue);
    }

    .staff-dashboard .quick-action-card.gold .qa-icon {
        background: rgba(189, 148, 20, 0.14);
        color: #a97800;
    }

    .staff-dashboard .quick-action-card.info .qa-icon {
        background: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
    }

    .staff-dashboard .quick-action-card.green .qa-icon {
        background: rgba(25, 135, 84, 0.1);
        color: #198754;
    }

    .staff-dashboard .quick-action-card.purple .qa-icon {
        background: rgba(111, 66, 193, 0.1);
        color: #6f42c1;
    }

    .staff-dashboard .quick-action-card strong {
        display: block;
        font-size: 0.95rem;
        line-height: 1.2;
    }

    .staff-dashboard .quick-action-card small {
        color: var(--text-muted);
        display: block;
        margin-top: 3px;
    }

    .staff-dashboard .submission-list {
        padding: 15px;
    }

    .staff-dashboard .submission-item {
        align-items: center;
        background: #fff;
        border: 1px solid #f0f0f0;
        border-radius: 14px;
        display: grid;
        gap: 16px;
        grid-template-columns: minmax(0, 1.4fr) 150px 120px;
        margin-bottom: 12px;
        padding: 15px;
        transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .staff-dashboard .submission-item:hover {
        border-color: var(--primary-light);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        transform: translateX(5px);
    }

    .staff-dashboard .submission-main {
        align-items: center;
        display: flex;
        gap: 12px;
        min-width: 0;
    }

    .staff-dashboard .avatar-circle {
        align-items: center;
        background: rgba(41, 67, 6, 0.06);
        border-radius: 12px;
        color: var(--primary-blue);
        display: inline-flex;
        flex-shrink: 0;
        font-weight: 800;
        height: 42px;
        justify-content: center;
        width: 42px;
    }

    .staff-dashboard .submission-details {
        min-width: 0;
    }

    .staff-dashboard .submission-details h6 {
        font-size: 0.95rem;
        font-weight: 700;
        margin: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .staff-dashboard .submission-details span {
        color: var(--text-muted);
        display: block;
        font-size: 0.75rem;
        margin-top: 2px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .staff-dashboard .submission-meta {
        color: var(--text-muted);
        font-size: 0.75rem;
    }

    .staff-dashboard .submission-score {
        text-align: right;
    }

    .staff-dashboard .score-value {
        display: block;
        font-size: 0.9rem;
        font-weight: 800;
        margin-bottom: 5px;
    }

    .staff-dashboard .workflow-card {
        min-height: 100%;
    }

    .staff-dashboard .workflow-step {
        display: grid;
        gap: 12px;
        grid-template-columns: 38px minmax(0, 1fr);
        padding: 12px 0;
    }

    .staff-dashboard .workflow-step + .workflow-step {
        border-top: 1px solid #f0f4eb;
    }

    .staff-dashboard .workflow-step .step-icon {
        align-items: center;
        background: rgba(41, 67, 6, 0.08);
        border-radius: 10px;
        color: var(--primary-blue);
        display: inline-flex;
        height: 38px;
        justify-content: center;
        width: 38px;
    }

    .staff-dashboard .empty-state-card {
        color: var(--text-muted);
        padding: 42px 20px;
        text-align: center;
    }

    .staff-dashboard .empty-state-card i {
        display: block;
        font-size: 2.6rem;
        margin-bottom: 14px;
        opacity: 0.2;
    }

    @media (max-width: 768px) {
        .staff-dashboard .quick-action-grid,
        .staff-dashboard .submission-item {
            grid-template-columns: 1fr;
        }

        .staff-dashboard .quick-action-card {
            grid-template-columns: 42px minmax(0, 1fr) 18px;
            min-height: 82px;
        }

        .staff-dashboard .submission-item {
            align-items: stretch;
            gap: 12px;
        }

        .staff-dashboard .submission-item:hover {
            transform: none;
        }

        .staff-dashboard .submission-score,
        .staff-dashboard .submission-meta {
            text-align: left;
        }

        .staff-dashboard .submission-details h6,
        .staff-dashboard .submission-details span {
            white-space: normal;
        }
    }
</style>

<div class="staff-dashboard">
<div class="page-hero fadeup">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
        <div>
            <div class="mb-1" style="color:#FFD97D;font-size:.88rem;font-weight:600;letter-spacing:.3px;"><?php echo getGreeting($_SESSION['full_name'] ?? ''); ?></div>
            <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.55);">HR Staff · Dashboard</div>
            <h4 class="text-white fw-bold mb-0 mt-1"><i class="fas fa-pen-to-square me-2" style="color:#BD9414;"></i>Staff Workspace</h4>
        </div>
        <div style="color:rgba(255,255,255,.6);font-size:.8rem;">
            <i class="fas fa-sync-alt me-1"></i>Data as of <?php echo date('F d, Y'); ?>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-6 col-md-2">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?php echo $draft_count; ?></div>
                        <div class="stat-label">Drafts</div>
                    </div>
                    <i class="fas fa-file-alt stat-icon" style="color:#ffc107;"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?php echo $submitted_month; ?></div>
                        <div class="stat-label">Submitted</div>
                    </div>
                    <i class="fas fa-paper-plane stat-icon" style="color:#0d6efd;"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?php echo $confirmed_count; ?></div>
                        <div class="stat-label">Confirmed by Supervisor</div>
                    </div>
                    <i class="fas fa-user-check stat-icon" style="color:#6f42c1;"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?php echo $approved_count; ?></div>
                        <div class="stat-label">Approved</div>
                    </div>
                    <i class="fas fa-check-circle stat-icon" style="color:#28a745;"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?php echo $returned_count; ?></div>
                        <div class="stat-label">Returned</div>
                    </div>
                    <i class="fas fa-undo stat-icon" style="color:#dc3545;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="chart-card h-100">
            <div class="cc-header">
                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
            </div>
            <div class="cc-body">
                <div class="quick-action-grid">
                    <a href="<?php echo BASE_URL; ?>/staff/submit-evaluation.php" class="quick-action-card primary">
                        <span class="qa-icon"><i class="fas fa-edit"></i></span>
                        <span>
                            <strong>Submit Evaluation</strong>
                            <small>Create a new employee evaluation.</small>
                        </span>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/staff/my-drafts.php" class="quick-action-card gold">
                        <span class="qa-icon"><i class="fas fa-file-alt"></i></span>
                        <span>
                            <strong>Continue Drafts</strong>
                            <small>Resume evaluations in progress.</small>
                        </span>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/staff/my-submissions.php" class="quick-action-card info">
                        <span class="qa-icon"><i class="fas fa-list-check"></i></span>
                        <span>
                            <strong>My Submissions</strong>
                            <small>Track status and scores.</small>
                        </span>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/staff/search-employees.php" class="quick-action-card green">
                        <span class="qa-icon"><i class="fas fa-search"></i></span>
                        <span>
                            <strong>Search Employees</strong>
                            <small>Look up records for evaluation.</small>
                        </span>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/staff/supervisor-ratings.php" class="quick-action-card purple">
                        <span class="qa-icon"><i class="fas fa-user-check"></i></span>
                        <span>
                            <strong>Supervisor Ratings</strong>
                            <small>View 360° ratings from supervisors.</small>
                        </span>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="chart-card workflow-card">
            <div class="cc-header">
                <h5 class="mb-0"><i class="fas fa-route me-2"></i>Staff Flow</h5>
            </div>
            <div class="cc-body">
                <div class="workflow-step">
                    <span class="step-icon"><i class="fas fa-user-check"></i></span>
                    <div>
                        <div class="fw-bold">Choose employee</div>
                        <small class="text-muted">Find the right employee record before creating an evaluation.</small>
                    </div>
                </div>
                <div class="workflow-step">
                    <span class="step-icon"><i class="fas fa-clipboard-list"></i></span>
                    <div>
                        <div class="fw-bold">Complete criteria</div>
                        <small class="text-muted">Save as draft while reviewing scores and comments.</small>
                    </div>
                </div>
                <div class="workflow-step">
                    <span class="step-icon"><i class="fas fa-share-square"></i></span>
                    <div>
                        <div class="fw-bold">Submit for validation</div>
                        <small class="text-muted">Clean submissions move forward to supervisor review.</small>
                    </div>
                </div>
                <a href="<?php echo BASE_URL; ?>/staff/submit-evaluation.php" class="btn btn-primary w-100 rounded-pill mt-3">
                    <i class="fas fa-edit me-2"></i>Start Evaluation
                </a>
            </div>
        </div>
    </div>
</div>

<div class="chart-card">
    <div class="cc-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Submissions</h5>
        <a href="<?php echo BASE_URL; ?>/staff/my-submissions.php" class="btn btn-sm btn-outline-primary rounded-pill px-3">View All</a>
    </div>
    <div class="cc-body p-0">
        <div class="submission-list">
            <?php if ($recent->num_rows === 0): ?>
                <div class="empty-state-card">
                    <i class="fas fa-file-circle-plus"></i>
                    <p class="mb-0">No submissions yet. Start by submitting a new evaluation.</p>
                </div>
            <?php else: ?>
                <?php while ($row = $recent->fetch_assoc()):
                    $name_parts = preg_split('/\s+/', trim($row['employee_name'] ?? ''));
                    $initials = strtoupper(substr($name_parts[0] ?? 'U', 0, 1) . substr($name_parts[1] ?? '', 0, 1));
                    $score = $row['total_score'] !== null ? (float) $row['total_score'] : null;
                    $score_width = $score !== null ? min(100, max(0, $score)) : 0;
                    $score_label = $score !== null ? rtrim(rtrim(number_format($score, 1), '0'), '.') . '%' : '-';
                ?>
                    <div class="submission-item">
                        <div class="submission-main">
                            <div class="avatar-circle"><?php echo e($initials ?: 'U'); ?></div>
                            <div class="submission-details">
                                <h6><?php echo e($row['employee_name']); ?></h6>
                                <span><?php echo e($row['template_name'] ?? 'Evaluation template'); ?></span>
                            </div>
                        </div>
                        <div class="submission-meta">
                            <span class="badge <?php echo getStatusBadgeClass($row['status']); ?>"><?php echo e($row['status']); ?></span>
                            <div class="mt-2"><?php echo $row['submitted_date'] ? formatDate($row['submitted_date']) : 'Draft'; ?></div>
                        </div>
                        <div class="submission-score">
                            <span class="score-value"><?php echo $score_label; ?></span>
                            <div class="progress" style="height: 4px;">
                                <div class="progress-bar <?php echo ($score_width >= 80) ? 'bg-success' : (($score_width >= 60) ? 'bg-primary' : 'bg-warning'); ?>" style="width: <?php echo $score_width; ?>%;"></div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>

<?php require_once '../includes/footer.php'; ?>
