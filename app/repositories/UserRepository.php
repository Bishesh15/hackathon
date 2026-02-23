<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Database.php';

final class UserRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(string $name, string $email, string $passwordHash): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (name, email, password_hash, auth_provider) VALUES (:name, :email, :password_hash, "local")'
        );
        $stmt->execute([
            'name'          => $name,
            'email'         => $email,
            'password_hash' => $passwordHash,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function findByGoogleId(string $googleId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE google_id = :gid LIMIT 1');
        $stmt->execute(['gid' => $googleId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function createFromGoogle(string $name, string $email, string $googleId, string $avatar): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (name, email, google_id, avatar, auth_provider)
             VALUES (:name, :email, :google_id, :avatar, "google")'
        );
        $stmt->execute([
            'name'      => $name,
            'email'     => $email,
            'google_id' => $googleId,
            'avatar'    => $avatar,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function linkGoogle(int $userId, string $googleId, string $avatar): void
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET google_id = :gid, avatar = :avatar, auth_provider = "google" WHERE id = :id'
        );
        $stmt->execute(['gid' => $googleId, 'avatar' => $avatar, 'id' => $userId]);
    }
}
