<?php
$page_title = 'Assign Evaluation';
require_once '../includes/session-check.php';
checkRole(['HR Supervisor']);
require_once '../includes/functions.php';

ensureEvaluationWorkflowSchema($conn);

$supervisor_id = (int) ($_SESSION['user_id'] ?? 0);
$branch_id = (int) ($_SESSION['branch_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = (int) ($_POST['employee_id'] ?? 0);
    $template_id = (int) ($_POST['template_id'] ?? 0);
    $period_start = $_POST['period_start'] ?: null;
    $period_end = $_POST['period_end'] ?: null;
    $assignment_note = trim($_POST['assignment_note'] ?? '');

    $errors = [];

    if ($employee_id <= 0) {
        $errors[] = 'Please select an employee.';
    }

    if ($template_id <= 0) {
        $errors[] = 'Please select a template.';
    }

    $employee_stmt = $conn->prepare("
        SELECT e.employee_id, e.first_name, e.last_name, e.job_title, u.user_id AS employee_user_id
        FROM employees e
        LEFT JOIN users u ON e.employee_id = u.employee_id AND u.is_active = 1
        WHERE e.employee_id = ?
          AND e.branch_id = ?
          AND e.is_active = 1
          AND e.employee_id NOT IN (SELECT employee_id FROM users WHERE role = 'Admin' AND employee_id IS NOT NULL)
        LIMIT 1
    ");
    $employee_stmt->bind_param("ii", $employee_id, $branch_id);
    $employee_stmt->execute();
    $employee = $employee_stmt->get_result()->fetch_assoc();
    $employee_stmt->close();

    if (!$employee) {
        $errors[] = 'The selected employee is not available in your assigned branch.';
    }

    $template_stmt = $conn->prepare("
        SELECT template_id, template_name, evaluation_type
        FROM evaluation_templates
        WHERE template_id = ?
          AND status = 'Active'
        LIMIT 1
    ");
    $template_stmt->bind_param("i", $template_id);
    $template_stmt->execute();
    $template = $template_stmt->get_result()->fetch_assoc();
    $template_stmt->close();

    if (!$template) {
        $errors[] = 'The selected template is not active.';
    }

    if ($employee && $template) {
        $duplicate_stmt = $conn->prepare("
            SELECT evaluation_id
            FROM evaluations
            WHERE employee_id = ?
              AND template_id = ?
              AND assigned_by IS NOT NULL
              AND status IN ('Pending Self-Rating', 'Draft', 'Pending Supervisor')
              AND deleted_at IS NULL
            LIMIT 1
        ");
        $duplicate_stmt->bind_param("ii", $employee_id, $template_id);
        $duplicate_stmt->execute();
        $duplicate = $duplicate_stmt->get_result()->fetch_assoc();
        $duplicate_stmt->close();

        if ($duplicate) {
            $errors[] = 'There is already an open assigned evaluation for this employee and template.';
        }
    }

    if (!empty($errors)) {
        redirectWith(BASE_URL . '/supervisor/assign-evaluation.php', 'danger', implode(' ', $errors));
    }

    $evaluation_type = (string) ($template['evaluation_type'] ?? 'Annual');
    $current_position = (string) ($employee['job_title'] ?? '');
    $assigned_at = date('Y-m-d H:i:s');
    $status = 'Pending Self-Rating';

    $insert_stmt = $conn->prepare("
        INSERT INTO evaluations (
            employee_id, template_id, evaluation_type, evaluation_period_start, evaluation_period_end,
            assigned_by, assigned_at, status, evaluator_comments, current_position
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $insert_stmt->bind_param(
        "iisssissss",
        $employee_id,
        $template_id,
        $evaluation_type,
        $period_start,
        $period_end,
        $supervisor_id,
        $assigned_at,
        $status,
        $assignment_note,
        $current_position
    );
    $insert_stmt->execute();
    $evaluation_id = (int) $insert_stmt->insert_id;
    $insert_stmt->close();

    $employee_name = trim(($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? ''));

    if (!empty($employee['employee_user_id'])) {
        createNotification(
            $conn,
            (int) $employee['employee_user_id'],
            'Evaluation Assigned',
            'Your Head assigned a performance evaluation. Please complete your self-rating.',
            BASE_URL . '/employee/self-rating.php?edit=' . $evaluation_id
        );
    }

    logAudit($conn, $supervisor_id, 'CREATE', 'Evaluation', $evaluation_id, 'Assigned evaluation to ' . $employee_name);
    redirectWith(BASE_URL . '/supervisor/assign-evaluation.php', 'success', 'Evaluation assigned successfully.');
}

$employees_stmt = $conn->prepare("
    SELECT e.employee_id, e.employee_code, e.first_name, e.last_name, e.job_title, d.department_name
    FROM employees e
    LEFT JOIN departments d ON e.department_id = d.department_id
    WHERE e.branch_id = ?
      AND e.is_active = 1
      AND e.employee_id NOT IN (SELECT employee_id FROM users WHERE role = 'Admin' AND employee_id IS NOT NULL)
    ORDER BY e.first_name, e.last_name
");
$employees_stmt->bind_param("i", $branch_id);
$employees_stmt->execute();
$employees = $employees_stmt->get_result();
$employees_stmt->close();

$templates = $conn->query("
    SELECT template_id, template_name, evaluation_type, kra_weight, behavior_weight
    FROM evaluation_templates
    WHERE status = 'Active'
    ORDER BY template_name
");

$recent_assignments_stmt = $conn->prepare("
    SELECT ev.evaluation_id, ev.status, ev.assigned_at, ev.evaluation_period_start, ev.evaluation_period_end,
           emp.first_name, emp.last_name, et.template_name
    FROM evaluations ev
    INNER JOIN employees emp ON ev.employee_id = emp.employee_id
    INNER JOIN evaluation_templates et ON ev.template_id = et.template_id
    WHERE ev.assigned_by = ?
      AND ev.deleted_at IS NULL
    ORDER BY COALESCE(ev.assigned_at, ev.created_at) DESC, ev.evaluation_id DESC
    LIMIT 10
");
$recent_assignments_stmt->bind_param("i", $supervisor_id);
$recent_assignments_stmt->execute();
$recent_assignments = $recent_assignments_stmt->get_result();
$recent_assignments_stmt->close();

require_once '../includes/header.php';
?>

<div class="page-hero fadeup">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-3">
        <div>
            <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.55);">Supervisor Portal · Evaluation</div>
            <h4 class="text-white fw-bold mb-0 mt-1"><i class="fas fa-user-plus me-2" style="color:var(--primary-light);"></i>Assign Evaluation</h4>
        </div>
        <a href="<?php echo BASE_URL; ?>/supervisor/dashboard.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>
    <p class="text-white-50 small mb-0">Assign a template to an employee and move the workflow into the self-rating stage.</p>
</div>

<div class="row g-4">
    <div class="col-xl-7">
        <div class="content-card">
            <div class="card-header">
                <h5><i class="fas fa-clipboard-list me-2"></i>New Assignment</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Employee</label>
                            <select name="employee_id" class="form-select" required>
                                <option value="">Select employee</option>
                                <?php while ($employee = $employees->fetch_assoc()): ?>
                                    <option value="<?php echo (int) $employee['employee_id']; ?>">
                                        <?php echo e(trim(($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? ''))); ?>
                                        <?php if (!empty($employee['job_title'])): ?>
                                            - <?php echo e($employee['job_title']); ?>
                                        <?php endif; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Template</label>
                            <select name="template_id" class="form-select" required>
                                <option value="">Select template</option>
                                <?php while ($template = $templates->fetch_assoc()): ?>
                                    <option value="<?php echo (int) $template['template_id']; ?>">
                                        <?php echo e($template['template_name']); ?> (<?php echo e($template['evaluation_type']); ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Period Start</label>
                            <input type="date" name="period_start" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Period End</label>
                            <input type="date" name="period_end" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Assignment Note</label>
                            <textarea name="assignment_note" class="form-control" rows="4" placeholder="Optional note or instruction for the employee..."></textarea>
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Assign Evaluation
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="content-card mb-4">
            <div class="card-header">
                <h5><i class="fas fa-circle-info me-2"></i>Workflow</h5>
            </div>
            <div class="card-body small text-muted">
                <p class="mb-2"><strong>1.</strong> Select the employee and template.</p>
                <p class="mb-2"><strong>2.</strong> Save the assignment as <code>Pending Self-Rating</code>.</p>
                <p class="mb-2"><strong>3.</strong> The employee receives a notification and completes the self-rating.</p>
                <p class="mb-0"><strong>4.</strong> Submission continues into the existing review flow.</p>
            </div>
        </div>

        <div class="content-card">
            <div class="card-header">
                <h5><i class="fas fa-history me-2"></i>Recent Assignments</h5>
            </div>
            <div class="card-body">
                <?php if (!$recent_assignments || $recent_assignments->num_rows === 0): ?>
                    <div class="empty-state py-4">
                        <i class="fas fa-inbox d-block"></i>
                        <p class="mb-0">No assignments yet.</p>
                    </div>
                <?php else: ?>
                    <div class="d-grid gap-3">
                        <?php while ($item = $recent_assignments->fetch_assoc()): ?>
                            <div class="border rounded p-3">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div>
                                        <div class="fw-semibold"><?php echo e(trim(($item['first_name'] ?? '') . ' ' . ($item['last_name'] ?? ''))); ?></div>
                                        <div class="small text-muted"><?php echo e($item['template_name'] ?? 'Template'); ?></div>
                                    </div>
                                    <span class="badge <?php echo getStatusBadgeClass($item['status']); ?>"><?php echo e($item['status']); ?></span>
                                </div>
                                <div class="small text-muted mt-2">
                                    Assigned: <?php echo formatDateTime($item['assigned_at'] ?? ''); ?>
                                </div>
                                <div class="small text-muted">
                                    Period: <?php echo formatDate($item['evaluation_period_start'] ?? ''); ?> - <?php echo formatDate($item['evaluation_period_end'] ?? ''); ?>
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
