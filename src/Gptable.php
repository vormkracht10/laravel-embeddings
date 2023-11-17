<?php

namespace Vormkracht10\LaravelGpt;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection as BaseCollection;

trait Gptable
{
    /**
     * Boot the trait.
     *
     * @return void
     */
    public static function bootGptable()
    {
        static::observe(new ModelObserver);

        (new static)->registerGptableMacros();
    }

    /**
     * Register the gptable macros.
     *
     * @return void
     */
    public function registerGptableMacros()
    {
        $self = $this;

        BaseCollection::macro('gptable', function () use ($self) {
            $self->queueMakeGptable($this);
        });

        BaseCollection::macro('ungptable', function () use ($self) {
            $self->queueRemoveFromGpt($this);
        });
    }

    /**
     * Dispatch the job to make the given models gptable.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    public function queueMakeGptable($models)
    {
        if ($models->isEmpty()) {
            return;
        }

        if (! config('gpt.queue')) {
            return $models->first()->makeGptableUsing($models)->first()->makeGptableUsing()->update($models);
        }

        dispatch((new Gpt::$makeGptableJob($models))
            ->onQueue($models->first()->syncWithGptUsingQueue())
            ->onConnection($models->first()->syncWithGptUsing()));
    }

    /**
     * Dispatch the job to make the given models ungptable.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    public function queueRemoveFromGpt($models)
    {
        if ($models->isEmpty()) {
            return;
        }

        if (! config('gpt.queue')) {
            return $models->first()->gptableUsing()->delete($models);
        }

        dispatch(new Gpt::$removeFromGptJob($models))
            ->onQueue($models->first()->syncWithGptUsingQueue())
            ->onConnection($models->first()->syncWithGptUsing());
    }

    /**
     * Determine if the model should be gptable.
     *
     * @return bool
     */
    public function shouldBeGptable()
    {
        return true;
    }

    /**
     * When updating a model, this method determines if we should update the gpt index.
     *
     * @return bool
     */
    public function gptIndexShouldBeUpdated()
    {
        return true;
    }

    /**
     * Make all instances of the model gptable.
     *
     * @param  int  $chunk
     * @return void
     */
    public static function makeAllGptable($chunk = null)
    {
        $self = new static;

        $softDelete = static::usesSoftDelete() && config('gpt.soft_delete', false);

        $self->newQuery()
            ->when(true, function ($query) use ($self) {
                $self->makeAllGptableUsing($query);
            })
            ->when($softDelete, function ($query) {
                $query->withTrashed();
            })
            ->orderBy(
                $self->qualifyColumn($self->getGptKeyName())
            )
            ->gptable($chunk);
    }

    /**
     * Modify the collection of models being made gptable.
     *
     * @return \Illuminate\Support\Collection
     */
    public function makeGptableUsing(BaseCollection $models)
    {
        return $models;
    }

    /**
     * Modify the query used to retrieve models when making all of the models gptable.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function makeAllGptableUsing(EloquentBuilder $query)
    {
        return $query;
    }

    /**
     * Make the given model instance gptable.
     *
     * @return void
     */
    public function gptable()
    {
        $this->newCollection([$this])->gptable();
    }

    /**
     * Remove all instances of the model from the gpt index.
     *
     * @return void
     */
    public static function removeAllFromGpt()
    {
        $self = new static;

        $self->gptableUsing()->flush($self);
    }

    /**
     * Remove the given model instance from the gpt index.
     *
     * @return void
     */
    public function ungptable()
    {
        $this->newCollection([$this])->ungptable();
    }

    /**
     * Determine if the model existed in the gpt index prior to an update.
     *
     * @return bool
     */
    public function wasGptableBeforeUpdate()
    {
        return true;
    }

    /**
     * Determine if the model existed in the gpt index prior to deletion.
     *
     * @return bool
     */
    public function wasGptableBeforeDelete()
    {
        return true;
    }

    /**
     * Enable gpt syncing for this model.
     *
     * @return void
     */
    public static function enableGptSyncing()
    {
        ModelObserver::enableSyncingFor(get_called_class());
    }

    /**
     * Disable gpt syncing for this model.
     *
     * @return void
     */
    public static function disableGptSyncing()
    {
        ModelObserver::disableSyncingFor(get_called_class());
    }

    /**
     * Temporarily disable gpt syncing for the given callback.
     *
     * @param  callable  $callback
     * @return mixed
     */
    public static function withoutSyncingToGpt($callback)
    {
        static::disableGptSyncing();

        try {
            return $callback();
        } finally {
            static::enableGptSyncing();
        }
    }

    /**
     * Get the index name for the model.
     *
     * @return string
     */
    public function gptableAs()
    {
        return config('gpt.prefix').$this->getTable();
    }

    /**
     * Get the indexable data string for the model.
     *
     * @return array
     */
    public function toGptableString()
    {
        return strip_tags(implode(', ', $this->contentable->toArray()) . implode(', ', $this->toArray()));
    }

    /**
     * Get the Gpt engine for the model.
     *
     * @return mixed
     */
    public function gptableUsing()
    {
        return app(EngineManager::class)->engine();
    }

    /**
     * Get the queue connection that should be used when syncing.
     *
     * @return string
     */
    public function syncWithGptUsing()
    {
        return config('gpt.queue.connection') ?: config('queue.default');
    }

    /**
     * Get the queue that should be used with syncing.
     *
     * @return string
     */
    public function syncWithGptUsingQueue()
    {
        return config('gpt.queue.queue');
    }

    /**
     * Get the value used to index the model.
     *
     * @return mixed
     */
    public function getGptKey()
    {
        return $this->getKey();
    }

    /**
     * Get the auto-incrementing key type for querying models.
     *
     * @return string
     */
    public function getGptKeyType()
    {
        return $this->getKeyType();
    }

    /**
     * Get the key name used to index the model.
     *
     * @return mixed
     */
    public function getGptKeyName()
    {
        return $this->getKeyName();
    }

    /**
     * Determine if the current class should use soft deletes with gpting.
     *
     * @return bool
     */
    protected static function usesSoftDelete()
    {
        return in_array(SoftDeletes::class, class_uses_recursive(get_called_class()));
    }
}
