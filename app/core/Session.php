<?php

declare(strict_types=1);

require_once __DIR__ . '/Config.php';

final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) return;
        session_name(Config::sessionName());
        session_start();
    }

    public static function login(int $id, string $name, string $email): void
    {
        self::start();
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => $id, 'name' => $name, 'email' => $email];
    }

    public static function user(): ?array
    {
        self::start();
        return $_SESSION['user'] ?? null;
    }

    public static function userId(): int
    {
        return (int)(self::user()['id'] ?? 0);
    }

    public static function isAuthenticated(): bool
    {
        return self::user() !== null;
    }

    public static function requireAuth(): void
    {
        if (!self::isAuthenticated()) {
            header('Location: /hackathon/public/login.php');
            exit;
        }
    }

    public static function requireAuthApi(): void
    {
        if (!self::isAuthenticated()) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
            exit;
        }
    }

    public static function logout(): void
    {
        self::start();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], (bool)$p['secure'], (bool)$p['httponly']);
        }
        session_destroy();
    }
}
