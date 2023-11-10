<?php

namespace Vormkracht10\LaravelGpt\Engines;

use Illuminate\Database\Eloquent\SoftDeletes;

class OpenAiEngine implements EngineInterface
{
    private $apiUrl = 'https://api.openai.com/v1';

    public function __construct(public string $key, public string $model = 'text-embedding-ada-002') {

    }

    public function embed($content) : array {

        dd('here we go', $content);
        $response = Http::withToken($this->key)
            ->post($this->apiUrl . '/embeddings', [
                'input' => $content,
                'model' => $this->model
            ])
            ->throw()
            ->json();

        return $response['data'][0]['embedding'];
    }

    /**
     * Update the given model
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     *
     */
    public function update($models)
    {
        if ($models->isEmpty()) {
            return;
        }

        $objects = $models->map(function ($model) {
            if (empty($contentString = $model->toSearchableString())) {
                return;
            }

            return [
                'objectID' => $model->getGptKey(),
                'content' => $contentString
            ];
        })->filter()->values()->all();

        if (! empty($objects)) {
            $this->saveObjects($objects);
        }
    }

    private function saveObjects($objects) {
        foreach ($objects as $object) {
            
            $embed = $this->embed($object['content']);

            DB::connection(config('gpt.database.connection'))
                ->table(config('gpt.database.table'))
                ->updateOrInsert([
                    'foreign_id' => $object['objectID']
                ], [
                    'content' => $object['content'],
                    'embed' => $embed
                ]);
        }
    }

    /**
     * Remove the given model from the index.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    public function delete($models)
    {
        foreach ($models as $model) {
            DB::connection(config('gpt.database.connection'))
                ->table(config('gpt.database.table'))
                ->where('foreign_id', $model->getGptKey())
                ->delete();
        }
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
