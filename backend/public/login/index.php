<?php
require_once __DIR__ . '/../../src/bootstrap.php';

if (Auth::check()) {
    header('Location: /dashboard/');
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($email !== '' && $password !== '' && Auth::attempt($email, $password)) {
        header('Location: /dashboard/');
        exit;
    }

    $error = 'Invalid email or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign in · Petrogistix HR</title>
<link rel="stylesheet" href="/assets/admin.css">
</head>
<body class="login-page">
<div class="login-card">
  <div class="brand">
    <span class="brand-mark">PGX</span>
    <span class="brand-name">HR Portal</span>
  </div>
  <h1>Sign in</h1>
  <p class="muted">Authorized HR personnel only.</p>
  <?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="post" class="login-form">
    <label>Email
      <input type="email" name="email" required autofocus>
    </label>
    <label>Password
      <input type="password" name="password" required>
    </label>
    <button type="submit" class="btn btn-primary btn-block">Sign in</button>
  </form>
</div>
</body>
</html>
