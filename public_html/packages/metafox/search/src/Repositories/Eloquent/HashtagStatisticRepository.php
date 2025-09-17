<?php

namespace MetaFox\Search\Repositories\Eloquent;

use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Search\Repositories\HashtagStatisticRepositoryInterface;
use MetaFox\Search\Models\HashtagStatistic;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class HashtagStatisticRepository.
 */
class HashtagStatisticRepository extends AbstractRepository implements HashtagStatisticRepositoryInterface
{
    public function model()
    {
        return HashtagStatistic::class;
    }

    public function increaseTotal(int $tagId, int $total = 1): bool
    {
        $statistic = HashtagStatistic::query()
            ->firstOrCreate(['tag_id' => $tagId]);

        if (null === $statistic) {
            return false;
        }

        $statistic->incrementAmount('total_item', $total);

        return true;
    }

    public function decreaseTotal(int $tagId, int $total = 1): bool
    {
        $statistic = HashtagStatistic::query()
            ->firstOrCreate(['tag_id' => $tagId]);

        if (null === $statistic) {
            return false;
        }

        $statistic->decrementAmount('total_item', $total);

        return true;
    }
}
