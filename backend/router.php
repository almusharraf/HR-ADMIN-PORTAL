<?php
// Dev-only router for `php -S localhost:8080 -t public router.php`.
// Mirrors what Apache's DirectoryIndex + .htaccess do automatically:
// requests for a directory are served by that directory's index.php,
// and static files under public/ are served as-is.

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$publicRoot = __DIR__ . '/public';
$path = $publicRoot . $uri;

if ($uri !== '/' && is_file($path)) {
    return false; // let the built-in server handle static assets directly
}

if (is_dir($path)) {
    $index = rtrim($path, '/') . '/index.php';
    if (is_file($index)) {
        require $index;
        return true;
    }
}

require $publicRoot . '/index.php';
return true;
