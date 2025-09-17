<?php

namespace MetaFox\User\Support\SEO;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\SEO\AbstractsSeoSchemaData;
use MetaFox\User\Models\User as ModelsUser;

/**
 * Class UserSchema.
 */
class UserSchema extends AbstractsSeoSchemaData
{
    public function buildStructured(mixed $data, ?Model $model): mixed
    {
        $results = [];

        if ($model instanceof Content) {
            $model = $model->user;
        }

        if (!$model instanceof User) {
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
        $resources = $this->loadPropertiesSchema(ModelsUser::ENTITY_TYPE);

        if ($key) {
            $resources = Arr::dot([$key => $resources]);
        }

        return array_keys($resources);
    }
}
