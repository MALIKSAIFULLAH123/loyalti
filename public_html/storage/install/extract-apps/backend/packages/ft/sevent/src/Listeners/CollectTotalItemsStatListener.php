<?php

namespace Foxexpert\Sevent\Listeners;

use Carbon\Carbon;
use Foxexpert\Sevent\Models\Sevent;
use Foxexpert\Sevent\Repositories\SeventRepositoryInterface;
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
                'name'  => Sevent::ENTITY_TYPE,
                'label' => 'sevent::phrase.sevent_stat_label',
                'value' => resolve(SeventRepositoryInterface::class)->getTotalItemByPeriod($after, $before),
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
                'name'  => 'pending_sevent',
                'label' => 'sevent::phrase.sevent_stat_label',
                'value' => resolve(SeventRepositoryInterface::class)->getTotalPendingItemByPeriod(null, null, $conditions),
                'group' => 'pending',
            ],
        ];
    }
}
