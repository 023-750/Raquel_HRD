<?php
$page_title = 'Team Evaluation Packages';
require_once '../includes/session-check.php';
require_once '../includes/functions.php';

if (!in_array($_SESSION['role'] ?? '', ['Employee', 'HR Manager', 'HR Supervisor'], true)) {
    header('Location: ' . BASE_URL . '/employee/index.php');
    exit;
}

ensureOrganizationEvaluationPackageSchema($conn);
syncPendingOrganizationPackageBoardApprover($conn);
$user_id = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $package_id = (int) ($_POST['package_id'] ?? 0);
    $action = $_POST['package_action'] ?? '';
    $comments = trim($_POST['comments'] ?? '');

    $step_stmt = $conn->prepare("SELECT rs.*, ep.department_id, ep.template_id, ep.period_start, ep.period_end
        FROM evaluation_package_route_steps rs JOIN evaluation_packages ep ON ep.package_id = rs.package_id
        WHERE rs.package_id = ? AND rs.reviewer_user_id = ? AND rs.action_status = 'Pending' LIMIT 1");
    $step_stmt->bind_param('ii', $package_id, $user_id); $step_stmt->execute();
    $step = $step_stmt->get_result()->fetch_assoc(); $step_stmt->close();
    if (!$step) redirectWith(BASE_URL . '/employee/team-evaluation-packages.php', 'danger', 'This package is not currently assigned to you.');

    if ($action === 'return') {
        if ($comments === '') redirectWith(BASE_URL . '/employee/team-evaluation-packages.php', 'danger', 'A return reason is required.');
        if ((int) $step['step_order'] <= 1) {
            redirectWith(BASE_URL . '/employee/team-evaluation-packages.php', 'danger', 'The consolidator cannot return the whole package. Use View & adjust to correct an individual rating before approving.');
        }
        $update = $conn->prepare("UPDATE evaluation_package_route_steps SET action_status = 'Returned', acted_at = NOW(), comments = ? WHERE package_route_step_id = ?");
        $update->bind_param('si', $comments, $step['package_route_step_id']); $update->execute(); $update->close();
        $previous_order = (int) $step['step_order'] - 1;
        $previous_stmt = $conn->prepare('SELECT package_route_step_id, reviewer_user_id, step_label, step_type FROM evaluation_package_route_steps WHERE package_id = ? AND step_order = ? LIMIT 1');
        $previous_stmt->bind_param('ii', $package_id, $previous_order); $previous_stmt->execute();
        $previous = $previous_stmt->get_result()->fetch_assoc(); $previous_stmt->close();
        if (!$previous) redirectWith(BASE_URL . '/employee/team-evaluation-packages.php', 'danger', 'The previous reviewer could not be found.');
        $reopen = $conn->prepare("UPDATE evaluation_package_route_steps SET action_status = 'Pending', acted_at = NULL WHERE package_route_step_id = ?");
        $reopen->bind_param('i', $previous['package_route_step_id']); $reopen->execute(); $reopen->close();
        $status = $previous['step_type'] === 'Consolidation' ? 'Pending Consolidation' : 'Pending Review';
        $package_update = $conn->prepare('UPDATE evaluation_packages SET status = ?, current_step_order = ? WHERE package_id = ?');
        $package_update->bind_param('sii', $status, $previous_order, $package_id); $package_update->execute(); $package_update->close();
        $audit = $conn->prepare("INSERT INTO evaluation_package_audit (package_id, user_id, action, remarks) VALUES (?, ?, 'RETURNED', ?)");
        $audit->bind_param('iis', $package_id, $user_id, $comments); $audit->execute(); $audit->close();
        createNotification($conn, (int) $previous['reviewer_user_id'], 'Team evaluation returned for revision', 'A package was returned to you by ' . $step['step_label'] . '. Reason: ' . $comments, BASE_URL . '/employee/team-evaluation-packages.php');
        redirectWith(BASE_URL . '/employee/team-evaluation-packages.php', 'success', 'The package was returned to ' . $previous['step_label'] . ' for revision.');
    }

    if ($step['step_type'] === 'Consolidation') {
        $expected_stmt = $conn->prepare("SELECT COUNT(DISTINCT e.employee_id) AS total FROM employees e JOIN users u ON u.employee_id = e.employee_id AND u.is_active = 1 WHERE e.department_id = ? AND e.is_active = 1 AND e.deleted_at IS NULL");
        $expected_stmt->bind_param('i', $step['department_id']); $expected_stmt->execute(); $expected = (int) $expected_stmt->get_result()->fetch_assoc()['total']; $expected_stmt->close();
        $submitted_stmt = $conn->prepare("SELECT COUNT(DISTINCT ev.employee_id) AS total FROM evaluations ev JOIN employees e ON e.employee_id = ev.employee_id
            WHERE e.department_id = ? AND ev.template_id = ? AND ev.evaluation_period_start = ? AND ev.evaluation_period_end = ?
              AND ev.deleted_at IS NULL AND ev.status NOT IN ('Draft', 'Pending Self-Rating', 'Returned', 'Rejected')");
        $submitted_stmt->bind_param('iiss', $step['department_id'], $step['template_id'], $step['period_start'], $step['period_end']); $submitted_stmt->execute(); $submitted = (int) $submitted_stmt->get_result()->fetch_assoc()['total']; $submitted_stmt->close();
        if ($submitted < $expected) redirectWith(BASE_URL . '/employee/team-evaluation-packages.php', 'danger', "Consolidation is blocked: $submitted of $expected required team self-ratings are submitted.");
        recalculateOrganizationPackageBehaviorScore($conn, $package_id);
    }

    $approve = $conn->prepare("UPDATE evaluation_package_route_steps SET action_status = 'Approved', acted_at = NOW(), comments = ? WHERE package_route_step_id = ?");
    $approve->bind_param('si', $comments, $step['package_route_step_id']); $approve->execute(); $approve->close();
    $next_order = (int) $step['step_order'] + 1;
    $next_stmt = $conn->prepare('SELECT package_route_step_id, reviewer_user_id, step_label, step_type FROM evaluation_package_route_steps WHERE package_id = ? AND step_order = ? LIMIT 1');
    $next_stmt->bind_param('ii', $package_id, $next_order); $next_stmt->execute(); $next = $next_stmt->get_result()->fetch_assoc(); $next_stmt->close();
    if ($next) {
        $next_update = $conn->prepare("UPDATE evaluation_package_route_steps SET action_status = 'Pending' WHERE package_route_step_id = ?");
        $next_update->bind_param('i', $next['package_route_step_id']); $next_update->execute(); $next_update->close();
        $status = $next['step_type'] === 'Governance' ? 'Pending Board Approval' : ($step['step_type'] === 'Consolidation' ? 'Pending Review' : 'Pending Review');
        $package_update = $conn->prepare('UPDATE evaluation_packages SET current_step_order = ?, status = ? WHERE package_id = ?');
        $package_update->bind_param('isi', $next_order, $status, $package_id); $package_update->execute(); $package_update->close();
        createNotification($conn, (int) $next['reviewer_user_id'], 'Team evaluation package awaiting your review', 'The ' . $step['department_name'] . ' evaluation package was approved by ' . $step['step_label'] . ' and is ready for your step: ' . $next['step_label'] . '.', BASE_URL . '/employee/team-evaluation-packages.php');
    } else {
        $conn->begin_transaction();
        $applied = applyOrganizationPackageResults($conn, $package_id);
        if ($applied) {
            $package_update = $conn->prepare("UPDATE evaluation_packages SET status = 'Approved and Applied', current_step_order = NULL WHERE package_id = ?");
            $package_update->bind_param('i', $package_id); $package_update->execute(); $package_update->close(); $conn->commit();
            $members_stmt = $conn->prepare('SELECT DISTINCT u.user_id FROM evaluation_package_members pm JOIN evaluations e ON e.evaluation_id = pm.evaluation_id JOIN users u ON u.employee_id = e.employee_id AND u.is_active = 1 WHERE pm.package_id = ?');
            $members_stmt->bind_param('i', $package_id); $members_stmt->execute(); $member_users = $members_stmt->get_result();
            while ($member_user = $member_users->fetch_assoc()) createNotification($conn, (int) $member_user['user_id'], 'Evaluation approved', 'Your team evaluation package has completed the organization approval flow.', BASE_URL . '/employee/evaluation-history.php');
            $members_stmt->close();
        } else { $conn->rollback(); redirectWith(BASE_URL . '/employee/team-evaluation-packages.php', 'danger', 'The package could not be applied because its shared score is incomplete.'); }
    }
    $audit = $conn->prepare("INSERT INTO evaluation_package_audit (package_id, user_id, action, remarks) VALUES (?, ?, 'APPROVED', ?)");
    $audit->bind_param('iis', $package_id, $user_id, $comments); $audit->execute(); $audit->close();
    redirectWith(BASE_URL . '/employee/team-evaluation-packages.php', 'success', 'Package action saved successfully.');
}

require_once '../includes/header.php';
$packages_stmt = $conn->prepare("SELECT ep.*, d.department_name, et.template_name, rs.package_route_step_id, rs.step_label, rs.step_type, rs.action_status
    FROM evaluation_packages ep JOIN evaluation_package_route_steps rs ON rs.package_id = ep.package_id
    JOIN departments d ON d.department_id = ep.department_id JOIN evaluation_templates et ON et.template_id = ep.template_id
    WHERE rs.reviewer_user_id = ? AND rs.action_status = 'Pending' ORDER BY ep.updated_at DESC");
$packages_stmt->bind_param('i', $user_id); $packages_stmt->execute(); $packages = $packages_stmt->get_result()->fetch_all(MYSQLI_ASSOC); $packages_stmt->close();
$waiting_stmt = $conn->prepare("SELECT ep.*, d.department_name, et.template_name, rs.step_label
    FROM evaluation_packages ep JOIN evaluation_package_route_steps rs ON rs.package_id = ep.package_id
    JOIN departments d ON d.department_id = ep.department_id JOIN evaluation_templates et ON et.template_id = ep.template_id
    WHERE rs.reviewer_user_id = ? AND rs.action_status = 'Waiting' AND rs.step_order = 1 ORDER BY ep.updated_at DESC");
$waiting_stmt->bind_param('i', $user_id); $waiting_stmt->execute(); $waiting_packages = $waiting_stmt->get_result()->fetch_all(MYSQLI_ASSOC); $waiting_stmt->close();
?>
<main class="evaluation-packages container-fluid py-4">
    <section class="package-hero">
        <p class="mb-1 small">Organization-driven evaluation workflow</p>
        <h1 class="h4 mb-2">Team Evaluation Packages</h1>
        <p class="mb-0">Review consolidated team evaluations assigned to you. KRA remains individual; Core Behaviors &amp; Values is shared at package level.</p>
        <div class="d-flex flex-wrap gap-2 mt-3"><a class="btn btn-sm btn-outline-light" href="<?php echo BASE_URL; ?>/employee/evaluation-history.php"><i class="fas fa-history me-1"></i>My Evaluation History</a><a class="btn btn-sm btn-outline-light" href="<?php echo BASE_URL; ?>/employee/team-evaluation-history.php"><i class="fas fa-users me-1"></i>Team Evaluation History</a></div>
    </section>
    <?php if (!$packages && !$waiting_packages): ?>
        <section class="package-empty"><h2 class="h5">No team package is assigned to you</h2><p class="mb-0">When all department members submit the same template and period, the standing supervisor receives the consolidation package here.</p></section>
    <?php endif; ?>
    <?php foreach ($waiting_packages as $package): ?>
        <?php $summary = getOrganizationPackageSubmissionSummary($conn, $package); $outstanding = array_filter($summary['members'], static fn($member) => !(int) $member['is_submitted']); ?>
        <article class="package-card">
            <header class="package-card__header"><div><h2 class="h5 mb-1"><?php echo e($package['department_name']); ?> — <?php echo e($package['template_name']); ?></h2><p class="mb-0 text-muted small"><?php echo e($package['period_start']); ?> to <?php echo e($package['period_end']); ?></p></div><span class="package-status package-status--waiting">Waiting for team submissions</span></header>
            <div class="package-card__body"><div class="row g-3"><div class="col-sm-4 package-stat"><strong class="tabular-nums"><?php echo $summary['submitted']; ?> of <?php echo $summary['required']; ?></strong>Required self-ratings submitted</div><div class="col-sm-8"><p class="mb-1 fw-semibold">Consolidation is not available yet.</p><p class="mb-0 text-muted small">Waiting for: <?php echo e(implode(', ', array_map(static fn($member) => $member['employee_name'] . ' (' . $member['job_title'] . ')', $outstanding))); ?></p></div></div></div>
        </article>
    <?php endforeach; ?>
    <?php foreach ($packages as $package): ?>
        <?php
        $members_stmt = $conn->prepare("SELECT e.evaluation_id, emp.first_name, emp.last_name, emp.job_title, e.kra_subtotal, e.behavior_average, e.status FROM evaluation_package_members pm JOIN evaluations e ON e.evaluation_id = pm.evaluation_id JOIN employees emp ON emp.employee_id = e.employee_id WHERE pm.package_id = ? ORDER BY emp.last_name, emp.first_name");
        $members_stmt->bind_param('i', $package['package_id']); $members_stmt->execute(); $members = $members_stmt->get_result()->fetch_all(MYSQLI_ASSOC); $members_stmt->close();
        $route_stmt = $conn->prepare('SELECT step_label, action_status, acted_at, comments FROM evaluation_package_route_steps WHERE package_id = ? ORDER BY step_order');
        $route_stmt->bind_param('i', $package['package_id']); $route_stmt->execute(); $timeline = $route_stmt->get_result()->fetch_all(MYSQLI_ASSOC); $route_stmt->close();
        ?>
        <article class="package-card">
            <header class="package-card__header"><div><h2 class="h5 mb-1"><?php echo e($package['department_name']); ?> — <?php echo e($package['template_name']); ?></h2><p class="mb-0 text-muted small"><?php echo e($package['period_start']); ?> to <?php echo e($package['period_end']); ?></p></div><span class="package-status package-status--review"><?php echo e($package['step_label']); ?></span></header>
            <div class="package-card__body">
                <div class="row g-3 mb-4"><div class="col-sm-4 package-stat"><strong class="tabular-nums"><?php echo count($members); ?></strong>Submitted members</div><div class="col-sm-4 package-stat"><strong class="tabular-nums"><?php echo $package['shared_behavior_score'] !== null ? number_format((float)$package['shared_behavior_score'], 2) : 'Not set'; ?></strong>Shared Behavior score</div><div class="col-sm-4 package-stat"><strong><?php echo e($package['status']); ?></strong>Package status</div></div>
                <div class="table-responsive mb-4"><table class="table package-table align-middle"><thead><tr><th>Employee</th><th>Position</th><th class="text-end">KRA</th><th class="text-end">Self Behavior</th><th>Status</th><th>Action</th></tr></thead><tbody><?php foreach ($members as $member): ?><tr><td><?php echo e($member['first_name'] . ' ' . $member['last_name']); ?></td><td><?php echo e($member['job_title']); ?></td><td class="text-end tabular-nums"><?php echo number_format((float)$member['kra_subtotal'], 2); ?></td><td class="text-end tabular-nums"><?php echo number_format((float)$member['behavior_average'], 2); ?></td><td><?php echo e($member['status']); ?></td><td><div class="d-flex flex-wrap gap-1"><a class="btn btn-sm btn-outline-secondary" href="<?php echo BASE_URL; ?>/employee/package-member-view.php?package_id=<?php echo (int)$package['package_id']; ?>&evaluation_id=<?php echo (int)$member['evaluation_id']; ?>">View</a><a class="btn btn-sm btn-outline-primary" href="<?php echo BASE_URL; ?>/employee/package-member-review.php?package_id=<?php echo (int)$package['package_id']; ?>&evaluation_id=<?php echo (int)$member['evaluation_id']; ?>">Adjust</a></div></td></tr><?php endforeach; ?></tbody></table></div>
                <section class="package-history mb-4"><h3 class="h6">Approval history</h3><ol class="package-timeline"><?php foreach ($timeline as $entry): ?><li><strong><?php echo e($entry['step_label']); ?></strong><div class="small text-muted"><?php echo e($entry['action_status']); ?><?php if (!empty($entry['acted_at'])): ?> · <?php echo e($entry['acted_at']); ?><?php endif; ?></div><?php if (!empty($entry['comments'])): ?><div class="small">Remarks: <?php echo e($entry['comments']); ?></div><?php endif; ?></li><?php endforeach; ?></ol></section>
                <form method="post" class="package-action-panel"><input type="hidden" name="package_id" value="<?php echo (int)$package['package_id']; ?>"><?php echo csrfField(); ?><?php if ($package['step_type'] === 'Consolidation'): ?><p class="mb-3 small text-muted">The shared Core Behaviors &amp; Values score is calculated automatically from every team member’s final Behavior ratings. Use <strong>View &amp; adjust</strong> to review an individual submission. Consolidators correct ratings directly, so the whole package cannot be returned.</p><?php endif; ?><div class="mb-3"><label class="form-label" for="comments-<?php echo (int)$package['package_id']; ?>">Review remarks</label><textarea class="form-control" id="comments-<?php echo (int)$package['package_id']; ?>" name="comments" rows="3" placeholder="Add review context or return instructions."></textarea></div><div class="d-flex flex-wrap gap-2"><button class="btn btn-primary" type="submit" name="package_action" value="approve">Approve and send to next reviewer</button><?php if ($package['step_type'] !== 'Consolidation'): ?><button class="btn btn-outline-danger" type="submit" name="package_action" value="return">Return for revision</button><?php endif; ?></div></form>
            </div>
        </article>
    <?php endforeach; ?>
</main>
<?php require_once '../includes/footer.php'; ?>
