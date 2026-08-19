<?php
require_once __DIR__ . '/../../src/bootstrap.php';

Auth::requireLogin();

$id = (int) ($_GET['id'] ?? 0);
$application = ApplicationRepository::findById($id);
if (!$application || !$application['cv_path']) {
    http_response_code(404);
    echo 'Not found';
    exit;
}

$uploadsDir = config('CAREERS_UPLOADS_PATH', __DIR__ . '/../../storage/uploads');
$path = realpath($uploadsDir . '/' . $application['cv_path']);
$uploadsRoot = realpath($uploadsDir);

if (!$path || !$uploadsRoot || !str_starts_with($path, $uploadsRoot) || !is_file($path)) {
    http_response_code(404);
    echo 'Not found';
    exit;
}

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($path) . '"');
readfile($path);
