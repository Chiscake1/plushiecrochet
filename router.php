<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Serve static files as-is
if (preg_match('/\.(?:png|jpg|jpeg|gif|webp|css|js|json|md|txt|ico|woff|woff2)$/', $path)) {
    return false;
}

$path = ltrim($path, '/');

if ($path === '' || $path === '/') {
    $path = 'index';
}

// Check if PHP file exists
if (file_exists(__DIR__ . '/' . $path . '.php')) {
    include __DIR__ . '/' . $path . '.php';
    exit;
}

// Check if HTML file exists
if (file_exists(__DIR__ . '/' . $path . '.html')) {
    include __DIR__ . '/' . $path . '.html';
    exit;
}

// Check if raw file exists (e.g. they requested the file with extension anyway)
if (file_exists(__DIR__ . '/' . $path) && is_file(__DIR__ . '/' . $path)) {
    return false;
}

// 404
http_response_code(404);
echo "404 Not Found";
exit;
