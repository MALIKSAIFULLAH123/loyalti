<?php

namespace MetaFox\ActivityPoint\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\ActivityPoint\Models\PointStatistic as Statistic;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * Interface PointStatisticRepositoryInterface.
 *
 * @mixin AbstractRepository
 *
 * @method Statistic find($id, $columns = ['*'])
 * @method Statistic getModel()
 */
interface PointStatisticRepositoryInterface
{
    /**
     * @param  User      $context
     * @param  int       $id
     * @return Statistic
     */
    public function viewStatistic(User $context, int $id): Statistic;

    /**
     * @param  User                 $context
     * @param  int                  $type
     * @param  array<string, mixed> $attributes
     * @return Statistic
     */
    public function updateStatistic(User $context, int $type, array $attributes): Statistic;

    /**
     * @param  User                 $context
     * @param  array<string, mixed> $attributes
     * @return Paginator
     */
    public function viewStatistics(User $context, array $attributes): Paginator;

    /**
     * @param  array $userIds
     * @return int
     */
    public function getMinPointByIds(array $userIds): int;
}
