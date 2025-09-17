<?php

namespace MetaFox\Video\Support\SEO;

use MetaFox\SEO\AbstractsSeoSchemaData;
use MetaFox\Video\Models\Video;

/**
 * Class VideoSchema.
 */
class VideoSchema extends AbstractsSeoSchemaData
{
    public function includesProperties(?string $key = null, bool $isRelation = false): array
    {
        $resources = $this->loadPropertiesSchema(Video::ENTITY_TYPE);
        $resources = array_keys($resources);

        if (!$isRelation) {
            return $resources;
        }

        $relationsSupported = ['categories' => 'video_category'];
        foreach ($relationsSupported as $key => $value) {
            $handler   = $this->schemaRepository()->getHandler($value);
            $resources = array_merge($resources, $handler?->includesProperties($key) ?? []);
        }

        return $resources;
    }
}
