<?php

namespace MetaFox\Report\Listeners;

use MetaFox\Platform\Contracts\Entity;
use MetaFox\Report\Repositories\ReportOwnerRepositoryInterface;

class ModelRemovedListener
{
    public function __construct(protected ReportOwnerRepositoryInterface $reportOwnerRepository) { }

    /**
     * @param mixed $model
     * @return void
     */
    public function handle(mixed $model): void
    {
        if (!$model instanceof Entity) {
            return;
        }
        
        $report = $this->reportOwnerRepository->getReportByItem($model->entityId(), $model->entityType());
        if (!$report) {
            return;
        }

        if ($report->total_report > 0) {
            $report->userReports()->delete();
            $report->update(['total_report' => 0]);
        }
    }
}
