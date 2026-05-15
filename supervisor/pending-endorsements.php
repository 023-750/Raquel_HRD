<?php
$page_title = 'Pending Endorsements';
require_once '../includes/session-check.php';
checkRole(['HR Supervisor']);
require_once '../includes/functions.php';

ensureEvaluationWorkflowSchema($conn);

$supervisor_id = (int) ($_SESSION['user_id'] ?? 0);
$branch_id = (int) ($_SESSION['branch_id'] ?? 0);

function supervisorPendingDate(string $value): string
{
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : '';
}

function supervisorPendingScore($value): ?float
{
    if ($value === '' || $value === null || !is_numeric($value)) {
        return null;
    }

    return max(0, min(4, (float) $value));
}

function supervisorPendingRows(mysqli $conn, string $sql, string $types = '', array $params = []): array
{
    $stmt = $conn->prepare($sql);
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function supervisorPendingScalar(mysqli $conn, string $sql, string $types = '', array $params = [])
{
    $stmt = $conn->prepare($sql);
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ? array_values($row)[0] : null;
}

function supervisorPendingQueryString(array $params): string
{
    $clean = [];
    foreach ($params as $key => $value) {
        if ($value !== '' && $value !== null) {
            $clean[$key] = $value;
        }
    }
    return http_build_query($clean);
}

function supervisorPendingHasScore(array $row): bool
{
    return isset($row['total_score']) && $row['total_score'] !== '' && $row['total_score'] !== null;
}

function supervisorPendingAttentionFlags(array $row): array
{
    $flags = [];
    $has_score = supervisorPendingHasScore($row);
    $score = $has_score ? (float) $row['total_score'] : null;
    $performance_level = $row['performance_level'] ?? '';
    $days_pending = (int) ($row['days_pending'] ?? 0);

    if (!$has_score) {
        $flags[] = 'No score';
    }
    if (($has_score && $score < 2) || $performance_level === 'Needs Improvement') {
        $flags[] = 'Low score';
    }
    if ($days_pending >= 7) {
        $flags[] = 'Overdue';
    }

    return $flags;
}

$allowed_eval_types = ['Initial', 'Final', 'Quarterly', 'Annual'];
$attention_filters = [
    'low_score' => 'Low Score',
    'overdue' => 'Overdue 7+ Days',
    'missing_score' => 'No Score',
];

$filter_search = trim($_GET['q'] ?? '');
if (strlen($filter_search) > 80) {
    $filter_search = substr($filter_search, 0, 80);
}
$filter_department = isset($_GET['department']) && $_GET['department'] !== '' ? max(0, (int) $_GET['department']) : 0;
$filter_staff = isset($_GET['submitted_by']) && $_GET['submitted_by'] !== '' ? max(0, (int) $_GET['submitted_by']) : 0;
$filter_template = isset($_GET['template']) && $_GET['template'] !== '' ? max(0, (int) $_GET['template']) : 0;
$filter_type = in_array($_GET['evaluation_type'] ?? '', $allowed_eval_types, true) ? $_GET['evaluation_type'] : '';
$filter_attention = array_key_exists($_GET['attention'] ?? '', $attention_filters) ? $_GET['attention'] : '';
$date_from = supervisorPendingDate(trim($_GET['date_from'] ?? ''));
$date_to = supervisorPendingDate(trim($_GET['date_to'] ?? ''));
if ($date_from !== '' && $date_to !== '' && $date_from > $date_to) {
    [$date_from, $date_to] = [$date_to, $date_from];
}
$score_min = supervisorPendingScore($_GET['score_min'] ?? null);
$score_max = supervisorPendingScore($_GET['score_max'] ?? null);
if ($score_min !== null && $score_max !== null && $score_min > $score_max) {
    [$score_min, $score_max] = [$score_max, $score_min];
}

$filter_params = [
    'q' => $filter_search,
    'department' => $filter_department ?: '',
    'submitted_by' => $filter_staff ?: '',
    'template' => $filter_template ?: '',
    'evaluation_type' => $filter_type,
    'attention' => $filter_attention,
    'date_from' => $date_from,
    'date_to' => $date_to,
    'score_min' => $score_min,
    'score_max' => $score_max,
];
$filter_query = supervisorPendingQueryString($filter_params);
$redirect_url = BASE_URL . '/supervisor/pending-endorsements.php' . ($filter_query ? '?' . $filter_query : '');
$form_action = 'pending-endorsements.php' . ($filter_query ? '?' . $filter_query : '');
$export_url = 'pending-endorsements.php?' . supervisorPendingQueryString(array_merge($filter_params, ['export' => 'csv']));

// Handle endorsement/return (MUST be before header.php to allow redirect)
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action'])) {
    $eval_id = (int)$_POST['evaluation_id'];
    $action = $_POST['action'];
    $comments = trim($_POST['supervisor_comments'] ?? '');

    $eval_stmt = $conn->prepare("
        SELECT ev.evaluation_id, ev.submitted_by, CONCAT(e.first_name, ' ', e.last_name) AS emp_name
        FROM evaluations ev
        INNER JOIN employees e ON ev.employee_id = e.employee_id
        WHERE ev.evaluation_id = ?
          AND ev.status = 'Pending Supervisor'
          AND e.branch_id = ?
          AND e.is_active = 1
          AND e.employee_id NOT IN (SELECT employee_id FROM users WHERE role = 'Admin' AND employee_id IS NOT NULL)
        LIMIT 1
    ");
    $eval_stmt->bind_param("ii", $eval_id, $branch_id);
    $eval_stmt->execute();
    $eval_info = $eval_stmt->get_result()->fetch_assoc();
    $eval_stmt->close();

    if (!$eval_info) {
        redirectWith($redirect_url, 'danger', 'Pending evaluation could not be found in your assigned branch.');
    }

    if ($action === 'endorse') {
        $stmt = $conn->prepare("
            UPDATE evaluations ev
            INNER JOIN employees e ON ev.employee_id = e.employee_id
            SET ev.status = 'Pending Manager',
                ev.endorsed_by = ?,
                ev.endorsed_date = NOW(),
                ev.supervisor_comments = ?
            WHERE ev.evaluation_id = ?
              AND ev.status = 'Pending Supervisor'
              AND e.branch_id = ?
              AND e.is_active = 1
        ");
        $stmt->bind_param("isii", $supervisor_id, $comments, $eval_id, $branch_id);
        $stmt->execute();
        $stmt->close();

        // Notify all HR Managers
        $managers = $conn->query("SELECT user_id FROM users WHERE role = 'HR Manager' AND is_active = 1");
        while ($mgr = $managers->fetch_assoc()) {
            createNotification($conn, $mgr['user_id'], 'Evaluation Endorsed', "Evaluation for {$eval_info['emp_name']} has been endorsed and requires your approval.", BASE_URL . '/manager/pending-approvals.php');
        }
        logAudit($conn, $supervisor_id, 'UPDATE', 'Evaluation', $eval_id, "Endorsed evaluation for {$eval_info['emp_name']}");
        redirectWith($redirect_url, 'success', 'Evaluation endorsed and forwarded to HR Manager.');

    } elseif ($action === 'return') {
        if (empty($comments)) {
            redirectWith($redirect_url, 'danger', 'Comments are required when returning an evaluation.');
        }
        $stmt = $conn->prepare("
            UPDATE evaluations ev
            INNER JOIN employees e ON ev.employee_id = e.employee_id
            SET ev.status = 'Returned',
                ev.endorsed_by = ?,
                ev.endorsed_date = NOW(),
                ev.supervisor_comments = ?
            WHERE ev.evaluation_id = ?
              AND ev.status = 'Pending Supervisor'
              AND e.branch_id = ?
              AND e.is_active = 1
        ");
        $stmt->bind_param("isii", $supervisor_id, $comments, $eval_id, $branch_id);
        $stmt->execute();
        $stmt->close();

        if ($eval_info['submitted_by']) {
            createNotification($conn, $eval_info['submitted_by'], 'Evaluation Returned', "Your evaluation for {$eval_info['emp_name']} has been returned for revision.", BASE_URL . '/staff/my-submissions.php');
        }
        logAudit($conn, $supervisor_id, 'UPDATE', 'Evaluation', $eval_id, "Returned evaluation for {$eval_info['emp_name']}");
        redirectWith($redirect_url, 'warning', 'Evaluation returned for revision.');
    }
}

$branchPendingBase = "
    FROM evaluations ev
    INNER JOIN employees e ON ev.employee_id = e.employee_id
    WHERE ev.status IN ('Pending Supervisor', 'Pending HR Consolidation')
      AND e.branch_id = ?
      AND e.is_active = 1
      AND e.employee_id NOT IN (SELECT employee_id FROM users WHERE role = 'Admin' AND employee_id IS NOT NULL)
";

$department_options = supervisorPendingRows(
    $conn,
    "SELECT DISTINCT d.department_id, d.department_name
     FROM evaluations ev
     INNER JOIN employees e ON ev.employee_id = e.employee_id
     INNER JOIN departments d ON e.department_id = d.department_id
     WHERE ev.status IN ('Pending Supervisor', 'Pending HR Consolidation')
       AND e.branch_id = ?
       AND e.is_active = 1
       AND d.is_active = 1
       AND e.employee_id NOT IN (SELECT employee_id FROM users WHERE role = 'Admin' AND employee_id IS NOT NULL)
     ORDER BY d.department_name",
    "i",
    [$branch_id]
);

$staff_options = supervisorPendingRows(
    $conn,
    "SELECT DISTINCT u.user_id, u.full_name
     FROM evaluations ev
     INNER JOIN employees e ON ev.employee_id = e.employee_id
     INNER JOIN users u ON ev.submitted_by = u.user_id
     WHERE ev.status = 'Pending Supervisor'
       AND e.branch_id = ?
       AND e.is_active = 1
       AND e.employee_id NOT IN (SELECT employee_id FROM users WHERE role = 'Admin' AND employee_id IS NOT NULL)
     ORDER BY u.full_name",
    "i",
    [$branch_id]
);

$template_options = supervisorPendingRows(
    $conn,
    "SELECT DISTINCT et.template_id, et.template_name
     FROM evaluations ev
     INNER JOIN employees e ON ev.employee_id = e.employee_id
     INNER JOIN evaluation_templates et ON ev.template_id = et.template_id
     WHERE ev.status IN ('Pending Supervisor', 'Pending HR Consolidation')
       AND e.branch_id = ?
       AND e.is_active = 1
       AND e.employee_id NOT IN (SELECT employee_id FROM users WHERE role = 'Admin' AND employee_id IS NOT NULL)
     ORDER BY et.template_name",
    "i",
    [$branch_id]
);

$pendingWhere = "WHERE ev.status IN ('Pending Supervisor', 'Pending HR Consolidation')
    AND e.branch_id = ?
    AND e.is_active = 1
    AND e.employee_id NOT IN (SELECT employee_id FROM users WHERE role = 'Admin' AND employee_id IS NOT NULL)";
$pendingTypes = "i";
$pendingParams = [$branch_id];

if ($filter_department > 0) {
    $pendingWhere .= " AND e.department_id = ?";
    $pendingTypes .= "i";
    $pendingParams[] = $filter_department;
}
if ($filter_staff > 0) {
    $pendingWhere .= " AND ev.submitted_by = ?";
    $pendingTypes .= "i";
    $pendingParams[] = $filter_staff;
}
if ($filter_template > 0) {
    $pendingWhere .= " AND ev.template_id = ?";
    $pendingTypes .= "i";
    $pendingParams[] = $filter_template;
}
if ($filter_type !== '') {
    $pendingWhere .= " AND ev.evaluation_type = ?";
    $pendingTypes .= "s";
    $pendingParams[] = $filter_type;
}
if ($date_from !== '') {
    $pendingWhere .= " AND ev.submitted_date >= ?";
    $pendingTypes .= "s";
    $pendingParams[] = $date_from;
}
if ($date_to !== '') {
    $pendingWhere .= " AND ev.submitted_date <= ?";
    $pendingTypes .= "s";
    $pendingParams[] = $date_to . ' 23:59:59';
}
if ($score_min !== null) {
    $pendingWhere .= " AND ev.total_score >= ?";
    $pendingTypes .= "d";
    $pendingParams[] = $score_min;
}
if ($score_max !== null) {
    $pendingWhere .= " AND ev.total_score <= ?";
    $pendingTypes .= "d";
    $pendingParams[] = $score_max;
}
if ($filter_attention === 'low_score') {
    $pendingWhere .= " AND ((ev.total_score IS NOT NULL AND ev.total_score < 2) OR ev.performance_level = 'Needs Improvement')";
} elseif ($filter_attention === 'overdue') {
    $pendingWhere .= " AND COALESCE(DATEDIFF(CURRENT_DATE(), DATE(ev.submitted_date)), 0) >= 7";
} elseif ($filter_attention === 'missing_score') {
    $pendingWhere .= " AND ev.total_score IS NULL";
}
if ($filter_search !== '') {
    $like = '%' . $filter_search . '%';
    $pendingWhere .= " AND (
        CONCAT(e.first_name, ' ', e.last_name) LIKE ?
        OR CONCAT(e.last_name, ', ', e.first_name) LIKE ?
        OR e.employee_code LIKE ?
        OR e.job_title LIKE ?
        OR et.template_name LIKE ?
    )";
    $pendingTypes .= "sssss";
    array_push($pendingParams, $like, $like, $like, $like, $like);
}

$all_pending = supervisorPendingRows(
    $conn,
    "SELECT ev.*, CONCAT(e.first_name, ' ', e.last_name) AS employee_name,
            e.employee_code, e.job_title, d.department_name,
            u.full_name AS submitted_by_name, et.template_name,
            COALESCE(DATEDIFF(CURRENT_DATE(), DATE(ev.submitted_date)), 0) AS days_pending,
            sup.full_name AS supervisor_confirmed_by_name,
            ev.supervisor_confirmed_date,
            ev.supervisor_altered_scores,
            ev.sent_to_hr_date,
            ev.status AS eval_status
     FROM evaluations ev
     INNER JOIN employees e ON ev.employee_id = e.employee_id
     LEFT JOIN departments d ON e.department_id = d.department_id
     LEFT JOIN users u ON ev.submitted_by = u.user_id
     LEFT JOIN users sup ON ev.supervisor_confirmed_by = sup.user_id
     LEFT JOIN evaluation_templates et ON ev.template_id = et.template_id
     $pendingWhere
     ORDER BY 
        CASE WHEN ev.status = 'Pending HR Consolidation' THEN 0 ELSE 1 END,
        days_pending DESC, ev.submitted_date ASC",
    $pendingTypes,
    $pendingParams
);

$branch_pending_count = (int) supervisorPendingScalar(
    $conn,
    "SELECT COUNT(*) $branchPendingBase",
    "i",
    [$branch_id]
);

$history_count = (int) supervisorPendingScalar(
    $conn,
    "SELECT COUNT(*)
     FROM evaluations ev
     INNER JOIN employees e ON ev.employee_id = e.employee_id
     WHERE ev.endorsed_by = ?
       AND e.branch_id = ?
       AND ev.status IN ('Pending Manager', 'Approved', 'Rejected', 'Returned')",
    "ii",
    [$supervisor_id, $branch_id]
);

$pending_count = count($all_pending);
$low_score_count = 0;
$overdue_count = 0;
$missing_score_count = 0;
$attention_count = 0;
$oldest_days = 0;
foreach ($all_pending as $row) {
    $has_score = supervisorPendingHasScore($row);
    $score = $has_score ? (float) $row['total_score'] : null;
    $is_low = ($has_score && $score < 2) || ($row['performance_level'] ?? '') === 'Needs Improvement';
    $is_overdue = (int) ($row['days_pending'] ?? 0) >= 7;
    $is_missing_score = !$has_score;
    if ($is_low) {
        $low_score_count++;
    }
    if ($is_overdue) {
        $overdue_count++;
    }
    if ($is_missing_score) {
        $missing_score_count++;
    }
    if ($is_low || $is_overdue || $is_missing_score) {
        $attention_count++;
    }
    $oldest_days = max($oldest_days, (int) ($row['days_pending'] ?? 0));
}

if (($_GET['export'] ?? '') === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="supervisor-pending-endorsements-' . date('Ymd-His') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Company ID', 'Employee', 'Position', 'Department', 'Template', 'Evaluation Type', 'Submitted By', 'Submitted Date', 'Days Pending', 'Score', 'Performance Level', 'Attention Flags']);
    foreach ($all_pending as $row) {
        $flags = supervisorPendingAttentionFlags($row);
        fputcsv($out, [
            getEmployeeDisplayId($row),
            $row['employee_name'],
            $row['job_title'],
            $row['department_name'],
            $row['template_name'],
            $row['evaluation_type'],
            $row['submitted_by_name'],
            $row['submitted_date'],
            $row['days_pending'],
            supervisorPendingHasScore($row) ? $row['total_score'] : 'No score',
            $row['performance_level'] ?: 'Unrated',
            $flags ? implode(', ', $flags) : 'None',
        ]);
    }
    fclose($out);
    exit;
}

$active_filter_count = 0;
foreach ($filter_params as $value) {
    if ($value !== '' && $value !== null) {
        $active_filter_count++;
    }
}

require_once '../includes/header.php';
?>

<style>
    .pending-endorsements .pending-filter-card {
        background: #fff;
        border: 1px solid #eef2e8;
        border-radius: 14px;
        box-shadow: 0 8px 22px rgba(12, 32, 8, 0.06);
        margin-bottom: 18px;
        padding: 16px;
    }

    .pending-endorsements .pending-filter-card .form-label {
        color: var(--text-muted);
        font-size: 0.72rem;
        font-weight: 800;
        margin-bottom: 6px;
        text-transform: uppercase;
    }

    .pending-endorsements .pending-avatar {
        align-items: center;
        background: rgba(41, 67, 6, 0.08);
        border-radius: 12px;
        color: var(--primary-blue);
        display: inline-flex;
        flex: 0 0 42px;
        font-weight: 800;
        height: 42px;
        justify-content: center;
        width: 42px;
    }

    .pending-endorsements .pending-age-badge {
        border-radius: 999px;
        display: inline-flex;
        font-size: 0.72rem;
        font-weight: 800;
        gap: 6px;
        padding: 6px 10px;
        white-space: nowrap;
    }

    .pending-endorsements .pending-stage {
        color: var(--text-muted);
        font-size: 0.72rem;
        font-weight: 700;
        margin-top: 6px;
    }

    .pending-endorsements .score-meter {
        min-width: 96px;
    }

    .pending-endorsements .attention-chip {
        border-radius: 999px;
        border: 1px solid #e5e7eb;
        color: #4b5563;
        display: inline-flex;
        font-size: 0.66rem;
        font-weight: 800;
        padding: 4px 8px;
    }

    .pending-endorsements .attention-chip.is-danger {
        background: rgba(220, 53, 69, 0.1);
        border-color: rgba(220, 53, 69, 0.2);
        color: #dc3545;
    }

    .pending-endorsements .attention-chip.is-warning {
        background: rgba(255, 193, 7, 0.16);
        border-color: rgba(255, 193, 7, 0.28);
        color: #8a6400;
    }

    .pending-endorsements .attention-chip.is-muted {
        background: #f3f4f6;
        color: #4b5563;
    }

    .pending-endorsements .pending-table tbody tr {
        transition: background 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
    }

    .pending-endorsements .pending-table tbody tr:hover {
        background: #fbfcf8;
    }

    .pending-endorsements .pending-table tbody tr.pending-low-score {
        box-shadow: inset 4px 0 0 rgba(220, 53, 69, 0.7);
    }

    .pending-endorsements .pending-table tbody tr.pending-overdue {
        box-shadow: inset 4px 0 0 rgba(255, 193, 7, 0.85);
    }

    .pending-endorsements .pending-table tbody tr.pending-missing-score {
        box-shadow: inset 4px 0 0 rgba(108, 117, 125, 0.5);
    }

    .pending-endorsements .pending-table tbody tr.pending-low-score.pending-overdue {
        box-shadow: inset 4px 0 0 rgba(220, 53, 69, 0.7), inset 8px 0 0 rgba(255, 193, 7, 0.85);
    }

    .pending-endorsements .filter-meta {
        color: var(--text-muted);
        font-size: 0.78rem;
        font-weight: 700;
    }

    @media (max-width: 768px) {
        .pending-endorsements .pending-filter-card .btn,
        .pending-endorsements .pending-filter-card a.btn {
            width: 100%;
        }

        .pending-endorsements .table-responsive {
            overflow: visible;
        }

        .pending-endorsements .pending-table thead {
            display: none;
        }

        .pending-endorsements .pending-table,
        .pending-endorsements .pending-table tbody,
        .pending-endorsements .pending-table tr,
        .pending-endorsements .pending-table td {
            display: block;
            width: 100%;
        }

        .pending-endorsements .pending-table tbody {
            padding: 12px;
        }

        .pending-endorsements .pending-table tbody tr {
            background: #fff;
            border: 1px solid #eef2e8;
            border-radius: 14px;
            box-shadow: 0 8px 18px rgba(12, 32, 8, 0.06);
            margin: 0 0 14px;
            padding: 12px;
        }

        .pending-endorsements .pending-table tbody tr.pending-low-score,
        .pending-endorsements .pending-table tbody tr.pending-overdue,
        .pending-endorsements .pending-table tbody tr.pending-missing-score,
        .pending-endorsements .pending-table tbody tr.pending-low-score.pending-overdue {
            box-shadow: 0 8px 18px rgba(12, 32, 8, 0.06);
        }

        .pending-endorsements .pending-table td {
            align-items: center;
            border: 0;
            display: flex;
            gap: 14px;
            justify-content: space-between;
            padding: 8px 0 !important;
            text-align: right;
        }

        .pending-endorsements .pending-table td::before {
            color: var(--text-muted);
            content: attr(data-label);
            flex: 0 0 auto;
            font-size: 0.68rem;
            font-weight: 800;
            text-align: left;
            text-transform: uppercase;
        }

        .pending-endorsements .pending-table td.pending-primary {
            align-items: flex-start;
            display: block;
            text-align: left;
        }

        .pending-endorsements .pending-table td.pending-primary::before {
            display: none;
        }

        .pending-endorsements .pending-table td[data-label="Actions"] .btn {
            width: 100%;
        }

        .pending-endorsements .fixed-action-bar {
            flex-direction: column;
        }

        .pending-endorsements .fixed-action-bar .btn {
            width: 100%;
        }
    }
</style>

<div class="pending-endorsements">
<div class="page-hero fadeup">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-3">
        <div>
            <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:0;color:rgba(255,255,255,.55);">HR Supervisor · Evaluation Review</div>
            <h4 class="text-white fw-bold mb-0 mt-1"><i class="fas fa-clipboard-check me-2" style="color:#BD9414;"></i>Pending Endorsements</h4>
        </div>
        <div style="color:rgba(255,255,255,.6);font-size:.8rem;">
            <i class="fas fa-hourglass-half me-1"></i><?php echo number_format($branch_pending_count); ?> branch pending
        </div>
    </div>
    <p class="text-white-50 small mb-0"><i class="fas fa-check-double me-1"></i>Review staff evaluations, add supervisor feedback, and forward endorsed records to HR Manager.</p>

    <div class="row g-3 mb-4 mt-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?php echo number_format($branch_pending_count); ?></div>
                        <div class="stat-label">Branch Pending</div>
                    </div>
                    <i class="fas fa-hourglass-half stat-icon" style="color:#ffc107;"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?php echo number_format($pending_count); ?></div>
                        <div class="stat-label">Filtered View</div>
                    </div>
                    <i class="fas fa-filter stat-icon" style="color:#17a2b8;"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?php echo number_format($attention_count); ?></div>
                        <div class="stat-label">Needs Attention</div>
                    </div>
                    <i class="fas fa-triangle-exclamation stat-icon" style="color:#dc3545;"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <a href="evaluation-history.php" class="stat-card text-decoration-none">
                <div class="d-flex justify-content-between align-items-start w-100">
                    <div>
                        <div class="stat-value"><?php echo number_format($history_count); ?></div>
                        <div class="stat-label">Processed</div>
                    </div>
                    <i class="fas fa-history stat-icon" style="color:#28a745;"></i>
                </div>
            </a>
        </div>
    </div>
</div>

<div class="pending-filter-card fadeup fadeup-1">
    <form method="GET" action="" class="row g-3 align-items-end">
        <div class="col-lg-4 col-md-6">
            <label class="form-label">Search</label>
            <input type="search" class="form-control form-control-sm" name="q" value="<?php echo e($filter_search); ?>" placeholder="Employee, Company ID, position">
        </div>
        <div class="col-lg-3 col-md-6">
            <label class="form-label">Department</label>
            <select class="form-select form-select-sm" name="department">
                <option value="">All Departments</option>
                <?php foreach ($department_options as $dept): ?>
                    <option value="<?php echo (int) $dept['department_id']; ?>" <?php echo $filter_department === (int) $dept['department_id'] ? 'selected' : ''; ?>>
                        <?php echo e($dept['department_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-lg-3 col-md-6">
            <label class="form-label">Submitted By</label>
            <select class="form-select form-select-sm" name="submitted_by">
                <option value="">All Staff</option>
                <?php foreach ($staff_options as $staff): ?>
                    <option value="<?php echo (int) $staff['user_id']; ?>" <?php echo $filter_staff === (int) $staff['user_id'] ? 'selected' : ''; ?>>
                        <?php echo e($staff['full_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-lg-2 col-md-6 d-flex flex-wrap gap-2 justify-content-lg-end">
            <button type="submit" class="btn btn-primary btn-sm px-3"><i class="fas fa-filter me-1"></i>Apply</button>
            <a href="pending-endorsements.php" class="btn btn-outline-secondary btn-sm px-3"><i class="fas fa-rotate-left me-1"></i>Reset</a>
        </div>
    </form>
</div>

<div class="content-card border-0 shadow-sm fadeup fadeup-2">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <h5 class="mb-0"><i class="fas fa-clipboard-check me-2 text-primary"></i>Evaluations Pending Endorsement</h5>
        <span class="badge bg-light text-muted border"><?php echo number_format($pending_count); ?> shown</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 pending-table" id="pendingTable">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-3">Employee</th>
                        <th>Department</th>
                        <th>Submitted By</th>
                        <th>Submitted</th>
                        <th>Type & Progress</th>
                        <th>Score & Alerts</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($all_pending)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-5"><i class="fas fa-check-circle fa-3x mb-3 d-block opacity-25"></i>No pending endorsements in this view.</td></tr>
                    <?php else: ?>
                        <?php foreach ($all_pending as $row): ?>
                            <?php
                            $has_score = supervisorPendingHasScore($row);
                            $score = $has_score ? (float) $row['total_score'] : null;
                            $score_width = $has_score ? max(0, min(100, ($score / 4) * 100)) : 0;
                            $days_pending = (int) ($row['days_pending'] ?? 0);
                            $attention_flags = supervisorPendingAttentionFlags($row);
                            $is_low_score = in_array('Low score', $attention_flags, true);
                            $is_overdue = $days_pending >= 7;
                            $is_missing_score = in_array('No score', $attention_flags, true);
                            $row_class = trim(($is_low_score ? 'pending-low-score ' : '') . ($is_overdue ? 'pending-overdue ' : '') . ($is_missing_score ? 'pending-missing-score' : ''));
                            $age_label = $days_pending === 0 ? 'Today' : $days_pending . ' day' . ($days_pending === 1 ? '' : 's');
                            $age_class = $is_overdue ? 'bg-warning-subtle text-warning border border-warning-subtle' : 'bg-primary-subtle text-primary border border-primary-subtle';
                            $initials = strtoupper(substr($row['employee_name'], 0, 1) . substr(explode(' ', $row['employee_name'])[1] ?? '', 0, 1));
                            $score_label = $has_score ? number_format($score, 2) . ' / 4' : 'No score';
                            ?>
                            <tr class="<?php echo e($row_class); ?>">
                                <td class="ps-3 pending-primary" data-label="Employee">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="pending-avatar"><?php echo e($initials); ?></div>
                                        <div class="min-w-0">
                                            <div class="fw-bold"><?php echo e($row['employee_name']); ?></div>
                                            <div class="small company-id-text">Company ID: <span class="company-id-value"><?php echo e(getEmployeeDisplayId($row)); ?></span></div>
                                            <div class="small text-muted"><?php echo e($row['template_name'] ?? 'Template not assigned'); ?></div>
                                            <?php if (!empty($attention_flags)): ?>
                                                <div class="d-flex flex-wrap gap-1 mt-2">
                                                    <?php foreach ($attention_flags as $flag): ?>
                                                        <span class="attention-chip <?php echo $flag === 'Low score' ? 'is-danger' : ($flag === 'Overdue' ? 'is-warning' : 'is-muted'); ?>"><?php echo e($flag); ?></span>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Department">
                                    <div class="fw-semibold"><?php echo e($row['job_title'] ?? 'N/A'); ?></div>
                                    <small class="text-muted"><?php echo e($row['department_name'] ?? 'Unassigned'); ?></small>
                                </td>
                                <td data-label="Submitted By">
                                    <?php if ($row['eval_status'] === 'Pending HR Consolidation' && !empty($row['supervisor_confirmed_by_name'])): ?>
                                        <div class="small fw-semibold text-success">
                                            <i class="fas fa-check-circle me-1"></i><?php echo e($row['supervisor_confirmed_by_name']); ?>
                                        </div>
                                        <div class="small text-muted">Supervisor Confirmed</div>
                                        <?php if (!empty($row['supervisor_altered_scores'])): ?>
                                            <span class="badge bg-warning text-dark">Scores Altered</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="small fw-semibold"><?php echo e($row['submitted_by_name'] ?? 'Unknown Staff'); ?></div>
                                        <div class="small text-muted">Direct Submission</div>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Submitted">
                                    <span class="pending-age-badge <?php echo e($age_class); ?>"><i class="fas fa-clock"></i><?php echo e($age_label); ?></span>
                                    <div><small class="text-muted"><?php echo formatDate($row['submitted_date']); ?></small></div>
                                </td>
                                <td data-label="Type & Progress">
                                    <span class="badge bg-info-subtle text-info border border-info-subtle"><?php echo e($row['evaluation_type'] ?? 'Annual'); ?></span>
                                    <?php if ($row['eval_status'] === 'Pending HR Consolidation'): ?>
                                        <div class="pending-stage text-success">
                                            <i class="fas fa-check me-1"></i>Supervisor confirmed &rarr; <strong>Ready for HR Consolidation</strong>
                                        </div>
                                    <?php else: ?>
                                        <div class="pending-stage">Staff submitted &rarr; Supervisor review</div>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Score & Alerts">
                                    <div class="score-meter">
                                        <div class="d-flex justify-content-between align-items-center gap-2">
                                            <span class="fw-bold"><?php echo e($score_label); ?></span>
                                            <span class="badge <?php echo getPerformanceBadgeClass($row['performance_level']); ?> rounded-pill px-2" style="font-size:0.68rem;"><?php echo e($row['performance_level'] ?: ($has_score ? 'Unrated' : 'Unscored')); ?></span>
                                        </div>
                                        <?php if ($has_score): ?>
                                            <div class="progress mt-2" style="height: 5px;">
                                                <div class="progress-bar <?php echo $is_low_score ? 'bg-danger' : 'bg-primary'; ?>" style="width: <?php echo $score_width; ?>%;"></div>
                                            </div>
                                        <?php else: ?>
                                            <div class="small text-muted mt-2">Score not calculated yet.</div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-end pe-3" data-label="Actions">
                                    <button class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#reviewModal<?php echo (int) $row['evaluation_id']; ?>">
                                        <i class="fas fa-clipboard-check me-1"></i>Review
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
// Render Modals at the end of the file
foreach ($all_pending as $row): 
    $modal_eval_id = (int) $row['evaluation_id'];
    $initials = strtoupper(substr($row['employee_name'], 0, 1) . substr(explode(' ', $row['employee_name'])[1] ?? '', 0, 1));
    $modal_has_score = supervisorPendingHasScore($row);
?>
    <div class="modal fade modal-premium" id="reviewModal<?php echo $modal_eval_id; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-1">Review Evaluation</h5>
                        <p class="mb-0 opacity-75 small">Reviewing evaluation for <?php echo e($row['employee_name']); ?></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 pt-0">
                    <!-- Status Stepper -->
                    <div class="status-stepper d-flex justify-content-between mb-4 py-3 border-bottom overflow-hidden">
                        <?php
                        $steps = [
                            ['l' => 'Drafted', 'a' => true, 'i' => 'fa-pencil-alt'],
                            ['l' => 'Supervisor', 'a' => true, 'i' => 'fa-user-tie', 'c' => true],
                            ['l' => 'Review', 'a' => false, 'i' => 'fa-user-shield'],
                            ['l' => 'Final', 'a' => false, 'i' => 'fa-check-double']
                        ];
                        foreach ($steps as $st): ?>
                            <div class="step-item text-center <?php echo $st['a'] ? 'text-primary' : 'text-muted'; ?>" style="flex: 1;">
                                <div class="mb-1">
                                    <i class="fas <?php echo $st['i']; ?> <?php echo isset($st['c']) ? 'fa-pulse' : ''; ?>"></i>
                                </div>
                                <div style="font-size: 0.65rem; font-weight: 800; text-transform: uppercase;"><?php echo $st['l']; ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="eval-summary-header">
                        <div class="d-flex align-items-center gap-3">
                            <div class="emp-avatar bg-primary text-white d-flex align-items-center justify-content-center fw-bold rounded" style="width: 55px; height: 55px; font-size: 1.2rem;"><?php echo $initials; ?></div>
                            <div>
                                <h4 class="mb-0 fw-bold"><?php echo e($row['employee_name']); ?></h4>
                                <div class="text-muted"><?php echo e($row['job_title'] ?? 'Staff'); ?> &bull; <?php echo e($row['template_name']); ?></div>
                            </div>
                        </div>
                        <div class="score-circle">
                            <div class="val"><?php echo $modal_has_score ? number_format((float) $row['total_score'], 2) . '/4' : 'No score'; ?></div>
                            <div class="lbl">Score</div>
                        </div>
                    </div>

                    <!-- KRA Section -->
                    <div class="section-premium-label mb-3 mt-4">
                        <i class="fas fa-bullseye"></i> I. Strategic Programs & Requirements
                    </div>
                    <div class="table-responsive mb-4">
                        <table class="table table-sm table-hover align-middle border-start">
                            <thead class="small text-muted bg-light">
                                <tr>
                                    <th class="ps-3">Criterion</th>
                                    <th class="text-center" style="width: 80px;">Weight</th>
                                    <th class="text-center" style="width: 80px;">Rating</th>
                                    <th class="text-center" style="width: 80px;">Total</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                                <?php
                                $kra_q = $conn->query("SELECT es.*, ec.criterion_name, ec.description, ec.weight FROM evaluation_scores es JOIN evaluation_criteria ec ON es.criterion_id = ec.criterion_id WHERE es.evaluation_id = $modal_eval_id AND ec.section = 'KRA' ORDER BY ec.sort_order");
                                $kra_num = 1;
                                while ($k = $kra_q->fetch_assoc()): ?>
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-bold">KRA <?php echo $kra_num++; ?>: <?php echo e($k['criterion_name']); ?></div>
                                            <?php if($k['description']): ?><div class="text-muted x-small"><?php echo e($k['description']); ?></div><?php endif; ?>
                                        </td>
                                        <td class="text-center"><?php echo $k['weight']; ?>%</td>
                                        <td class="text-center fw-bold"><?php echo $k['score_value']; ?></td>
                                        <td class="text-center text-primary fw-bold"><?php echo $k['weighted_score']; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                                <tr class="bg-light fw-bold border-top">
                                    <td class="ps-3">KRA Sub-total</td>
                                    <td class="text-center">100%</td>
                                    <td></td>
                                    <td class="text-center text-primary"><?php echo $row['kra_subtotal']; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Behavior Section -->
                    <div class="section-premium-label mb-3 mt-5">
                        <i class="fas fa-heart"></i> II. Behavior & Values
                    </div>
                    <div class="table-responsive mb-4">
                        <table class="table table-sm table-hover align-middle border-start">
                            <thead class="small text-muted bg-light">
                                <tr>
                                    <th class="ps-3">Behavior KPI</th>
                                    <th class="text-center" style="width: 100px;">Rating (1-4)</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                                <?php
                                $beh_q = $conn->query("SELECT es.*, ec.criterion_name, ec.kpi_description FROM evaluation_scores es JOIN evaluation_criteria ec ON es.criterion_id = ec.criterion_id WHERE es.evaluation_id = $modal_eval_id AND ec.section = 'Behavior' ORDER BY ec.sort_order");
                                while ($b = $beh_q->fetch_assoc()): ?>
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-bold"><?php echo e($b['criterion_name']); ?></div>
                                            <div class="text-muted x-small"><?php echo e($b['kpi_description']); ?></div>
                                        </td>
                                        <td class="text-center text-primary fw-bold"><?php echo $b['score_value']; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                                <tr class="bg-light fw-bold border-top">
                                    <td class="ps-3">Behavior Average</td>
                                    <td class="text-center text-primary"><?php echo $row['behavior_average']; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Career Growth -->
                    <?php $cg_suited = !empty($row['career_growth_suited']) ? 1 : (!empty($row['desired_position']) ? 1 : 0); ?>
                    <div class="section-premium-label mb-3 mt-5">
                        <i class="fas fa-chart-line"></i> III. Career Growth
                    </div>
                    <div class="p-3 bg-light rounded-3 mb-4 border-start border-4 border-info">
                        <div class="mb-2 fw-semibold" style="font-size:0.9rem;">
                            Is the employee better suited for another job within the company?
                            <span class="badge ms-2 <?php echo $cg_suited ? 'bg-success' : 'bg-secondary'; ?>">
                                <?php echo $cg_suited ? '&#9745; Yes' : '&#9744; No'; ?>
                            </span>
                        </div>
                        <?php if ($cg_suited && !empty($row['desired_position'])): ?>
                        <div class="small text-muted mt-1">
                            <i class="fas fa-briefcase me-1 text-info"></i>
                            <strong>Job Function / Department:</strong>
                            <span class="text-dark fw-semibold ms-1"><?php echo e($row['desired_position']); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Action Section -->
                    <div class="section-premium-label mb-3 mt-5">
                        <i class="fas fa-comments"></i> IV. Remarks & Decisions
                    </div>
                    <form method="POST" action="<?php echo e($form_action); ?>">
                        <input type="hidden" name="evaluation_id" value="<?php echo $modal_eval_id; ?>">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Supervisor Comments / Feedback</label>
                            <textarea class="form-control bg-light" name="supervisor_comments" rows="3" placeholder="Required for returns, optional for endorsements..."></textarea>
                            <div class="form-text x-small text-danger">* Comments are required when returning an evaluation for revision.</div>
                        </div>
                        <div class="fixed-action-bar d-flex gap-2 justify-content-end">
                            <button type="submit" name="action" value="return" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm">
                                <i class="fas fa-undo me-2"></i>Return for Revision
                            </button>
                            <button type="submit" name="action" value="endorse" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">
                                <i class="fas fa-check-double me-2"></i>Validate & Forward
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
