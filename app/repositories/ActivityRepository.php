<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Database.php';

final class ActivityRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function save(int $userId, string $module, string $topic, string $prompt, string $response): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO activity_history (user_id, module, topic, prompt, response)
             VALUES (:uid, :module, :topic, :prompt, :response)'
        );
        $stmt->execute([
            'uid'      => $userId,
            'module'   => $module,
            'topic'    => $topic,
            'prompt'   => $prompt,
            'response' => $response,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function listByUser(int $userId, int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM activity_history WHERE user_id = :uid ORDER BY created_at DESC LIMIT :lim'
        );
        $stmt->bindValue('uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue('lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countByUser(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM activity_history WHERE user_id = :uid');
        $stmt->execute(['uid' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    public function countByModule(int $userId, string $module): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM activity_history WHERE user_id = :uid AND module = :mod'
        );
        $stmt->execute(['uid' => $userId, 'mod' => $module]);
        return (int) $stmt->fetchColumn();
    }
}
