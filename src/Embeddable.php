<?php

namespace Vormkracht10\Embedding;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection as BaseCollection;

trait Embeddable
{
    /**
     * Boot the trait.
     *
     * @return void
     */
    public static function bootEmbeddable()
    {
        static::addGlobalScope(new EmbeddableScope);

        static::observe(new ModelObserver);

        (new static)->registerEmbeddableMacros();
    }

    /**
     * Register the embeddable macros.
     *
     * @return void
     */
    public function registerEmbeddableMacros()
    {
        $self = $this;

        BaseCollection::macro('embeddable', function () use ($self) {
            $self->queueMakeEmbeddable($this);
        });

        BaseCollection::macro('unembeddable', function () use ($self) {
            $self->queueRemoveFromEmbed($this);
        });
    }

    /**
     * Dispatch the job to make the given models embeddable.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    public function queueMakeEmbeddable($models)
    {
        if ($models->isEmpty()) {
            return;
        }

        if (! config('embeddings.queue')) {
            return $models->first()->makeEmbeddableUsing($models)->first()->makeEmbeddableUsing()->update($models);
        }

        dispatch((new Embed::$makeEmbeddableJob($models))
            ->onQueue($models->first()->syncWithEmbedUsingQueue())
            ->onConnection($models->first()->syncWithEmbedUsing()));
    }

    /**
     * Dispatch the job to make the given models unembeddable.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    public function queueRemoveFromEmbed($models)
    {
        if ($models->isEmpty()) {
            return;
        }

        if (! config('embeddings.queue')) {
            return $models->first()->embeddableUsing()->delete($models);
        }

        dispatch(new Embed::$removeFromEmbedJob($models))
            ->onQueue($models->first()->syncWitEmbedUsingQueue())
            ->onConnection($models->first()->syncWitEmbedUsing());
    }

    /**
     * Determine if the model should be embeddable.
     *
     * @return bool
     */
    public function shouldBeEmbeddable()
    {
        return true;
    }

    /**
     * When updating a model, this method determines if we should update the embed index.
     *
     * @return bool
     */
    public function embedIndexShouldBeUpdated()
    {
        return true;
    }

    /**
     * Make all instances of the model embeddable.
     *
     * @param  int  $chunk
     * @return void
     */
    public static function makeAllEmbeddable($chunk = null)
    {
        $self = new static;

        $softDelete = static::usesSoftDelete() && config('embeddings.soft_delete', false);

        $self->newQuery()
            ->when(true, function ($query) use ($self) {
                $self->makeAllEmbeddableUsing($query);
            })
            ->when($softDelete, function ($query) {
                $query->withTrashed();
            })
            ->orderBy(
                $self->qualifyColumn($self->getEmbedKeyName())
            )
            ->embeddable($chunk);
    }

    /**
     * Modify the collection of models being made embeddable.
     *
     * @return \Illuminate\Support\Collection
     */
    public function makeEmbeddableUsing(BaseCollection $models)
    {
        return $models;
    }

    /**
     * Modify the query used to retrieve models when making all of the models embeddable.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function makeAllEmbeddableUsing(EloquentBuilder $query)
    {
        return $query;
    }

    /**
     * Make the given model instance embeddable.
     *
     * @return void
     */
    public function embeddable()
    {
        $this->newCollection([$this])->embeddable();
    }

    /**
     * Remove all instances of the model from the embed index.
     *
     * @return void
     */
    public static function removeAllFromEmbed()
    {
        $self = new static;

        $self->embeddableUsing()->flush($self);
    }

    /**
     * Remove the given model instance from the embed index.
     *
     * @return void
     */
    public function unembeddable()
    {
        $this->newCollection([$this])->unembeddable();
    }

    /**
     * Determine if the model existed in the embed index prior to an update.
     *
     * @return bool
     */
    public function wasEmbeddableBeforeUpdate()
    {
        return true;
    }

    /**
     * Determine if the model existed in the embed index prior to deletion.
     *
     * @return bool
     */
    public function wasEmbeddableBeforeDelete()
    {
        return true;
    }

    /**
     * Enable embed syncing for this model.
     *
     * @return void
     */
    public static function enableEmbedSyncing()
    {
        ModelObserver::enableSyncingFor(get_called_class());
    }

    /**
     * Disable embed syncing for this model.
     *
     * @return void
     */
    public static function disableEmbedSyncing()
    {
        ModelObserver::disableSyncingFor(get_called_class());
    }

    /**
     * Temporarily disable embed syncing for the given callback.
     *
     * @param  callable  $callback
     * @return mixed
     */
    public static function withoutSyncingToEmbed($callback)
    {
        static::disableEmbedSyncing();

        try {
            return $callback();
        } finally {
            static::enableEmbedSyncing();
        }
    }

    /**
     * Get the index name for the model.
     *
     * @return string
     */
    public function embeddableAs()
    {
        return config('embeddings.prefix').$this->getTable();
    }

    /**
     * Get the indexable data string for the model.
     *
     * @return array
     */
    public function toEmbeddableString()
    {
        return strip_tags(implode(', ', $this->toArray()));
    }

    /**
     * Get the Embed engine for the model.
     *
     * @return mixed
     */
    public function embeddableUsing()
    {
        return app(EngineManager::class)->engine();
    }

    /**
     * Get the queue connection that should be used when syncing.
     *
     * @return string
     */
    public function syncWithEmbedUsing()
    {
        return config('embeddings.queue.connection') ?: config('queue.default');
    }

    /**
     * Get the queue that should be used with syncing.
     *
     * @return string
     */
    public function syncWithEmbedUsingQueue()
    {
        return config('embeddings.queue.queue');
    }

    /**
     * Get the value used to index the model.
     *
     * @return mixed
     */
    public function getEmbedKey()
    {
        return $this->getKey();
    }

    /**
     * Get the auto-incrementing key type for querying models.
     *
     * @return string
     */
    public function getEmbedKeyType()
    {
        return $this->getKeyType();
    }

    /**
     * Get the key name used to index the model.
     *
     * @return mixed
     */
    public function getEmbedKeyName()
    {
        return $this->getKeyName();
    }

    /**
     * Determine if the current class should use soft deletes with embedding.
     *
     * @return bool
     */
    protected static function usesSoftDelete()
    {
        return in_array(SoftDeletes::class, class_uses_recursive(get_called_class()));
    }
}
