<?php

namespace Vormkracht10\Embedding\Engines;

interface EngineInterface
{
    /**
     * Update the given model in the index.
     *
     * @param  string  $content
     * @return array $embedding
     */
    public function embed($models): array;
}
