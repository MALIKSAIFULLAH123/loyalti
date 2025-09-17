<?php

namespace MetaFox\Report\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\User;
use MetaFox\Report\Models\ReportOwner;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface ReportOwner.
 * @mixin BaseRepository
 * @method ReportOwner find($id, $columns = ['*'])
 * @method ReportOwner getModel()
 */
interface ReportOwnerRepositoryInterface
{
    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewReports(User $context, array $attributes): Paginator;

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return false|ReportOwner
     * @throws AuthorizationException
     */
    public function createReportOwner(User $context, array $attributes);

    /**
     * @param User                 $context
     * @param int                  $id
     * @param array<string, mixed> $attributes
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function updateReportOwner(User $context, int $id, array $attributes): bool;

    /**
     * @param User $context
     * @param int  $reportId
     *
     * @return bool
     */
    public function checkReportExist(User $context, int $reportId): bool;

    /**
     * @param User $context
     * @param int  $reportId
     * @return mixed
     */
    public function viewUsers(User $context, int $reportId);

    /**
     * @param int    $itemId
     * @param string $itemType
     * @return Model|null
     */
    public function getReportByItem(int $itemId, string $itemType): ?Model;
}
