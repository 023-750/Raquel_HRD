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

if (!hasSupervisorPrivileges($conn, $supervisor_employee_id)) {
    redirectWith(BASE_URL . '/employee/dashboard.php', 'danger', 'Access Denied: You do not have supervisor privileges.');
}

// Ensure 360-degree columns exist
ensure360DegreeEvaluationColumns($conn);

// Get evaluation ID from URL
$evaluation_id = (int)($_GET['evaluation_id'] ?? 0);

// Check if user has supervisor privileges (has subordinates OR holds a supervisor/manager role)
$is_supervisor = hasSupervisorPrivileges($conn, $supervisor_employee_id);

// Fetch the evaluation with employee details
$evaluation = null;
$employee = null;
$scores = [];
$template = null;
$criteria_kra = [];
$criteria_behavior = [];
$is_readonly = false;

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
        WHERE e.evaluation_id = ?
          AND emp.employee_id <> ?
          AND (
            e.status IN ('Pending Dept Supervisor', 'Pending Supervisor', 'Supervisor Confirmed')
            OR e.dept_supervisor_confirmed_by = ?
            OR e.supervisor_confirmed_by = ?
        )
        LIMIT 1
    ");
    $eval_stmt->bind_param("iiii", $evaluation_id, $supervisor_employee_id, $user_id, $user_id);
    $eval_stmt->execute();
    $evaluation = $eval_stmt->get_result()->fetch_assoc();
    $eval_stmt->close();
    
    if ($evaluation) {
        if (!in_array($evaluation['status'], ['Pending Dept Supervisor', 'Pending Supervisor'], true)) {
            $is_readonly = true;
        }

        $uses_hr_specific_flow = getEmployeeHRRole($conn, (int)$evaluation['employee_id']) !== null
            || isMainOfficeHumanResourcesEmployee($conn, (int)$evaluation['employee_id']);

        if ($uses_hr_specific_flow) {
            redirectWith(BASE_URL . '/employee/dashboard.php', 'danger', 'This HR self-rating is routed through HRD review.');
        }
        
        // Check if this supervisor is the employee's immediate head
        $is_authorized = isSupervisorOfEmployee($conn, $user_id, (int)$evaluation['employee_id']);
        
        if (!$is_authorized) {
            redirectWith(BASE_URL . '/employee/dashboard.php', 'danger', 'You are not authorized to confirm this rating.');
        }

        // ── Hierarchy sequential-step guard ─────────────────────────────────
        // A Branch Manager (rank 3) must NOT act on evaluations still at
        // 'Pending Dept Supervisor' — that step belongs exclusively to the
        // Branch Supervisor (rank 4). Prevent direct-URL bypass.
        $actor_rank_stmt = $conn->prepare("SELECT rank_category_id FROM employees WHERE employee_id = ? LIMIT 1");
        $actor_rank_stmt->bind_param("i", $supervisor_employee_id);
        $actor_rank_stmt->execute();
        $actor_rank_row = $actor_rank_stmt->get_result()->fetch_assoc();
        $actor_rank_stmt->close();
        $actor_rank = $actor_rank_row ? (int)$actor_rank_row['rank_category_id'] : 0;

        if ($actor_rank === 3 && in_array($evaluation['status'], ['Pending Dept Supervisor', 'Pending Supervisor'], true)) {
            // Only block rank-3 managers if a real rank-4 supervisor exists in this branch.
            // Manager-only departments (no rank-4 supervisor) are allowed to act directly.
            $branch_has_real_supervisor = getDeptSupervisorOfEmployee($conn, (int)$evaluation['employee_id']) !== null;
            if ($branch_has_real_supervisor) {
                redirectWith(BASE_URL . '/employee/dashboard.php', 'danger', 'This evaluation is pending Branch Supervisor review. You may only access it after the Branch Supervisor has confirmed it.');
            }
        }
        
        $dept_manager = getDeptManagerOfEmployee($conn, (int)$evaluation['employee_id']);
        
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

$readonly_attr = !empty($is_readonly) ? 'readonly' : '';
$disabled_attr = !empty($is_readonly) ? 'disabled' : '';

// Handle form submission (confirmation or sending to HR)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $evaluation && $is_supervisor && $is_readonly) {
    redirectWith(BASE_URL . '/employee/confirm-rating.php?evaluation_id=' . $evaluation_id, 'warning', 'This confirmation has already been sent and is view-only.');
}

// POST-level sequential-step guard: re-check actor rank on submission
// to block any forged POST requests from Branch Managers on Supervisor-stage evaluations.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $evaluation) {
    if (isset($actor_rank) && $actor_rank === 3 && in_array($evaluation['status'], ['Pending Dept Supervisor', 'Pending Supervisor'], true)) {
        $branch_has_real_supervisor_post = getDeptSupervisorOfEmployee($conn, (int)$evaluation['employee_id']) !== null;
        if ($branch_has_real_supervisor_post) {
            redirectWith(BASE_URL . '/employee/dashboard.php', 'danger', 'Unauthorized: This evaluation must be reviewed by the Branch Supervisor first.');
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $evaluation && $is_supervisor && !$is_readonly) {
    $action = $_POST['confirm_action'] ?? '';
    $supervisor_comments = trim($_POST['supervisor_comments'] ?? '');
    $supervisor_altered = false;
    
    if ($action === 'confirm_and_send') {
        // Supervisor can alter scores if needed
        $kra_scores = $_POST['kra_scores'] ?? [];
        $beh_scores = $_POST['beh_scores'] ?? [];
        
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

        // Check if scores were altered
        $altered_details = [];
        foreach ($all_scores as $criterion_id => $new_rating) {
            $original_rating = (float)($scores[$criterion_id]['score_value'] ?? 0);
            
            if (abs($new_rating - $original_rating) > 0.01) {
                $supervisor_altered = true;
                $criterion_name = $scores[$criterion_id]['criterion_name'] ?? 'Criterion #' . $criterion_id;
                $altered_details[] = "$criterion_name (Self-Rating: " . number_format($original_rating, 2) . " -> Adjusted: " . number_format($new_rating, 2) . ")";
                // Update the score
                $update_score = $conn->prepare("
                    UPDATE evaluation_scores 
                    SET score_value = ?, weighted_score = ?,
                        supervisor_override_score = NULL, supervisor_override_by = NULL, supervisor_override_at = NULL,
                        manager_override_score = NULL, manager_override_by = NULL, manager_override_at = NULL
                    WHERE evaluation_id = ? AND criterion_id = ?
                ");
                $weight = (float)($scores[$criterion_id]['weight'] ?? 0);
                $is_behavior = ($scores[$criterion_id]['section'] ?? '') === 'Behavior';
                $weighted = $is_behavior ? $new_rating : round(($weight / 100) * $new_rating, 2);
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
        
        // Determine the next status based on roles and department structure
        $emp_hr_role = getEmployeeHRRole($conn, (int)$evaluation['employee_id']);
        
        if ($emp_hr_role === 'HR Manager') {
            $next_status = 'Approved';
        } elseif ($emp_hr_role === 'HR Staff') {
            $next_status = 'Pending Manager';
        } else {
            // Non-HR employee: check if there is a separate department manager
            $dept_managers = getDeptManagersOfEmployee($conn, (int)$evaluation['employee_id']);
            $is_emp_manager = isDeptManagerRole($conn, (int)$evaluation['employee_id']);
            $has_separate_manager = false;
            foreach ($dept_managers as $dm) {
                if ((int)$dm['supervisor_employee_id'] !== $supervisor_employee_id) {
                    $has_separate_manager = true;
                    break;
                }
            }
            if (!$is_emp_manager && !empty($dept_managers) && $has_separate_manager) {
                // Route to department manager
                $next_status = 'Pending Dept Manager';
            } else {
                // No separate department manager, route directly to HR Supervisor
                $next_status = 'Pending HR Consolidation';
            }
        }

        // Update evaluation status
        $sql = "
            UPDATE evaluations 
            SET status = ?,
                dept_supervisor_confirmed_by = ?,
                dept_supervisor_confirmed_date = NOW(),
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
                performance_level = ?,
                approved_by = " . ($next_status === 'Approved' ? '?' : 'approved_by') . ",
                approved_date = " . ($next_status === 'Approved' ? 'NOW()' : 'approved_date') . "
            WHERE evaluation_id = ?
        ";
        $update = $conn->prepare($sql);
        $altered_int = $supervisor_altered ? 1 : 0;
        
        if ($next_status === 'Approved') {
            $update->bind_param(
                "siiisdiddddsii",
                $next_status,
                $user_id,
                $user_id,
                $altered_int,
                $supervisor_comments,
                $new_supervisor_rating,
                $user_id,
                $new_total_score,
                $new_kra_subtotal,
                $new_behavior_average,
                $new_performance_level,
                $user_id, // approved_by
                $evaluation_id
            );
        } else {
            $update->bind_param(
                "siiisdiddddi",
                $next_status,
                $user_id,
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
        }
        $update->execute();
        $update->close();
        
        $emp_name = $evaluation['first_name'] . ' ' . $evaluation['last_name'];
        $supervisor_name = $_SESSION['full_name'] ?? 'Supervisor';

        if ($next_status === 'Pending Dept Manager') {
            // Notify Dept Managers
            $dept_managers = getDeptManagersOfEmployee($conn, (int)$evaluation['employee_id']);
            foreach ($dept_managers as $dm) {
                if (!empty($dm['user_id'])) {
                    createNotification(
                        $conn,
                        (int)$dm['user_id'],
                        'Evaluation Pending Endorsement',
                        $supervisor_name . ' confirmed self-rating for ' . $emp_name . ' and requires your endorsement.',
                        BASE_URL . '/employee/dept-manager-review.php?evaluation_id=' . $evaluation_id
                    );
                }
            }
        } elseif ($next_status === 'Pending Manager') {
            // Notify HR Manager
            $hr_users = $conn->query("SELECT user_id FROM users WHERE role = 'HR Manager' AND is_active = 1");
            while ($hr = $hr_users->fetch_assoc()) {
                createNotification(
                    $conn,
                    (int)$hr['user_id'],
                    'Evaluation Pending Approval',
                    $supervisor_name . ' confirmed self-rating for ' . $emp_name . ' (HR Staff) and requires your approval.',
                    BASE_URL . '/manager/pending-approvals.php'
                );
            }
        } elseif ($next_status === 'Pending HR Consolidation') {
            // Notify HR Supervisor only — HR Manager will be notified when HR Supervisor endorses (Pending Manager)
            $hr_users = $conn->query("SELECT user_id FROM users WHERE role = 'HR Supervisor' AND is_active = 1");
            while ($hr = $hr_users->fetch_assoc()) {
                createNotification(
                    $conn,
                    (int)$hr['user_id'],
                    'Rating Ready for Consolidation',
                    $supervisor_name . ' confirmed self-rating for ' . $emp_name . ($supervisor_altered ? ' (with alterations)' : ''),
                    BASE_URL . '/supervisor/pending-endorsements.php'
                );
            }
        }
        
        // Notify employee
        $emp_user = $conn->query("SELECT user_id FROM users WHERE employee_id = " . (int)$evaluation['employee_id'] . " AND role = 'Employee' AND is_active = 1 LIMIT 1")->fetch_assoc();
        if ($emp_user) {
            if ($next_status === 'Approved') {
                $msg = 'Your evaluation has been approved.';
            } elseif ($next_status === 'Pending Dept Manager') {
                $msg = 'Your supervisor has confirmed your self-rating and forwarded it to the Department Manager.';
            } elseif ($next_status === 'Pending Manager') {
                $msg = 'Your supervisor has confirmed your self-rating and forwarded it to the HR Manager.';
            } else {
                $msg = 'Your supervisor has confirmed your self-rating and sent it to HRD.';
            }
            createNotification(
                $conn,
                (int)$emp_user['user_id'],
                'Self-Rating Confirmed',
                $msg,
                BASE_URL . '/employee/self-rating.php'
            );
        }
        
        $audit_details = 'Confirmed self-rating. Status set to ' . $next_status;
        if ($supervisor_altered && !empty($altered_details)) {
            $audit_details .= ". Score adjustments:\n" . implode("\n", $altered_details);
        }
        logAudit($conn, $user_id, 'UPDATE', 'Evaluation', $evaluation_id, $audit_details);
        
        $success_msg = 'Self-rating confirmed successfully.';
        if ($next_status === 'Pending Dept Manager') {
            $success_msg = 'Self-rating confirmed and forwarded to Department Manager successfully.';
        } elseif ($next_status === 'Pending Manager') {
            $success_msg = 'Self-rating confirmed and forwarded to HR Manager successfully.';
        } elseif ($next_status === 'Approved') {
            $success_msg = 'Evaluation confirmed and approved successfully.';
        } else {
            $success_msg = 'Self-rating confirmed and sent to HRD successfully.';
        }
        redirectWith(BASE_URL . '/employee/confirm-rating.php?evaluation_id=' . $evaluation_id, 'success', $success_msg);
    } elseif ($action === 'reject') {
        if (empty($supervisor_comments)) {
            redirectWith(BASE_URL . '/employee/confirm-rating.php?evaluation_id=' . $evaluation_id, 'danger', 'Comments/rejection reason is required.');
        }

        // Return the evaluation to the employee for revision.
        // Status = 'Returned' removes it from Pending Confirmations and sends it back to the employee.
        $update = $conn->prepare("
            UPDATE evaluations
            SET status = 'Returned',
                supervisor_comments = ?,
                dept_supervisor_confirmed_by = NULL,
                dept_supervisor_confirmed_date = NULL,
                supervisor_confirmed_by = NULL,
                supervisor_confirmed_date = NULL
            WHERE evaluation_id = ?
        ");
        $update->bind_param("si", $supervisor_comments, $evaluation_id);
        $update->execute();
        $update->close();

        // Notify the employee their self-rating was returned for revision
        $emp_user = $conn->query("SELECT user_id FROM users WHERE employee_id = " . (int)$evaluation['employee_id'] . " AND role = 'Employee' AND is_active = 1 LIMIT 1")->fetch_assoc();
        $emp_name = $evaluation['first_name'] . ' ' . $evaluation['last_name'];
        if ($emp_user) {
            createNotification(
                $conn,
                (int)$emp_user['user_id'],
                'Self-Rating Returned for Revision',
                ($_SESSION['full_name'] ?? 'Your Immediate Head') . ' returned your self-rating for revision. Please review the comments and resubmit.',
                BASE_URL . '/employee/self-rating.php'
            );
        }

        logAudit($conn, $user_id, 'UPDATE', 'Evaluation', $evaluation_id, 'Immediate Head returned self-rating to employee for revision. Status: Returned.');
        redirectWith(BASE_URL . '/employee/confirm-rating.php', 'warning', 'Evaluation returned to employee for revision.');
    }
}

// Fetch pending confirmations for this supervisor
$pending_confirmations = [];
$confirmation_history = [];
if ($is_supervisor) {
    // Fetch supervisor's branch AND rank so we can correctly scope the pending list
    $sup_info_stmt = $conn->prepare("SELECT branch_id, rank_category_id FROM employees WHERE employee_id = ? LIMIT 1");
    $sup_info_stmt->bind_param("i", $supervisor_employee_id);
    $sup_info_stmt->execute();
    $sup_info_row = $sup_info_stmt->get_result()->fetch_assoc();
    $supervisor_branch_id = $sup_info_row ? (int)$sup_info_row['branch_id'] : 0;
    $supervisor_rank      = $sup_info_row ? (int)$sup_info_row['rank_category_id'] : 0;
    $sup_info_stmt->close();

    $pending_stmt = $conn->prepare("
        SELECT e.*, emp.first_name, emp.last_name, emp.job_title, emp.employee_code,
               emp.reports_to, emp.branch_id, emp.rank_category_id,
               t.template_name
        FROM evaluations e
        JOIN employees emp ON e.employee_id = emp.employee_id
        LEFT JOIN evaluation_templates t ON e.template_id = t.template_id
        WHERE e.status IN ('Pending Dept Supervisor', 'Pending Supervisor') 
          AND emp.employee_id <> ?
          AND emp.employee_id NOT IN (
              SELECT employee_id
              FROM users
              WHERE role IN ('HR Staff', 'HR Supervisor', 'HR Manager')
                AND employee_id IS NOT NULL
          )
          AND NOT EXISTS (
              SELECT 1
              FROM departments hd
              JOIN branches hb ON hb.branch_id = emp.branch_id
              WHERE hd.department_id = emp.department_id
                AND (
                    LOWER(TRIM(hd.department_name)) IN ('human resources', 'human resource', 'hr')
                    OR LOWER(hd.department_name) LIKE '%human resources%'
                )
                AND (LOWER(hb.branch_name) LIKE '%main%' OR LOWER(hb.branch_name) LIKE '%head office%')
          )
          AND (
            emp.reports_to = ?
            OR emp.branch_id = ?
          )
        ORDER BY e.submitted_date DESC
    ");
    $pending_stmt->bind_param("iii", $supervisor_employee_id, $supervisor_employee_id, $supervisor_branch_id);
    $pending_stmt->execute();
    $all_pending = $pending_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $pending_stmt->close();

    $pending_confirmations = [];

    foreach ($all_pending as $p) {
        if (isSupervisorOfEmployee($conn, $user_id, (int)$p['employee_id'])) {
            $pending_confirmations[] = $p;
        }
    }

    // Fetch confirmation history where supervisor had historically confirmed
    $history_stmt = $conn->prepare("
        SELECT e.*, emp.first_name, emp.last_name, emp.job_title, emp.employee_code,
               t.template_name
        FROM evaluations e
        JOIN employees emp ON e.employee_id = emp.employee_id
        LEFT JOIN evaluation_templates t ON e.template_id = t.template_id
        WHERE (e.dept_supervisor_confirmed_by = ? OR e.supervisor_confirmed_by = ?)
        ORDER BY COALESCE(e.dept_supervisor_confirmed_date, e.supervisor_confirmed_date) DESC
    ");
    $history_stmt->bind_param("ii", $user_id, $user_id);
    $history_stmt->execute();
    $confirmation_history = $history_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $history_stmt->close();
}

// Pre-compute PHP-side KRA total and behavior average for the "original" summary card
$orig_kra_total = 0;
foreach ($criteria_kra as $c) {
    $orig_kra_total += round(($c['weight'] / 100) * (float)$c['score_value'], 4);
}
$orig_kra_total = round($orig_kra_total, 2);

$orig_beh_avg = 0;
if (!empty($criteria_behavior)) {
    $orig_beh_avg = round(array_sum(array_column($criteria_behavior, 'score_value')) / count($criteria_behavior), 2);
}

$kra_weight_display = (float)($evaluation['kra_weight'] ?? 80);
$beh_weight_display = (float)($evaluation['behavior_weight'] ?? 20);

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
        <div class="d-none d-md-block text-end">
            <a href="<?php echo BASE_URL; ?>/employee/dashboard.php" class="btn btn-outline-light btn-sm rounded-pill px-3 mb-2">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>
</div>

<!-- Mobile-only section -->
<div class="d-md-none d-flex justify-content-between align-items-center mt-3 mb-4 flex-wrap gap-3 fadeup" style="animation-delay: 0.1s;">
    <a href="<?php echo BASE_URL; ?>/employee/dashboard.php" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
        <i class="fas fa-arrow-left me-2"></i>Back to My Dashboard
    </a>
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

    <!-- List of pending confirmations -->
    <div class="content-card fadeup-1">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
            <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Pending Confirmations</h5>
            <div class="d-flex align-items-center gap-2">
                <?php if (!empty($pending_confirmations)): ?>
                    <span class="badge bg-warning text-dark"><?php echo count($pending_confirmations); ?> pending</span>
                <?php endif; ?>
                <button class="btn btn-sm btn-outline-secondary" type="button"
                        data-bs-toggle="collapse" data-bs-target="#confirmationHistoryPanel"
                        aria-expanded="false" aria-controls="confirmationHistoryPanel"
                        title="View Confirmation History" id="historyGearBtn">
                    <i class="fas fa-cog me-1"></i>
                    <span class="d-none d-sm-inline">History</span>
                    <?php if (!empty($confirmation_history)): ?>
                        <span class="badge bg-secondary ms-1"><?php echo count($confirmation_history); ?></span>
                    <?php endif; ?>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (empty($pending_confirmations)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-check-circle fa-3x mb-3"></i>
                    <p class="mb-0">No pending self-ratings to confirm.</p>
                    <div class="small mt-1">All caught up! Click the <i class="fas fa-cog"></i> gear icon to view past confirmations.</div>
                </div>
            <?php else:
                // Group pending confirmations by employee_id
                $grouped_confirmations = [];
                foreach ($pending_confirmations as $p) {
                    $grouped_confirmations[$p['employee_id']][] = $p;
                }
            ?>
                <div class="px-3 pt-3 pb-1">
                <?php foreach ($grouped_confirmations as $emp_id => $entries):
                    $first = $entries[0];
                    $count = count($entries);
                    $capsule_id = 'capsule_' . (int)$emp_id;
                    $is_open = $count === 1; // auto-expand single entries
                ?>
                    <div class="emp-capsule">
                        <!-- Capsule Header -->
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
        <!-- Confirmation History panel — hidden by default, toggled by gear icon -->
        <div class="collapse" id="confirmationHistoryPanel">
            <div class="border-top">
                <div class="px-3 py-2 bg-light d-flex align-items-center justify-content-between">
                    <span class="fw-semibold text-secondary" style="font-size:.85rem;">
                        <i class="fas fa-history me-1"></i>Confirmation History
                    </span>
                    <span class="badge bg-secondary">
                        <?php echo count($confirmation_history); ?> record<?php echo count($confirmation_history) !== 1 ? 's' : ''; ?>
                    </span>
                </div>
                <?php if (empty($confirmation_history)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p class="mb-0 small">No confirmation history yet.</p>
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
                                    <th class="text-center">Confirmed On</th>
                                    <th class="text-center">Scores Altered?</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($confirmation_history as $hist):
                                    $confirmed_date = $hist['dept_supervisor_confirmed_date'] ?? $hist['supervisor_confirmed_date'] ?? null;
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
                                        <td data-label="Confirmed On" class="small text-muted">
                                            <?php echo $confirmed_date ? formatDateTime($confirmed_date) : '—'; ?>
                                        </td>
                                        <td data-label="Scores Altered?">
                                            <?php if (!empty($hist['supervisor_altered_scores'])): ?>
                                                <span class="badge bg-warning text-dark" title="You adjusted the employee's original scores">
                                                    <i class="fas fa-pen me-1"></i>Yes
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-light text-secondary">No</span>
                                            <?php endif; ?>
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
        const panel = document.getElementById('confirmationHistoryPanel');
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
    <!-- Review and Confirm Form -->

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
        margin-left: 6px;
        white-space: nowrap;
    }
    .change-badge .arrow { font-size: .7rem; }
    
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
    
    .final-grade-card .grade-table tbody tr td,
    .final-grade-card .grade-table thead tr th {
        background-color: transparent !important;
        background: transparent !important;
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
        --bs-table-color: #ffffff !important;
        margin-bottom: 0 !important;
        border-color: rgba(203,161,53,.22) !important;
    }
    .final-grade-card .grade-table th,
    .final-grade-card .grade-table td {
        border-color: rgba(203,161,53,.22) !important;
        font-size: .88rem !important;
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
            border-top: none !important;
        }
        .final-grade-value {
            font-size: 3.5rem !important;
        }

        /* Hide table headers */
        #kraTable thead, #behTable thead {
            display: none !important;
        }
        
        /* Make table and body block */
        #kraTable, #behTable, 
        #kraTable tbody, #behTable tbody {
            display: block !important;
            width: 100% !important;
        }
        
        /* Make each row a card */
        #kraTable tr.kra-row, #behTable tr.beh-row {
            display: block !important;
            background: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 14px !important;
            padding: 16px !important;
            margin-bottom: 16px !important;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.02) !important;
            transition: all 0.2s ease !important;
        }
        
        #kraTable tr.kra-row.score-changed, #behTable tr.beh-row.score-changed {
            background: #fffdf5 !important;
            border-left: 5px solid #f59e0b !important;
        }
        
        /* Make table cells act as row items inside the card */
        #kraTable tr.kra-row td, #behTable tr.beh-row td {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            padding: 8px 0 !important;
            border: none !important;
            width: 100% !important;
            border-bottom: 1px solid #f1f5f9 !important;
            background: transparent !important;
        }
        
        /* The first cell (Criterion name/description) should be full width block at the top of the card */
        #kraTable tr.kra-row td:first-child, #behTable tr.beh-row td:first-child {
            display: block !important;
            padding-top: 0 !important;
            padding-bottom: 12px !important;
            border-bottom: 1.5px solid #e2e8f0 !important;
            margin-bottom: 8px !important;
            text-align: left !important;
        }
        
        #kraTable tr.kra-row td:first-child::before, #behTable tr.beh-row td:first-child::before {
            display: none !important;
        }
        
        /* The last cell in the card doesn't need a bottom border */
        #kraTable tr.kra-row td:last-child, #behTable tr.beh-row td:last-child {
            border-bottom: none !important;
            padding-bottom: 0 !important;
        }
        
        /* Add labels on mobile via data-label */
        #kraTable tr.kra-row td[data-label]::before, #behTable tr.beh-row td[data-label]::before {
            content: attr(data-label) !important;
            font-weight: 700 !important;
            color: #64748b !important;
            font-size: 0.75rem !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
        }
        
        /* Adjust input sizing and interaction on mobile */
        .kra-input, .beh-input {
            max-width: 110px !important;
            height: 38px !important;
            font-size: 0.95rem !important;
            border-radius: 8px !important;
            padding: 4px 8px !important;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
            border: 1.5px solid #cbd5e1 !important;
        }
        
        /* Table footer totals as a card too */
        #kraTable tfoot, #behTable tfoot,
        #kraTable tfoot tr, #behTable tfoot tr {
            display: block !important;
            width: 100% !important;
        }
        
        #kraTable tfoot tr.total-row, #behTable tfoot tr.total-row {
            background: #f8fbf4 !important;
            border: 1px solid rgba(203, 161, 53, 0.45) !important;
            border-radius: 10px !important;
            padding: 12px 16px !important;
            margin-top: 8px !important;
        }
        
        #kraTable tfoot tr.total-row td, #behTable tfoot tr.total-row td {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            background: transparent !important;
            border: none !important;
            padding: 4px 0 !important;
            width: 100% !important;
            font-size: 0.9rem !important;
        }
        
        #kraTable tfoot tr.total-row td:first-child,
        #behTable tfoot tr.total-row td:first-child {
            display: none !important; /* Hide the colspan cell */
        }
        
        #kraTable tfoot tr.total-row td#kraTotal, #behTable tfoot tr.total-row td#behAvg {
            font-size: 1.1rem !important;
            font-weight: 900 !important;
            color: #082E06 !important;
        }
        
        #kraTable tfoot tr.total-row td#kraTotal::before {
            content: "KRA Final Score:" !important;
            color: #082E06 !important;
            font-weight: 700 !important;
            font-size: 0.8rem !important;
            text-transform: uppercase !important;
        }
        
        #behTable tfoot tr.total-row td#behAvg::before {
            content: "Behavior Final Avg:" !important;
            color: #082E06 !important;
            font-weight: 700 !important;
            font-size: 0.8rem !important;
            text-transform: uppercase !important;
        }
        
        .change-badge {
            margin-left: 0 !important;
            margin-top: 4px !important;
            font-size: 0.68rem !important;
        }
    }
    </style>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="content-card fadeup-1">
                <div class="card-header">
                    <h5><i class="fas fa-star me-2"></i>Review Self-Rating</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" id="confirmForm">
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
                            <div class="col-12">
                                <label class="form-label">
                                    <i class="fas fa-file-alt me-1 text-muted"></i>Template
                                </label>
                                <input type="text" class="form-control fw-semibold"
                                       value="<?php echo e($evaluation['template_name'] ?? 'N/A'); ?>" readonly
                                       style="background: #f8f9fa; color: #495057; border-color: #dee2e6;">
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
                            <div class="rating-section">
                                <h2 class="rating-section-title">
                                    <i class="fas fa-bullseye me-2" aria-hidden="true"></i>KRA Ratings
                                    <span class="ms-2 text-muted fw-normal" style="font-size:.8rem;">(You can adjust if needed)</span>
                                </h2>

                                <?php foreach ($criteria_kra as $criterion): 
                                    $orig = (float)$criterion['score_value'];
                                    $weight = (float)$criterion['weight'];
                                    $weighted_orig = round(($weight / 100) * $orig, 2);
                                ?>
                                    <div class="rating-item kra-row"
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
                                                <span class="text-muted small fw-semibold">Employee Rating:</span>
                                                <span class="badge bg-light text-dark fs-6 orig-val border">
                                                    <?php echo number_format($orig, 2); ?>
                                                </span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <label class="text-muted small fw-semibold mb-0" for="kra_<?php echo (int)$criterion['criterion_id']; ?>">Your Rating:</label>
                                                <input type="number" class="form-control kra-input" 
                                                       name="kra_scores[<?php echo (int)$criterion['criterion_id']; ?>]"
                                                       id="kra_<?php echo (int)$criterion['criterion_id']; ?>"
                                                       min="0" max="4" step="0.01"
                                                       value="<?php echo number_format($orig, 2); ?>"
                                                       placeholder="0.00 – 4.00"
                                                       oninput="if(parseFloat(this.value) > 4) this.value = 4;"
                                                       <?php echo $disabled_attr; ?>
                                                       style="max-width: 90px; text-align: center;">
                                                <div class="change-badge d-none" id="chg_kra_<?php echo (int)$criterion['criterion_id']; ?>">
                                                    <i class="fas fa-edit"></i> Changed
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
                                    <span class="ms-2 text-muted fw-normal" style="font-size:.8rem;">(You can adjust if needed)</span>
                                </h2>

                                <?php foreach ($criteria_behavior as $criterion):
                                    $orig = (float)$criterion['score_value'];
                                ?>
                                    <div class="rating-item beh-row"
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
                                                <span class="text-muted small fw-semibold">Employee Rating:</span>
                                                <span class="badge bg-light text-dark fs-6 orig-val border">
                                                    <?php echo number_format($orig, 2); ?>
                                                </span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <label class="text-muted small fw-semibold mb-0" for="beh_<?php echo (int)$criterion['criterion_id']; ?>">Your Rating:</label>
                                                <input type="number" class="form-control beh-input" 
                                                       name="beh_scores[<?php echo (int)$criterion['criterion_id']; ?>]"
                                                       id="beh_<?php echo (int)$criterion['criterion_id']; ?>"
                                                       min="0" max="4" step="0.01"
                                                       value="<?php echo number_format($orig, 2); ?>"
                                                       placeholder="0.00 – 4.00"
                                                       oninput="if(parseFloat(this.value) > 4) this.value = 4;"
                                                       <?php echo $disabled_attr; ?>
                                                       style="max-width: 90px; text-align: center;">
                                                <div class="change-badge d-none" id="chg_beh_<?php echo (int)$criterion['criterion_id']; ?>">
                                                    <i class="fas fa-edit"></i> Changed
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
                                <div class="col-md-8">
                                    <table class="table grade-table mb-0">
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
                                <strong>Scores have been adjusted.</strong> The final grade reflects your changes.
                            </div>
                        </div>

                        <!-- Supervisor Comments -->
                        <div class="mb-4 mt-4">
                            <label class="form-label fw-semibold">Your Comments / Justification for Changes</label>
                            <textarea class="form-control" name="supervisor_comments" rows="4" 
                                      placeholder="Enter your comments or justification for any rating adjustments..."
                                      <?php echo $readonly_attr; ?>><?php echo !empty($is_readonly) ? e($evaluation['supervisor_comments'] ?? '') : ''; ?></textarea>
                            <div class="form-text">
                                <?php echo !empty($is_readonly) ? 'View-only. This confirmation has already been sent forward.' : 'Optional. This will be visible to HR and the employee.'; ?>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex flex-wrap justify-content-between gap-2 mt-3">
                            <a href="<?php echo BASE_URL; ?>/employee/confirm-rating.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to List
                            </a>
                            <?php if (!empty($is_readonly)): ?>
                                <span class="badge bg-secondary align-self-center px-3 py-2">
                                    <i class="fas fa-eye me-1"></i>View-only
                                </span>
                            <?php else: ?>
                                <div class="d-flex flex-wrap gap-2 w-100 w-sm-auto justify-content-end">
                                    <button type="submit" name="confirm_action" value="reject" class="btn btn-warning text-dark flex-fill flex-sm-grow-0"
                                            onclick="return confirm('Are you sure you want to reject this self-rating? It goes back to employee for revision. Comments/justification are required.');">
                                        <i class="fas fa-times-circle me-1"></i>Return for Revision
                                    </button>
                                    <button type="submit" name="confirm_action" value="confirm_and_send" 
                                            class="btn btn-primary flex-fill flex-sm-grow-0" id="submitBtn"
                                            onclick="return confirm('Confirm this self-rating and send to branch manager?');">
                                        <i class="fas fa-check-circle me-2"></i>Confirm &amp; Send
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
    const kraWeight   = <?php echo $kra_weight_display; ?> / 100;
    const behWeight   = <?php echo $beh_weight_display; ?> / 100;

    function getPerformanceLabel(score) {
        if (score >= 3.60) return 'Outstanding';
        if (score >= 2.60) return 'Exceeds Expectations';
        if (score >= 2.00) return 'Meets Expectations';
        return 'Needs Improvement';
    }

    function recalculate() {
        // --- KRA ---
        let kraTotal = 0;
        let anyChanged = false;

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
            badge.classList.toggle('d-none', !changed);
            row.classList.toggle('score-changed', changed);
        });

        // --- Behavior ---
        let behSum = 0, behCount = 0;
        document.querySelectorAll('.beh-row').forEach(row => {
            const orig = parseFloat(row.dataset.orig);
            const cid  = row.dataset.criterion;
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
            badge.classList.toggle('d-none', !changed);
            row.classList.toggle('score-changed', changed);
        });

        const kraRounded  = Math.round(kraTotal * 100) / 100;
        const behAvg      = behCount > 0 ? Math.round((behSum / behCount) * 100) / 100 : 0;
        const finalGrade  = Math.round((kraRounded * kraWeight + behAvg * behWeight) * 100) / 100;

        // Update KRA total row
        const kraEl = document.getElementById('kraTotal');
        if (kraEl) kraEl.textContent = kraRounded.toFixed(2);

        // Update Behavior average row
        const behEl = document.getElementById('behAvg');
        if (behEl) behEl.textContent = behAvg.toFixed(2);

        // Update final grade card
        const fpgKRA = document.getElementById('fpgKRA');
        const fpgKRAW = document.getElementById('fpgKRAWeighted');
        const fpgBeh = document.getElementById('fpgBeh');
        const fpgBehW = document.getElementById('fpgBehWeighted');
        const fpgTotal = document.getElementById('fpgTotal');
        const fpgLevel = document.getElementById('fpgLevel');
        const alteredNotice = document.getElementById('alteredNotice');

        if (fpgKRA) fpgKRA.textContent = kraRounded.toFixed(2);
        if (fpgKRAW) fpgKRAW.textContent = (kraRounded * kraWeight).toFixed(2);
        if (fpgBeh) fpgBeh.textContent = behAvg.toFixed(2);
        if (fpgBehW) fpgBehW.textContent = (behAvg * behWeight).toFixed(2);
        if (fpgTotal) fpgTotal.textContent = finalGrade.toFixed(2);
        if (fpgLevel) fpgLevel.textContent = getPerformanceLabel(finalGrade);
        if (alteredNotice) alteredNotice.classList.toggle('d-none', !anyChanged);
    }

    // Attach listeners
    document.querySelectorAll('.kra-input, .beh-input').forEach(inp => {
        inp.addEventListener('input', recalculate);
    });

    // Initial run
    recalculate();
})();

// Real-time status polling to check if another supervisor has confirmed or returned the evaluation
document.addEventListener('DOMContentLoaded', function () {
    const evaluationId = <?php echo $evaluation_id; ?>;
    const isReadonly = <?php echo $is_readonly ? 'true' : 'false'; ?>;
    
    if (evaluationId > 0 && !isReadonly) {
        let statusChecksFailed = 0;
        const statusPollInterval = setInterval(function() {
            fetch('../includes/ajax/check-evaluation-status.php?evaluation_id=' + evaluationId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        statusChecksFailed = 0;
                        const activeStatuses = ['Pending Dept Supervisor', 'Pending Supervisor'];
                        if (!activeStatuses.includes(data.status)) {
                            clearInterval(statusPollInterval);
                            showEvaluationLockedNotification();
                        }
                    }
                })
                .catch(err => {
                    statusChecksFailed++;
                    if (statusChecksFailed > 5) {
                        clearInterval(statusPollInterval);
                    }
                });
        }, 5000); // Check every 5 seconds
        
        function showEvaluationLockedNotification() {
            // Disable all forms and inputs
            document.querySelectorAll('input, select, textarea, button').forEach(el => {
                el.disabled = true;
                el.classList.add('disabled');
            });
            
            // Show a live toast using the system's showLiveToast helper
            if (typeof showLiveToast === 'function') {
                showLiveToast(
                    'Evaluation Locked', 
                    'This evaluation has just been confirmed or returned by another supervisor and is now read-only. Reloading...', 
                    ''
                );
            } else {
                alert('This evaluation has just been confirmed or returned by another supervisor and is now read-only.');
            }
            
            setTimeout(() => {
                window.location.reload();
            }, 4000);
        }
    }
});
</script>

<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
