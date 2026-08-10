<?php
$page_title = 'Career Movements';
require_once '../includes/session-check.php';
checkRole(['HR Supervisor']);
require_once '../includes/functions.php';

$movement_ready = ensureCareerProgressionMovements($conn);
if ($movement_ready) {
    applyDueCareerProgressionMovements($conn);
}

$current_user_id = (int) ($_SESSION['user_id'] ?? 0);
$sup_branch_id   = (int) ($_SESSION['branch_id'] ?? 0);

// Resolve this supervisor's own employee_id so we can block self-movements
$sup_emp_id = 0;
$sup_emp_stmt = $conn->prepare("SELECT employee_id FROM users WHERE user_id = ? LIMIT 1");
$sup_emp_stmt->bind_param("i", $current_user_id);
$sup_emp_stmt->execute();
$sup_emp_row = $sup_emp_stmt->get_result()->fetch_assoc();
$sup_emp_stmt->close();
if ($sup_emp_row) {
    $sup_emp_id = (int)$sup_emp_row['employee_id'];
}

// ── Helper: apply movement immediately + RBAC ───────────────────────────────
function applyMovementNow($conn, array $movement, int $movement_id): void
{
    executeCareerMovementApplication($conn, $movement, $movement_id);
}

// ── POST: Create movement (HR Supervisor direct submission) ─────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_movement'])) {
    if (!$movement_ready) {
        redirectWith(BASE_URL . '/supervisor/career-movements.php', 'danger', 'Career Movements could not be initialized.');
    }

    $department_id  = (int)  ($_POST['department_id']   ?? 0);
    $employee_id    = (int)  ($_POST['employee_id']      ?? 0);
    $movement_type  = trim(  $_POST['movement_type']     ?? '');
    $new_position   = trim(  $_POST['new_position']      ?? '');
    $new_branch_id  = ($_POST['new_branch_id'] ?? '') !== '' ? (int) $_POST['new_branch_id'] : null;
    $effective_date = trim(  $_POST['effective_date']    ?? '');
    $reason         = trim(  $_POST['reason']            ?? '');
    $allowed_types  = ['Promotion', 'Transfer', 'Demotion', 'Role Change'];

    // Basic validation
    if ($department_id <= 0 || $employee_id <= 0 || !in_array($movement_type, $allowed_types, true) || $effective_date === '' || $reason === '') {
        redirectWith(BASE_URL . '/supervisor/career-movements.php', 'danger', 'Please complete all required Career Movement fields.');
    }

    if ($movement_type === 'Transfer') {
        if (empty($new_branch_id)) {
            redirectWith(BASE_URL . '/supervisor/career-movements.php', 'danger', 'New Branch is required for a Transfer.');
        }
    } else {
        if ($new_position === '') {
            redirectWith(BASE_URL . '/supervisor/career-movements.php', 'danger', 'New Position is required for ' . $movement_type . '.');
        }
    }

    // Safeguard: cannot file for themselves
    if ($sup_emp_id > 0 && $employee_id === $sup_emp_id) {
        redirectWith(BASE_URL . '/supervisor/career-movements.php', 'danger', 'You cannot file a career movement for yourself.');
    }

    // Safeguard: cannot file for HR Managers
    $mgr_check = $conn->prepare("SELECT user_id FROM users WHERE employee_id=? AND role='HR Manager' AND is_active=1 LIMIT 1");
    $mgr_check->bind_param("i", $employee_id);
    $mgr_check->execute();
    if ($mgr_check->get_result()->num_rows > 0) {
        $mgr_check->close();
        redirectWith(BASE_URL . '/supervisor/career-movements.php', 'danger', 'HR Managers\' career movements must be processed by an HR Manager or higher.');
    }
    $mgr_check->close();

    // Validate employee belongs to selected department (and branch if supervisor is branch-scoped)
    if ($sup_branch_id > 0) {
        $emp_chk = $conn->prepare("SELECT employee_id, job_title, branch_id FROM employees WHERE employee_id=? AND department_id=? AND branch_id=? AND is_active=1 LIMIT 1");
        $emp_chk->bind_param("iii", $employee_id, $department_id, $sup_branch_id);
    } else {
        $emp_chk = $conn->prepare("SELECT employee_id, job_title, branch_id FROM employees WHERE employee_id=? AND department_id=? AND is_active=1 LIMIT 1");
        $emp_chk->bind_param("ii", $employee_id, $department_id);
    }
    $emp_chk->execute();
    $employee = $emp_chk->get_result()->fetch_assoc();
    $emp_chk->close();

    if (!$employee) {
        redirectWith(BASE_URL . '/supervisor/career-movements.php', 'danger', 'Selected employee is not valid or not within your authorized department/branch.');
    }

    $previous_branch_id = !empty($employee['branch_id']) ? (int)$employee['branch_id'] : null;
    if ($new_branch_id === $previous_branch_id) { $new_branch_id = null; }
    $previous_position  = $employee['job_title'] ?? '';

    // Fetch full employee name
    $emp_name_stmt = $conn->prepare("SELECT first_name, last_name FROM employees WHERE employee_id=? LIMIT 1");
    $emp_name_stmt->bind_param("i", $employee_id);
    $emp_name_stmt->execute();
    $emp_name_row = $emp_name_stmt->get_result()->fetch_assoc();
    $emp_name_stmt->close();
    $employee_name = trim(($emp_name_row['first_name'] ?? '') . ' ' . ($emp_name_row['last_name'] ?? ''));

    $insert = $conn->prepare("
        INSERT INTO career_movements
            (employee_id, movement_type, previous_position, new_position,
             previous_branch_id, new_branch_id, effective_date, reason,
             logged_by, approval_status, initiated_by_name, initiated_by_role,
             initiated_via, request_source)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', 'HR Supervisor', 'HR Supervisor', 'Memo', 'HR Portal')
    ");
    $insert->bind_param("isssiissi",
        $employee_id, $movement_type, $previous_position, $new_position,
        $previous_branch_id, $new_branch_id, $effective_date, $reason, $current_user_id
    );

    if (!$insert->execute()) {
        $insert->close();
        redirectWith(BASE_URL . '/supervisor/career-movements.php', 'danger', 'Unable to create the career movement.');
    }
    $movement_id = $insert->insert_id;
    $insert->close();

    $managers = $conn->query("SELECT user_id FROM users WHERE role='HR Manager' AND is_active=1");
    while ($mgr = $managers->fetch_assoc()) {
        createNotification($conn, (int)$mgr['user_id'],
            'Career Movement Submitted',
            "A {$movement_type} has been submitted for {$employee_name}.",
            BASE_URL . '/manager/career-movements.php');
    }
    logAudit($conn, $current_user_id, 'CREATE', 'Career Movement', $movement_id, "Submitted {$movement_type} for {$employee_name}.");
    redirectWith(BASE_URL . '/supervisor/career-movements.php', 'success', 'Career movement submitted for HR Manager review.');
}

// ── POST: Approve / Reject ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['movement_action'])) {
    if (!$movement_ready) {
        redirectWith(BASE_URL . '/supervisor/career-movements.php', 'danger', 'Career Movements could not be initialized.');
    }
    $movement_id = (int)($_POST['movement_id'] ?? 0);
    $action      = trim($_POST['movement_action'] ?? '');

    if ($movement_id <= 0 || !in_array($action, ['Approve','Reject'], true)) {
        redirectWith(BASE_URL . '/supervisor/career-movements.php', 'danger', 'Invalid action.');
    }

    // Task 7.3/7.4 — Fetch movement; accept both HR Portal pending AND Portal_Requests at Pending_HR_Supervisor
    $stmt = $conn->prepare("
        SELECT cm.*, CONCAT(e.first_name,' ',e.last_name) AS employee_name
        FROM career_movements cm
        JOIN employees e ON cm.employee_id = e.employee_id
        WHERE cm.movement_id = ?
          AND (
            (cm.approval_status = 'Pending' AND (cm.portal_workflow_stage IS NULL OR cm.request_source = 'HR Portal'))
            OR
            (cm.request_source = 'Employee Portal' AND cm.portal_workflow_stage = 'Pending_HR_Supervisor')
          )
        LIMIT 1
    ");
    $stmt->bind_param("i", $movement_id);
    $stmt->execute();
    $movement = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$movement) {
        redirectWith(BASE_URL . '/supervisor/career-movements.php', 'danger', 'Movement not found or already processed.');
    }

    // Self-approval block
    if ((int)$movement['logged_by'] === $current_user_id) {
        redirectWith(BASE_URL . '/supervisor/career-movements.php', 'danger', 'You cannot approve or reject a request you submitted.');
    }

    // Detect whether this is an Employee Portal request (Portal_Request) or an HR Portal request
    $is_portal_request = (
        ($movement['request_source'] ?? '') === 'Employee Portal' &&
        ($movement['portal_workflow_stage'] ?? null) !== null
    );

    // ── Task 7.3 — APPROVE ────────────────────────────────────────────────────
    if ($action === 'Approve') {

        if ($is_portal_request) {
            // Portal_Request: stage guard via checkApprovalAuthorization
            if (!checkApprovalAuthorization($movement, $current_user_id, $sup_branch_id, 'HR Supervisor', 'Pending_HR_Supervisor')) {
                redirectWith(BASE_URL . '/supervisor/career-movements.php', 'danger', 'You are not authorized to approve this request.');
            }

            // Advance stage to Pending_HR_Manager, record HR Supervisor decision
            $upd = $conn->prepare("
                UPDATE career_movements
                SET portal_workflow_stage       = 'Pending_HR_Manager',
                    hr_supervisor_approved_by   = ?,
                    hr_supervisor_decision_date = NOW()
                WHERE movement_id = ?
            ");
            $upd->bind_param("ii", $current_user_id, $movement_id);
            $upd->execute();
            $upd->close();

            // Notify all active HR Managers
            $hr_mgr_res = $conn->query("SELECT user_id FROM users WHERE role = 'HR Manager' AND is_active = 1");
            if ($hr_mgr_res && $hr_mgr_res->num_rows > 0) {
                while ($hm = $hr_mgr_res->fetch_assoc()) {
                    createNotification(
                        $conn,
                        (int) $hm['user_id'],
                        'Transfer Request Pending Your Approval',
                        "A Transfer request for {$movement['employee_name']} has been approved by HR Supervisor and requires your final decision.",
                        BASE_URL . '/manager/career-movements.php'
                    );
                }
                $hr_mgr_res->free();
            } else {
                error_log("Career movement notification skipped: no active HR Manager users found for movement_id={$movement_id}.");
            }

            logAudit($conn, $current_user_id, 'APPROVE', 'Career Movement', $movement_id,
                "HR Supervisor approved portal Transfer request for {$movement['employee_name']}.",
                ['module' => 'Career Progression']);
            redirectWith(BASE_URL . '/supervisor/career-movements.php', 'success', 'Transfer request approved and forwarded to HR Manager.');

        } else {
            // ── Existing HR Portal single-step approval flow (unchanged) ─────
            $status   = 'Approved';
            $comments = trim($_POST['manager_comments'] ?? '');

            $upd = $conn->prepare("UPDATE career_movements SET approval_status=?, approved_by=?, decision_date=NOW(), manager_comments=?, is_applied=0 WHERE movement_id=?");
            $upd->bind_param("sisi", $status, $current_user_id, $comments, $movement_id);
            $upd->execute();
            $upd->close();

            // Apply immediately if effective date has already passed / is today
            if ($movement['effective_date'] <= date('Y-m-d')) {
                applyMovementNow($conn, $movement, $movement_id);
            }

            if (!empty($movement['logged_by'])) {
                createNotification($conn, (int)$movement['logged_by'],
                    "Career Movement {$status}",
                    "The {$movement['movement_type']} for {$movement['employee_name']} has been {$status}.",
                    BASE_URL . '/supervisor/career-movements.php');
            }
            $emp_user = getPreferredLinkedUserId($conn, $movement['employee_id'], 'employee_portal');
            if ($emp_user) {
                createNotification($conn, $emp_user,
                    "Career Movement {$status}",
                    "Your career movement ({$movement['movement_type']}) has been {$status}.",
                    BASE_URL . '/employee/notifications.php');
            }

            logAudit($conn, $current_user_id, 'APPROVE', 'Career Movement', $movement_id,
                "Approved {$movement['movement_type']} for {$movement['employee_name']}.");
            redirectWith(BASE_URL . '/supervisor/career-movements.php', 'success', 'Career movement approved.');
        }
    }

    // ── Task 7.4 — REJECT ─────────────────────────────────────────────────────
    if ($action === 'Reject') {

        if ($is_portal_request) {
            // Portal_Request: stage guard via checkApprovalAuthorization
            if (!checkApprovalAuthorization($movement, $current_user_id, $sup_branch_id, 'HR Supervisor', 'Pending_HR_Supervisor')) {
                redirectWith(BASE_URL . '/supervisor/career-movements.php', 'danger', 'You are not authorized to reject this request.');
            }

            // Require non-empty rejection comments (1–1000 chars)
            $hr_supervisor_comments = trim($_POST['manager_comments'] ?? '');
            if (strlen($hr_supervisor_comments) === 0 || strlen($hr_supervisor_comments) > 1000) {
                redirectWith(BASE_URL . '/supervisor/career-movements.php', 'danger', 'A rejection reason is required (1–1000 characters).');
            }

            // Terminate stage at Rejected, set approval_status = Rejected
            $upd = $conn->prepare("
                UPDATE career_movements
                SET portal_workflow_stage       = 'Rejected',
                    approval_status             = 'Rejected',
                    hr_supervisor_approved_by   = ?,
                    hr_supervisor_decision_date = NOW(),
                    hr_supervisor_comments      = ?
                WHERE movement_id = ?
            ");
            $upd->bind_param("isi", $current_user_id, $hr_supervisor_comments, $movement_id);
            $upd->execute();
            $upd->close();

            // Resolve submitter's Employee Portal user for rejection notification
            $submitter_portal_user_id = null;
            $logged_by_user_id = (int)($movement['logged_by'] ?? 0);
            if ($logged_by_user_id > 0) {
                $sub_stmt = $conn->prepare("SELECT employee_id FROM users WHERE user_id = ? LIMIT 1");
                $sub_stmt->bind_param("i", $logged_by_user_id);
                $sub_stmt->execute();
                $sub_row = $sub_stmt->get_result()->fetch_assoc();
                $sub_stmt->close();
                if ($sub_row && !empty($sub_row['employee_id'])) {
                    $submitter_portal_user_id = getPreferredLinkedUserId($conn, (int)$sub_row['employee_id'], 'employee_portal');
                }
            }

            if ($submitter_portal_user_id) {
                createNotification(
                    $conn,
                    $submitter_portal_user_id,
                    'Transfer Request Rejected',
                    "Your Transfer request for {$movement['employee_name']} has been rejected by HR Supervisor. Reason: {$hr_supervisor_comments}",
                    BASE_URL . '/employee/career-movement-request.php'
                );
            } else {
                error_log("Rejection notification skipped: could not resolve submitter portal user for movement_id={$movement_id}.");
            }

            logAudit($conn, $current_user_id, 'REJECT', 'Career Movement', $movement_id,
                "HR Supervisor rejected portal Transfer request for {$movement['employee_name']}. Reason: {$hr_supervisor_comments}",
                ['module' => 'Career Progression']);
            redirectWith(BASE_URL . '/supervisor/career-movements.php', 'success', 'Transfer request rejected.');

        } else {
            // ── Existing HR Portal single-step rejection flow (unchanged) ────
            $status   = 'Rejected';
            $comments = trim($_POST['manager_comments'] ?? '');

            $upd = $conn->prepare("UPDATE career_movements SET approval_status=?, approved_by=?, decision_date=NOW(), manager_comments=?, is_applied=0 WHERE movement_id=?");
            $upd->bind_param("sisi", $status, $current_user_id, $comments, $movement_id);
            $upd->execute();
            $upd->close();

            if (!empty($movement['logged_by'])) {
                createNotification($conn, (int)$movement['logged_by'],
                    "Career Movement {$status}",
                    "The {$movement['movement_type']} for {$movement['employee_name']} has been {$status}.",
                    BASE_URL . '/supervisor/career-movements.php');
            }
            $emp_user = getPreferredLinkedUserId($conn, $movement['employee_id'], 'employee_portal');
            if ($emp_user) {
                createNotification($conn, $emp_user,
                    "Career Movement {$status}",
                    "Your career movement ({$movement['movement_type']}) has been {$status}.",
                    BASE_URL . '/employee/notifications.php');
            }

            logAudit($conn, $current_user_id, 'REJECT', 'Career Movement', $movement_id,
                "Rejected {$movement['movement_type']} for {$movement['employee_name']}.");
            redirectWith(BASE_URL . '/supervisor/career-movements.php', 'warning', 'Career movement rejected.');
        }
    }
}

// ── Fetch display data ───────────────────────────────────────────────────────
require_once '../includes/header.php';

$branches    = [];
$branch_names = [];
$movements   = [];
$counts      = ['Submitted'=>0,'Pending'=>0,'Approved'=>0,'Rejected'=>0,'Applied'=>0];

// Departments
$dept_result = $conn->query("SELECT department_id, department_name FROM departments WHERE is_active=1 ORDER BY department_name");
$departments = [];
while ($row = $dept_result->fetch_assoc()) $departments[] = $row;

// Job titles grouped by department (for JS)
$jt_result = $conn->query("SELECT job_title_id, job_title, department_id FROM job_titles WHERE is_active=1 ORDER BY department_id, job_title");
$dept_positions = []; // dept_id => [positions]
while ($row = $jt_result->fetch_assoc()) {
    $dept_positions[$row['department_id']][] = ['id' => $row['job_title_id'], 'title' => $row['job_title']];
}

// Employees grouped by department (+ branch filter)
// We send to JS so the cascade works client-side
$emp_sql_params = "";
$emp_sql_where  = "e.is_active = 1
      AND e.employee_id NOT IN (SELECT employee_id FROM users WHERE role='Admin' AND employee_id IS NOT NULL)
      AND e.employee_id NOT IN (SELECT employee_id FROM users WHERE role='HR Manager' AND is_active=1 AND employee_id IS NOT NULL)";
if ($sup_branch_id > 0) {
    $emp_sql_where .= " AND e.branch_id = {$sup_branch_id}";
}
$emp_result = $conn->query("
    SELECT e.employee_id, e.employee_code, e.first_name, e.last_name, e.job_title,
           e.branch_id, e.department_id,
           b.branch_name, d.department_name,
           rc.rank_name
    FROM employees e
    LEFT JOIN branches b ON e.branch_id = b.branch_id
    LEFT JOIN departments d ON e.department_id = d.department_id
    LEFT JOIN rank_categories rc ON e.rank_category_id = rc.rank_category_id
    WHERE {$emp_sql_where}
    ORDER BY e.last_name, e.first_name
");
$dept_employees = []; // dept_id => [employees]
$all_employees  = [];
while ($row = $emp_result->fetch_assoc()) {
    $did = (int)$row['department_id'];
    $dept_employees[$did][] = $row;
    $all_employees[$row['employee_id']] = $row;
}

$branch_result = $conn->query("SELECT branch_id, branch_name FROM branches ORDER BY branch_name");
while ($row = $branch_result->fetch_assoc()) {
    $branches[]  = $row;
    $branch_names[(string)$row['branch_id']] = $row['branch_name'];
}

if ($movement_ready) {
    $stmt = $conn->prepare("
        SELECT cm.*,
            e.employee_code,
            e.job_title AS current_job_title,
            CONCAT(e.last_name, ', ', e.first_name) AS employee_name,
            d.department_name,
            pb.branch_name AS previous_branch_name,
            nb.branch_name AS new_branch_name,
            u1.full_name   AS logged_by_name,
            u2.full_name   AS approved_by_name
        FROM career_movements cm
        JOIN employees  e  ON cm.employee_id       = e.employee_id
        LEFT JOIN departments d ON e.department_id = d.department_id
        LEFT JOIN branches pb ON cm.previous_branch_id = pb.branch_id
        LEFT JOIN branches nb ON cm.new_branch_id       = nb.branch_id
        LEFT JOIN users   u1 ON cm.logged_by            = u1.user_id
        LEFT JOIN users   u2 ON cm.approved_by          = u2.user_id
        ORDER BY cm.created_at DESC
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $movements[] = $row;
        $counts['Submitted']++;
        if (isset($counts[$row['approval_status']])) $counts[$row['approval_status']]++;
        if ((int)($row['is_applied']??0)===1) $counts['Applied']++;
    }
    $stmt->close();
}

function supCmTypeClass($t){return match($t){'Promotion'=>'bg-success','Transfer'=>'bg-info text-dark','Demotion'=>'bg-danger','Role Change'=>'bg-primary',default=>'bg-secondary'};}
function supCmStatusClass($s){return match($s){'Approved'=>'bg-success','Rejected'=>'bg-danger',default=>'bg-warning text-dark'};}
?>

<div class="page-hero fadeup">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
        <div>
            <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.55);">HR Supervisor &middot; Career</div>
            <h4 class="text-white fw-bold mb-0 mt-1"><i class="fas fa-route me-2" style="color:#BD9414;"></i>Career Movements</h4>
            <p class="text-white-50 small mb-0 mt-2">Submit and monitor employee promotion, transfer, and role-change requests for managerial review.</p>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-6 col-md-3"><div class="stat-card"><div class="stat-value"><?php echo $counts['Submitted']; ?></div><div class="stat-label">Total</div></div></div>
        <div class="col-6 col-md-3"><div class="stat-card"><div class="stat-value"><?php echo $counts['Pending']; ?></div><div class="stat-label">Pending</div></div></div>
        <div class="col-6 col-md-3"><div class="stat-card"><div class="stat-value"><?php echo $counts['Approved']; ?></div><div class="stat-label">Approved</div></div></div>
        <div class="col-6 col-md-3"><div class="stat-card"><div class="stat-value"><?php echo $counts['Applied']; ?></div><div class="stat-label">Applied</div></div></div>
    </div>
</div>

<?php if (!$movement_ready): ?>
    <div class="alert alert-danger">Career Movements could not be initialized.</div>
<?php endif; ?>

<div class="chart-card fadeup">
    <div class="cc-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <ul class="nav nav-tabs cc-header-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#supListTab" type="button" role="tab">
                    <i class="fas fa-list me-1"></i>All Movements
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#supCreateTab" type="button" role="tab" id="supCreateTabBtn">
                    <i class="fas fa-plus me-1"></i>Create Movement
                </button>
            </li>
        </ul>
        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="form-control form-control-sm" id="supMovSearch" placeholder="Search movements...">
        </div>
    </div>

    <div class="cc-body p-0">
        <div class="tab-content">

            <!-- LIST TAB -->
            <div class="tab-pane fade show active" id="supListTab" role="tabpanel">
                <div class="table-responsive">
                    <table class="table modern-table align-middle mb-0" id="supMovTable">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Type</th>
                                <th>Position Change</th>
                                <th>Branch Change</th>
                                <th>Effective</th>
                                <th>Source</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($movements)): ?>
                                <tr><td colspan="8" class="text-center py-5 text-muted"><i class="fas fa-route d-block mb-2" style="font-size:2rem;opacity:.2;"></i>No career movements yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($movements as $mv):
                                    $is_mine    = (int)($mv['logged_by']??0) === $current_user_id;
                                    $is_pending = $mv['approval_status'] === 'Pending';
                                    $is_hr_staff_req = ($mv['request_source']==='HR Portal' && ($mv['initiated_by_role']??'')==='HR Staff');
                                ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?php echo e($mv['employee_name']); ?></div>
                                        <small class="text-muted"><?php echo e(getEmployeeDisplayId($mv)); ?> &middot; <?php echo e($mv['current_job_title']); ?></small>
                                        <?php if (!empty($mv['department_name'])): ?>
                                            <div><span class="badge bg-light text-dark border" style="font-size:.68rem;"><?php echo e($mv['department_name']); ?></span></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge <?php echo supCmTypeClass($mv['movement_type']); ?>"><?php echo e($mv['movement_type']); ?></span></td>
                                    <td>
                                        <div class="small text-muted"><?php echo e($mv['previous_position'] ?: $mv['current_job_title'] ?: '—'); ?></div>
                                        <div class="fw-semibold"><i class="fas fa-arrow-right text-success me-1" style="font-size:.75rem;"></i><?php echo e($mv['new_position']); ?></div>
                                    </td>
                                    <td>
                                        <?php if (!empty($mv['new_branch_id'])): ?>
                                            <div class="small text-muted"><?php echo e($mv['previous_branch_name']?:'N/A'); ?></div>
                                            <div class="fw-semibold"><?php echo e($mv['new_branch_name']?:'N/A'); ?></div>
                                        <?php else: ?>
                                            <span class="text-muted small">No branch change</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="small"><?php echo formatDate($mv['effective_date']); ?></span></td>
                                    <td>
                                        <?php if ($is_hr_staff_req): ?>
                                            <span class="badge bg-warning text-dark"><i class="fas fa-user-shield me-1"></i>HR Staff Requisition</span>
                                            <div class="small text-muted mt-1">Leaderless Branch</div>
                                        <?php elseif ($mv['request_source']==='Employee Portal'): ?>
                                            <span class="badge bg-info text-dark"><i class="fas fa-user-tie me-1"></i>Branch Head Requisition</span>
                                            <?php if (!empty($mv['initiated_by_name'])): ?>
                                                <div class="small text-muted mt-1">by <?php echo e($mv['initiated_by_name']); ?></div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><i class="fas fa-building me-1"></i>HR Portal</span>
                                            <div class="small text-muted mt-1">by <?php echo e($mv['logged_by_name']?:'HRD'); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo supCmStatusClass($mv['approval_status']); ?>"><?php echo e($mv['approval_status']); ?></span>
                                        <?php if ($mv['approval_status']==='Approved' && (int)$mv['is_applied']===1): ?>
                                            <span class="badge bg-success ms-1">Applied</span>
                                        <?php elseif ($mv['approval_status']==='Approved'): ?>
                                            <span class="badge bg-secondary ms-1">Scheduled</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($is_pending && !$is_mine): ?>
                                            <form method="POST" class="d-inline">
                                                <?php echo csrfField(); ?>
                                                <input type="hidden" name="movement_id" value="<?php echo (int)$mv['movement_id']; ?>">
                                                <input type="hidden" name="movement_action" value="Approve">
                                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve this career movement?');"><i class="fas fa-check me-1"></i>Approve</button>
                                            </form>
                                            <button class="btn btn-sm btn-outline-danger ms-1"
                                                data-bs-toggle="modal" data-bs-target="#supRejectModal"
                                                data-mvid="<?php echo (int)$mv['movement_id']; ?>"
                                                data-empname="<?php echo e($mv['employee_name']); ?>">
                                                <i class="fas fa-times me-1"></i>Reject
                                            </button>
                                        <?php elseif ($is_pending && $is_mine): ?>
                                            <span class="badge bg-light text-dark border" title="You submitted this request — awaiting HR Manager approval."><i class="fas fa-clock me-1"></i>Awaiting Review</span>
                                        <?php else: ?>
                                            <span class="small text-muted"><?php echo e($mv['approved_by_name']?:'Processed'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php if (!empty($mv['reason'])||!empty($mv['manager_comments'])): ?>
                                <tr class="bg-light">
                                    <td colspan="8" class="small text-muted py-2">
                                        <?php if (!empty($mv['reason'])): ?><span class="fw-semibold">Reason:</span> <?php echo e($mv['reason']); ?><?php endif; ?>
                                        <?php if (!empty($mv['manager_comments'])): ?><span class="ms-3 fw-semibold">Decision Notes:</span> <?php echo e($mv['manager_comments']); ?><?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- CREATE TAB -->
            <div class="tab-pane fade" id="supCreateTab" role="tabpanel">
                <div class="p-4">
                    <form method="POST" id="supCreateForm">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="create_movement" value="1">
                        <div class="row g-3">

                            <!-- Department -->
                            <div class="col-lg-6">
                                <label class="form-label fw-semibold">Department <span class="text-danger">*</span></label>
                                <select class="form-select" name="department_id" id="supDeptSelect" required <?php echo !$movement_ready ? 'disabled' : ''; ?>>
                                    <option value="">-- Select Department --</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo (int)$dept['department_id']; ?>">
                                            <?php echo e($dept['department_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text text-muted"><i class="fas fa-info-circle me-1"></i>Select a department to load employees and valid positions.</div>
                            </div>

                            <!-- Employee -->
                            <div class="col-lg-6">
                                <label class="form-label fw-semibold">Employee <span class="text-danger">*</span></label>
                                <select class="form-select" name="employee_id" id="supEmpSelect" required disabled <?php echo !$movement_ready ? 'disabled' : ''; ?>>
                                    <option value="">-- Select Department First --</option>
                                </select>
                            </div>

                            <!-- Current Assignment Preview -->
                            <div class="col-12" id="supAssignmentPreview" style="display:none;">
                                <div class="p-3 rounded-3 border" style="background:linear-gradient(135deg,#f0f9ff 0%,#e0f2fe 100%); border-color:#bae6fd !important;">
                                    <div class="d-flex flex-wrap gap-4">
                                        <div>
                                            <div class="text-muted" style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;">Current Department</div>
                                            <div class="fw-bold" id="supPreviewDept">—</div>
                                        </div>
                                        <div>
                                            <div class="text-muted" style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;">Current Position</div>
                                            <div class="fw-bold" id="supPreviewPos">—</div>
                                        </div>
                                        <div>
                                            <div class="text-muted" style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;">Current Branch</div>
                                            <div class="fw-bold" id="supPreviewBranch">—</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Movement Type -->
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Movement Type <span class="text-danger">*</span></label>
                                <select class="form-select" name="movement_type" id="supMovTypeSelect" required <?php echo !$movement_ready ? 'disabled' : ''; ?>>
                                    <option value="">-- Select type --</option>
                                    <option value="Transfer">Transfer</option>
                                    <option value="Promotion">Promotion</option>
                                    <option value="Demotion">Demotion</option>
                                    <option value="Role Change">Role Change</option>
                                </select>
                            </div>

                            <!-- New Position (dynamic dropdown) -->
                            <div class="col-md-4" id="supPositionWrapper">
                                <label class="form-label fw-semibold" id="supPositionLabel">New Position <span class="text-danger" id="supPositionAsterisk">*</span></label>
                                <select class="form-select" name="new_position" id="supPositionSelect" required disabled <?php echo !$movement_ready ? 'disabled' : ''; ?>>
                                    <option value="">-- Select Department First --</option>
                                </select>
                                <div class="form-text text-muted" id="supPositionHint">Only valid positions for the selected department are shown.</div>
                            </div>

                            <!-- New Branch -->
                            <div class="col-md-4">
                                <label class="form-label fw-semibold" id="supBranchLabel">New Branch</label>
                                <select class="form-select" name="new_branch_id" id="supBranchSelect" <?php echo !$movement_ready ? 'disabled' : ''; ?>>
                                    <option value="">No branch change</option>
                                    <?php foreach ($branches as $br): ?>
                                        <option value="<?php echo (int)$br['branch_id']; ?>"><?php echo e($br['branch_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text text-muted" id="supBranchHint"></div>
                            </div>

                            <!-- Effective Date -->
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Effective Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="effective_date" required <?php echo !$movement_ready ? 'disabled' : ''; ?>>
                            </div>

                            <!-- Reason -->
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Reason <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="reason" rows="3" placeholder="Enter the justification for this career movement." required <?php echo !$movement_ready ? 'disabled' : ''; ?>></textarea>
                            </div>

                        </div>
                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" <?php echo !$movement_ready ? 'disabled' : ''; ?>>
                                <i class="fas fa-paper-plane me-1"></i>Submit for Review
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="supRejectModal" tabindex="-1" aria-labelledby="supRejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="supRejectModalLabel"><i class="fas fa-times-circle text-danger me-2"></i>Reject Career Movement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <?php echo csrfField(); ?>
                <input type="hidden" name="movement_id" id="supRejectMvId">
                <input type="hidden" name="movement_action" value="Reject">
                <div class="modal-body">
                    <p class="mb-2">Rejecting movement for: <strong id="supRejectEmpName"></strong></p>
                    <label class="form-label fw-semibold">Reason for Rejection <span class="text-muted small">(optional)</span></label>
                    <textarea class="form-control" name="manager_comments" rows="3" placeholder="Provide a brief reason..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-times me-1"></i>Confirm Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // ── Data from PHP ─────────────────────────────────────────────────────────
    const branchNames    = <?php echo json_encode($branch_names, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_QUOT); ?>;
    const deptEmployees  = <?php echo json_encode($dept_employees, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_QUOT); ?>;
    const deptPositions  = <?php echo json_encode($dept_positions, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_QUOT); ?>;
    const allEmployees   = <?php echo json_encode($all_employees, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_QUOT); ?>;

    // ── Elements ──────────────────────────────────────────────────────────────
    const deptSel    = document.getElementById('supDeptSelect');
    const empSel     = document.getElementById('supEmpSelect');
    const posSel     = document.getElementById('supPositionSelect');
    const preview    = document.getElementById('supAssignmentPreview');
    const prevDept   = document.getElementById('supPreviewDept');
    const prevPos    = document.getElementById('supPreviewPos');
    const prevBranch = document.getElementById('supPreviewBranch');

    // ── Department change → cascade employees & positions ────────────────────
    deptSel.addEventListener('change', function () {
        const did = this.value;

        // Reset employee dropdown
        empSel.innerHTML = '<option value="">-- Select Employee --</option>';
        empSel.disabled  = !did;

        // Reset position dropdown
        posSel.innerHTML = '<option value="">-- Select New Position --</option>';
        posSel.disabled  = !did;

        // Hide preview
        preview.style.display = 'none';

        if (!did) return;

        // Load employees
        const emps = deptEmployees[did] || [];
        if (emps.length === 0) {
            empSel.innerHTML = '<option value="">No employees found in this department</option>';
        } else {
            emps.forEach(function (emp) {
                const opt = document.createElement('option');
                opt.value              = emp.employee_id;
                const rank             = emp.rank_name ? ' [' + emp.rank_name + ']' : '';
                opt.textContent        = emp.last_name + ', ' + emp.first_name + rank + ' – ' + (emp.employee_code || emp.employee_id);
                opt.dataset.jobtitle   = emp.job_title || '';
                opt.dataset.branch     = emp.branch_id || '';
                opt.dataset.branchname = emp.branch_name || '';
                opt.dataset.deptname   = emp.department_name || '';
                empSel.appendChild(opt);
            });
        }

        // Load positions
        const positions = deptPositions[did] || [];
        if (positions.length === 0) {
            posSel.innerHTML = '<option value="">No positions defined for this department</option>';
        } else {
            positions.forEach(function (pos) {
                const opt = document.createElement('option');
                opt.value       = pos.title;
                opt.textContent = pos.title;
                posSel.appendChild(opt);
            });
            posSel.disabled = false;
        }
    });

    // ── Employee change → preview card ────────────────────────────────────────
    empSel.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        if (!opt.value) {
            preview.style.display = 'none';
            return;
        }
        prevDept.textContent   = opt.dataset.deptname   || '—';
        prevPos.textContent    = opt.dataset.jobtitle   || '—';
        prevBranch.textContent = opt.dataset.branchname || branchNames[opt.dataset.branch] || '—';
        preview.style.display  = 'block';
    });

    // ── Movement Type change → toggle Position required / Branch required ──────
    const movTypeSel     = document.getElementById('supMovTypeSelect');
    const posAsterisk    = document.getElementById('supPositionAsterisk');
    const posHint        = document.getElementById('supPositionHint');
    const branchLabel    = document.getElementById('supBranchLabel');
    const branchHint     = document.getElementById('supBranchHint');
    const branchSel      = document.getElementById('supBranchSelect');

    function applyMovTypeRules() {
        const isTransfer = movTypeSel.value === 'Transfer';

        // New Position: optional for Transfer, required for everything else
        if (isTransfer) {
            posSel.removeAttribute('required');
            posAsterisk.style.display = 'none';
            posHint.textContent = 'Optional for Transfer — leave blank to keep current position.';
        } else {
            // Only mark required if it isn't still disabled (i.e. dept was already picked)
            if (!posSel.disabled || posSel.options.length > 1) {
                posSel.setAttribute('required', 'required');
            }
            posAsterisk.style.display = '';
            posHint.textContent = 'Only valid positions for the selected department are shown.';
        }

        // New Branch: required for Transfer
        if (isTransfer) {
            branchSel.setAttribute('required', 'required');
            branchLabel.innerHTML = 'New Branch <span class="text-danger">*</span>';
            branchHint.textContent = 'Branch is required for a Transfer.';
        } else {
            branchSel.removeAttribute('required');
            branchLabel.innerHTML = 'New Branch';
            branchHint.textContent = '';
        }
    }

    if (movTypeSel) {
        movTypeSel.addEventListener('change', applyMovTypeRules);
    }

    // ── Search ────────────────────────────────────────────────────────────────
    const searchInput = document.getElementById('supMovSearch');
    const tableRows   = document.querySelectorAll('#supMovTable tbody tr');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const term = this.value.trim().toLowerCase();
            tableRows.forEach(r => { r.style.display = r.textContent.toLowerCase().includes(term) ? '' : 'none'; });
            if (typeof applyZebraStriping === 'function') applyZebraStriping('#supMovTable');
        });
    }

    // ── Reject Modal ──────────────────────────────────────────────────────────
    const rejectModal = document.getElementById('supRejectModal');
    if (rejectModal) {
        rejectModal.addEventListener('show.bs.modal', function (e) {
            const btn = e.relatedTarget;
            document.getElementById('supRejectMvId').value          = btn.dataset.mvid;
            document.getElementById('supRejectEmpName').textContent = btn.dataset.empname;
        });
    }

    // ── Pre-select employee from career-progression.php deep-link ─────────────
    // URL: ?new_movement=1&emp_id=123
    const urlParams   = new URLSearchParams(window.location.search);
    const preEmpId    = urlParams.get('emp_id');
    const preNewMov   = urlParams.get('new_movement');

    if (preNewMov === '1' && preEmpId && allEmployees[preEmpId]) {
        const emp = allEmployees[preEmpId];

        // 1. Switch to the Create tab
        const createTabBtn = document.getElementById('supCreateTabBtn');
        if (createTabBtn) {
            bootstrap.Tab.getOrCreateInstance(createTabBtn).show();
        }

        // 2. Select the correct department (triggers cascade)
        if (emp.department_id && deptSel) {
            deptSel.value = emp.department_id;
            deptSel.dispatchEvent(new Event('change'));

            // 3. After cascade populates employees, select this employee
            // Use a short delay so the options are rendered first
            setTimeout(function () {
                if (empSel) {
                    empSel.value = preEmpId;
                    empSel.dispatchEvent(new Event('change'));
                }
                // Clean up the URL so a page refresh doesn't re-trigger
                const cleanUrl = window.location.pathname;
                window.history.replaceState({}, '', cleanUrl);
            }, 50);
        }
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
