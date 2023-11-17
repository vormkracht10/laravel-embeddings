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
                __DIR__.'/../config/gpt.php' => config_path('gpt.php'),
            ], 'laravel-gpt-config');
            $this->publishes([
                __DIR__.'/../database/migrations/' => database_path('migrations'),
            ], 'laravel-gpt-migrations');
        }
    }
}
