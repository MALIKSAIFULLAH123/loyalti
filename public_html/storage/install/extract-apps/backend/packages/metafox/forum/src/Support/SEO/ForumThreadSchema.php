<?php

namespace MetaFox\Forum\Support\SEO;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\Forum\Models\Forum;
use MetaFox\Forum\Models\ForumThread;
use MetaFox\SEO\AbstractsSeoSchemaData;

/**
 * Class ForumThreadSchema.
 */
class ForumThreadSchema extends AbstractsSeoSchemaData
{
    public function buildStructured(mixed $data, ?Model $model): mixed
    {
        $results = [];

        if ($model instanceof Forum) {
            return $this->buildStructuredThread($data, $model);
        }

        if ($model instanceof ForumThread) {
            return $this->handleBuildStructured($data, $model);
        }

        return $results;
    }

    public function handleBuildStructured(mixed $data, ForumThread $model): mixed
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

    public function buildStructuredThread(mixed $data, ?Model $model): mixed
    {
        $results = [];
        if (!$model instanceof Forum) {
            return $results;
        }

        $model->threads->take(5)->each(function ($item) use (&$results, $data, $model) {
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

    private function handleValue(string $data, Forum $model, $item): mixed
    {
        preg_match($this->getPatternCheckValueOrRelation(), $data, $matches);

        if (count($matches) == 3) {
            $model = $item;
        }

        return $this->transformValue($data, $model);
    }

    public function includesProperties(?string $key = null, bool $isRelation = false): array
    {
        $resources = $this->loadPropertiesSchema(ForumThread::ENTITY_TYPE);

        if ($key) {
            $resources = Arr::dot([$key => $resources]);
        }

        $results = array_keys($resources);

        if (!$isRelation) {
            return $results;
        }

        $relationsSupported = ['posts' => 'forum_post'];
        foreach ($relationsSupported as $key => $value) {
            $handler = $this->schemaRepository()->getHandler($value);
            $results = array_merge($results, $handler?->includesProperties($key) ?? []);
        }

        return $results;
    }
}
