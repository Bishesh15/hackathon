<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Helpers.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../repositories/NoteRepository.php';

final class NoteController
{
    private NoteRepository $repo;

    public function __construct()
    {
        $this->repo = new NoteRepository();
    }

    /** GET -> list all notes for user */
    public function list(): void
    {
        Session::requireAuthApi();
        $userId = Session::userId();
        $notes = $this->repo->findByUser($userId);
        json_success(['notes' => $notes]);
    }

    /** POST {title, content} -> create a note */
    public function save(): void
    {
        Session::requireAuthApi();
        $userId = Session::userId();
        $input = read_json_input();

        $title   = trim($input['title'] ?? '');
        $content = trim($input['content'] ?? '');
        $noteId  = isset($input['id']) ? (int) $input['id'] : 0;

        if ($title === '' && $content === '') {
            json_error('Title and content cannot both be empty.');
        }
        if ($title === '') {
            // Auto-generate title from first line of content
            $title = substr(strip_tags($content), 0, 60);
            if (strlen($content) > 60) $title .= '…';
        }

        if ($noteId > 0) {
            $this->repo->update($noteId, $userId, $title, $content);
            json_success(['id' => $noteId], 'Note updated');
        } else {
            $id = $this->repo->create($userId, $title, $content);
            json_success(['id' => $id], 'Note saved');
        }
    }

    /** POST {id} -> delete a note */
    public function delete(): void
    {
        Session::requireAuthApi();
        $userId = Session::userId();
        $input = read_json_input();

        $noteId = (int) ($input['id'] ?? 0);
        if ($noteId <= 0) {
            json_error('Invalid note ID.');
        }
        $this->repo->delete($noteId, $userId);
        json_success([], 'Note deleted');
    }
}
