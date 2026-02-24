<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Helpers.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../services/AiService.php';
require_once __DIR__ . '/../services/PromptFactory.php';
require_once __DIR__ . '/../repositories/ExamRepository.php';
require_once __DIR__ . '/../services/ValidationService.php';

final class ExamAnalysisController
{
    private ExamRepository $exams;

    public function __construct()
    {
        $this->exams = new ExamRepository();
    }

    /** POST {exam_id} -> returns analysis for an exam */
    public function analysis(): void
    {
        Session::requireAuthApi();
        $input = read_json_input();
        ValidationService::requireFields($input, ['exam_id']);

        $examId = (int) $input['exam_id'];
        $exam = $this->exams->findById($examId);
        if (!$exam) json_error('Exam not found.', 404);
        if ($exam['status'] !== 'graded') json_error('Exam not graded yet.', 400);

        // Return cached if exists
        if (!empty($exam['analysis'])) {
            json_success(['analysis' => $exam['analysis']]);
        }

        $topic    = $exam['topic'];
        $feedback = $exam['feedback'];
        $questions = $exam['questions'];

        // Build data about weak areas from feedback
        $weakAreas = [];
        if (!empty($feedback['questions'])) {
            foreach ($feedback['questions'] as $i => $fb) {
                if (($fb['score'] ?? 100) < 70) {
                    $weakAreas[] = [
                        'question' => $questions[$i]['question'] ?? 'Q' . ($i + 1),
                        'score'    => $fb['score'] ?? 0,
                        'feedback' => $fb['feedback'] ?? '',
                    ];
                }
            }
        }

        if (empty($weakAreas)) {
            $analysis = [
                'weaknesses'      => [],
                'insights'        => ['Excellent performance! All answers scored well.'],
                'recommendations' => ['Keep up the great work and try more challenging topics.'],
            ];
        } else {
            $weakJson = json_encode($weakAreas, JSON_UNESCAPED_UNICODE);
            $systemPrompt = 'You are a learning analyst. Analyze the student\'s weak areas in a long-answer test and return ONLY a JSON object: {"weaknesses": ["..."], "insights": ["..."], "recommendations": ["..."]}. No extra text.';
            $userPrompt = "Topic: $topic\nOverall score: {$exam['score']}%\nWeak areas:\n$weakJson";

            try {
                $analysis = AiService::chatJson($systemPrompt, $userPrompt);
            } catch (\Throwable $e) {
                json_error('AI request failed: ' . $e->getMessage(), 502);
            }
        }

        $this->exams->saveAnalysis($examId, $analysis);
        json_success(['analysis' => $analysis]);
    }

    /** POST {exam_id} -> returns study plan for an exam */
    public function plan(): void
    {
        Session::requireAuthApi();
        $input = read_json_input();
        ValidationService::requireFields($input, ['exam_id']);

        $examId = (int) $input['exam_id'];
        $exam = $this->exams->findById($examId);
        if (!$exam) json_error('Exam not found.', 404);
        if ($exam['status'] !== 'graded') json_error('Exam not graded yet.', 400);

        // Return cached if exists
        if (!empty($exam['study_plan'])) {
            json_success(['plan' => $exam['study_plan']]);
        }

        // Ensure analysis exists
        $analysis = $exam['analysis'];
        if (empty($analysis)) {
            // Generate analysis first (inline)
            $topic    = $exam['topic'];
            $feedback = $exam['feedback'];
            $questions = $exam['questions'];
            $weakAreas = [];
            if (!empty($feedback['questions'])) {
                foreach ($feedback['questions'] as $i => $fb) {
                    if (($fb['score'] ?? 100) < 70) {
                        $weakAreas[] = [
                            'question' => $questions[$i]['question'] ?? 'Q' . ($i + 1),
                            'score'    => $fb['score'] ?? 0,
                            'feedback' => $fb['feedback'] ?? '',
                        ];
                    }
                }
            }
            if (empty($weakAreas)) {
                $analysis = [
                    'weaknesses'      => [],
                    'insights'        => ['Excellent performance!'],
                    'recommendations' => ['Try harder topics.'],
                ];
            } else {
                $weakJson = json_encode($weakAreas, JSON_UNESCAPED_UNICODE);
                $systemPrompt = 'You are a learning analyst. Analyze the student\'s weak areas and return ONLY a JSON object: {"weaknesses": ["..."], "insights": ["..."], "recommendations": ["..."]}. No extra text.';
                $userPrompt = "Topic: {$exam['topic']}\nWeak areas:\n$weakJson";
                try {
                    $analysis = AiService::chatJson($systemPrompt, $userPrompt);
                } catch (\Throwable $e) {
                    json_error('AI request failed: ' . $e->getMessage(), 502);
                }
            }
            $this->exams->saveAnalysis($examId, $analysis);
        }

        $topic = $exam['topic'];
        $prompt = PromptFactory::studyPlan($topic, $analysis);

        try {
            $plan = AiService::chatJson($prompt['system'], $prompt['user']);
        } catch (\Throwable $e) {
            json_error('AI request failed: ' . $e->getMessage(), 502);
        }

        $this->exams->saveStudyPlan($examId, $plan);
        json_success(['plan' => $plan]);
    }
}
