<?php

namespace Vormkracht10\Embedding;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Scope;
use Vormkracht10\Embedding\Events\ModelsFlushed;
use Vormkracht10\Embedding\Events\ModelsImported;

class EmbeddableScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @return void
     */
    public function apply(EloquentBuilder $builder, Model $model)
    {
        //
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @return void
     */
    public function extend(EloquentBuilder $builder)
    {
        $builder->macro('embeddable', function (EloquentBuilder $builder, $chunk = null) {
            $embedKeyName = $builder->getModel()->getEmbedKeyName();

            $builder->chunkById($chunk ?: config('embeddings.chunk.embeddable', 500), function ($models) {
                $models->filter->shouldBeEmbeddable()->embeddable();

                event(new ModelsImported($models));
            }, $builder->qualifyColumn($embedKeyName), $embedKeyName);
        });

        $builder->macro('unembeddable', function (EloquentBuilder $builder, $chunk = null) {
            $embedKeyName = $builder->getModel()->getEmbedKeyName();

            $builder->chunkById($chunk ?: config('embeddings.chunk.unembeddable', 500), function ($models) {
                $models->unembeddable();

                event(new ModelsFlushed($models));
            }, $builder->qualifyColumn($embedKeyName), $embedKeyName);
        });

        HasManyThrough::macro('embeddable', function ($chunk = null) {
            /** @var HasManyThrough $this */
            $this->chunkById($chunk ?: config('embeddings.chunk.embeddable', 500), function ($models) {
                $models->filter->shouldBeEmbeddable()->embeddable();

                event(new ModelsImported($models));
            });
        });

        HasManyThrough::macro('unembeddable', function ($chunk = null) {
            /** @var HasManyThrough $this */
            $this->chunkById($chunk ?: config('embeddings.chunk.unembeddable', 500), function ($models) {
                $models->unembeddable();

                event(new ModelsFlushed($models));
            });
        });
    }
}
