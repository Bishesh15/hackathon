<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Helpers.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../services/AiService.php';
require_once __DIR__ . '/../services/PromptFactory.php';
require_once __DIR__ . '/../services/TopicService.php';
require_once __DIR__ . '/../services/ValidationService.php';
require_once __DIR__ . '/../repositories/ActivityRepository.php';

final class ModuleController
{
    private ActivityRepository $activities;

    public function __construct()
    {
        $this->activities = new ActivityRepository();
    }

    /**
     * Unified handler for tutor / notes / quiz modules.
     * Expects JSON: {module: "tutor"|"notes"|"quiz", topic: "..."}
     */
    public function run(): void
    {
        Session::requireAuthApi();

        $input = read_json_input();
        ValidationService::requireFields($input, ['module', 'topic']);

        $module = strtolower(trim((string) $input['module']));
        $topic  = TopicService::normalize((string) $input['topic']);

        if (!in_array($module, ['tutor', 'notes', 'quiz'], true)) {
            json_error('Invalid module. Use tutor, notes, or quiz.', 422);
        }

        $prompt = match ($module) {
            'tutor' => PromptFactory::tutor($topic),
            'notes' => PromptFactory::notes($topic),
            'quiz'  => PromptFactory::quiz($topic),
        };

        try {
            $aiResponse = AiService::chat($prompt['system'], $prompt['user']);
        } catch (\Throwable $e) {
            json_error('AI request failed: ' . $e->getMessage(), 502);
        }

        $this->activities->save(Session::userId(), $module, $topic, $prompt['user'], $aiResponse);

        json_success(['module' => $module, 'topic' => $topic, 'content' => $aiResponse]);
    }
}
