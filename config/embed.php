<?php

// config for Vormkracht10/Embeddings
return [
    'enabled' => env('EMBED_ENABLED', true),
    'driver' => env('EMBED_DRIVER', 'openai'),
    'queue' => true,
    'database' => [
        'connection' => env('EMBED_DATABASE_CONNECTION'),
        'table' => env('EMBED_DB_TABLE', 'embeddings'),
    ],
    'openai' => [
        'key' => env('OPENAI_API_KEY'),
    ],
];
