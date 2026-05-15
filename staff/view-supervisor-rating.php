<?php
/**
 * HR Staff Portal - View Supervisor Rating Details
 */
$page_title = 'View Supervisor Rating';
require_once '../includes/session-check.php';
checkRole(['HR Staff']);
require_once '../includes/functions.php';

$evaluation_id = (int)($_GET['id'] ?? 0);

if ($evaluation_id <= 0) {
    redirectWith('supervisor-ratings.php', 'danger', 'Invalid evaluation ID.');
}

// Fetch the evaluation with employee and supervisor details
$query = "
    SELECT e.*, emp.employee_code, emp.first_name, emp.last_name, emp.job_title,
           sup.first_name as sup_first, sup.last_name as sup_last, sup.job_title as sup_job,
           t.template_name, t.kra_weight, t.behavior_weight,
           d.department_name, b.branch_name
    FROM evaluations e
    JOIN employees emp ON e.employee_id = emp.employee_id
    LEFT JOIN employees sup ON e.supervisor_confirmed_by = sup.employee_id
    LEFT JOIN evaluation_templates t ON e.template_id = t.template_id
    LEFT JOIN departments d ON emp.department_id = d.department_id
    LEFT JOIN branches b ON emp.branch_id = b.branch_id
    WHERE e.evaluation_id = ?
    LIMIT 1
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $evaluation_id);
$stmt->execute();
$evaluation = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$evaluation) {
    redirectWith('supervisor-ratings.php', 'danger', 'Evaluation record not found.');
}

// Fetch scores
$scores_query = "
    SELECT es.*, ec.criterion_name, ec.description, ec.weight, ec.section
    FROM evaluation_scores es
    JOIN evaluation_criteria ec ON es.criterion_id = ec.criterion_id
    WHERE es.evaluation_id = ?
    ORDER BY ec.section, ec.sort_order
";
$stmt = $conn->prepare($scores_query);
$stmt->bind_param("i", $evaluation_id);
$stmt->execute();
$scores_result = $stmt->get_result();
$criteria_kra = [];
$criteria_behavior = [];
while ($score = $scores_result->fetch_assoc()) {
    if ($score['section'] === 'Behavior') {
        $criteria_behavior[] = $score;
    } else {
        $criteria_kra[] = $score;
    }
}
$stmt->close();

require_once '../includes/header.php';
?>

<div class="page-hero fadeup">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-0 gap-4">
        <div>
            <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.55);">HR Staff Portal · Details</div>
            <h2 class="text-white fw-bold mb-1 mt-1">Evaluation Details</h2>
            <p class="mb-0 text-white-50 small">
                <i class="fas fa-file-invoice me-1"></i>Reviewing supervisor-confirmed rating for <?php echo e($evaluation['first_name'] . ' ' . $evaluation['last_name']); ?>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="supervisor-ratings.php" class="btn btn-light rounded-pill px-4">
                <i class="fas fa-arrow-left me-2"></i>Back to List
            </a>
            <button onclick="window.print()" class="btn btn-primary rounded-pill px-4">
                <i class="fas fa-print me-2"></i>Print Report
            </button>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="content-card h-100 border-start border-4 border-primary">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-circle me-3">
                                <?php echo strtoupper(substr($evaluation['first_name'], 0, 1) . substr($evaluation['last_name'], 0, 1)); ?>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold"><?php echo e($evaluation['last_name'] . ', ' . $evaluation['first_name']); ?></h5>
                                <div class="text-muted small"><?php echo e($evaluation['employee_code']); ?></div>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="small text-muted">Position</div>
                                <div class="fw-semibold small"><?php echo e($evaluation['job_title']); ?></div>
                            </div>
                            <div class="col-6">
                                <div class="small text-muted">Department</div>
                                <div class="fw-semibold small"><?php echo e($evaluation['department_name'] ?: 'N/A'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="content-card h-100 border-start border-4 border-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="small text-muted mb-1">Final Performance Score</div>
                                <div class="display-6 fw-bold text-success mb-0"><?php echo number_format($evaluation['total_score'], 2); ?>%</div>
                                <span class="badge bg-success px-3 rounded-pill"><?php echo e($evaluation['performance_level']); ?></span>
                            </div>
                            <div class="text-end">
                                <div class="small text-muted">Type</div>
                                <div class="fw-bold text-primary"><?php echo e($evaluation['evaluation_type']); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scores Breakdown -->
        <div class="content-card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2 text-primary"></i>Score Breakdown</h5>
            </div>
            <div class="card-body">
                <!-- KRA Section -->
                <?php if (!empty($criteria_kra)): ?>
                    <div class="section-label mb-3 mt-2">
                        <span>KEY RESULT AREAS (<?php echo $evaluation['kra_weight']; ?>%)</span>
                    </div>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>Criterion</th>
                                    <th class="text-center" style="width: 100px;">Weight</th>
                                    <th class="text-center" style="width: 120px;">Rating (0-4)</th>
                                    <th class="text-center" style="width: 120px;">Weighted</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($criteria_kra as $kra): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold"><?php echo e($kra['criterion_name']); ?></div>
                                            <div class="small text-muted"><?php echo e($kra['description']); ?></div>
                                        </td>
                                        <td class="text-center"><?php echo $kra['weight']; ?>%</td>
                                        <td class="text-center fw-bold"><?php echo number_format($kra['score_value'], 2); ?></td>
                                        <td class="text-center"><?php echo number_format($kra['weighted_score'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="3" class="text-end">KRA Subtotal:</td>
                                    <td class="text-center text-primary"><?php echo number_format($evaluation['kra_subtotal'], 2); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?>

                <!-- Behavior Section -->
                <?php if (!empty($criteria_behavior)): ?>
                    <div class="section-label mb-3">
                        <span>BEHAVIORAL COMPETENCIES (<?php echo $evaluation['behavior_weight']; ?>%)</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>Criterion</th>
                                    <th class="text-center" style="width: 120px;">Rating (0-4)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($criteria_behavior as $beh): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold"><?php echo e($beh['criterion_name']); ?></div>
                                            <div class="small text-muted"><?php echo e($beh['description']); ?></div>
                                        </td>
                                        <td class="text-center fw-bold"><?php echo number_format($beh['score_value'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td class="text-end">Behavioral Average:</td>
                                    <td class="text-center text-primary"><?php echo number_format($evaluation['behavior_average'], 2); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Comments Section -->
        <div class="row g-4">
            <div class="col-md-6">
                <div class="content-card h-100">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="fas fa-comment-dots me-2 text-info"></i>Employee's Self-Comments</h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($evaluation['staff_comments'])): ?>
                            <p class="mb-0 small" style="line-height: 1.6;"><?php echo nl2br(e($evaluation['staff_comments'])); ?></p>
                        <?php else: ?>
                            <em class="text-muted small">No comments provided by employee.</em>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="content-card h-100 border-info border-top border-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="fas fa-user-shield me-2 text-info"></i>Supervisor's Feedback</h6>
                    </div>
                    <div class="card-body">
                        <?php if ($evaluation['supervisor_altered_scores']): ?>
                            <div class="alert alert-warning py-2 mb-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <span class="small">Supervisor adjusted the employee's self-rating.</span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($evaluation['supervisor_comments'])): ?>
                            <p class="mb-0 small" style="line-height: 1.6; font-style: italic;">"<?php echo nl2br(e($evaluation['supervisor_comments'])); ?>"</p>
                        <?php else: ?>
                            <em class="text-muted small">No comments provided by supervisor.</em>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Validation Info -->
        <div class="content-card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-shield-alt me-2 text-primary"></i>Validation Info</h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="small text-muted d-block mb-1">Confirmed By</label>
                    <div class="d-flex align-items-center">
                        <div class="avatar-circle-sm bg-primary text-white me-2">
                            <?php echo strtoupper(substr($evaluation['sup_first'] ?: 'S', 0, 1) . substr($evaluation['sup_last'] ?: '', 0, 1)); ?>
                        </div>
                        <div>
                            <div class="fw-bold small"><?php echo e($evaluation['sup_last'] . ', ' . $evaluation['sup_first'] ?: 'N/A'); ?></div>
                            <div class="text-muted" style="font-size: 0.7rem;"><?php echo e($evaluation['sup_job'] ?: 'Area Supervisor / Head'); ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="small text-muted d-block mb-1">Confirmation Date</label>
                    <div class="fw-semibold"><i class="far fa-calendar-check me-2 text-success"></i><?php echo formatDate($evaluation['supervisor_confirmed_date']); ?></div>
                    <div class="text-muted small ms-4"><?php echo date('h:i A', strtotime($evaluation['supervisor_confirmed_date'])); ?></div>
                </div>

                <div class="mb-4">
                    <label class="small text-muted d-block mb-1">Evaluation Period</label>
                    <div class="fw-semibold"><i class="far fa-clock me-2 text-primary"></i><?php echo formatDate($evaluation['evaluation_period_start']); ?> - <?php echo formatDate($evaluation['evaluation_period_end']); ?></div>
                </div>

                <div class="p-3 bg-light rounded-3 border">
                    <div class="small text-muted mb-2">Workflow Status</div>
                    <div class="d-flex align-items-center">
                        <div class="status-indicator active"></div>
                        <div class="ms-2 small fw-bold text-uppercase"><?php echo e($evaluation['status']); ?></div>
                    </div>
                    <div class="mt-2 small text-muted">
                        This evaluation is now ready for HR consolidation and final manager approval.
                    </div>
                </div>
            </div>
        </div>

        <!-- System Audit -->
        <div class="content-card">
            <div class="card-body">
                <h6 class="mb-3 text-muted small uppercase">System Audit Trail</h6>
                <div class="timeline-small">
                    <div class="item">
                        <span class="dot bg-success"></span>
                        <div class="time"><?php echo date('M d, H:i', strtotime($evaluation['supervisor_confirmed_date'])); ?></div>
                        <div class="text">Supervisor Confirmed</div>
                    </div>
                    <div class="item">
                        <span class="dot bg-primary"></span>
                        <div class="time"><?php echo date('M d, H:i', strtotime($evaluation['submitted_date'])); ?></div>
                        <div class="text">Employee Submitted Self-Rating</div>
                    </div>
                    <div class="item">
                        <span class="dot bg-secondary"></span>
                        <div class="time"><?php echo date('M d, H:i', strtotime($evaluation['created_at'])); ?></div>
                        <div class="text">Draft Created</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.section-label {
    display: flex;
    align-items: center;
    gap: 15px;
    color: var(--primary-blue);
    font-weight: 800;
    font-size: 0.75rem;
    letter-spacing: 1px;
}
.section-label::after {
    content: '';
    flex: 1;
    height: 1px;
    background: linear-gradient(to right, rgba(41, 67, 6, 0.1), transparent);
}
.avatar-circle-sm {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.75rem;
}
.timeline-small {
    position: relative;
    padding-left: 20px;
}
.timeline-small::before {
    content: '';
    position: absolute;
    left: 4px;
    top: 5px;
    bottom: 5px;
    width: 2px;
    background: #f0f0f0;
}
.timeline-small .item {
    position: relative;
    margin-bottom: 20px;
}
.timeline-small .item:last-child { margin-bottom: 0; }
.timeline-small .dot {
    position: absolute;
    left: -20px;
    top: 5px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #f0f0f0;
}
.timeline-small .time {
    font-size: 0.65rem;
    color: #999;
}
.timeline-small .text {
    font-size: 0.8rem;
    font-weight: 600;
}
.status-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #ccc;
}
.status-indicator.active {
    background: #28a745;
    box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.2);
}
@media print {
    .page-hero, .btn, aside, header { display: none !important; }
    .main-content { padding: 0 !important; margin: 0 !important; }
    .content-card { border: 1px solid #eee !important; box-shadow: none !important; }
}
</style>

<?php require_once '../includes/footer.php'; ?>
