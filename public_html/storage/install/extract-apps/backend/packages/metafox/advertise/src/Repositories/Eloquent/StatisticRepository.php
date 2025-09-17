<?php

namespace MetaFox\Advertise\Repositories\Eloquent;

use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Advertise\Repositories\StatisticRepositoryInterface;
use MetaFox\Advertise\Models\Statistic;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class StatisticRepository.
 */
class StatisticRepository extends AbstractRepository implements StatisticRepositoryInterface
{
    public function model()
    {
        return Statistic::class;
    }

    public function createStatistic(Entity $entity): Statistic
    {
        return Statistic::firstOrCreate([
            'item_id'   => $entity->entityId(),
            'item_type' => $entity->entityType(),
        ]);
    }

    public function deleteStatistic(Entity $entity): void
    {
        $this->deleteWhere([
            'item_id'   => $entity->entityId(),
            'item_type' => $entity->entityType(),
        ]);
    }
}
