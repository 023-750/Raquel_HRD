<?php
// Router for Vercel serverless deployment
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . '/..' . $uri;

if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    
    // Static assets MIME types
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
        'json'  => 'application/json'
    ];
    
    if (isset($mimes[$ext])) {
        header("Content-Type: " . $mimes[$ext]);
        header("Cache-Control: public, max-age=3600");
        readfile($file);
        exit;
    }
    
    require $file;
} else {
    require __DIR__ . '/../index.php';
}
?>
