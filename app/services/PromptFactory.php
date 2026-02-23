<?php

declare(strict_types=1);

final class PromptFactory
{
    // ── Module prompts ────────────────────────────────

    public static function tutor(string $topic): array
    {
        return [
            'system' => 'You are a friendly, expert tutor. Explain concepts clearly with examples. Use markdown formatting.',
            'user'   => "Teach me about: $topic",
        ];
    }

    public static function notes(string $topic): array
    {
        return [
            'system' => 'You are an academic note generator. Create concise, well-structured study notes with headings, bullet points, and key takeaways. Use markdown.',
            'user'   => "Generate study notes for: $topic",
        ];
    }

    public static function quiz(string $topic): array
    {
        return [
            'system' => 'You are a quiz generator. Create 5 multiple-choice questions with 4 options each and indicate the correct answer. Format in markdown with numbering.',
            'user'   => "Create a quiz about: $topic",
        ];
    }

    // ── Test prompts ──────────────────────────────────

    public static function test(string $topic, int $count): array
    {
        return [
            'system' => "You are a test generator. Return ONLY a JSON array of $count objects. Each object: {\"question\": \"...\", \"options\": [\"A\",\"B\",\"C\",\"D\"], \"correct\": \"A\"}. No extra text.",
            'user'   => "Generate $count MCQ questions on: $topic",
        ];
    }

    // ── Analysis prompt ───────────────────────────────

    public static function analysis(string $topic, array $wrongQuestions): array
    {
        $list = json_encode($wrongQuestions, JSON_UNESCAPED_UNICODE);
        return [
            'system' => 'You are a learning analyst. Analyze the student\'s wrong answers and return ONLY a JSON object: {"weaknesses": ["..."], "insights": ["..."], "recommendations": ["..."]}. No extra text.',
            'user'   => "Topic: $topic\nWrong answers:\n$list",
        ];
    }

    // ── Study plan prompt ─────────────────────────────

    public static function studyPlan(string $topic, array $analysis): array
    {
        $ctx = json_encode($analysis, JSON_UNESCAPED_UNICODE);
        return [
            'system' => 'You are a study planner. Based on the analysis, create a structured study plan. Return ONLY a JSON object: {"title": "...", "days": [{"day": 1, "focus": "...", "tasks": ["..."]}]}. No extra text.',
            'user'   => "Topic: $topic\nAnalysis:\n$ctx",
        ];
    }
}
