<?php

namespace MetaFox\Comment\Listeners;

use Carbon\Carbon;
use MetaFox\Comment\Models\Comment;
use MetaFox\Comment\Repositories\CommentRepositoryInterface;
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
                'name'  => Comment::ENTITY_TYPE,
                'label' => 'comment::phrase.comment_stat_label',
                'value' => resolve(CommentRepositoryInterface::class)->getTotalItemByPeriod($after, $before),
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
        ];

        return [
            [
                'name'  => 'pending_comment',
                'label' => 'comment::phrase.comment_stat_label',
                'value' => resolve(CommentRepositoryInterface::class)->getTotalPendingItemByPeriod(null, null, $conditions),
                'group' => 'pending',
            ],
        ];
    }
}
