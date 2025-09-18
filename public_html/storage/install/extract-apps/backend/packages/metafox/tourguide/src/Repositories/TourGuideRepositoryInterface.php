<?php

namespace MetaFox\TourGuide\Repositories;

use MetaFox\Platform\Contracts\User;
use MetaFox\TourGuide\Models\TourGuide;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface TourGuide.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface TourGuideRepositoryInterface
{
    /**
     * @param User $context
     * @param array $params
     * @return array
     */
    public function getActions(User $context, array $params): array;

    /**
     * @param User $context
     * @param string $pageName
     * @return TourGuide|null
     */
    public function getLatestTourGuide(User $context, string $pageName): ?TourGuide;

    /**
     * @param User $context
     * @param array $params
     * @return TourGuide
     */
    public function createTourGuide(User $context, array $params): TourGuide;

    /**
     * @param int $tourGuideId
     * @param array $params
     * @return TourGuide
     */
    public function updateTourGuide(int $tourGuideId, array $params): TourGuide;
}
