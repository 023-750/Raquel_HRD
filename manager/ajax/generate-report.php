<?php
/**
 * AJAX Endpoint: Generate Report Preview
 * Returns JSON with HTML table and count for on-screen preview.
 */
header('Content-Type: application/json');

require_once '../../includes/session-check.php';
checkRole(['HR Manager']);

$report_type = $_POST['report_type'] ?? '';
$branch_id   = intval($_POST['branch_id'] ?? 0);
$department_id = intval($_POST['department'] ?? 0);
$date_from   = trim($_POST['date_from'] ?? '');
$date_to     = trim($_POST['date_to'] ?? '');

$html = '';
$count = 0;

function reportSummaryGrid(array $items): string {
    $html = '<div class="report-summary-grid">';
    foreach ($items as $item) {
        $html .= '<div class="report-summary-card">';
        $html .= '<span>' . htmlspecialchars($item['label']) . '</span>';
        $html .= '<strong>' . htmlspecialchars((string)$item['value']) . '</strong>';
        $html .= '</div>';
    }
    $html .= '</div>';
    return $html;
}

try {
    switch ($report_type) {

        // ===========================
        // EMPLOYEE MASTERLIST
        // ===========================
        case 'employee_masterlist':
            $where = "WHERE e.is_active = 1 AND e.deleted_at IS NULL AND e.employee_id NOT IN (SELECT employee_id FROM users WHERE role = 'Admin' AND employee_id IS NOT NULL)";
            $params = [];
            $types = '';

            if ($branch_id > 0) {
                $where .= " AND e.branch_id = ?";
                $params[] = $branch_id;
                $types .= 'i';
            }
            if ($department_id > 0) {
                $where .= " AND e.department_id = ?";
                $params[] = $department_id;
                $types .= 'i';
            }
            if (!empty($date_from)) {
                $where .= " AND e.hire_date >= ?";
                $params[] = $date_from;
                $types .= 's';
            }
            if (!empty($date_to)) {
                $where .= " AND e.hire_date <= ?";
                $params[] = $date_to;
                $types .= 's';
            }

            $sql = "SELECT e.employee_id, e.first_name, e.last_name, e.middle_name,
                           e.job_title, d.department_name, e.hire_date, e.employment_status, e.employment_type,
                           b.branch_name,
                           c.mobile_number, c.personal_email
                    FROM employees e
                    LEFT JOIN branches b ON e.branch_id = b.branch_id
                    LEFT JOIN departments d ON e.department_id = d.department_id
                    LEFT JOIN employee_contacts c ON e.employee_id = c.employee_id
                    $where
                    ORDER BY e.last_name, e.first_name";

            $stmt = $conn->prepare($sql);
            if (!empty($params)) $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $count = $result->num_rows;

            if ($count === 0) {
                echo json_encode(['success' => false, 'message' => 'No employees found matching your filters.']);
                exit;
            }

            $regularCount = 0;
            $probationaryCount = 0;
            $branchNames = [];
            $departmentNames = [];
            $rows = [];

            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
                if ($row['employment_status'] === 'Regular') {
                    $regularCount++;
                }
                if ($row['employment_status'] === 'Probationary') {
                    $probationaryCount++;
                }
                if (!empty($row['branch_name'])) {
                    $branchNames[$row['branch_name']] = true;
                }
                if (!empty($row['department_name'])) {
                    $departmentNames[$row['department_name']] = true;
                }
            }

            $html = reportSummaryGrid([
                ['label' => 'Total Employees', 'value' => $count],
                ['label' => 'Regular', 'value' => $regularCount],
                ['label' => 'Probationary', 'value' => $probationaryCount],
                ['label' => 'Branches Covered', 'value' => count($branchNames)],
            ]);
            $html .= '<div class="table-responsive report-table-wrap"><table class="table table-hover table-striped report-preview-table report-masterlist-table">';
            $html .= '<thead><tr>
                <th>#</th>
                <th>Employee Name</th>
                <th>Position</th>
                <th>Department</th>
                <th>Branch</th>
                <th>Hire Date</th>
                <th>Status</th>
                <th>Type</th>
                <th>Mobile</th>
                <th>Email</th>
            </tr></thead><tbody>';

            $i = 1;
            foreach ($rows as $row) {
                $fullName = htmlspecialchars($row['last_name'] . ', ' . $row['first_name'] . (!empty($row['middle_name']) ? ' ' . $row['middle_name'] : ''));
                $statusClass = $row['employment_status'] === 'Regular' ? 'bg-success' : ($row['employment_status'] === 'Probationary' ? 'bg-warning text-dark' : 'bg-secondary');
                $html .= '<tr>
                    <td data-label="#">' . $i++ . '</td>
                    <td data-label="Employee Name"><strong>' . $fullName . '</strong></td>
                    <td data-label="Position">' . htmlspecialchars($row['job_title'] ?? '') . '</td>
                    <td data-label="Department">' . htmlspecialchars($row['department_name'] ?? 'N/A') . '</td>
                    <td data-label="Branch">' . htmlspecialchars($row['branch_name'] ?? 'N/A') . '</td>
                    <td data-label="Hire Date">' . ($row['hire_date'] ? date('M d, Y', strtotime($row['hire_date'])) : 'N/A') . '</td>
                    <td data-label="Status"><span class="badge ' . $statusClass . '">' . htmlspecialchars($row['employment_status'] ?? '') . '</span></td>
                    <td data-label="Type">' . htmlspecialchars($row['employment_type'] ?? '') . '</td>
                    <td data-label="Mobile">' . htmlspecialchars($row['mobile_number'] ?? 'N/A') . '</td>
                    <td data-label="Email">' . htmlspecialchars($row['personal_email'] ?? 'N/A') . '</td>
                </tr>';
            }
            $html .= '</tbody></table></div>';
            $stmt->close();
            break;

        // ===========================
        // PERFORMANCE SUMMARY
        // ===========================
        case 'performance_summary':
            $where = "WHERE ev.status = 'Approved' AND ev.deleted_at IS NULL AND ev.employee_id NOT IN (SELECT employee_id FROM users WHERE role = 'Admin' AND employee_id IS NOT NULL)";
            $params = [];
            $types = '';

            if ($branch_id > 0) {
                $where .= " AND e.branch_id = ?";
                $params[] = $branch_id;
                $types .= 'i';
            }
            if ($department_id > 0) {
                $where .= " AND e.department_id = ?";
                $params[] = $department_id;
                $types .= 'i';
            }
            if (!empty($date_from)) {
                $where .= " AND ev.approved_date >= ?";
                $params[] = $date_from;
                $types .= 's';
            }
            if (!empty($date_to)) {
                $where .= " AND ev.approved_date <= ?";
                $params[] = $date_to . ' 23:59:59';
                $types .= 's';
            }

            $sql = "SELECT e.employee_id, 
                           CONCAT(e.last_name, ', ', e.first_name) as employee_name,
                           e.job_title, d.department_name,
                           b.branch_name,
                           et.template_name,
                           ev.total_score, ev.performance_level,
                           ev.evaluation_period_start, ev.evaluation_period_end,
                           ev.approved_date
                    FROM evaluations ev
                    LEFT JOIN employees e ON ev.employee_id = e.employee_id
                    LEFT JOIN branches b ON e.branch_id = b.branch_id
                    LEFT JOIN departments d ON e.department_id = d.department_id
                    LEFT JOIN evaluation_templates et ON ev.template_id = et.template_id
                    $where
                    ORDER BY ev.approved_date DESC, e.last_name";

            $stmt = $conn->prepare($sql);
            if (!empty($params)) $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $count = $result->num_rows;

            if ($count === 0) {
                echo json_encode(['success' => false, 'message' => 'No approved evaluations found matching your filters.']);
                exit;
            }

            $scoreTotal = 0;
            $ratedEmployees = [];
            $levelCounts = [];
            $latestApproved = '';
            $rows = [];

            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
                $scoreTotal += (float)$row['total_score'];
                if (!empty($row['employee_id'])) {
                    $ratedEmployees[$row['employee_id']] = true;
                }
                $level = $row['performance_level'] ?: 'N/A';
                $levelCounts[$level] = ($levelCounts[$level] ?? 0) + 1;
                if (!$latestApproved && !empty($row['approved_date'])) {
                    $latestApproved = date('M d, Y', strtotime($row['approved_date']));
                }
            }
            arsort($levelCounts);
            $topLevel = key($levelCounts) ?: 'N/A';
            $averageScore = $count > 0 ? number_format($scoreTotal / $count, 1) . '%' : '0.0%';

            $html = reportSummaryGrid([
                ['label' => 'Approved Evaluations', 'value' => $count],
                ['label' => 'Employees Rated', 'value' => count($ratedEmployees)],
                ['label' => 'Average Score', 'value' => $averageScore],
                ['label' => 'Top Level', 'value' => $topLevel],
            ]);
            $html .= '<div class="table-responsive report-table-wrap"><table class="table table-hover table-striped report-preview-table">';
            $html .= '<thead><tr>
                <th>#</th>
                <th>Employee</th>
                <th>Position</th>
                <th>Department</th>
                <th>Branch</th>
                <th>Template</th>
                <th>Eval Period</th>
                <th>Score</th>
                <th>Performance Level</th>
                <th>Approved Date</th>
            </tr></thead><tbody>';

            $i = 1;
            foreach ($rows as $row) {
                $perfClass = 'bg-secondary';
                switch ($row['performance_level']) {
                    case 'Excellent': $perfClass = 'bg-success'; break;
                    case 'Above Average': $perfClass = 'bg-info'; break;
                    case 'Average': $perfClass = 'bg-warning text-dark'; break;
                    case 'Needs Improvement': $perfClass = 'bg-danger'; break;
                }
                $period = '';
                if ($row['evaluation_period_start'] && $row['evaluation_period_end']) {
                    $period = date('M Y', strtotime($row['evaluation_period_start'])) . ' - ' . date('M Y', strtotime($row['evaluation_period_end']));
                }
                $html .= '<tr>
                    <td data-label="#">' . $i++ . '</td>
                    <td data-label="Employee"><strong>' . htmlspecialchars($row['employee_name']) . '</strong></td>
                    <td data-label="Position">' . htmlspecialchars($row['job_title'] ?? '') . '</td>
                    <td data-label="Department">' . htmlspecialchars($row['department_name'] ?? 'N/A') . '</td>
                    <td data-label="Branch">' . htmlspecialchars($row['branch_name'] ?? 'N/A') . '</td>
                    <td data-label="Template">' . htmlspecialchars($row['template_name'] ?? '') . '</td>
                    <td data-label="Eval Period">' . $period . '</td>
                    <td data-label="Score"><strong>' . number_format($row['total_score'], 1) . '%</strong></td>
                    <td data-label="Performance Level"><span class="badge ' . $perfClass . '">' . htmlspecialchars($row['performance_level'] ?? 'N/A') . '</span></td>
                    <td data-label="Approved Date">' . ($row['approved_date'] ? date('M d, Y', strtotime($row['approved_date'])) : 'N/A') . '</td>
                </tr>';
            }
            $html .= '</tbody></table></div>';
            $stmt->close();
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid report type selected.']);
            exit;
    }

    echo json_encode(['success' => true, 'html' => $html, 'count' => $count]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
