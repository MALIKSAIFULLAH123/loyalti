<?php

namespace MetaFox\Group\Support\SEO;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\Group\Models\Group;
use MetaFox\Platform\Contracts\Content;
use MetaFox\SEO\AbstractsSeoSchemaData;

/**
 * Class GroupSchema.
 */
class GroupSchema extends AbstractsSeoSchemaData
{
    public function buildStructured(mixed $data, ?Model $model): mixed
    {
        $results = [];
        if ($model instanceof Group) {
            return $this->handleBuildStructured($data, $model);
        }

        if (!$model instanceof Content) {
            return $results;
        }

        if ($model->owner instanceof Group) {
            return $this->handleBuildStructured($data, $model->owner);
        }

        return $results;
    }

    public function handleBuildStructured(mixed $data, Group $model): mixed
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
        $resources = $this->loadPropertiesSchema(Group::ENTITY_TYPE);

        return array_keys($resources);
    }
}
