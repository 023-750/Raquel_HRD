<?php
/**
 * Employee Portal - Department Manager Review
 * Allows Department Managers to review, alter scores, endorse, or return subordinate evaluations.
 */
$page_title = 'Department Manager Review';
require_once '../includes/session-check.php';
checkRole(['Employee']);
require_once '../includes/functions.php';

$user_id = (int)($_SESSION['user_id'] ?? 0);
$manager_employee_id = (int)($_SESSION['employee_id'] ?? 0);

// Check if user is a department manager
$is_dept_manager = isDeptManagerRole($conn, $manager_employee_id);

// Ensure 360-degree columns exist
ensureEvaluationWorkflowSchema($conn);
ensure360DegreeEvaluationColumns($conn);

// Get evaluation ID from URL
$evaluation_id = (int)($_GET['evaluation_id'] ?? 0);

$evaluation = null;
$scores = [];
$criteria_kra = [];
$criteria_behavior = [];
$is_readonly = false;

if ($evaluation_id > 0) {
    $eval_stmt = $conn->prepare("
        SELECT e.*, emp.employee_id, emp.employee_code, emp.first_name, emp.last_name, emp.job_title,
               emp.department_id, emp.branch_id, emp.reports_to,
               d.department_name, b.branch_name,
               t.template_name, t.kra_weight, t.behavior_weight,
               su.full_name AS supervisor_name
        FROM evaluations e
        JOIN employees emp ON e.employee_id = emp.employee_id
        LEFT JOIN departments d ON emp.department_id = d.department_id
        LEFT JOIN branches b ON emp.branch_id = b.branch_id
        LEFT JOIN evaluation_templates t ON e.template_id = t.template_id
        LEFT JOIN users su ON e.dept_supervisor_confirmed_by = su.user_id
        WHERE e.evaluation_id = ? AND e.status IN ('Pending Dept Manager', 'Pending HR Consolidation', 'Pending Manager', 'Supervisor Confirmed', 'Approved')
        LIMIT 1
    ");
    $eval_stmt->bind_param("i", $evaluation_id);
    $eval_stmt->execute();
    $evaluation = $eval_stmt->get_result()->fetch_assoc();
    $eval_stmt->close();

    if ($evaluation) {
        $is_readonly = ($evaluation['status'] !== 'Pending Dept Manager');

        // Authorize: Check if this manager is the employee's department manager
        $is_authorized = isDeptManagerOfEmployee($conn, $user_id, (int)$evaluation['employee_id']);
        if (!$is_authorized) {
            redirectWith(BASE_URL . '/employee/dashboard.php', 'danger', 'You are not authorized to review this evaluation.');
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

        // Fetch audit history for this evaluation
        $audit_stmt = $conn->prepare("
            SELECT al.*, u.full_name, u.role, e.job_title
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.user_id
            LEFT JOIN employees e ON u.employee_id = e.employee_id
            WHERE al.entity_type = 'Evaluation' AND al.entity_id = ?
            ORDER BY al.timestamp DESC, al.log_id DESC
        ");
        $audit_stmt->bind_param("i", $evaluation_id);
        $audit_stmt->execute();
        $audit_history = $audit_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $audit_stmt->close();
    }
}

$readonly_attr = $is_readonly ? 'readonly' : '';
$disabled_attr = $is_readonly ? 'disabled' : '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $evaluation && $is_dept_manager && $is_readonly) {
    redirectWith(BASE_URL . '/employee/dept-manager-review.php?evaluation_id=' . $evaluation_id, 'warning', 'This evaluation is no longer pending department manager review and is view-only.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $evaluation && $is_dept_manager && !$is_readonly) {

    // ── Race-condition guard: re-check current DB status before processing ────
    $recheck_stmt = $conn->prepare("SELECT status FROM evaluations WHERE evaluation_id = ? LIMIT 1");
    $recheck_stmt->bind_param("i", $evaluation_id);
    $recheck_stmt->execute();
    $recheck_row = $recheck_stmt->get_result()->fetch_assoc();
    $recheck_stmt->close();
    if (!$recheck_row || $recheck_row['status'] !== 'Pending Dept Manager') {
        redirectWith(BASE_URL . '/employee/dept-manager-review.php?evaluation_id=' . $evaluation_id, 'warning',
            'Another department manager has already acted on this evaluation. It is now view-only.');
    }
    // ─────────────────────────────────────────────────────────────────────────

    $action = $_POST['confirm_action'] ?? '';
    $dept_manager_comments = trim($_POST['dept_manager_comments'] ?? '');

    if ($action === 'endorse') {
        $kra_scores = $_POST['kra_scores'] ?? [];
        $beh_scores = $_POST['beh_scores'] ?? [];
        $manager_altered = false;

        // Combine scores manually to preserve criterion_id numeric keys (array_merge reindexes numeric keys)
        $all_scores = [];
        foreach ($kra_scores as $cid => $rating) {
            $val = (float)$rating;
            if ($val > 4.0) {
                $val = 4.0;
            }
            $all_scores[(int)$cid] = $val;
        }
        foreach ($beh_scores as $cid => $rating) {
            $val = (float)$rating;
            if ($val > 4.0) {
                $val = 4.0;
            }
            $all_scores[(int)$cid] = $val;
        }

        // Apply any score changes via dept_manager_override_* columns (preserves original self-rating)
        $altered_details = [];
        $user_id_for_override = $user_id; // the logged-in dept manager's user_id
        foreach ($all_scores as $criterion_id => $new_rating) {
            $original_rating = (float)($scores[$criterion_id]['score_value'] ?? 0);

            if (abs($new_rating - $original_rating) > 0.01) {
                $manager_altered = true;
                $criterion_name = $scores[$criterion_id]['criterion_name'] ?? 'Criterion #' . $criterion_id;
                $altered_details[] = "$criterion_name (Self-Rating: " . number_format($original_rating, 2) . " -> Adjusted: " . number_format($new_rating, 2) . ")";

                $update_override = $conn->prepare("
                    UPDATE evaluation_scores
                    SET dept_manager_override_score = ?,
                        dept_manager_override_by    = ?,
                        dept_manager_override_at    = NOW()
                    WHERE evaluation_id = ? AND criterion_id = ?
                ");
                $update_override->bind_param("diii", $new_rating, $user_id_for_override, $evaluation_id, $criterion_id);
                $update_override->execute();
                $update_override->close();
            }
        }

        // Recalculate totals using the centralized function (honours the full override hierarchy)
        recalculateEvaluationScores($conn, $evaluation_id);

        // Fetch updated totals to store on the evaluations row
        $updated_eval_q = $conn->prepare("SELECT total_score, kra_subtotal, behavior_average, performance_level FROM evaluations WHERE evaluation_id = ? LIMIT 1");
        $updated_eval_q->bind_param("i", $evaluation_id);
        $updated_eval_q->execute();
        $updated_eval_row = $updated_eval_q->get_result()->fetch_assoc();
        $updated_eval_q->close();

        $new_total_score      = (float)($updated_eval_row['total_score']      ?? $evaluation['total_score']);
        $new_kra_subtotal     = (float)($updated_eval_row['kra_subtotal']     ?? $evaluation['kra_subtotal']);
        $new_behavior_average = (float)($updated_eval_row['behavior_average'] ?? $evaluation['behavior_average']);
        $new_performance_level = $updated_eval_row['performance_level']      ?? $evaluation['performance_level'];


        // Update evaluation
        $update = $conn->prepare("
            UPDATE evaluations
            SET status = 'Pending HR Consolidation',
                dept_manager_endorsed_by = ?,
                dept_manager_endorsed_date = NOW(),
                dept_manager_comments = ?,
                sent_to_hr_date = NOW(),
                sent_to_hr_by = ?,
                total_score = ?,
                kra_subtotal = ?,
                behavior_average = ?,
                performance_level = ?
            WHERE evaluation_id = ?
        ");
        $update->bind_param(
            "isidddsi",
            $user_id,
            $dept_manager_comments,
            $user_id,
            $new_total_score,
            $new_kra_subtotal,
            $new_behavior_average,
            $new_performance_level,
            $evaluation_id
        );
        $update->execute();
        $update->close();

        // Notify HR Supervisor only — HR Manager will be notified when HR Supervisor endorses (Pending Manager)
        $hr_users = $conn->query("SELECT user_id FROM users WHERE role = 'HR Supervisor' AND is_active = 1");
        $emp_name    = $evaluation['first_name'] . ' ' . $evaluation['last_name'];
        $manager_name = $_SESSION['full_name'] ?? 'Department Manager';
        while ($hr = $hr_users->fetch_assoc()) {
            createNotification(
                $conn,
                (int)$hr['user_id'],
                'Evaluation Endorsed by Dept Manager',
                $manager_name . ' endorsed evaluation for ' . $emp_name . ($manager_altered ? ' (with score adjustments)' : '') . ' — forwarded to HRD.',
                BASE_URL . '/supervisor/pending-endorsements.php'
            );
        }

        // Notify employee
        $emp_user = $conn->query("SELECT user_id FROM users WHERE employee_id = " . (int)$evaluation['employee_id'] . " AND role = 'Employee' AND is_active = 1 LIMIT 1")->fetch_assoc();
        if ($emp_user) {
            createNotification(
                $conn,
                (int)$emp_user['user_id'],
                'Self-Rating Endorsed',
                'Your self-rating has been endorsed by your Department Manager ' . $manager_name . ' and sent to HRD.',
                BASE_URL . '/employee/self-rating.php'
            );
        }

        $audit_details = 'Department Manager endorsed evaluation' . ($manager_altered ? ' with score adjustments' : '') . '. Status: Pending HR Consolidation.';
        if ($manager_altered && !empty($altered_details)) {
            $audit_details .= ". Score adjustments:\n" . implode("\n", $altered_details);
        }
        logAudit($conn, $user_id, 'UPDATE', 'Evaluation', $evaluation_id, $audit_details);
        redirectWith(BASE_URL . '/employee/dept-manager-review.php', 'success', 'Evaluation endorsed and sent to HRD successfully.');

    } elseif ($action === 'return') {
        if (empty($dept_manager_comments)) {
            redirectWith(BASE_URL . '/employee/dept-manager-review.php?evaluation_id=' . $evaluation_id, 'danger', 'Comments are required when returning an evaluation.');
        }

        // Return the evaluation to the employee for revision (not to HR Supervisor)
        $update = $conn->prepare("
            UPDATE evaluations
            SET status = 'Returned',
                dept_manager_comments = ?,
                dept_manager_endorsed_by = ?,
                dept_manager_endorsed_date = NOW()
            WHERE evaluation_id = ?
        ");
        $update->bind_param("sii", $dept_manager_comments, $user_id, $evaluation_id);
        $update->execute();
        $update->close();

        $emp_name = $evaluation['first_name'] . ' ' . $evaluation['last_name'];
        $manager_name = $_SESSION['full_name'] ?? 'Department Manager';

        // Notify the employee directly — they must revise and resubmit
        $emp_user = $conn->query("SELECT user_id FROM users WHERE employee_id = " . (int)$evaluation['employee_id'] . " AND role = 'Employee' AND is_active = 1 LIMIT 1")->fetch_assoc();
        if ($emp_user) {
            createNotification(
                $conn,
                (int)$emp_user['user_id'],
                'Evaluation Returned for Revision',
                $manager_name . ' returned your self-rating for revision. Please review the feedback and resubmit. Remarks: ' . $dept_manager_comments,
                BASE_URL . '/employee/self-rating.php?edit=' . $evaluation_id
            );
        }

        logAudit($conn, $user_id, 'UPDATE', 'Evaluation', $evaluation_id, 'Department Manager returned evaluation to employee for revision. Status: Returned.');
        redirectWith(BASE_URL . '/employee/dept-manager-review.php', 'warning', 'Evaluation returned to employee for revision.');
    }
}

// Fetch pending reviews for this department manager
$pending_reviews = [];
if ($is_dept_manager) {
    $pending_stmt = $conn->prepare("
        SELECT e.*, emp.first_name, emp.last_name, emp.job_title, emp.employee_code,
               t.template_name
        FROM evaluations e
        JOIN employees emp ON e.employee_id = emp.employee_id
        LEFT JOIN evaluation_templates t ON e.template_id = t.template_id
        WHERE e.status = 'Pending Dept Manager'
          AND e.deleted_at IS NULL
          AND emp.is_active = 1
        ORDER BY e.submitted_date DESC
    ");
    $pending_stmt->execute();
    $all_pending_reviews = $pending_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $pending_stmt->close();

    foreach ($all_pending_reviews as $pending) {
        if (isDeptManagerOfEmployee($conn, $user_id, (int)$pending['employee_id'])) {
            $pending_reviews[] = $pending;
        }
    }

    // Fetch endorsement history for this department manager.
    // Filtered directly by SQL (dept_manager_endorsed_by = ?) — each manager only sees their own.
    $endorsement_stmt = $conn->prepare("
        SELECT e.*, emp.first_name, emp.last_name, emp.job_title, emp.employee_code,
               t.template_name
        FROM evaluations e
        JOIN employees emp ON e.employee_id = emp.employee_id
        LEFT JOIN evaluation_templates t ON e.template_id = t.template_id
        WHERE e.dept_manager_endorsed_by = ?
        ORDER BY e.dept_manager_endorsed_date DESC
    ");
    $endorsement_stmt->bind_param("i", $user_id);
    $endorsement_stmt->execute();
    $endorsement_history = $endorsement_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $endorsement_stmt->close();
}

// Pre-compute PHP-side KRA total and behavior average
$orig_kra_total = 0;
foreach ($criteria_kra as $c) {
    $val = $c['dept_manager_override_score'] !== null ? (float)$c['dept_manager_override_score'] : (float)$c['score_value'];
    $orig_kra_total += round(($c['weight'] / 100) * $val, 4);
}
$orig_kra_total = round($orig_kra_total, 2);

$orig_beh_avg = 0;
if (!empty($criteria_behavior)) {
    $beh_vals = [];
    foreach ($criteria_behavior as $b) {
        $beh_vals[] = $b['dept_manager_override_score'] !== null ? (float)$b['dept_manager_override_score'] : (float)$b['score_value'];
    }
    $orig_beh_avg = round(array_sum($beh_vals) / count($criteria_behavior), 2);
}

$kra_weight_display = (float)($evaluation['kra_weight'] ?? 80);
$beh_weight_display = (float)($evaluation['behavior_weight'] ?? 20);

require_once '../includes/header.php';
?>

<div class="page-hero fadeup">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-0 gap-4">
        <div>
            <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.55);">Employee Portal</div>
            <h2 class="text-white fw-bold mb-1 mt-1">Department Manager Review</h2>
            <p class="mb-0 text-white-50 small">
                <i class="fas fa-clipboard-check me-1"></i>Review and endorse evaluations from your department's teams
            </p>
        </div>
    </div>
</div>


<?php if (!$is_dept_manager): ?>
    <div class="content-card fadeup-1">
        <div class="card-body text-center py-5">
            <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">Department Manager Access Only</h5>
            <p class="text-muted mb-0">
                This page is available only to Department Managers with subordinates who are supervisors.<br>
                If you believe you should have access, please contact HRD.
            </p>
        </div>
    </div>
<?php elseif (!$evaluation): ?>
    <style>
    @media (max-width: 767px) {
        /* Hide table headers */
        .mobile-responsive-table thead {
            display: none !important;
        }
        /* Make table and body block */
        .mobile-responsive-table, 
        .mobile-responsive-table tbody, 
        .mobile-responsive-table tr {
            display: block !important;
            width: 100% !important;
        }
        /* Style each row as a card */
        .mobile-responsive-table tr {
            display: block !important;
            background: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 12px !important;
            padding: 16px !important;
            margin-bottom: 16px !important;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03) !important;
        }
        /* Table cells stack vertically */
        .mobile-responsive-table td {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            padding: 8px 0 !important;
            border: none !important;
            width: 100% !important;
            border-bottom: 1px solid #f1f5f9 !important;
            text-align: right !important;
        }
        /* Employee details at the top, full width */
        .mobile-responsive-table td:first-child {
            display: block !important;
            text-align: left !important;
            border-bottom: 1.5px solid #e2e8f0 !important;
            padding-top: 0 !important;
            padding-bottom: 10px !important;
            margin-bottom: 8px !important;
        }
        /* Action cell at the bottom */
        .mobile-responsive-table td:last-child {
            border-bottom: none !important;
            padding-bottom: 0 !important;
            margin-top: 8px !important;
            justify-content: flex-end !important;
        }
        /* Add labels on mobile via data-label */
        .mobile-responsive-table td[data-label]::before {
            content: attr(data-label) !important;
            font-weight: 700 !important;
            color: #64748b !important;
            font-size: 0.72rem !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            float: left;
            text-align: left;
        }
    }
    <style>
    /* ── Grouped Employee Capsules ─────────────────────────── */
    .emp-capsule {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 10px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        background: #ffffff;
    }
    .emp-capsule-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        cursor: pointer;
        background: #f8fafc;
        transition: background .15s ease;
        user-select: none;
        border-bottom: 1px solid transparent;
    }
    .emp-capsule-header:hover {
        background: #eef2ff;
    }
    .emp-capsule-header.open {
        background: #eef2ff;
        border-bottom: 1px solid #e2e8f0;
    }
    .emp-capsule-header .emp-info { flex: 1; min-width: 0; }
    .emp-capsule-header .emp-name {
        font-weight: 700;
        font-size: .95rem;
        color: #1e293b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .emp-capsule-header .emp-title {
        font-size: .77rem;
        color: #64748b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .emp-capsule-chevron {
        color: #94a3b8;
        font-size: .85rem;
        transition: transform .25s ease;
        margin-left: 10px;
        flex-shrink: 0;
    }
    .emp-capsule-header.open .emp-capsule-chevron {
        transform: rotate(180deg);
    }
    .emp-capsule-body {
        display: none;
    }
    .emp-capsule-body.show {
        display: block;
    }
    .emp-capsule-body .sub-entry {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        padding: 10px 16px;
        border-bottom: 1px solid #f1f5f9;
        font-size: .85rem;
    }
    .emp-capsule-body .sub-entry:last-child { border-bottom: none; }
    .sub-entry-template { flex: 1; min-width: 120px; color: #334155; font-weight: 500; }
    .sub-entry-period   { color: #64748b; font-size: .78rem; min-width: 110px; }
    .sub-entry-score    { min-width: 80px; text-align: center; }
    .sub-entry-action   { margin-left: auto; }

    @media (max-width: 767px) {
        .emp-capsule-body .sub-entry {
            flex-direction: column;
            align-items: flex-start;
            gap: 6px;
        }
        .sub-entry-action { margin-left: 0; width: 100%; }
        .sub-entry-action a { width: 100%; text-align: center; }
    }
    </style>

    <!-- List of pending endorsements -->
    <div class="content-card fadeup-1">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Pending Endorsements</h5>
            <div class="d-flex align-items-center gap-2">
                <?php if (!empty($pending_reviews)): ?>
                    <span class="badge bg-warning text-dark"><?php echo count($pending_reviews); ?> pending</span>
                <?php endif; ?>
                <button class="btn btn-sm btn-outline-secondary" type="button"
                        data-bs-toggle="collapse" data-bs-target="#endorsementHistoryPanel"
                        aria-expanded="false" aria-controls="endorsementHistoryPanel"
                        title="View Endorsement History" id="historyGearBtn">
                    <i class="fas fa-cog me-1"></i>
                    <span class="d-none d-sm-inline">History</span>
                    <?php if (!empty($endorsement_history)): ?>
                        <span class="badge bg-secondary ms-1"><?php echo count($endorsement_history); ?></span>
                    <?php endif; ?>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (empty($pending_reviews)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-check-circle fa-3x mb-3"></i>
                    <p class="mb-0">No pending evaluations to endorse.</p>
                    <div class="small mt-1">All caught up! Click the <i class="fas fa-cog"></i> gear icon to view past endorsements.</div>
                </div>
            <?php else:
                // Group pending reviews by employee_id
                $grouped_pending = [];
                foreach ($pending_reviews as $p) {
                    $grouped_pending[$p['employee_id']][] = $p;
                }
                $capsule_idx = 0;
            ?>
                <div class="px-3 pt-3 pb-1">
                <?php foreach ($grouped_pending as $emp_id => $entries):
                    $first = $entries[0];
                    $count = count($entries);
                    $capsule_id = 'capsule_' . (int)$emp_id;
                    $is_open = $count === 1; // auto-expand single entries
                    $capsule_idx++;
                ?>
                    <div class="emp-capsule">
                        <!-- Capsule Header (clickable toggle) -->
                        <div class="emp-capsule-header <?php echo $is_open ? 'open' : ''; ?>"
                             onclick="toggleCapsule(this, '<?php echo $capsule_id; ?>')"
                             role="button" aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>">
                            <div class="emp-info">
                                <div class="emp-name"><?php echo e($first['last_name'] . ', ' . $first['first_name']); ?></div>
                                <div class="emp-title"><?php echo e($first['job_title']); ?></div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <?php if ($count > 1): ?>
                                    <span class="badge bg-warning text-dark" title="<?php echo $count; ?> evaluations pending"><?php echo $count; ?> entries</span>
                                <?php else: ?>
                                    <span class="badge bg-light text-secondary border">1 entry</span>
                                <?php endif; ?>
                                <i class="fas fa-chevron-down emp-capsule-chevron"></i>
                            </div>
                        </div>
                        <!-- Capsule Body -->
                        <div class="emp-capsule-body <?php echo $is_open ? 'show' : ''; ?>" id="<?php echo $capsule_id; ?>">
                            <?php foreach ($entries as $pending): ?>
                            <div class="sub-entry">
                                <div class="sub-entry-template">
                                    <i class="fas fa-file-alt me-1 text-muted"></i>
                                    <?php echo e($pending['template_name'] ?? 'N/A'); ?>
                                </div>
                                <div class="sub-entry-period">
                                    <i class="fas fa-calendar-alt me-1 text-muted"></i>
                                    <?php echo formatDate($pending['evaluation_period_start']) . ' – ' . formatDate($pending['evaluation_period_end']); ?>
                                </div>
                                <div class="sub-entry-score">
                                    <div class="fw-bold text-primary"><?php echo number_format((float)$pending['total_score'], 2); ?></div>
                                    <span class="badge <?php echo getPerformanceLevelBadgeClass($pending['performance_level'] ?? ''); ?>" style="font-size:.7rem;"><?php echo e($pending['performance_level'] ?? '—'); ?></span>
                                </div>
                                <div class="sub-entry-action">
                                    <a href="?evaluation_id=<?php echo (int)$pending['evaluation_id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye me-1"></i>Review
                                    </a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

        <!-- Endorsement History panel — hidden by default, toggled by gear icon -->
        <div class="collapse" id="endorsementHistoryPanel">
            <div class="border-top">
                <div class="px-3 py-2 bg-light d-flex align-items-center justify-content-between">
                    <span class="fw-semibold text-secondary" style="font-size:.85rem;">
                        <i class="fas fa-history me-1"></i>Endorsement History
                    </span>
                    <span class="badge bg-secondary">
                        <?php echo count($endorsement_history); ?> record<?php echo count($endorsement_history) !== 1 ? 's' : ''; ?>
                    </span>
                </div>
                <?php if (empty($endorsement_history)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p class="mb-0 small">No endorsement history yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle mobile-responsive-table">
                            <thead class="table-light" style="font-size:.82rem;">
                                <tr>
                                    <th class="ps-3">Employee</th>
                                    <th>Template</th>
                                    <th>Period</th>
                                    <th class="text-center">Final Score</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Endorsed On</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($endorsement_history as $hist): 
                                    $status_classes = [
                                        'Pending Dept Manager'     => 'bg-warning text-dark',
                                        'Pending HR Consolidation' => 'bg-info text-dark',
                                        'Pending Manager'          => 'bg-info text-dark',
                                        'Approved'                 => 'bg-success',
                                        'Returned'                 => 'bg-danger',
                                    ];
                                    $status_labels = [
                                        'Pending Dept Manager'     => 'Pending Dept Mgr',
                                        'Pending HR Consolidation' => 'Sent to HR',
                                        'Pending Manager'          => 'Pending HR Mgr',
                                        'Approved'                 => 'Approved',
                                        'Returned'                 => 'Returned',
                                    ];
                                    $sc = $status_classes[$hist['status']] ?? 'bg-secondary';
                                    $sl = $status_labels[$hist['status']] ?? $hist['status'];
                                ?>
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-semibold text-dark fs-6"><?php echo e($hist['last_name'] . ', ' . $hist['first_name']); ?></div>
                                            <div class="small text-muted"><?php echo e($hist['job_title']); ?></div>
                                        </td>
                                        <td data-label="Template" class="text-dark fw-medium"><?php echo e($hist['template_name'] ?? 'N/A'); ?></td>
                                        <td data-label="Period" class="small text-muted">
                                            <?php echo formatDate($hist['evaluation_period_start']); ?> –<br>
                                            <?php echo formatDate($hist['evaluation_period_end']); ?>
                                        </td>
                                        <td data-label="Final Score">
                                            <div class="fw-bold text-primary fs-6"><?php echo number_format((float)$hist['total_score'], 2); ?></div>
                                            <span class="badge <?php echo getPerformanceLevelBadgeClass($hist['performance_level'] ?? ''); ?>" style="font-size:.7rem;"><?php echo e($hist['performance_level'] ?? '—'); ?></span>
                                        </td>
                                        <td data-label="Status">
                                            <span class="badge <?php echo $sc; ?>"><?php echo $sl; ?></span>
                                        </td>
                                        <td data-label="Endorsed On" class="small text-muted">
                                            <?php echo $hist['dept_manager_endorsed_date'] ? formatDateTime($hist['dept_manager_endorsed_date']) : '—'; ?>
                                        </td>
                                        <td class="text-end">
                                            <a href="?evaluation_id=<?php echo (int)$hist['evaluation_id']; ?>" class="btn btn-sm btn-outline-secondary">
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
    </div>

    <script>
    // Rotate gear icon when history panel is open
    (function () {
        const btn   = document.getElementById('historyGearBtn');
        const panel = document.getElementById('endorsementHistoryPanel');
        if (!btn || !panel) return;
        const icon = btn.querySelector('.fa-cog');
        panel.addEventListener('show.bs.collapse', function () {
            icon.style.transition = 'transform .3s ease';
            icon.style.transform  = 'rotate(90deg)';
        });
        panel.addEventListener('hide.bs.collapse', function () {
            icon.style.transform = 'rotate(0deg)';
        });
    })();

    // Toggle employee grouped capsules
    function toggleCapsule(header, bodyId) {
        const body = document.getElementById(bodyId);
        if (!body) return;
        const isShowing = body.classList.contains('show');
        if (isShowing) {
            body.classList.remove('show');
            header.classList.remove('open');
            header.setAttribute('aria-expanded', 'false');
        } else {
            body.classList.add('show');
            header.classList.add('open');
            header.setAttribute('aria-expanded', 'true');
        }
    }
    </script>
<?php else: ?>
    <!-- Progress Indicator for evaluation review workflow -->
    <?php
    // Determine active step:
    // Step 0 (Review Scores)  – always active when first opening; status = 'Pending Dept Manager'
    // Step 1 (Adjust & Comment) – active if we have supervisor/employee comments loaded (still pending)
    // Step 2 (Endorse)        – active if read-only (already endorsed / forwarded)
    $dm_step = 0;
    if ($is_readonly) {
        $dm_step = 2; // Endorsed or view-only
    } elseif (!empty($evaluation['supervisor_comments']) || !empty($criteria_kra) || !empty($criteria_behavior)) {
        $dm_step = 1; // Scores loaded, ready to adjust & comment
    }
    $dm_steps = [
        ['label' => 'Review Scores',    'icon' => 'fas fa-eye'],
        ['label' => 'Adjust & Comment', 'icon' => 'fas fa-pen'],
        ['label' => 'Endorse',          'icon' => 'fas fa-check-circle'],
    ];
    ?>
    <div class="progress-indicator" role="navigation" aria-label="Review progress">
        <?php foreach ($dm_steps as $di => $ds):
            $ds_state = $di < $dm_step ? 'completed' : ($di === $dm_step ? 'active' : '');
        ?>
        <div class="progress-step <?php echo $ds_state; ?>" aria-current="<?php echo $di === $dm_step ? 'step' : 'false'; ?>">
            <div class="progress-step-number">
                <?php if ($di < $dm_step): ?>
                    <i class="fas fa-check" aria-hidden="true"></i>
                <?php else: ?>
                    <?php echo $di + 1; ?>
                <?php endif; ?>
            </div>
            <div class="progress-step-label"><?php echo e($ds['label']); ?></div>
        </div>
        <?php if ($di < count($dm_steps) - 1): ?>
        <div class="progress-line <?php echo $di < $dm_step ? 'completed' : ''; ?>"></div>
        <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <!-- Review and Endorse Form -->

    <style>
    .score-changed {
        background: #fff8e1 !important;
        border-left: 4px solid #f59e0b !important;
    }
    .change-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: .72rem;
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fcd34d;
        border-radius: 4px;
        padding: 1px 6px;
        margin-top: 4px;
        white-space: nowrap;
    }
    
    /* TOTAL ROWS DESIGN UPGRADE */
    .total-row td {
        background: #f8f3df !important;
        font-weight: 800 !important;
        border-top: 2px solid #CBA135 !important;
        border-bottom: 3px double #CBA135 !important;
        color: #082E06 !important;
        font-size: 0.95rem !important;
    }
    
    /* INPUT VALUE CENTER ALIGNMENT */
    .kra-input, .beh-input {
        text-align: center !important;
        font-weight: 600 !important;
    }
    
    /* FINAL PERFORMANCE GRADE PREMIUM CONTAINER */
    .final-grade-card {
        background: linear-gradient(135deg, #041D03 0%, #082E06 68%, #294306 100%) !important;
        border-radius: 10px !important;
        padding: 1.8rem !important;
        color: #ffffff !important;
        margin-top: 2rem !important;
        border: 1px solid rgba(203, 161, 53, 0.36) !important;
        border-top: 5px solid #CBA135 !important;
        box-shadow: 0 14px 28px rgba(8, 46, 6, 0.28), inset 0 1px 0 rgba(255, 255, 255, 0.08) !important;
        transform: none !important;
        transition: none !important;
    }
    .final-grade-card:hover {
        box-shadow: 0 14px 28px rgba(8, 46, 6, 0.28), inset 0 1px 0 rgba(255, 255, 255, 0.08) !important;
        transform: none !important;
    }

    .final-grade-card .col-md-8:hover,
    .final-grade-card .table-hover tbody tr:hover,
    .final-grade-card .table-hover tbody tr:hover > *,
    .final-grade-card .grade-table tbody tr:hover,
    .final-grade-card .grade-table tbody tr:hover td {
        background: transparent !important;
        background-color: transparent !important;
        --bs-table-bg: transparent !important;
        --bs-table-accent-bg: transparent !important;
        --bs-table-hover-bg: transparent !important;
        --bs-table-hover-color: #ffffff !important;
        color: #ffffff !important;
    }

    .final-grade-card,
    .final-grade-card th,
    .final-grade-card td,
    .final-grade-card div,
    .final-grade-card span,
    .final-grade-card small,
    .final-grade-card table {
        color: #ffffff !important;
    }
    .final-grade-card .table {
        background: transparent !important;
        background-color: transparent !important;
        --bs-table-bg: transparent !important;
        --bs-table-accent-bg: transparent !important;
        --bs-table-striped-bg: transparent !important;
        --bs-table-active-bg: transparent !important;
        --bs-table-hover-bg: transparent !important;
        --bs-table-color: #ffffff !important;
        margin-bottom: 0 !important;
        border-color: rgba(203,161,53,.22) !important;
    }
    .final-grade-card .table > :not(caption) > * > *,
    .final-grade-card .grade-table > :not(caption) > * > * {
        background: transparent !important;
        background-color: transparent !important;
        box-shadow: none !important;
        --bs-table-bg: transparent !important;
        --bs-table-accent-bg: transparent !important;
        --bs-table-striped-bg: transparent !important;
        --bs-table-active-bg: transparent !important;
        --bs-table-hover-bg: transparent !important;
        color: #ffffff !important;
    }
    .final-grade-card .grade-table th,
    .final-grade-card .grade-table td {
        color: rgba(255,255,255,.9) !important;
        border-color: rgba(203,161,53,.22) !important;
        font-size: .88rem !important;
        background: transparent !important;
        background-color: transparent !important;
        --bs-table-bg: transparent !important;
        --bs-table-color: #ffffff !important;
    }
    .final-grade-card .grade-table th {
        font-weight: 600 !important;
        color: rgba(248,243,223,.72) !important;
        font-size: .75rem !important;
        text-transform: uppercase !important;
        letter-spacing: .5px !important;
    }
    .final-grade-card .col-md-8,
    .final-grade-card .col-md-8:hover,
    .final-grade-card .col-md-8:hover *,
    .final-grade-card .grade-table,
    .final-grade-card .grade-table tbody,
    .final-grade-card .grade-table tbody tr,
    .final-grade-card .grade-table tbody tr:hover,
    .final-grade-card .grade-table tbody tr:hover > *,
    .final-grade-card .grade-table th,
    .final-grade-card .grade-table td,
    .final-grade-card .grade-table th:hover,
    .final-grade-card .grade-table td:hover {
        background: transparent !important;
        background-color: transparent !important;
        box-shadow: none !important;
        --bs-table-bg: transparent !important;
        --bs-table-accent-bg: transparent !important;
        --bs-table-striped-bg: transparent !important;
        --bs-table-active-bg: transparent !important;
        --bs-table-hover-bg: transparent !important;
        --bs-table-hover-color: #ffffff !important;
        color: #ffffff !important;
    }
    .final-grade-value {
        font-size: 3.5rem !important;
        font-weight: 850 !important;
        line-height: 1.1 !important;
        color: #ffffff !important;
        text-shadow: 0 2px 12px rgba(203, 161, 53, 0.28) !important;
    }
    .perf-badge {
        display: inline-block !important;
        padding: .4rem 1rem !important;
        border-radius: 20px !important;
        font-weight: 800 !important;
        font-size: .85rem !important;
        margin-top: .6rem !important;
        background: rgba(203,161,53,.18) !important;
        border: 1px solid rgba(203,161,53,.45) !important;
        color: #f8f3df !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
    }
    
    /* RESPONSIVE LAYOUT FOR MOBILE */
    @media (max-width: 767px) {
        .final-grade-card .row {
            flex-direction: column-reverse !important;
            gap: 1.5rem !important;
        }
        .final-grade-card .col-md-4 {
            border-bottom: 1px dashed rgba(203, 161, 53, 0.35) !important;
            padding-bottom: 1.5rem !important;
            margin-bottom: 0.5rem !important;
        }
        .final-grade-value {
            font-size: 3.5rem !important;
        }
    }
    .supervisor-altered-tag {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: .72rem;
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fcd34d;
        border-radius: 4px;
        padding: 2px 7px;
        margin-left: 6px;
    }
    </style>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="content-card fadeup-1">
                <div class="card-header">
                    <h5><i class="fas fa-star me-2"></i>Review Evaluation Details</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <!-- Employee Info -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label text-muted small uppercase">Employee</label>
                                <div class="fw-bold fs-5 text-dark"><?php echo e($evaluation['last_name'] . ', ' . $evaluation['first_name']); ?></div>
                                <div class="small text-muted"><?php echo e($evaluation['job_title']); ?></div>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <label class="form-label text-muted small uppercase">Department &amp; Branch</label>
                                <div class="fw-semibold"><?php echo e($evaluation['department_name']); ?></div>
                                <div class="small text-muted"><?php echo e($evaluation['branch_name']); ?></div>
                            </div>
                            <hr class="my-3">
                            <div class="col-md-4">
                                <label class="form-label text-muted small">Evaluation Type</label>
                                <input type="text" class="form-control bg-light" value="<?php echo e($evaluation['evaluation_type']); ?>" readonly>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label text-muted small">Evaluation Period</label>
                                <input type="text" class="form-control bg-light" 
                                       value="<?php echo formatDate($evaluation['evaluation_period_start']) . ' - ' . formatDate($evaluation['evaluation_period_end']); ?>" readonly>
                            </div>
                        </div>

                        <!-- Employee Self-Comments -->
                        <?php if (!empty($evaluation['staff_comments'])): ?>
                            <div class="alert alert-light border mb-4">
                                <label class="form-label fw-semibold text-primary">
                                    <i class="fas fa-comment-dots me-1"></i>Employee Self-Comments
                                </label>
                                <p class="mb-0 text-dark"><?php echo nl2br(e($evaluation['staff_comments'])); ?></p>
                            </div>
                        <?php endif; ?>

                        <!-- Supervisor Comments -->
                        <?php if (!empty($evaluation['supervisor_comments'])): ?>
                            <div class="alert bg-light border-start border-warning border-3 mb-4">
                                <label class="form-label fw-semibold text-warning">
                                    <i class="fas fa-user-shield me-1"></i>Supervisor Comments
                                    (<?php echo e($evaluation['supervisor_name'] ?? 'Immediate Head'); ?>)
                                    <?php if (!empty($evaluation['supervisor_altered_scores'])): ?>
                                        <span class="supervisor-altered-tag">
                                            <i class="fas fa-pen"></i> Scores Adjusted by Supervisor
                                        </span>
                                    <?php endif; ?>
                                </label>
                                <p class="mb-0 text-dark"><?php echo nl2br(e($evaluation['supervisor_comments'])); ?></p>
                            </div>
                        <?php elseif (!empty($evaluation['supervisor_altered_scores'])): ?>
                            <div class="alert bg-light border-start border-warning border-3 mb-4">
                                <span class="supervisor-altered-tag">
                                    <i class="fas fa-pen"></i> Supervisor adjusted scores before forwarding
                                </span>
                            </div>
                        <?php endif; ?>

                        <!-- KRA Scores -->
                        <?php if (!empty($criteria_kra)): ?>
                            <div class="rating-section">
                                <h2 class="rating-section-title">
                                    <i class="fas fa-bullseye me-2" aria-hidden="true"></i>KRA Ratings
                                    <span class="ms-2 text-muted fw-normal" style="font-size:.8rem;">(You may adjust scores before endorsing)</span>
                                </h2>

                                <?php foreach ($criteria_kra as $criterion):
                                    $orig   = (float)$criterion['score_value'];
                                    $weight = (float)$criterion['weight'];
                                    $current_val = $criterion['dept_manager_override_score'] !== null ? (float)$criterion['dept_manager_override_score'] : $orig;
                                    $weighted_orig = round(($weight / 100) * $current_val, 2);
                                ?>
                                    <div class="rating-item kra-row <?php echo $criterion['dept_manager_override_score'] !== null ? 'score-changed' : ''; ?>"
                                         data-orig="<?php echo $orig; ?>"
                                         data-weight="<?php echo $weight; ?>"
                                         data-criterion="<?php echo (int)$criterion['criterion_id']; ?>">
                                        <div class="rating-header">
                                            <h3 class="rating-title">
                                                <?php echo e($criterion['criterion_name']); ?>
                                                <span class="badge bg-secondary ms-2" style="font-size:0.75rem;font-weight:600;">
                                                    Weight: <?php echo e($weight); ?>%
                                                </span>
                                            </h3>
                                            <?php if (!empty($criterion['description'])): ?>
                                                <p class="rating-description"><?php echo e($criterion['description']); ?></p>
                                            <?php endif; ?>
                                        </div>

                                        <div class="d-flex flex-wrap align-items-center gap-3 mt-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="text-muted small fw-semibold">Current Rating:</span>
                                                <span class="badge bg-primary text-white fs-6">
                                                    <?php echo number_format($orig, 2); ?>
                                                </span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <label class="text-muted small fw-semibold mb-0" for="kra_<?php echo (int)$criterion['criterion_id']; ?>">Your Adjustment:</label>
                                                <input type="number" class="form-control kra-input"
                                                       name="kra_scores[<?php echo (int)$criterion['criterion_id']; ?>]"
                                                       id="kra_<?php echo (int)$criterion['criterion_id']; ?>"
                                                       min="0" max="4" step="0.01"
                                                       value="<?php echo number_format($current_val, 2); ?>"
                                                       oninput="if(parseFloat(this.value) > 4) this.value = 4;"
                                                       <?php echo $disabled_attr; ?>
                                                       placeholder="0.00 – 4.00"
                                                       style="max-width: 90px; text-align: center;">
                                                <div class="change-badge <?php echo $criterion['dept_manager_override_score'] !== null ? '' : 'd-none'; ?>" id="chg_kra_<?php echo (int)$criterion['criterion_id']; ?>">
                                                    <i class="fas fa-pen"></i> Adjusted
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center gap-2 ms-auto">
                                                <span class="text-muted small fw-semibold">Weighted:</span>
                                                <span class="weighted-cell fw-semibold text-primary"><?php echo number_format($weighted_orig, 2); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                                <div class="total-row d-flex justify-content-end align-items-center gap-2 px-3 py-2 rounded mt-1">
                                    <span class="fw-bold"><i class="fas fa-sigma me-1"></i>KRA Total:</span>
                                    <span class="fw-bold text-primary" id="kraTotal"><?php echo number_format($orig_kra_total, 2); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Behavior Scores -->
                        <?php if (!empty($criteria_behavior)): ?>
                            <div class="rating-section">
                                <h2 class="rating-section-title">
                                    <i class="fas fa-heart me-2" aria-hidden="true"></i>Behavior Ratings
                                    <span class="ms-2 text-muted fw-normal" style="font-size:.8rem;">(You may adjust scores before endorsing)</span>
                                </h2>

                                <?php foreach ($criteria_behavior as $criterion):
                                    $orig = (float)$criterion['score_value'];
                                    $current_val = $criterion['dept_manager_override_score'] !== null ? (float)$criterion['dept_manager_override_score'] : $orig;
                                ?>
                                    <div class="rating-item beh-row <?php echo $criterion['dept_manager_override_score'] !== null ? 'score-changed' : ''; ?>"
                                         data-orig="<?php echo $orig; ?>"
                                         data-criterion="<?php echo (int)$criterion['criterion_id']; ?>">
                                        <div class="rating-header">
                                            <h3 class="rating-title"><?php echo e($criterion['criterion_name']); ?></h3>
                                            <?php if (!empty($criterion['description'])): ?>
                                                <p class="rating-description"><?php echo e($criterion['description']); ?></p>
                                            <?php endif; ?>
                                        </div>

                                        <div class="d-flex flex-wrap align-items-center gap-3 mt-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="text-muted small fw-semibold">Current Rating:</span>
                                                <span class="badge bg-info text-dark fs-6">
                                                    <?php echo number_format($orig, 2); ?>
                                                </span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <label class="text-muted small fw-semibold mb-0" for="beh_<?php echo (int)$criterion['criterion_id']; ?>">Your Adjustment:</label>
                                                <input type="number" class="form-control beh-input"
                                                       name="beh_scores[<?php echo (int)$criterion['criterion_id']; ?>]"
                                                       id="beh_<?php echo (int)$criterion['criterion_id']; ?>"
                                                       min="0" max="4" step="0.01"
                                                       value="<?php echo number_format($current_val, 2); ?>"
                                                       oninput="if(parseFloat(this.value) > 4) this.value = 4;"
                                                       <?php echo $disabled_attr; ?>
                                                       placeholder="0.00 – 4.00"
                                                       style="max-width: 90px; text-align: center;">
                                                <div class="change-badge <?php echo $criterion['dept_manager_override_score'] !== null ? '' : 'd-none'; ?>" id="chg_beh_<?php echo (int)$criterion['criterion_id']; ?>">
                                                    <i class="fas fa-pen"></i> Adjusted
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                                <div class="total-row d-flex justify-content-end align-items-center gap-2 px-3 py-2 rounded mt-1">
                                    <span class="fw-bold"><i class="fas fa-calculator me-1"></i>Behavior Average:</span>
                                    <span class="fw-bold text-info" id="behAvg"><?php echo number_format($orig_beh_avg, 2); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Final Grade Summary Card -->
                        <div class="final-grade-card">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <i class="fas fa-award fa-lg" style="color:#CBA135;"></i>
                                <span class="fw-bold" style="font-size:1rem;letter-spacing:.5px;">Final Performance Grade</span>
                            </div>
                            <div class="row align-items-center">
                                <div class="col-md-8" style="background:transparent !important;">
                                    <table class="table grade-table mb-0" style="background:transparent !important;--bs-table-bg:transparent;--bs-table-accent-bg:transparent;--bs-table-striped-bg:transparent;--bs-table-active-bg:transparent;--bs-table-hover-bg:transparent;--bs-table-color:#ffffff;">
                                        <thead>
                                            <tr>
                                                <th>Section</th>
                                                <th class="text-end">Score</th>
                                                <th class="text-end">Weight</th>
                                                <th class="text-end">Weighted Score</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><i class="fas fa-bullseye me-1" style="color:#CBA135;"></i>KRA</td>
                                                <td class="text-end fw-semibold" id="fpgKRA"><?php echo number_format($orig_kra_total, 2); ?></td>
                                                <td class="text-end"><?php echo $kra_weight_display; ?>%</td>
                                                <td class="text-end fw-semibold" id="fpgKRAWeighted"><?php echo number_format($orig_kra_total * ($kra_weight_display / 100), 2); ?></td>
                                            </tr>
                                            <tr>
                                                <td><i class="fas fa-heart me-1" style="color:#8FB55A;"></i>Behavior</td>
                                                <td class="text-end fw-semibold" id="fpgBeh"><?php echo number_format($orig_beh_avg, 2); ?></td>
                                                <td class="text-end"><?php echo $beh_weight_display; ?>%</td>
                                                <td class="text-end fw-semibold" id="fpgBehWeighted"><?php echo number_format($orig_beh_avg * ($beh_weight_display / 100), 2); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-md-4 text-center mt-3 mt-md-0">
                                    <div class="final-grade-value" id="fpgTotal"><?php echo number_format((float)$evaluation['total_score'], 2); ?></div>
                                    <div class="perf-badge" id="fpgLevel"><?php echo e($evaluation['performance_level'] ?? '—'); ?></div>
                                    <div class="small mt-2" style="color:rgba(255,255,255,.5);">Final Grade</div>
                                </div>
                            </div>
                            <div id="alteredNotice" class="mt-3 d-none" style="background:rgba(251,191,36,.15);border:1px solid rgba(251,191,36,.4);border-radius:8px;padding:.5rem .9rem;font-size:.82rem;color:#fde68a;">
                                <i class="fas fa-triangle-exclamation me-1"></i>
                                <strong>Scores have been adjusted.</strong> The final grade reflects your changes and will be saved on endorsement.
                            </div>
                        </div>

                        <!-- Dept Manager Comments -->
                        <div class="mb-4 mt-4">
                            <label for="dept_manager_comments" class="form-label fw-bold">
                                Department Manager Endorsement Comments
                            </label>
                            <textarea class="form-control border-primary" id="dept_manager_comments" name="dept_manager_comments" rows="4"
                                      placeholder="Provide feedback, endorsements, or reasons for returning this self-rating..."
                                      <?php echo $readonly_attr; ?>><?php echo $is_readonly ? e($evaluation['dept_manager_comments'] ?? '') : ''; ?></textarea>
                            <div class="form-text">
                                <?php echo $is_readonly ? 'View-only. This evaluation has already moved past department manager review.' : 'Required when returning. Optional when endorsing.'; ?>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex flex-wrap justify-content-between gap-2 mt-4">
                            <a href="dept-manager-review.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back to List
                            </a>
                            <?php if ($is_readonly): ?>
                                <span class="badge bg-secondary align-self-center px-3 py-2">
                                    <i class="fas fa-eye me-1"></i>View-only
                                </span>
                            <?php else: ?>
                                <div class="d-flex flex-wrap gap-2 w-100 w-sm-auto justify-content-end">
                                    <button type="submit" name="confirm_action" value="return" class="btn btn-warning text-dark flex-fill flex-sm-grow-0"
                                            onclick="return confirm('Are you sure you want to return this evaluation for revision? Comments are required.');">
                                        <i class="fas fa-undo me-1"></i>Return for Revision
                                    </button>
                                    <button type="submit" name="confirm_action" value="endorse" class="btn btn-success flex-fill flex-sm-grow-0"
                                            onclick="return confirm('Endorse this evaluation and send to HRD?');">
                                        <i class="fas fa-check-circle me-1"></i>Endorse &amp; Send to HRD
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Audit History Timeline -->
            <?php if ($evaluation_id > 0): ?>
                <div class="content-card mt-4 fadeup-2">
                    <div class="card-header">
                        <h5><i class="fas fa-history me-2"></i>Evaluation Audit History</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($audit_history)): ?>
                            <p class="text-muted small mb-0">No audit logs found for this evaluation.</p>
                        <?php else: ?>
                            <div class="timeline" style="border-left: 2px solid #e2e8f0; padding-left: 20px; position: relative;">
                                <?php foreach ($audit_history as $log): ?>
                                    <div class="timeline-item mb-3" style="position: relative;">
                                        <div class="timeline-marker" style="width: 12px; height: 12px; border-radius: 50%; background: #3b82f6; position: absolute; left: -27px; top: 5px;"></div>
                                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-1 mb-1">
                                            <span class="fw-bold text-dark small" style="word-break: break-word; overflow-wrap: anywhere;">
                                                <?php echo e($log['full_name'] ?? 'System'); ?>
                                                <span class="text-muted fw-normal">
                                                    <?php
                                                        $audit_label = !empty($log['job_title'])
                                                            ? $log['job_title']
                                                            : ($log['role'] ?? 'System');
                                                        echo '(' . e($audit_label) . ')';
                                                    ?>
                                                </span>
                                            </span>
                                            <span class="text-muted x-small ms-auto"><?php echo formatDateTime($log['timestamp']); ?></span>
                                        </div>
                                        <div class="small text-secondary fw-semibold"><?php echo e($log['action_type']); ?> - <?php echo e(explode('.', $log['details'])[0]); ?></div>
                                        <?php if (strpos($log['details'], 'Score adjustments:') !== false): ?>
                                            <div class="mt-2 p-2 bg-light rounded border x-small text-muted" style="white-space: pre-wrap; font-family: monospace; font-size: 0.78rem;"><?php echo e(substr($log['details'], strpos($log['details'], 'Score adjustments:'))); ?></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <div class="content-card fadeup-2">
                <div class="card-header">
                    <h5><i class="fas fa-chart-pie me-2"></i>Summary</h5>
                </div>
                <div class="card-body text-center py-4">
                    <div style="font-size:3.5rem;font-weight:800;color:var(--primary-blue);line-height:1;margin-bottom:.5rem;" id="sideFinalGrade">
                        <?php echo number_format((float)$evaluation['total_score'], 2); ?>
                    </div>
                    <div class="fw-bold mb-3" id="sidePerfLevel"><?php echo e($evaluation['performance_level'] ?? '—'); ?></div>
                    <hr>
                    <div class="row g-2 text-start mt-2">
                        <div class="col-6 text-muted small">KRA Weight:</div>
                        <div class="col-6 text-end fw-semibold small"><?php echo e($evaluation['kra_weight']); ?>%</div>
                        <div class="col-6 text-muted small">KRA Score:</div>
                        <div class="col-6 text-end fw-semibold small text-primary" id="sideKRA"><?php echo number_format($orig_kra_total, 2); ?></div>

                        <div class="col-12"><hr class="my-2"></div>

                        <div class="col-6 text-muted small">Behavior Weight:</div>
                        <div class="col-6 text-end fw-semibold small"><?php echo e($evaluation['behavior_weight']); ?>%</div>
                        <div class="col-6 text-muted small">Behavior Avg:</div>
                        <div class="col-6 text-end fw-semibold small text-info" id="sideBeh"><?php echo number_format($orig_beh_avg, 2); ?></div>
                    </div>
                </div>
            </div>

            <!-- Rating Scale -->
            <div class="content-card fadeup-2 mt-4">
                <div class="card-header">
                    <h5><i class="fas fa-chart-bar me-2"></i>Rating Scale</h5>
                </div>
                <div class="card-body">
                    <div class="small">
                        <div class="d-flex justify-content-between mb-1">
                            <span>3.60 - 4.00</span>
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

<script>
(function () {
    const kraWeight = <?php echo $kra_weight_display; ?> / 100;
    const behWeight = <?php echo $beh_weight_display; ?> / 100;

    function getPerformanceLabel(score) {
        if (score >= 3.60) return 'Outstanding';
        if (score >= 2.60) return 'Exceeds Expectations';
        if (score >= 2.00) return 'Meets Expectations';
        return 'Needs Improvement';
    }

    function recalculate() {
        let kraTotal   = 0;
        let anyChanged = false;

        // KRA
        document.querySelectorAll('.kra-row').forEach(row => {
            const orig   = parseFloat(row.dataset.orig);
            const weight = parseFloat(row.dataset.weight) / 100;
            const cid    = row.dataset.criterion;
            const input  = document.getElementById('kra_' + cid);
            const badge  = document.getElementById('chg_kra_' + cid);
            const wCell  = row.querySelector('.weighted-cell');

            if (!input) return;
            if (input.value !== '') {
                const numVal = parseFloat(input.value);
                if (!isNaN(numVal) && numVal > 4) {
                    input.value = 4;
                }
            }
            const val = parseFloat(input.value) || 0;
            const weighted = val * weight;
            kraTotal += weighted;

            if (wCell) wCell.textContent = weighted.toFixed(2);

            const changed = Math.abs(val - orig) > 0.005;
            if (changed) anyChanged = true;
            if (badge) badge.classList.toggle('d-none', !changed);
            row.classList.toggle('score-changed', changed);
        });

        // Behavior
        let behSum = 0, behCount = 0;
        document.querySelectorAll('.beh-row').forEach(row => {
            const orig  = parseFloat(row.dataset.orig);
            const cid   = row.dataset.criterion;
            const input = document.getElementById('beh_' + cid);
            const badge = document.getElementById('chg_beh_' + cid);

            if (!input) return;
            if (input.value !== '') {
                const numVal = parseFloat(input.value);
                if (!isNaN(numVal) && numVal > 4) {
                    input.value = 4;
                }
            }
            const val = parseFloat(input.value) || 0;
            behSum += val;
            behCount++;

            const changed = Math.abs(val - orig) > 0.005;
            if (changed) anyChanged = true;
            if (badge) badge.classList.toggle('d-none', !changed);
            row.classList.toggle('score-changed', changed);
        });

        const kraRounded = Math.round(kraTotal * 100) / 100;
        const behAvg     = behCount > 0 ? Math.round((behSum / behCount) * 100) / 100 : 0;
        const finalGrade = Math.round((kraRounded * kraWeight + behAvg * behWeight) * 100) / 100;
        const perfLabel  = getPerformanceLabel(finalGrade);

        // Update table totals
        const kraEl = document.getElementById('kraTotal');
        if (kraEl) kraEl.textContent = kraRounded.toFixed(2);
        const behEl = document.getElementById('behAvg');
        if (behEl) behEl.textContent = behAvg.toFixed(2);

        // Update final grade card
        const els = {
            fpgKRA: kraRounded.toFixed(2),
            fpgKRAWeighted: (kraRounded * kraWeight).toFixed(2),
            fpgBeh: behAvg.toFixed(2),
            fpgBehWeighted: (behAvg * behWeight).toFixed(2),
            fpgTotal: finalGrade.toFixed(2),
            fpgLevel: perfLabel,
            sideFinalGrade: finalGrade.toFixed(2),
            sidePerfLevel: perfLabel,
            sideKRA: kraRounded.toFixed(2),
            sideBeh: behAvg.toFixed(2)
        };
        for (const [id, val] of Object.entries(els)) {
            const el = document.getElementById(id);
            if (el) el.textContent = val;
        }

        // Altered notice
        const notice = document.getElementById('alteredNotice');
        if (notice) notice.classList.toggle('d-none', !anyChanged);
    }

    document.querySelectorAll('.kra-input, .beh-input').forEach(inp => {
        inp.addEventListener('input', recalculate);
    });

    recalculate();
})();

// ── Real-time status polling: lock UI if another dept manager acts first ──────
document.addEventListener('DOMContentLoaded', function () {
    const evaluationId = <?php echo $evaluation_id; ?>;
    const isReadonly   = <?php echo $is_readonly ? 'true' : 'false'; ?>;

    if (evaluationId > 0 && !isReadonly) {
        let statusChecksFailed = 0;
        const statusPollInterval = setInterval(function () {
            fetch('../includes/ajax/check-evaluation-status.php?evaluation_id=' + evaluationId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        statusChecksFailed = 0;
                        // Active status for dept manager stage
                        if (data.status !== 'Pending Dept Manager') {
                            clearInterval(statusPollInterval);
                            showDeptManagerLockedNotification();
                        }
                    }
                })
                .catch(() => {
                    statusChecksFailed++;
                    if (statusChecksFailed > 5) clearInterval(statusPollInterval);
                });
        }, 5000); // Poll every 5 seconds

        function showDeptManagerLockedNotification() {
            // Disable all interactive elements
            document.querySelectorAll('input, select, textarea, button').forEach(el => {
                el.disabled = true;
                el.classList.add('disabled');
            });

            if (typeof showLiveToast === 'function') {
                showLiveToast(
                    'Evaluation Locked',
                    'Another department manager has already acted on this evaluation. It is now read-only. Reloading...',
                    ''
                );
            } else {
                alert('Another department manager has already acted on this evaluation. It is now read-only.');
            }

            setTimeout(() => window.location.reload(), 4000);
        }
    }
});
// ─────────────────────────────────────────────────────────────────────────────
</script>

<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
