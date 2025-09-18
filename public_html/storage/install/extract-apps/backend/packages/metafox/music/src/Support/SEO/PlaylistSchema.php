<?php

namespace MetaFox\Music\Support\SEO;

use MetaFox\Music\Models\Playlist;
use MetaFox\SEO\AbstractsSeoSchemaData;

/**
 * Class PlaylistSchema.
 */
class PlaylistSchema extends AbstractsSeoSchemaData
{
    public function includesProperties(?string $key = null, bool $isRelation = false): array
    {
        $resources = $this->loadPropertiesSchema(Playlist::ENTITY_TYPE);

        return array_keys($resources);
    }
}
