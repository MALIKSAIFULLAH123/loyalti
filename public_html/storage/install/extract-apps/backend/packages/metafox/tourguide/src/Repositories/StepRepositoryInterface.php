<?php

namespace MetaFox\TourGuide\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\TourGuide\Models\Step;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Step.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface StepRepositoryInterface
{
    /**
     * @param array $params
     * @return Step
     */
    public function createStep(array $params): Step;

    /**
     * @param int $id
     * @param array $params
     * @return Step
     */
    public function updateStep(int $id, array $params): Step;

    /**
     * @param int $id
     * @param int $isActive
     * @return bool
     */
    public function updateActive(int $id, int $isActive): bool;

    /**
     * @param array $params
     * @return Paginator
     */
    public function viewSteps(array $params): Paginator;

    /**
     * @param array $orderIds
     * @return bool
     */
    public function orderSteps(array $orderIds): bool;
}
