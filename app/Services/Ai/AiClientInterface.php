<?php

namespace App\Services\Ai;

interface AiClientInterface
{
    /**
     * Generate a completion given the messages.
     *
     * @param array<int, array{role: string, content: string}> $messages
     * @param int $maxTokens
     * @return string JSON string or raw content returned by provider
     */
    public function chat(array $messages, int $maxTokens = 4000): string;
}
