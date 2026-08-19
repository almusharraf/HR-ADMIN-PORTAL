<?php
require_once __DIR__ . '/../../src/bootstrap.php';

if (!Auth::check()) {
    Response::error('Unauthorized', 401);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Method not allowed', 405);
}

$input = json_decode((string) file_get_contents('php://input'), true) ?: [];
$jobId = (int) ($input['job_id'] ?? 0);

if (!$jobId || !JobRepository::findById($jobId)) {
    Response::error('Job not found', 404);
}

try {
    $result = CandidateScorer::scoreJob($jobId);
    Response::json(['ok' => true] + $result);
} catch (Throwable $e) {
    Response::error($e->getMessage(), 500);
}
