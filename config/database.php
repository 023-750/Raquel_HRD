<?php
if (!ob_get_level()) {
    ob_start();
}
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'raquel_hris');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_SSL', getenv('DB_SSL') === 'true' || getenv('DB_SSL') === '1');

// Raquel Pawnshop operates on Philippine time. All scheduled tasks use this clock.
date_default_timezone_set('Asia/Manila');

$is_https = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || 
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
$protocol = $is_https ? 'https://' : 'http://';
define('BASE_URL', isset($_SERVER['HTTP_HOST']) ? $protocol . $_SERVER['HTTP_HOST'] : '');

$is_remote = (DB_HOST !== 'localhost' && DB_HOST !== '127.0.0.1');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = mysqli_init();
if ($is_remote || DB_SSL) {
    // TiDB Cloud / Remote Cloud MySQL requires SSL connection
    $conn->ssl_set(NULL, NULL, NULL, NULL, NULL);
    $conn->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, true);
    $conn->real_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, (int)DB_PORT, NULL, MYSQLI_CLIENT_SSL);
} else {
    $conn->real_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, (int)DB_PORT);
}

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
