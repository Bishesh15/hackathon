<?php

declare(strict_types=1);

final class Config
{
    private static array $env = [];

    private static function env(string $key, string $default = ''): string
    {
        if (empty(self::$env)) {
            $file = __DIR__ . '/../../.env';
            if (file_exists($file)) {
                $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line === '' || $line[0] === '#') continue;
                    if (str_contains($line, '=')) {
                        [$k, $v] = explode('=', $line, 2);
                        self::$env[trim($k)] = trim($v);
                    }
                }
            }
        }
        return self::$env[$key] ?? $default;
    }

    // ── Database ────────────────────────────────────────
    public static function dbHost(): string { return self::env('DB_HOST', '127.0.0.1'); }
    public static function dbPort(): string { return self::env('DB_PORT', '3306'); }
    public static function dbName(): string { return self::env('DB_NAME', 'hackathon_db'); }
    public static function dbUser(): string { return self::env('DB_USER', 'root'); }
    public static function dbPass(): string { return self::env('DB_PASS', ''); }

    public static function dsn(): string
    {
        return 'mysql:host=' . self::dbHost() .
            ';port=' . self::dbPort() .
            ';dbname=' . self::dbName() .
            ';charset=utf8mb4';
    }

    // ── AI API (Groq – free tier) ───────────────────────────
    public static function aiApiUrl(): string  { return self::env('AI_API_URL', 'https://api.groq.com/openai/v1/chat/completions'); }
    public static function aiApiKey(): string  { return self::env('AI_API_KEY'); }
    public static function aiModel(): string   { return self::env('AI_MODEL', 'llama-3.3-70b-versatile'); }

    // ── Google OAuth ────────────────────────────────────
    public static function googleClientId(): string     { return self::env('GOOGLE_CLIENT_ID'); }
    public static function googleClientSecret(): string { return self::env('GOOGLE_CLIENT_SECRET'); }
    public static function googleRedirectUri(): string  { return self::env('GOOGLE_REDIRECT_URI', 'http://localhost/hackathon/api/auth/google-callback.php'); }

    // ── App ────────────────────────────────────────────
    public static function appName(): string    { return 'Hackathon Learning App'; }
    public static function sessionName(): string { return 'hackathon_sid'; }
}