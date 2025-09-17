<?php

namespace MetaFox\Event\Support\SEO;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\Event\Models\Event;
use MetaFox\Platform\Contracts\Content;
use MetaFox\SEO\AbstractsSeoSchemaData;

/**
 * Class EventSchema.
 */
class EventSchema extends AbstractsSeoSchemaData
{
    public function buildStructured(mixed $data, ?Model $model): mixed
    {
        $results = [];
        if ($model instanceof Event) {
            return $this->handleBuildStructured($data, $model);
        }

        if (!$model instanceof Content) {
            return $results;
        }

        if ($model->owner instanceof Event) {
            return $this->handleBuildStructured($data, $model->owner);
        }

        return $results;
    }

    public function handleBuildStructured(mixed $data, Event $model): mixed
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
        $resources = $this->loadPropertiesSchema(Event::ENTITY_TYPE);
        $results   = array_keys($resources);

        if (!$isRelation) {
            return $results;
        }

        $relationsSupported = ['categories' => 'event_category'];
        foreach ($relationsSupported as $key => $value) {
            $handler = $this->schemaRepository()->getHandler($value);
            $results = array_merge($results, $handler?->includesProperties($key) ?? []);
        }

        return $results;
    }
}
