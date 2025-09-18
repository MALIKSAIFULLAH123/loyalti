<?php

namespace MetaFox\Group\Listeners;

use Carbon\Carbon;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
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
                'name'  => Group::ENTITY_TYPE,
                'label' => 'group::phrase.group_stat_label',
                'value' => resolve(GroupRepositoryInterface::class)->getTotalItemByPeriod($after, $before),
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
                'name'  => 'pending_group',
                'label' => 'group::phrase.group_stat_label',
                'value' => resolve(GroupRepositoryInterface::class)->getTotalPendingItemByPeriod(),
                'group' => 'pending',
            ],
        ];
    }
}
