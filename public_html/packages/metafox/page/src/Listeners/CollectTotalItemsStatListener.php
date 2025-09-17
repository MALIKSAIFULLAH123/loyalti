<?php

namespace MetaFox\Page\Listeners;

use Carbon\Carbon;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Repositories\PageRepositoryInterface;
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
                'name'  => Page::ENTITY_TYPE,
                'label' => 'page::phrase.page_stat_label',
                'value' => resolve(PageRepositoryInterface::class)->getTotalItemByPeriod($after, $before),
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
                'name'  => 'pending_page',
                'label' => 'page::phrase.page_stat_label',
                'value' => resolve(PageRepositoryInterface::class)->getTotalPendingItemByPeriod(),
            ],
        ];
    }
}
