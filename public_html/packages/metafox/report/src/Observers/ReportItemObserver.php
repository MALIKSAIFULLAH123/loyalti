<?php

namespace MetaFox\Report\Observers;

use MetaFox\Report\Models\ReportItem;
use MetaFox\Report\Repositories\ReportItemAggregateAdminRepositoryInterface;

/**
 * Class ReportItemObserver.
 */
class ReportItemObserver
{
    public function created(ReportItem $model): void
    {
        $this->getAggregateRepository()->upsertAggregationByReport($model);
    }

    public function deleted(ReportItem $model): void
    {
        $this->getAggregateRepository()->updateTotalReportsByReport($model);
    }

    protected function getAggregateRepository(): ReportItemAggregateAdminRepositoryInterface
    {
        return resolve(ReportItemAggregateAdminRepositoryInterface::class);
    }
}

// end stub
