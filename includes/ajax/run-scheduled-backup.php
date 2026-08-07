<?php
require_once '../../includes/session-check.php';
checkRole(['Admin']);
require_once '../../includes/functions.php';
verifyCsrfToken();

header('Content-Type: application/json');
require_once '../../includes/auto-backup-check.php';

echo json_encode([
    'success' => true,
    'result' => $GLOBALS['auto_backup_check_result'] ?? ['checked' => false],
]);
