<?php

namespace MetaFox\Advertise\Repositories;

use MetaFox\Advertise\Models\Statistic;
use MetaFox\Platform\Contracts\Entity;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Statistic.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface StatisticRepositoryInterface
{
    /**
     * @param  Entity    $entity
     * @return Statistic
     */
    public function createStatistic(Entity $entity): Statistic;

    /**
     * @param  Entity $entity
     * @return void
     */
    public function deleteStatistic(Entity $entity): void;
}
