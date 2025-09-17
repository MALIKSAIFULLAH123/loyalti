<?php

namespace MetaFox\Report\Listeners;

use Carbon\Carbon;
use MetaFox\Core\Listeners\Abstracts\AbstractCollectTotalItemStatListener as AbstractClass;
use MetaFox\Report\Repositories\ReportItemRepositoryInterface;

class CollectTotalItemsStatListener extends AbstractClass
{
    public function __construct(protected ReportItemRepositoryInterface $reportItemRepository) {}

    /**
     * @param Carbon|null $after
     * @param Carbon|null $before
     *
     * @return array<int, mixed>|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getDefaultStats(?Carbon $after = null, ?Carbon $before = null): ?array
    {
        return [
            [
                'name'  => 'pending_report',
                'label' => 'report::phrase.pending_report_stat_label',
                'value' => $this->reportItemRepository->getTotalItemByPeriod($after, $before),
            ],
        ];
    }

    /**
     * @param Carbon|null $after
     * @param Carbon|null $before
     *
     * @return array<int, mixed>|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getSiteStats(?Carbon $after = null, ?Carbon $before = null): ?array
    {
        return [
            [
                'name'  => 'pending_report',
                'label' => 'report::phrase.pending_report_stat_label',
                'value' => $this->reportItemRepository->getTotalPendingItemByPeriod(null, null, []),
                'group' => 'site_stat',
                'url'   => '/report/items/browse',
            ],
        ];
    }
}
