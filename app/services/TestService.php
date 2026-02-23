<?php

declare(strict_types=1);

require_once __DIR__ . '/AiService.php';
require_once __DIR__ . '/PromptFactory.php';
require_once __DIR__ . '/../repositories/TestRepository.php';
require_once __DIR__ . '/../repositories/AttemptRepository.php';

final class TestService
{
    private TestRepository $tests;
    private AttemptRepository $attempts;

    public function __construct()
    {
        $this->tests    = new TestRepository();
        $this->attempts = new AttemptRepository();
    }

    /**
     * Generate a test via AI and persist it.
     * Returns [test_id, questions[]].
     */
    public function generate(int $userId, string $topic, int $count = 5): array
    {
        $prompt    = PromptFactory::test($topic, $count);
        $questions = AiService::chatJson($prompt['system'], $prompt['user']);

        // Ensure we have the right structure
        $questions = array_slice($questions, 0, $count);

        $testId = $this->tests->create($userId, $topic, $questions);

        return [
            'test_id'   => $testId,
            'topic'     => $topic,
            'questions' => $questions,
        ];
    }

    /**
     * Score submitted answers, persist the attempt.
     * $answers = [0 => "A", 1 => "C", ...] indexed by question index.
     */
    public function submit(int $testId, int $userId, array $answers): array
    {
        $test = $this->tests->findById($testId);
        if (!$test) throw new RuntimeException('Test not found.');

        $questions = $test['questions'];
        $score = 0;
        $details = [];

        foreach ($questions as $i => $q) {
            $given   = $answers[$i] ?? '';
            $correct = $q['correct'] ?? '';
            $isRight = (strtoupper($given) === strtoupper($correct));
            if ($isRight) $score++;

            $details[] = [
                'question'   => $q['question'],
                'options'    => $q['options'],
                'correct'    => $correct,
                'given'      => $given,
                'is_correct' => $isRight,
            ];
        }

        $total = count($questions);
        $pct   = $total > 0 ? round(($score / $total) * 100, 1) : 0;

        $attemptId = $this->attempts->create($testId, $userId, $details, $score, $total, $pct);

        return [
            'attempt_id' => $attemptId,
            'score'      => $score,
            'total'      => $total,
            'percentage' => $pct,
            'details'    => $details,
        ];
    }
}
