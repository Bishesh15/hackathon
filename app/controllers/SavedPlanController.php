<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Helpers.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../repositories/PlanRepository.php';

final class SavedPlanController
{
    private PlanRepository $repo;

    public function __construct()
    {
        $this->repo = new PlanRepository();
    }

    /** GET -> list all saved plans for user */
    public function list(): void
    {
        Session::requireAuthApi();
        $userId = Session::userId();
        $plans = $this->repo->findByUser($userId);
        json_success(['plans' => $plans]);
    }

    /** POST {type, topic, content, source, attempt_id?, exam_id?} -> save a plan */
    public function save(): void
    {
        Session::requireAuthApi();
        $userId = Session::userId();
        $input = read_json_input();

        $type      = trim($input['type'] ?? '');
        $topic     = trim($input['topic'] ?? '');
        $content   = $input['content'] ?? [];
        $source    = trim($input['source'] ?? 'quiz');
        $attemptId = isset($input['attempt_id']) ? (int) $input['attempt_id'] : null;
        $examId    = isset($input['exam_id']) ? (int) $input['exam_id'] : null;

        if (!in_array($type, ['analysis', 'study_plan'], true)) {
            json_error('Invalid plan type.');
        }
        if ($topic === '') {
            json_error('Topic is required.');
        }
        if (!is_array($content) || empty($content)) {
            json_error('Content is required.');
        }

        $id = $this->repo->save($userId, $type, $topic, $content, $source, $attemptId, $examId);
        json_success(['id' => $id], 'Plan saved');
    }

    /** POST {id} -> delete a saved plan */
    public function delete(): void
    {
        Session::requireAuthApi();
        $userId = Session::userId();
        $input = read_json_input();

        $planId = (int) ($input['id'] ?? 0);
        if ($planId <= 0) {
            json_error('Invalid plan ID.');
        }
        $this->repo->delete($planId, $userId);
        json_success([], 'Plan deleted');
    }
}
