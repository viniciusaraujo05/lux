<?php

namespace App\Services\Ai;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use function mt_rand;
use function random_int;

class OpenAiClient implements AiClientInterface
{
    private string $apiKey;

    private string $model;

    private int $timeout;

    private int $connectTimeout;

    public function __construct()
    {
        $this->apiKey = config('ai.openai.api_key');
        $this->model = config('ai.openai.model');
        $this->timeout = (int) (config('ai.openai.timeout', 60));
        $this->connectTimeout = (int) (config('ai.openai.connect_timeout', 10));
    }

    public function chat(array $messages, int $maxTokens = 4000): string
    {
        // Verificar se alguma mensagem contém a palavra 'json'
        $containsJson = false;
        foreach ($messages as $message) {
            if (isset($message['content']) && stripos($message['content'], 'json') !== false) {
                $containsJson = true;
                break;
            }
        }
        
        if ($this->isGpt5()) {
            return $this->chatGpt5($messages, $maxTokens, $containsJson);
        }

        return $this->chatStandard($messages, $maxTokens);
    }

    // --- Helpers ---
    private function isGpt5(): bool
    {
        return Str::startsWith($this->model, 'gpt-5');
    }

    private function chatGpt5(array $messages, int $maxTokens, bool $useJsonFormat = false): string
    {
        // GPT-5: use Responses API with JSON mode; increase output token budget to avoid truncation
        $inputString = $this->messagesToInputString($messages);
        // Respect caller-provided budgets; only cap to API hard limit
        $safeMax = min($maxTokens, 8192);

        $payload = [
            'model' => $this->model,
            'input' => $inputString,
            'max_output_tokens' => $safeMax,
        ];
        
        // Adiciona formato JSON apenas se a mensagem contiver a palavra 'json'
        if ($useJsonFormat) {
            $payload['text'] = ['format' => ['type' => 'json_object']];
        }

        // Optional reasoning effort (e.g., 'low' for faster responses) if configured
        $effort = config('ai.openai.reasoning_effort'); // e.g., 'low' | 'medium' | 'high'
        if (is_string($effort) && $effort !== '') {
            $payload['reasoning'] = ['effort' => $effort];
        }

        // Try with optional reasoning; on 4xx, retry once without the reasoning field
        try {
            $json = $this->postResponses($payload);
        } catch (\RuntimeException $e) {
            $code = (int) $e->getCode();
            if ($code >= 400 && $code < 500 && isset($payload['reasoning'])) {
                unset($payload['reasoning']);
                $json = $this->postResponses($payload);
            } else {
                throw $e;
            }
        }
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
            'response_format' => ['type' => 'json_object'],
        ];
        try {
            $json = $this->postChat($payload);
        } catch (\RuntimeException $e) {
            $code = (int) $e->getCode();
            if ($code >= 400 && $code < 500 && isset($payload['response_format'])) {
                // Retry once without response_format for models that don't support JSON mode
                unset($payload['response_format']);
                $json = $this->postChat($payload);
            } else {
                throw $e;
            }
        }
        $this->logTokenUsage($json, 'chat');

        return Arr::get($json, 'choices.0.message.content', '');
    }

    private function postChat(array $payload): array
    {
        return $this->postJsonWithRetry('https://api.openai.com/v1/chat/completions', $payload);
    }

    private function postResponses(array $payload): array
    {
        return $this->postJsonWithRetry('https://api.openai.com/v1/responses', $payload);
    }

    /**
     * Logs token usage (input/output/total) for both Responses and Chat endpoints.
     */
    private function logTokenUsage(array $json, string $endpoint): void
    {
        $usage = Arr::get($json, 'usage');
        if (! is_array($usage)) {
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
    }

    private function messagesToInputString(array $messages): string
    {
        $parts = [];
        // Keep system + last up to 6 user/assistant turns to minimize prompt size
        $system = [];
        $rest = [];
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
            if ($label === 'System') {
                $system[] = $label.': '.$content;
            } else {
                $rest[] = $label.': '.$content;
            }
        }
        // Limit conversation turns to reduce tokens
        if (count($rest) > 12) {
            $rest = array_slice($rest, -12);
        }
        $parts = array_merge($system, $rest);

        return implode("\n\n", $parts);
    }

    /**
     * Returns a cryptographically secure random integer when available,
     * otherwise falls back to mt_rand. Keeps analyzers happy without leading \
     * global namespace tokens in call sites.
     */
    private function randomSleep(int $minMs = 100, int $maxMs = 500): void
    {
        // Avoid rate limits with a small random sleep
        $ms = $this->secureRandomInt($minMs, $maxMs);
        usleep($ms * 1000);
    }
    
    /**
     * Gera um número aleatório seguro
     */
    private function secureRandomInt(int $min, int $max): int
    {
        try {
            return \random_int($min, $max);
        } catch (\Exception $e) {
            return \mt_rand($min, $max);
        }
    }

    /**
     * Perform a JSON POST with retries, exponential backoff, and controlled timeouts.
     * Optimized for Railpack deployment.
     */
    private function postJsonWithRetry(string $url, array $payload, int $retries = 2): array
    {
        $backoffMs = 250;
        for ($attempt = 0; $attempt <= $retries; $attempt++) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer '.$this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'User-Agent' => 'Verbum/1.0',
                ])
                ->withOptions([
                    'http_errors' => false,
                    'stream' => false,  // Importante para Railpack
                    'verify' => true,
                    'timeout' => $this->timeout,
                    'connect_timeout' => $this->connectTimeout,
                ])
                ->connectTimeout($this->connectTimeout)
                ->timeout($this->timeout)
                ->post($url, $payload);

                if ($response->successful()) {
                    return (array) $response->json();
                }

                $status = $response->status();
                $body = $response->body();

                // Log para debug no Railpack
                Log::debug('OpenAI API response', [
                    'status' => $status,
                    'attempt' => $attempt + 1,
                    'url' => $url,
                    'body_length' => strlen($body)
                ]);

                // Retry on transient status codes
                if (in_array($status, [408, 409, 425, 429, 500, 502, 503, 504], true) && $attempt < $retries) {
                    $sleepTime = ($backoffMs + $this->secureRandomInt(0, 150)) * 1000;
                    usleep($sleepTime);
                    $backoffMs = min($backoffMs * 2, 2000);

                    continue;
                }

                throw new \RuntimeException('OpenAI API error: '.$body, $status);
            } catch (\Throwable $e) {
                Log::error('OpenAI API exception', [
                    'message' => $e->getMessage(),
                    'attempt' => $attempt + 1,
                    'url' => $url
                ]);

                if ($attempt < $retries) {
                    $sleepTime = ($backoffMs + $this->secureRandomInt(0, 150)) * 1000;
                    usleep($sleepTime);
                    $backoffMs = min($backoffMs * 2, 2000);

                    continue;
                }
                throw new \RuntimeException('OpenAI API network error: '.$e->getMessage(), $e->getCode() ?: 0, $e);
            }
        }

        // Unreachable, but PHP requires a return
        return [];
    }
}
