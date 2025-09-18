<?php

namespace MetaFox\LiveStreaming\Listeners;

use Carbon\Carbon;
use MetaFox\LiveStreaming\Models\LiveVideo;
use MetaFox\LiveStreaming\Repositories\LiveVideoRepositoryInterface;
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
                'name'  => LiveVideo::ENTITY_TYPE,
                'label' => 'livestreaming::phrase.live_video_stat_label',
                'value' => resolve(LiveVideoRepositoryInterface::class)->getTotalItemByPeriod($after, $before),
            ],
        ];
    }
}
