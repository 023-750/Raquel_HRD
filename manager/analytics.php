<?php
$page_title = 'Analytics';
require_once '../includes/session-check.php';
checkRole(['HR Manager']);
require_once '../includes/functions.php';
require_once '../includes/header.php';

/* ── Filters ────────────────────────────────────────────────────────────── */
$date_from     = $_GET['date_from']  ?? '';
$date_to       = $_GET['date_to']    ?? '';
$filter_branch = $_GET['branch']     ?? '';
$filter_dept   = $_GET['department'] ?? '';

$where  = "WHERE ev.status = 'Approved'";
$params = [];  $types = '';

if (!empty($date_from))    { $where .= " AND ev.approved_date >= ?";              $params[] = $date_from;           $types .= 's'; }
if (!empty($date_to))      { $where .= " AND ev.approved_date <= ?";              $params[] = $date_to.' 23:59:59'; $types .= 's'; }
if (!empty($filter_branch)){ $where .= " AND e.branch_id = ?";                    $params[] = (int)$filter_branch;  $types .= 'i'; }
if (!empty($filter_dept))  { $where .= " AND e.department_id = ?";                $params[] = (int)$filter_dept;    $types .= 'i'; }

/* ── Summary Stats ──────────────────────────────────────────────────────── */
$stats_q = $conn->prepare(
    "SELECT COUNT(*) AS total,
            ROUND(AVG(ev.total_score), 2) AS avg_score,
            SUM(ev.performance_level = 'Outstanding')        AS outstanding,
            SUM(ev.performance_level = 'Needs Improvement')  AS needs_imp
     FROM evaluations ev
     LEFT JOIN employees e ON ev.employee_id = e.employee_id
     $where AND ev.employee_id NOT IN (SELECT employee_id FROM users WHERE role = 'Admin' AND employee_id IS NOT NULL)");
if (!empty($params)) $stats_q->bind_param($types, ...$params);
$stats_q->execute();
$stats = $stats_q->get_result()->fetch_assoc();
$stats_q->close();

$total_evals = (int)($stats['total']       ?? 0);
$avg_score   = (float)($stats['avg_score'] ?? 0);
$outstanding = (int)($stats['outstanding'] ?? 0);
$needs_imp   = (int)($stats['needs_imp']   ?? 0);

/* ── Performance Distribution ───────────────────────────────────────────── */
$perf_dist = ['Outstanding' => 0, 'Exceeds Expectations' => 0,
               'Meets Expectations' => 0, 'Needs Improvement' => 0];
$perf_q = $conn->prepare(
    "SELECT ev.performance_level, COUNT(*) AS cnt
     FROM evaluations ev
     LEFT JOIN employees e ON ev.employee_id = e.employee_id
     $where AND ev.performance_level IS NOT NULL
     AND ev.employee_id NOT IN (SELECT employee_id FROM users WHERE role = 'Admin' AND employee_id IS NOT NULL)
     GROUP BY ev.performance_level");
if (!empty($params)) $perf_q->bind_param($types, ...$params);
$perf_q->execute();
$pr = $perf_q->get_result();
while ($row = $pr->fetch_assoc())
    if (isset($perf_dist[$row['performance_level']])) $perf_dist[$row['performance_level']] = (int)$row['cnt'];
$perf_q->close();

/* ── Score Trend — per-year 12-month data (built-in year filter) ────────── */
// Discover all years that have approved evaluations
$yr_q = $conn->query("SELECT DISTINCT YEAR(approved_date) AS yr FROM evaluations WHERE status='Approved' ORDER BY yr DESC");
$available_years = [];
while ($row = $yr_q->fetch_assoc()) $available_years[] = (int)$row['yr'];
if (empty($available_years)) $available_years = [(int)date('Y')];
$default_trend_year = (int)date('Y');
// If the current year has no data yet, use the most recent year that does
if (!in_array($default_trend_year, $available_years)) $default_trend_year = $available_years[0];

// Build 12-month arrays for every available year (respects branch/dept filter)
$all_years_trend = [];
foreach ($available_years as $yr) {
    $yr_months = [];
    for ($m = 1; $m <= 12; $m++) {
        $t_where  = "WHERE status='Approved' AND MONTH(approved_date)=$m AND YEAR(approved_date)=$yr AND employee_id NOT IN (SELECT employee_id FROM users WHERE role = 'Admin' AND employee_id IS NOT NULL)";
        $t_params = []; $t_types = '';
        if (!empty($filter_branch)) { $t_where .= ' AND employee_id IN (SELECT employee_id FROM employees WHERE branch_id=?)';     $t_params[] = (int)$filter_branch; $t_types .= 'i'; }
        if (!empty($filter_dept))   { $t_where .= ' AND employee_id IN (SELECT employee_id FROM employees WHERE department_id=?)'; $t_params[] = (int)$filter_dept;   $t_types .= 'i'; }
        $tq2 = $conn->prepare("SELECT ROUND(AVG(total_score),2) AS v, COUNT(*) AS cnt FROM evaluations $t_where");
        if (!empty($t_params)) $tq2->bind_param($t_types, ...$t_params);
        $tq2->execute();
        $r = $tq2->get_result()->fetch_assoc();
        $tq2->close();
        $yr_months[] = ['value' => (float)($r['v'] ?? 0), 'count' => (int)($r['cnt'] ?? 0)];
    }
    $all_years_trend[$yr] = $yr_months;
}
$trend_label = 'By Year';

/* ── Branch Comparison  ──────────────────────────────────────────────────
   Always shows ALL branches; date filter respected but branch/dept filters
   are intentionally excluded so users can compare branches side-by-side.   */
$br_where = "WHERE ev.status = 'Approved'";
$br_params = []; $br_types = '';
if (!empty($date_from)) { $br_where .= " AND ev.approved_date >= ?"; $br_params[] = $date_from;            $br_types .= 's'; }
if (!empty($date_to))   { $br_where .= " AND ev.approved_date <= ?"; $br_params[] = $date_to.' 23:59:59'; $br_types .= 's'; }

$branch_data = [];
$bq = $conn->prepare(
    "SELECT b.branch_name,
            ROUND(AVG(ev.total_score), 2) AS avg_score,
            COUNT(ev.evaluation_id)        AS eval_count
     FROM evaluations ev
     INNER JOIN employees e ON ev.employee_id = e.employee_id
     INNER JOIN branches  b ON e.branch_id    = b.branch_id
     $br_where AND b.branch_name IS NOT NULL
     AND ev.employee_id NOT IN (SELECT employee_id FROM users WHERE role = 'Admin' AND employee_id IS NOT NULL)
     GROUP BY b.branch_id, b.branch_name
     ORDER BY avg_score DESC");
if (!empty($br_params)) $bq->bind_param($br_types, ...$br_params);
$bq->execute();
$br = $bq->get_result();
while ($row = $br->fetch_assoc())
    $branch_data[] = ['label' => $row['branch_name'], 'value' => (float)$row['avg_score'], 'count' => (int)$row['eval_count']];
$bq->close();

$top_branch     = count($branch_data) ? $branch_data[0]['label'] : 'N/A';
$top_branch_avg = count($branch_data) ? $branch_data[0]['value'] : 0;

/* ── Department Breakdown ────────────────────────────────────────────────── */
$dept_data = [];
$dq = $conn->prepare(
    "SELECT d.department_name,
            ROUND(AVG(ev.total_score), 2) AS avg_score,
            COUNT(*) AS cnt
     FROM evaluations ev
     INNER JOIN employees   e ON ev.employee_id  = e.employee_id
     INNER JOIN departments d ON e.department_id = d.department_id
     $where AND d.department_name IS NOT NULL
     AND ev.employee_id NOT IN (SELECT employee_id FROM users WHERE role = 'Admin' AND employee_id IS NOT NULL)
     GROUP BY d.department_id, d.department_name ORDER BY avg_score DESC");
if (!empty($params)) $dq->bind_param($types, ...$params);
$dq->execute();
$dept_data = $dq->get_result()->fetch_all(MYSQLI_ASSOC);
$dq->close();

/* ── Top Performers ──────────────────────────────────────────────────────── */
$tq = $conn->prepare(
    "SELECT CONCAT(e.first_name,' ',e.last_name) AS name, e.job_title,
            b.branch_name, ev.total_score, ev.performance_level
     FROM evaluations ev
     LEFT JOIN employees e ON ev.employee_id = e.employee_id
     LEFT JOIN branches  b ON e.branch_id    = b.branch_id
     $where 
     AND ev.employee_id NOT IN (SELECT employee_id FROM users WHERE role = 'Admin' AND employee_id IS NOT NULL)
     ORDER BY ev.total_score DESC LIMIT 10");
if (!empty($params)) $tq->bind_param($types, ...$params);
$tq->execute();
$top_performers = $tq->get_result()->fetch_all(MYSQLI_ASSOC);
$tq->close();

/* ── [NEW] Department KRA vs. Behavior Breakdown ───────────────────────── */
$kra_beh_data = [];
$kb_q = $conn->prepare("
    SELECT d.department_name,
           ROUND(AVG(ev.kra_subtotal), 2) as avg_kra,
           ROUND(AVG(ev.behavior_average), 2) as avg_behavior
    FROM evaluations ev
    INNER JOIN employees e ON ev.employee_id = e.employee_id
    INNER JOIN departments d ON e.department_id = d.department_id
    $where
    AND ev.employee_id NOT IN (SELECT employee_id FROM users WHERE role = 'Admin' AND employee_id IS NOT NULL)
    GROUP BY d.department_id, d.department_name
    ORDER BY avg_kra DESC
");
if (!empty($params)) $kb_q->bind_param($types, ...$params);
$kb_q->execute();
$kra_beh_data = $kb_q->get_result()->fetch_all(MYSQLI_ASSOC);
$kb_q->close();

/* ── [NEW] Evaluation Lifecycle Status ──────────────────────────────────── */
$status_dist = [];
$status_where = "WHERE ev.employee_id NOT IN (SELECT employee_id FROM users WHERE role = 'Admin' AND employee_id IS NOT NULL)";
$status_params = []; $status_types = '';
if (!empty($date_from))    { $status_where .= " AND ev.created_at >= ?"; $status_params[] = $date_from; $status_types .= 's'; }
if (!empty($date_to))      { $status_where .= " AND ev.created_at <= ?"; $status_params[] = $date_to.' 23:59:59'; $status_types .= 's'; }
if (!empty($filter_branch)){ $status_where .= " AND e.branch_id = ?"; $status_params[] = (int)$filter_branch; $status_types .= 'i'; }
if (!empty($filter_dept))  { $status_where .= " AND e.department_id = ?"; $status_params[] = (int)$filter_dept; $status_types .= 'i'; }

$status_q = $conn->prepare("
    SELECT ev.status, COUNT(*) as cnt
    FROM evaluations ev
    LEFT JOIN employees e ON ev.employee_id = e.employee_id
    $status_where
    GROUP BY ev.status
");
if (!empty($status_params)) $status_q->bind_param($status_types, ...$status_params);
$status_q->execute();
$sr = $status_q->get_result();
while ($row = $sr->fetch_assoc()) {
    $status_dist[$row['status']] = (int)$row['cnt'];
}
$status_q->close();

/* ── [NEW] Career Movements Monthly Volume ────────────────────────────── */
$movement_months = [];
$movement_types = ['Promotion' => [], 'Transfer' => [], 'Demotion' => [], 'Role Change' => []];

$cm_where = "WHERE cm.approval_status = 'Approved'";
$cm_params = []; $cm_types = '';
if (!empty($date_from))    { $cm_where .= " AND cm.effective_date >= ?"; $cm_params[] = $date_from; $cm_types .= 's'; }
if (!empty($date_to))      { $cm_where .= " AND cm.effective_date <= ?"; $cm_params[] = $date_to; $cm_types .= 's'; }
if (!empty($filter_branch)){ $cm_where .= " AND (cm.new_branch_id = ? OR cm.previous_branch_id = ?)"; $cm_params[] = (int)$filter_branch; $cm_params[] = (int)$filter_branch; $cm_types .= 'ii'; }
if (!empty($filter_dept))  { $cm_where .= " AND e.department_id = ?"; $cm_params[] = (int)$filter_dept; $cm_types .= 'i'; }

$cm_q = $conn->prepare("
    SELECT DATE_FORMAT(cm.effective_date, '%b %Y') as yr_mo,
           SUM(cm.movement_type = 'Promotion') as promotions,
           SUM(cm.movement_type = 'Transfer') as transfers,
           SUM(cm.movement_type = 'Demotion') as demotions,
           SUM(cm.movement_type = 'Role Change') as role_changes
    FROM career_movements cm
    LEFT JOIN employees e ON cm.employee_id = e.employee_id
    $cm_where
    GROUP BY DATE_FORMAT(cm.effective_date, '%Y-%m'), yr_mo
    ORDER BY DATE_FORMAT(cm.effective_date, '%Y-%m') ASC
    LIMIT 6
");
if (!empty($cm_params)) $cm_q->bind_param($cm_types, ...$cm_params);
$cm_q->execute();
$cm_res = $cm_q->get_result();
while ($row = $cm_res->fetch_assoc()) {
    $movement_months[] = $row['yr_mo'];
    $movement_types['Promotion'][]   = (int)$row['promotions'];
    $movement_types['Transfer'][]    = (int)$row['transfers'];
    $movement_types['Demotion'][]    = (int)$row['demotions'];
    $movement_types['Role Change'][] = (int)$row['role_changes'];
}
$cm_q->close();

/* ── [NEW] Tenure Cohort Performance ───────────────────────────────────── */
$tenure_data = [];
$tenure_q = $conn->prepare("
    SELECT 
        CASE 
            WHEN DATEDIFF(ev.approved_date, e.hire_date) / 365.25 < 1 THEN '< 1 Year'
            WHEN DATEDIFF(ev.approved_date, e.hire_date) / 365.25 BETWEEN 1 AND 3 THEN '1-3 Years'
            WHEN DATEDIFF(ev.approved_date, e.hire_date) / 365.25 BETWEEN 3 AND 5 THEN '3-5 Years'
            ELSE '5+ Years'
        END as tenure_bracket,
        ROUND(AVG(ev.total_score), 2) as avg_score,
        COUNT(*) as cnt
    FROM evaluations ev
    INNER JOIN employees e ON ev.employee_id = e.employee_id
    $where
    AND ev.employee_id NOT IN (SELECT employee_id FROM users WHERE role = 'Admin' AND employee_id IS NOT NULL)
    GROUP BY tenure_bracket
    ORDER BY FIELD(tenure_bracket, '< 1 Year', '1-3 Years', '3-5 Years', '5+ Years')
");
if (!empty($params)) $tenure_q->bind_param($types, ...$params);
$tenure_q->execute();
$tenure_data = $tenure_q->get_result()->fetch_all(MYSQLI_ASSOC);
$tenure_q->close();

/* ── [NEW] Gender Performance Insights ─────────────────────────────────── */
$gender_data = [];
$gender_q = $conn->prepare("
    SELECT e.gender, 
           ROUND(AVG(ev.total_score), 2) as avg_score,
           COUNT(*) as cnt
    FROM evaluations ev
    INNER JOIN employees e ON ev.employee_id = e.employee_id
    $where
    AND ev.employee_id NOT IN (SELECT employee_id FROM users WHERE role = 'Admin' AND employee_id IS NOT NULL)
    AND e.gender IS NOT NULL
    GROUP BY e.gender
");
if (!empty($params)) $gender_q->bind_param($types, ...$params);
$gender_q->execute();
$gender_data = $gender_q->get_result()->fetch_all(MYSQLI_ASSOC);
$gender_q->close();

/* ── Branch color palette (26 brand-harmonious colors) ─────────────────── */
$palette = ['#294306','#BD9414','#D71920','#2E86AB','#3BB273',
            '#7B2D8B','#F18F01','#44BBA4','#E94F37','#386FA4',
            '#59A608','#9B2226','#0077B6','#F77F00','#5C4033',
            '#00B4D8','#80B918','#E63946','#457B9D','#6A0572',
            '#C77DFF','#52B788','#F4A261','#2D6A4F','#E9C46A','#A8DADC'];
$branch_colors = [];
foreach ($branch_data as $i => $_) $branch_colors[] = $palette[$i % count($palette)];

/* ── Filter dropdowns ───────────────────────────────────────────────────── */
$branches_dd    = $conn->query("SELECT * FROM branches    ORDER BY branch_name");
$departments_dd = $conn->query("SELECT department_id, department_name FROM departments WHERE is_active=1 ORDER BY department_name");

/* ── Badge helpers ──────────────────────────────────────────────────────── */
$level_meta = [
    'Outstanding'          => ['icon' => 'fa-star',           'color' => '#28a745', 'bg' => 'rgba(40,167,69,.12)'],
    'Exceeds Expectations' => ['icon' => 'fa-thumbs-up',      'color' => '#17a2b8', 'bg' => 'rgba(23,162,184,.12)'],
    'Meets Expectations'   => ['icon' => 'fa-check-circle',   'color' => '#ffc107', 'bg' => 'rgba(255,193,7,.12)'],
    'Needs Improvement'    => ['icon' => 'fa-exclamation-circle','color'=>'#dc3545', 'bg' => 'rgba(220,53,69,.12)'],
];
?>



<!-- ═══════════════════════  HERO  ═══════════════════════ -->
<div class="page-hero fadeup">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
        <div>
            <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.55);">HR Manager · Analytics</div>
            <h4 class="text-white fw-bold mb-0 mt-1"><i class="fas fa-chart-bar me-2" style="color:#BD9414;"></i>Performance Analytics</h4>
        </div>
        <div style="color:rgba(255,255,255,.6);font-size:.8rem;">
            <i class="fas fa-sync-alt me-1"></i>Data as of <?php echo date('F d, Y'); ?>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?php echo number_format($total_evals); ?></div>
                        <div class="stat-label">Total Evaluations</div>
                    </div>
                    <i class="fas fa-file-alt stat-icon text-white-50"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?php echo number_format($avg_score, 2); ?></div>
                        <div class="stat-label">Average Score</div>
                    </div>
                    <i class="fas fa-chart-line stat-icon" style="color:#BD9414;"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?php echo number_format($outstanding); ?></div>
                        <div class="stat-label">Outstanding</div>
                    </div>
                    <i class="fas fa-star stat-icon" style="color:#BD9414;"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value" style="font-size:1.1rem;padding-top:4px;"><?php echo e($top_branch); ?></div>
                        <div class="stat-label">Top Branch · <?php echo number_format($top_branch_avg,2); ?></div>
                    </div>
                    <i class="fas fa-trophy stat-icon" style="color:#BD9414;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════  FILTERS  ═══════════════════════ -->
<div class="filter-card fadeup fadeup-1">
    <form method="GET" action="" class="row align-items-end g-3">
        <div class="col-md-2 col-6">
            <label class="form-label fw-semibold" style="font-size:.78rem;">Date From</label>
            <input type="date" class="form-control form-control-sm" name="date_from" value="<?php echo e($date_from); ?>">
        </div>
        <div class="col-md-2 col-6">
            <label class="form-label fw-semibold" style="font-size:.78rem;">Date To</label>
            <input type="date" class="form-control form-control-sm" name="date_to" value="<?php echo e($date_to); ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold" style="font-size:.78rem;">Branch</label>
            <select class="form-select form-select-sm" name="branch">
                <option value="">All Branches</option>
                <?php while ($b = $branches_dd->fetch_assoc()): ?>
                    <option value="<?php echo $b['branch_id']; ?>" <?php echo ($filter_branch == $b['branch_id']) ? 'selected' : ''; ?>>
                        <?php echo e($b['branch_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold" style="font-size:.78rem;">Department</label>
            <select class="form-select form-select-sm" name="department">
                <option value="">All Departments</option>
                <?php while ($d = $departments_dd->fetch_assoc()): ?>
                    <option value="<?php echo $d['department_id']; ?>" <?php echo ($filter_dept == $d['department_id']) ? 'selected' : ''; ?>>
                        <?php echo e($d['department_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn-apply flex-fill"><i class="fas fa-filter me-1"></i>Apply</button>
            <a href="analytics.php" class="btn btn-sm btn-outline-secondary px-3">Reset</a>
        </div>
    </form>
</div>

<style>
/* Custom Tab Styles matching brand colors */
.approval-tabs {
    border-bottom: 2px solid #eef2e8;
}
.approval-tabs .nav-link {
    border: none;
    padding: 12px 20px;
    color: #666;
    font-weight: 600;
    font-size: 0.9rem;
    position: relative;
    transition: all 0.3s;
    background: transparent !important;
}
.approval-tabs .nav-link:hover {
    color: #294306;
}
.approval-tabs .nav-link.active {
    color: #294306 !important;
    font-weight: 700;
}
.approval-tabs .nav-link.active::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 15px;
    right: 15px;
    height: 3px;
    background: #294306;
    border-radius: 10px;
}
.tab-pane {
    animation: fadeSlideUp 0.4s ease forwards;
}
</style>

<!-- ═══════════════════════  TABS NAVIGATION  ═══════════════════════ -->
<ul class="nav nav-tabs approval-tabs mb-4 fadeup" id="analyticsTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">
            <i class="fas fa-eye me-2"></i>Performance Overview
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" id="org-tab" data-bs-toggle="tab" data-bs-target="#org" type="button" role="tab">
            <i class="fas fa-sitemap me-2"></i>Organizational Analysis
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" id="mobility-tab" data-bs-toggle="tab" data-bs-target="#mobility" type="button" role="tab">
            <i class="fas fa-route me-2"></i>Talent Mobility & Progress
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" id="demographics-tab" data-bs-toggle="tab" data-bs-target="#demographics" type="button" role="tab">
            <i class="fas fa-users me-2"></i>Demographics Insights
        </button>
    </li>
</ul>

<div class="tab-content" id="analyticsTabsContent">
    <!-- ═══════════════════════ TAB 1: OVERVIEW ═══════════════════════ -->
    <div class="tab-pane fade show active" id="overview" role="tabpanel">
        <div class="row g-3 mb-3">
            <!-- Performance Distribution -->
            <div class="col-lg-5">
                <div class="chart-card">
                    <div class="cc-header">
                        <h6><i class="fas fa-chart-pie me-2" style="color:#294306;"></i>Performance Distribution</h6>
                        <span class="badge bg-light text-muted" style="font-size:.7rem;"><?php echo number_format($total_evals); ?> evals</span>
                    </div>
                    <div class="cc-body">
                        <div class="chart-wrap" style="height:240px;">
                            <canvas id="perfPieChart"></canvas>
                        </div>
                        <!-- Legend pills -->
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <?php foreach ([
                                'Outstanding'          => ['#28a745', $perf_dist['Outstanding']],
                                'Exceeds Exp.'         => ['#17a2b8', $perf_dist['Exceeds Expectations']],
                                'Meets Exp.'           => ['#ffc107', $perf_dist['Meets Expectations']],
                                'Needs Impr.'          => ['#dc3545', $perf_dist['Needs Improvement']],
                            ] as $lbl => [$col, $cnt]): ?>
                            <span style="display:inline-flex;align-items:center;gap:5px;font-size:.72rem;font-weight:600;color:#555;">
                                <span style="width:10px;height:10px;border-radius:50%;background:<?php echo $col; ?>;display:inline-block;flex-shrink:0;"></span>
                                <?php echo e($lbl); ?> <span style="color:#aaa;">(<?php echo $cnt; ?>)</span>
                            </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Score Trend (Monthly Bar Chart) -->
            <div class="col-lg-7">
                <div class="chart-card">
                    <div class="cc-header d-flex justify-content-between align-items-center">
                        <div>
                            <h6><i class="fas fa-chart-line me-2" style="color:#294306;"></i>Score Trend</h6>
                            <span style="font-size:.7rem;color:#aaa;" id="trendSubtitle"><?php echo $default_trend_year; ?> Monthly Averages</span>
                        </div>
                        <!-- Year Pill Buttons -->
                        <div class="btn-group btn-group-sm" role="group">
                            <?php foreach ($available_years as $yr): ?>
                            <button type="button" class="btn btn-outline-secondary trend-year-btn <?php echo $yr === $default_trend_year ? 'active btn-secondary text-white' : ''; ?>" data-year="<?php echo $yr; ?>">
                                <?php echo $yr; ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="cc-body">
                        <div class="chart-wrap" style="height:245px;">
                            <canvas id="trendLineChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <!-- Top Performers -->
            <div class="col-12">
                <div class="chart-card">
                    <div class="cc-header">
                        <h6><i class="fas fa-trophy me-2" style="color:#BD9414;"></i>Top Performers</h6>
                        <a href="?export=csv&<?php echo http_build_query($_GET); ?>" class="btn btn-sm btn-outline-success" style="font-size:.72rem;padding:3px 10px;">
                            <i class="fas fa-download me-1"></i>Export CSV
                        </a>
                    </div>
                    <div class="cc-body p-0">
                        <?php if (empty($top_performers)): ?>
                        <div class="empty-state"><i class="fas fa-users"></i><div>No performer data yet.</div></div>
                        <?php else: ?>
                        <?php foreach ($top_performers as $idx => $tp):
                            $rkClass = $idx === 0 ? 'gold' : ($idx === 1 ? 'silver' : ($idx === 2 ? 'bronze' : 'plain'));
                            $lvl = $tp['performance_level'] ?? getPerformanceLevel($tp['total_score']);
                            $meta = $level_meta[$lvl] ?? ['icon'=>'fa-circle','color'=>'#888','bg'=>'#f0f0f0'];
                        ?>
                        <div class="performer-row">
                            <div class="performer-rank <?php echo $rkClass; ?>"><?php echo $idx+1; ?></div>
                            <div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0">
                                <div style="width:36px;height:36px;border-radius:50%;background:<?php echo $meta['bg']; ?>;
                                            display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i class="fas <?php echo $meta['icon']; ?>" style="color:<?php echo $meta['color']; ?>;font-size:.85rem;"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="performer-name text-truncate"><?php echo e($tp['name']); ?></div>
                                    <div class="performer-meta text-truncate">
                                        <?php echo e($tp['job_title'] ?? ''); ?>
                                        <?php if (!empty($tp['branch_name'])): ?> · <span style="color:#294306;"><?php echo e($tp['branch_name']); ?></span><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="performer-score">
                                <div class="ps-val"><?php echo number_format($tp['total_score'],2); ?></div>
                                <div class="lvl-pill ps-level" style="background:<?php echo $meta['bg']; ?>;color:<?php echo $meta['color']; ?>;">
                                    <?php echo e($lvl); ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════ TAB 2: ORGANIZATIONAL BREAKDOWN ═══════════════════════ -->
    <div class="tab-pane fade" id="org" role="tabpanel">
        <div class="row g-3 mb-3">
            <!-- Branch Comparison -->
            <div class="col-lg-6">
                <div class="chart-card">
                    <div class="cc-header">
                        <h6><i class="fas fa-code-branch me-2" style="color:#294306;"></i>Branch Comparison</h6>
                        <span style="font-size:.7rem;color:#aaa;"><?php echo count($branch_data); ?> branches</span>
                    </div>
                    <div class="cc-body" style="overflow-y:auto;max-height:340px;padding-right:4px;">
                        <?php if (empty($branch_data)): ?>
                        <div class="empty-state"><i class="fas fa-building" style="color:#ddd;"></i><div>No branch data yet.</div></div>
                        <?php else:
                            $max_b = max(array_column($branch_data,'value'));
                            foreach ($branch_data as $idx => $b):
                                $pct = $max_b > 0 ? round($b['value'] / $max_b * 100) : 0;
                                $color = $palette[$idx % count($palette)];
                                $rankClass = $idx === 0 ? 'gold' : ($idx === 1 ? 'silver' : ($idx === 2 ? 'bronze' : 'plain'));
                        ?>
                        <div class="branch-row">
                            <div class="branch-rank <?php echo $rankClass; ?>" style="<?php echo $idx > 2 ? "background:$color;" : ''; ?>"><?php echo $idx+1; ?></div>
                            <div class="branch-name" title="<?php echo e($b['label']); ?>"><?php echo e($b['label']); ?></div>
                            <div class="branch-bar-wrap">
                                <div class="branch-bar-fill" style="width:<?php echo $pct; ?>%;background:<?php echo $color; ?>;"></div>
                            </div>
                            <div class="branch-score"><?php echo number_format($b['value'],2); ?></div>
                            <div class="branch-count"><?php echo $b['count']; ?> <span style="font-size:.62rem;">eval<?php echo $b['count']!=1?'s':''; ?></span></div>
                        </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>
            </div>

            <!-- Department Breakdown -->
            <div class="col-lg-6">
                <div class="chart-card">
                    <div class="cc-header">
                        <h6><i class="fas fa-sitemap me-2" style="color:#294306;"></i>Department Breakdown</h6>
                        <span style="font-size:.7rem;color:#aaa;"><?php echo count($dept_data); ?> departments</span>
                    </div>
                    <div class="cc-body p-0" style="overflow-y:auto;max-height:340px;">
                        <?php if (empty($dept_data)): ?>
                        <div class="empty-state"><i class="fas fa-building"></i><div>No department data.</div></div>
                        <?php else: ?>
                        <table class="dept-table">
                            <thead><tr>
                                <th>Department</th>
                                <th>Avg</th>
                                <th>Score</th>
                            </tr></thead>
                            <tbody>
                            <?php
                            $max_dept = max(array_column($dept_data,'avg_score'));
                            foreach ($dept_data as $d):
                                $dpct = $max_dept > 0 ? round($d['avg_score']/$max_dept*100) : 0;
                            ?>
                            <tr>
                                <td><span class="fw-semibold" style="font-size:.8rem;"><?php echo e($d['department_name']); ?></span>
                                    <br><span style="font-size:.68rem;color:#aaa;"><?php echo $d['cnt']; ?> eval<?php echo $d['cnt']!=1?'s':''; ?></span>
                                </td>
                                <td><div class="dept-bar-wrap"><div class="dept-bar-fill" style="width:<?php echo $dpct; ?>%;"></div></div></td>
                                <td><strong style="color:#294306;"><?php echo number_format($d['avg_score'],2); ?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- NEW KRA vs. Behavior Breakdown Chart -->
        <div class="row g-3 mb-3">
            <div class="col-12">
                <div class="chart-card">
                    <div class="cc-header">
                        <h6><i class="fas fa-balance-scale me-2" style="color:#294306;"></i>Department KRA vs. Behavior Breakdown</h6>
                        <span class="badge bg-light text-muted" style="font-size:.7rem;">Technical KRA vs. Competency Behavior</span>
                    </div>
                    <div class="cc-body">
                        <?php if (empty($kra_beh_data)): ?>
                        <div class="empty-state"><i class="fas fa-sitemap" style="color:#ddd;"></i><div>No department data yet.</div></div>
                        <?php else: ?>
                        <div class="chart-wrap" style="height:350px;">
                            <canvas id="kraBehChart"></canvas>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════ TAB 3: TALENT MOBILITY & PROGRESS ═══════════════════════ -->
    <div class="tab-pane fade" id="mobility" role="tabpanel">
        <div class="row g-3 mb-3">
            <!-- Evaluation Status Process Progress -->
            <div class="col-lg-5">
                <div class="chart-card">
                    <div class="cc-header">
                        <h6><i class="fas fa-tasks me-2" style="color:#294306;"></i>Evaluation Cycle Lifecycle Progress</h6>
                        <span class="badge bg-light text-muted" style="font-size:.7rem;">Active Workflow Statuses</span>
                    </div>
                    <div class="cc-body">
                        <?php if (empty($status_dist)): ?>
                        <div class="empty-state"><i class="fas fa-clock" style="color:#ddd;"></i><div>No status data yet.</div></div>
                        <?php else: ?>
                        <div class="chart-wrap" style="height:250px;">
                            <canvas id="lifecycleChart"></canvas>
                        </div>
                        <!-- Color Legend mapping -->
                        <div class="d-flex flex-wrap gap-2 justify-content-center mt-3" style="max-height:100px; overflow-y:auto; padding-top:5px;">
                            <?php
                            $status_colors = [
                                'Draft' => '#6c757d',
                                'Pending Self-Rating' => '#17a2b8',
                                'Pending Supervisor' => '#ffc107',
                                'Pending HR Consolidation' => '#fd7e14',
                                'Pending Manager' => '#007bff',
                                'Supervisor Confirmed' => '#28a745',
                                'Approved' => '#294306',
                                'Rejected' => '#dc3545',
                                'Returned' => '#e83e8c'
                            ];
                            foreach ($status_dist as $st => $cnt): 
                                $col = $status_colors[$st] ?? '#888';
                            ?>
                            <span style="display:inline-flex;align-items:center;gap:5px;font-size:.72rem;font-weight:600;color:#555;">
                                <span style="width:10px;height:10px;border-radius:50%;background:<?php echo $col; ?>;display:inline-block;flex-shrink:0;"></span>
                                <?php echo e($st); ?> <span style="color:#aaa;">(<?php echo $cnt; ?>)</span>
                            </span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Stacked Career Movements -->
            <div class="col-lg-7">
                <div class="chart-card">
                    <div class="cc-header">
                        <h6><i class="fas fa-exchange-alt me-2" style="color:#294306;"></i>Career Movements Monthly Volume</h6>
                        <span class="badge bg-light text-muted" style="font-size:.7rem;">Talent Mobility Dynamics</span>
                    </div>
                    <div class="cc-body">
                        <?php if (empty($movement_months)): ?>
                        <div class="empty-state"><i class="fas fa-route" style="color:#ddd;"></i><div>No approved movements recorded yet.</div></div>
                        <?php else: ?>
                        <div class="chart-wrap" style="height:350px;">
                            <canvas id="movementsChart"></canvas>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════ TAB 4: DEMOGRAPHICS INSIGHTS ═══════════════════════ -->
    <div class="tab-pane fade" id="demographics" role="tabpanel">
        <div class="row g-3 mb-3">
            <!-- Tenure Performance Cohorts -->
            <div class="col-lg-6">
                <div class="chart-card">
                    <div class="cc-header">
                        <h6><i class="fas fa-hourglass-half me-2" style="color:#294306;"></i>Tenure Bracket Performance Correlation</h6>
                        <span class="badge bg-light text-muted" style="font-size:.7rem;">Score vs. Years of Service</span>
                    </div>
                    <div class="cc-body">
                        <?php if (empty($tenure_data)): ?>
                        <div class="empty-state"><i class="fas fa-calendar-alt" style="color:#ddd;"></i><div>No tenure data available.</div></div>
                        <?php else: ?>
                        <div class="chart-wrap" style="height:300px;">
                            <canvas id="tenureChart"></canvas>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Gender Performance Insights -->
            <div class="col-lg-6">
                <div class="chart-card">
                    <div class="cc-header">
                        <h6><i class="fas fa-venus-mars me-2" style="color:#294306;"></i>Gender Performance Insights</h6>
                        <span class="badge bg-light text-muted" style="font-size:.7rem;">Averages by Gender</span>
                    </div>
                    <div class="cc-body">
                        <?php if (empty($gender_data)): ?>
                        <div class="empty-state"><i class="fas fa-users" style="color:#ddd;"></i><div>No gender performance data.</div></div>
                        <?php else: ?>
                        <div class="chart-wrap" style="height:300px;">
                            <canvas id="genderPerfChart"></canvas>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const SPINE = '#294306', GOLD = '#BD9414', RED = '#D71920';
    const gridColor = 'rgba(0,0,0,.04)';

    /* ── shared defaults ── */
    Chart.defaults.font.family = "'Inter','Segoe UI',sans-serif";
    Chart.defaults.font.size   = 11;

    /* ── 1. Performance Distribution (Doughnut) ── */
    new Chart(document.getElementById('perfPieChart'), {
        type: 'doughnut',
        data: {
            labels: ['Outstanding','Exceeds Expectations','Meets Expectations','Needs Improvement'],
            datasets: [{
                data: [<?php echo implode(',', array_values($perf_dist)); ?>],
                backgroundColor: ['#28a745','#17a2b8','#ffc107','#dc3545'],
                borderWidth: 3, borderColor: '#fff',
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '65%',
            plugins: { 
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed}` } }
            },
            animation: {
                animateRotate: true,
                animateScale: true,
                duration: 1500,
                easing: 'easeInOutQuart'
            }
        }
    });

    /* ── 2. Score Trend (Bar) — built-in year filter ── */
    const allYearsTrend = <?php echo json_encode($all_years_trend); ?>;
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    
    let currentTrendYear = <?php echo $default_trend_year; ?>;
    
    function getTrendDataForYear(yr) {
        const data = allYearsTrend[yr] || [];
        const vals = data.map(d => d.value || 0);
        return {
            labels: monthNames.map(m => m + ' ' + yr),
            vals: vals
        };
    }
    
    let initialTrend = getTrendDataForYear(currentTrendYear);

    // Color each bar by the performance level of its avg score
    function scoreLevelColor(v) {
        if (v <= 0)   return 'rgba(200,200,200,0.35)';  // no data — grey
        if (v >= 3.60) return 'rgba(40,167,69,0.82)';   // Outstanding
        if (v >= 2.60) return 'rgba(23,162,184,0.82)';  // Exceeds Expectations
        if (v >= 2.00) return 'rgba(255,193,7,0.82)';   // Meets Expectations
        return 'rgba(220,53,69,0.82)';                   // Needs Improvement
    }
    function scoreLevelBorder(v) {
        if (v <= 0)   return 'rgba(180,180,180,0.5)';
        if (v >= 3.60) return '#28a745';
        if (v >= 2.60) return '#17a2b8';
        if (v >= 2.00) return '#e0a800';
        return '#dc3545';
    }

    const trendChart = new Chart(document.getElementById('trendLineChart'), {
        type: 'bar',
        data: {
            labels: initialTrend.labels,
            datasets: [{
                label: 'Avg Score',
                data: initialTrend.vals,
                backgroundColor: initialTrend.vals.map(v => scoreLevelColor(v)),
                borderColor:     initialTrend.vals.map(v => scoreLevelBorder(v)),
                borderWidth: 1.5,
                borderRadius: 6,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: '#888' }
                },
                y: {
                    min: 0, max: 4,
                    ticks: {
                        color: '#888', stepSize: 1,
                        callback: v => v === 0 ? '0' : v.toFixed(0)
                    },
                    grid: { color: gridColor }
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => {
                            const v = ctx.parsed.y;
                            if (!v) return ' No data this month';
                            let lvl = v >= 3.60 ? 'Outstanding'
                                    : v >= 2.60 ? 'Exceeds Expectations'
                                    : v >= 2.00 ? 'Meets Expectations'
                                    : 'Needs Improvement';
                            return ` Avg: ${v.toFixed(2)}  (${lvl})`;
                        }
                    }
                }
            },
            animation: {
                duration: 2000,
                easing: 'easeInOutQuart',
                delay: (context) => {
                    // Stagger animation for each bar
                    return context.dataIndex * 100;
                }
            }
        }
    });

    // Year filter click logic
    document.querySelectorAll('.trend-year-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            // Un-style all buttons
            document.querySelectorAll('.trend-year-btn').forEach(b => {
                b.classList.remove('active', 'btn-secondary', 'text-white');
                if (!b.classList.contains('btn-outline-secondary')) {
                    b.classList.add('btn-outline-secondary');
                }
            });
            // Style active button
            this.classList.remove('btn-outline-secondary');
            this.classList.add('active', 'btn-secondary', 'text-white');
            
            const yr = parseInt(this.dataset.year);
            const newData = getTrendDataForYear(yr);
            
            // Update chart data
            trendChart.data.labels = newData.labels;
            trendChart.data.datasets[0].data = newData.vals;
            trendChart.data.datasets[0].backgroundColor = newData.vals.map(v => scoreLevelColor(v));
            trendChart.data.datasets[0].borderColor = newData.vals.map(v => scoreLevelBorder(v));
            trendChart.update();
            
            document.getElementById('trendSubtitle').textContent = yr + ' Monthly Averages';
        });
    });

    /* ── [NEW] 3. Department KRA vs. Behavior Breakdown Chart ── */
    <?php if (!empty($kra_beh_data)): ?>
    new Chart(document.getElementById('kraBehChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($kra_beh_data, 'department_name')); ?>,
            datasets: [
                {
                    label: 'KRA (Technical)',
                    data: <?php echo json_encode(array_map('floatval', array_column($kra_beh_data, 'avg_kra'))); ?>,
                    backgroundColor: 'rgba(41, 67, 6, 0.85)',
                    borderColor: '#294306',
                    borderWidth: 1,
                    borderRadius: 4
                },
                {
                    label: 'Behavior (Competencies)',
                    data: <?php echo json_encode(array_map('floatval', array_column($kra_beh_data, 'avg_behavior'))); ?>,
                    backgroundColor: 'rgba(189, 148, 20, 0.85)',
                    borderColor: '#BD9414',
                    borderWidth: 1,
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { grid: { display: false } },
                y: { min: 0, max: 4, ticks: { stepSize: 1 }, grid: { color: gridColor } }
            },
            plugins: {
                legend: { position: 'top', labels: { boxWidth: 12, font: { weight: 600 } } },
                tooltip: { callbacks: { label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y.toFixed(2)}` } }
            }
        }
    });
    <?php endif; ?>

    /* ── [NEW] 4. Evaluation Lifecycle Progress Doughnut Chart ── */
    <?php if (!empty($status_dist)): ?>
    new Chart(document.getElementById('lifecycleChart'), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_keys($status_dist)); ?>,
            datasets: [{
                data: <?php echo json_encode(array_values($status_dist)); ?>,
                backgroundColor: <?php 
                    $lifecycle_colors = [];
                    $status_colors = [
                        'Draft' => '#6c757d',
                        'Pending Self-Rating' => '#17a2b8',
                        'Pending Supervisor' => '#ffc107',
                        'Pending HR Consolidation' => '#fd7e14',
                        'Pending Manager' => '#007bff',
                        'Supervisor Confirmed' => '#28a745',
                        'Approved' => '#294306',
                        'Rejected' => '#dc3545',
                        'Returned' => '#e83e8c'
                    ];
                    foreach (array_keys($status_dist) as $st) {
                        $lifecycle_colors[] = $status_colors[$st] ?? '#888';
                    }
                    echo json_encode($lifecycle_colors); 
                ?>,
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%',
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed} evaluation(s)` } }
            }
        }
    });
    <?php endif; ?>

    /* ── [NEW] 5. Career Movements Monthly Volume Stacked Bar Chart ── */
    <?php if (!empty($movement_months)): ?>
    new Chart(document.getElementById('movementsChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($movement_months); ?>,
            datasets: [
                {
                    label: 'Promotions',
                    data: <?php echo json_encode($movement_types['Promotion']); ?>,
                    backgroundColor: '#28a745',
                    borderRadius: 4
                },
                {
                    label: 'Transfers',
                    data: <?php echo json_encode($movement_types['Transfer']); ?>,
                    backgroundColor: '#17a2b8',
                    borderRadius: 4
                },
                {
                    label: 'Role Changes',
                    data: <?php echo json_encode($movement_types['Role Change']); ?>,
                    backgroundColor: '#ffc107',
                    borderRadius: 4
                },
                {
                    label: 'Demotions',
                    data: <?php echo json_encode($movement_types['Demotion']); ?>,
                    backgroundColor: '#dc3545',
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { stacked: true, grid: { display: false } },
                y: { stacked: true, grid: { color: gridColor }, ticks: { stepSize: 1 } }
            },
            plugins: {
                legend: { position: 'top', labels: { boxWidth: 12, font: { weight: 600 } } },
                tooltip: { callbacks: { label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y} movement(s)` } }
            }
        }
    });
    <?php endif; ?>

    /* ── [NEW] 6. Tenure Bracket Performance Correlation Chart ── */
    <?php if (!empty($tenure_data)): ?>
    new Chart(document.getElementById('tenureChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($tenure_data, 'tenure_bracket')); ?>,
            datasets: [{
                label: 'Avg Score',
                data: <?php echo json_encode(array_map('floatval', array_column($tenure_data, 'avg_score'))); ?>,
                backgroundColor: 'rgba(59, 178, 115, 0.85)',
                borderColor: '#3BB273',
                borderWidth: 1.5,
                borderRadius: 6,
                barThickness: 40
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { grid: { display: false } },
                y: { min: 0, max: 4, ticks: { stepSize: 1 }, grid: { color: gridColor } }
            },
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => ` Avg Score: ${ctx.parsed.y.toFixed(2)}` } }
            }
        }
    });
    <?php endif; ?>

    /* ── [NEW] 7. Gender Performance Insights Dual-Axis Chart ── */
    <?php if (!empty($gender_data)): ?>
    new Chart(document.getElementById('genderPerfChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($gender_data, 'gender')); ?>,
            datasets: [
                {
                    label: 'Avg Score',
                    data: <?php echo json_encode(array_map('floatval', array_column($gender_data, 'avg_score'))); ?>,
                    backgroundColor: '#386FA4',
                    yAxisID: 'yScore',
                    borderRadius: 4,
                    barThickness: 30
                },
                {
                    label: 'Evaluation Count',
                    data: <?php echo json_encode(array_map('intval', array_column($gender_data, 'cnt'))); ?>,
                    backgroundColor: '#E94F37',
                    yAxisID: 'yCount',
                    borderRadius: 4,
                    barThickness: 30
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { grid: { display: false } },
                yScore: {
                    type: 'linear',
                    position: 'left',
                    min: 0,
                    max: 4,
                    ticks: { stepSize: 1 },
                    grid: { color: gridColor }
                },
                yCount: {
                    type: 'linear',
                    position: 'right',
                    min: 0,
                    ticks: { stepSize: 1 },
                    grid: { drawOnChartArea: false }
                }
            },
            plugins: {
                legend: { position: 'top', labels: { boxWidth: 12, font: { weight: 600 } } },
                tooltip: {
                    callbacks: {
                           label: ctx => {
                               if (ctx.datasetIndex === 0) {
                                   return ` Avg Score: ${ctx.parsed.y.toFixed(2)}`;
                               } else {
                                   return ` Evals Count: ${ctx.parsed.y}`;
                               }
                           }
                    }
                }
            }
        }
    });
    <?php endif; ?>
});
</script>

<?php require_once '../includes/footer.php'; ?>
