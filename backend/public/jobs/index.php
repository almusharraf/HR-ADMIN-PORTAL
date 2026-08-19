<?php
require_once __DIR__ . '/../../src/bootstrap.php';
Auth::requireLogin();

$jobs = JobRepository::findAll();
$counts = ApplicationRepository::counts();

$pageTitle = 'Jobs';
$activeNav = 'jobs';
require __DIR__ . '/../../src/views/header.php';
?>
<div class="page-header">
  <h1>Jobs</h1>
  <p class="muted">Post, edit, and manage open roles.</p>
  <a href="/jobs/create.php" class="btn btn-primary">+ Post a job</a>
</div>

<div class="panel">
  <table class="data-table">
    <thead>
      <tr><th>Title</th><th>Location</th><th>Department</th><th>Status</th><th>Applications</th><th></th></tr>
    </thead>
    <tbody>
      <?php foreach ($jobs as $job): $c = $counts[(int) $job['id']] ?? []; ?>
      <tr>
        <td><?= htmlspecialchars($job['title']) ?></td>
        <td><?= htmlspecialchars($job['location']) ?></td>
        <td><?= htmlspecialchars($job['department'] ?? '—') ?></td>
        <td><span class="badge badge-<?= htmlspecialchars($job['status']) ?>"><?= htmlspecialchars($job['status']) ?></span></td>
        <td><?= (int) ($c['total'] ?? 0) ?></td>
        <td class="row-actions">
          <a href="/applications/?job=<?= (int) $job['id'] ?>">Applicants</a>
          <a href="/jobs/edit.php?id=<?= (int) $job['id'] ?>">Edit</a>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$jobs): ?>
      <tr><td colspan="6" class="empty">No jobs yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/../../src/views/footer.php'; ?>
