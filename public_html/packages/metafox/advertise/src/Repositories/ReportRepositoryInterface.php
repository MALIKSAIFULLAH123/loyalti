<?php

namespace MetaFox\Advertise\Repositories;

use MetaFox\Advertise\Models\Report;
use MetaFox\Advertise\Support\Support;
use MetaFox\Platform\Contracts\Entity;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Report.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface ReportRepositoryInterface
{
    /**
     * @param  Entity      $entity
     * @param  string      $totalType
     * @param  string      $dateType
     * @return Report|null
     */
    public function createReport(Entity $entity, string $totalType, string $dateType = Support::DATE_TYPE_HOUR): ?Report;

    /**
     * @param  Entity $entity
     * @param  string $dateType
     * @return mixed
     */
    public function deleteReports(Entity $entity, string $dateType = Support::DATE_TYPE_HOUR);

    /**
     * @param  Entity      $entity
     * @param  string      $view
     * @param  string      $totalType
     * @param  string|null $startDate
     * @param  string|null $endDate
     * @return array
     */
    public function viewReport(Entity $entity, string $view, string $totalType, ?string $startDate = null, ?string $endDate = null): array;
}
