<?php

namespace MetaFox\ActivityPoint\Repositories\Eloquent;

use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\ActivityPoint\Repositories\ConversionStatisticRepositoryInterface;
use MetaFox\ActivityPoint\Models\ConversionStatistic;
use MetaFox\Platform\Contracts\User;

/**
 * stub: /packages/repositories/eloquent_repository.stub
 */

/**
 * Class ConversionStatisticRepository
 *
 */
class ConversionStatisticRepository extends AbstractRepository implements ConversionStatisticRepositoryInterface
{
    public function model()
    {
        return ConversionStatistic::class;
    }

    public function getStatistic(User $user): ConversionStatistic
    {
        return $this->getModel()->newQuery()
            ->firstOrCreate([
                'user_id' => $user->entityId(),
                'user_type' => $user->entityType()
            ], [
                'total_converted' => 0,
                'total_pending' => 0,
            ]);
    }
}
