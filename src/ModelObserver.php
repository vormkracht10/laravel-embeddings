<?php

namespace Vormkracht10\LaravelGpt;

use Closure;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;

class ModelObserver
{
    /**
     * Indicates if the model is currently force saving.
     *
     * @var bool
     */
    protected $forceSaving = false;

    /**
     * The class names that syncing is disabled for.
     *
     * @var array
     */
    protected static $syncingDisabledFor = [];

    /**
     * Indicates if Gpt will keep soft deleted records in the gpt indexes.
     *
     * @var bool
     */
    protected $usingSoftDeletes;

    /**
     * Create a new observer instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->usingSoftDeletes = Config::get('gpt.soft_delete', false);
    }

    /**
     * Enable syncing for the given class.
     *
     * @param  string  $class
     * @return void
     */
    public static function enableSyncingFor($class)
    {
        unset(static::$syncingDisabledFor[$class]);
    }

    /**
     * Disable syncing for the given class.
     *
     * @param  string  $class
     * @return void
     */
    public static function disableSyncingFor($class)
    {
        static::$syncingDisabledFor[$class] = true;
    }

    /**
     * Determine if syncing is disabled for the given class or model.
     *
     * @param  object|string  $class
     * @return bool
     */
    public static function syncingDisabledFor($class)
    {
        $class = is_object($class) ? get_class($class) : $class;

        return isset(static::$syncingDisabledFor[$class]);
    }

    /**
     * Handle the saved event for the model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function saved($model)
    {
        if (! $this->forceSaving && ! $model->gptIndexShouldBeUpdated()) {
            return;
        }

        if (! $model->shouldBeGptable()) {
            $model->ungptable();

            return;
        }

        $model->gptable();
    }

    /**
     * Handle the deleted event for the model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function deleted($model)
    {
        if (static::syncingDisabledFor($model)) {
            return;
        }

        if ($this->usingSoftDeletes && $this->usesSoftDelete($model)) {
            $this->whileForcingUpdate(function () use ($model) {
                $this->saved($model);
            });
        } else {
            $model->ungptable();
        }
    }

    /**
     * Handle the force deleted event for the model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function forceDeleted($model)
    {
        if (static::syncingDisabledFor($model)) {
            return;
        }

        $model->ungptable();
    }

    /**
     * Handle the restored event for the model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function restored($model)
    {
        $this->whileForcingUpdate(function () use ($model) {
            $this->saved($model);
        });
    }

    /**
     * Execute the given callback while forcing updates.
     *
     * @return mixed
     */
    protected function whileForcingUpdate(Closure $callback)
    {
        $this->forceSaving = true;

        $result = $callback();

        $this->forceSaving = false;

        return $result;
    }

    /**
     * Determine if the given model uses soft deletes.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return bool
     */
    protected function usesSoftDelete($model)
    {
        return in_array(SoftDeletes::class, class_uses_recursive($model));
    }
}
