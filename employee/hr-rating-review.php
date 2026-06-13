<?php
/**
 * Employee Portal - HR Internal Rating Review
 *
 * Handles the HR-specific self-rating review chain entirely within the Employee Portal:
 *   HR Staff     → reviewed by HR Supervisor  → reviewed by HR Manager → Approved
 *   HR Supervisor → reviewed by HR Manager   → Approved
 *   HR Manager   → reviewed by HR Supervisor → Approved
 *
 * Logged-in user must have role = 'Employee' AND their linked employee record must
 * hold an HR role (HR Supervisor or HR Manager) to act as a reviewer here.
 */
$page_title = 'HR Rating Review';
require_once '../includes/session-check.php';
checkRole(['Employee']);
require_once '../includes/functions.php';

ensureEvaluationWorkflowSchema($conn);

$user_id            = (int) ($_SESSION['user_id']     ?? 0);
$reviewer_emp_id    = (int) ($_SESSION['employee_id'] ?? 0);

// Determine the logged-in employee's own HR role
$reviewer_hr_role = getEmployeeHRRole($conn, $reviewer_emp_id); // 'HR Supervisor' | 'HR Manager' | null

// Only HR Supervisor and HR Manager can review on this page
if (!in_array($reviewer_hr_role, ['HR Supervisor', 'HR Manager'], true)) {
    redirectWith(BASE_URL . '/employee/dashboard.php', 'danger', 'Access Denied: This page is for HR Supervisors and HR Managers only.');
}

// ── Which statuses can THIS reviewer act on? ────────────────────────────────
// HR Supervisor reviews:  'Pending Supervisor'  (HR Staff or HR Manager submitted)
// HR Manager reviews:     'Pending Manager'     (HR Staff after HR Supervisor confirmed,
//                                                or HR Supervisor submitted directly)
$reviewer_pending_status = ($reviewer_hr_role === 'HR Supervisor') ? 'Pending Supervisor' : 'Pending Manager';

// ── Fetch a specific evaluation if ?evaluation_id=X ─────────────────────────
$evaluation_id = (int) ($_GET['evaluation_id'] ?? 0);
$evaluation    = null;
$scores        = [];
$criteria_kra  = [];
$criteria_behavior = [];
$is_readonly   = false;
$audit_history = [];

if ($evaluation_id > 0) {
    $eval_stmt = $conn->prepare("
        SELECT ev.*,
               emp.employee_id AS emp_record_id, emp.employee_code,
               emp.first_name, emp.last_name, emp.job_title,
               emp.department_id, emp.branch_id,
               d.department_name, b.branch_name,
               t.template_name, t.kra_weight, t.behavior_weight,
               u_sub.full_name AS submitted_by_name
        FROM evaluations ev
        JOIN employees emp ON ev.employee_id = emp.employee_id
        LEFT JOIN departments d  ON emp.department_id = d.department_id
        LEFT JOIN branches    b  ON emp.branch_id     = b.branch_id
        LEFT JOIN evaluation_templates t ON ev.template_id = t.template_id
        LEFT JOIN users u_sub ON ev.submitted_by = u_sub.user_id
        WHERE ev.evaluation_id = ?
          AND ev.deleted_at IS NULL
          AND ev.status IN ('Pending Supervisor', 'Pending Manager', 'Approved', 'Rejected', 'Returned')
        LIMIT 1
    ");
    $eval_stmt->bind_param("i", $evaluation_id);
    $eval_stmt->execute();
    $evaluation = $eval_stmt->get_result()->fetch_assoc();
    $eval_stmt->close();

    if ($evaluation) {
        // Must be an HR employee's evaluation
        $subject_hr_role = getEmployeeHRRole($conn, (int) $evaluation['employee_id']);
        $subject_is_main_hr = isMainOfficeHumanResourcesEmployee($conn, (int) $evaluation['employee_id']);

        if ($subject_hr_role === null && !$subject_is_main_hr) {
            redirectWith(BASE_URL . '/employee/hr-rating-review.php', 'danger', 'This evaluation is not part of the HR review chain.');
        }

        // Authorization: HR Supervisor can review HR Staff & HR Manager evals (Pending Supervisor)
        //               HR Manager can review HR Supervisor & HR Staff evals (Pending Manager)
        $authorized = false;
        if ($reviewer_hr_role === 'HR Supervisor') {
            // Can review evaluations currently at 'Pending Supervisor'
            // OR view ones they already acted on
            $authorized = in_array($evaluation['status'], ['Pending Supervisor', 'Pending Manager', 'Approved', 'Rejected', 'Returned'], true)
                && ($evaluation['status'] === 'Pending Supervisor'
                    || (int)($evaluation['supervisor_confirmed_by'] ?? 0) === $user_id
                    || (int)($evaluation['dept_supervisor_confirmed_by'] ?? 0) === $user_id);
        } elseif ($reviewer_hr_role === 'HR Manager') {
            $authorized = in_array($evaluation['status'], ['Pending Manager', 'Approved', 'Rejected'], true);
        }

        if (!$authorized) {
            redirectWith(BASE_URL . '/employee/hr-rating-review.php', 'danger', 'You are not authorized to review this evaluation, or it is no longer in the correct status.');
        }

        // Read-only if not in the reviewer's actionable status
        $is_readonly = ($evaluation['status'] !== $reviewer_pending_status);

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
        $sr = $scores_stmt->get_result();
        while ($row = $sr->fetch_assoc()) {
            $scores[(int) $row['criterion_id']] = $row;
            if ($row['section'] === 'Behavior') {
                $criteria_behavior[] = $row;
            } else {
                $criteria_kra[] = $row;
            }
        }
        $scores_stmt->close();

        // Audit history
        $audit_stmt = $conn->prepare("
            SELECT al.*, u.full_name, u.role
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.user_id
            WHERE al.entity_type = 'Evaluation' AND al.entity_id = ?
            ORDER BY al.timestamp DESC, al.log_id DESC
        ");
        $audit_stmt->bind_param("i", $evaluation_id);
        $audit_stmt->execute();
        $audit_history = $audit_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $audit_stmt->close();
    }
}

$readonly_attr  = $is_readonly ? 'readonly'  : '';
$disabled_attr  = $is_readonly ? 'disabled'  : '';

// ── Handle form submission ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $evaluation && !$is_readonly) {

    $action            = $_POST['confirm_action'] ?? '';
    $reviewer_comments = trim($_POST['reviewer_comments'] ?? '');
    $reviewer_altered  = false;

    if ($action === 'confirm') {
        $kra_scores = $_POST['kra_scores'] ?? [];
        $beh_scores = $_POST['beh_scores'] ?? [];

        $all_scores = [];
        foreach ($kra_scores as $cid => $rating) {
            $all_scores[(int) $cid] = (float) $rating;
        }
        foreach ($beh_scores as $cid => $rating) {
            $all_scores[(int) $cid] = (float) $rating;
        }

        // Apply score alterations
        $altered_details = [];
        foreach ($all_scores as $criterion_id => $new_rating) {
            $original_rating = (float) ($scores[$criterion_id]['score_value'] ?? 0);
            if (abs($new_rating - $original_rating) > 0.01) {
                $reviewer_altered  = true;
                $crit_name = $scores[$criterion_id]['criterion_name'] ?? 'Criterion #' . $criterion_id;
                $altered_details[] = "$crit_name (Self-Rating: " . number_format($original_rating, 2)
                    . " → Adjusted: " . number_format($new_rating, 2) . ")";

                $upd = $conn->prepare("
                    UPDATE evaluation_scores
                    SET score_value = ?, weighted_score = ?,
                        supervisor_override_score = NULL, supervisor_override_by = NULL, supervisor_override_at = NULL,
                        manager_override_score    = NULL, manager_override_by    = NULL, manager_override_at    = NULL
                    WHERE evaluation_id = ? AND criterion_id = ?
                ");
                $weight      = (float) ($scores[$criterion_id]['weight']  ?? 0);
                $is_behavior = ($scores[$criterion_id]['section'] ?? '') === 'Behavior';
                $weighted    = $is_behavior ? $new_rating : round(($weight / 100) * $new_rating, 2);
                $upd->bind_param("ddii", $new_rating, $weighted, $evaluation_id, $criterion_id);
                $upd->execute();
                $upd->close();
            }
        }

        // Recalculate scores
        $new_total_score      = (float) $evaluation['total_score'];
        $new_kra_subtotal     = (float) $evaluation['kra_subtotal'];
        $new_behavior_average = (float) $evaluation['behavior_average'];
        $new_performance_level = $evaluation['performance_level'];
        $new_reviewer_rating  = null;

        if ($reviewer_altered) {
            $kra_sub = 0;
            foreach ($criteria_kra as $c) {
                $cid    = (int) $c['criterion_id'];
                $rating = (float) ($kra_scores[$cid] ?? $c['score_value'] ?? 0);
                $kra_sub += round(($c['weight'] / 100) * $rating, 4);
            }
            $new_kra_subtotal = round($kra_sub, 2);

            $beh_total = 0; $beh_cnt = 0;
            foreach ($criteria_behavior as $c) {
                $cid    = (int) $c['criterion_id'];
                $rating = (float) ($beh_scores[$cid] ?? $c['score_value'] ?? 0);
                $beh_total += $rating;
                $beh_cnt++;
            }
            $new_behavior_average = $beh_cnt > 0 ? round($beh_total / $beh_cnt, 2) : 0;

            $kra_w = (float) ($evaluation['kra_weight']      ?? 80);
            $beh_w = (float) ($evaluation['behavior_weight'] ?? 20);
            $new_total_score       = calculateEvalTotal($new_kra_subtotal, $new_behavior_average, $kra_w, $beh_w);
            $new_performance_level = getPerformanceLevel($new_total_score);
            $new_reviewer_rating   = $new_total_score;
        }

        // Determine next status
        $subject_hr_role = getEmployeeHRRole($conn, (int) $evaluation['employee_id']);

        if ($reviewer_hr_role === 'HR Supervisor') {
            // HR Supervisor confirmed
            if ($subject_hr_role === 'HR Manager') {
                // HR Manager's eval → HR Supervisor is final approver
                $next_status = 'Approved';
            } else {
                // HR Staff (or Main-Office HR without role tag) → forward to HR Manager
                $next_status = 'Pending Manager';
            }
        } else {
            // HR Manager confirmed → always final
            $next_status = 'Approved';
        }

        $altered_int = $reviewer_altered ? 1 : 0;

        if ($reviewer_hr_role === 'HR Supervisor') {
            // Update using supervisor_confirmed_by fields
            if ($next_status === 'Approved') {
                $upd = $conn->prepare("
                    UPDATE evaluations
                    SET status = ?,
                        supervisor_confirmed_by      = ?,
                        dept_supervisor_confirmed_by = ?,
                        supervisor_confirmed_date    = NOW(),
                        dept_supervisor_confirmed_date = NOW(),
                        supervisor_altered_scores    = ?,
                        supervisor_comments          = ?,
                        supervisor_rating            = ?,
                        sent_to_hr_date              = NOW(),
                        sent_to_hr_by                = ?,
                        total_score                  = ?,
                        kra_subtotal                 = ?,
                        behavior_average             = ?,
                        performance_level            = ?,
                        approved_by                  = ?,
                        approved_date                = NOW()
                    WHERE evaluation_id = ?
                ");
                $upd->bind_param("siiisiddddsii",
                    $next_status, $user_id, $user_id, $altered_int,
                    $reviewer_comments, $new_reviewer_rating, $user_id,
                    $new_total_score, $new_kra_subtotal, $new_behavior_average, $new_performance_level,
                    $user_id, $evaluation_id
                );
            } else {
                $upd = $conn->prepare("
                    UPDATE evaluations
                    SET status = ?,
                        supervisor_confirmed_by      = ?,
                        dept_supervisor_confirmed_by = ?,
                        supervisor_confirmed_date    = NOW(),
                        dept_supervisor_confirmed_date = NOW(),
                        supervisor_altered_scores    = ?,
                        supervisor_comments          = ?,
                        supervisor_rating            = ?,
                        sent_to_hr_date              = NOW(),
                        sent_to_hr_by                = ?,
                        total_score                  = ?,
                        kra_subtotal                 = ?,
                        behavior_average             = ?,
                        performance_level            = ?
                    WHERE evaluation_id = ?
                ");
                $upd->bind_param("siiisiddddsi",
                    $next_status, $user_id, $user_id, $altered_int,
                    $reviewer_comments, $new_reviewer_rating, $user_id,
                    $new_total_score, $new_kra_subtotal, $new_behavior_average, $new_performance_level,
                    $evaluation_id
                );
            }
        } else {
            // HR Manager — always Approved
            $upd = $conn->prepare("
                UPDATE evaluations
                SET status        = 'Approved',
                    manager_comments = ?,
                    manager_rating   = ?,
                    approved_by      = ?,
                    approved_date    = NOW(),
                    total_score      = ?,
                    kra_subtotal     = ?,
                    behavior_average = ?,
                    performance_level = ?
                WHERE evaluation_id = ?
            ");
            $upd->bind_param("sdiiddsi",
                $reviewer_comments, $new_reviewer_rating, $user_id,
                $new_total_score, $new_kra_subtotal, $new_behavior_average, $new_performance_level,
                $evaluation_id
            );
        }
        $upd->execute();
        $upd->close();

        $emp_name      = $evaluation['first_name'] . ' ' . $evaluation['last_name'];
        $reviewer_name = $_SESSION['full_name'] ?? $reviewer_hr_role;

        // Notifications
        if ($next_status === 'Pending Manager') {
            // Notify HR Manager (Employee Portal account, same employee_id lookup)
            $hr_mgr_stmt = $conn->prepare("
                SELECT u.user_id FROM users u
                JOIN employees emp ON u.employee_id = emp.employee_id
                WHERE u.role = 'Employee'
                  AND u.is_active = 1
                  AND emp.employee_id IN (
                      SELECT employee_id FROM users
                      WHERE role = 'HR Manager' AND is_active = 1
                  )
            ");
            $hr_mgr_stmt->execute();
            $hr_mgr_rows = $hr_mgr_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $hr_mgr_stmt->close();

            // Fallback: also notify HR Manager admin accounts
            $hr_mgr_admin_stmt = $conn->prepare("SELECT user_id FROM users WHERE role = 'HR Manager' AND is_active = 1");
            $hr_mgr_admin_stmt->execute();
            $hr_mgr_admin_rows = $hr_mgr_admin_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $hr_mgr_admin_stmt->close();

            $notify_ids = array_unique(array_column(array_merge($hr_mgr_rows, $hr_mgr_admin_rows), 'user_id'));
            foreach ($notify_ids as $nid) {
                createNotification($conn, (int) $nid,
                    'HR Self-Rating Pending Your Review',
                    $reviewer_name . ' confirmed self-rating for ' . $emp_name . '. Awaiting your final review.',
                    BASE_URL . '/employee/hr-rating-review.php?evaluation_id=' . $evaluation_id
                );
            }
        } elseif ($next_status === 'Approved') {
            // Notify employee (Employee Portal account)
            $emp_user = $conn->query("SELECT user_id FROM users WHERE employee_id = " . (int) $evaluation['employee_id'] . " AND role = 'Employee' LIMIT 1")->fetch_assoc();
            if ($emp_user) {
                createNotification($conn, (int) $emp_user['user_id'],
                    'Self-Rating Approved',
                    'Your self-rating has been approved by ' . $reviewer_name . '.',
                    BASE_URL . '/employee/self-rating.php?view=' . $evaluation_id
                );
            }
        }

        // Always notify the subject employee (Employee Portal)
        if ($next_status !== 'Approved') {
            $emp_user2 = $conn->query("SELECT user_id FROM users WHERE employee_id = " . (int) $evaluation['employee_id'] . " AND role = 'Employee' LIMIT 1")->fetch_assoc();
            if ($emp_user2) {
                createNotification($conn, (int) $emp_user2['user_id'],
                    'Self-Rating Reviewed',
                    $reviewer_name . ' has reviewed your self-rating and forwarded it for further approval.',
                    BASE_URL . '/employee/self-rating.php?view=' . $evaluation_id
                );
            }
        }

        $audit_msg = $reviewer_hr_role . ' confirmed HR self-rating. New status: ' . $next_status;
        if ($reviewer_altered && !empty($altered_details)) {
            $audit_msg .= ". Adjustments: " . implode("; ", $altered_details);
        }
        logAudit($conn, $user_id, 'UPDATE', 'Evaluation', $evaluation_id, $audit_msg);

        $success_msg = $next_status === 'Approved'
            ? 'Self-rating reviewed and approved successfully.'
            : 'Self-rating confirmed and forwarded to HR Manager for final approval.';
        redirectWith(BASE_URL . '/employee/hr-rating-review.php?evaluation_id=' . $evaluation_id, 'success', $success_msg);

    } elseif ($action === 'return') {
        if (empty($reviewer_comments)) {
            redirectWith(BASE_URL . '/employee/hr-rating-review.php?evaluation_id=' . $evaluation_id, 'danger', 'Please provide a reason for returning the evaluation.');
        }

        $upd = $conn->prepare("
            UPDATE evaluations
            SET status = 'Returned',
                supervisor_comments  = ?,
                supervisor_confirmed_by      = NULL,
                dept_supervisor_confirmed_by = NULL,
                supervisor_confirmed_date    = NULL,
                dept_supervisor_confirmed_date = NULL
            WHERE evaluation_id = ?
        ");
        $upd->bind_param("si", $reviewer_comments, $evaluation_id);
        $upd->execute();
        $upd->close();

        $emp_user = $conn->query("SELECT user_id FROM users WHERE employee_id = " . (int) $evaluation['employee_id'] . " AND role = 'Employee' LIMIT 1")->fetch_assoc();
        $emp_name = $evaluation['first_name'] . ' ' . $evaluation['last_name'];
        if ($emp_user) {
            createNotification($conn, (int) $emp_user['user_id'],
                'Self-Rating Returned for Revision',
                ($_SESSION['full_name'] ?? $reviewer_hr_role) . ' returned your self-rating for revision. Please review the comments and resubmit.',
                BASE_URL . '/employee/self-rating.php'
            );
        }

        logAudit($conn, $user_id, 'UPDATE', 'Evaluation', $evaluation_id, $reviewer_hr_role . ' returned HR self-rating to employee. Status: Returned.');
        redirectWith(BASE_URL . '/employee/hr-rating-review.php', 'warning', 'Evaluation returned to employee for revision.');
    }
}

// ── Fetch pending queue for this reviewer ────────────────────────────────────
$pending_reviews  = [];
$review_history   = [];

$pending_stmt = $conn->prepare("
    SELECT ev.evaluation_id, ev.status, ev.total_score, ev.performance_level,
           ev.submitted_date, ev.evaluation_period_start, ev.evaluation_period_end,
           emp.first_name, emp.last_name, emp.job_title, emp.employee_code,
           t.template_name,
           u_emp.role AS subject_hr_role
    FROM evaluations ev
    JOIN employees emp ON ev.employee_id = emp.employee_id
    JOIN users     u_emp ON u_emp.employee_id = emp.employee_id AND u_emp.role IN ('HR Staff','HR Supervisor','HR Manager') AND u_emp.is_active = 1
    LEFT JOIN evaluation_templates t ON ev.template_id = t.template_id
    WHERE ev.status = ?
      AND ev.deleted_at IS NULL
      AND emp.is_active = 1
      AND emp.deleted_at IS NULL
      AND emp.employee_id <> ?
    ORDER BY ev.submitted_date ASC
");
$pending_stmt->bind_param("si", $reviewer_pending_status, $reviewer_emp_id);
$pending_stmt->execute();
$pending_reviews = $pending_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$pending_stmt->close();

// Review history (evaluations this reviewer has already acted on)
if ($reviewer_hr_role === 'HR Supervisor') {
    $hist_stmt = $conn->prepare("
        SELECT ev.evaluation_id, ev.status, ev.total_score, ev.performance_level,
               ev.supervisor_confirmed_date,
               emp.first_name, emp.last_name, emp.job_title,
               t.template_name
        FROM evaluations ev
        JOIN employees emp ON ev.employee_id = emp.employee_id
        LEFT JOIN evaluation_templates t ON ev.template_id = t.template_id
        WHERE (ev.supervisor_confirmed_by = ? OR ev.dept_supervisor_confirmed_by = ?)
          AND ev.deleted_at IS NULL
        ORDER BY ev.supervisor_confirmed_date DESC
        LIMIT 50
    ");
    $hist_stmt->bind_param("ii", $user_id, $user_id);
} else {
    $hist_stmt = $conn->prepare("
        SELECT ev.evaluation_id, ev.status, ev.total_score, ev.performance_level,
               ev.approved_date AS supervisor_confirmed_date,
               emp.first_name, emp.last_name, emp.job_title,
               t.template_name
        FROM evaluations ev
        JOIN employees emp ON ev.employee_id = emp.employee_id
        LEFT JOIN evaluation_templates t ON ev.template_id = t.template_id
        WHERE ev.approved_by = ?
          AND ev.deleted_at IS NULL
        ORDER BY ev.approved_date DESC
        LIMIT 50
    ");
    $hist_stmt->bind_param("i", $user_id);
}
$hist_stmt->execute();
$review_history = $hist_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$hist_stmt->close();

// Pre-compute score summary for the detail view
$orig_kra_total = 0;
foreach ($criteria_kra as $c) {
    $orig_kra_total += round(($c['weight'] / 100) * (float) $c['score_value'], 4);
}
$orig_kra_total = round($orig_kra_total, 2);
$orig_beh_avg = 0;
if (!empty($criteria_behavior)) {
    $orig_beh_avg = round(array_sum(array_column($criteria_behavior, 'score_value')) / count($criteria_behavior), 2);
}
$kra_weight_display = (float) ($evaluation['kra_weight'] ?? 80);
$beh_weight_display = (float) ($evaluation['behavior_weight'] ?? 20);

require_once '../includes/header.php';
?>

<div class="page-hero fadeup">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-0 gap-4">
        <div>
            <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.55);">Employee Portal</div>
            <h2 class="text-white fw-bold mb-1 mt-1">HR Rating Review</h2>
            <p class="mb-0 text-white-50 small">
                <i class="fas fa-user-shield me-1"></i>
                <?php if ($reviewer_hr_role === 'HR Supervisor'): ?>
                    Review self-ratings from HR Staff and HR Manager
                <?php else: ?>
                    Final approval of HR Supervisor and HR Staff self-ratings
                <?php endif; ?>
            </p>
        </div>
        <div class="d-none d-md-block text-end">
            <a href="<?php echo BASE_URL; ?>/employee/dashboard.php" class="btn btn-outline-light btn-sm rounded-pill px-3 mb-2">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>
</div>

<div class="d-md-none d-flex justify-content-between align-items-center mt-3 mb-4 flex-wrap gap-3 fadeup" style="animation-delay:.1s;">
    <a href="<?php echo BASE_URL; ?>/employee/dashboard.php" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
        <i class="fas fa-arrow-left me-2"></i>Back to My Dashboard
    </a>
</div>

<?php if (!$evaluation): ?>
<!-- ══════════════════════════════════════════════════════════════════════════
     QUEUE LIST VIEW
══════════════════════════════════════════════════════════════════════════════ -->

    <div class="content-card fadeup-1">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">
                <i class="fas fa-inbox me-2"></i>
                <?php echo $reviewer_hr_role === 'HR Supervisor' ? 'Pending Review' : 'Pending Final Approval'; ?>
            </h5>
            <?php if (!empty($pending_reviews)): ?>
                <span class="badge bg-warning text-dark"><?php echo count($pending_reviews); ?> pending</span>
            <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <?php if (empty($pending_reviews)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-check-circle fa-3x mb-3"></i>
                    <p class="mb-0">No pending HR self-ratings to review.</p>
                    <div class="small mt-1">All caught up!</div>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Employee</th>
                                <th>HR Role</th>
                                <th>Template</th>
                                <th>Period</th>
                                <th class="text-center">Score</th>
                                <th class="text-center">Submitted</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_reviews as $p): ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-semibold"><?php echo e($p['last_name'] . ', ' . $p['first_name']); ?></div>
                                        <div class="small text-muted"><?php echo e($p['job_title']); ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-dark"><?php echo e($p['subject_hr_role']); ?></span>
                                    </td>
                                    <td><?php echo e($p['template_name'] ?? 'N/A'); ?></td>
                                    <td class="small text-muted">
                                        <?php echo formatDate($p['evaluation_period_start']); ?> –<br>
                                        <?php echo formatDate($p['evaluation_period_end']); ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="fw-bold text-primary"><?php echo number_format((float) $p['total_score'], 2); ?></div>
                                        <span class="badge bg-light text-dark"><?php echo e($p['performance_level'] ?? '—'); ?></span>
                                    </td>
                                    <td class="text-center small"><?php echo formatDateTime($p['submitted_date']); ?></td>
                                    <td class="text-center">
                                        <a href="?evaluation_id=<?php echo (int) $p['evaluation_id']; ?>" class="btn btn-sm btn-primary">
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

    <!-- Review History -->
    <div class="content-card fadeup-2 mt-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Review History</h5>
            <span class="badge bg-secondary"><?php echo count($review_history); ?> record<?php echo count($review_history) !== 1 ? 's' : ''; ?></span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($review_history)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-inbox fa-3x mb-3"></i>
                    <p class="mb-0">No review history yet.</p>
                </div>
            <?php else: ?>
                <?php
                $status_badges = [
                    'Pending Manager'          => 'bg-warning text-dark',
                    'Pending Supervisor'       => 'bg-warning text-dark',
                    'Pending HR Consolidation' => 'bg-info text-dark',
                    'Approved'                 => 'bg-success',
                    'Rejected'                 => 'bg-danger',
                    'Returned'                 => 'bg-secondary',
                ];
                ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Employee</th>
                                <th>Template</th>
                                <th class="text-center">Final Score</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Reviewed On</th>
                                <th class="text-center">View</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($review_history as $h): ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-semibold"><?php echo e($h['last_name'] . ', ' . $h['first_name']); ?></div>
                                        <div class="small text-muted"><?php echo e($h['job_title']); ?></div>
                                    </td>
                                    <td><?php echo e($h['template_name'] ?? 'N/A'); ?></td>
                                    <td class="text-center">
                                        <span class="fw-bold text-primary"><?php echo number_format((float) $h['total_score'], 2); ?></span>
                                        <div><span class="badge bg-light text-dark"><?php echo e($h['performance_level'] ?? '—'); ?></span></div>
                                    </td>
                                    <td class="text-center">
                                        <?php $sc = $status_badges[$h['status']] ?? 'bg-secondary'; ?>
                                        <span class="badge <?php echo $sc; ?>"><?php echo e($h['status']); ?></span>
                                    </td>
                                    <td class="text-center small"><?php echo formatDateTime($h['supervisor_confirmed_date']); ?></td>
                                    <td class="text-center">
                                        <a href="?evaluation_id=<?php echo (int) $h['evaluation_id']; ?>" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-eye me-1"></i>View
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
<!-- ══════════════════════════════════════════════════════════════════════════
     EVALUATION DETAIL / REVIEW FORM
══════════════════════════════════════════════════════════════════════════════ -->

    <?php
    $subject_hr_role_label = getEmployeeHRRole($conn, (int) $evaluation['employee_id']) ?? 'HR Employee';
    $status_badge_class = [
        'Pending Supervisor'       => 'bg-warning text-dark',
        'Pending Manager'          => 'bg-info text-dark',
        'Pending HR Consolidation' => 'bg-info text-dark',
        'Approved'                 => 'bg-success',
        'Rejected'                 => 'bg-danger',
        'Returned'                 => 'bg-secondary',
    ][$evaluation['status']] ?? 'bg-secondary';
    ?>

    <div class="d-flex flex-wrap gap-2 mb-3 fadeup-1">
        <a href="<?php echo BASE_URL; ?>/employee/hr-rating-review.php" class="btn btn-sm btn-outline-secondary rounded-pill">
            <i class="fas fa-arrow-left me-2"></i>Back to Queue
        </a>
    </div>

    <!-- Employee & Status Summary -->
    <div class="content-card fadeup-1 mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <div class="col-md-6">
                    <div class="small text-muted text-uppercase fw-semibold mb-1">Evaluatee</div>
                    <div class="fw-bold fs-5"><?php echo e($evaluation['last_name'] . ', ' . $evaluation['first_name']); ?></div>
                    <div class="text-muted small"><?php echo e($evaluation['job_title']); ?> &bull; <?php echo e($evaluation['department_name']); ?> &bull; <?php echo e($evaluation['branch_name']); ?></div>
                    <div class="mt-1"><span class="badge bg-info text-dark"><?php echo e($subject_hr_role_label); ?></span></div>
                </div>
                <div class="col-md-3">
                    <div class="small text-muted text-uppercase fw-semibold mb-1">Template</div>
                    <div class="fw-semibold"><?php echo e($evaluation['template_name']); ?></div>
                    <div class="small text-muted">
                        <?php echo formatDate($evaluation['evaluation_period_start']); ?> –
                        <?php echo formatDate($evaluation['evaluation_period_end']); ?>
                    </div>
                </div>
                <div class="col-md-3 text-md-end">
                    <div class="small text-muted text-uppercase fw-semibold mb-1">Status</div>
                    <span class="badge <?php echo $status_badge_class; ?> px-3 py-2 fs-6"><?php echo e($evaluation['status']); ?></span>
                    <?php if ($is_readonly): ?>
                        <div class="small text-muted mt-1"><i class="fas fa-lock me-1"></i>View only</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Score summary row -->
            <div class="row g-3 mt-2 pt-2 border-top">
                <div class="col-6 col-md-3">
                    <div class="small text-muted">Self-Rating Score</div>
                    <div class="fw-bold text-primary fs-5"><?php echo number_format((float)$evaluation['total_score'], 2); ?></div>
                    <div class="small text-muted"><?php echo e($evaluation['performance_level']); ?></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="small text-muted">KRA Subtotal</div>
                    <div class="fw-semibold"><?php echo number_format($orig_kra_total, 2); ?></div>
                    <div class="small text-muted"><?php echo $kra_weight_display; ?>% weight</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="small text-muted">Behavior Average</div>
                    <div class="fw-semibold"><?php echo number_format($orig_beh_avg, 2); ?></div>
                    <div class="small text-muted"><?php echo $beh_weight_display; ?>% weight</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="small text-muted">Submitted</div>
                    <div class="fw-semibold small"><?php echo formatDateTime($evaluation['submitted_date']); ?></div>
                </div>
            </div>

            <?php if (!empty($evaluation['staff_comments'])): ?>
                <div class="mt-3 pt-2 border-top">
                    <div class="small text-muted fw-semibold mb-1">Employee's Comments</div>
                    <p class="mb-0 text-muted small fst-italic">"<?php echo e($evaluation['staff_comments']); ?>"</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Review Form -->
    <form method="POST" action="?evaluation_id=<?php echo $evaluation_id; ?>" id="hr-review-form">
        <input type="hidden" name="evaluation_id" value="<?php echo $evaluation_id; ?>">

        <!-- KRA Criteria -->
        <?php if (!empty($criteria_kra)): ?>
        <div class="content-card fadeup-2 mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bullseye me-2"></i>Key Result Areas (KRA)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="width:40%">Criterion</th>
                                <th class="text-center" style="width:12%">Weight</th>
                                <th class="text-center" style="width:15%">Self-Rating</th>
                                <th class="text-center" style="width:15%"><?php echo $reviewer_hr_role; ?> Rating</th>
                                <th class="text-center" style="width:18%">Weighted Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($criteria_kra as $c):
                                $cid       = (int) $c['criterion_id'];
                                $orig      = (float) $c['score_value'];
                                $sup_ov    = isset($c['supervisor_override_score']) ? (float) $c['supervisor_override_score'] : null;
                                $cur_score = $sup_ov !== null ? $sup_ov : $orig;
                                $weighted  = round(($c['weight'] / 100) * $cur_score, 2);
                            ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-semibold"><?php echo e($c['criterion_name']); ?></div>
                                        <?php if (!empty($c['description'])): ?>
                                            <div class="small text-muted"><?php echo e($c['description']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center"><?php echo number_format((float) $c['weight'], 1); ?>%</td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border"><?php echo number_format($orig, 2); ?></span>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!$is_readonly): ?>
                                            <input type="number" name="kra_scores[<?php echo $cid; ?>]"
                                                   value="<?php echo number_format($cur_score, 2); ?>"
                                                   min="0" max="4" step="0.01"
                                                   class="form-control form-control-sm text-center score-input"
                                                   style="width:80px;margin:0 auto;"
                                                   data-criterion-id="<?php echo $cid; ?>"
                                                   data-section="kra"
                                                   data-weight="<?php echo (float) $c['weight']; ?>">
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark border"><?php echo number_format($cur_score, 2); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center fw-semibold" id="weighted-<?php echo $cid; ?>">
                                        <?php echo number_format($weighted, 2); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Behavior Criteria -->
        <?php if (!empty($criteria_behavior)): ?>
        <div class="content-card fadeup-3 mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-heart me-2"></i>Behavioral Competencies</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="width:55%">Criterion</th>
                                <th class="text-center" style="width:15%">Self-Rating</th>
                                <th class="text-center" style="width:15%"><?php echo $reviewer_hr_role; ?> Rating</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($criteria_behavior as $c):
                                $cid      = (int) $c['criterion_id'];
                                $orig     = (float) $c['score_value'];
                                $sup_ov   = isset($c['supervisor_override_score']) ? (float) $c['supervisor_override_score'] : null;
                                $cur_score = $sup_ov !== null ? $sup_ov : $orig;
                            ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-semibold"><?php echo e($c['criterion_name']); ?></div>
                                        <?php if (!empty($c['description'])): ?>
                                            <div class="small text-muted"><?php echo e($c['description']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border"><?php echo number_format($orig, 2); ?></span>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!$is_readonly): ?>
                                            <input type="number" name="beh_scores[<?php echo $cid; ?>]"
                                                   value="<?php echo number_format($cur_score, 2); ?>"
                                                   min="0" max="4" step="0.01"
                                                   class="form-control form-control-sm text-center score-input"
                                                   style="width:80px;margin:0 auto;"
                                                   data-criterion-id="<?php echo $cid; ?>"
                                                   data-section="behavior">
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark border"><?php echo number_format($cur_score, 2); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Comments & Actions -->
        <?php if (!$is_readonly): ?>
        <div class="content-card fadeup-4 mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-comment-dots me-2"></i>Review Comments</h5>
            </div>
            <div class="card-body">
                <label for="reviewer_comments" class="form-label">
                    Comments / Remarks
                    <span class="text-muted small">(required when returning; optional when confirming)</span>
                </label>
                <textarea name="reviewer_comments" id="reviewer_comments" rows="4"
                          class="form-control" placeholder="Add your review notes here…"></textarea>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-3 fadeup-5 mb-5">
            <button type="submit" name="confirm_action" value="confirm" class="btn btn-success rounded-pill px-4">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $reviewer_hr_role === 'HR Manager' ? 'Approve Evaluation' : 'Confirm & Forward to HR Manager'; ?>
            </button>
            <button type="submit" name="confirm_action" value="return"
                    class="btn btn-outline-warning rounded-pill px-4"
                    onclick="return confirm('Return this evaluation to the employee for revision?');">
                <i class="fas fa-undo me-2"></i>Return to Employee
            </button>
            <a href="<?php echo BASE_URL; ?>/employee/hr-rating-review.php" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fas fa-times me-2"></i>Cancel
            </a>
        </div>
        <?php else: ?>
        <!-- Read-only footer showing what happened -->
        <div class="content-card fadeup-4 mb-5">
            <div class="card-body">
                <?php if (!empty($evaluation['supervisor_comments'])): ?>
                    <div class="mb-3">
                        <div class="small text-muted fw-semibold mb-1">HR Supervisor Comments</div>
                        <p class="mb-0"><?php echo e($evaluation['supervisor_comments']); ?></p>
                    </div>
                <?php endif; ?>
                <?php if (!empty($evaluation['manager_comments'])): ?>
                    <div>
                        <div class="small text-muted fw-semibold mb-1">HR Manager Comments</div>
                        <p class="mb-0"><?php echo e($evaluation['manager_comments']); ?></p>
                    </div>
                <?php endif; ?>
                <div class="mt-3">
                    <a href="<?php echo BASE_URL; ?>/employee/hr-rating-review.php" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="fas fa-arrow-left me-2"></i>Back to Queue
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </form>

    <!-- Audit History -->
    <?php if (!empty($audit_history)): ?>
    <div class="content-card fadeup-5 mt-2 mb-5">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i>Audit Trail</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Action</th>
                            <th>By</th>
                            <th>Role</th>
                            <th>When</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($audit_history as $log): ?>
                            <tr>
                                <td class="ps-3"><span class="badge bg-light text-dark"><?php echo e($log['action']); ?></span></td>
                                <td><?php echo e($log['full_name'] ?? '—'); ?></td>
                                <td class="small text-muted"><?php echo e($log['role'] ?? '—'); ?></td>
                                <td class="small text-muted"><?php echo formatDateTime($log['timestamp']); ?></td>
                                <td class="small text-muted"><?php echo e(mb_strimwidth($log['details'] ?? '', 0, 120, '…')); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

<?php endif; ?>

<?php if (!$is_readonly && $evaluation): ?>
<script>
/* Live score recalculation -------------------------------------------------- */
(function () {
    const kraWeight  = <?php echo $kra_weight_display; ?> / 100;
    const behavWeight = <?php echo $beh_weight_display; ?> / 100;

    function recalcKRA() {
        let kraSubtotal = 0;
        document.querySelectorAll('input[data-section="kra"]').forEach(function (inp) {
            const cid    = inp.dataset.criterionId;
            const weight = parseFloat(inp.dataset.weight) / 100;
            const score  = Math.min(4, Math.max(0, parseFloat(inp.value) || 0));
            const w      = Math.round(weight * score * 100) / 100;
            const el     = document.getElementById('weighted-' + cid);
            if (el) el.textContent = w.toFixed(2);
            kraSubtotal += w;
        });
        return Math.round(kraSubtotal * 100) / 100;
    }

    function recalcBeh() {
        let total = 0, count = 0;
        document.querySelectorAll('input[data-section="behavior"]').forEach(function (inp) {
            total += Math.min(4, Math.max(0, parseFloat(inp.value) || 0));
            count++;
        });
        return count > 0 ? Math.round((total / count) * 100) / 100 : 0;
    }

    document.querySelectorAll('.score-input').forEach(function (inp) {
        inp.addEventListener('input', function () {
            const kraSubtotal = recalcKRA();
            const behAvg      = recalcBeh();
            // Could show a live total here if desired
        });
    });
}());
</script>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
