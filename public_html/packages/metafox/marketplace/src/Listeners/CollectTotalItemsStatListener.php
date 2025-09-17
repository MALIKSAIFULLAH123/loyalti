<?php

namespace MetaFox\Marketplace\Listeners;

use Carbon\Carbon;
use MetaFox\Marketplace\Models\Listing;
use MetaFox\Marketplace\Repositories\ListingRepositoryInterface;
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
                'name'  => Listing::ENTITY_TYPE,
                'label' => 'marketplace::phrase.marketplace_stat_label',
                'value' => resolve(ListingRepositoryInterface::class)->getTotalItemByPeriod($after, $before),
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
                'name'  => sprintf('pending_%s', Listing::ENTITY_TYPE),
                'label' => 'marketplace::phrase.marketplace_stat_label',
                'value' => resolve(ListingRepositoryInterface::class)->getTotalPendingItemByPeriod(null, null, $conditions),
                'group' => 'pending',
            ],
        ];
    }
}
