<?php

namespace MetaFox\Blog\Support\SEO;

use MetaFox\Blog\Models\Blog;
use MetaFox\SEO\AbstractsSeoSchemaData;

/**
 * Class BlogSchema.
 */
class BlogSchema extends AbstractsSeoSchemaData
{
    public function includesProperties(?string $key = null, bool $isRelation = false): array
    {
        $resources = $this->loadPropertiesSchema(Blog::ENTITY_TYPE);
        $resources = array_keys($resources);

        if (!$isRelation) {
            return $resources;
        }

        $relationsSupported = ['categories' => 'blog_category'];
        foreach ($relationsSupported as $key => $value) {
            $handler   = $this->schemaRepository()->getHandler($value);
            $resources = array_merge($resources, $handler?->includesProperties($key) ?? []);
        }

        return $resources;
    }
}
