<?php

namespace MetaFox\Music\Support\SEO;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\Music\Models\Genre;
use MetaFox\SEO\AbstractsSeoSchemaData;

/**
 * Class CategorySchema.
 */
class GenreSchema extends AbstractsSeoSchemaData
{
    public function buildStructured(mixed $data, ?Model $model): mixed
    {
        $results = [];
        if (!$model instanceof Model) {
            return $results;
        }

        if (!$model->isRelation('genres')) {
            return $results;
        }

        $model->genres->take(5)->each(function ($category) use (&$results, $data, $model) {
            $result = [];

            if (is_string($data)) {
                return $results[] = $this->handleValue($data, $model, $category);
            }

            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    continue;
                }

                Arr::set($result, $key, $this->handleValue($value, $model, $category));
            }

            return $results[] = $result;
        });

        return $results;
    }

    private function handleValue(string $data, Model $model, $category): mixed
    {
        preg_match($this->getPatternCheckValueOrRelation(), $data, $matches);

        if (count($matches) == 3) {
            $model = $category;
        }

        return $this->transformValue($data, $model);
    }

    public function includesProperties(?string $key = null, bool $isRelation = false): array
    {
        $resources = $this->loadPropertiesSchema(Genre::ENTITY_TYPE);

        if ($key) {
            $resources = Arr::dot([$key => $resources]);
        }

        return array_keys($resources);
    }
}
