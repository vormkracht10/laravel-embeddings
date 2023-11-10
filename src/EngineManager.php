<?php

namespace Vormkracht10\LaravelGpt;

use Exception;
use Illuminate\Support\Manager;
use Vormkracht10\LaravelGpt\Engines\OpenAiEngine;

class EngineManager extends Manager
{
    /**
     * Get a driver instance.
     *
     * @param  string|null  $name
     * @return \Vormkracht10\Gpt\Engines\EngineInterface
     */
    public function engine($name = null)
    {
        return $this->driver($name);
    }

    /**
     * Create an Algolia engine instance.
     *
     * @return \Vormkracht10\Gpt\Engines\OpenAiEngine
     */
    public function createOpenAiDriver()
    {
        $this->ensureOpenAiIsConfigured();

        return new OpenAiEngine(config('gpt.openai.key'));
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
        if (empty(config('gpt.openai.key'))) {
            throw new \Exception(__('Please fiill the :config key.', ['config' => 'gpt.openai.key']));
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
     * Get the default Gpt driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        if (is_null($driver = config('gpt.driver'))) {
            return 'openai';
        }

        return $driver;
    }
}
