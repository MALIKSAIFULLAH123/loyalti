<?php

namespace MetaFox\Search\Repositories;

use MetaFox\Search\Models\HashtagStatistic;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface HashtagStatistic.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface HashtagStatisticRepositoryInterface
{
    /**
     * @param  int  $tagId
     * @param  int  $total
     * @return bool
     */
    public function increaseTotal(int $tagId, int $total = 1): bool;

    /**
     * @param  int  $tagId
     * @param  int  $total
     * @return bool
     */
    public function decreaseTotal(int $tagId, int $total = 1): bool;
}
