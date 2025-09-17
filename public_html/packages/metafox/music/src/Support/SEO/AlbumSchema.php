<?php

namespace MetaFox\Music\Support\SEO;

use MetaFox\Music\Models\Album;
use MetaFox\SEO\AbstractsSeoSchemaData;

/**
 * Class AlbumSchema.
 */
class AlbumSchema extends AbstractsSeoSchemaData
{
    public function includesProperties(?string $key = null, bool $isRelation = false): array
    {
        $resources = $this->loadPropertiesSchema(Album::ENTITY_TYPE);
        $results   = array_keys($resources);

        if (!$isRelation) {
            return $results;
        }

        $relationsSupported = ['genres' => 'music_genre'];
        foreach ($relationsSupported as $key => $value) {
            $handler = $this->schemaRepository()->getHandler($value);
            $results = array_merge($results, $handler?->includesProperties($key) ?? []);
        }

        return $results;
    }
}
