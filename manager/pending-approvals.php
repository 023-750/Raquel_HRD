<?php
$page_title = 'Pending Approvals';
require_once '../includes/session-check.php';
checkRole(['HR Manager']);
require_once '../includes/functions.php';

// Handle approval/rejection (MUST be before header.php to allow redirect)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $eval_id = (int) $_POST['evaluation_id'];
    $action = $_POST['action'];
    $comments = trim($_POST['manager_comments'] ?? '');

    if ($action === 'approve') {
        // 1. Update status to Approved
        $stmt = $conn->prepare("UPDATE evaluations SET status = 'Approved', approved_by = ?, manager_comments = ? WHERE evaluation_id = ?");
        $stmt->bind_param("isi", $_SESSION['user_id'], $comments, $eval_id);
        $stmt->execute();
        $stmt->close();

        // Recalculate evaluation scores to ensure manager overrides are reflected in total scores
        recalculateEvaluationScores($conn, $eval_id);

        // 2. Fetch evaluation details for notifications
        $eval_info_q = $conn->prepare("SELECT ev.*, CONCAT(e.first_name, ' ', e.last_name) as emp_name, e.branch_id as emp_branch_id, u_emp.user_id as emp_user_id, et.template_name
                                     FROM evaluations ev 
                                     LEFT JOIN employees e ON ev.employee_id = e.employee_id 
                                     LEFT JOIN users u_emp ON u_emp.employee_id = e.employee_id
                                     LEFT JOIN evaluation_templates et ON ev.template_id = et.template_id
                                     WHERE ev.evaluation_id = ?");
        $eval_info_q->bind_param("i", $eval_id);
        $eval_info_q->execute();
        $eval_info = $eval_info_q->get_result()->fetch_assoc();
        $eval_info_q->close();

        // 3. Send notifications
        $app_msg = "Your evaluation for {$eval_info['template_name']} has been approved by the HR Manager.";
        if (!empty($comments)) {
            $app_msg .= " Remarks: " . $comments;
        }

        if ($eval_info['submitted_by']) {
            createNotification($conn, $eval_info['submitted_by'], 'Evaluation Approved', "Evaluation for {$eval_info['emp_name']} has been approved.", BASE_URL . '/staff/evaluation-history.php');
        }
        if ($eval_info['endorsed_by']) {
            createNotification($conn, $eval_info['endorsed_by'], 'Evaluation Approved', "Evaluation for {$eval_info['emp_name']} has been approved by the HR Manager.", BASE_URL . '/supervisor/evaluation-history.php');
        }
        // 4. Notify the employee being evaluated
        if (!empty($eval_info['emp_user_id'])) {
            createNotification($conn, $eval_info['emp_user_id'], 'Evaluation Approved', $app_msg, BASE_URL . '/employee/self-rating.php?view=' . $eval_id);
        }
        logAudit($conn, $_SESSION['user_id'], 'UPDATE', 'Evaluation', $eval_id, "Approved evaluation for {$eval_info['emp_name']}");
        redirectWith(BASE_URL . '/manager/pending-approvals.php', 'success', 'Evaluation approved successfully.');

    } elseif ($action === 'reject') {
        if (empty($comments)) {
            redirectWith(BASE_URL . '/manager/pending-approvals.php', 'danger', 'Comments are required when rejecting an evaluation.');
        }
        $stmt = $conn->prepare("UPDATE evaluations SET status = 'Rejected', approved_by = ?, manager_comments = ? WHERE evaluation_id = ?");
        $stmt->bind_param("isi", $_SESSION['user_id'], $comments, $eval_id);
        $stmt->execute();
        $stmt->close();

        // Recalculate evaluation scores
        recalculateEvaluationScores($conn, $eval_id);

        // Fetch evaluation details for notifications
        $eval_info_q = $conn->prepare("SELECT ev.*, CONCAT(e.first_name, ' ', e.last_name) as emp_name, u_emp.user_id as emp_user_id, et.template_name
                                     FROM evaluations ev 
                                     LEFT JOIN employees e ON ev.employee_id = e.employee_id 
                                     LEFT JOIN users u_emp ON u_emp.employee_id = e.employee_id
                                     LEFT JOIN evaluation_templates et ON ev.template_id = et.template_id
                                     WHERE ev.evaluation_id = ?");
        $eval_info_q->bind_param("i", $eval_id);
        $eval_info_q->execute();
        $eval_info = $eval_info_q->get_result()->fetch_assoc();
        $eval_info_q->close();

        $rej_msg = "Your evaluation for {$eval_info['template_name']} has been rejected by the HR Manager. Reason: " . $comments;

        if ($eval_info['submitted_by']) {
            createNotification($conn, $eval_info['submitted_by'], 'Evaluation Rejected', "Your evaluation for {$eval_info['emp_name']} has been rejected.", BASE_URL . '/staff/evaluation-history.php');
        }
        if ($eval_info['endorsed_by']) {
            createNotification($conn, $eval_info['endorsed_by'], 'Evaluation Rejected', "Evaluation for {$eval_info['emp_name']} has been rejected by the HR Manager.", BASE_URL . '/supervisor/evaluation-history.php');
        }
        // Notify the employee being evaluated
        if (!empty($eval_info['emp_user_id'])) {
            createNotification($conn, $eval_info['emp_user_id'], 'Evaluation Rejected', $rej_msg, BASE_URL . '/employee/self-rating.php?view=' . $eval_id);
        }

        logAudit($conn, $_SESSION['user_id'], 'UPDATE', 'Evaluation', $eval_id, "Rejected evaluation for {$eval_info['emp_name']}");
        redirectWith(BASE_URL . '/manager/pending-approvals.php', 'warning', 'Evaluation rejected.');

    } elseif ($action === 'revision') {
        if (empty($comments)) {
            redirectWith(BASE_URL . '/manager/pending-approvals.php', 'danger', 'Comments are required when requesting revision.');
        }
        $stmt = $conn->prepare("UPDATE evaluations SET status = 'Returned', manager_comments = ? WHERE evaluation_id = ?");
        $stmt->bind_param("si", $comments, $eval_id);
        $stmt->execute();
        $stmt->close();

        // Recalculate evaluation scores
        recalculateEvaluationScores($conn, $eval_id);

        // Get details of the evaluation
        $eval_info_q = $conn->prepare("SELECT ev.*, CONCAT(e.first_name, ' ', e.last_name) as emp_name, u_emp.user_id as emp_user_id, et.template_name, u.role as submitter_role
                                     FROM evaluations ev 
                                     LEFT JOIN employees e ON ev.employee_id = e.employee_id 
                                     LEFT JOIN users u_emp ON u_emp.employee_id = e.employee_id
                                     LEFT JOIN evaluation_templates et ON ev.template_id = et.template_id
                                     LEFT JOIN users u ON ev.submitted_by = u.user_id 
                                     WHERE ev.evaluation_id = ?");
        $eval_info_q->bind_param("i", $eval_id);
        $eval_info_q->execute();
        $eval_info = $eval_info_q->get_result()->fetch_assoc();
        $eval_info_q->close();

        $rev_msg = "Your evaluation for {$eval_info['template_name']} has been returned for revision by the HR Manager. Remarks: " . $comments;

        if ($eval_info['submitted_by']) {
            if ($eval_info['submitter_role'] === 'Employee') {
                createNotification($conn, $eval_info['submitted_by'], 'Revision Requested', $rev_msg, BASE_URL . '/employee/self-rating.php?edit=' . $eval_id);
            } else {
                createNotification($conn, $eval_info['submitted_by'], 'Revision Requested', "Your evaluation for {$eval_info['emp_name']} needs revision.", BASE_URL . '/staff/evaluation-history.php');
            }
        }

        if ($eval_info['endorsed_by']) {
            createNotification($conn, $eval_info['endorsed_by'], 'Revision Requested', "The evaluation for {$eval_info['emp_name']} has been returned by the HR Manager for revision.", BASE_URL . '/supervisor/evaluation-history.php');
        }

        // Notify the employee being evaluated (if not already notified as the submitter)
        if (!empty($eval_info['emp_user_id']) && ($eval_info['emp_user_id'] != $eval_info['submitted_by'] || $eval_info['submitter_role'] !== 'Employee')) {
            createNotification($conn, $eval_info['emp_user_id'], 'Revision Requested', $rev_msg, BASE_URL . '/employee/self-rating.php?edit=' . $eval_id);
        }

        logAudit($conn, $_SESSION['user_id'], 'UPDATE', 'Evaluation', $eval_id, "Requested revision for evaluation of {$eval_info['emp_name']}");
        redirectWith(BASE_URL . '/manager/pending-approvals.php', 'info', 'Revision requested.');
    }
}

require_once '../includes/header.php';

// Fetch counts for summary
$finalized_count_q = $conn->prepare("SELECT COUNT(*) as cnt FROM evaluations WHERE approved_by = ? AND status IN ('Approved', 'Rejected')");
$finalized_count_q->bind_param("i", $_SESSION['user_id']);
$finalized_count_q->execute();
$finalized_count = $finalized_count_q->get_result()->fetch_assoc()['cnt'];
$finalized_count_q->close();

// Fetch pending evaluations
$pending = $conn->query("SELECT ev.*, CONCAT(e.first_name, ' ', e.last_name) as employee_name, e.job_title, e.profile_picture,
    u.full_name as submitted_by_name, et.template_name, et.kra_weight, et.behavior_weight
    FROM evaluations ev
    LEFT JOIN employees e ON ev.employee_id = e.employee_id
    LEFT JOIN users u ON ev.submitted_by = u.user_id
    LEFT JOIN evaluation_templates et ON ev.template_id = et.template_id
    WHERE ev.status = 'Pending Manager'
    ORDER BY ev.submitted_date DESC");

$pending_count = $pending->num_rows;

// Prepare results in array
$all_pending = [];
while ($row = $pending->fetch_assoc()) {
    $all_pending[] = $row;
}

$total_pending_all = $pending_count;
?>

<div class="page-hero fadeup">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
        <div>
            <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.55);">HR Manager · Approvals</div>
            <h4 class="text-white fw-bold mb-0 mt-1"><i class="fas fa-check-double me-2" style="color:#BD9414;"></i>Pending Approvals</h4>
        </div>
        <div style="color:rgba(255,255,255,.6);font-size:.8rem;">
            <i class="fas fa-sync-alt me-1"></i>Data as of <?php echo date('F d, Y'); ?>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?php echo $total_pending_all; ?></div>
                        <div class="stat-label">Pending Actions</div>
                    </div>
                    <i class="fas fa-hourglass-half stat-icon text-white-50"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?php echo $pending_count; ?></div>
                        <div class="stat-label">Evaluations</div>
                    </div>
                    <i class="fas fa-file-signature stat-icon" style="color:#BD9414;"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-value"><?php echo $finalized_count; ?></div>
                        <div class="stat-label">Finalized</div>
                    </div>
                    <i class="fas fa-check-circle stat-icon" style="color:#28a745;"></i>
                </div>
            </div>
        </div>
    </div>
</div>


<style>
    .approval-center-tabs {
        background: #fff;
        border-radius: 12px 12px 0 0;
        border: 1px solid #f0f0f0;
        border-bottom: none;
        padding: 5px 15px 0;
    }
    .approval-center-tabs .nav-link {
        border: none;
        padding: 15px 25px;
        font-weight: 600;
        color: var(--text-muted);
        position: relative;
        transition: all 0.3s;
    }
    .approval-center-tabs .nav-link.active {
        color: var(--primary-blue) !important;
        background: transparent !important;
    }
    .approval-center-tabs .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 20px;
        right: 20px;
        height: 4px;
        background: var(--primary-blue);
        border-radius: 10px;
    }
    .approval-card-list {
        background: #fff;
        border-radius: 0 0 12px 12px;
        border: 1px solid #f0f0f0;
        min-height: 400px;
    }
    .modern-table thead th {
        background: rgba(41, 67, 6, 0.03);
        color: var(--primary-blue);
        font-weight: 700;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        border: none;
        padding: 15px 20px;
    }
    .modern-table tbody td {
        padding: 18px 20px;
        border-bottom: 1px solid #f8f9fa;
    }
    .emp-avatar {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: #fff;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.9rem;
        overflow: hidden;
        border: 1px solid #eef2e8;
        box-shadow: 0 2px 8px rgba(12, 32, 8, 0.08);
    }
    .emp-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .badge-audit {
        background: rgba(255, 193, 7, 0.15);
        color: #d39e00;
        border: 1px solid rgba(255, 193, 7, 0.4);
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 0.65rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        backdrop-filter: blur(4px);
        margin-left: 5px;
        vertical-align: middle;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .badge-audit:hover {
        background: rgba(255, 193, 7, 0.25);
        transform: translateY(-1px);
    }
    .score-input {
        transition: all 0.3s ease;
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid #ced4da;
    }
    .score-input:focus {
        background: #fff;
        border-color: #BD9414;
        box-shadow: 0 0 0 0.2rem rgba(189, 148, 20, 0.25);
    }
    
    /* TOTAL ROWS DESIGN UPGRADE FOR REVIEW CONSOLE */
    .total-row td {
        background: #fef3c7 !important;
        font-weight: 800 !important;
        border-top: 2px solid #fbbf24 !important;
        border-bottom: 3px double #fbbf24 !important;
        color: #92400e !important;
        font-size: 0.88rem !important;
    }
</style>

<div class="row mb-5">
    <div class="col-12">
        <div class="approval-center-tabs d-flex justify-content-between align-items-center">
            <ul class="nav nav-tabs border-0" id="approvalTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="evaluations-tab" data-bs-toggle="tab" data-bs-target="#evaluations-pane" type="button" role="tab">
                        <i class="fas fa-file-signature me-2"></i>Evaluations
                        <span class="badge rounded-pill bg-primary ms-1"><?php echo $pending_count; ?></span>
                    </button>
                </li>
            </ul>
            <div class="search-box me-3">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="form-control form-control-sm border-0 bg-light" id="unifiedSearch" placeholder="Search approvals...">
            </div>
        </div>

        <div class="tab-content approval-card-list shadow-sm" id="approvalTabsContent">
            <!-- Evaluations Tab -->
            <div class="tab-pane fade show active" id="evaluations-pane" role="tabpanel">
                <div class="table-responsive">
                    <table class="table modern-table align-middle mb-0" id="evalTable">
                        <thead>
                            <tr>
                                <th>Employee & Template</th>
                                <th>Submitted By</th>
                                <th>Date</th>
                                <th>Performance Score</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($all_pending)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <i class="fas fa-check-circle fa-3x text-light mb-3"></i>
                                        <p class="text-muted">No pending evaluations for review.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($all_pending as $row): 
                                    $initials = strtoupper(substr($row['employee_name'], 0, 1) . substr(explode(' ', $row['employee_name'])[1] ?? '', 0, 1));
                                    $avatar_url = getEmployeeAvatar($row['profile_picture'] ?? '');
                                ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="emp-avatar">
                                                    <img src="<?php echo e($avatar_url); ?>?v=<?php echo time(); ?>" alt="<?php echo e($row['employee_name']); ?> profile picture">
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark"><?php echo e($row['employee_name']); ?></div>
                                                    <small class="text-muted"><?php echo e($row['template_name']); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="small text-muted"><?php echo e($row['submitted_by_name']); ?></span></td>
                                        <td><span class="small text-muted"><?php echo formatDate($row['submitted_date']); ?></span></td>
                                        <td>
                                            <?php
                                            $score = (float)$row['total_score'];
                                            $score_width = max(0, min(100, ($score / 4) * 100));
                                            $badge_class = getPerformanceBadgeClass($row['performance_level']);
                                            ?>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="fw-bold" style="min-width: 55px; white-space: nowrap;"><?php echo number_format($score, 2); ?> / 4</div>
                                                <div class="progress flex-grow-1" style="height: 6px; max-width: 100px;">
                                                    <div class="progress-bar <?php echo $badge_class; ?>" 
                                                         style="width: <?php echo $score_width; ?>%"></div>
                                                </div>
                                                <span class="badge <?php echo $badge_class; ?> rounded-pill"><?php echo e($row['performance_level']); ?></span>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-primary px-3 rounded-pill shadow-sm" 
                                                    data-bs-toggle="modal" data-bs-target="#reviewModal<?php echo $row['evaluation_id']; ?>">
                                                Review Details
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
    </div>
</div>

<?php 
// Render Modals at the end of the file
foreach ($all_pending as $row): 
    $initials = strtoupper(substr($row['employee_name'], 0, 1) . substr(explode(' ', $row['employee_name'])[1] ?? '', 0, 1));
    $modal_avatar_url = getEmployeeAvatar($row['profile_picture'] ?? '');
?>
    <div class="modal fade modal-premium" id="reviewModal<?php echo $row['evaluation_id']; ?>" tabindex="-1" aria-hidden="true" data-kra-weight="<?php echo (float)($row['kra_weight'] ?? 80); ?>" data-behavior-weight="<?php echo (float)($row['behavior_weight'] ?? 20); ?>">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-1">Performance Review</h5>
                        <p class="mb-0 opacity-75 small">Reviewing evaluation for <?php echo e($row['employee_name']); ?></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 pt-0">
                    <!-- Status Stepper -->
                    <div class="status-stepper d-flex justify-content-between mb-4 py-3 border-bottom overflow-hidden">
                        <?php
                        $steps = [
                            ['l' => 'Drafted', 'a' => true, 'i' => 'fa-pencil-alt'],
                            ['l' => 'Supervisor', 'a' => true, 'i' => 'fa-user-tie'],
                            ['l' => 'Review', 'a' => true, 'i' => 'fa-user-shield', 'c' => true],
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

                    <?php $can_edit_scores = ($row['status'] ?? '') === 'Pending Manager'; ?>
                    <?php if ($can_edit_scores): ?>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-4 d-print-none eval-rating-toolbar">
                        <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-bold btn-edit-ratings" onclick="toggleEditRatings(<?php echo (int) $row['evaluation_id']; ?>)">
                            <i class="fas fa-edit me-1"></i>Edit Ratings
                        </button>
                        <button type="button" class="btn btn-sm btn-success rounded-pill px-3 fw-bold btn-save-ratings d-none" onclick="saveRatings(<?php echo (int) $row['evaluation_id']; ?>)">
                            <i class="fas fa-save me-1"></i>Save Changes
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-bold btn-cancel-ratings d-none" onclick="toggleEditRatings(<?php echo (int) $row['evaluation_id']; ?>, true)">
                            <i class="fas fa-times me-1"></i>Cancel
                        </button>
                    </div>
                    <?php endif; ?>

                    <div class="eval-summary-header">
                        <div class="d-flex align-items-center gap-3">
                            <div class="emp-avatar" style="width: 55px; height: 55px; font-size: 1.2rem;">
                                <img src="<?php echo e($modal_avatar_url); ?>?v=<?php echo time(); ?>" alt="<?php echo e($row['employee_name']); ?> profile picture">
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold"><?php echo e($row['employee_name']); ?></h4>
                                <div class="text-muted"><?php echo e($row['job_title']); ?> &bull; <?php echo e($row['template_name']); ?></div>
                            </div>
                        </div>
                        <div class="score-circle">
                            <div class="val total-score-val"><?php echo number_format((float)$row['total_score'], 2); ?>/4</div>
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
                                $kra_q = $conn->query("SELECT es.*, ec.criterion_name, ec.description, ec.weight FROM evaluation_scores es JOIN evaluation_criteria ec ON es.criterion_id = ec.criterion_id WHERE es.evaluation_id = {$row['evaluation_id']} AND ec.section = 'KRA' ORDER BY ec.sort_order");
                                $kra_num = 1;
                                while ($k = $kra_q->fetch_assoc()): ?>
                                    <tr class="kra-row" data-weight="<?php echo $k['weight']; ?>">
                                        <td class="ps-3">
                                            <div class="fw-bold">KRA <?php echo $kra_num++; ?>: <?php echo e($k['criterion_name']); ?></div>
                                            <?php if($k['description']): ?><div class="text-muted x-small"><?php echo e($k['description']); ?></div><?php endif; ?>
                                        </td>
                                        <td class="text-center"><?php echo $k['weight']; ?>%</td>
                                        <td class="text-center">
                                            <?php
                                            $effective_score = $k['score_value'];
                                            $supervisor_override_score = $k['supervisor_override_score'] ?? null;
                                            $manager_override_score = $k['manager_override_score'] ?? null;
                                            $badge_html = '';
                                            if ($supervisor_override_score !== null) {
                                                $effective_score = $supervisor_override_score;
                                                $sup_name_q = $conn->query("SELECT full_name FROM users WHERE user_id = " . (int)($k['supervisor_override_by'] ?? 0))->fetch_assoc();
                                                $sup_name = $sup_name_q['full_name'] ?? 'Supervisor';
                                                $formatted_date = formatDate($k['supervisor_override_at'] ?? '', 'M d, Y h:i A');
                                                $badge_html = '<span class="badge-audit ms-2" data-bs-toggle="tooltip" data-bs-html="true" title="<strong>Supervisor Override</strong><br>Edited by: ' . e($sup_name) . '<br>On: ' . $formatted_date . '<br>Original: ' . $k['score_value'] . '"><i class="fas fa-user-edit me-1"></i>Sup Override</span>';
                                            }
                                            if ($manager_override_score !== null) {
                                                $effective_score = $manager_override_score;
                                                $mgr_name_q = $conn->query("SELECT full_name FROM users WHERE user_id = " . (int)($k['manager_override_by'] ?? 0))->fetch_assoc();
                                                $mgr_name = $mgr_name_q['full_name'] ?? 'Manager';
                                                $formatted_date = formatDate($k['manager_override_at'] ?? '', 'M d, Y h:i A');
                                                $badge_html = '<span class="badge-audit ms-2" data-bs-toggle="tooltip" data-bs-html="true" title="<strong>Manager Override</strong><br>Edited by: ' . e($mgr_name) . '<br>On: ' . $formatted_date . '<br>Original: ' . $k['score_value'] . '"><i class="fas fa-user-edit me-1"></i>Mgr Override</span>';
                                            }
                                            ?>
                                            <?php if ($can_edit_scores): ?>
                                            <span class="score-display fw-bold"><?php echo number_format($effective_score, 2); ?></span>
                                            <input type="number" step="0.01" min="1.00" max="4.00" class="form-control form-control-sm score-input d-none text-center mx-auto" data-score-id="<?php echo $k['score_id']; ?>" data-original-val="<?php echo number_format($effective_score, 2); ?>" value="<?php echo number_format($effective_score, 2); ?>" style="width:75px;margin:0 auto;">
                                            <?php else: ?>
                                            <span class="fw-bold"><?php echo number_format($effective_score, 2); ?></span>
                                            <?php endif; ?>
                                            <?php echo $badge_html; ?>
                                        </td>
                                        <td class="weighted-score text-center text-primary fw-bold"><?php echo number_format($k['weighted_score'], 2); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                                <tr class="total-row bg-light fw-bold border-top">
                                    <td class="ps-3">KRA Sub-total</td>
                                    <td class="text-center">100%</td>
                                    <td></td>
                                    <td class="text-center text-primary kra-subtotal-val"><?php echo number_format($row['kra_subtotal'], 2); ?></td>
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
                                $beh_q = $conn->query("SELECT es.*, ec.criterion_name, ec.kpi_description FROM evaluation_scores es JOIN evaluation_criteria ec ON es.criterion_id = ec.criterion_id WHERE es.evaluation_id = {$row['evaluation_id']} AND ec.section = 'Behavior' ORDER BY ec.sort_order");
                                while ($b = $beh_q->fetch_assoc()): ?>
                                    <tr class="beh-row">
                                        <td class="ps-3">
                                            <div class="fw-bold"><?php echo e($b['criterion_name']); ?></div>
                                            <div class="text-muted x-small"><?php echo e($b['kpi_description']); ?></div>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $effective_score = $b['score_value'];
                                            $supervisor_override_score = $b['supervisor_override_score'] ?? null;
                                            $manager_override_score = $b['manager_override_score'] ?? null;
                                            $badge_html = '';
                                            if ($supervisor_override_score !== null) {
                                                $effective_score = $supervisor_override_score;
                                                $sup_name_q = $conn->query("SELECT full_name FROM users WHERE user_id = " . (int)($b['supervisor_override_by'] ?? 0))->fetch_assoc();
                                                $sup_name = $sup_name_q['full_name'] ?? 'Supervisor';
                                                $formatted_date = formatDate($b['supervisor_override_at'] ?? '', 'M d, Y h:i A');
                                                $badge_html = '<span class="badge-audit ms-2" data-bs-toggle="tooltip" data-bs-html="true" title="<strong>Supervisor Override</strong><br>Edited by: ' . e($sup_name) . '<br>On: ' . $formatted_date . '<br>Original: ' . $b['score_value'] . '"><i class="fas fa-user-edit me-1"></i>Sup Override</span>';
                                            }
                                            if ($manager_override_score !== null) {
                                                $effective_score = $manager_override_score;
                                                $mgr_name_q = $conn->query("SELECT full_name FROM users WHERE user_id = " . (int)($b['manager_override_by'] ?? 0))->fetch_assoc();
                                                $mgr_name = $mgr_name_q['full_name'] ?? 'Manager';
                                                $formatted_date = formatDate($b['manager_override_at'] ?? '', 'M d, Y h:i A');
                                                $badge_html = '<span class="badge-audit ms-2" data-bs-toggle="tooltip" data-bs-html="true" title="<strong>Manager Override</strong><br>Edited by: ' . e($mgr_name) . '<br>On: ' . $formatted_date . '<br>Original: ' . $b['score_value'] . '"><i class="fas fa-user-edit me-1"></i>Mgr Override</span>';
                                            }
                                            ?>
                                            <?php if ($can_edit_scores): ?>
                                            <span class="score-display text-primary fw-bold"><?php echo number_format($effective_score, 2); ?></span>
                                            <input type="number" step="0.01" min="1.00" max="4.00" class="form-control form-control-sm score-input d-none text-center mx-auto" data-score-id="<?php echo $b['score_id']; ?>" data-original-val="<?php echo number_format($effective_score, 2); ?>" value="<?php echo number_format($effective_score, 2); ?>" style="width:75px;margin:0 auto;">
                                            <?php else: ?>
                                            <span class="text-primary fw-bold"><?php echo number_format($effective_score, 2); ?></span>
                                            <?php endif; ?>
                                            <?php echo $badge_html; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                                <tr class="total-row bg-light fw-bold border-top">
                                    <td class="ps-3">Behavior Average</td>
                                    <td class="text-center text-primary beh-avg-val"><?php echo number_format($row['behavior_average'], 2); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Employee Comments -->
                    <?php if (!empty($row['staff_comments'])): ?>
                    <div class="section-premium-label mb-3 mt-5">
                        <i class="fas fa-comment-dots"></i> Employee Comments / Notes
                    </div>
                    <div class="p-3 bg-light rounded-3 mb-4 border-start border-4 border-primary">
                        <p class="mb-0 text-dark small" style="white-space: pre-wrap;"><?php echo e($row['staff_comments']); ?></p>
                    </div>
                    <?php endif; ?>

                    <!-- Career Growth -->
                    <?php $cg_suited = !empty($row['career_growth_suited']) ? 1 : (!empty($row['desired_position']) ? 1 : 0); ?>
                    <div class="section-premium-label mb-3 mt-5">
                        <i class="fas fa-chart-line"></i> III. Career Growth
                        <button type="button" class="btn btn-sm btn-outline-primary ms-2" id="editCareerBtn<?php echo $row['evaluation_id']; ?>" onclick="toggleCareerEdit(<?php echo $row['evaluation_id']; ?>)"><i class="fas fa-edit me-1"></i>Edit</button>
                    </div>
                    <div class="p-3 bg-light rounded-3 mb-4 border-start border-4 border-info">
                        <!-- View mode -->
                        <div id="careerView<?php echo $row['evaluation_id']; ?>">
                            <div class="mb-2 fw-semibold" style="font-size:0.9rem;">
                                Is the employee better suited for another job within the company?
                                <span class="badge ms-2 <?php echo $cg_suited ? 'bg-success' : 'bg-secondary'; ?>" id="careerSuitedBadge<?php echo $row['evaluation_id']; ?>">
                                    <?php echo $cg_suited ? '&#9745; Yes' : '&#9744; No'; ?>
                                </span>
                            </div>
                            <div class="cg-details-container mt-1 <?php echo !$cg_suited ? 'd-none' : ''; ?>" id="careerPositionContainer<?php echo $row['evaluation_id']; ?>">
                                <div class="small text-muted">
                                    <i class="fas fa-briefcase me-1 text-info"></i>
                                    <strong>Job Function / Department:</strong>
                                    <span class="text-dark fw-semibold ms-1" id="careerPositionText<?php echo $row['evaluation_id']; ?>"><?php echo e($row['desired_position'] ?? 'N/A'); ?></span>
                                </div>
                            </div>
                            <div class="cg-details-container mt-1 <?php echo (!$cg_suited || empty($row['target_date'])) ? 'd-none' : ''; ?>" id="careerDateContainer<?php echo $row['evaluation_id']; ?>">
                                <div class="small text-muted">
                                    <i class="fas fa-calendar-alt me-1 text-info"></i>
                                    <strong>Target Date:</strong>
                                    <span class="text-dark fw-semibold ms-1" id="careerDateText<?php echo $row['evaluation_id']; ?>"><?php echo e($row['target_date'] ?? 'N/A'); ?></span>
                                </div>
                            </div>
                            <div class="cg-details-container mt-1 <?php echo (!$cg_suited || empty($row['career_growth_details'])) ? 'd-none' : ''; ?>" id="careerDetailsContainer<?php echo $row['evaluation_id']; ?>">
                                <div class="small text-muted">
                                    <i class="fas fa-info-circle me-1 text-info"></i>
                                    <strong>Details:</strong>
                                    <span class="text-dark fw-semibold ms-1" id="careerDetailsText<?php echo $row['evaluation_id']; ?>"><?php echo e($row['career_growth_details'] ?? 'N/A'); ?></span>
                                </div>
                            </div>
                        </div>
                        <!-- Edit mode -->
                        <div id="careerEdit<?php echo $row['evaluation_id']; ?>" class="d-none">
                            <div class="mb-2">
                                <label class="form-label fw-semibold small">Is the employee better suited for another job within the company?</label>
                                <select class="form-select form-select-sm" id="careerSuitedInput<?php echo $row['evaluation_id']; ?>" onchange="toggleSuitedInputFields(<?php echo $row['evaluation_id']; ?>)">
                                    <option value="1" <?php echo $cg_suited ? 'selected' : ''; ?>>Yes</option>
                                    <option value="0" <?php echo !$cg_suited ? 'selected' : ''; ?>>No</option>
                                </select>
                            </div>
                            <div id="suitedInputsContainer<?php echo $row['evaluation_id']; ?>" class="<?php echo !$cg_suited ? 'd-none' : ''; ?>">
                                <div class="mb-2">
                                    <label class="form-label fw-semibold small">Desired Position / Department</label>
                                    <input type="text" class="form-control form-control-sm" id="careerPosition<?php echo $row['evaluation_id']; ?>" value="<?php echo e($row['desired_position'] ?? ''); ?>" placeholder="Enter desired position...">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label fw-semibold small">Target Date</label>
                                    <input type="date" class="form-control form-control-sm" id="careerDate<?php echo $row['evaluation_id']; ?>" value="<?php echo e($row['target_date'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small">Career Growth Details</label>
                                    <textarea class="form-control form-control-sm" id="careerDetails<?php echo $row['evaluation_id']; ?>" rows="3" placeholder="Enter career growth details..."><?php echo e($row['career_growth_details'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-success btn-sm" onclick="saveCareerGrowth(<?php echo $row['evaluation_id']; ?>)"><i class="fas fa-save me-1"></i>Save Career Growth</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleCareerEdit(<?php echo $row['evaluation_id']; ?>)">Cancel</button>
                            </div>
                        </div>
                    </div>

                    <!-- Developmental Plan -->
                    <div class="section-premium-label mb-3 mt-5">
                        <i class="fas fa-seedling"></i> IV. Developmental Plan
                        <button type="button" class="btn btn-sm btn-outline-primary ms-2" id="editDevBtn<?php echo $row['evaluation_id']; ?>" onclick="toggleDevEdit(<?php echo $row['evaluation_id']; ?>)"><i class="fas fa-edit me-1"></i>Edit</button>
                    </div>
                    <div class="p-3 bg-light rounded-3 mb-4 border-start border-4 border-success">
                        <!-- View mode -->
                        <div id="devView<?php echo $row['evaluation_id']; ?>">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle bg-white rounded border mb-0">
                                    <thead class="small text-muted bg-light">
                                        <tr>
                                            <th class="ps-3">Area of Improvement</th>
                                            <th>Support Needed</th>
                                            <th>Time Frame</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small" id="devViewTableBody<?php echo $row['evaluation_id']; ?>">
                                        <?php
                                        $dev_q = $conn->query("SELECT * FROM evaluation_dev_plans WHERE evaluation_id = {$row['evaluation_id']} ORDER BY sort_order");
                                        $has_dev = $dev_q->num_rows > 0;
                                        if ($has_dev):
                                            while ($dp = $dev_q->fetch_assoc()): ?>
                                            <tr>
                                                <td class="ps-3"><?php echo e($dp['improvement_area']); ?></td>
                                                <td><?php echo e($dp['support_needed']); ?></td>
                                                <td><?php echo e($dp['time_frame']); ?></td>
                                            </tr>
                                        <?php endwhile; else: ?>
                                            <tr class="no-dev-row"><td colspan="3" class="text-center text-muted small py-3">No developmental plan recorded.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Edit mode -->
                        <div id="devEdit<?php echo $row['evaluation_id']; ?>" class="d-none">
                            <div class="table-responsive mb-3">
                                <table class="table table-sm table-hover align-middle bg-white rounded border mb-0" id="devEditTable<?php echo $row['evaluation_id']; ?>">
                                    <thead class="small text-muted bg-light">
                                        <tr>
                                            <th class="ps-3">Area of Improvement</th>
                                            <th>Support Needed</th>
                                            <th>Time Frame</th>
                                            <th style="width: 50px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="small">
                                        <?php
                                        if ($has_dev) {
                                            $dev_q->data_seek(0);
                                            while ($dp = $dev_q->fetch_assoc()): ?>
                                                <tr class="dev-edit-row">
                                                    <td class="ps-2">
                                                        <input type="text" class="form-control form-control-sm dev-improvement" value="<?php echo e($dp['improvement_area']); ?>" placeholder="e.g. Technical writing skill">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control form-control-sm dev-support" value="<?php echo e($dp['support_needed']); ?>" placeholder="e.g. Online course or mentoring">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control form-control-sm dev-timeframe" value="<?php echo e($dp['time_frame']); ?>" placeholder="e.g. Q3 2026">
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="this.closest('tr').remove()"><i class="fas fa-trash-alt"></i></button>
                                                    </td>
                                                </tr>
                                            <?php endwhile;
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="addDevPlanRow(<?php echo $row['evaluation_id']; ?>)">
                                    <i class="fas fa-plus me-1"></i>Add Row
                                </button>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-success btn-sm" onclick="saveDevPlan(<?php echo $row['evaluation_id']; ?>)">
                                        <i class="fas fa-save me-1"></i>Save Developmental Plan
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleDevEdit(<?php echo $row['evaluation_id']; ?>, true)">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Remarks -->
                    <div class="section-premium-label mb-3 mt-5">
                        <i class="fas fa-comments"></i> V. Remarks & Decisions
                    </div>
                    <?php if(!empty($row['supervisor_comments'])): ?>
                        <div class="mb-3">
                            <label class="x-small fw-bold text-muted text-uppercase mb-1">Department Supervisor Remarks</label>
                            <div class="p-3 bg-light rounded-3 border italic small"><?php echo nl2br(e($row['supervisor_comments'])); ?></div>
                        </div>
                    <?php endif; ?>
                    <?php if(!empty($row['dept_manager_comments'])): ?>
                        <div class="mb-3">
                            <label class="x-small fw-bold text-muted text-uppercase mb-1">Department Manager Remarks</label>
                            <div class="p-3 bg-light rounded-3 border italic small"><?php echo nl2br(e($row['dept_manager_comments'])); ?></div>
                        </div>
                    <?php endif; ?>
                    <?php if(!empty($row['evaluator_comments'])): ?>
                        <div class="mb-3">
                            <label class="x-small fw-bold text-muted text-uppercase mb-1">HR Supervisor Remarks</label>
                            <div class="p-3 bg-light rounded-3 border italic small"><?php echo nl2br(e($row['evaluator_comments'])); ?></div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" onsubmit="return handleApprovalFormSubmit(event, <?php echo $row['evaluation_id']; ?>)">
                        <input type="hidden" name="evaluation_id" value="<?php echo $row['evaluation_id']; ?>">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Manager's Final Comments</label>
                            <textarea class="form-control bg-light" name="manager_comments" rows="3" placeholder="Enter findings, recommendations, or reasons for rejection..."></textarea>
                        </div>
                        <div class="fixed-action-bar d-flex gap-2 justify-content-end">
                            <button type="submit" name="action" value="revision" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm">
                                <i class="fas fa-undo me-2"></i>Provision
                            </button>
                            <button type="submit" name="action" value="reject" class="btn btn-outline-danger rounded-pill px-4 fw-bold">
                                <i class="fas fa-times me-2"></i>Reject
                            </button>
                            <button type="submit" name="action" value="approve" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">
                                <i class="fas fa-check-double me-2"></i>Approve Evaluation
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Search functionality for unified center
    document.getElementById('unifiedSearch')?.addEventListener('input', function() {
        const filter = this.value.toLowerCase();
        const activePane = document.querySelector('.tab-pane.active');
        if (!activePane) return;
        const rows = activePane.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });

        const activeTable = activePane.querySelector('.table');
        if (activeTable && activeTable.id) {
            applyZebraStriping('#' + activeTable.id);
        }
    });

    // Handle deep linking for review
    const urlParams = new URLSearchParams(window.location.search);
    const reviewId = urlParams.get('review');
    if (reviewId) {
        const modal = new bootstrap.Modal(document.getElementById('reviewModal' + reviewId));
        modal.show();
    }
});

function toggleEditRatings(evalId, cancel = false) {
    const modal = document.querySelector(`#reviewModal${evalId}`);
    if (!modal) return;

    const displays = modal.querySelectorAll('.score-display');
    const inputs = modal.querySelectorAll('.score-input');
    const badgeAudits = modal.querySelectorAll('.badge-audit');
    const editBtn = modal.querySelector('.btn-edit-ratings');
    const saveBtn = modal.querySelector('.btn-save-ratings');
    const cancelBtn = modal.querySelector('.btn-cancel-ratings');

    if (!inputs.length) {
        alert('No rating fields are available to edit for this evaluation.');
        return;
    }

    if (cancel) {
        inputs.forEach(input => {
            input.value = input.getAttribute('data-original-val');
            input.classList.remove('is-invalid');
        });
    }

    const isEditing = inputs[0].classList.contains('d-none');

    if (isEditing) {
        displays.forEach(d => d.classList.add('d-none'));
        badgeAudits.forEach(b => b.classList.add('d-none'));
        inputs.forEach(i => i.classList.remove('d-none'));

        if (editBtn) editBtn.classList.add('d-none');
        if (saveBtn) saveBtn.classList.remove('d-none');
        if (cancelBtn) cancelBtn.classList.remove('d-none');
    } else {
        displays.forEach(d => d.classList.remove('d-none'));
        badgeAudits.forEach(b => b.classList.remove('d-none'));
        inputs.forEach(i => i.classList.add('d-none'));

        if (editBtn) editBtn.classList.remove('d-none');
        if (saveBtn) saveBtn.classList.add('d-none');
        if (cancelBtn) cancelBtn.classList.add('d-none');
    }
}

function saveRatings(evalId) {
    const modal = document.querySelector(`#reviewModal${evalId}`);
    if (!modal) return;

    const inputs = modal.querySelectorAll('.score-input');
    const ratings = {};
    let hasError = false;

    inputs.forEach(input => {
        const val = parseFloat(input.value);
        const scoreId = input.getAttribute('data-score-id');
        if (!scoreId || isNaN(val) || val < 1.00 || val > 4.00) {
            hasError = true;
            input.classList.add('is-invalid');
        } else {
            input.classList.remove('is-invalid');
            ratings[scoreId] = val;
        }
    });

    if (hasError || Object.keys(ratings).length === 0) {
        alert('Please enter valid ratings between 1.00 and 4.00.');
        return;
    }

    const saveBtn = modal.querySelector('.btn-save-ratings');
    if (!saveBtn) return;

    const originalBtnText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';

    const formData = new FormData();
    formData.append('evaluation_id', evalId);
    for (const [key, value] of Object.entries(ratings)) {
        formData.append(`ratings[${key}]`, value);
    }

    fetch('ajax/save-rating.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || 'An error occurred while saving ratings.');
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalBtnText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An unexpected error occurred.');
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalBtnText;
    });
}

function toggleDevEdit(evalId, cancel = false) {
    const viewDiv = document.getElementById(`devView${evalId}`);
    const editDiv = document.getElementById(`devEdit${evalId}`);
    if (!viewDiv || !editDiv) return;

    if (cancel) {
        const viewRows = viewDiv.querySelectorAll('tbody tr');
        const editTbody = editDiv.querySelector('tbody');
        editTbody.innerHTML = '';
        
        viewRows.forEach(row => {
            if (row.classList.contains('no-dev-row')) return;
            const imp = row.cells[0].textContent;
            const sup = row.cells[1].textContent;
            const time = row.cells[2].textContent;
            
            const newRow = document.createElement('tr');
            newRow.className = 'dev-edit-row';
            newRow.innerHTML = `
                <td class="ps-2">
                    <input type="text" class="form-control form-control-sm dev-improvement" value="${escapeHtml(imp)}" placeholder="e.g. Technical writing skill">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm dev-support" value="${escapeHtml(sup)}" placeholder="e.g. Online course or mentoring">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm dev-timeframe" value="${escapeHtml(time)}" placeholder="e.g. Q3 2026">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="this.closest('tr').remove()"><i class="fas fa-trash-alt"></i></button>
                </td>
            `;
            editTbody.appendChild(newRow);
        });
    }

    viewDiv.classList.toggle('d-none');
    editDiv.classList.toggle('d-none');
}

function escapeHtml(text) {
    if (!text) return '';
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function addDevPlanRow(evalId) {
    const editTbody = document.querySelector(`#devEditTable${evalId} tbody`);
    if (!editTbody) return;
    
    const newRow = document.createElement('tr');
    newRow.className = 'dev-edit-row';
    newRow.innerHTML = `
        <td class="ps-2">
            <input type="text" class="form-control form-control-sm dev-improvement" value="" placeholder="e.g. Technical writing skill">
        </td>
        <td>
            <input type="text" class="form-control form-control-sm dev-support" value="" placeholder="e.g. Online course or mentoring">
        </td>
        <td>
            <input type="text" class="form-control form-control-sm dev-timeframe" value="" placeholder="e.g. Q3 2026">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="this.closest('tr').remove()"><i class="fas fa-trash-alt"></i></button>
        </td>
    `;
    editTbody.appendChild(newRow);
}

function saveDevPlan(evalId) {
    const editDiv = document.getElementById(`devEdit${evalId}`);
    if (!editDiv) return;
    
    const rows = editDiv.querySelectorAll('.dev-edit-row');
    const plans = [];
    
    rows.forEach((row, index) => {
        const imp = row.querySelector('.dev-improvement').value.trim();
        const sup = row.querySelector('.dev-support').value.trim();
        const time = row.querySelector('.dev-timeframe').value.trim();
        
        if (imp || sup || time) {
            plans.push({
                improvement_area: imp,
                support_needed: sup,
                time_frame: time
            });
        }
    });

    const saveBtn = editDiv.querySelector('button.btn-success');
    const originalText = saveBtn ? saveBtn.innerHTML : '';
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
    }

    const formData = new FormData();
    formData.append('evaluation_id', evalId);
    plans.forEach((plan, i) => {
        formData.append(`plans[${i}][improvement_area]`, plan.improvement_area);
        formData.append(`plans[${i}][support_needed]`, plan.support_needed);
        formData.append(`plans[${i}][time_frame]`, plan.time_frame);
    });

    fetch('ajax/save-dev-plan.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
        }

        if (data.success) {
            alert(data.message);
            
            const viewTbody = document.getElementById(`devViewTableBody${evalId}`);
            if (viewTbody) {
                viewTbody.innerHTML = '';
                if (plans.length > 0) {
                    plans.forEach(plan => {
                        const r = document.createElement('tr');
                        r.innerHTML = `
                            <td class="ps-3">${escapeHtml(plan.improvement_area)}</td>
                            <td>${escapeHtml(plan.support_needed)}</td>
                            <td>${escapeHtml(plan.time_frame)}</td>
                        `;
                        viewTbody.appendChild(r);
                    });
                } else {
                    viewTbody.innerHTML = '<tr class="no-dev-row"><td colspan="3" class="text-center text-muted small py-3">No developmental plan recorded.</td></tr>';
                }
            }
            
            toggleDevEdit(evalId);
        } else {
            alert(data.message || 'An error occurred while saving developmental plan details.');
        }
    })
    .catch(error => {
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
        }
        console.error('Error:', error);
        alert('An unexpected error occurred.');
    });
}

function toggleCareerEdit(evalId) {
    const viewDiv = document.getElementById(`careerView${evalId}`);
    const editDiv = document.getElementById(`careerEdit${evalId}`);
    if (viewDiv && editDiv) {
        viewDiv.classList.toggle('d-none');
        editDiv.classList.toggle('d-none');
    }
}

function toggleSuitedInputFields(evalId) {
    const suitedInput = document.getElementById(`careerSuitedInput${evalId}`);
    const inputsContainer = document.getElementById(`suitedInputsContainer${evalId}`);
    if (suitedInput && inputsContainer) {
        if (suitedInput.value === '1') {
            inputsContainer.classList.remove('d-none');
        } else {
            inputsContainer.classList.add('d-none');
        }
    }
}

function saveCareerGrowth(evalId) {
    const suitedInput = document.getElementById(`careerSuitedInput${evalId}`);
    const positionInput = document.getElementById(`careerPosition${evalId}`);
    const dateInput = document.getElementById(`careerDate${evalId}`);
    const detailsInput = document.getElementById(`careerDetails${evalId}`);

    if (!suitedInput) return;

    const suited = parseInt(suitedInput.value) || 0;
    const position = positionInput ? positionInput.value.trim() : '';
    const date = dateInput ? dateInput.value : '';
    const details = detailsInput ? detailsInput.value.trim() : '';

    const formData = new FormData();
    formData.append('evaluation_id', evalId);
    formData.append('career_growth_suited', suited);
    formData.append('desired_position', position);
    formData.append('target_date', date);
    formData.append('career_growth_details', details);

    const saveBtn = document.querySelector(`#careerEdit${evalId} button.btn-success`);
    const originalText = saveBtn ? saveBtn.innerHTML : '';
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
    }

    fetch('ajax/save-career-growth.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
        }

        if (data.success) {
            alert(data.message);
            
            const suitedBadge = document.getElementById(`careerSuitedBadge${evalId}`);
            if (suitedBadge) {
                if (suited === 1) {
                    suitedBadge.innerHTML = '&#9745; Yes';
                    suitedBadge.classList.remove('bg-secondary');
                    suitedBadge.classList.add('bg-success');
                } else {
                    suitedBadge.innerHTML = '&#9744; No';
                    suitedBadge.classList.remove('bg-success');
                    suitedBadge.classList.add('bg-secondary');
                }
            }

            const positionText = document.getElementById(`careerPositionText${evalId}`);
            if (positionText) {
                positionText.textContent = position || 'N/A';
            }
            const dateText = document.getElementById(`careerDateText${evalId}`);
            if (dateText) {
                dateText.textContent = date || 'N/A';
            }
            const detailsText = document.getElementById(`careerDetailsText${evalId}`);
            if (detailsText) {
                detailsText.textContent = details || 'N/A';
            }

            const positionContainer = document.getElementById(`careerPositionContainer${evalId}`);
            if (positionContainer) {
                if (suited === 1) {
                    positionContainer.classList.remove('d-none');
                } else {
                    positionContainer.classList.add('d-none');
                }
            }

            const dateContainer = document.getElementById(`careerDateContainer${evalId}`);
            if (dateContainer) {
                if (suited === 1 && date) {
                    dateContainer.classList.remove('d-none');
                } else {
                    dateContainer.classList.add('d-none');
                }
            }

            const detailsContainer = document.getElementById(`careerDetailsContainer${evalId}`);
            if (detailsContainer) {
                if (suited === 1 && details) {
                    detailsContainer.classList.remove('d-none');
                } else {
                    detailsContainer.classList.add('d-none');
                }
            }

            toggleCareerEdit(evalId);
        } else {
            alert(data.message || 'An error occurred while saving career growth details.');
        }
    })
    .catch(error => {
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
        }
        console.error('Error:', error);
        alert('An unexpected error occurred.');
    });
}

function handleApprovalFormSubmit(event, evalId) {
    const form = event.target;
    const modal = document.querySelector(`#reviewModal${evalId}`);
    if (!modal) return true;

    const inputs = modal.querySelectorAll('.score-input');
    let modified = false;
    let hasError = false;
    const ratings = {};

    inputs.forEach(input => {
        const val = parseFloat(input.value);
        const origVal = parseFloat(input.getAttribute('data-original-val'));
        const scoreId = input.getAttribute('data-score-id');

        if (!scoreId || isNaN(val) || val < 1.00 || val > 4.00) {
            hasError = true;
            input.classList.add('is-invalid');
        } else {
            input.classList.remove('is-invalid');
            ratings[scoreId] = val;
            if (val !== origVal) {
                modified = true;
            }
        }
    });

    if (hasError) {
        if (inputs.length && inputs[0].classList.contains('d-none')) {
            toggleEditRatings(evalId);
        }
        alert('Please enter valid ratings between 1.00 and 4.00.');
        return false;
    }

    if (!modified) {
        return true;
    }

    event.preventDefault();

    const submitBtn = event.submitter;
    const originalBtnText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving & Submitting...';

    const otherBtns = form.querySelectorAll('.fixed-action-bar button');
    otherBtns.forEach(btn => {
        if (btn !== submitBtn) btn.disabled = true;
    });

    const formData = new FormData();
    formData.append('evaluation_id', evalId);
    for (const [key, value] of Object.entries(ratings)) {
        formData.append(`ratings[${key}]`, value);
    }

    fetch('ajax/save-rating.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const hiddenAction = document.createElement('input');
            hiddenAction.type = 'hidden';
            hiddenAction.name = 'action';
            hiddenAction.value = submitBtn.value;
            form.appendChild(hiddenAction);

            form.submit();
        } else {
            alert(data.message || 'An error occurred while saving ratings before submission.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
            otherBtns.forEach(btn => {
                btn.disabled = false;
            });
        }
    })
    .catch(error => {
        console.error('Error saving ratings:', error);
        alert('An unexpected error occurred while saving ratings before submission.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
        otherBtns.forEach(btn => {
            btn.disabled = false;
        });
    });

    return false;
}

// Live ratings recalculation for HRD review modals
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.modal-premium').forEach(modal => {
        const kraWeight = parseFloat(modal.getAttribute('data-kra-weight')) / 100;
        const behaviorWeight = parseFloat(modal.getAttribute('data-behavior-weight')) / 100;

        const inputs = modal.querySelectorAll('.score-input');
        inputs.forEach(input => {
            input.addEventListener('input', () => {
                // --- KRA Recalculation ---
                let kraTotal = 0;
                modal.querySelectorAll('.kra-row').forEach(row => {
                    const weight = parseFloat(row.getAttribute('data-weight')) / 100;
                    const inp = row.querySelector('.score-input');
                    const weightedCell = row.querySelector('.weighted-score');
                    if (inp) {
                        const val = parseFloat(inp.value) || 0;
                        const weighted = val * weight;
                        kraTotal += weighted;
                        if (weightedCell) {
                            weightedCell.textContent = weighted.toFixed(2);
                        }
                    }
                });

                // --- Behavior Recalculation ---
                let behSum = 0;
                let behCount = 0;
                modal.querySelectorAll('.beh-row').forEach(row => {
                    const inp = row.querySelector('.score-input');
                    if (inp) {
                        const val = parseFloat(inp.value) || 0;
                        behSum += val;
                        behCount++;
                    }
                });
                const behAvg = behCount > 0 ? (behSum / behCount) : 0;

                // --- Round & Sum ---
                const kraRounded = Math.round(kraTotal * 100) / 100;
                const behAvgRounded = Math.round(behAvg * 100) / 100;
                const finalScore = (kraRounded * kraWeight + behAvgRounded * behaviorWeight);
                const finalScoreRounded = Math.round(finalScore * 100) / 100;

                // --- Update UI ---
                const kraSubtotalVal = modal.querySelector('.kra-subtotal-val');
                if (kraSubtotalVal) kraSubtotalVal.textContent = kraRounded.toFixed(2);

                const behAvgVal = modal.querySelector('.beh-avg-val');
                if (behAvgVal) behAvgVal.textContent = behAvgRounded.toFixed(2);

                const totalScoreVal = modal.querySelector('.total-score-val');
                if (totalScoreVal) totalScoreVal.textContent = finalScoreRounded.toFixed(2) + '/4';
            });
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
