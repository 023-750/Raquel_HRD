<?php
$page_title = 'Self Rating';
require_once '../includes/session-check.php';
checkRole(['Employee']);
require_once '../includes/functions.php';

ensureEvaluationWorkflowSchema($conn);

$employee_id = (int)($_SESSION['employee_id'] ?? 0);
$user_id = (int)($_SESSION['user_id'] ?? 0);

$employee_stmt = $conn->prepare("
    SELECT e.employee_id, e.employee_code, e.first_name, e.last_name, e.job_title, e.department_id, e.branch_id,
           d.department_name, b.branch_name
    FROM employees e
    LEFT JOIN departments d ON e.department_id = d.department_id
    LEFT JOIN branches b ON e.branch_id = b.branch_id
    WHERE e.employee_id = ?
    LIMIT 1
");
$employee_stmt->bind_param("i", $employee_id);
$employee_stmt->execute();
$employee = $employee_stmt->get_result()->fetch_assoc();
$employee_stmt->close();

if (!$employee) {
    redirectWith(BASE_URL . '/employee/dashboard.php', 'danger', 'No employee record found for self-rating.');
}

$edit_eval = null;
$view_eval = null;
$edit_scores = [];
$view_scores = [];
$selected_template_id = isset($_GET['template']) ? (int)$_GET['template'] : 0;
$view_mode = false;
$assigned_evaluations = null;
$is_assigned_edit = false;

if (isset($_GET['view']) && is_numeric($_GET['view'])) {
    $view_id = (int)$_GET['view'];
    $stmt = $conn->prepare("
        SELECT ev.*, et.template_name, et.kra_weight, et.behavior_weight, au.full_name AS assigned_by_name
        FROM evaluations ev
        LEFT JOIN evaluation_templates et ON ev.template_id = et.template_id
        LEFT JOIN users au ON ev.assigned_by = au.user_id
        WHERE ev.evaluation_id = ?
          AND ev.employee_id = ?
          AND (ev.submitted_by = ? OR ev.assigned_by IS NOT NULL)
        LIMIT 1
    ");
    $stmt->bind_param("iii", $view_id, $employee_id, $user_id);
    $stmt->execute();
    $view_eval = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($view_eval) {
        $view_mode = true;
        $selected_template_id = (int)$view_eval['template_id'];
        $score_rs = $conn->query("SELECT criterion_id, score_value, weighted_score FROM evaluation_scores WHERE evaluation_id = " . (int)$view_eval['evaluation_id']);
        while ($score = $score_rs->fetch_assoc()) {
            $view_scores[(int)$score['criterion_id']] = $score;
        }
    }
}

if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("
        SELECT ev.*, au.full_name AS assigned_by_name
        FROM evaluations ev
        LEFT JOIN users au ON ev.assigned_by = au.user_id
        WHERE ev.evaluation_id = ?
          AND ev.employee_id = ?
          AND (ev.submitted_by = ? OR ev.assigned_by IS NOT NULL)
          AND (
            ev.status IN ('Draft', 'Returned', 'Pending Self-Rating')
            OR (ev.status = 'Pending Supervisor' AND TIMESTAMPDIFF(HOUR, ev.submitted_date, NOW()) < 24)
          )
        LIMIT 1
    ");
    $stmt->bind_param("iii", $edit_id, $employee_id, $user_id);
    $stmt->execute();
    $edit_eval = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($edit_eval) {
        $is_assigned_edit = !empty($edit_eval['assigned_by']);
        $selected_template_id = (int)$edit_eval['template_id'];
        $score_rs = $conn->query("SELECT criterion_id, score_value FROM evaluation_scores WHERE evaluation_id = " . (int)$edit_eval['evaluation_id']);
        while ($score = $score_rs->fetch_assoc()) {
            $edit_scores[(int)$score['criterion_id']] = $score['score_value'];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $template_id = (int)($_POST['template_id'] ?? 0);
    $period_start = $_POST['period_start'] ?: null;
    $period_end = $_POST['period_end'] ?: null;
    $self_comments = trim($_POST['self_comments'] ?? '');
    $action = $_POST['submit_action'] ?? 'draft';
    $kra_scores = $_POST['kra_scores'] ?? [];
    $beh_scores = $_POST['beh_scores'] ?? [];
    $editing_id = (int)($_POST['edit_id'] ?? 0);
    $editable_eval = null;
    $is_assigned_submission = false;

    $errors = [];

    if ($editing_id > 0) {
        $stmt = $conn->prepare("
            SELECT evaluation_id, template_id, assigned_by, status, submitted_date
            FROM evaluations
            WHERE evaluation_id = ?
              AND employee_id = ?
              AND (submitted_by = ? OR assigned_by IS NOT NULL)
            LIMIT 1
        ");
        $stmt->bind_param("iii", $editing_id, $employee_id, $user_id);
        $stmt->execute();
        $editable_eval = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$editable_eval) {
            $errors[] = 'The selected self-rating could not be found.';
        } else {
            $is_assigned_submission = !empty($editable_eval['assigned_by']);
            $allowed_statuses = ['Draft', 'Returned', 'Pending Self-Rating'];
            $is_recent_submission = (
                $editable_eval['status'] === 'Pending Supervisor'
                && !empty($editable_eval['submitted_date'])
                && strtotime((string) $editable_eval['submitted_date']) > strtotime('-24 hours')
            );

            if (!in_array($editable_eval['status'], $allowed_statuses, true) && !$is_recent_submission) {
                $errors[] = 'This self-rating is no longer editable.';
            }

            if ($is_assigned_submission) {
                $template_id = (int)$editable_eval['template_id'];
            }
        }
    }

    if ($template_id <= 0) {
        $errors[] = 'Please select an evaluation template.';
    }

    $template_stmt = $conn->prepare("SELECT template_id, template_name, kra_weight, behavior_weight, evaluation_type FROM evaluation_templates WHERE template_id = ? AND status = 'Active' LIMIT 1");
    $template_stmt->bind_param("i", $template_id);
    $template_stmt->execute();
    $template = $template_stmt->get_result()->fetch_assoc();
    $template_stmt->close();

    if (!$template) {
        $errors[] = 'Selected template is not available.';
    }

    $evaluation_type = trim($template['evaluation_type'] ?? 'Annual');

    if ($action === 'submit') {
        $criteria_count_rs = $conn->query("SELECT COUNT(*) AS total FROM evaluation_criteria WHERE template_id = $template_id");
        $criteria_total = (int)($criteria_count_rs->fetch_assoc()['total'] ?? 0);
        if ($criteria_total <= 0) {
            $errors[] = 'This template has no criteria yet.';
        }
    }

    if (!empty($errors)) {
        redirectWith(BASE_URL . '/employee/self-rating.php' . ($editing_id ? '?edit=' . $editing_id : '?template=' . $template_id), 'danger', implode(' ', $errors));
    }

    $kra_weight_pct = (float)($template['kra_weight'] ?? 80);
    $beh_weight_pct = (float)($template['behavior_weight'] ?? 20);

    $kra_subtotal = 0;
    $kra_score_data = [];
    $kra_criteria = $conn->query("SELECT * FROM evaluation_criteria WHERE template_id = $template_id AND section='KRA' ORDER BY sort_order");
    while ($criterion = $kra_criteria->fetch_assoc()) {
        $criterion_id = (int)$criterion['criterion_id'];
        $rating = (float)($kra_scores[$criterion_id] ?? 0);
        if ($rating > 4.00) $rating = 4.00;
        if ($rating < 0) $rating = 0;
        $weight = (float)$criterion['weight'];
        $weighted = round(($weight / 100) * $rating, 2);
        $kra_subtotal += $weighted;
        $kra_score_data[] = ['criterion_id' => $criterion_id, 'score_value' => $rating, 'weighted_score' => $weighted];
    }
    $kra_subtotal = round($kra_subtotal, 2);

    $beh_score_data = [];
    $behavior_total = 0;
    $behavior_count = 0;
    $beh_criteria = $conn->query("SELECT * FROM evaluation_criteria WHERE template_id = $template_id AND section='Behavior' ORDER BY sort_order");
    while ($criterion = $beh_criteria->fetch_assoc()) {
        $criterion_id = (int)$criterion['criterion_id'];
        $rating = (float)($beh_scores[$criterion_id] ?? 0);
        if ($rating > 4.00) $rating = 4.00;
        if ($rating < 0) $rating = 0;
        $behavior_total += $rating;
        $behavior_count++;
        $beh_score_data[] = ['criterion_id' => $criterion_id, 'score_value' => $rating, 'weighted_score' => $rating];
    }
    $behavior_average = $behavior_count > 0 ? round($behavior_total / $behavior_count, 2) : 0;

    $total_score = calculateEvalTotal($kra_subtotal, $behavior_average, $kra_weight_pct, $beh_weight_pct);
    $performance_level = getPerformanceLevel($total_score);
    if ($is_assigned_submission) {
        $status = ($action === 'submit') ? 'Pending Supervisor' : 'Pending Self-Rating';
    } else {
        $status = ($action === 'submit') ? 'Pending Supervisor' : 'Draft';
    }
    $submitted_date = ($action === 'submit') ? date('Y-m-d H:i:s') : null;

    if ($editing_id > 0) {
        $stmt = $conn->prepare("
            UPDATE evaluations
            SET template_id=?, evaluation_type=?, evaluation_period_start=?, evaluation_period_end=?,
                status=?, total_score=?, kra_subtotal=?, behavior_average=?, performance_level=?,
                submitted_by=?, submitted_date=?, staff_comments=?, current_position=?, months_in_position=?,
                desired_position=?, target_date=?, career_growth_suited=?, career_growth_details=?
            WHERE evaluation_id=? AND employee_id=? AND (submitted_by=? OR assigned_by IS NOT NULL)
        ");
        $current_position = (string)($employee['job_title'] ?? '');
        $months_in_position = 0;
        $desired_position = '';
        $target_date = null;
        $career_growth_suited = 0;
        $career_growth_details = '';
        $stmt->bind_param("issssdddsssssissisiii", $template_id, $evaluation_type, $period_start, $period_end, $status, $total_score, $kra_subtotal, $behavior_average, $performance_level, $user_id, $submitted_date, $self_comments, $current_position, $months_in_position, $desired_position, $target_date, $career_growth_suited, $career_growth_details, $editing_id, $employee_id, $user_id);
        $stmt->execute();
        $stmt->close();

        $conn->query("DELETE FROM evaluation_scores WHERE evaluation_id = $editing_id");
        $eval_id = $editing_id;
    } else {
        $stmt = $conn->prepare("
            INSERT INTO evaluations (
                employee_id, template_id, evaluation_type, evaluation_period_start, evaluation_period_end,
                submitted_by, status, total_score, kra_subtotal, behavior_average, performance_level,
                submitted_date, staff_comments, current_position, months_in_position,
                desired_position, target_date, career_growth_suited, career_growth_details
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $current_position = (string)($employee['job_title'] ?? '');
        $months_in_position = 0;
        $desired_position = '';
        $target_date = null;
        $career_growth_suited = 0;
        $career_growth_details = '';
        $stmt->bind_param("iisssisdddssssissis", $employee_id, $template_id, $evaluation_type, $period_start, $period_end, $user_id, $status, $total_score, $kra_subtotal, $behavior_average, $performance_level, $submitted_date, $self_comments, $current_position, $months_in_position, $desired_position, $target_date, $career_growth_suited, $career_growth_details);
        $stmt->execute();
        $eval_id = (int)$stmt->insert_id;
        $stmt->close();
    }

    $score_stmt = $conn->prepare("INSERT INTO evaluation_scores (evaluation_id, criterion_id, score_value, weighted_score) VALUES (?, ?, ?, ?)");
    foreach (array_merge($kra_score_data, $beh_score_data) as $score_data) {
        $score_stmt->bind_param("iidd", $eval_id, $score_data['criterion_id'], $score_data['score_value'], $score_data['weighted_score']);
        $score_stmt->execute();
    }
    $score_stmt->close();

    if ($action === 'submit') {
        $employee_name = trim(($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? ''));

        // Notify employee's immediate supervisor (for 360-degree workflow)
        $supervisor_notified = notifySupervisorOfSelfRating($conn, $employee_id, $eval_id);

        // If no supervisor found, notify HR Supervisor as fallback
        if (!$supervisor_notified) {
            $hr_supervisors = $conn->query("SELECT user_id FROM users WHERE role = 'HR Supervisor' AND is_active = 1");
            while ($hr_sup = $hr_supervisors->fetch_assoc()) {
                createNotification(
                    $conn,
                    (int) $hr_sup['user_id'],
                    'Employee Self-Rating Submitted',
                    $employee_name . ' submitted a self-rating for review. (No supervisor assigned)',
                    BASE_URL . '/supervisor/pending-endorsements.php'
                );
            }
        }

        logAudit($conn, $user_id, 'CREATE', 'Evaluation', $eval_id, 'Submitted employee self-rating');
        redirectWith(BASE_URL . '/employee/self-rating.php', 'success', 'Your self-rating was submitted successfully. Awaiting supervisor confirmation.');
    }

    logAudit($conn, $user_id, 'CREATE', 'Evaluation', $eval_id, 'Saved employee self-rating draft');
    redirectWith(BASE_URL . '/employee/self-rating.php?edit=' . $eval_id, 'success', 'Your self-rating draft was saved.');
}

// Get employee's department name for template filtering
$employee_dept = $employee['department_name'] ?? '';

// Filter templates: show if target_position is empty, 'All Positions', or matches employee's department
$templates_stmt = $conn->prepare("
    SELECT template_id, template_name, kra_weight, behavior_weight, evaluation_type, target_position 
    FROM evaluation_templates 
    WHERE status = 'Active' 
      AND (target_position IS NULL OR target_position = '' OR target_position = 'All Positions' OR target_position = ?)
    ORDER BY template_name
");
$templates_stmt->bind_param("s", $employee_dept);
$templates_stmt->execute();
$templates = $templates_stmt->get_result();
$templates_stmt->close();

// Fetch selected template details for evaluation type (verify it's accessible to this employee)
$selected_template = null;
if ($selected_template_id > 0) {
    if ($is_assigned_edit) {
        $sel_template_stmt = $conn->prepare("
            SELECT template_id, template_name, evaluation_type, target_position
            FROM evaluation_templates
            WHERE template_id = ?
            LIMIT 1
        ");
        $sel_template_stmt->bind_param("i", $selected_template_id);
    } else {
        $sel_template_stmt = $conn->prepare("
            SELECT template_id, template_name, evaluation_type, target_position 
            FROM evaluation_templates 
            WHERE template_id = ? 
              AND (target_position IS NULL OR target_position = '' OR target_position = 'All Positions' OR target_position = ?)
            LIMIT 1
        ");
        $sel_template_stmt->bind_param("is", $selected_template_id, $employee_dept);
    }
    $sel_template_stmt->execute();
    $selected_template = $sel_template_stmt->get_result()->fetch_assoc();
    $sel_template_stmt->close();
    
    // If template not accessible, clear the selection
    if (!$selected_template) {
        $selected_template_id = 0;
    }
}

$assigned_evaluations = $conn->query("
    SELECT ev.evaluation_id, ev.evaluation_type, ev.status, ev.assigned_at, ev.updated_at,
           et.template_name, au.full_name AS assigned_by_name
    FROM evaluations ev
    LEFT JOIN evaluation_templates et ON ev.template_id = et.template_id
    LEFT JOIN users au ON ev.assigned_by = au.user_id
    WHERE ev.employee_id = $employee_id
      AND ev.assigned_by IS NOT NULL
      AND ev.status = 'Pending Self-Rating'
      AND ev.deleted_at IS NULL
    ORDER BY COALESCE(ev.assigned_at, ev.updated_at, ev.created_at) DESC, ev.evaluation_id DESC
");

// Check if employee has completed self-ratings
$completed_evaluations = $conn->query("
    SELECT e.evaluation_id, e.evaluation_type, e.status, et.template_name, e.total_score, e.performance_level, e.submitted_date
    FROM evaluations e
    LEFT JOIN evaluation_templates et ON e.template_id = et.template_id
    WHERE e.employee_id = $employee_id 
      AND e.submitted_by = $user_id
      AND e.status IN ('Approved', 'Pending Supervisor', 'Pending Manager', 'Pending HR Consolidation')
      AND e.deleted_at IS NULL
    ORDER BY e.evaluation_id DESC
");

$criteria_kra = [];
$criteria_behavior = [];
if ($selected_template_id > 0) {
    $criteria_query = $conn->query("SELECT * FROM evaluation_criteria WHERE template_id = " . $selected_template_id . " ORDER BY section, sort_order");
    while ($criterion = $criteria_query->fetch_assoc()) {
        if (($criterion['section'] ?? '') === 'Behavior') {
            $criteria_behavior[] = $criterion;
        } else {
            $criteria_kra[] = $criterion;
        }
    }
}

$history = $conn->query("
    SELECT ev.evaluation_id, ev.evaluation_type, ev.status, ev.total_score, ev.performance_level, ev.submitted_date, ev.updated_at,
           et.template_name
    FROM evaluations ev
    LEFT JOIN evaluation_templates et ON ev.template_id = et.template_id
    WHERE ev.employee_id = $employee_id AND ev.submitted_by = $user_id
    ORDER BY COALESCE(ev.submitted_date, ev.updated_at) DESC, ev.evaluation_id DESC
    LIMIT 10
");

require_once '../includes/header.php';
?>

<div class="page-hero fadeup">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-3">
        <div>
            <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.55);">Employee Portal · Evaluation</div>
            <h4 class="text-white fw-bold mb-0 mt-1"><i class="fas fa-star-half-alt me-2" style="color:var(--primary-light);"></i>360-Degree Self Rating</h4>
        </div>
        <div class="d-none d-md-block text-end">
            <a href="<?php echo BASE_URL; ?>/employee/dashboard.php" class="btn btn-outline-light btn-sm rounded-pill px-3 mb-1">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>
    <p class="text-white-50 small mb-0 d-none d-md-block"><i class="fas fa-info-circle me-1"></i>Complete your self-rating to provide insights for your performance review.</p>
</div>

<!-- Mobile-only section -->
<div class="d-md-none d-flex justify-content-between align-items-center mt-3 mb-4 flex-wrap gap-3 fadeup" style="animation-delay: 0.1s;">
    <a href="<?php echo BASE_URL; ?>/employee/dashboard.php" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
        <i class="fas fa-arrow-left me-2"></i>Back to My Dashboard
    </a>
    <div class="alert alert-light border-0 shadow-sm py-2 px-3 mb-0" style="border-radius: 10px; font-size: 0.85rem; background: #fff;">
        <i class="fas fa-info-circle me-2 text-primary"></i>
        <span class="text-muted fw-500">Complete your self-rating to provide insights.</span>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-8">
        <?php if ($assigned_evaluations && $assigned_evaluations->num_rows > 0 && !$view_mode): ?>
            <div class="content-card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-bell me-2"></i>Assigned Evaluations</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <?php while ($assigned_item = $assigned_evaluations->fetch_assoc()): ?>
                            <div class="border rounded p-3">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <div class="fw-semibold"><?php echo e($assigned_item['template_name'] ?? 'Assigned Template'); ?></div>
                                        <div class="small text-muted">
                                            Assigned by <?php echo e($assigned_item['assigned_by_name'] ?? 'your Head'); ?>
                                            <?php if (!empty($assigned_item['assigned_at'])): ?>
                                                on <?php echo formatDateTime($assigned_item['assigned_at']); ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="small text-muted mt-1"><?php echo e($assigned_item['evaluation_type'] ?? 'Annual'); ?> evaluation</div>
                                    </div>
                                    <span class="badge <?php echo getStatusBadgeClass($assigned_item['status']); ?>"><?php echo e($assigned_item['status']); ?></span>
                                </div>
                                <div class="mt-3">
                                    <a href="<?php echo BASE_URL; ?>/employee/self-rating.php?edit=<?php echo (int)$assigned_item['evaluation_id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-play me-1"></i>Start Self Rating
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($view_mode && $view_eval): ?>
            <!-- View Mode -->
            <div class="content-card">
                <div class="card-header">
                    <h5><i class="fas fa-eye me-2"></i>View Self Rating</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Employee</label>
                            <div class="fw-semibold"><?php echo e(trim(($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? ''))); ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Position</label>
                            <div class="fw-semibold"><?php echo e($employee['job_title'] ?? '—'); ?></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted">Evaluation Type</label>
                            <div class="fw-semibold"><?php echo e($view_eval['evaluation_type'] ?? '—'); ?></div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label text-muted">Evaluation Period</label>
                            <div class="fw-semibold">
                                <?php echo formatDate($view_eval['evaluation_period_start'] ?? ''); ?> - 
                                <?php echo formatDate($view_eval['evaluation_period_end'] ?? ''); ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Template</label>
                            <div class="fw-semibold"><?php echo e($view_eval['template_name'] ?? '—'); ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Status</label>
                            <div><span class="badge <?php echo getStatusBadgeClass($view_eval['status']); ?>"><?php echo e($view_eval['status']); ?></span></div>
                        </div>
                    </div>

                    <?php if (!empty($view_eval['staff_comments'])): ?>
                        <div class="alert alert-light border mb-4">
                            <label class="form-label fw-semibold">Self Comments:</label>
                            <p class="mb-0"><?php echo nl2br(e($view_eval['staff_comments'])); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php
                    $view_criteria_kra = [];
                    $view_criteria_behavior = [];
                    if ($selected_template_id > 0) {
                        $view_criteria_query = $conn->query("SELECT * FROM evaluation_criteria WHERE template_id = " . $selected_template_id . " ORDER BY section, sort_order");
                        while ($criterion = $view_criteria_query->fetch_assoc()) {
                            if (($criterion['section'] ?? '') === 'Behavior') {
                                $view_criteria_behavior[] = $criterion;
                            } else {
                                $view_criteria_kra[] = $criterion;
                            }
                        }
                    }
                    ?>

                    <?php if (!empty($view_criteria_kra)): ?>
                        <div class="section-premium-label mb-3">
                            <i class="fas fa-bullseye"></i>KRA Ratings
                        </div>
                        <div class="table-responsive mb-4">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Criterion</th>
                                        <th style="width:110px;">Weight</th>
                                        <th style="width:160px;">Rating</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($view_criteria_kra as $criterion): ?>
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
                                                    <?php echo e($view_scores[(int)$criterion['criterion_id']]['score_value'] ?? '0.00'); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($view_criteria_behavior)): ?>
                        <div class="section-premium-label mb-3">
                            <i class="fas fa-heart"></i>Behavior Ratings
                        </div>
                        <div class="table-responsive mb-4">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Criterion</th>
                                        <th style="width:160px;">Rating</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($view_criteria_behavior as $criterion): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-semibold"><?php echo e($criterion['criterion_name']); ?></div>
                                                <?php if (!empty($criterion['description'])): ?>
                                                    <div class="small text-muted"><?php echo e($criterion['description']); ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark fs-6">
                                                    <?php echo e($view_scores[(int)$criterion['criterion_id']]['score_value'] ?? '0.00'); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <div class="alert alert-info mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small text-muted">Total Score</div>
                                <div class="h4 mb-0"><?php echo e($view_eval['total_score'] ?? '0.00'); ?> 
                                    <span class="badge bg-primary"><?php echo e($view_eval['performance_level'] ?? '—'); ?></span>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="small text-muted">KRA: <?php echo e($view_eval['kra_subtotal'] ?? '0.00'); ?></div>
                                <div class="small text-muted">Behavior: <?php echo e($view_eval['behavior_average'] ?? '0.00'); ?></div>
                            </div>
                        </div>
                    </div>

                    <?php 
                    $can_edit = in_array($view_eval['status'], ['Draft', 'Returned', 'Pending Self-Rating'], true) || 
                               ($view_eval['status'] === 'Pending Supervisor' && 
                                strtotime($view_eval['submitted_date']) > strtotime('-24 hours'));
                    ?>
                    <div class="d-flex flex-wrap justify-content-between gap-2">
                        <a href="<?php echo BASE_URL; ?>/employee/self-rating.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back
                        </a>
                        <?php if ($can_edit): ?>
                            <a href="<?php echo BASE_URL; ?>/employee/self-rating.php?edit=<?php echo (int)$view_eval['evaluation_id']; ?>" class="btn btn-primary">
                                <i class="fas fa-edit me-2"></i>Edit Rating
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Edit/New Mode -->
            <div class="content-card">
                <div class="card-header">
                    <h5><i class="fas fa-star me-2"></i><?php echo $edit_eval ? 'Continue Self Rating' : 'New Self Rating'; ?></h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <?php if ($edit_eval): ?>
                            <input type="hidden" name="edit_id" value="<?php echo (int)$edit_eval['evaluation_id']; ?>">
                        <?php endif; ?>
                        <?php if ($is_assigned_edit && $edit_eval): ?>
                            <div class="alert alert-primary border-0 mb-4">
                                <div class="fw-semibold mb-1"><i class="fas fa-user-check me-2"></i>Assigned by Head</div>
                                <div class="small">
                                    <?php echo e($edit_eval['assigned_by_name'] ?? 'Your Head'); ?> assigned this evaluation to you.
                                    Complete your ratings, then submit it for review.
                                </div>
                            </div>
                        <?php endif; ?>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Employee</label>
                            <input type="text" class="form-control" value="<?php echo e(trim(($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? ''))); ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Position</label>
                            <input type="text" class="form-control" value="<?php echo e($employee['job_title'] ?? '—'); ?>" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Evaluation Type</label>
                            <?php
                            // Determine evaluation type: from existing evaluation, selected template, or default
                            if ($edit_eval) {
                                $display_eval_type = $edit_eval['evaluation_type'] ?? 'Annual';
                            } elseif ($selected_template) {
                                $display_eval_type = $selected_template['evaluation_type'] ?? 'Annual';
                            } else {
                                $display_eval_type = '—';
                            }
                            ?>
                            <input type="text" class="form-control" value="<?php echo e($display_eval_type); ?>" readonly>
                            <?php if ($edit_eval): ?>
                                <input type="hidden" name="evaluation_type" value="<?php echo e($edit_eval['evaluation_type'] ?? 'Annual'); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Evaluation Period</label>
                            <div class="input-group custom-period-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="far fa-calendar-alt text-primary-light"></i>
                                    <span class="ms-2 small text-muted">From</span>
                                </span>
                                <input type="date" class="form-control border-start-0 ps-1" name="period_start" value="<?php echo e($edit_eval['evaluation_period_start'] ?? ''); ?>" required>
                                <span class="input-group-text bg-light border-start-0 border-end-0 px-3">
                                    <i class="fas fa-arrow-right text-muted"></i>
                                </span>
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="far fa-calendar-check text-primary-light"></i>
                                    <span class="ms-2 small text-muted">To</span>
                                </span>
                                <input type="date" class="form-control border-start-0 ps-1" name="period_end" value="<?php echo e($edit_eval['evaluation_period_end'] ?? ''); ?>" required>
                            </div>
                            <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle me-1"></i>Select the inclusive dates for this evaluation period.</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Template</label>
                            <?php if ($is_assigned_edit && $selected_template): ?>
                                <input type="hidden" name="template_id" value="<?php echo (int)$selected_template['template_id']; ?>">
                                <input type="text" class="form-control" value="<?php echo e($selected_template['template_name']); ?>" readonly>
                                <small class="text-muted mt-1 d-block"><i class="fas fa-lock me-1"></i>This template was assigned by your Head and cannot be changed.</small>
                            <?php else: ?>
                                <select class="form-select" name="template_id" onchange="if(this.value){ window.location='?template=' + this.value <?php echo $edit_eval ? " + '&edit=" . (int)$edit_eval['evaluation_id'] . "'" : ''; ?>; } else { window.location='self-rating.php'; }" required>
                                    <option value="">Select Template</option>
                                    <?php while ($template = $templates->fetch_assoc()): ?>
                                        <option value="<?php echo (int)$template['template_id']; ?>" <?php echo $selected_template_id === (int)$template['template_id'] ? 'selected' : ''; ?>>
                                            <?php echo e($template['template_name']); ?> (<?php echo (float)$template['kra_weight']; ?>% KRA / <?php echo (float)$template['behavior_weight']; ?>% Behavior)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($selected_template_id > 0 && (!empty($criteria_kra) || !empty($criteria_behavior))): ?>
                        <div class="section-premium-label mb-3">
                            <i class="fas fa-bullseye"></i>KRA Self Rating
                        </div>
                        <div class="table-responsive mb-4">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Criterion</th>
                                        <th style="width:110px;">Weight</th>
                                        <th style="width:160px;">Your Rating</th>
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
                                                <input type="number" class="form-control" name="kra_scores[<?php echo (int)$criterion['criterion_id']; ?>]"
                                                       min="0" max="4" step="0.01"
                                                       value="<?php echo e($edit_scores[(int)$criterion['criterion_id']] ?? ''); ?>"
                                                       placeholder="0.00 - 4.00">
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="section-premium-label mb-3">
                            <i class="fas fa-heart"></i>Behavior Self Rating
                        </div>
                        <div class="table-responsive mb-4">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Criterion</th>
                                        <th style="width:160px;">Your Rating</th>
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
                                                <input type="number" class="form-control" name="beh_scores[<?php echo (int)$criterion['criterion_id']; ?>]"
                                                       min="0" max="4" step="0.01"
                                                       value="<?php echo e($edit_scores[(int)$criterion['criterion_id']] ?? ''); ?>"
                                                       placeholder="0.00 - 4.00">
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Self Comments</label>
                            <textarea class="form-control" name="self_comments" rows="4" placeholder="Share any notes about your self-rating..."><?php echo e($edit_eval['staff_comments'] ?? ''); ?></textarea>
                        </div>

                        <div class="d-flex flex-wrap justify-content-end gap-2">
                            <button type="submit" name="submit_action" value="draft" class="btn btn-outline-secondary">
                                <i class="fas fa-save me-2"></i>Save Draft
                            </button>
                            <button type="submit" name="submit_action" value="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Submit Self Rating
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-file-signature d-block"></i>
                            <p class="mb-0">Select an active template to start your self-rating.</p>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-xl-4">
        <div class="content-card mb-4">
            <div class="card-header">
                <h5><i class="fas fa-route me-2"></i>How It Works</h5>
            </div>
            <div class="card-body">
                <div class="small text-muted">
                    <p class="mb-2"><strong>1.</strong> Choose the active evaluation template.</p>
                    <p class="mb-2"><strong>2.</strong> Encode your self-rating and save a draft if needed.</p>
                    <p class="mb-2"><strong>3.</strong> Submit your self-rating to your Immediate Head.</p>
                    <p class="mb-2"><strong>4.</strong> Your supervisor confirms and may adjust ratings.</p>
                    <p class="mb-0"><strong>5.</strong> HRD consolidates the final rating.</p>
                </div>
            </div>
        </div>

        <div class="content-card">
            <div class="card-header">
                <h5><i class="fas fa-history me-2"></i>Recent Self Ratings</h5>
            </div>
            <div class="card-body">
                <?php if ($history->num_rows === 0): ?>
                    <div class="empty-state py-4">
                        <i class="fas fa-inbox d-block"></i>
                        <p class="mb-0">No self-ratings yet.</p>
                    </div>
                <?php else: ?>
                    <div class="d-grid gap-3">
                        <?php while ($item = $history->fetch_assoc()): ?>
                            <div class="border rounded p-3">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div>
                                        <div class="fw-semibold"><?php echo e($item['template_name'] ?? 'Template'); ?></div>
                                        <div class="small text-muted"><?php echo e($item['evaluation_type'] ?? 'Evaluation'); ?></div>
                                    </div>
                                    <span class="badge <?php echo getStatusBadgeClass($item['status']); ?>"><?php echo e($item['status']); ?></span>
                                </div>
                                <div class="small text-muted mt-2">
                                    Updated: <?php echo formatDateTime($item['updated_at'] ?? ''); ?>
                                </div>
                                <div class="small mt-1">
                                    Score: <strong><?php echo e($item['total_score'] ?? '0.00'); ?></strong>
                                    <?php if (!empty($item['performance_level'])): ?>
                                        <span class="text-muted">• <?php echo e($item['performance_level']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="mt-3">
                                    <a href="<?php echo BASE_URL; ?>/employee/self-rating.php?view=<?php echo (int)$item['evaluation_id']; ?>" class="btn btn-sm btn-outline-info me-2">
                                        <i class="fas fa-eye me-1"></i>View
                                    </a>
                                    <?php 
                                    $can_edit_item = in_array($item['status'], ['Draft', 'Returned', 'Pending Self-Rating'], true) || 
                                                      ($item['status'] === 'Pending Supervisor' && 
                                                       strtotime($item['submitted_date'] ?? $item['updated_at']) > strtotime('-24 hours'));
                                    ?>
                                    <?php if ($can_edit_item): ?>
                                        <a href="<?php echo BASE_URL; ?>/employee/self-rating.php?edit=<?php echo (int)$item['evaluation_id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
