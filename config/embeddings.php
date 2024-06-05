<?php

// config for Vormkracht10/Embeddings
return [
    'enabled' => env('EMBEDDINGS_ENABLED', true),
    'driver' => env('EMBEDDINGS_DRIVER', 'null'), // 'null' / 'openai'
    'queue' => true,
    'database' => [
        'connection' => env('EMBEDDINGS_DATABASE_CONNECTION', 'pgsql'),
        'table' => env('EMBEDDINGS_DB_TABLE', 'embeddings'),
    ],
    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_EMBEDDING_MODEL', 'text-embedding-ada-002'),
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
