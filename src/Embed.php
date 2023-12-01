<?php

namespace Vormkracht10\Embedding;

use Vormkracht10\Embedding\Jobs\MakeEmbeddable;
use Vormkracht10\Embedding\Jobs\RemoveFromEmbed;

class Embed
{
    /**
     * The job class that should make models searchable.
     *
     * @var string
     */
    public static $makeEmbeddableJob = MakeEmbeddable::class;

    /**
     * The job that should remove models from the search index.
     *
     * @var string
     */
    public static $removeFromEmbedJob = RemoveFromEmbed::class;

    /**
     * Specify the job class that should make models searchable.
     *
     * @return void
     */
    public static function makeEmbeddableUsing(string $class)
    {
        static::$makeEmbeddableJob = $class;
    }

    /**
     * Specify the job class that should remove models from the search index.
     *
     * @return void
     */
    public static function RemoveFromEmbedUsing(string $class)
    {
        static::$removeFromEmbedJob = $class;
    }
}
