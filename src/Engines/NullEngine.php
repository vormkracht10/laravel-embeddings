<?php

namespace Vormkracht10\Embedding\Engines;

class NullEngine implements EngineInterface
{
    public function __construct(public string $key, public string $model = 'text-embedding-ada-002')
    {

    }

    public function embed($content): array
    {
        return [];
    }

    /**
     * Update the given model
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    public function update($models)
    {
    }

    /**
     * Remove the given model from the index.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    public function delete($models)
    {
    }
}
