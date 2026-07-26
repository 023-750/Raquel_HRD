<?php
require_once '../../includes/session-check.php';
checkRole(['Admin']);
require_once '../../includes/functions.php';
verifyCsrfToken();

$db_host = DB_HOST;
$db_user = DB_USER;
$db_pass = DB_PASS;
$db_name = DB_NAME;

$backup_dir = dirname(dirname(__DIR__)) . '/backups/';
$timestamp  = date('Y-m-d_His');

// Ensure backups directory exists
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0777, true);
}

// ── Determine backup type ────────────────────────────────────────────────────
// type: 'full' (default), 'schema', 'data'
$type = $_POST['type'] ?? 'full';
$type = in_array($type, ['full', 'schema', 'data']) ? $type : 'full';

// ── Build filename & mysqldump flags ─────────────────────────────────────────
if ($type === 'schema') {
    $filename      = 'raquel_hris_schema_' . $timestamp . '.sql';
    $extra_flags   = '--no-data';
    $label         = 'Schema Only';
} elseif ($type === 'data') {
    $filename      = 'raquel_hris_data_' . $timestamp . '.sql';
    $extra_flags   = '--no-create-info';
    $label         = 'Data Only';
} else {
    $filename      = 'raquel_hris_backup_' . $timestamp . '.sql';
    $extra_flags   = '';
    $label         = 'Full Backup';
}
$dest_path = $backup_dir . $filename;

// ── mysqldump path ───────────────────────────────────────────────────────────
$mysqldump_path = 'C:\xampp\mysql\bin\mysqldump.exe';
if (!file_exists($mysqldump_path)) {
    $mysqldump_path = 'mysqldump';
}

// ── Build command ────────────────────────────────────────────────────────────
$command = sprintf(
    '"%s" --user=%s --password=%s --host=%s %s %s > "%s"',
    $mysqldump_path,
    $db_user,
    $db_pass != '' ? $db_pass : "''",
    $db_host,
    $extra_flags,
    $db_name,
    $dest_path
);

// ── Try mysqldump ────────────────────────────────────────────────────────────
$return_var = -1;
$output = [];
try {
    @exec($command, $output, $return_var);
} catch (Exception $e) {
    $return_var = -1;
}

$backup_method = 'mysqldump';
$success = ($return_var === 0 && file_exists($dest_path) && filesize($dest_path) > 0);

// ── Fallback: PHP native dump ────────────────────────────────────────────────
if (!$success) {
    $backup_method = 'php_native';
    if ($type === 'schema') {
        $success = generate_php_schema($conn, $dest_path);
    } elseif ($type === 'data') {
        $success = generate_php_data($conn, $dest_path);
    } else {
        $success = generate_php_full($conn, $dest_path);
    }
}

// ── Respond ──────────────────────────────────────────────────────────────────
if ($success) {
    require_once '../../includes/functions.php';
    logAudit($conn, $_SESSION['user_id'], 'CREATE', 'Backup', 0,
        "Created {$label} backup ({$backup_method}): {$filename}");

    echo json_encode([
        'success'  => true,
        'filename' => $filename,
        'size'     => filesize($dest_path),
        'method'   => $backup_method,
        'type'     => $type,
        'label'    => $label,
    ]);
} else {
    if (file_exists($dest_path)) {
        @unlink($dest_path);
    }
    echo json_encode([
        'success' => false,
        'error'   => "Database {$label} backup failed. Ensure database settings are correct and the backups folder is writable.",
    ]);
}

// ── PHP Native Helpers ───────────────────────────────────────────────────────

/** Full backup: schema + data */
function generate_php_full($conn, $dest_path) {
    $sql  = php_backup_header('Full Backup (Structure + Data)');
    $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
    foreach (get_tables($conn) as $table) {
        $sql .= php_table_schema($conn, $table);
        $sql .= php_table_data($conn, $table);
    }
    $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
    return file_put_contents($dest_path, $sql) !== false;
}

/** Schema-only backup: CREATE TABLE statements, no data */
function generate_php_schema($conn, $dest_path) {
    $sql  = php_backup_header('Schema Only (Table Structures)');
    $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
    foreach (get_tables($conn) as $table) {
        $sql .= php_table_schema($conn, $table);
    }
    $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
    return file_put_contents($dest_path, $sql) !== false;
}

/** Data-only backup: INSERT INTO statements, no CREATE TABLE */
function generate_php_data($conn, $dest_path) {
    $sql  = php_backup_header('Data Only (Records / Values)');
    $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
    foreach (get_tables($conn) as $table) {
        $sql .= php_table_data($conn, $table);
    }
    $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
    return file_put_contents($dest_path, $sql) !== false;
}

/** Common header comment block */
function php_backup_header($label) {
    return "-- Raquel HRIS Database Backup ({$label})\n"
         . "-- Generated: " . date('Y-m-d H:i:s') . "\n"
         . "-- Database: " . DB_NAME . "\n\n";
}

/** Get all table names */
function get_tables($conn) {
    $tables = [];
    $res = $conn->query("SHOW TABLES");
    if (!$res) return $tables;
    while ($row = $res->fetch_row()) {
        $tables[] = $row[0];
    }
    return $tables;
}

/** Generate DROP + CREATE TABLE for one table */
function php_table_schema($conn, $table) {
    $sql  = "-- Table structure for `{$table}`\n";
    $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
    $res  = $conn->query("SHOW CREATE TABLE `{$table}`");
    if (!$res) return $sql . "\n";
    $row  = $res->fetch_row();
    $sql .= $row[1] . ";\n\n";
    return $sql;
}

/** Generate INSERT INTO rows for one table */
function php_table_data($conn, $table) {
    $sql = "-- Data for `{$table}`\n";
    $res = $conn->query("SELECT * FROM `{$table}`");
    if (!$res) return $sql . "\n";
    $num_fields = $res->field_count;
    $row_count  = 0;
    while ($row = $res->fetch_row()) {
        $sql .= "INSERT INTO `{$table}` VALUES(";
        $vals = [];
        for ($j = 0; $j < $num_fields; $j++) {
            $vals[] = isset($row[$j]) ? "'" . $conn->real_escape_string($row[$j]) . "'" : 'NULL';
        }
        $sql .= implode(',', $vals) . ");\n";
        $row_count++;
    }
    if ($row_count === 0) {
        $sql .= "-- (no records)\n";
    }
    return $sql . "\n";
}
?>
