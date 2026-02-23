<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Helpers.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../services/PlanService.php';
require_once __DIR__ . '/../services/ValidationService.php';

final class PlanController
{
    /** POST {attempt_id} -> returns study plan */
    public function get(): void
    {
        Session::requireAuthApi();

        $input = read_json_input();
        ValidationService::requireFields($input, ['attempt_id']);

        try {
            $plan = (new PlanService())->generate((int) $input['attempt_id']);
        } catch (\Throwable $e) {
            json_error('AI request failed: ' . $e->getMessage(), 502);
        }
        json_success(['plan' => $plan]);
    }
}
