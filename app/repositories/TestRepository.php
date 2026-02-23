<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Database.php';

final class TestRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function create(int $userId, string $topic, array $questions): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO tests (user_id, topic, questions, total_questions)
             VALUES (:uid, :topic, :questions, :total)'
        );
        $stmt->execute([
            'uid'       => $userId,
            'topic'     => $topic,
            'questions' => json_encode($questions, JSON_UNESCAPED_UNICODE),
            'total'     => count($questions),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM tests WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (!$row) return null;
        $row['questions'] = json_decode($row['questions'], true);
        return $row;
    }

    public function listByUser(int $userId, int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, topic, total_questions, created_at FROM tests WHERE user_id = :uid ORDER BY created_at DESC LIMIT :lim'
        );
        $stmt->bindValue('uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue('lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
