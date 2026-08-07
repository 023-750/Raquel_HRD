<?php
require_once '../../includes/session-check.php';
checkRole(['Admin']);
require_once '../../includes/functions.php';
require_once '../../includes/backup-engine.php';
verifyCsrfToken();

header('Content-Type: application/json');

$type = $_POST['type'] ?? 'full';
$result = backup_create_database_snapshot($conn, $type);

if ($result['success']) {
    logAudit($conn, $_SESSION['user_id'], 'CREATE', 'Backup', 0,
        "Created {$result['label']} backup ({$result['method']}): {$result['filename']}");
}

echo json_encode($result);
?>
