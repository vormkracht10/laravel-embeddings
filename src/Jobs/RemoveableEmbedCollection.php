<?php

namespace Vormkracht10\Embedding\Jobs;

use Illuminate\Database\Eloquent\Collection;

class RemoveableEmbedCollection extends Collection
{
    /**
     * Get the Embed identifiers for all of the entities.
     *
     * @return array
     */
    public function getQueueableIds()
    {
        if ($this->isEmpty()) {
            return [];
        }

        return in_array(Embeddable::class, class_uses_recursive($this->first()))
                    ? $this->map->getEmbedKey()->all()
                    : parent::getQueueableIds();
    }
}
