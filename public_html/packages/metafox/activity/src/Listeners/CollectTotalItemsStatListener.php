<?php

namespace MetaFox\Activity\Listeners;

use Carbon\Carbon;
use MetaFox\Activity\Models\Post;
use MetaFox\Activity\Repositories\PostRepositoryInterface;
use MetaFox\Core\Listeners\Abstracts\AbstractCollectTotalItemStatListener as AbstractClass;

class CollectTotalItemsStatListener extends AbstractClass
{
    /**
     * @param  Carbon|null            $after
     * @param  Carbon|null            $before
     * @return array<int, mixed>|null
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getDefaultStats(?Carbon $after = null, ?Carbon $before = null): ?array
    {
        return [
            [
                'name'  => Post::ENTITY_TYPE,
                'label' => 'activity::phrase.activity_post_stat_label',
                'value' => resolve(PostRepositoryInterface::class)->getTotalItemByPeriod($after, $before),
            ],
        ];
    }
}
