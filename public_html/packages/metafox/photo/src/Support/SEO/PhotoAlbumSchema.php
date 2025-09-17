<?php

namespace MetaFox\Photo\Support\SEO;

use MetaFox\Photo\Models\Album;
use MetaFox\SEO\AbstractsSeoSchemaData;

/**
 * Class PhotoAlbumSchema.
 */
class PhotoAlbumSchema extends AbstractsSeoSchemaData
{
    public function includesProperties(?string $key = null, bool $isRelation = false): array
    {
        $resources = $this->loadPropertiesSchema(Album::ENTITY_TYPE);

        return array_keys($resources);
    }
}
