<?php

namespace MetaFox\Poll\Support\SEO;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Poll\Models\Poll;
use MetaFox\SEO\AbstractsSeoSchemaData;

/**
 * Class PollSchema.
 */
class PollSchema extends AbstractsSeoSchemaData
{
    public function buildStructured(mixed $data, ?Model $model): mixed
    {
        $results = [];
        if (!$model instanceof Content) {
            return $results;
        }

        if ($model instanceof Poll) {
            return $this->handleBuildStructured($data, $model);
        }

        return $results;
    }

    public function handleBuildStructured(mixed $data, Poll $model): mixed
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

    public function includesProperties(?string $key = null, bool $isRelation = false): array
    {
        $resources = $this->loadPropertiesSchema(Poll::ENTITY_TYPE);

        return array_keys($resources);
    }
}
