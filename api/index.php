<?php
// Router for Vercel serverless deployment
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . '/..' . $uri;

if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    require $file;
} else {
    require __DIR__ . '/../index.php';
}
?>
