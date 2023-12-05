<?php

namespace Vormkracht10\Embedding;

use Illuminate\Support\Manager;
use Vormkracht10\Embedding\Engines\OpenAiEngine;

class EngineManager extends Manager
{
    /**
     * Get a driver instance.
     *
     * @param  string|null  $name
     * @return \Vormkracht10\Embedding\Engines\EngineInterface
     */
    public function engine($name = null)
    {
        return $this->driver($name);
    }

    /**
     * Create an Algolia engine instance.
     *
     * @return \Vormkracht10\Embedding\Engines\OpenAiEngine
     */
    public function createOpenAiDriver()
    {
        $this->ensureOpenAiIsConfigured();

        return new OpenAiEngine(config('embeddings.openai.key'));
    }

    /**
     * Ensure the OpenAI API client is installed.
     *
     * @return void
     *
     * @throws \Exception
     */
    protected function ensureOpenAiIsConfigured()
    {
        if (empty(config('embeddings.openai.key'))) {
            throw new \Exception(__('Please fill the :config key.', ['config' => 'embed.openai.key']));
        }
    }

    /**
     * Forget all of the resolved engine instances.
     *
     * @return $this
     */
    public function forgetEngines()
    {
        $this->drivers = [];

        return $this;
    }

    /**
     * Get the default Embed driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        if (is_null($driver = config('embeddings.driver'))) {
            return 'openai';
        }

        return $driver;
    }
}
