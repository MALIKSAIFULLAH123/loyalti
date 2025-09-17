<?php

namespace MetaFox\Video\Listeners;

use Carbon\Carbon;
use MetaFox\Video\Models\Video;
use MetaFox\Video\Repositories\VideoRepositoryInterface;
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
                'name'  => Video::ENTITY_TYPE,
                'label' => 'video::phrase.video_stat_label',
                'value' => resolve(VideoRepositoryInterface::class)->getTotalItemByPeriod($after, $before),
            ],
        ];
    }

    /**
     * @param  Carbon|null            $after
     * @param  Carbon|null            $before
     * @return array<int, mixed>|null
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getPendingStats(?Carbon $after = null, ?Carbon $before = null): ?array
    {
        return [
            [
                'name'  => 'pending_video',
                'label' => 'video::phrase.video_stat_label',
                'value' => resolve(VideoRepositoryInterface::class)->getTotalPendingItemByPeriod(),
            ],
        ];
    }
}
