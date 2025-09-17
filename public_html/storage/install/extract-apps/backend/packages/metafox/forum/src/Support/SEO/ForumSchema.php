<?php

namespace MetaFox\Forum\Support\SEO;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\Forum\Models\Forum;
use MetaFox\SEO\AbstractsSeoSchemaData;

/**
 * Class ForumSchema.
 */
class ForumSchema extends AbstractsSeoSchemaData
{
    public function buildStructured(mixed $data, ?Model $model): mixed
    {
        $results = [];
        if (!$model instanceof Forum) {
            return $results;
        }

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                Arr::set($results, $key, $this->buildStructured($value, $model));
                continue;
            }

            Arr::set($results, $key, $this->transformValue($value, $model));
        }
        return $results;
    }

    public function includesProperties(?string $key = null, bool $isRelation = false): array
    {
        $resources = $this->loadPropertiesSchema(Forum::ENTITY_TYPE);
        $results   = array_keys($resources);

        if (!$isRelation) {
            return $results;
        }

        $relationsSupported = ['threads' => 'forum_thread'];
        foreach ($relationsSupported as $key => $value) {
            $handler = $this->schemaRepository()->getHandler($value);
            $results = array_merge($results, $handler?->includesProperties($key) ?? []);
        }

        return $results;
    }
}
