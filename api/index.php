<?php
// Router for Vercel serverless deployment
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Strip leading slash
$uri = ltrim($uri, '/');

// Default to index.php at root
if (empty($uri) || $uri === '/') {
    $uri = 'index.php';
}

$base = __DIR__ . '/..';
$file = $base . '/' . $uri;

// Serve static assets with correct MIME types
if (file_exists($file) && !is_dir($file)) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    $mimes = [
        'css'   => 'text/css; charset=UTF-8',
        'js'    => 'application/javascript; charset=UTF-8',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'svg'   => 'image/svg+xml',
        'webp'  => 'image/webp',
        'ico'   => 'image/x-icon',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
        'eot'   => 'application/vnd.ms-fontobject',
        'json'  => 'application/json',
        'mp3'   => 'audio/mpeg',
        'wav'   => 'audio/wav',
    ];

    if (isset($mimes[$ext])) {
        header("Content-Type: " . $mimes[$ext]);
        header("Cache-Control: public, max-age=3600");
        readfile($file);
        exit;
    }

    if ($ext === 'php') {
        // Change working directory to the file's directory so relative paths work
        chdir(dirname($file));
        require $file;
        exit;
    }
}

// Default: serve root index.php
chdir($base);
require $base . '/index.php';
?>
