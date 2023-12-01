<?php

// config for Vormkracht10/Embeddings
return [
    'enabled' => env('EMBED_ENABLED', true),
    'driver' => env('EMBED_DRIVER', 'openai'),
    'queue' => true,
    'database' => [
        'connection' => env('EMBED_DATABASE_CONNECTION', 'pgsql'),
        'table' => env('EMBED_DB_TABLE', 'embeddings'),
    ],
    'openai' => [
        'key' => env('OPENAI_API_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Chunk Sizes
    |--------------------------------------------------------------------------
    |
    | These options allow you to control the maximum chunk size when you are
    | mass importing data into the embed engine. This allows you to fine
    | tune each of these chunk sizes based on the power of the servers.
    |
    */
    'chunk' => [
        'embeddable' => 500,
        'unembeddable' => 500,
    ],
];
