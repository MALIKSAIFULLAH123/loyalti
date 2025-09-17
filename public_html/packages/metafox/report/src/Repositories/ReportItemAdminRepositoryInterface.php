<?php

namespace MetaFox\Report\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Platform\Contracts\User;
use MetaFox\Report\Models\ReportItem;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface ReportItem.
 * @mixin BaseRepository
 * @method ReportItem getModel()
 * @method ReportItem find($id, $columns = ['*'])()
 */
interface ReportItemAdminRepositoryInterface
{
    /**
     * @param User   $context
     * @param string $itemType
     * @param int    $itemId
     * @return bool
     */
    public function deleteReportByItem(User $context, string $itemType, int $itemId): bool;

    /**
     * @param string $itemType
     * @param int    $itemId
     * @return bool
     */
    public function handleDeleteReportByItem(string $itemType, int $itemId): bool;

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     * @return Paginator
     */
    public function viewReportItems(User $context, array $attributes = []): Paginator;
}
