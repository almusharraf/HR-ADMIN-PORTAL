<?php
declare(strict_types=1);

final class Auth
{
    public static function attempt(string $email, string $password): ?array
    {
        $stmt = db()->prepare('SELECT * FROM admin_users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return null;
        }

        unset($user['password_hash']);
        $_SESSION['user'] = $user;

        return $user;
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function requireLogin(string $basePath = ''): void
    {
        if (!self::check()) {
            header('Location: ' . $basePath . '/login/');
            exit;
        }
    }

    public static function requireRole(string $role): void
    {
        $user = self::user();
        if (!$user || $user['role'] !== $role) {
            http_response_code(403);
            echo 'Forbidden';
            exit;
        }
    }
}
