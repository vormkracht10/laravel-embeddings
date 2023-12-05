<?php

namespace Vormkracht10\Embedding;

use Illuminate\Support\ServiceProvider;
use Vormkracht10\Embedding\Commands\ImportCommand;

class EmbeddingServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/embeddings.php', 'embeddings');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ImportCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/embeddings.php' => config_path('embeddings.php'),
            ], 'laravel-embeddings-config');
            $this->publishes([
                __DIR__.'/../database/migrations/' => database_path('migrations'),
            ], 'laravel-embeddings-migrations');
        }
    }
}
