<?php

namespace MetaFox\Music\Support\SEO;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\Music\Models\Album;
use MetaFox\Music\Models\Playlist;
use MetaFox\Music\Models\Song;
use MetaFox\SEO\AbstractsSeoSchemaData;

/**
 * Class PhotoSchema.
 */
class SongSchema extends AbstractsSeoSchemaData
{
    public function buildStructured(mixed $data, ?Model $model): mixed
    {
        $results = [];
        if ($model instanceof Album || $model instanceof Playlist) {
            return $this->buildStructuredItems($data, $model);
        }

        if ($model instanceof Song) {
            return $this->handleBuildStructured($data, $model);
        }

        return $results;
    }

    public function handleBuildStructured(mixed $data, Song $model): mixed
    {
        $results = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                Arr::set($results, $key, $this->handleBuildStructured($value, $model));
                continue;
            }

            Arr::set($results, $key, $this->transformValue($value, $model));
        }
        return $results;
    }

    public function buildStructuredItems(mixed $data, ?Model $model): mixed
    {
        $results = [];
        if (!$model instanceof Album && !$model instanceof Playlist) {
            return $results;
        }
        
        $model->songs->take(5)->each(function ($item) use (&$results, $data, $model) {
            $result = [];

            if (is_string($data)) {
                return $results[] = $this->handleValue($data, $model, $item);
            }

            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    continue;
                }

                Arr::set($result, $key, $this->handleValue($value, $model, $item));
            }

            return $results[] = $result;
        });

        return $results;
    }

    private function handleValue(string $data, Model $model, $item): mixed
    {
        preg_match($this->getPatternCheckValueOrRelation(), $data, $matches);

        if (count($matches) == 3) {
            $model = $item;
        }

        return $this->transformValue($data, $model);
    }

    public function includesProperties(?string $key = null, bool $isRelation = false): array
    {
        $resources = $this->loadPropertiesSchema(Song::ENTITY_TYPE);
        $results   = array_keys($resources);

        if (!$isRelation) {
            return $results;
        }

        $relationsSupported = ['genres' => 'music_genre'];
        foreach ($relationsSupported as $key => $value) {
            $handler = $this->schemaRepository()->getHandler($value);
            $results = array_merge($results, $handler?->includesProperties($key) ?? []);
        }

        return $results;
    }
}
