<?php

return [
    // Define which provider to use: OPENAI or PERPLEXITY
    'provider' => env('AI_PROVIDER', 'OPENAI'),

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        // Increased timeouts to avoid cURL error 28 (timeout after 30s)
        'timeout' => env('OPENAI_TIMEOUT', 90),                // seconds (increased to 90s for GPT-4o-mini/GPT-5)
        'connect_timeout' => env('OPENAI_CONNECT_TIMEOUT', 10), // seconds
        // 'low' | 'medium' | 'high' (if supported by the model). If unset, no reasoning field is sent.
        'reasoning_effort' => env('OPENAI_REASONING_EFFORT'),
    ],

    'perplexity' => [
        'api_key' => env('PERPLEXITY_API_KEY'),
        'model' => env('PERPLEXITY_MODEL', 'sonar-medium-online'),
    ],
];
