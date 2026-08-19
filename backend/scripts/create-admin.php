<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

[, $email, $name, $role] = array_pad($argv, 4, null);

if (!$email || !$name) {
    fwrite(STDERR, "Usage: php create-admin.php <email> <name> [admin|recruiter]\n");
    exit(1);
}

$role = in_array($role, ['admin', 'recruiter'], true) ? $role : 'recruiter';

fwrite(STDOUT, 'Password: ');
system('stty -echo');
$password = trim((string) fgets(STDIN));
system('stty echo');
fwrite(STDOUT, "\n");

if (strlen($password) < 8) {
    fwrite(STDERR, "Password must be at least 8 characters.\n");
    exit(1);
}

$hash = password_hash($password, PASSWORD_BCRYPT);

$stmt = db()->prepare(
    'INSERT INTO admin_users (email, name, password_hash, role) VALUES (?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE name = VALUES(name), password_hash = VALUES(password_hash), role = VALUES(role)'
);
$stmt->execute([$email, $name, $hash, $role]);

echo "Admin user '{$email}' saved with role '{$role}'.\n";
