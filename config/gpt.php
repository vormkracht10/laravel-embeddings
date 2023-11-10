<?php

// config for Vormkracht10/LaravelGpt
return [
    'enabled' => env('GPT_ENABLED', true),
    'driver' => env('GPT_DRIVER', 'openai'),
    'queue' => true,
    'database' => [
        'connection' => env('GPT_DATABASE_CONNECTION', env('DB_CONNECTION', 'mysql')),
        'table' => env('GPT_DB_TABLE', 'gpt')
    ],
    'openai' => [
        'key' => env('OPENAI_API_KEY')
    ]
];
