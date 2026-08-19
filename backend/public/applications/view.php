<?php
require_once __DIR__ . '/../../src/bootstrap.php';
Auth::requireLogin();

$id = (int) ($_GET['id'] ?? 0);
$application = ApplicationRepository::findById($id);
if (!$application) {
    http_response_code(404);
    echo 'Application not found';
    exit;
}
$job = JobRepository::findById((int) $application['job_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = (string) ($_POST['review_status'] ?? '');
    if (in_array($status, ['new', 'shortlisted', 'rejected', 'hired'], true)) {
        ApplicationRepository::setReviewStatus($id, $status);
        header('Location: /applications/view.php?id=' . $id);
        exit;
    }
}

$experience = json_decode((string) ($application['experience_json'] ?? ''), true) ?: [];
$education = json_decode((string) ($application['education_json'] ?? ''), true) ?: [];

$pageTitle = $application['full_name'];
$activeNav = 'jobs';
require __DIR__ . '/../../src/views/header.php';
?>
<div class="page-header">
  <h1><?= htmlspecialchars($application['full_name']) ?></h1>
  <p class="muted">Applying for <a href="/applications/?job=<?= (int) $job['id'] ?>"><?= htmlspecialchars($job['title']) ?></a></p>
</div>

<div class="detail-grid">
  <div class="panel">
    <div class="panel-header"><h2>AI fit assessment</h2></div>
    <?php if ($application['ai_score'] !== null): ?>
      <div class="score-pill score-<?= $application['ai_score'] >= 75 ? 'high' : ($application['ai_score'] >= 50 ? 'mid' : 'low') ?> score-pill-lg"><?= (int) $application['ai_score'] ?>/100</div>
      <p><?= nl2br(htmlspecialchars($application['ai_rationale'])) ?></p>
      <p class="muted small">Scored <?= htmlspecialchars($application['scored_at']) ?></p>
    <?php else: ?>
      <p class="muted">Not yet scored. Run scoring from the applicants list.</p>
    <?php endif; ?>

    <form method="post" class="review-status-form">
      <label>Review status
        <select name="review_status" onchange="this.form.submit()">
          <?php foreach (['new', 'shortlisted', 'rejected', 'hired'] as $s): ?>
            <option value="<?= $s ?>" <?= $application['review_status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
    </form>
  </div>

  <div class="panel">
    <div class="panel-header"><h2>Contact</h2></div>
    <dl class="detail-list">
      <dt>Email</dt><dd><?= htmlspecialchars($application['email']) ?></dd>
      <dt>Phone</dt><dd><?= htmlspecialchars($application['phone'] ?? '—') ?></dd>
      <dt>Location</dt><dd><?= htmlspecialchars($application['city'] ?? $application['current_location'] ?? '—') ?></dd>
      <dt>Nationality</dt><dd><?= htmlspecialchars($application['nationality'] ?? '—') ?></dd>
      <dt>Willing to relocate</dt><dd><?= $application['willing_to_relocate'] ? 'Yes' : 'No' ?></dd>
      <dt>LinkedIn</dt><dd><?= $application['linkedin_url'] ? '<a href="' . htmlspecialchars($application['linkedin_url']) . '" target="_blank" rel="noopener">' . htmlspecialchars($application['linkedin_url']) . '</a>' : '—' ?></dd>
      <?php if ($application['cv_path']): ?>
      <dt>CV</dt><dd><a href="/api/download-cv.php?id=<?= $id ?>">Download</a></dd>
      <?php endif; ?>
    </dl>
  </div>

  <div class="panel span-2">
    <div class="panel-header"><h2>Education &amp; Status</h2></div>
    <dl class="detail-list">
      <dt>Status</dt><dd><?= htmlspecialchars($application['status'] ?? '—') ?></dd>
      <dt>Education level</dt><dd><?= htmlspecialchars($application['education_level'] ?? '—') ?></dd>
      <dt>Institution</dt><dd><?= htmlspecialchars($application['institution'] ?? '—') ?></dd>
      <dt>Specialization</dt><dd><?= htmlspecialchars($application['specialization'] ?? '—') ?></dd>
      <?php if ($application['job_interest']): ?>
      <dt>Job interest</dt><dd><?= htmlspecialchars($application['job_interest']) ?></dd>
      <?php endif; ?>
    </dl>
  </div>

  <?php if ($experience): ?>
  <div class="panel span-2">
    <div class="panel-header"><h2>Experience</h2></div>
    <ul class="list-plain">
      <?php foreach ($experience as $e): ?>
        <li><strong><?= htmlspecialchars($e['title'] ?? '') ?></strong> — <?= htmlspecialchars($e['company'] ?? '') ?> <span class="muted small"><?= htmlspecialchars($e['dates'] ?? '') ?></span></li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../../src/views/footer.php'; ?>
