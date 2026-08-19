<?php
require_once __DIR__ . '/../../src/bootstrap.php';
Auth::requireLogin();

$id = (int) ($_GET['id'] ?? 0);
$job = JobRepository::findById($id);
if (!$job) {
    http_response_code(404);
    echo 'Job not found';
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim((string) ($_POST['title'] ?? ''));
    $location = trim((string) ($_POST['location'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));

    if ($title === '' || $location === '' || $description === '') {
        $error = 'Title, location, and description are required.';
        $job = array_merge($job, $_POST);
    } else {
        JobRepository::update($id, [
            'title' => $title,
            'location' => $location,
            'department' => trim((string) ($_POST['department'] ?? '')),
            'employment_type' => trim((string) ($_POST['employment_type'] ?? '')),
            'description' => $description,
            'requirements' => trim((string) ($_POST['requirements'] ?? '')),
            'status' => in_array($_POST['status'] ?? '', ['draft', 'open', 'closed'], true) ? $_POST['status'] : 'draft',
        ]);
        header('Location: /jobs/');
        exit;
    }
}

$pageTitle = 'Edit job';
$activeNav = 'jobs';
require __DIR__ . '/../../src/views/header.php';
?>
<div class="page-header">
  <h1>Edit job</h1>
  <p class="muted">Job link: <code>/careers/apply/<?= htmlspecialchars($job['job_uuid']) ?>/</code> on the careers site.</p>
</div>
<div class="panel">
  <?php $formAction = '/jobs/edit.php?id=' . $id; require __DIR__ . '/../../src/views/job-form.php'; ?>
</div>
<?php require __DIR__ . '/../../src/views/footer.php'; ?>
