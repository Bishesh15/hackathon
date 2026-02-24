<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Database.php';

final class PlanRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function save(int $userId, string $type, string $topic, array $content, string $source, ?int $attemptId = null, ?int $examId = null): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO saved_plans (user_id, type, topic, content, source, attempt_id, exam_id) VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $userId,
            $type,
            $topic,
            json_encode($content, JSON_UNESCAPED_UNICODE),
            $source,
            $attemptId,
            $examId
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function findByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, type, topic, content, source, attempt_id, exam_id, created_at FROM saved_plans WHERE user_id = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$row) {
            $row['content'] = json_decode($row['content'], true);
        }
        return $rows;
    }

    public function delete(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM saved_plans WHERE id = ? AND user_id = ?'
        );
        return $stmt->execute([$id, $userId]);
    }
}
