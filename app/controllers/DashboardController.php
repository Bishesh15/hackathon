<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Helpers.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../repositories/ActivityRepository.php';
require_once __DIR__ . '/../repositories/AttemptRepository.php';
require_once __DIR__ . '/../repositories/ConversationRepository.php';

final class DashboardController
{
    /** GET -> dashboard summary stats */
    public function summary(): void
    {
        Session::requireAuthApi();
        $uid = Session::userId();

        $activities    = new ActivityRepository();
        $attempts      = new AttemptRepository();
        $conversations = new ConversationRepository();

        json_success([
            'stats' => [
                'total_activities' => $activities->countByUser($uid),
                'tutor_sessions'   => $conversations->countByUser($uid),
                'notes_generated'  => $activities->countByModule($uid, 'notes'),
                'quizzes_taken'    => $attempts->totalAttempts($uid),
                'tests_attempted'  => $activities->countByModule($uid, 'test'),
                'avg_score'        => $attempts->avgScore($uid),
            ],
        ]);
    }
}
