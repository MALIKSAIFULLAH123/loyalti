<?php

namespace MetaFox\ActivityPoint\Repositories;

use MetaFox\ActivityPoint\Models\ConversionStatistic;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Statistic
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface ConversionStatisticRepositoryInterface
{
    /**
     * @param User $user
     * @return ConversionStatistic
     */
    public function getStatistic(User $user): ConversionStatistic;
}
