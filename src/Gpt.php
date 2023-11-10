<?php

namespace Vormkracht10\LaravelGpt;

use Vormkracht10\LaravelGpt\Jobs\MakeGptable;
use Vormkracht10\LaravelGpt\Jobs\RemoveFromGpt;

class Gpt
{
    /**
     * The job class that should make models searchable.
     *
     * @var string
     */
    public static $makeGptableJob = MakeGptable::class;

    /**
     * The job that should remove models from the search index.
     *
     * @var string
     */
    public static $removeFromGptJob = RemoveFromGpt::class;

    /**
     * Specify the job class that should make models searchable.
     *
     * @return void
     */
    public static function makeGptableUsing(string $class)
    {
        static::$makeGptableJob = $class;
    }

    /**
     * Specify the job class that should remove models from the search index.
     *
     * @return void
     */
    public static function removeFromGptUsing(string $class)
    {
        static::$removeFromGptJob = $class;
    }
}
