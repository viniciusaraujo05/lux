<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\App;

class AiClientFactory
{
    public static function make(): AiClientInterface
    {
        $provider = strtoupper(config('ai.provider', 'OPENAI'));

        return match ($provider) {
            'PERPLEXITY' => App::make(PerplexityClient::class),
            default       => App::make(OpenAiClient::class),
        };
    }
}
