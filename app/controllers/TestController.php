<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Helpers.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../services/TestService.php';
require_once __DIR__ . '/../services/TopicService.php';
require_once __DIR__ . '/../services/ValidationService.php';

final class TestController
{
    private TestService $svc;

    public function __construct()
    {
        $this->svc = new TestService();
    }

    /** POST  {topic, count?}  -> generates MCQ test */
    public function create(): void
    {
        Session::requireAuthApi();

        $input = read_json_input();
        ValidationService::requireFields($input, ['topic']);

        $topic = TopicService::normalize((string) $input['topic']);
        $count = max(1, min(20, (int) ($input['count'] ?? 5)));

        try {
            $result = $this->svc->generate(Session::userId(), $topic, $count);
        } catch (\Throwable $e) {
            json_error('AI request failed: ' . $e->getMessage(), 502);
        }
        json_success($result, 'Test generated.');
    }

    /** POST  {test_id, answers: ["A","C",...]}  -> scores test */
    public function submit(): void
    {
        Session::requireAuthApi();

        $input = read_json_input();
        ValidationService::requireFields($input, ['test_id', 'answers']);

        $testId  = (int) $input['test_id'];
        $answers = (array) $input['answers'];

        $result = $this->svc->submit($testId, Session::userId(), $answers);
        json_success($result, 'Test submitted.');
    }
}
