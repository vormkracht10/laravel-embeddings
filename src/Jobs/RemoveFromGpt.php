<?php

namespace Vormkracht10\LaravelGpt\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class RemoveFromGpt implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The models to be removed from the search index.
     *
     * @var \Vormkracht10\LaravelGpt\Jobs\RemoveableGptCollection
     */
    public $models;

    /**
     * Create a new job instance.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    public function __construct($models)
    {
        $this->models = RemoveableGptCollection::make($models);
    }

    /**
     * Handle the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->models->isNotEmpty()) {
            $this->models->first()->gptableUsing()->delete($this->models);
        }
    }

    /**
     * Restore a queueable collection instance.
     *
     * @param  \Illuminate\Contracts\Database\ModelIdentifier  $value
     * @return \Vormkracht10\LaravelGpt\Jobs\RemoveableGptCollection
     */
    protected function restoreCollection($value)
    {
        if (! $value->class || count($value->id) === 0) {
            return new RemoveableGptCollection;
        }

        return new RemoveableGptCollection(
            collect($value->id)->map(function ($id) use ($value) {
                return tap(new $value->class, function ($model) use ($id) {
                    $model->setKeyType(
                        is_string($id) ? 'string' : 'int'
                    )->forceFill([
                        $model->getGptKeyName() => $id,
                    ]);
                });
            })
        );
    }
}
