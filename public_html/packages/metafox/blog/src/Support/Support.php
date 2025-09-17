<?php

namespace MetaFox\Blog\Support;

use MetaFox\Blog\Models\Blog;

/**
 * Class CacheManager.
 */
class Support
{
    public static function getStatusTexts(Blog $blog): array
    {
        if ($blog->isDraft()) {
            return [
                'label' => __p('blog::phrase.draft'),
                'color' => null,
            ];
        }

        if ($blog->isApproved()) {
            return [
                'label' => __p('core::phrase.approved'),
                'color' => null,
            ];
        }
        
        return [
            'label' => __p('core::phrase.pending'),
            'color' => null,
        ];
    }
}
