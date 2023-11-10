<?php

namespace Vormkracht10\LaravelGpt\Jobs;

use Illuminate\Database\Eloquent\Collection;

class RemoveableGptCollection extends Collection
{
    /**
     * Get the Gpt identifiers for all of the entities.
     *
     * @return array
     */
    public function getQueueableIds()
    {
        if ($this->isEmpty()) {
            return [];
        }

        return in_array(Gptable::class, class_uses_recursive($this->first()))
                    ? $this->map->getGptKey()->all()
                    : parent::getQueueableIds();
    }
}
