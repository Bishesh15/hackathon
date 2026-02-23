<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Helpers.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../repositories/ActivityRepository.php';
require_once __DIR__ . '/../repositories/AttemptRepository.php';

final class HistoryController
{
    /** GET -> combined activity + test history */
    public function list(): void
    {
        Session::requireAuthApi();
        $uid = Session::userId();

        $activities = (new ActivityRepository())->listByUser($uid);
        $attempts   = (new AttemptRepository())->listByUser($uid);

        json_success([
            'activities' => $activities,
            'attempts'   => $attempts,
        ]);
    }
}
