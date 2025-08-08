<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class OpenAiClient implements AiClientInterface
{
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('ai.openai.api_key');
        $this->model  = config('ai.openai.model');
    }

    public function chat(array $messages, int $maxTokens = 4000): string
    {
        if ($this->isGpt5()) {
            return $this->chatGpt5($messages, $maxTokens);
        }
        return $this->chatStandard($messages, $maxTokens);
    }

    // --- Helpers ---
    private function isGpt5(): bool
    {
        return Str::startsWith($this->model, 'gpt-5');
    }

    private function chatGpt5(array $messages, int $maxTokens): string
    {
        // GPT-5: use Responses API with JSON mode; increase output token budget to avoid truncation
        $inputString = $this->messagesToInputString($messages);
        $safeMax = min(max($maxTokens, 7000), 8192);

        $payload = [
            'model' => $this->model,
            'input' => $inputString,
            'max_output_tokens' => $safeMax,
        ];

        $json = $this->postResponses($payload);
        $this->logTokenUsage($json, 'responses');
        // Auto-retry if truncated by max_output_tokens
        if (\Illuminate\Support\Arr::get($json, 'status') === 'incomplete'
            && \Illuminate\Support\Arr::get($json, 'incomplete_details.reason') === 'max_output_tokens'
            && $safeMax < 8192
        ) {
            $payload['max_output_tokens'] = 8192;
            $json = $this->postResponses($payload);
            $this->logTokenUsage($json, 'responses');
        }

        // Prefer the convenience field if present
        $content = (string) Arr::get($json, 'output_text', '');
        if ($content !== '') {
            return $content;
        }

        // Fallback for chat-style shape
        $content = (string) Arr::get($json, 'choices.0.message.content', '');
        if ($content !== '') {
            return $content;
        }

        // Scan all output items for text parts
        $buffer = '';
        $outputs = Arr::get($json, 'output', []);
        if (is_array($outputs)) {
            foreach ($outputs as $out) {
                $parts = Arr::get($out, 'content', []);
                if (is_array($parts)) {
                    foreach ($parts as $part) {
                        $text = (string) Arr::get($part, 'text', '');
                        if ($text !== '') {
                            $buffer .= $text;
                        }
                    }
                }
            }
        }
        return $buffer;
    }

    private function chatStandard(array $messages, int $maxTokens): string
    {
        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => 0.6,
            'max_tokens' => $maxTokens,
            'presence_penalty' => 0.1,
        ];
        $json = $this->postChat($payload);
        $this->logTokenUsage($json, 'chat');
        return Arr::get($json, 'choices.0.message.content', '');
    }

    private function postChat(array $payload): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(120)->post('https://api.openai.com/v1/chat/completions', $payload);
        logger($response->json());
        if (! $response->successful()) {
            throw new \RuntimeException('OpenAI API error: '.$response->body(), $response->status());
        }
        return (array) $response->json();
    }

    private function postResponses(array $payload): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(180)->post('https://api.openai.com/v1/responses', $payload);
        logger($response->json());
        if (! $response->successful()) {
            throw new \RuntimeException('OpenAI API error: '.$response->body(), $response->status());
        }
        return (array) $response->json();
    }

    /**
     * Logs token usage (input/output/total) for both Responses and Chat endpoints.
     */
    private function logTokenUsage(array $json, string $endpoint): void
    {
        $usage = Arr::get($json, 'usage');
        if (!is_array($usage)) {
            Log::debug('OpenAI usage not present', [
                'endpoint' => $endpoint,
                'model' => $this->model,
            ]);
            return;
        }

        $inputTokens = Arr::get($usage, 'input_tokens', Arr::get($usage, 'prompt_tokens'));
        $outputTokens = Arr::get($usage, 'output_tokens', Arr::get($usage, 'completion_tokens'));
        $totalTokens = Arr::get($usage, 'total_tokens');

        $inputDetails = Arr::get($usage, 'input_tokens_details');
        $outputDetails = Arr::get($usage, 'output_tokens_details');

        Log::debug('OpenAI token usage', [
            'endpoint' => $endpoint,
            'model' => $this->model,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'total_tokens' => $totalTokens,
            'input_tokens_details' => $inputDetails,
            'output_tokens_details' => $outputDetails,
            'status' => Arr::get($json, 'status'),
            'incomplete_reason' => Arr::get($json, 'incomplete_details.reason'),
        ]);
    }

    private function messagesToInputString(array $messages): string
    {
        $parts = [];
        foreach ($messages as $m) {
            $content = trim((string) Arr::get($m, 'content', ''));
            if ($content === '') {
                continue;
            }
            $role = strtolower((string) Arr::get($m, 'role', 'user'));
            $label = match ($role) {
                'system' => 'System',
                'assistant' => 'Assistant',
                default => 'User',
            };
            $parts[] = $label.': '.$content;
        }
        return implode("\n\n", $parts);
    }
}
