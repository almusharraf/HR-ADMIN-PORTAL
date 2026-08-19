<?php
require_once __DIR__ . '/../../src/bootstrap.php';
Auth::requireLogin();

$jobId = (int) ($_GET['job'] ?? 0);
$job = $jobId ? JobRepository::findById($jobId) : null;
if (!$job) {
    http_response_code(404);
    echo 'Job not found';
    exit;
}

$sort = (string) ($_GET['sort'] ?? 'score_desc');
$reviewStatus = (string) ($_GET['review_status'] ?? '');
$view = (string) ($_GET['view'] ?? 'all'); // all | top10

$applications = $view === 'top10'
    ? ApplicationRepository::topByJob($jobId, 10)
    : ApplicationRepository::findByJob($jobId, $sort, $reviewStatus);

$pageTitle = $job['title'] . ' — Applicants';
$activeNav = 'jobs';
require __DIR__ . '/../../src/views/header.php';
?>
<div class="page-header">
  <h1><?= htmlspecialchars($job['title']) ?></h1>
  <p class="muted"><?= htmlspecialchars($job['location']) ?> · <?= count($applications) ?> shown</p>
  <div class="page-header-actions">
    <button id="score-job-btn" class="btn btn-primary" data-job-id="<?= $jobId ?>">Run AI scoring</button>
    <a href="/jobs/edit.php?id=<?= $jobId ?>" class="btn btn-secondary">Edit job</a>
  </div>
</div>

<div class="tabs">
  <a href="?job=<?= $jobId ?>&view=all" class="tab <?= $view === 'all' ? 'active' : '' ?>">All applicants</a>
  <a href="?job=<?= $jobId ?>&view=top10" class="tab <?= $view === 'top10' ? 'active' : '' ?>">Top 10 (AI calibrated)</a>
</div>

<?php if ($view === 'all'): ?>
<div class="filter-bar">
  <form method="get" class="filter-form">
    <input type="hidden" name="job" value="<?= $jobId ?>">
    <select name="sort" onchange="this.form.submit()">
      <option value="score_desc" <?= $sort === 'score_desc' ? 'selected' : '' ?>>Highest AI score</option>
      <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
      <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Oldest</option>
      <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Name</option>
    </select>
    <select name="review_status" onchange="this.form.submit()">
      <option value="">All statuses</option>
      <?php foreach (['new', 'shortlisted', 'rejected', 'hired'] as $s): ?>
        <option value="<?= $s ?>" <?= $reviewStatus === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
      <?php endforeach; ?>
    </select>
  </form>
</div>
<?php endif; ?>

<div id="score-status" class="alert" style="display:none;"></div>

<div class="panel">
  <table class="data-table" id="applications-table">
    <thead>
      <tr>
        <th>Candidate</th><th>Status</th><th>AI Score</th><th>Rationale</th><th>Review</th><th></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($applications as $a): ?>
      <tr>
        <td>
          <div><?= htmlspecialchars($a['full_name']) ?></div>
          <div class="muted small"><?= htmlspecialchars($a['email']) ?></div>
        </td>
        <td><?= htmlspecialchars($a['status'] ?? '—') ?></td>
        <td>
          <?php if ($a['ai_score'] !== null): ?>
            <span class="score-pill score-<?= $a['ai_score'] >= 75 ? 'high' : ($a['ai_score'] >= 50 ? 'mid' : 'low') ?>"><?= (int) $a['ai_score'] ?></span>
          <?php else: ?>
            <span class="muted small">not scored</span>
          <?php endif; ?>
        </td>
        <td class="rationale-cell"><?= htmlspecialchars($a['ai_rationale'] ?? '') ?></td>
        <td><span class="badge badge-<?= htmlspecialchars($a['review_status']) ?>"><?= htmlspecialchars($a['review_status']) ?></span></td>
        <td><a href="/applications/view.php?id=<?= (int) $a['id'] ?>">View →</a></td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$applications): ?>
      <tr><td colspan="6" class="empty">No applicants yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/../../src/views/footer.php'; ?>
