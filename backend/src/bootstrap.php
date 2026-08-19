<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/JobRepository.php';
require_once __DIR__ . '/ApplicationRepository.php';
require_once __DIR__ . '/AnthropicClient.php';
require_once __DIR__ . '/CandidateScorer.php';
require_once __DIR__ . '/Response.php';

session_name(config('SESSION_NAME', 'pgx_hr_session'));
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();
