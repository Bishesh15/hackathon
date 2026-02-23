<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Config.php';

final class AiService
{
    /**
     * Send a chat completion request to the Groq API (OpenAI-compatible).
     * Returns the assistant message string.
     */
    public static function chat(string $systemPrompt, string $userMessage): string
    {
        return self::chatWithMessages([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user',   'content' => $userMessage],
        ]);
    }

    /**
     * Send a multi-message chat completion (for continuous conversation).
     * $messages = [['role'=>'system','content'=>'...'], ['role'=>'user','content'=>'...'], ...]
     */
    public static function chatWithMessages(array $messages): string
    {
        $payload = [
            'model'       => Config::aiModel(),
            'messages'    => $messages,
            'temperature' => 0.7,
        ];

        $ch = curl_init(Config::aiApiUrl());
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . Config::aiApiKey(),
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT    => 60,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            throw new RuntimeException('AI API error (HTTP ' . $httpCode . '): ' . ($response ?: 'no response'));
        }

        $data = json_decode($response, true);
        return $data['choices'][0]['message']['content'] ?? '';
    }

    /**
     * Same as chat() but parses the response as JSON array.
     */
    public static function chatJson(string $systemPrompt, string $userMessage): array
    {
        $raw = self::chat($systemPrompt, $userMessage);

        // Strip markdown code fences if the model wraps them
        $raw = preg_replace('/^```(?:json)?\s*/i', '', $raw);
        $raw = preg_replace('/```\s*$/', '', $raw);

        $decoded = json_decode(trim($raw), true);
        if (!is_array($decoded)) {
            throw new RuntimeException('AI did not return valid JSON.');
        }
        return $decoded;
    }
}
