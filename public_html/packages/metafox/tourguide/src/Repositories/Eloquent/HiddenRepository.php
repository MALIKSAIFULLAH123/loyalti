<?php

namespace MetaFox\TourGuide\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\TourGuide\Repositories\HiddenRepositoryInterface;
use MetaFox\TourGuide\Models\Hidden;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class HiddenRepository.
 */
class HiddenRepository extends AbstractRepository implements HiddenRepositoryInterface
{
    public function model(): string
    {
        return Hidden::class;
    }

    public function getTourGuideIdBuilder(User $context): Builder
    {
        return $this->getModel()
            ->newQuery()
            ->where('user_id', $context->entityId())
            ->select('tour_guide_id');
    }

    public function createHidden(int $userId, int $tourGuideId): Hidden
    {
        return $this->getModel()
            ->newQuery()
            ->create([
                'user_id'       => $userId,
                'tour_guide_id' => $tourGuideId,
            ]);
    }

    public function deleteHidden(int $userId, int $tourGuideId): int
    {
        return $this->getModel()
            ->newQuery()
            ->where([
                'user_id'       => $userId,
                'tour_guide_id' => $tourGuideId,
            ])->delete();
    }

    public function deleteHiddenByTourGuideId(int $tourGuideId): void
    {
        $this->getModel()
            ->newQuery()
            ->where('tour_guide_id', $tourGuideId)
            ->delete();
    }
}
