<?php
$page_title = 'Completed Self-Ratings';
require_once '../includes/session-check.php';
checkRole(['Employee']);
require_once '../includes/functions.php';

$employee_id = (int)($_SESSION['employee_id'] ?? 0);
$user_id = (int)($_SESSION['user_id'] ?? 0);

// Fetch completed self-ratings
$completed_evaluations = $conn->query("
    SELECT e.evaluation_id, e.evaluation_type, e.status, e.evaluation_period_start, e.evaluation_period_end,
           et.template_name, e.total_score, e.performance_level, e.submitted_date, e.updated_at
    FROM evaluations e
    LEFT JOIN evaluation_templates et ON e.template_id = et.template_id
    WHERE e.employee_id = $employee_id 
      AND e.submitted_by = $user_id
      AND e.status IN ('Approved', 'Pending Supervisor', 'Pending Manager', 'Pending HR Consolidation')
      AND e.deleted_at IS NULL
    ORDER BY e.submitted_date DESC, e.evaluation_id DESC
");

require_once '../includes/header.php';
?>

<div class="page-hero fadeup">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-3">
        <div>
            <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.55);">Employee Portal · Evaluation</div>
            <h4 class="text-white fw-bold mb-0 mt-1"><i class="fas fa-check-circle me-2 text-success"></i>Completed Self-Ratings</h4>
        </div>
        <div class="d-none d-md-block text-end">
            <a href="<?php echo BASE_URL; ?>/employee/dashboard.php" class="btn btn-outline-light btn-sm rounded-pill px-3 mb-1">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>
    <p class="text-white-50 small mb-0 d-none d-md-block"><i class="fas fa-info-circle me-1"></i>View your completed 360-degree self-ratings and their current status.</p>
</div>

<!-- Mobile-only section -->
<div class="d-md-none d-flex justify-content-between align-items-center mt-3 mb-4 flex-wrap gap-3 fadeup" style="animation-delay: 0.1s;">
    <a href="<?php echo BASE_URL; ?>/employee/dashboard.php" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
        <i class="fas fa-arrow-left me-2"></i>Back to My Dashboard
    </a>
    <div class="alert alert-light border-0 shadow-sm py-2 px-3 mb-0" style="border-radius: 10px; font-size: 0.85rem; background: #fff;">
        <i class="fas fa-info-circle me-2 text-primary"></i>
        <span class="text-muted fw-500">View your completed self-ratings.</span>
    </div>
</div>

<div class="row g-4">
    <div class="col-12">
        <div class="content-card">
            <div class="card-header">
                <h5><i class="fas fa-list me-2"></i>My Completed Self-Ratings</h5>
            </div>
            <div class="card-body">
                <?php if ($completed_evaluations->num_rows === 0): ?>
                    <div class="empty-state py-5">
                        <i class="fas fa-inbox d-block"></i>
                        <p class="mb-0">No completed self-ratings yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Template</th>
                                    <th>Period</th>
                                    <th>Status</th>
                                    <th>Score</th>
                                    <th>Submitted</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($comp = $completed_evaluations->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold"><?php echo e($comp['evaluation_type']); ?></div>
                                        </td>
                                        <td><?php echo e($comp['template_name'] ?? '—'); ?></td>
                                        <td>
                                            <?php echo formatDate($comp['evaluation_period_start'] ?? ''); ?> -<br>
                                            <?php echo formatDate($comp['evaluation_period_end'] ?? ''); ?>
                                        </td>
                                        <td><span class="badge <?php echo getStatusBadgeClass($comp['status']); ?>"><?php echo e($comp['status']); ?></span></td>
                                        <td>
                                            <strong><?php echo e($comp['total_score'] ?? '0.00'); ?></strong>
                                            <?php if (!empty($comp['performance_level'])): ?>
                                                <span class="badge bg-primary"><?php echo e($comp['performance_level']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo formatDateTime($comp['submitted_date'] ?? ''); ?></td>
                                        <td>
                                            <a href="<?php echo BASE_URL; ?>/employee/self-rating.php?view=<?php echo (int)$comp['evaluation_id']; ?>" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-eye me-1"></i>View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
