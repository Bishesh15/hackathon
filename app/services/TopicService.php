<?php

declare(strict_types=1);

final class TopicService
{
    /**
     * Normalize and tag a topic string.
     * For a hackathon we simply clean + capitalize; extend with AI tagging later.
     */
    public static function normalize(string $raw): string
    {
        $clean = trim(preg_replace('/\s+/', ' ', $raw));
        return ucfirst($clean);
    }
}
