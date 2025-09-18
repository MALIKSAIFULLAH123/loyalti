<?php

namespace MetaFox\Page\Support\SEO;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\Page\Models\Page;
use MetaFox\Platform\Contracts\Content;
use MetaFox\SEO\AbstractsSeoSchemaData;

/**
 * Class PageSchema.
 */
class PageSchema extends AbstractsSeoSchemaData
{
    public function buildStructured(mixed $data, ?Model $model): mixed
    {
        $results = [];
        if ($model instanceof Page) {
            return $this->handleBuildStructured($data, $model);
        }

        if (!$model instanceof Content) {
            return $results;
        }

        if ($model->owner instanceof Page) {
            return $this->handleBuildStructured($data, $model->owner);
        }

        return $results;
    }

    public function handleBuildStructured(mixed $data, Page $model): mixed
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
        $resources = $this->loadPropertiesSchema(Page::ENTITY_TYPE);
        $results   = array_keys($resources);

        if (!$isRelation) {
            return $results;
        }

        $relationsSupported = ['category' => 'page_category'];
        foreach ($relationsSupported as $key => $value) {
            $handler = $this->schemaRepository()->getHandler($value);
            $results = array_merge($results, $handler?->includesProperties($key) ?? []);
        }

        return $results;
    }
}
