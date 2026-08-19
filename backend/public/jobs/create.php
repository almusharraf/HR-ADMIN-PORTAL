<?php
require_once __DIR__ . '/../../src/bootstrap.php';
Auth::requireLogin();

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim((string) ($_POST['title'] ?? ''));
    $location = trim((string) ($_POST['location'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));

    if ($title === '' || $location === '' || $description === '') {
        $error = 'Title, location, and description are required.';
    } else {
        $id = JobRepository::create([
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

$pageTitle = 'Post a job';
$activeNav = 'jobs';
require __DIR__ . '/../../src/views/header.php';
?>
<div class="page-header">
  <h1>Post a job</h1>
  <p class="muted">This will appear on the careers page once its status is set to Open.</p>
</div>
<div class="panel">
  <?php $formAction = '/jobs/create.php'; require __DIR__ . '/../../src/views/job-form.php'; ?>
</div>
<?php require __DIR__ . '/../../src/views/footer.php'; ?>
