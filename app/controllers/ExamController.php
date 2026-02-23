<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Helpers.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../services/AiService.php';
require_once __DIR__ . '/../services/ValidationService.php';
require_once __DIR__ . '/../services/TopicService.php';
require_once __DIR__ . '/../repositories/ExamRepository.php';

final class ExamController
{
    private ExamRepository $exams;

    public function __construct()
    {
        $this->exams = new ExamRepository();
    }

    /**
     * POST {topic, count?} → generate long-answer questions
     */
    public function create(): void
    {
        Session::requireAuthApi();
        $uid   = Session::userId();
        $input = read_json_input();
        ValidationService::requireFields($input, ['topic']);

        $topic = TopicService::normalize((string) $input['topic']);
        $count = max(1, min(7, (int) ($input['count'] ?? 3)));

        $systemPrompt = "You are an exam question generator for school and high-school students. "
            . "Generate exactly $count long-answer questions about the given topic. "
            . "Questions should require paragraph-length answers or analysis. "
            . "Include a mix of: explain-type, compare-and-contrast, analyze, and application questions. "
            . "Return ONLY a JSON array of objects: [{\"question\": \"...\", \"type\": \"explain|compare|analyze|apply\"}]. No extra text.";

        try {
            $questions = AiService::chatJson($systemPrompt, "Generate $count long-answer questions on: $topic");
        } catch (\Throwable $e) {
            json_error('AI request failed: ' . $e->getMessage(), 502);
        }

        $questions = array_slice($questions, 0, $count);
        $examId = $this->exams->create($uid, $topic, $questions);

        json_success([
            'exam_id'   => $examId,
            'topic'     => $topic,
            'questions' => $questions,
        ]);
    }

    /**
     * POST {exam_id, answers: [{answer: "...", image_data?: "base64..."}]}
     * Grade answers via AI.
     */
    public function submit(): void
    {
        Session::requireAuthApi();
        $input = read_json_input();
        ValidationService::requireFields($input, ['exam_id', 'answers']);

        $examId  = (int) $input['exam_id'];
        $answers = (array) $input['answers'];

        $exam = $this->exams->findById($examId);
        if (!$exam) json_error('Exam not found.', 404);

        // Save raw answers
        $this->exams->submitAnswers($examId, $answers);

        // Build grading prompt
        $topic     = $exam['topic'];
        $questions = $exam['questions'];
        $gradingData = [];
        foreach ($questions as $i => $q) {
            $answerText = $answers[$i]['answer'] ?? '';
            if ($answerText === '') {
                $answerText = '(no answer provided)';
            }

            $gradingData[] = [
                'question' => $q['question'],
                'answer'   => $answerText,
            ];
        }

        $systemPrompt = "You are a teacher grading a student's long-answer exam on '$topic'. "
            . "For each question-answer pair, evaluate the answer's quality (correctness, depth, clarity). "
            . "Return ONLY a JSON object: {\"total_score\": 0-100, \"questions\": [{\"score\": 0-100, \"feedback\": \"...\"}]}. No extra text.";

        $userPrompt = "Grade these answers:\n" . json_encode($gradingData, JSON_UNESCAPED_UNICODE);

        try {
            $feedback = AiService::chatJson($systemPrompt, $userPrompt);
        } catch (\Throwable $e) {
            json_error('AI grading failed: ' . $e->getMessage(), 502);
        }

        $totalScore = (float) ($feedback['total_score'] ?? 0);
        $this->exams->saveFeedback($examId, $feedback, $totalScore);

        json_success([
            'exam_id'  => $examId,
            'score'    => $totalScore,
            'feedback' => $feedback,
        ]);
    }
}
