<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Database.php';

final class ConversationRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /** Create a new conversation, return its ID. */
    public function create(int $userId, string $module, string $title): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO conversations (user_id, module, title) VALUES (:uid, :module, :title)'
        );
        $stmt->execute(['uid' => $userId, 'module' => $module, 'title' => $title]);
        return (int) $this->db->lastInsertId();
    }

    /** Find a conversation by ID. */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM conversations WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** List conversations for a user, newest first. */
    public function listByUser(int $userId, int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            'SELECT c.*, (SELECT COUNT(*) FROM messages m WHERE m.conversation_id = c.id) as message_count
             FROM conversations c
             WHERE c.user_id = :uid
             ORDER BY c.updated_at DESC
             LIMIT :lim'
        );
        $stmt->bindValue('uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue('lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Add a message to a conversation. */
    public function addMessage(int $conversationId, string $role, string $content): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO messages (conversation_id, role, content) VALUES (:cid, :role, :content)'
        );
        $stmt->execute(['cid' => $conversationId, 'role' => $role, 'content' => $content]);

        // Touch updated_at on conversation
        $this->db->prepare('UPDATE conversations SET updated_at = NOW() WHERE id = :id')
                 ->execute(['id' => $conversationId]);

        return (int) $this->db->lastInsertId();
    }

    /** Get all messages for a conversation, ordered chronologically. */
    public function getMessages(int $conversationId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM messages WHERE conversation_id = :cid ORDER BY created_at ASC'
        );
        $stmt->execute(['cid' => $conversationId]);
        return $stmt->fetchAll();
    }

    /** Count conversations for a user. */
    public function countByUser(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM conversations WHERE user_id = :uid');
        $stmt->execute(['uid' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    /** Delete a conversation (cascade deletes messages too). */
    public function delete(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM conversations WHERE id = :id AND user_id = :uid');
        $stmt->execute(['id' => $id, 'uid' => $userId]);
        return $stmt->rowCount() > 0;
    }
}
