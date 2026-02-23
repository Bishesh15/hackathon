<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Helpers.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../repositories/ConversationRepository.php';
require_once __DIR__ . '/../repositories/AttemptRepository.php';
require_once __DIR__ . '/../repositories/ExamRepository.php';

final class HistoryController
{
    /** GET -> combined conversation + quiz attempt + exam history */
    public function list(): void
    {
        Session::requireAuthApi();
        $uid = Session::userId();

        $conversations = (new ConversationRepository())->listByUser($uid);
        $attempts      = (new AttemptRepository())->listByUser($uid);
        $exams         = (new ExamRepository())->listByUser($uid);

        json_success([
            'conversations' => $conversations,
            'attempts'      => $attempts,
            'exams'         => $exams,
        ]);
    }

    /** POST {type: "conversation"|"attempt", id: N} -> delete */
    public function delete(): void
    {
        Session::requireAuthApi();
        $uid   = Session::userId();
        $input = read_json_input();

        $type = $input['type'] ?? '';
        $id   = (int) ($input['id'] ?? 0);

        if ($id <= 0) json_error('Invalid id.', 422);

        if ($type === 'conversation') {
            $ok = (new ConversationRepository())->delete($id, $uid);
        } elseif ($type === 'attempt') {
            $ok = (new AttemptRepository())->delete($id, $uid);
        } else {
            json_error('Invalid type. Use conversation or attempt.', 422);
        }

        if (!$ok) json_error('Item not found or already deleted.', 404);

        json_success([], 'Deleted successfully.');
    }
}
