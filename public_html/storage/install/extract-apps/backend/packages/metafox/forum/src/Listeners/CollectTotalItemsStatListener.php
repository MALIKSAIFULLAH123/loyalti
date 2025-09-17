<?php

namespace MetaFox\Forum\Listeners;

use Carbon\Carbon;
use MetaFox\Forum\Models\ForumPost;
use MetaFox\Forum\Models\ForumThread;
use MetaFox\Forum\Repositories\ForumPostRepositoryInterface;
use MetaFox\Forum\Repositories\ForumThreadRepositoryInterface;
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
                'name'  => ForumPost::ENTITY_TYPE,
                'label' => 'forum::phrase.forum_post_stat_label',
                'value' => resolve(ForumPostRepositoryInterface::class)->getTotalItemByPeriod($after, $before),
            ],
            [
                'name'  => ForumThread::ENTITY_TYPE,
                'label' => 'forum::phrase.forum_thread_stat_label',
                'value' => resolve(ForumThreadRepositoryInterface::class)->getTotalItemByPeriod($after, $before),
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
        return [
            [
                'name'  => 'pending_forum_post',
                'label' => 'forum::phrase.forum_post_stat_label',
                'value' => resolve(ForumPostRepositoryInterface::class)->getTotalPendingItemByPeriod(),
                'group' => 'pending',
            ],
            [
                'name'  => 'pending_forum_thread',
                'label' => 'forum::phrase.forum_thread_stat_label',
                'value' => resolve(ForumThreadRepositoryInterface::class)->getTotalPendingItemByPeriod(),
                'group' => 'pending',
            ],
        ];
    }
}
