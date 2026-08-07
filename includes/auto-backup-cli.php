<?php
/**
 * CLI entry point for Windows Task Scheduler.
 * Run this every minute; the saved schedule decides whether a backup is due.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

$is_auto_backup_cli = true;
require __DIR__ . '/auto-backup-check.php';
