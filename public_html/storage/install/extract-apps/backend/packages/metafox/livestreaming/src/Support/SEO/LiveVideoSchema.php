<?php

namespace MetaFox\LiveStreaming\Support\SEO;

use MetaFox\LiveStreaming\Models\LiveVideo;
use MetaFox\SEO\AbstractsSeoSchemaData;

/**
 * Class LiveVideoSchema.
 */
class LiveVideoSchema extends AbstractsSeoSchemaData
{
    public function includesProperties(?string $key = null, bool $isRelation = false): array
    {
        $resources = $this->loadPropertiesSchema(LiveVideo::ENTITY_TYPE);

        return array_keys($resources);
    }
}
