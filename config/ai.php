<?php

return [
    // Define which provider to use: OPENAI or PERPLEXITY
    'provider' => env('AI_PROVIDER', 'OPENAI'),

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        // Keep timeout bounded to reduce worst-case request latency
        'timeout' => env('OPENAI_TIMEOUT', 45),                // seconds
        'connect_timeout' => env('OPENAI_CONNECT_TIMEOUT', 10), // seconds
        // Low retry count avoids multiplicative latency when app-level retry also exists
        'retries' => env('OPENAI_RETRIES', 1),
        // 'low' | 'medium' | 'high' (if supported by the model). If unset, no reasoning field is sent.
        'reasoning_effort' => env('OPENAI_REASONING_EFFORT'),
    ],

    'generation' => [
        // App-level attempts for AI generation and JSON validation
        'max_attempts' => env('AI_GENERATION_MAX_ATTEMPTS', 2),
        // Output token budgets tuned for verse/chapter responses
        'max_tokens_verse' => env('AI_GENERATION_MAX_TOKENS_VERSE', 2400),
        'max_tokens_chapter' => env('AI_GENERATION_MAX_TOKENS_CHAPTER', 2800),
        // Extra budget only for retry attempts
        'retry_tokens_increment' => env('AI_GENERATION_RETRY_TOKENS_INCREMENT', 600),
    ],

    'perplexity' => [
        'api_key' => env('PERPLEXITY_API_KEY'),
        'model' => env('PERPLEXITY_MODEL', 'sonar-medium-online'),
    ],
];
