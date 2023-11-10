<?php

namespace Vormkracht10\LaravelGpt\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Vormkracht10\LaravelGpt\LaravelGpt
 */
class LaravelGpt extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Vormkracht10\LaravelGpt\LaravelGpt::class;
    }
}
