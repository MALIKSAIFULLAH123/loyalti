<?php

namespace MetaFox\TourGuide\Repositories;

use Illuminate\Database\Eloquent\Builder;
use MetaFox\Platform\Contracts\User;
use MetaFox\TourGuide\Models\Hidden;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Hidden.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface HiddenRepositoryInterface
{
    /**
     * @param User $context
     * @return Builder
     */
    public function getTourGuideIdBuilder(User $context): Builder;

    /**
     * @param int $userId
     * @param int $tourGuideId
     * @return Hidden
     */
    public function createHidden(int $userId, int $tourGuideId): Hidden;

    /**
     * @param int $userId
     * @param int $tourGuideId
     * @return int
     */
    public function deleteHidden(int $userId, int $tourGuideId): int;

    /**
     * @param int $tourGuideId
     * @return void
     */
    public function deleteHiddenByTourGuideId(int $tourGuideId): void;
}
