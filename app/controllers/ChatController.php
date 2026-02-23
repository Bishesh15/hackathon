<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Helpers.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../core/Config.php';
require_once __DIR__ . '/../services/AiService.php';
require_once __DIR__ . '/../services/ValidationService.php';
require_once __DIR__ . '/../repositories/ConversationRepository.php';

final class ChatController
{
    private ConversationRepository $convos;

    public function __construct()
    {
        $this->convos = new ConversationRepository();
    }

    /**
     * POST  {message, conversation_id?}
     * If conversation_id is null/0 → create new conversation.
     * Returns {conversation_id, reply}.
     */
    public function send(): void
    {
        Session::requireAuthApi();
        $uid   = Session::userId();
        $input = read_json_input();
        ValidationService::requireFields($input, ['message']);

        $message = trim((string) $input['message']);
        $convId  = (int) ($input['conversation_id'] ?? 0);

        // Education-only filter system prompt
        $systemPrompt = "You are LearnAI, an expert educational tutor for school and high-school students. "
            . "You MUST ONLY answer questions related to school and high-school education topics such as math, science, social studies, English, history, geography, physics, chemistry, biology, computer science, etc. "
            . "If the user asks something unrelated to education (like coding professional software, adult topics, entertainment, etc.), politely decline and redirect them to ask an educational question. "
            . "Explain concepts clearly with examples. Use markdown formatting for readability. "
            . "Be encouraging and supportive.";

        // If continuing, load previous messages for context
        if ($convId > 0) {
            $conv = $this->convos->findById($convId);
            if (!$conv || (int) $conv['user_id'] !== $uid) {
                json_error('Conversation not found.', 404);
            }
        } else {
            // Create new conversation with first few words as title
            $title  = mb_substr($message, 0, 60);
            $convId = $this->convos->create($uid, 'tutor', $title);
        }

        // Save user message
        $this->convos->addMessage($convId, 'user', $message);

        // Build messages array for AI (include history for context)
        $history  = $this->convos->getMessages($convId);
        $messages = [['role' => 'system', 'content' => $systemPrompt]];

        // Keep last 20 messages to stay within token limits
        $recentHistory = array_slice($history, -20);
        foreach ($recentHistory as $msg) {
            $messages[] = ['role' => $msg['role'], 'content' => $msg['content']];
        }

        try {
            $reply = AiService::chatWithMessages($messages);
        } catch (\Throwable $e) {
            json_error('AI request failed: ' . $e->getMessage(), 502);
        }

        // Save assistant reply
        $this->convos->addMessage($convId, 'assistant', $reply);

        json_success([
            'conversation_id' => $convId,
            'reply'           => $reply,
        ]);
    }

    /** GET ?id=N → get conversation messages */
    public function messages(): void
    {
        Session::requireAuthApi();
        $uid    = Session::userId();
        $convId = (int) ($_GET['id'] ?? 0);

        if ($convId <= 0) json_error('Missing conversation id.', 422);

        $conv = $this->convos->findById($convId);
        if (!$conv || (int) $conv['user_id'] !== $uid) {
            json_error('Conversation not found.', 404);
        }

        $messages = $this->convos->getMessages($convId);

        json_success([
            'conversation' => $conv,
            'messages'     => $messages,
        ]);
    }

    /** GET → list user conversations */
    public function list(): void
    {
        Session::requireAuthApi();
        $uid = Session::userId();

        $conversations = $this->convos->listByUser($uid);

        json_success(['conversations' => $conversations]);
    }
}
