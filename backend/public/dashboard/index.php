<?php
require_once __DIR__ . '/../../src/bootstrap.php';
Auth::requireLogin();

$jobs = JobRepository::findAll();
$counts = ApplicationRepository::counts();

$totalOpen = 0;
$totalApplications = 0;
$totalNew = 0;
$totalUnscored = 0;
foreach ($jobs as $job) {
    if ($job['status'] === 'open') {
        $totalOpen++;
    }
    $c = $counts[(int) $job['id']] ?? null;
    $totalApplications += (int) ($c['total'] ?? 0);
    $totalNew += (int) ($c['new_count'] ?? 0);
    $totalUnscored += (int) ($c['unscored'] ?? 0);
}

$pageTitle = 'Dashboard';
$activeNav = 'dashboard';
require __DIR__ . '/../../src/views/header.php';
?>
<div class="page-header">
  <h1>Dashboard</h1>
  <p class="muted">Overview of open roles and candidate pipeline.</p>
</div>

<div class="stat-grid">
  <div class="stat-card">
    <div class="stat-value"><?= $totalOpen ?></div>
    <div class="stat-label">Open roles</div>
  </div>
  <div class="stat-card">
    <div class="stat-value"><?= $totalApplications ?></div>
    <div class="stat-label">Total applications</div>
  </div>
  <div class="stat-card">
    <div class="stat-value"><?= $totalNew ?></div>
    <div class="stat-label">Awaiting review</div>
  </div>
  <div class="stat-card">
    <div class="stat-value"><?= $totalUnscored ?></div>
    <div class="stat-label">Not yet AI-scored</div>
  </div>
</div>

<div class="panel">
  <div class="panel-header">
    <h2>Recent roles</h2>
    <a href="/jobs/" class="btn btn-secondary btn-sm">View all jobs</a>
  </div>
  <table class="data-table">
    <thead>
      <tr><th>Title</th><th>Location</th><th>Status</th><th>Applications</th><th>Unscored</th><th></th></tr>
    </thead>
    <tbody>
      <?php foreach (array_slice($jobs, 0, 8) as $job): $c = $counts[(int) $job['id']] ?? []; ?>
      <tr>
        <td><?= htmlspecialchars($job['title']) ?></td>
        <td><?= htmlspecialchars($job['location']) ?></td>
        <td><span class="badge badge-<?= htmlspecialchars($job['status']) ?>"><?= htmlspecialchars($job['status']) ?></span></td>
        <td><?= (int) ($c['total'] ?? 0) ?></td>
        <td><?= (int) ($c['unscored'] ?? 0) ?></td>
        <td><a href="/applications/?job=<?= (int) $job['id'] ?>">Review →</a></td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$jobs): ?>
      <tr><td colspan="6" class="empty">No jobs yet. <a href="/jobs/create.php">Post the first role</a>.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/../../src/views/footer.php'; ?>
