<?php

namespace Vormkracht10\LaravelGpt;

use Illuminate\Support\ServiceProvider;

class LaravelGptServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/gpt.php', 'gpt');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/gpt.php' => $this->app['path.config'].DIRECTORY_SEPARATOR.'gpt.php',
            ]);
        }
    }
}
