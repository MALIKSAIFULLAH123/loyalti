<?php

namespace MetaFox\Poll\Listeners;

use Carbon\Carbon;
use MetaFox\Poll\Models\Poll;
use MetaFox\Poll\Repositories\PollRepositoryInterface;
use MetaFox\Core\Listeners\Abstracts\AbstractCollectTotalItemStatListener as AbstractClass;
use MetaFox\Poll\Support\Facade\Poll as PollFacade;

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
                'name'  => Poll::ENTITY_TYPE,
                'label' => 'poll::phrase.poll_stat_label',
                'value' => resolve(PollRepositoryInterface::class)->getTotalItemByPeriod($after, $before),
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
            ['view_id', '<>', PollFacade::getIntegrationViewId()],
        ];

        return [
            [
                'name'  => 'pending_poll',
                'label' => 'poll::phrase.poll_stat_label',
                'value' => resolve(PollRepositoryInterface::class)->getTotalPendingItemByPeriod(null, null, $conditions),
            ],
        ];
    }
}
