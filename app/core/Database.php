<?php

declare(strict_types=1);

require_once __DIR__ . '/Config.php';

final class Database
{
    private static ?PDO $conn = null;

    public static function connection(): PDO
    {
        if (self::$conn === null) {
            self::$conn = new PDO(
                Config::dsn(),
                Config::dbUser(),
                Config::dbPass(),
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        }

        return self::$conn;
    }
}