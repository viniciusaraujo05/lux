<?php

return [
    // Define which provider to use: OPENAI or PERPLEXITY
    'provider' => env('AI_PROVIDER', 'OPENAI'),

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-3.5-turbo'),
    ],

    'perplexity' => [
        'api_key' => env('PERPLEXITY_API_KEY'),
        'model' => env('PERPLEXITY_MODEL', 'sonar-medium-online'),
    ],
];
