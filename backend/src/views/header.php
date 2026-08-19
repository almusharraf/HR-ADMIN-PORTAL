<?php
/** @var string $pageTitle */
/** @var string $activeNav */
$user = Auth::user();
$activeNav = $activeNav ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'Petrogistix HR') ?> · Petrogistix HR</title>
<link rel="stylesheet" href="/assets/admin.css">
</head>
<body>
<div class="app-shell">
  <aside class="sidebar">
    <div class="brand">
      <span class="brand-mark">PGX</span>
      <span class="brand-name">HR Portal</span>
    </div>
    <nav class="nav">
      <a href="/dashboard/" class="<?= $activeNav === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
      <a href="/jobs/" class="<?= $activeNav === 'jobs' ? 'active' : '' ?>">Jobs</a>
    </nav>
    <div class="sidebar-footer">
      <?php if ($user): ?>
        <div class="user-chip">
          <div class="user-name"><?= htmlspecialchars($user['name']) ?></div>
          <div class="user-role"><?= htmlspecialchars($user['role']) ?></div>
        </div>
        <a href="/logout.php" class="logout-link">Sign out</a>
      <?php endif; ?>
    </div>
  </aside>
  <main class="main">
