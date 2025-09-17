<?php

namespace MetaFox\Report\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Report\Models\ReportItem;
use MetaFox\Report\Models\ReportItemAggregate;
use Prettus\Repository\Eloquent\BaseRepository;
use MetaFox\Report\Models\ReportItemAggregate as Model;
use MetaFox\Platform\Contracts\User;

/**
 * Interface ReportItemAggregate.
 *
 * @mixin BaseRepository
 * @method Model getModel()
 * @method Model find($id, $columns = ['*'])
 */
interface ReportItemAggregateAdminRepositoryInterface
{
    /**
     * @param  user                 $context
     * @param  array<string, mixed> $attributes
     * @return Paginator
     */
    public function viewAggregations(User $context, array $attributes = []): Paginator;

    /**
     * @param User $context
     * @param int  $id
     */
    public function deleteAggregation(User $context, int $id): bool;

    /**
     * @param  ReportItem $reportItem
     * @return Model
     */
    public function upsertAggregationByReport(ReportItem $reportItem): Model;

    /**
     * @param  User $context
     * @param  int  $id
     * @return bool
     */
    public function processAggregation(User $context, int $id): bool;

    /**
     * @param  ReportItem $report
     * @return bool
     */
    public function updateTotalReportsByReport(ReportItem $report): bool;
}
