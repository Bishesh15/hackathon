<?php

declare(strict_types=1);

final class ValidationService
{
    public static function requireFields(array $input, array $fields): void
    {
        $missing = [];
        foreach ($fields as $f) {
            if (!isset($input[$f]) || (is_string($input[$f]) && trim($input[$f]) === '')) {
                $missing[] = $f;
            }
        }
        if (!empty($missing)) {
            require_once __DIR__ . '/../core/Helpers.php';
            json_error('Missing required fields: ' . implode(', ', $missing), 422);
        }
    }

    public static function requireEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            require_once __DIR__ . '/../core/Helpers.php';
            json_error('Invalid email address.', 422);
        }
    }

    public static function requireMinLength(string $value, int $min, string $fieldName): void
    {
        if (strlen($value) < $min) {
            require_once __DIR__ . '/../core/Helpers.php';
            json_error("$fieldName must be at least $min characters.", 422);
        }
    }
}
