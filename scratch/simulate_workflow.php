<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Disable auditing or define dummy session variables to avoid warnings
session_start();
$_SESSION['user_id'] = 6; // Kenneth Losloso user_id is 6
$_SESSION['employee_id'] = 503;
$_SESSION['full_name'] = "Kenneth Losloso";

ensureEvaluationWorkflowSchema($conn);

echo "=== RESETTING EVALUATION 1 TO DRAFT ===\n";
$conn->query("UPDATE evaluations SET status = 'Draft', dept_supervisor_confirmed_by = NULL, dept_supervisor_confirmed_date = NULL, dept_manager_endorsed_by = NULL, dept_manager_endorsed_date = NULL, dept_manager_comments = NULL, supervisor_confirmed_by = NULL, supervisor_confirmed_date = NULL, supervisor_comments = NULL, endorsed_by = NULL, endorsed_date = NULL, evaluator_comments = NULL, approved_by = NULL, approved_date = NULL WHERE evaluation_id = 1");
$conn->query("DELETE FROM notifications"); // Clear notifications for clean output

$eval = $conn->query("SELECT * FROM evaluations WHERE evaluation_id = 1")->fetch_assoc();
echo "Initial Status: " . $eval['status'] . "\n";

// STEP 1: Kenneth Submits Self-Rating
echo "\n=== STEP 1: KENNETH SUBMITS SELF-RATING ===\n";
$employee_id = 503;
$supervisor = getEmployeeSupervisor($conn, $employee_id);
$has_supervisor = ($supervisor !== null && !empty($supervisor['user_id']));
$status = $has_supervisor ? 'Pending Dept Supervisor' : 'Pending HR Consolidation';

$conn->query("UPDATE evaluations SET status = '$status', submitted_date = NOW() WHERE evaluation_id = 1");
if ($has_supervisor) {
    createNotification(
        $conn,
        (int)$supervisor['user_id'],
        'Self-Rating Submitted',
        "Kenneth Losloso has submitted their self-rating for review.",
        'confirm-rating.php?evaluation_id=1'
    );
}
echo "New Status: " . $conn->query("SELECT status FROM evaluations WHERE evaluation_id = 1")->fetch_assoc()['status'] . "\n";
echo "Notifications created:\n";
$notifs = $conn->query("SELECT * FROM notifications");
while ($n = $notifs->fetch_assoc()) {
    echo "To User ID: {$n['user_id']} | Title: {$n['title']} | Msg: {$n['message']}\n";
}

// STEP 2: Supervisor Marie Cruz (User ID 2) Confirms Self-Rating
echo "\n=== STEP 2: SUPERVISOR MARIE CONFIRMS SELF-RATING ===\n";
$_SESSION['user_id'] = 2; // Marie Cruz
$_SESSION['employee_id'] = 502;
$_SESSION['full_name'] = "Marie Cruz";

$eval = $conn->query("SELECT * FROM evaluations WHERE evaluation_id = 1")->fetch_assoc();
$dept_manager = getDeptManagerOfEmployee($conn, (int)$eval['employee_id']);
$next_status = $dept_manager ? 'Pending Dept Manager' : 'Pending HR Consolidation';

$update = $conn->prepare("
    UPDATE evaluations 
    SET status = ?,
        dept_supervisor_confirmed_by = ?,
        dept_supervisor_confirmed_date = NOW(),
        supervisor_confirmed_by = ?,
        supervisor_confirmed_date = NOW(),
        supervisor_comments = 'Good performance, verified.',
        sent_to_hr_date = NOW(),
        sent_to_hr_by = ?
    WHERE evaluation_id = 1
");
$user_id = 2;
$update->bind_param("siii", $next_status, $user_id, $user_id, $user_id);
$update->execute();
$update->close();

$conn->query("DELETE FROM notifications"); // Clear for step output
if ($next_status === 'Pending Dept Manager') {
    createNotification(
        $conn,
        (int)$dept_manager['user_id'],
        'Evaluation Pending Endorsement',
        "Marie Cruz confirmed self-rating for Kenneth Losloso and requires your endorsement.",
        'dept-manager-review.php?evaluation_id=1'
    );
}
echo "New Status: " . $conn->query("SELECT status FROM evaluations WHERE evaluation_id = 1")->fetch_assoc()['status'] . "\n";
echo "Notifications created:\n";
$notifs = $conn->query("SELECT * FROM notifications");
while ($n = $notifs->fetch_assoc()) {
    echo "To User ID: {$n['user_id']} | Title: {$n['title']} | Msg: {$n['message']}\n";
}

// STEP 3: Dept Manager Raymond Santos (User ID 9) Endorses Self-Rating
echo "\n=== STEP 3: DEPT MANAGER RAYMOND ENDORSES ===\n";
$_SESSION['user_id'] = 9; // Raymond Santos
$_SESSION['employee_id'] = 501;
$_SESSION['full_name'] = "Raymond Santos";

$eval = $conn->query("SELECT * FROM evaluations WHERE evaluation_id = 1")->fetch_assoc();
$conn->query("DELETE FROM notifications"); // Clear

$update = $conn->prepare("
    UPDATE evaluations
    SET status = 'Pending HR Consolidation',
        dept_manager_endorsed_by = ?,
        dept_manager_endorsed_date = NOW(),
        dept_manager_comments = 'Endorsed. Strong technical skills.'
    WHERE evaluation_id = 1
");
$user_id = 9;
$update->bind_param("i", $user_id);
$update->execute();
$update->close();

$hr_users = $conn->query("SELECT user_id FROM users WHERE role IN ('HR Supervisor', 'HR Manager') AND is_active = 1");
while ($hr = $hr_users->fetch_assoc()) {
    createNotification(
        $conn,
        (int)$hr['user_id'],
        'Evaluation Endorsed by Dept Manager',
        "Raymond Santos endorsed evaluation for Kenneth Losloso — forwarded to HRD.",
        'pending-endorsements.php'
    );
}

echo "New Status: " . $conn->query("SELECT status FROM evaluations WHERE evaluation_id = 1")->fetch_assoc()['status'] . "\n";
echo "Notifications created:\n";
$notifs = $conn->query("SELECT * FROM notifications");
while ($n = $notifs->fetch_assoc()) {
    echo "To User ID: {$n['user_id']} | Title: {$n['title']} | Msg: {$n['message']}\n";
}

// STEP 4: HR Supervisor Patricia Gomez (User ID 4 or 12? Wait, Patrica's user_id in check_users was 4 and 12)
echo "\n=== STEP 4: HR SUPERVISOR PATRICIA ENDORSES ===\n";
$_SESSION['user_id'] = 12; // Patricia Gomez (HR Supervisor)
$_SESSION['employee_id'] = 301;
$_SESSION['full_name'] = "Patricia Gomez";

$conn->query("DELETE FROM notifications"); // Clear

$conn->query("
    UPDATE evaluations
    SET status = 'Pending Manager',
        endorsed_by = 12,
        endorsed_date = NOW(),
        evaluator_comments = 'HR reviewed and consolidated.'
    WHERE evaluation_id = 1
");

$managers = $conn->query("SELECT user_id FROM users WHERE role = 'HR Manager' AND is_active = 1");
while ($mgr = $managers->fetch_assoc()) {
    createNotification($conn, $mgr['user_id'], 'Evaluation Endorsed', "Evaluation for Kenneth Losloso has been endorsed and requires your approval.", 'pending-approvals.php');
}

echo "New Status: " . $conn->query("SELECT status FROM evaluations WHERE evaluation_id = 1")->fetch_assoc()['status'] . "\n";
echo "Notifications created:\n";
$notifs = $conn->query("SELECT * FROM notifications");
while ($n = $notifs->fetch_assoc()) {
    echo "To User ID: {$n['user_id']} | Title: {$n['title']} | Msg: {$n['message']}\n";
}

// STEP 5: HR Manager Elena Delgado (User ID 3 or 11) Approves
echo "\n=== STEP 5: HR MANAGER ELENA APPROVES ===\n";
$_SESSION['user_id'] = 11; // Elena Delgado (HR Manager)
$_SESSION['employee_id'] = 101;
$_SESSION['full_name'] = "Elena Delgado";

$conn->query("DELETE FROM notifications"); // Clear

$conn->query("
    UPDATE evaluations
    SET status = 'Approved',
        approved_by = 11,
        approved_date = NOW()
    WHERE evaluation_id = 1
");

echo "New Status: " . $conn->query("SELECT status FROM evaluations WHERE evaluation_id = 1")->fetch_assoc()['status'] . "\n";

// Reset to Returned for the user
$conn->query("UPDATE evaluations SET status = 'Returned' WHERE evaluation_id = 1");
echo "\n=== RESET COMPLETED ===\n";
