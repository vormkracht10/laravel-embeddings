<?php

namespace Vormkracht10\Embedding\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Vormkracht10\Embedding\LaravelEmbed
 */
class LaravelEmbed extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Vormkracht10\Embedding\LaravelEmbed::class;
    }
}
