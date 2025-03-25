<?php

namespace Vormkracht10\Embedding\Engines;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class OpenAiEngine implements EngineInterface
{
    private $apiUrl = 'https://api.openai.com/v1';

    public function __construct(public string $key, public string $model = 'text-embedding-ada-002') {}

    public function embed($content): array
    {
        $response = Http::withToken($this->key)
            ->post($this->apiUrl.'/embeddings', [
                'input' => $content,
                'model' => $this->model,
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
     */
    public function update($models)
    {
        if ($models->isEmpty()) {
            return;
        }

        $objects = $models->map(function ($model) {
            if (empty($contentString = $model->toEmbeddableString())) {
                return;
            }

            return [
                'objectID' => $model->getEmbedKey(),
                'content' => $contentString,
            ];
        })->filter()->values()->all();

        if (! empty($objects)) {
            $this->saveObjects($objects);
        }
    }

    private function saveObjects($objects)
    {
        foreach ($objects as $object) {
            dd($objects);
            if ((int) config('embeddings.openai.chunk') > 0) {
                $chunks = str_split($object['content'], (int) config('embeddings.openai.chunk'));
            } else {
                $chunks = [$object['content']];
            }

            if (count($chunks) > 1) {
                DB::connection(config('embeddings.database.connection'))
                    ->table(config('embeddings.database.table'))
                    ->where('foreign_id', $object['objectID'])
                    ->delete();
            }

            foreach ($chunks as $chunk) {
                $exists = DB::connection(config('embeddings.database.connection'))
                    ->table(config('embeddings.database.table'))
                    ->where('foreign_id', $object['objectID'])
                    ->where('content', $chunk)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $embed = $this->embed($chunk);

                if (count($chunks) > 1) {
                    // Dont update previous chunks
                    DB::connection(config('embeddings.database.connection'))
                        ->table(config('embeddings.database.table'))
                        ->insert([
                            'foreign_id' => $object['objectID'],
                            'content' => $chunk,
                            'embedding' => '['.implode(',', $embed).']',
                        ]);
                } else {
                    DB::connection(config('embeddings.database.connection'))
                        ->table(config('embeddings.database.table'))
                        ->updateOrInsert([
                            'foreign_id' => $object['objectID'],
                        ], [
                            'content' => $chunk,
                            'embedding' => '['.implode(',', $embed).']',
                        ]);
                }
            }
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
            DB::connection(config('embeddings.database.connection'))
                ->table(config('embeddings.database.table'))
                ->where('foreign_id', $model->getEmbedKey())
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
