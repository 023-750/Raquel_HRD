<?php
require_once '../../includes/session-check.php';
checkRole(['Employee']);
require_once '../../includes/functions.php';

header('Content-Type: application/json');

$employee_id = (int)($_SESSION['employee_id'] ?? 0);
if ($employee_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

// ── All approved evaluations ─────────────────────────────────────────────────
$evals_stmt = $conn->prepare("
    SELECT
        ev.evaluation_id,
        ev.evaluation_type,
        ev.evaluation_period_start,
        ev.evaluation_period_end,
        ev.total_score,
        ev.kra_subtotal,
        ev.behavior_average,
        ev.performance_level,
        ev.approved_date,
        ev.supervisor_comments,
        ev.manager_comments,
        ev.evaluator_comments,
        et.template_name,
        YEAR(ev.evaluation_period_end) AS eval_year
    FROM evaluations ev
    LEFT JOIN evaluation_templates et ON ev.template_id = et.template_id
    WHERE ev.employee_id = ?
      AND ev.status = 'Approved'
      AND ev.deleted_at IS NULL
    ORDER BY ev.evaluation_period_end DESC, ev.approved_date DESC
");
$evals_stmt->bind_param("i", $employee_id);
$evals_stmt->execute();
$all_evals = $evals_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$evals_stmt->close();

// ── Build yearly chart data ──────────────────────────────────────────────────
$yearly_map = [];
foreach ($all_evals as $ev) {
    $yr = (int)$ev['eval_year'];
    if ($yr <= 0) continue;
    if (!isset($yearly_map[$yr])) $yearly_map[$yr] = ['total' => 0, 'count' => 0];
    $yearly_map[$yr]['total'] += (float)$ev['total_score'];
    $yearly_map[$yr]['count']++;
}

ksort($yearly_map);
$all_years    = array_keys($yearly_map);
$last_5_years = array_slice($all_years, -5);

$chart_labels = [];
$chart_scores = [];
foreach ($last_5_years as $yr) {
    $chart_labels[] = (string)$yr;
    $chart_scores[] = round($yearly_map[$yr]['total'] / $yearly_map[$yr]['count'], 2);
}

// ── Aggregate stats ──────────────────────────────────────────────────────────
$total_count  = count($all_evals);
$avg_score    = $total_count > 0
    ? round(array_sum(array_column($all_evals, 'total_score')) / $total_count, 2)
    : null;
$latest_eval  = $all_evals[0] ?? null;
$latest_score = $latest_eval ? (float)$latest_eval['total_score'] : null;
$prev_eval    = $all_evals[1] ?? null;
$prev_score   = $prev_eval   ? (float)$prev_eval['total_score']   : null;

// ── Trend classification ─────────────────────────────────────────────────────
$trend = 'stable';
if ($latest_score !== null && $prev_score !== null) {
    $diff = $latest_score - $prev_score;
    if ($diff >= 0.15)      $trend = 'improving';
    elseif ($diff <= -0.15) $trend = 'declining';
}
if ($avg_score !== null && $avg_score >= 3.60 && $total_count >= 3) {
    $trend = 'consistently_outstanding';
}

// ── Career readiness (out of 100) ────────────────────────────────────────────
$readiness = 0;
if ($avg_score !== null) {
    $eval_weight = min(($avg_score / 4.00) * 60, 60);

    $hire_stmt = $conn->prepare("SELECT hire_date FROM employees WHERE employee_id = ?");
    $hire_stmt->bind_param("i", $employee_id);
    $hire_stmt->execute();
    $hire_row = $hire_stmt->get_result()->fetch_assoc();
    $hire_stmt->close();

    $years_service = 0;
    if (!empty($hire_row['hire_date'])) {
        $d = (new DateTime($hire_row['hire_date']))->diff(new DateTime());
        $years_service = $d->y + ($d->m / 12);
    }

    $service_weight    = min(($years_service / 5) * 25, 25);
    $eval_count_weight = min(($total_count / 3) * 15, 15);
    $readiness = min((int)round($eval_weight + $service_weight + $eval_count_weight), 100);
}

// ── Next level target ────────────────────────────────────────────────────────
$next_level    = null;
$points_needed = null;
if ($avg_score !== null) {
    if ($avg_score < 2.00)     { $next_level = 'Meets Expectations';   $points_needed = round(2.00 - $avg_score, 2); }
    elseif ($avg_score < 2.60) { $next_level = 'Exceeds Expectations'; $points_needed = round(2.60 - $avg_score, 2); }
    elseif ($avg_score < 3.60) { $next_level = 'Outstanding';          $points_needed = round(3.60 - $avg_score, 2); }
    else                       { $next_level = 'Outstanding';           $points_needed = 0; }
}

echo json_encode([
    'success'       => true,
    'evaluations'   => $all_evals,
    'chart_labels'  => $chart_labels,
    'chart_scores'  => $chart_scores,
    'avg_score'     => $avg_score,
    'latest_score'  => $latest_score,
    'prev_score'    => $prev_score,
    'total_count'   => $total_count,
    'trend'         => $trend,
    'readiness'     => $readiness,
    'next_level'    => $next_level,
    'points_needed' => $points_needed,
    'latest_eval'   => $latest_eval,
]);