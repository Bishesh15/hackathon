<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Database.php';

final class ExamRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /** Create an exam (long-answer test). */
    public function create(int $userId, string $topic, array $questions): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO exams (user_id, topic, questions) VALUES (:uid, :topic, :questions)'
        );
        $stmt->execute([
            'uid'       => $userId,
            'topic'     => $topic,
            'questions' => json_encode($questions, JSON_UNESCAPED_UNICODE),
        ]);
        return (int) $this->db->lastInsertId();
    }

    /** Find exam by ID. */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM exams WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (!$row) return null;
        $row['questions']  = json_decode($row['questions'], true);
        $row['answers']    = $row['answers'] ? json_decode($row['answers'], true) : null;
        $row['feedback']   = $row['feedback'] ? json_decode($row['feedback'], true) : null;
        $row['analysis']   = !empty($row['analysis']) ? json_decode($row['analysis'], true) : null;
        $row['study_plan'] = !empty($row['study_plan']) ? json_decode($row['study_plan'], true) : null;
        return $row;
    }

    /** Submit answers for an exam. */
    public function submitAnswers(int $examId, array $answers): void
    {
        $stmt = $this->db->prepare(
            'UPDATE exams SET answers = :answers, status = "submitted" WHERE id = :id'
        );
        $stmt->execute([
            'answers' => json_encode($answers, JSON_UNESCAPED_UNICODE),
            'id'      => $examId,
        ]);
    }

    /** Save AI feedback + score. */
    public function saveFeedback(int $examId, array $feedback, float $score): void
    {
        $stmt = $this->db->prepare(
            'UPDATE exams SET feedback = :feedback, score = :score, status = "graded" WHERE id = :id'
        );
        $stmt->execute([
            'feedback' => json_encode($feedback, JSON_UNESCAPED_UNICODE),
            'score'    => $score,
            'id'       => $examId,
        ]);
    }

    /** Save analysis for an exam. */
    public function saveAnalysis(int $examId, array $analysis): void
    {
        $stmt = $this->db->prepare(
            'UPDATE exams SET analysis = :analysis WHERE id = :id'
        );
        $stmt->execute([
            'analysis' => json_encode($analysis, JSON_UNESCAPED_UNICODE),
            'id'       => $examId,
        ]);
    }

    /** Save study plan for an exam. */
    public function saveStudyPlan(int $examId, array $plan): void
    {
        $stmt = $this->db->prepare(
            'UPDATE exams SET study_plan = :plan WHERE id = :id'
        );
        $stmt->execute([
            'plan' => json_encode($plan, JSON_UNESCAPED_UNICODE),
            'id'   => $examId,
        ]);
    }

    /** List exams for a user. */
    public function listByUser(int $userId, int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, topic, status, score, created_at FROM exams WHERE user_id = :uid ORDER BY created_at DESC LIMIT :lim'
        );
        $stmt->bindValue('uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue('lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
