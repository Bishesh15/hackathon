<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Database.php';

final class AttemptRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function create(int $testId, int $userId, array $answers, int $score, int $total, float $percentage): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO attempts (test_id, user_id, answers, score, total, percentage)
             VALUES (:tid, :uid, :answers, :score, :total, :pct)'
        );
        $stmt->execute([
            'tid'     => $testId,
            'uid'     => $userId,
            'answers' => json_encode($answers, JSON_UNESCAPED_UNICODE),
            'score'   => $score,
            'total'   => $total,
            'pct'     => $percentage,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM attempts WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (!$row) return null;
        $row['answers']    = json_decode($row['answers'], true);
        $row['analysis']   = $row['analysis'] ? json_decode($row['analysis'], true) : null;
        $row['study_plan'] = $row['study_plan'] ? json_decode($row['study_plan'], true) : null;
        return $row;
    }

    public function saveAnalysis(int $attemptId, array $analysis): void
    {
        $stmt = $this->db->prepare('UPDATE attempts SET analysis = :a WHERE id = :id');
        $stmt->execute(['a' => json_encode($analysis, JSON_UNESCAPED_UNICODE), 'id' => $attemptId]);
    }

    public function savePlan(int $attemptId, array $plan): void
    {
        $stmt = $this->db->prepare('UPDATE attempts SET study_plan = :p WHERE id = :id');
        $stmt->execute(['p' => json_encode($plan, JSON_UNESCAPED_UNICODE), 'id' => $attemptId]);
    }

    public function listByUser(int $userId, int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            'SELECT a.id, a.test_id, t.topic, a.score, a.total, a.percentage, a.created_at
             FROM attempts a
             JOIN tests t ON t.id = a.test_id
             WHERE a.user_id = :uid
             ORDER BY a.created_at DESC
             LIMIT :lim'
        );
        $stmt->bindValue('uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue('lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function avgScore(int $userId): float
    {
        $stmt = $this->db->prepare('SELECT AVG(percentage) FROM attempts WHERE user_id = :uid');
        $stmt->execute(['uid' => $userId]);
        return round((float) $stmt->fetchColumn(), 1);
    }

    public function totalAttempts(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM attempts WHERE user_id = :uid');
        $stmt->execute(['uid' => $userId]);
        return (int) $stmt->fetchColumn();
    }
}
