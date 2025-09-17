<?php

namespace MetaFox\Forum\Support\SEO;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\Forum\Models\ForumPost;
use MetaFox\Forum\Models\ForumThread;
use MetaFox\Platform\Contracts\Content;
use MetaFox\SEO\AbstractsSeoSchemaData;

/**
 * Class ForumPostSchema.
 */
class ForumPostSchema extends AbstractsSeoSchemaData
{
    public function buildStructured(mixed $data, ?Model $model): mixed
    {
        $results = [];

        if (!$model instanceof Content) {
            return $results;
        }

        if ($model instanceof ForumThread) {
            return $this->buildStructuredPost($data, $model);
        }

        return $results;
    }

    public function buildStructuredPost(mixed $data, ?Model $model): mixed
    {
        $results = [];
        if (!$model instanceof ForumThread) {
            return $results;
        }

        $model->posts->take(5)->each(function ($item) use (&$results, $data, $model) {
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

    private function handleValue(string $data, ForumThread $model, $category): mixed
    {
        preg_match($this->getPatternCheckValueOrRelation(), $data, $matches);

        if (count($matches) == 3) {
            $model = $category;
        }

        return $this->transformValue($data, $model);
    }

    public function includesProperties(?string $key = null, bool $isRelation = false): array
    {
        $resources = $this->loadPropertiesSchema(ForumPost::ENTITY_TYPE);

        if ($key) {
            $resources = Arr::dot([$key => $resources]);
        }

        return array_keys($resources);
    }
}
