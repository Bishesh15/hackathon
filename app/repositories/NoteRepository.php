<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Database.php';

final class NoteRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function create(int $userId, string $title, string $content): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO tutor_notes (user_id, title, content) VALUES (?, ?, ?)'
        );
        $stmt->execute([$userId, $title, $content]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, int $userId, string $title, string $content): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE tutor_notes SET title = ?, content = ? WHERE id = ? AND user_id = ?'
        );
        return $stmt->execute([$title, $content, $id, $userId]);
    }

    public function delete(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM tutor_notes WHERE id = ? AND user_id = ?'
        );
        return $stmt->execute([$id, $userId]);
    }

    public function findByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, title, content, created_at, updated_at FROM tutor_notes WHERE user_id = ? ORDER BY updated_at DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function findById(int $id, int $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, title, content, created_at, updated_at FROM tutor_notes WHERE id = ? AND user_id = ?'
        );
        $stmt->execute([$id, $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
