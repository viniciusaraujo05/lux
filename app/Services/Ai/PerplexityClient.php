<?php

namespace App\Services\Ai;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class PerplexityClient implements AiClientInterface
{
    private string $apiKey;

    private string $model;

    public function __construct()
    {
        $this->apiKey = config('ai.perplexity.api_key');
        $this->model = config('ai.perplexity.model');
    }

    public function chat(array $messages, int $maxTokens = 4000): string
    {
        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => 0.6,
            'max_tokens' => $maxTokens,
            'presence_penalty' => 0.1,
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(120)->post('https://api.perplexity.ai/chat/completions', $payload);

        if (! $response->successful()) {
            throw new \RuntimeException('Perplexity API error: '.$response->body(), $response->status());
        }

        return Arr::get($response->json(), 'choices.0.message.content', '');
    }
}
