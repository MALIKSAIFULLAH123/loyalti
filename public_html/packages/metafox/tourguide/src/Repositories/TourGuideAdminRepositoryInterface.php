<?php

namespace MetaFox\TourGuide\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\TourGuide\Models\TourGuide;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface TourGuide.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface TourGuideAdminRepositoryInterface
{
    /**
     * @param array $params
     * @return Paginator
     */
    public function viewTourGuides(array $params): Paginator;

    /**
     * @param int $id
     * @param array $params
     * @return TourGuide
     */
    public function updateTourGuide(int $id, array $params): TourGuide;

    /**
     * @param int $id
     * @param int $isActive
     * @return bool
     */
    public function updateActive(int $id, int $isActive): bool;

    /**
     * @param array $ids
     * @return void
     */
    public function batchDelete(array $ids): void;
}
