<?php
require_once __DIR__ . '/../src/bootstrap.php';

header('Location: ' . (Auth::check() ? '/dashboard/' : '/login/'));
