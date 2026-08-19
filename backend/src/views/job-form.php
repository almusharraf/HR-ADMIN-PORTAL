<?php
/** @var array|null $job */
/** @var string $formAction */
/** @var string|null $error */
$job = $job ?? [];
$val = static fn (string $key, string $default = '') => htmlspecialchars((string) ($job[$key] ?? $default));
?>
<?php if (!empty($error)): ?>
  <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<form method="post" action="<?= htmlspecialchars($formAction) ?>" class="form-grid">
  <label>Job title
    <input type="text" name="title" required value="<?= $val('title') ?>">
  </label>
  <label>Location
    <input type="text" name="location" required value="<?= $val('location') ?>">
  </label>
  <label>Department
    <input type="text" name="department" value="<?= $val('department') ?>">
  </label>
  <label>Employment type
    <select name="employment_type">
      <?php foreach (['Full-time', 'Part-time', 'Contract', 'Internship'] as $type): ?>
        <option value="<?= $type ?>" <?= ($job['employment_type'] ?? '') === $type ? 'selected' : '' ?>><?= $type ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <label>Status
    <select name="status">
      <?php foreach (['draft', 'open', 'closed'] as $status): ?>
        <option value="<?= $status ?>" <?= ($job['status'] ?? 'draft') === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <label class="span-2">Description
    <textarea name="description" rows="6" required><?= $val('description') ?></textarea>
  </label>
  <label class="span-2">Requirements
    <textarea name="requirements" rows="6"><?= $val('requirements') ?></textarea>
  </label>
  <div class="span-2 form-actions">
    <a href="/jobs/" class="btn btn-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary">Save job</button>
  </div>
</form>
