<?php

declare(strict_types=1);

require_once __DIR__ . '/AiService.php';
require_once __DIR__ . '/PromptFactory.php';
require_once __DIR__ . '/AnalysisService.php';
require_once __DIR__ . '/../repositories/AttemptRepository.php';
require_once __DIR__ . '/../repositories/TestRepository.php';

final class PlanService
{
    private AttemptRepository $attempts;
    private TestRepository $tests;

    public function __construct()
    {
        $this->attempts = new AttemptRepository();
        $this->tests    = new TestRepository();
    }

    /**
     * Generate a study plan from analysis for an attempt.
     */
    public function generate(int $attemptId): array
    {
        $attempt = $this->attempts->findById($attemptId);
        if (!$attempt) throw new RuntimeException('Attempt not found.');

        // Return cached if exists
        if (!empty($attempt['study_plan'])) {
            return $attempt['study_plan'];
        }

        // Ensure analysis exists first
        $analysis = $attempt['analysis'];
        if (empty($analysis)) {
            $analysisSvc = new AnalysisService();
            $analysis = $analysisSvc->analyze($attemptId);
        }

        $test  = $this->tests->findById((int) $attempt['test_id']);
        $topic = $test['topic'] ?? 'General';

        $prompt = PromptFactory::studyPlan($topic, $analysis);
        $plan   = AiService::chatJson($prompt['system'], $prompt['user']);

        $this->attempts->savePlan($attemptId, $plan);
        return $plan;
    }
}
