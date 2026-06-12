<?php
$page_title = 'My Dashboard';
require_once '../includes/session-check.php';
checkRole(['Employee']);
require_once '../includes/functions.php';

$employee_id = (int)($_SESSION['employee_id'] ?? 0);
$user_id     = (int)($_SESSION['user_id']     ?? 0);

// ── Employee core info ──────────────────────────────────────────────────────
$emp_stmt = $conn->prepare("
    SELECT e.employee_id, e.employee_code, e.first_name, e.last_name,
           e.job_title, e.profile_picture, e.hire_date,
           e.employment_status, e.employment_type,
           d.department_name, b.branch_name,
           rc.rank_name
    FROM employees e
    LEFT JOIN departments    d  ON e.department_id      = d.department_id
    LEFT JOIN branches       b  ON e.branch_id          = b.branch_id
    LEFT JOIN rank_categories rc ON e.rank_category_id = rc.rank_category_id
    WHERE e.employee_id = ?
");
$emp_stmt->bind_param("i", $employee_id);
$emp_stmt->execute();
$emp = $emp_stmt->get_result()->fetch_assoc() ?? [];
$emp_stmt->close();

// ── Years of service ────────────────────────────────────────────────────────
$years_of_service = 0;
$months_of_service = 0;
if (!empty($emp['hire_date'])) {
    $hire = new DateTime($emp['hire_date']);
    $now  = new DateTime();
    $diff = $hire->diff($now);
    $years_of_service  = $diff->y;
    $months_of_service = $diff->m;
}

// ── Active / pending evaluations for this employee ──────────────────────────
$active_eval = null;
$eval_stmt = $conn->prepare("
    SELECT ev.evaluation_id, ev.status, ev.evaluation_type,
           ev.evaluation_period_start, ev.evaluation_period_end,
           ev.total_score, ev.performance_level,
           et.template_name AS template_title
    FROM evaluations ev
    LEFT JOIN evaluation_templates et ON ev.template_id = et.template_id
    WHERE ev.employee_id = ? AND ev.deleted_at IS NULL
    ORDER BY ev.created_at DESC
    LIMIT 1
");
$eval_stmt->bind_param("i", $employee_id);
$eval_stmt->execute();
$active_eval = $eval_stmt->get_result()->fetch_assoc();
$eval_stmt->close();

// ── All evaluations count ───────────────────────────────────────────────────
$total_evals = 0;
$completed_evals = 0;
$r = $conn->query("SELECT COUNT(*) as c FROM evaluations WHERE employee_id = $employee_id AND deleted_at IS NULL");
if ($r) $total_evals = (int)$r->fetch_assoc()['c'];
$r2 = $conn->query("SELECT COUNT(*) as c FROM evaluations WHERE employee_id = $employee_id AND status = 'Approved' AND deleted_at IS NULL");
if ($r2) $completed_evals = (int)$r2->fetch_assoc()['c'];

$pending_template_count = 0;
$employee_dept = $emp['department_name'] ?? '';
$pending_templates_stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM evaluation_templates et
    WHERE et.status = 'Active'
      AND et.deleted_at IS NULL
      AND (et.target_department IS NULL OR et.target_department = '' OR et.target_department = 'All Departments' OR et.target_department = ?)
      AND NOT EXISTS (
          SELECT 1
          FROM evaluations ev
          WHERE ev.employee_id = ?
            AND ev.template_id = et.template_id
            AND ev.deleted_at IS NULL
            AND ev.status NOT IN ('Draft', 'Returned', 'Rejected', 'Pending Self-Rating')
      )
");
$pending_templates_stmt->bind_param("si", $employee_dept, $employee_id);
$pending_templates_stmt->execute();
$pending_template_count = (int) ($pending_templates_stmt->get_result()->fetch_assoc()['total'] ?? 0);
$pending_templates_stmt->close();

// ── Latest approved score ───────────────────────────────────────────────────
$latest_score = null;
$score_stmt = $conn->prepare("
    SELECT total_score, performance_level, approved_date
    FROM evaluations
    WHERE employee_id = ? AND status = 'Approved' AND deleted_at IS NULL
    ORDER BY approved_date DESC LIMIT 1
");
$score_stmt->bind_param("i", $employee_id);
$score_stmt->execute();
$latest_score = $score_stmt->get_result()->fetch_assoc();
$score_stmt->close();

// ── Career movements ────────────────────────────────────────────────────────
$career_movements = [];
$cm_stmt = $conn->prepare("
    SELECT cm.movement_type, cm.previous_position, cm.new_position,
           cm.effective_date, cm.approval_status,
           b.branch_name AS new_branch_name
    FROM career_movements cm
    LEFT JOIN branches b ON cm.new_branch_id = b.branch_id
    WHERE cm.employee_id = ?
    ORDER BY cm.effective_date DESC
    LIMIT 5
");
$cm_stmt->bind_param("i", $employee_id);
$cm_stmt->execute();
$career_movements = $cm_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$cm_stmt->close();

// ── Recent notifications ────────────────────────────────────────────────────
$recent_notifs = getRecentNotifications($conn, $user_id, 4, 'employee');

// ── Has supervisor privileges? (subordinates in DB OR supervisor/manager role) ─
$is_supervisor = hasSupervisorPrivileges($conn, $employee_id);

// ── Does THIS employee have a dept supervisor / dept manager above them? ─────
ensureEmployeesReportsTo($conn);
$my_supervisor    = getEmployeeSupervisor($conn, $employee_id);
$has_dept_supervisor = ($my_supervisor !== null && !empty($my_supervisor['user_id']));
$my_dept_manager  = getDeptManagerOfEmployee($conn, $employee_id);
$has_dept_manager = ($my_dept_manager !== null && !empty($my_dept_manager['user_id']));

// ── Pending subordinate ratings count ──────────────────────────────────────
$pending_sub_count = 0;
if ($is_supervisor) {
    $ps = $conn->query("
        SELECT COUNT(*) AS total
        FROM evaluations ev
        INNER JOIN employees e ON ev.employee_id = e.employee_id
        WHERE e.reports_to = $employee_id
          AND ev.status IN ('Pending Dept Supervisor', 'Pending Supervisor')
          AND ev.deleted_at IS NULL
    ");
    if ($ps) $pending_sub_count = (int)$ps->fetch_assoc()['total'];
}

require_once '../includes/header.php';

// ── Helpers ─────────────────────────────────────────────────────────────────
function evalStatusBadge(string $status): string {
    $map = [
        'Draft'                    => ['secondary', 'fa-pencil-alt'],
        'Pending Self-Rating'      => ['warning',   'fa-user-edit'],
        'Pending Dept Supervisor'  => ['warning text-dark', 'fa-user-check'],
        'Pending Dept Manager'     => ['info text-dark', 'fa-user-tie'],
        'Pending Supervisor'       => ['info',      'fa-user-check'],
        'Pending HR Consolidation' => ['primary',   'fa-layer-group'],
        'Pending Manager'          => ['primary',   'fa-user-tie'],
        'Supervisor Confirmed'     => ['success',   'fa-check-double'],
        'Approved'                 => ['success',   'fa-check-circle'],
        'Rejected'                 => ['danger',    'fa-times-circle'],
        'Returned'                 => ['warning',   'fa-undo'],
    ];
    [$color, $icon] = $map[$status] ?? ['secondary', 'fa-circle'];
    return "<span class=\"badge bg-{$color}\"><i class=\"fas {$icon} me-1\"></i>" . htmlspecialchars($status) . "</span>";
}

function movementIcon(string $type): string {
    $icons = [
        'Promotion'   => 'fa-arrow-up text-success',
        'Transfer'    => 'fa-exchange-alt text-info',
        'Demotion'    => 'fa-arrow-down text-danger',
        'Role Change' => 'fa-sync-alt text-warning',
    ];
    return $icons[$type] ?? 'fa-circle text-secondary';
}
?>

<!-- ══════════════════════════════════════════════════════════════════════════
     HERO BANNER
══════════════════════════════════════════════════════════════════════════ -->
<div class="page-hero fadeup">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-4">
        <div class="d-flex align-items-center gap-4 flex-wrap">
            <img src="<?php echo getEmployeeAvatar($emp['profile_picture'] ?? ''); ?>?v=<?php echo time(); ?>"
                 onclick="viewFullImage('<?php echo getEmployeeAvatar($emp['profile_picture'] ?? ''); ?>', '<?php echo e(($emp['first_name'] ?? '') . ' ' . ($emp['last_name'] ?? '')); ?>')"
                 class="cursor-pointer"
                 loading="lazy"
                 style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:3px solid rgba(255,255,255,.35);box-shadow:0 6px 20px rgba(0,0,0,.25);transition:transform .2s;">
            <div>
                <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:1.5px;color:rgba(255,255,255,.55);">Employee Portal · Welcome Back</div>
                <h2 class="text-white fw-bold mb-1 mt-1" style="font-size:1.6rem;">
                    <i class="fas fa-user-circle me-1"></i> Hello, <?php echo e(trim(($emp['first_name'] ?? '') . ' ' . ($emp['last_name'] ?? ''))); ?>!
                </h2>
                <p class="mb-0 text-white-50 small">
                    <i class="fas fa-briefcase me-1"></i><?php echo e($emp['job_title'] ?? '—'); ?>
                    <?php if (!empty($emp['department_name'])): ?>&nbsp;·&nbsp;<i class="fas fa-sitemap me-1"></i><?php echo e($emp['department_name']); ?><?php endif; ?>
                    <?php if (!empty($emp['branch_name'])): ?>&nbsp;·&nbsp;<i class="fas fa-map-marker-alt me-1"></i><?php echo e($emp['branch_name']); ?><?php endif; ?>
                </p>
                <?php if (!empty($emp['rank_name'])): ?>
                <p class="mb-0 mt-1">
                    <span class="badge" style="background:rgba(255,255,255,.18);color:#fff;font-size:.72rem;letter-spacing:.5px;">
                        <i class="fas fa-layer-group me-1"></i><?php echo e($emp['rank_name']); ?>
                    </span>
                    <span class="badge ms-1" style="background:rgba(255,255,255,.18);color:#fff;font-size:.72rem;">
                        <i class="fas fa-id-badge me-1"></i><?php echo e($emp['employment_status'] ?? '—'); ?>
                    </span>
                </p>
                <?php endif; ?>
            </div>
        </div>
        <div class="text-end d-none d-md-block">
            <div class="text-white fw-bold" style="font-size:1.8rem;line-height:1;" id="live-time"><?php echo date('h:i A'); ?></div>
            <div class="text-white-50 small mt-1"><?php echo date('l, F d, Y'); ?></div>
            <div class="mt-2">
                <span class="badge" style="background:rgba(255,255,255,.18);color:#fff;font-size:.72rem;">
                    <i class="fas fa-calendar-alt me-1"></i>
                    Hired <?php echo formatDate($emp['hire_date'] ?? ''); ?>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════════
     BREADCRUMB
══════════════════════════════════════════════════════════════════════════ -->
<nav aria-label="Breadcrumb" class="breadcrumb-nav" style="margin-top: 1rem;">
    <ol class="breadcrumb">
        <li class="breadcrumb-item active" aria-current="page">My Dashboard</li>
    </ol>
</nav>

<!-- ══════════════════════════════════════════════════════════════════════════
     STATS ROW (UX-revamp stat-cards)
══════════════════════════════════════════════════════════════════════════ -->
<div class="row g-3 mb-4 fadeup">
    <!-- Years of Service -->
    <div class="col-6 col-md-3">
        <div class="stat-card h-100">
            <div class="stat-icon stat-icon-warning" aria-hidden="true">
                <i class="fas fa-star"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-value">
                    <?php echo $years_of_service; ?><span style="font-size:1rem;font-weight:500;"> yr<?php echo $years_of_service != 1 ? 's' : ''; ?></span>
                </h3>
                <?php if ($months_of_service > 0): ?>
                <p class="stat-label" style="font-size:.75rem;text-transform:none;letter-spacing:0;"><?php echo $months_of_service; ?> month<?php echo $months_of_service != 1 ? 's' : ''; ?> more</p>
                <?php endif; ?>
                <p class="stat-label">Years of Service</p>
            </div>
        </div>
    </div>

    <!-- Total Evaluations -->
    <div class="col-6 col-md-3">
        <div class="stat-card h-100">
            <div class="stat-icon stat-icon-info" aria-hidden="true">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-value"><?php echo $total_evals; ?></h3>
                <p class="stat-label" style="text-transform:none;letter-spacing:0;font-size:.75rem;"><?php echo $completed_evals; ?> approved · <?php echo $pending_template_count; ?> pending</p>
                <p class="stat-label">Total Evaluations</p>
            </div>
        </div>
    </div>

    <!-- Latest Score -->
    <div class="col-6 col-md-3">
        <?php if ($latest_score):
            $ls_level = $latest_score['performance_level'] ?? '';
            $ls_color = $ls_level ? getPerformanceLevelColor($ls_level) : 'var(--text-dark)';
            $ls_badge = $ls_level ? getPerformanceLevelBadgeClass($ls_level) : 'bg-secondary';
        ?>
        <div class="stat-card h-100">
            <div class="stat-icon stat-icon-success" aria-hidden="true">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-value" style="color:<?php echo $ls_color; ?>;"><?php echo number_format((float)$latest_score['total_score'], 1); ?></h3>
                <?php if ($ls_level): ?>
                <span class="badge <?php echo $ls_badge; ?>" style="font-size:.65rem;">
                    <i class="fas fa-award me-1"></i><?php echo e($ls_level); ?>
                </span>
                <?php endif; ?>
                <p class="stat-label">Latest Score</p>
            </div>
        </div>
        <?php else: ?>
        <div class="stat-card h-100">
            <div class="stat-icon stat-icon-success" aria-hidden="true">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-value" style="font-size:1.4rem;color:var(--color-text-muted,#5E6B5C);">N/A</h3>
                <p class="stat-label" style="text-transform:none;font-size:.75rem;letter-spacing:0;">No approved score yet</p>
                <p class="stat-label">Latest Score</p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Career Movements -->
    <div class="col-6 col-md-3">
        <div class="stat-card h-100">
            <div class="stat-icon stat-icon-danger" aria-hidden="true">
                <i class="fas fa-route"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-value"><?php echo count($career_movements); ?></h3>
                <p class="stat-label" style="text-transform:none;letter-spacing:0;font-size:.75rem;">recorded movements</p>
                <p class="stat-label">Career Movements</p>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════════
     MAIN BODY — 2 columns (responsive: single col on mobile, 2-col on md+)
══════════════════════════════════════════════════════════════════════════ -->
<div class="dashboard-grid row g-4 fadeup">

    <!-- LEFT COL ─────────────────────────────────────────────────────────── -->
    <div class="col-12 col-md-6">

        <!-- Current Evaluation Status -->
        <div class="content-card mb-4">
            <div class="content-card-header">
                <h2 class="content-card-title">
                    <i class="fas fa-clipboard-check" aria-hidden="true"></i>
                    My Evaluation Status
                </h2>
                <div class="d-flex align-items-center gap-2">
                    <a href="<?php echo BASE_URL; ?>/employee/self-rating.php" class="badge bg-warning text-dark text-decoration-none">
                        <i class="fas fa-clock me-1"></i><?php echo $pending_template_count; ?> pending
                    </a>
                    <a href="<?php echo BASE_URL; ?>/employee/completed-ratings.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
            </div>
            <div class="content-card-body">
                <?php if ($active_eval): ?>
                <div style="background:var(--bg-gray);border-radius:12px;padding:1.25rem;" class="mb-3">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div>
                            <div class="fw-bold mb-1"><?php echo e($active_eval['template_title'] ?? 'Evaluation'); ?></div>
                            <div class="text-muted small mb-2">
                                <i class="fas fa-tag me-1"></i><?php echo e($active_eval['evaluation_type'] ?? '—'); ?>
                                <?php if (!empty($active_eval['evaluation_period_start'])): ?>
                                &nbsp;·&nbsp;
                                <i class="fas fa-calendar me-1"></i>
                                <?php echo formatDate($active_eval['evaluation_period_start']); ?> – <?php echo formatDate($active_eval['evaluation_period_end'] ?? ''); ?>
                                <?php endif; ?>
                            </div>
                            <?php echo evalStatusBadge($active_eval['status'] ?? 'Draft'); ?>
                        </div>
                        <?php if (!empty($active_eval['total_score'])):
                            $ae_level = $active_eval['performance_level'] ?? '';
                            $ae_color = $ae_level ? getPerformanceLevelColor($ae_level) : 'var(--primary-blue)';
                            $ae_badge = $ae_level ? getPerformanceLevelBadgeClass($ae_level) : 'bg-secondary';
                        ?>
                        <div class="text-end">
                            <div style="font-size:2rem;font-weight:800;color:<?php echo $ae_color; ?>;line-height:1;"><?php echo number_format((float)$active_eval['total_score'], 1); ?></div>
                            <?php if ($ae_level): ?>
                            <span class="badge <?php echo $ae_badge; ?>" style="font-size:.65rem;margin-top:2px;"><i class="fas fa-award me-1"></i><?php echo e($ae_level); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if (in_array($active_eval['status'], ['Pending Self-Rating', 'Draft'])): ?>
                    <div class="mt-3">
                        <a href="<?php echo BASE_URL; ?>/employee/self-rating.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-play me-1"></i>Continue Self Rating
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <?php
                // Workflow Progress Bar (dynamic: HRD vs full-chain)
                $current_status = $active_eval['status'] ?? '';
                $hr_role = getEmployeeHRRole($conn, $employee_id);

                if ($hr_role === 'HR Manager') {
                    $workflow_steps  = ['Pending Self-Rating', 'Pending Supervisor', 'Approved'];
                    $workflow_labels = ['Self-Rating', 'HR Supervisor', 'Approved'];
                    $status_step_map = [
                        'Draft'                    => 0,
                        'Returned'                 => 0,
                        'Pending Self-Rating'      => 0,
                        'Pending Dept Supervisor'  => 1,
                        'Pending Supervisor'       => 1,
                        'Approved'                 => 2,
                        'Rejected'                 => 2,
                    ];
                } elseif ($hr_role === 'HR Supervisor') {
                    $workflow_steps  = ['Pending Self-Rating', 'Pending Manager', 'Approved'];
                    $workflow_labels = ['Self-Rating', 'HR Manager', 'Approved'];
                    $status_step_map = [
                        'Draft'                    => 0,
                        'Returned'                 => 0,
                        'Pending Self-Rating'      => 0,
                        'Pending Manager'          => 1,
                        'Approved'                 => 2,
                        'Rejected'                 => 2,
                    ];
                } elseif ($hr_role === 'HR Staff') {
                    $workflow_steps  = ['Pending Self-Rating', 'Pending Supervisor', 'Pending Manager', 'Approved'];
                    $workflow_labels = ['Self-Rating', 'HR Supervisor', 'HR Manager', 'Approved'];
                    $status_step_map = [
                        'Draft'                    => 0,
                        'Returned'                 => 0,
                        'Pending Self-Rating'      => 0,
                        'Pending Dept Supervisor'  => 1,
                        'Pending Supervisor'       => 1,
                        'Pending Manager'          => 2,
                        'Approved'                 => 3,
                        'Rejected'                 => 3,
                    ];
                } else {
                    // Non-HR employee
                    if ($has_dept_supervisor && $has_dept_manager) {
                        // Full org-chain workflow
                        $workflow_steps  = ['Pending Self-Rating', 'Pending Dept Supervisor', 'Pending Dept Manager', 'Pending HR Consolidation', 'Pending Manager', 'Approved'];
                        $workflow_labels = ['Self-Rating', 'Dept Supervisor', 'Dept Manager', 'HR Consolidation', 'HR Manager', 'Approved'];
                        $status_step_map = [
                            'Draft'                    => 0,
                            'Returned'                 => 0,
                            'Pending Self-Rating'      => 0,
                            'Pending Dept Supervisor'  => 1,
                            'Pending Supervisor'       => 1,
                            'Pending Dept Manager'     => 2,
                            'Supervisor Confirmed'     => 3,
                            'Pending HR Consolidation' => 3,
                            'Pending Manager'          => 4,
                            'Approved'                 => 5,
                            'Rejected'                 => 5,
                        ];
                    } elseif ($has_dept_supervisor) {
                        // Supervisor only workflow
                        $workflow_steps  = ['Pending Self-Rating', 'Pending Dept Supervisor', 'Pending HR Consolidation', 'Pending Manager', 'Approved'];
                        $workflow_labels = ['Self-Rating', 'Dept Supervisor', 'HR Consolidation', 'HR Manager', 'Approved'];
                        $status_step_map = [
                            'Draft'                    => 0,
                            'Returned'                 => 0,
                            'Pending Self-Rating'      => 0,
                            'Pending Dept Supervisor'  => 1,
                            'Pending Supervisor'       => 1,
                            'Pending Dept Manager'     => 1,
                            'Supervisor Confirmed'     => 2,
                            'Pending HR Consolidation' => 2,
                            'Pending Manager'          => 3,
                            'Approved'                 => 4,
                            'Rejected'                 => 4,
                        ];
                    } else {
                        // Direct-to-HR (no supervisor at all)
                        $workflow_steps  = ['Pending Self-Rating', 'Pending HR Consolidation', 'Pending Manager', 'Approved'];
                        $workflow_labels = ['Self-Rating', 'HR Consolidation', 'HR Manager', 'Approved'];
                        $status_step_map = [
                            'Draft'                    => 0,
                            'Returned'                 => 0,
                            'Pending Self-Rating'      => 0,
                            'Pending Dept Supervisor'  => 1,
                            'Pending Supervisor'       => 1,
                            'Pending Dept Manager'     => 1,
                            'Supervisor Confirmed'     => 1,
                            'Pending HR Consolidation' => 1,
                            'Pending Manager'          => 2,
                            'Approved'                 => 3,
                            'Rejected'                 => 3,
                        ];
                    }
                }

                $step_index = $status_step_map[$current_status] ?? null;
                if ($step_index === null) {
                    $step_index = array_search($current_status, $workflow_steps, true);
                }
                if ($step_index === false || $step_index === null) {
                    $step_index = count($workflow_steps) - 1;
                }
                $total_steps  = count($workflow_steps) - 1;
                $progress_pct = $total_steps > 0 ? round(($step_index / $total_steps) * 100) : 100;
                $is_approved  = ($current_status === 'Approved');
                $is_rejected  = ($current_status === 'Rejected');
                ?>
                <div class="mt-3">
                    <!-- Step dots -->
                    <div class="d-flex align-items-center justify-content-between mb-2" style="gap:4px;">
                        <?php foreach ($workflow_labels as $i => $label):
                            $done    = ($i < $step_index);
                            $current = ($i === $step_index);
                            $dot_color = $done    ? 'var(--primary-blue)'
                                       : ($current ? ($is_rejected ? '#dc3545' : 'var(--primary-blue)') : '#dee2e6');
                            $dot_bg    = $done    ? $dot_color
                                       : ($current ? 'rgba(67,104,254,.12)' : '#f0f4eb');
                            $text_w    = $current ? '700' : '500';
                            $text_col  = ($done || $current) ? 'var(--text-dark)' : 'var(--text-muted)';
                        ?>
                        <div class="text-center" style="flex:1;min-width:0;">
                            <div style="width:28px;height:28px;border-radius:50%;background:<?php echo $dot_bg; ?>;border:2px solid <?php echo $dot_color; ?>;display:flex;align-items:center;justify-content:center;margin:0 auto 4px;">
                                <?php if ($done): ?>
                                    <i class="fas fa-check" style="font-size:.55rem;color:<?php echo $dot_color; ?>;"></i>
                                <?php elseif ($current && $is_rejected): ?>
                                    <i class="fas fa-times" style="font-size:.55rem;color:#dc3545;"></i>
                                <?php else: ?>
                                    <span style="font-size:.6rem;font-weight:700;color:<?php echo $dot_color; ?>;"><?php echo $i + 1; ?></span>
                                <?php endif; ?>
                            </div>
                            <div style="font-size:.58rem;font-weight:<?php echo $text_w; ?>;color:<?php echo $text_col; ?>;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo e($label); ?></div>
                        </div>
                        <?php if ($i < count($workflow_labels) - 1): ?>
                        <div style="flex:1;height:2px;background:<?php echo $done ? 'var(--primary-blue)' : '#dee2e6'; ?>;margin-bottom:20px;transition:background .4s;"></div>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <!-- Progress bar -->
                    <div class="progress" style="height:5px;border-radius:10px;">
                        <div class="progress-bar <?php echo $is_rejected ? 'bg-danger' : 'bg-primary'; ?>" style="width:<?php echo $progress_pct; ?>%;border-radius:10px;transition:width .6s;"></div>
                    </div>
                </div>

                <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-inbox" style="font-size:2.5rem;opacity:.2;"></i>
                    <p class="text-muted mt-3 mb-0">No active evaluation assigned.</p>
                    <p class="text-muted small">Your HR will assign one when it's time.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Employment Snapshot -->
        <div class="content-card mb-4">
            <div class="content-card-header">
                <h2 class="content-card-title">
                    <i class="fas fa-id-badge" aria-hidden="true"></i>
                    Employment Snapshot
                </h2>
                <a href="<?php echo BASE_URL; ?>/employee/my-employment.php" class="btn btn-sm btn-outline-primary">Full Details</a>
            </div>
            <div class="content-card-body" style="padding: 0;">
                <table class="table table-borderless table-sm mb-0" style="font-size:.85rem;">
                    <tr style="border-bottom:1px solid var(--border-color);">
                        <td class="text-muted px-3 py-2" style="width:40%;">Company ID</td>
                        <td class="px-3 py-2 fw-semibold"><?php echo e(getEmployeeDisplayId($emp)); ?></td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--border-color);">
                        <td class="text-muted px-3 py-2">Full Name</td>
                        <td class="px-3 py-2 fw-semibold"><?php echo e(trim(($emp['first_name'] ?? '') . ' ' . ($emp['last_name'] ?? ''))); ?></td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--border-color);">
                        <td class="text-muted px-3 py-2">Position</td>
                        <td class="px-3 py-2"><?php echo e($emp['job_title'] ?? '—'); ?></td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--border-color);">
                        <td class="text-muted px-3 py-2">Department</td>
                        <td class="px-3 py-2"><?php echo e($emp['department_name'] ?? '—'); ?></td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--border-color);">
                        <td class="text-muted px-3 py-2">Branch</td>
                        <td class="px-3 py-2"><?php echo e($emp['branch_name'] ?? '—'); ?></td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--border-color);">
                        <td class="text-muted px-3 py-2">Rank</td>
                        <td class="px-3 py-2">
                            <?php if (!empty($emp['rank_name'])): ?>
                            <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:.72rem;"><i class="fas fa-layer-group me-1"></i><?php echo e($emp['rank_name']); ?></span>
                            <?php else: ?>—<?php endif; ?>
                        </td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--border-color);">
                        <td class="text-muted px-3 py-2">Status</td>
                        <td class="px-3 py-2">
                            <?php
                            $st = $emp['employment_status'] ?? '—';
                            if (in_array($st, ['Regular', 'Full-time'])) {
                                $stc = 'success'; $sti = 'fa-check-circle';
                            } elseif (in_array($st, ['Probationary', 'OJT', 'Trainee'])) {
                                $stc = 'warning'; $sti = 'fa-clock';
                            } else {
                                $stc = 'secondary'; $sti = 'fa-circle';
                            }
                            echo "<span class=\"badge bg-{$stc} text-white\" style=\"font-size:.72rem;\"><i class=\"fas {$sti} me-1\"></i>" . e($st) . "</span>";
                            ?>
                        </td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--border-color);">
                        <td class="text-muted px-3 py-2">Type</td>
                        <td class="px-3 py-2"><?php echo e($emp['employment_type'] ?? '—'); ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted px-3 py-2">Hire Date</td>
                        <td class="px-3 py-2"><?php echo formatDate($emp['hire_date'] ?? ''); ?></td>
                    </tr>
                </table>
            </div>
        </div>

    </div><!-- /LEFT COL -->

    <!-- RIGHT COL ────────────────────────────────────────────────────────── -->
    <div class="col-12 col-md-6">

        <!-- Career Timeline -->
        <div class="content-card mb-4">
            <div class="content-card-header">
                <h2 class="content-card-title">
                    <i class="fas fa-route" aria-hidden="true"></i>
                    Career Timeline
                </h2>
                <a href="<?php echo BASE_URL; ?>/employee/career-movement-request.php" class="btn btn-sm btn-outline-primary">Request Movement</a>
            </div>
            <div class="content-card-body">
                <?php if (!empty($career_movements)): ?>
                <div class="timeline">
                    <?php foreach ($career_movements as $i => $cm): ?>
                    <div class="timeline-item <?php echo $i === 0 ? 'latest' : ''; ?>">
                        <div class="timeline-icon">
                            <i class="fas <?php echo movementIcon($cm['movement_type']); ?>" style="font-size:.8rem;"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-1">
                                <div>
                                    <span class="fw-semibold"><?php echo e($cm['movement_type']); ?></span>
                                    <?php if (!empty($cm['previous_position'])): ?>
                                    <span class="text-muted small"> · From: <?php echo e($cm['previous_position']); ?></span>
                                    <?php endif; ?>
                                    <div class="small"><?php echo e($cm['new_position']); ?>
                                        <?php if (!empty($cm['new_branch_name'])): ?>
                                        <span class="text-muted">· <?php echo e($cm['new_branch_name']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div style="font-size:.72rem;color:var(--text-muted);"><?php echo formatDate($cm['effective_date']); ?></div>
                                    <?php
                                    $statusColors = ['Approved'=>'success','Rejected'=>'danger','Pending'=>'warning'];
                                    $statusIcons  = ['Approved'=>'fa-check-circle','Rejected'=>'fa-times-circle','Pending'=>'fa-clock'];
                                    $sc = $statusColors[$cm['approval_status']] ?? 'secondary';
                                    $si = $statusIcons[$cm['approval_status']] ?? 'fa-circle';
                                    ?>
                                    <span class="badge bg-<?php echo $sc; ?>" style="font-size:.65rem;"><i class="fas <?php echo $si; ?> me-1"></i><?php echo e($cm['approval_status']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-route" style="font-size:2.5rem;opacity:.2;"></i>
                    <p class="text-muted mt-3 mb-0">No career movements on record.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Notifications -->
        <div class="content-card mb-4">
            <div class="content-card-header">
                <h2 class="content-card-title">
                    <i class="fas fa-bell" aria-hidden="true"></i>
                    Recent Notifications
                </h2>
                <a href="<?php echo BASE_URL; ?>/employee/notifications.php" class="btn btn-sm btn-outline-primary">All</a>
            </div>
            <div class="content-card-body" style="padding: 0;">
                <?php if (!empty($recent_notifs)): ?>
                    <?php foreach ($recent_notifs as $n): ?>
                    <a href="<?php echo e($n['link'] ?? '#'); ?>"
                       class="d-flex gap-3 p-3 text-decoration-none <?php echo $n['is_read'] ? '' : 'notif-unread-item'; ?>"
                       style="border-bottom:1px solid var(--border-color);transition:background .2s;"
                       onmouseover="this.style.background='var(--bg-gray)'" onmouseout="this.style.background=''">
                        <div style="width:36px;height:36px;border-radius:50%;background:<?php echo $n['is_read'] ? 'var(--bg-gray)' : 'rgba(67,104,254,.12)'; ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="fas fa-bell" style="font-size:.75rem;color:<?php echo $n['is_read'] ? 'var(--text-muted)' : 'var(--primary-blue)'; ?>;"></i>
                        </div>
                        <div style="min-width:0;">
                            <div class="fw-semibold" style="font-size:.82rem;color:var(--text-dark);"><?php echo e($n['title']); ?></div>
                            <div class="text-muted" style="font-size:.75rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo e($n['message']); ?></div>
                            <div style="font-size:.68rem;color:var(--text-muted);margin-top:2px;"><?php echo formatDateTime($n['created_at']); ?></div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-bell-slash" style="font-size:2rem;opacity:.2;"></i>
                    <p class="text-muted small mt-2 mb-0">No new notifications.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- /RIGHT COL -->

</div>

<!-- ══════════════════════════════════════════════════════════════════════════
     QUICK ACTIONS — grouped with visual hierarchy (Req 6.2, 6.3, 6.5, 17.2)
══════════════════════════════════════════════════════════════════════════ -->
<div class="content-card mb-4 fadeup">
    <div class="content-card-header">
        <h2 class="content-card-title">
            <i class="fas fa-bolt" aria-hidden="true"></i>
            Quick Actions
        </h2>
    </div>
    <div class="content-card-body">

        <!-- Section: Evaluations (highest priority) -->
        <h3 class="quick-actions-section-heading">
            <i class="fas fa-clipboard-check me-2" aria-hidden="true"></i>Evaluations
        </h3>
        <div class="row g-3 mb-4">
            <div class="col-6 col-sm-4 col-md-3">
                <a href="<?php echo BASE_URL; ?>/employee/self-rating.php"
                   class="btn btn-primary quick-action-btn-list w-100"
                   aria-label="Start or continue self-rating evaluation">
                    <i class="fas fa-user-edit" aria-hidden="true"></i>
                    My Self-Rating
                </a>
            </div>
            <div class="col-6 col-sm-4 col-md-3">
                <a href="<?php echo BASE_URL; ?>/employee/completed-ratings.php"
                   class="btn btn-outline-primary quick-action-btn-list w-100"
                   aria-label="View all completed evaluations">
                    <i class="fas fa-clipboard-list" aria-hidden="true"></i>
                    View Evaluations
                </a>
            </div>
            <?php if ($is_supervisor && $pending_sub_count > 0): ?>
            <div class="col-6 col-sm-4 col-md-3">
                <a href="<?php echo BASE_URL; ?>/employee/dept-manager-review.php"
                   class="btn btn-outline-primary quick-action-btn-list w-100 position-relative"
                   aria-label="Review pending subordinate evaluations">
                    <i class="fas fa-users-cog" aria-hidden="true"></i>
                    Review Team
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                          style="font-size:.6rem;"><?php echo $pending_sub_count; ?></span>
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Section: My Information -->
        <h3 class="quick-actions-section-heading">
            <i class="fas fa-id-card me-2" aria-hidden="true"></i>My Information
        </h3>
        <div class="row g-3 mb-4">
            <div class="col-6 col-sm-4 col-md-3">
                <a href="<?php echo BASE_URL; ?>/employee/my-pds.php"
                   class="btn btn-primary quick-action-btn-list w-100"
                   aria-label="View or update my Personal Data Sheet">
                    <i class="fas fa-id-badge" aria-hidden="true"></i>
                    My PDS
                </a>
            </div>
            <div class="col-6 col-sm-4 col-md-3">
                <a href="<?php echo BASE_URL; ?>/employee/my-employment.php"
                   class="btn btn-outline-primary quick-action-btn-list w-100"
                   aria-label="View my employment details">
                    <i class="fas fa-briefcase" aria-hidden="true"></i>
                    Employment Details
                </a>
            </div>
            <div class="col-6 col-sm-4 col-md-3">
                <a href="<?php echo BASE_URL; ?>/employee/profile-settings.php"
                   class="btn btn-outline-primary quick-action-btn-list w-100"
                   aria-label="Manage my profile and account settings">
                    <i class="fas fa-user-cog" aria-hidden="true"></i>
                    Profile Settings
                </a>
            </div>
        </div>

        <!-- Section: Team & Career -->
        <h3 class="quick-actions-section-heading">
            <i class="fas fa-users me-2" aria-hidden="true"></i>Team &amp; Career
        </h3>
        <div class="row g-3">
            <div class="col-6 col-sm-4 col-md-3">
                <a href="<?php echo BASE_URL; ?>/employee/career-movement-request.php"
                   class="btn btn-primary quick-action-btn-list w-100"
                   aria-label="Submit a career movement request">
                    <i class="fas fa-trending-up" aria-hidden="true"></i>
                    Career Request
                </a>
            </div>
            <div class="col-6 col-sm-4 col-md-3">
                <a href="<?php echo BASE_URL; ?>/employee/team-list.php"
                   class="btn btn-outline-primary quick-action-btn-list w-100"
                   aria-label="View my team list">
                    <i class="fas fa-sitemap" aria-hidden="true"></i>
                    My Team
                </a>
            </div>
            <div class="col-6 col-sm-4 col-md-3">
                <a href="<?php echo BASE_URL; ?>/employee/notifications.php"
                   class="btn btn-outline-primary quick-action-btn-list w-100"
                   aria-label="View all notifications">
                    <i class="fas fa-bell" aria-hidden="true"></i>
                    Notifications
                </a>
            </div>
        </div>

    </div>
</div>

<style>
/* ── Quick Actions Section Headings (Req 6.2, 17.2 — 1.5rem, 24px margin-top) */
.quick-actions-section-heading {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--color-text-primary, #1C271B);
    margin-top: 1.5rem;
    margin-bottom: .75rem;
    padding-bottom: .4rem;
    border-bottom: 2px solid var(--color-border-light, #D1D5CE);
    letter-spacing: .01em;
}
.quick-actions-section-heading:first-child { margin-top: 0; }

/* ── Quick Action List-style Buttons (Req 6.3, 6.5 — primary for top priority) */
.quick-action-btn-list {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .5rem;
    min-height: 48px;   /* touch-friendly (Req 3.1) */
    font-size: .95rem;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    transition: all .2s;
}
.quick-action-btn-list i {
    font-size: 1rem;
    flex-shrink: 0;
}

/* Legacy icon-grid style (kept for backward compat if used elsewhere) */
.quick-action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: .5rem;
    padding: 1rem .5rem;
    border-radius: 14px;
    border: 2px solid var(--border-color);
    background: var(--bg-white);
    color: var(--text-dark);
    text-decoration: none;
    font-size: .78rem;
    font-weight: 600;
    text-align: center;
    transition: all .2s;
    height: 100%;
    min-height: 90px;
}
.quick-action-btn i {
    font-size: 1.4rem;
    color: var(--primary-blue);
    transition: transform .2s;
}
.quick-action-btn:hover {
    border-color: var(--primary-blue);
    background: rgba(67,104,254,.05);
    color: var(--primary-blue);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(67,104,254,.12);
}
.quick-action-btn:hover i { transform: scale(1.15); }

/* ── Timeline ─────────────────────────────────────────────────────────────── */
.timeline { position: relative; padding-left: 2rem; }
.timeline::before {
    content: '';
    position: absolute;
    left: .85rem;
    top: 0; bottom: 0;
    width: 2px;
    background: var(--border-color);
}
.timeline-item {
    position: relative;
    margin-bottom: 1.2rem;
}
.timeline-item:last-child { margin-bottom: 0; }
.timeline-icon {
    position: absolute;
    left: -2rem;
    top: .1rem;
    width: 28px; height: 28px;
    border-radius: 50%;
    background: var(--bg-white);
    border: 2px solid var(--border-color);
    display: flex; align-items: center; justify-content: center;
    z-index: 1;
}
.timeline-item.latest .timeline-icon {
    border-color: var(--primary-blue);
    background: rgba(67,104,254,.08);
}
.timeline-content {
    background: var(--bg-gray);
    border-radius: 10px;
    padding: .75rem 1rem;
    transition: background .2s;
}
.timeline-content:hover { background: rgba(67,104,254,.05); }

/* ── Unread notification item highlight ─────────────────────────────────── */
.notif-unread-item { background: rgba(67,104,254,.04); }

/* ── Cursor pointer ─────────────────────────────────────────────────────── */
.cursor-pointer { cursor: pointer; }
</style>

<script>
// Live clock
function updateClock() {
    const now = new Date();
    let h = now.getHours(), m = now.getMinutes();
    const ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12 || 12;
    const el = document.getElementById('live-time');
    if (el) el.textContent = `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')} ${ampm}`;
}
setInterval(updateClock, 1000);
</script>

<?php require_once '../includes/footer.php'; ?>
