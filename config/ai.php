<?php

return [
    // Define which provider to use: OPENAI or PERPLEXITY
    'provider' => env('AI_PROVIDER', 'OPENAI'),

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        // Optional tuning knobs used by App\Services\Ai\OpenAiClient
        'timeout' => env('OPENAI_TIMEOUT', 60),                // seconds
        'connect_timeout' => env('OPENAI_CONNECT_TIMEOUT', 10), // seconds
        // 'low' | 'medium' | 'high' (if supported by the model). If unset, no reasoning field is sent.
        'reasoning_effort' => env('OPENAI_REASONING_EFFORT'),
    ],

    'perplexity' => [
        'api_key' => env('PERPLEXITY_API_KEY'),
        'model' => env('PERPLEXITY_MODEL', 'sonar-medium-online'),
    ],
];
