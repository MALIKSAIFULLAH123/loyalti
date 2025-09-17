<?php

namespace MetaFox\Blog\Listeners;

use Carbon\Carbon;
use MetaFox\Blog\Models\Blog;
use MetaFox\Blog\Repositories\BlogRepositoryInterface;
use MetaFox\Core\Listeners\Abstracts\AbstractCollectTotalItemStatListener as AbstractClass;

class CollectTotalItemsStatListener extends AbstractClass
{
    /**
     * @param  Carbon|null            $after
     * @param  Carbon|null            $before
     * @return array<int, mixed>|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getDefaultStats(?Carbon $after = null, ?Carbon $before = null): ?array
    {
        return [
            [
                'name'  => Blog::ENTITY_TYPE,
                'label' => 'blog::phrase.blog_stat_label',
                'value' => resolve(BlogRepositoryInterface::class)->getTotalItemByPeriod($after, $before),
            ],
        ];
    }

    /**
     * @param  Carbon|null            $after
     * @param  Carbon|null            $before
     * @return array<int, mixed>|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getPendingStats(?Carbon $after = null, ?Carbon $before = null): ?array
    {
        $conditions = [
            'is_approved' => 0,
            'is_draft'    => 0,
        ];

        return [
            [
                'name'  => 'pending_blog',
                'label' => 'blog::phrase.blog_stat_label',
                'value' => resolve(BlogRepositoryInterface::class)->getTotalPendingItemByPeriod(null, null, $conditions),
                'group' => 'pending',
            ],
        ];
    }
}
