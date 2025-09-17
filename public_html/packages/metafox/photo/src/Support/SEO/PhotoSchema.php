<?php

namespace MetaFox\Photo\Support\SEO;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\Photo\Models\Album;
use MetaFox\Photo\Models\Photo;
use MetaFox\SEO\AbstractsSeoSchemaData;

/**
 * Class PhotoSchema.
 */
class PhotoSchema extends AbstractsSeoSchemaData
{
    public function buildStructured(mixed $data, ?Model $model): mixed
    {
        $results = [];

        if ($model instanceof Album) {
            return $this->buildStructuredItems($data, $model);
        }

        if ($model instanceof Photo) {
            return $this->handleBuildStructured($data, $model);
        }

        return $results;
    }

    public function handleBuildStructured(mixed $data, Photo $model): mixed
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
        if (!$model instanceof Album) {
            return $results;
        }

        $model->items->take(5)->each(function ($item) use (&$results, $data, $model) {
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

    private function handleValue(string $data, Album $model, $item): mixed
    {
        preg_match($this->getPatternCheckValueOrRelation(), $data, $matches);

        if (count($matches) == 3) {
            $model = $item;
        }

        return $this->transformValue($data, $model);
    }

    public function includesProperties(?string $key = null, bool $isRelation = false): array
    {
        $resources = $this->loadPropertiesSchema(Photo::ENTITY_TYPE);
        $results   = array_keys($resources);

        if (!$isRelation) {
            return $results;
        }

        $relationsSupported = ['categories' => 'photo_category'];
        foreach ($relationsSupported as $key => $value) {
            $handler = $this->schemaRepository()->getHandler($value);
            $results = array_merge($results, $handler?->includesProperties($key) ?? []);
        }

        return $results;
    }
}
