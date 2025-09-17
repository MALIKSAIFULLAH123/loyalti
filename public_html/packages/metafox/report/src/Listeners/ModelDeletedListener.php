<?php

namespace MetaFox\Report\Listeners;

use MetaFox\Platform\Contracts\Entity;
use MetaFox\Report\Repositories\ReportItemAdminRepositoryInterface;

class ModelDeletedListener
{
    public function __construct(protected ReportItemAdminRepositoryInterface $reportItemAdminRepository) {}

    /**
     * @param mixed $model
     * @return void
     */
    public function handle(mixed $model): void
    {
        if ($model instanceof Entity) {
            $this->reportItemAdminRepository->handleDeleteReportByItem($model->entityType(), $model->entityId());
        }
    }
}
