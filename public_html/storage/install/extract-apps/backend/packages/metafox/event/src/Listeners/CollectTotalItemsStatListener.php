<?php

namespace MetaFox\Event\Listeners;

use Carbon\Carbon;
use MetaFox\Core\Listeners\Abstracts\AbstractCollectTotalItemStatListener as AbstractClass;
use MetaFox\Event\Models\Event;
use MetaFox\Event\Repositories\EventRepositoryInterface;

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
                'name'  => Event::ENTITY_TYPE,
                'label' => 'event::phrase.event_stat_label',
                'value' => resolve(EventRepositoryInterface::class)->getTotalItemByPeriod($after, $before),
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
                'name'  => 'pending_event',
                'label' => 'event::phrase.event_stat_label',
                'value' => resolve(EventRepositoryInterface::class)->getTotalPendingItemByPeriod(),
                'group' => 'pending',
            ],
        ];
    }
}
