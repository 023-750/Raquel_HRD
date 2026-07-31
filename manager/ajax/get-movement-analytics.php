<?php
require_once '../../includes/session-check.php';
checkRole(['HR Manager']);
require_once '../../config/database.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

$type     = trim($_GET['type']      ?? 'hiring');   // 'hiring' | 'transfers' | 'promotions'
$year     = (int)($_GET['year']     ?? date('Y'));
$branch_id= (int)($_GET['branch_id']?? 0);

$out = [];

if ($type === 'hiring') {
    /* Monthly hiring trend: employees hired per month for a given year */
    $where = "WHERE YEAR(e.hire_date) = ?";
    $params = [$year]; $types = 'i';
    if ($branch_id > 0) { $where .= " AND e.branch_id = ?"; $params[] = $branch_id; $types .= 'i'; }
    $where .= " AND e.employee_id NOT IN (SELECT employee_id FROM users WHERE role='Admin' AND employee_id IS NOT NULL)";

    $sql = "SELECT MONTH(e.hire_date) AS mon, COUNT(*) AS cnt FROM employees e $where GROUP BY mon ORDER BY mon";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    $monthly = array_fill(1, 12, 0);
    while ($row = $res->fetch_assoc()) $monthly[(int)$row['mon']] = (int)$row['cnt'];
    $stmt->close();
    $out['monthly'] = array_values($monthly);

    /* Yearly totals (last 7 years) */
    $yearly_sql = "SELECT YEAR(e.hire_date) AS yr, COUNT(*) AS cnt
        FROM employees e
        WHERE YEAR(e.hire_date) BETWEEN ? AND ?
        AND e.employee_id NOT IN (SELECT employee_id FROM users WHERE role='Admin' AND employee_id IS NOT NULL)";
    $yparams = [date('Y')-6, date('Y')]; $ytypes = 'ii';
    if ($branch_id > 0) { $yearly_sql .= " AND e.branch_id = ?"; $yparams[] = $branch_id; $ytypes .= 'i'; }
    $yearly_sql .= " GROUP BY yr ORDER BY yr";
    $ys = $conn->prepare($yearly_sql);
    $ys->bind_param($ytypes, ...$yparams);
    $ys->execute();
    $yr = $ys->get_result();
    $yearly = [];
    while ($row = $yr->fetch_assoc()) $yearly[] = ['year' => (int)$row['yr'], 'count' => (int)$row['cnt']];
    $ys->close();
    $out['yearly'] = $yearly;

} elseif ($type === 'transfers') {
    /* Transfers per month for a given year */
    $where = "WHERE YEAR(cm.effective_date) = ? AND cm.movement_type = 'Transfer' AND cm.approval_status = 'Approved'";
    $params = [$year]; $types = 'i';
    if ($branch_id > 0) { $where .= " AND (cm.previous_branch_id = ? OR cm.new_branch_id = ?)"; $params[] = $branch_id; $params[] = $branch_id; $types .= 'ii'; }

    $sql = "SELECT MONTH(cm.effective_date) AS mon, COUNT(*) AS cnt FROM career_movements cm $where GROUP BY mon ORDER BY mon";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    $monthly = array_fill(1, 12, 0);
    while ($row = $res->fetch_assoc()) $monthly[(int)$row['mon']] = (int)$row['cnt'];
    $stmt->close();
    $out['monthly'] = array_values($monthly);

} elseif ($type === 'promotions') {
    /* Promotions per month for a given year */
    $where = "WHERE YEAR(cm.effective_date) = ? AND cm.movement_type = 'Promotion' AND cm.approval_status = 'Approved'";
    $params = [$year]; $types = 'i';
    if ($branch_id > 0) { $where .= " AND (cm.previous_branch_id = ? OR cm.new_branch_id = ?)"; $params[] = $branch_id; $params[] = $branch_id; $types .= 'ii'; }

    $sql = "SELECT MONTH(cm.effective_date) AS mon, COUNT(*) AS cnt FROM career_movements cm $where GROUP BY mon ORDER BY mon";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    $monthly = array_fill(1, 12, 0);
    while ($row = $res->fetch_assoc()) $monthly[(int)$row['mon']] = (int)$row['cnt'];
    $stmt->close();
    $out['monthly'] = array_values($monthly);

} elseif ($type === 'movement_summary') {
    /* Breakdown of ALL movement types (Promotion, Transfer, Demotion, Role Change) */
    $where = "WHERE cm.approval_status = 'Approved'";
    $params = []; $types = '';
    if ($year > 0) { $where .= " AND YEAR(cm.effective_date) = ?"; $params[] = $year; $types .= 'i'; }
    if ($branch_id > 0) { $where .= " AND (cm.previous_branch_id = ? OR cm.new_branch_id = ?)"; $params[] = $branch_id; $params[] = $branch_id; $types .= 'ii'; }

    $sql = "SELECT cm.movement_type, COUNT(*) AS cnt FROM career_movements cm $where GROUP BY cm.movement_type";
    $stmt = $conn->prepare($sql);
    if (!empty($params)) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    $types_data = ['Promotion' => 0, 'Transfer' => 0, 'Demotion' => 0, 'Role Change' => 0];
    while ($row = $res->fetch_assoc()) $types_data[$row['movement_type']] = (int)$row['cnt'];
    $stmt->close();
    $out['types'] = $types_data;
}

/* Available years */
$yr_result = $conn->query("SELECT DISTINCT YEAR(hire_date) AS yr FROM employees WHERE hire_date IS NOT NULL ORDER BY yr DESC LIMIT 10");
$available_years = [];
while ($yr_row = $yr_result->fetch_assoc()) $available_years[] = (int)$yr_row['yr'];
$out['available_years'] = $available_years;

echo json_encode(['success' => true] + $out);
