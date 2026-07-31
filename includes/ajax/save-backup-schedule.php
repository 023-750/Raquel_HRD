<?php
require_once '../../includes/session-check.php';
checkRole(['Admin']);
require_once '../../includes/functions.php';
verifyCsrfToken();

header('Content-Type: application/json');

// ── Read & Validate Inputs ───────────────────────────────────────────────────
$enabled   = isset($_POST['enabled'])   ? (int)$_POST['enabled']   : 0;
$frequency = $_POST['frequency']  ?? 'daily';
$weekday   = (int)($_POST['weekday']   ?? 1); // 0=Sun..6=Sat
$monthday  = (int)($_POST['monthday']  ?? 1); // 1..28
$hour      = (int)($_POST['hour']      ?? 2); // 0..23
$btype     = $_POST['btype']      ?? 'full';
$keep      = (int)($_POST['keep']      ?? 7); // min 1

$frequency = in_array($frequency, ['daily','weekly','monthly']) ? $frequency : 'daily';
$btype     = in_array($btype, ['full','schema','data']) ? $btype : 'full';
$weekday   = max(0, min(6, $weekday));
$monthday  = max(1, min(28, $monthday));
$hour      = max(0, min(23, $hour));
$keep      = max(1, min(90, $keep));

// ── Compute Next Run ─────────────────────────────────────────────────────────
function compute_next_run($frequency, $weekday, $monthday, $hour) {
    $now = new DateTime('now');
    $next = new DateTime('now');
    $next->setTime($hour, 0, 0);

    switch ($frequency) {
        case 'daily':
            if ($next <= $now) {
                $next->modify('+1 day');
            }
            break;
        case 'weekly':
            // Find next occurrence of the given weekday (0=Sun, 1=Mon … 6=Sat)
            $days_of_week = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
            $target_day   = $days_of_week[$weekday];
            $next->modify("next {$target_day}");
            $next->setTime($hour, 0, 0);
            // If that next day is today and time hasn't passed yet, use today
            $today_run = new DateTime('now');
            $today_run->setTime($hour, 0, 0);
            $today_dow = (int)date('w');
            if ($today_dow === $weekday && $today_run > $now) {
                $next = $today_run;
            }
            break;
        case 'monthly':
            $next->setDate((int)date('Y'), (int)date('n'), $monthday);
            $next->setTime($hour, 0, 0);
            if ($next <= $now) {
                $next->modify('+1 month');
                $next->setDate((int)$next->format('Y'), (int)$next->format('n'), $monthday);
                $next->setTime($hour, 0, 0);
            }
            break;
    }
    return $next->format('Y-m-d H:i:s');
}

$next_run = compute_next_run($frequency, $weekday, $monthday, $hour);

// ── Persist to system_settings ───────────────────────────────────────────────
$settings = [
    'auto_backup_enabled'   => $enabled,
    'auto_backup_frequency' => $frequency,
    'auto_backup_weekday'   => $weekday,
    'auto_backup_monthday'  => $monthday,
    'auto_backup_hour'      => $hour,
    'auto_backup_type'      => $btype,
    'auto_backup_keep'      => $keep,
    'auto_backup_next_run'  => $next_run,
];

$stmt = $conn->prepare("INSERT INTO system_settings (setting_key, setting_value)
                        VALUES (?, ?)
                        ON DUPLICATE KEY UPDATE setting_value = ?");

foreach ($settings as $key => $value) {
    $v = (string)$value;
    $stmt->bind_param('sss', $key, $v, $v);
    $stmt->execute();
}

logAudit($conn, $_SESSION['user_id'], 'UPDATE', 'BackupSchedule', 0,
    "Auto-backup schedule updated: {$frequency}, type={$btype}, hour={$hour}h, keep={$keep}, enabled={$enabled}");

echo json_encode([
    'success'  => true,
    'next_run' => $next_run,
    'enabled'  => $enabled,
]);
?>
