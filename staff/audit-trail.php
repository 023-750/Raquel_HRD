<?php
$page_title = 'My Audit Trail';
require_once '../includes/session-check.php';
checkRole(['HR Staff']);
require_once '../includes/functions.php';
require_once '../includes/header.php';
require_once '../includes/audit-trail-monitor.php';
require_once '../includes/footer.php';
