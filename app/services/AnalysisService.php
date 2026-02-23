<?php

declare(strict_types=1);

require_once __DIR__ . '/AiService.php';
require_once __DIR__ . '/PromptFactory.php';
require_once __DIR__ . '/../repositories/AttemptRepository.php';
require_once __DIR__ . '/../repositories/TestRepository.php';

final class AnalysisService
{
    private AttemptRepository $attempts;
    private TestRepository $tests;

    public function __construct()
    {
        $this->attempts = new AttemptRepository();
        $this->tests    = new TestRepository();
    }

    /**
     * Generate weakness analysis for an attempt via AI and persist it.
     */
    public function analyze(int $attemptId): array
    {
        $attempt = $this->attempts->findById($attemptId);
        if (!$attempt) throw new RuntimeException('Attempt not found.');

        // If already analyzed, return cached
        if (!empty($attempt['analysis'])) {
            return $attempt['analysis'];
        }

        $test = $this->tests->findById((int) $attempt['test_id']);
        $topic = $test['topic'] ?? 'General';

        // Collect wrong answers
        $wrong = array_filter($attempt['answers'], fn($d) => !$d['is_correct']);

        if (empty($wrong)) {
            $analysis = [
                'weaknesses'      => [],
                'insights'        => ['Perfect score! No weaknesses detected.'],
                'recommendations' => ['Keep up the great work and try harder topics.'],
            ];
        } else {
            $prompt   = PromptFactory::analysis($topic, array_values($wrong));
            $analysis = AiService::chatJson($prompt['system'], $prompt['user']);
        }

        $this->attempts->saveAnalysis($attemptId, $analysis);
        return $analysis;
    }
}
