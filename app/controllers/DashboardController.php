<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Helpers.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../repositories/ActivityRepository.php';
require_once __DIR__ . '/../repositories/AttemptRepository.php';

final class DashboardController
{
    /** GET -> dashboard summary stats */
    public function summary(): void
    {
        Session::requireAuthApi();
        $uid = Session::userId();

        $activities = new ActivityRepository();
        $attempts   = new AttemptRepository();

        json_success([
            'stats' => [
                'total_activities' => $activities->countByUser($uid),
                'tutor_sessions'   => $activities->countByModule($uid, 'tutor'),
                'notes_generated'  => $activities->countByModule($uid, 'notes'),
                'quizzes_taken'    => $activities->countByModule($uid, 'quiz'),
                'tests_attempted'  => $attempts->totalAttempts($uid),
                'avg_score'        => $attempts->avgScore($uid),
            ],
        ]);
    }
}
