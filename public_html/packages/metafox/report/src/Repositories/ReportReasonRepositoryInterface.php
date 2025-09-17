<?php

namespace MetaFox\Report\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use MetaFox\Platform\Contracts\User;
use MetaFox\Report\Models\ReportReason;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Interface ReportReason.
 * @mixin BaseRepository
 * @method ReportReason getModel()
 * @method ReportReason find($id, $columns = ['*'])
 */
interface ReportReasonRepositoryInterface
{
    /**
     * @param User $context
     * @param int  $id
     *
     * @return ReportReason
     * @throws AuthorizationException
     */
    public function viewReason(User $context, int $id): ReportReason;

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewReasons(User $context, array $attributes): Paginator;

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return ReportReason
     * @throws AuthorizationException
     * @throws ValidatorException
     */
    public function createReason(User $context, array $attributes): ReportReason;

    /**
     * @param User                 $context
     * @param int                  $id
     * @param array<string, mixed> $attributes
     *
     * @return ReportReason
     * @throws AuthorizationException
     */
    public function updateReason(User $context, int $id, array $attributes): ReportReason;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function deleteReason(User $context, int $id): bool;

    /**
     * @param  User       $context
     * @return Collection
     */
    public function getFormReason(User $context): Collection;
}
