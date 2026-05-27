<?php
/**
 * Employee Portal - Supervisor Confirmation of Self-Ratings
 * Immediate Head/Manager confirms or alters self-ratings before sending to HRD
 */
$page_title = 'Confirm Self-Rating';
require_once '../includes/session-check.php';
checkRole(['Employee']);
require_once '../includes/functions.php';

$user_id = (int)($_SESSION['user_id'] ?? 0);
$supervisor_employee_id = (int)($_SESSION['employee_id'] ?? 0);

// Ensure 360-degree columns exist
ensure360DegreeEvaluationColumns($conn);

// Get evaluation ID from URL
$evaluation_id = (int)($_GET['evaluation_id'] ?? 0);

// Check if user has subordinates (is a supervisor)
$is_supervisor = hasEmployeeSubordinates($conn, $supervisor_employee_id);

// Fetch the evaluation with employee details
$evaluation = null;
$employee = null;
$scores = [];
$template = null;
$criteria_kra = [];
$criteria_behavior = [];

if ($evaluation_id > 0) {
    $eval_stmt = $conn->prepare("
        SELECT e.*, emp.employee_id, emp.employee_code, emp.first_name, emp.last_name, emp.job_title,
               emp.department_id, emp.branch_id, emp.reports_to,
               d.department_name, b.branch_name,
               t.template_name, t.kra_weight, t.behavior_weight
        FROM evaluations e
        JOIN employees emp ON e.employee_id = emp.employee_id
        LEFT JOIN departments d ON emp.department_id = d.department_id
        LEFT JOIN branches b ON emp.branch_id = b.branch_id
        LEFT JOIN evaluation_templates t ON e.template_id = t.template_id
        WHERE e.evaluation_id = ? AND e.status IN ('Pending Supervisor', 'Supervisor Confirmed')
        LIMIT 1
    ");
    $eval_stmt->bind_param("i", $evaluation_id);
    $eval_stmt->execute();
    $evaluation = $eval_stmt->get_result()->fetch_assoc();
    $eval_stmt->close();
    
    if ($evaluation) {
        // Check if this supervisor is the employee's immediate head
        $is_authorized = isSupervisorOfEmployee($conn, $user_id, (int)$evaluation['employee_id']);
        
        if (!$is_authorized) {
            redirectWith(BASE_URL . '/employee/dashboard.php', 'danger', 'You are not authorized to confirm this rating.');
        }
        
        // Fetch scores
        $scores_stmt = $conn->prepare("
            SELECT es.*, ec.criterion_name, ec.description, ec.weight, ec.section
            FROM evaluation_scores es
            JOIN evaluation_criteria ec ON es.criterion_id = ec.criterion_id
            WHERE es.evaluation_id = ?
            ORDER BY ec.section, ec.sort_order
        ");
        $scores_stmt->bind_param("i", $evaluation_id);
        $scores_stmt->execute();
        $scores_result = $scores_stmt->get_result();
        while ($score = $scores_result->fetch_assoc()) {
            $scores[(int)$score['criterion_id']] = $score;
            if ($score['section'] === 'Behavior') {
                $criteria_behavior[] = $score;
            } else {
                $criteria_kra[] = $score;
            }
        }
        $scores_stmt->close();
    }
}

// Handle form submission (confirmation or sending to HR)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $evaluation && $is_supervisor) {
    $action = $_POST['confirm_action'] ?? '';
    $supervisor_comments = trim($_POST['supervisor_comments'] ?? '');
    $supervisor_altered = false;
    
    if ($action === 'confirm_and_send') {
        // Supervisor can alter scores if needed
        $kra_scores = $_POST['kra_scores'] ?? [];
        $beh_scores = $_POST['beh_scores'] ?? [];
        
        // Check if scores were altered
        foreach (array_merge($kra_scores, $beh_scores) as $criterion_id => $new_rating) {
            $criterion_id = (int)$criterion_id;
            $new_rating = (float)$new_rating;
            $original_rating = (float)($scores[$criterion_id]['score_value'] ?? 0);
            
            if (abs($new_rating - $original_rating) > 0.01) {
                $supervisor_altered = true;
                // Update the score
                $update_score = $conn->prepare("
                    UPDATE evaluation_scores 
                    SET score_value = ?, weighted_score = ?,
                        supervisor_override_score = NULL, supervisor_override_by = NULL, supervisor_override_at = NULL,
                        manager_override_score = NULL, manager_override_by = NULL, manager_override_at = NULL
                    WHERE evaluation_id = ? AND criterion_id = ?
                ");
                $weight = (float)($scores[$criterion_id]['weight'] ?? 0);
                $weighted = round(($weight / 100) * $new_rating, 2);
                $update_score->bind_param("ddii", $new_rating, $weighted, $evaluation_id, $criterion_id);
                $update_score->execute();
                $update_score->close();
            }
        }
        
        // Recalculate totals if altered
        $new_total_score = (float)$evaluation['total_score'];
        $new_kra_subtotal = (float)$evaluation['kra_subtotal'];
        $new_behavior_average = (float)$evaluation['behavior_average'];
        $new_performance_level = $evaluation['performance_level'];
        $new_supervisor_rating = null;
        
        if ($supervisor_altered) {
            // Recalculate KRA
            $kra_subtotal = 0;
            foreach ($criteria_kra as $criterion) {
                $cid = (int)$criterion['criterion_id'];
                $rating = (float)($kra_scores[$cid] ?? $criterion['score_value'] ?? 0);
                $weight = (float)$criterion['weight'];
                $weighted = round(($weight / 100) * $rating, 2);
                $kra_subtotal += $weighted;
            }
            $new_kra_subtotal = round($kra_subtotal, 2);
            
            // Recalculate Behavior
            $beh_total = 0;
            $beh_count = 0;
            foreach ($criteria_behavior as $criterion) {
                $cid = (int)$criterion['criterion_id'];
                $rating = (float)($beh_scores[$cid] ?? $criterion['score_value'] ?? 0);
                $beh_total += $rating;
                $beh_count++;
            }
            $new_behavior_average = $beh_count > 0 ? round($beh_total / $beh_count, 2) : 0;
            
            // Calculate new total
            $kra_weight_pct = (float)($evaluation['kra_weight'] ?? 80);
            $beh_weight_pct = (float)($evaluation['behavior_weight'] ?? 20);
            $new_total_score = calculateEvalTotal($new_kra_subtotal, $new_behavior_average, $kra_weight_pct, $beh_weight_pct);
            $new_performance_level = getPerformanceLevel($new_total_score);
            $new_supervisor_rating = $new_total_score;
        }
        
        // Update evaluation status
        $update = $conn->prepare("
            UPDATE evaluations 
            SET status = 'Pending HR Consolidation',
                supervisor_confirmed_by = ?,
                supervisor_confirmed_date = NOW(),
                supervisor_altered_scores = ?,
                supervisor_comments = ?,
                supervisor_rating = ?,
                sent_to_hr_date = NOW(),
                sent_to_hr_by = ?,
                total_score = ?,
                kra_subtotal = ?,
                behavior_average = ?,
                performance_level = ?
            WHERE evaluation_id = ?
        ");
        $altered_int = $supervisor_altered ? 1 : 0;
        $update->bind_param(
            "iissidddsi",
            $user_id,
            $altered_int,
            $supervisor_comments,
            $new_supervisor_rating,
            $user_id,
            $new_total_score,
            $new_kra_subtotal,
            $new_behavior_average,
            $new_performance_level,
            $evaluation_id
        );
        $update->execute();
        $update->close();
        
        // Notify HR Supervisor and HR Manager
        $hr_users = $conn->query("SELECT user_id FROM users WHERE role IN ('HR Supervisor', 'HR Manager') AND is_active = 1");
        $emp_name = $evaluation['first_name'] . ' ' . $evaluation['last_name'];
        $supervisor_name = $_SESSION['full_name'] ?? 'Supervisor';
        while ($hr = $hr_users->fetch_assoc()) {
            createNotification(
                $conn,
                (int)$hr['user_id'],
                'Rating Ready for Consolidation',
                $supervisor_name . ' confirmed self-rating for ' . $emp_name . ($supervisor_altered ? ' (with alterations)' : ''),
                BASE_URL . '/supervisor/pending-endorsements.php'
            );
        }
        
        // Notify employee
        $emp_user = $conn->query("SELECT user_id FROM users WHERE employee_id = " . (int)$evaluation['employee_id'] . " LIMIT 1")->fetch_assoc();
        if ($emp_user) {
            createNotification(
                $conn,
                (int)$emp_user['user_id'],
                'Self-Rating Confirmed',
                'Your supervisor has confirmed your self-rating and sent it to HRD.',
                BASE_URL . '/employee/self-rating.php'
            );
        }
        
        logAudit($conn, $user_id, 'UPDATE', 'Evaluation', $evaluation_id, 'Confirmed self-rating' . ($supervisor_altered ? ' with alterations' : ''));
        
        redirectWith(BASE_URL . '/employee/confirm-rating.php?evaluation_id=' . $evaluation_id, 'success', 'Self-rating confirmed and sent to HRD successfully.');
    }
}

// Fetch pending confirmations for this supervisor
$pending_confirmations = [];
if ($is_supervisor) {
    $pending_stmt = $conn->prepare("
        SELECT e.*, emp.first_name, emp.last_name, emp.job_title, emp.employee_code,
               t.template_name
        FROM evaluations e
        JOIN employees emp ON e.employee_id = emp.employee_id
        LEFT JOIN evaluation_templates t ON e.template_id = t.template_id
        WHERE e.status = 'Pending Supervisor' AND emp.reports_to = ?
        ORDER BY e.submitted_date DESC
    ");
    $pending_stmt->bind_param("i", $supervisor_employee_id);
    $pending_stmt->execute();
    $pending_confirmations = $pending_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $pending_stmt->close();
}

require_once '../includes/header.php';
?>

<div class="page-hero fadeup">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-0 gap-4">
        <div>
            <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.55);">Employee Portal</div>
            <h2 class="text-white fw-bold mb-1 mt-1">Confirm Self-Rating</h2>
            <p class="mb-0 text-white-50 small">
                <i class="fas fa-check-double me-1"></i>Review and confirm self-ratings from your team members
            </p>
        </div>
    </div>
</div>

<?php if (!$is_supervisor): ?>
    <!-- Not a supervisor - informational message -->
    <div class="content-card fadeup-1">
        <div class="card-body text-center py-5">
            <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">Supervisor Access Only</h5>
            <p class="text-muted mb-0">
                This feature is available for Immediate Heads and Department Managers only.<br>
                If you believe you should have access, please contact HRD.
            </p>
        </div>
    </div>
<?php elseif (!$evaluation): ?>
    <!-- List of pending confirmations -->
    <div class="content-card fadeup-1">
        <div class="card-header">
            <h5><i class="fas fa-clipboard-check me-2"></i>Pending Confirmations</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($pending_confirmations)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-check-circle fa-3x mb-3"></i>
                    <p>No pending self-ratings to confirm.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Template</th>
                                <th>Period</th>
                                <th>Score</th>
                                <th>Submitted</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_confirmations as $pending): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?php echo e($pending['last_name'] . ', ' . $pending['first_name']); ?></div>
                                        <div class="small text-muted"><?php echo e($pending['job_title']); ?></div>
                                    </td>
                                    <td><?php echo e($pending['template_name'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php echo formatDate($pending['evaluation_period_start']); ?> -<br>
                                        <?php echo formatDate($pending['evaluation_period_end']); ?>
                                    </td>
                                    <td>
                                        <span class="fw-bold"><?php echo e($pending['total_score']); ?></span><br>
                                        <span class="badge bg-light text-dark"><?php echo e($pending['performance_level']); ?></span>
                                    </td>
                                    <td><?php echo formatDateTime($pending['submitted_date']); ?></td>
                                    <td>
                                        <a href="?evaluation_id=<?php echo (int)$pending['evaluation_id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye me-1"></i>Review
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <!-- Review and Confirm Form -->
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="content-card fadeup-1">
                <div class="card-header">
                    <h5><i class="fas fa-star me-2"></i>Review Self-Rating</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <!-- Employee Info -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Employee</label>
                                <input type="text" class="form-control" 
                                       value="<?php echo e($evaluation['last_name'] . ', ' . $evaluation['first_name']); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Position</label>
                                <input type="text" class="form-control" 
                                       value="<?php echo e($evaluation['job_title']); ?>" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Evaluation Type</label>
                                <input type="text" class="form-control" 
                                       value="<?php echo e($evaluation['evaluation_type']); ?>" readonly>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Period</label>
                                <input type="text" class="form-control" 
                                       value="<?php echo formatDate($evaluation['evaluation_period_start']) . ' - ' . formatDate($evaluation['evaluation_period_end']); ?>" readonly>
                            </div>
                        </div>

                        <!-- Self Comments -->
                        <?php if (!empty($evaluation['staff_comments'])): ?>
                            <div class="alert alert-light border mb-4">
                                <label class="form-label fw-semibold">Employee's Comments:</label>
                                <p class="mb-0"><?php echo nl2br(e($evaluation['staff_comments'])); ?></p>
                            </div>
                        <?php endif; ?>

                        <!-- KRA Scores -->
                        <?php if (!empty($criteria_kra)): ?>
                            <div class="section-premium-label mb-3">
                                <i class="fas fa-bullseye"></i>KRA Ratings (You can adjust if needed)
                            </div>
                            <div class="table-responsive mb-4">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>Criterion</th>
                                            <th style="width:100px;">Weight</th>
                                            <th style="width:140px;">Employee Rating</th>
                                            <th style="width:140px;">Your Rating</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($criteria_kra as $criterion): ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold"><?php echo e($criterion['criterion_name']); ?></div>
                                                    <?php if (!empty($criterion['description'])): ?>
                                                        <div class="small text-muted"><?php echo e($criterion['description']); ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo e($criterion['weight']); ?>%</td>
                                                <td>
                                                    <span class="badge bg-light text-dark fs-6">
                                                        <?php echo e($criterion['score_value']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control" 
                                                           name="kra_scores[<?php echo (int)$criterion['criterion_id']; ?>]"
                                                           min="0" max="4" step="0.01"
                                                           value="<?php echo e($criterion['score_value']); ?>"
                                                           placeholder="0.00 - 4.00">
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                </tbody>
                                </table>
                            </div>
                        <?php endif; ?>

                        <!-- Behavior Scores -->
                        <?php if (!empty($criteria_behavior)): ?>
                            <div class="section-premium-label mb-3">
                                <i class="fas fa-heart"></i>Behavior Ratings (You can adjust if needed)
                            </div>
                            <div class="table-responsive mb-4">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>Criterion</th>
                                            <th style="width:140px;">Employee Rating</th>
                                            <th style="width:140px;">Your Rating</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($criteria_behavior as $criterion): ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold"><?php echo e($criterion['criterion_name']); ?></div>
                                                    <?php if (!empty($criterion['description'])): ?>
                                                        <div class="small text-muted"><?php echo e($criterion['description']); ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark fs-6">
                                                        <?php echo e($criterion['score_value']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control" 
                                                           name="beh_scores[<?php echo (int)$criterion['criterion_id']; ?>]"
                                                           min="0" max="4" step="0.01"
                                                           value="<?php echo e($criterion['score_value']); ?>"
                                                           placeholder="0.00 - 4.00">
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>

                        <!-- Supervisor Comments -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Your Comments / Justification for Changes</label>
                            <textarea class="form-control" name="supervisor_comments" rows="4" 
                                      placeholder="Enter your comments or justification for any rating adjustments..."></textarea>
                            <div class="form-text">Optional. This will be visible to HR and the employee.</div>
                        </div>

                        <!-- Current Score Display -->
                        <div class="alert alert-info mb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="small text-muted">Employee's Self-Rating Score</div>
                                    <div class="h4 mb-0"><?php echo e($evaluation['total_score']); ?> 
                                        <span class="badge bg-primary"><?php echo e($evaluation['performance_level']); ?></span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="small text-muted">KRA: <?php echo e($evaluation['kra_subtotal']); ?></div>
                                    <div class="small text-muted">Behavior: <?php echo e($evaluation['behavior_average']); ?></div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex flex-wrap justify-content-between gap-2">
                            <a href="<?php echo BASE_URL; ?>/employee/confirm-rating.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to List
                            </a>
                            <button type="submit" name="confirm_action" value="confirm_and_send" 
                                    class="btn btn-primary" onclick="return confirm('Confirm this self-rating and send to HRD?');">
                                <i class="fas fa-check-circle me-2"></i>Confirm & Send to HRD
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- How It Works -->
            <div class="content-card fadeup-1 mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-info-circle me-2"></i>How It Works</h5>
                </div>
                <div class="card-body">
                    <div class="small text-muted">
                        <p class="mb-2"><strong>1.</strong> Review the employee's self-rating.</p>
                        <p class="mb-2"><strong>2.</strong> Adjust ratings if you disagree (optional).</p>
                        <p class="mb-2"><strong>3.</strong> Add comments to justify any changes.</p>
                        <p class="mb-0"><strong>4.</strong> Confirm and send to HRD for consolidation.</p>
                    </div>
                </div>
            </div>

            <!-- Rating Scale -->
            <div class="content-card fadeup-1">
                <div class="card-header">
                    <h5><i class="fas fa-chart-bar me-2"></i>Rating Scale</h5>
                </div>
                <div class="card-body">
                    <div class="small">
                        <div class="d-flex justify-content-between mb-1">
                            <span>4.00</span>
                            <span class="badge bg-success">Outstanding</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>2.60 - 3.59</span>
                            <span class="badge bg-info text-dark">Exceeds Expectations</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>2.00 - 2.59</span>
                            <span class="badge bg-primary">Meets Expectations</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Below 2.00</span>
                            <span class="badge bg-warning text-dark">Needs Improvement</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
