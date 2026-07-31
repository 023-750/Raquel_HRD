<?php
/**
 * auto-backup-check.php
 * ─────────────────────
 * "Poor man's cron" — included at the top of every Admin page load.
 * Silently checks whether a scheduled backup is due and runs it if so.
 * Sets $_SESSION['auto_backup_toast'] for the UI to display a flash notice.
 */

if (!isset($conn) || !isset($_SESSION['user_id'])) return;

// Only run for Admin role
if (($_SESSION['role'] ?? '') !== 'Admin') return;

// ── Helper: read a setting ───────────────────────────────────────────────────
function ab_get_setting($conn, $key, $default = '') {
    $stmt = $conn->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
    if (!$stmt) return $default;
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $r ? $r['setting_value'] : $default;
}

// ── Helper: set a setting ────────────────────────────────────────────────────
function ab_set_setting($conn, $key, $value) {
    $v = (string)$value;
    $stmt = $conn->prepare("INSERT INTO system_settings (setting_key, setting_value)
                            VALUES (?, ?)
                            ON DUPLICATE KEY UPDATE setting_value = ?");
    if (!$stmt) return;
    $stmt->bind_param('sss', $key, $v, $v);
    $stmt->execute();
    $stmt->close();
}

// ── Bail early if not enabled ────────────────────────────────────────────────
$enabled = (int)ab_get_setting($conn, 'auto_backup_enabled', '0');
if (!$enabled) return;

// ── Check if it is time ──────────────────────────────────────────────────────
$next_run_str = ab_get_setting($conn, 'auto_backup_next_run', '');
if (empty($next_run_str)) return;

$now      = new DateTime('now');
$next_run = new DateTime($next_run_str);

if ($now < $next_run) return; // Not yet time

// ── Prevent duplicate runs in the same minute (session flag) ─────────────────
$lock_key = 'ab_lock_' . date('YmdHi');
if (!empty($_SESSION[$lock_key])) return;
$_SESSION[$lock_key] = true;

// ── Run the backup ───────────────────────────────────────────────────────────
$btype      = ab_get_setting($conn, 'auto_backup_type', 'full');
$keep       = (int)ab_get_setting($conn, 'auto_backup_keep', '7');
$frequency  = ab_get_setting($conn, 'auto_backup_frequency', 'daily');
$weekday    = (int)ab_get_setting($conn, 'auto_backup_weekday', '1');
$monthday   = (int)ab_get_setting($conn, 'auto_backup_monthday', '1');
$hour       = (int)ab_get_setting($conn, 'auto_backup_hour', '2');

$backup_dir = dirname(__DIR__, 2) . '/backups/';
if (!is_dir($backup_dir)) @mkdir($backup_dir, 0777, true);

$timestamp = date('Y-m-d_His');
$label     = 'Full Backup';
$filename  = 'raquel_hris_backup_' . $timestamp . '.sql';
$extra     = '';
if ($btype === 'schema') {
    $filename = 'raquel_hris_schema_' . $timestamp . '.sql';
    $extra    = '--no-data';
    $label    = 'Schema Only';
} elseif ($btype === 'data') {
    $filename = 'raquel_hris_data_' . $timestamp . '.sql';
    $extra    = '--no-create-info';
    $label    = 'Data Only';
}
$dest = $backup_dir . $filename;

// ── Try mysqldump first ──────────────────────────────────────────────────────
$mysqldump = file_exists('C:\xampp\mysql\bin\mysqldump.exe')
           ? 'C:\xampp\mysql\bin\mysqldump.exe'
           : 'mysqldump';
$cmd = sprintf('"%s" --user=%s --password=%s --host=%s %s %s > "%s"',
    $mysqldump, DB_USER,
    DB_PASS != '' ? DB_PASS : "''",
    DB_HOST, $extra, DB_NAME, $dest);
$ret = -1;
@exec($cmd, $out, $ret);
$success = ($ret === 0 && file_exists($dest) && filesize($dest) > 0);

// ── Fallback: PHP native dump ────────────────────────────────────────────────
if (!$success) {
    // Inline PHP dump (schema + data)
    $tables_res = $conn->query("SHOW TABLES");
    $tables = [];
    while ($row = $tables_res->fetch_row()) $tables[] = $row[0];
    $sql  = "-- Raquel HRIS Auto Backup ({$label})\n";
    $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
    $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
    foreach ($tables as $tbl) {
        if ($btype !== 'data') {
            $r = $conn->query("SHOW CREATE TABLE `{$tbl}`");
            if ($r) {
                $row = $r->fetch_row();
                $sql .= "DROP TABLE IF EXISTS `{$tbl}`;\n" . $row[1] . ";\n\n";
            }
        }
        if ($btype !== 'schema') {
            $r = $conn->query("SELECT * FROM `{$tbl}`");
            if ($r) {
                while ($row = $r->fetch_row()) {
                    $vals = array_map(fn($v) => isset($v) ? "'" . $conn->real_escape_string($v) . "'" : 'NULL', $row);
                    $sql .= "INSERT INTO `{$tbl}` VALUES(" . implode(',', $vals) . ");\n";
                }
                $sql .= "\n";
            }
        }
    }
    $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
    $success = (file_put_contents($dest, $sql) !== false);
}

// ── Auto-cleanup: keep only last N backups ───────────────────────────────────
if ($success && $keep > 0) {
    $files = @scandir($backup_dir);
    $files = is_array($files) ? array_diff($files, ['.','..']) : [];
    $sql_files = [];
    foreach ($files as $f) {
        if (pathinfo($f, PATHINFO_EXTENSION) === 'sql') {
            $sql_files[] = ['name' => $f, 'time' => filemtime($backup_dir . $f)];
        }
    }
    usort($sql_files, fn($a,$b) => $b['time'] - $a['time']); // newest first
    foreach (array_slice($sql_files, $keep) as $old) {
        @unlink($backup_dir . $old['name']);
    }
}

// ── Compute next run time ────────────────────────────────────────────────────
function ab_next_run($frequency, $weekday, $monthday, $hour) {
    $now  = new DateTime('now');
    $next = new DateTime('now');
    $next->setTime($hour, 0, 0);
    if ($frequency === 'daily') {
        $next->modify('+1 day');
    } elseif ($frequency === 'weekly') {
        $days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        $next->modify("next {$days[$weekday]}")->setTime($hour, 0, 0);
    } elseif ($frequency === 'monthly') {
        $next->modify('+1 month');
        $next->setDate((int)$next->format('Y'), (int)$next->format('n'), $monthday);
        $next->setTime($hour, 0, 0);
    }
    return $next->format('Y-m-d H:i:s');
}

$next_str = ab_next_run($frequency, $weekday, $monthday, $hour);

// ── Persist run timestamps ───────────────────────────────────────────────────
ab_set_setting($conn, 'auto_backup_last_run', date('Y-m-d H:i:s'));
ab_set_setting($conn, 'auto_backup_next_run', $next_str);

// ── Log audit ────────────────────────────────────────────────────────────────
if ($success) {
    logAudit($conn, $_SESSION['user_id'], 'CREATE', 'AutoBackup', 0,
        "Scheduled auto-backup ({$label}): {$filename}");
}

// ── Set session toast for UI display ─────────────────────────────────────────
$_SESSION['auto_backup_toast'] = $success
    ? ['type' => 'success', 'msg' => "✅ Scheduled {$label} auto-backup completed: <strong>{$filename}</strong>"]
    : ['type' => 'danger',  'msg' => "❌ Scheduled auto-backup failed. Check backups folder permissions."];
?>
