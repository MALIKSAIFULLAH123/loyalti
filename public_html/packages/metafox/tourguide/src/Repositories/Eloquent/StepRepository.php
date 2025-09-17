<?php

namespace MetaFox\TourGuide\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Arr;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\TourGuide\Repositories\StepRepositoryInterface;
use MetaFox\TourGuide\Models\Step;
use MetaFox\TourGuide\Repositories\TourGuideRepositoryInterface;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class StepRepository.
 */
class StepRepository extends AbstractRepository implements StepRepositoryInterface
{
    public function model(): string
    {
        return Step::class;
    }

    public function viewSteps(array $params): Paginator
    {
        $limit = Arr::get($params, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        $query = $this->getModel()->newQuery();

        $tourGuideId = Arr::get($params, 'parentId');

        if ($tourGuideId) {
            $query->where('tour_guide_id', $tourGuideId);
        }

        return $query->orderBy('ordering')
            ->paginate($limit, ['tour_guide_steps.*']);
    }

    public function createStep(array $params): Step
    {
        $params = array_merge($params, [
            'ordering'  => $this->getNextOrdering(Arr::get($params, 'tour_guide_id')),
            'is_active' => (bool) Arr::get($params, 'is_active'),
        ]);

        $step = $this->getModel()->newQuery()->create($params);

        $this->updateRelatedTourGuide($step->tour_guide_id, $params);

        return $step;
    }

    protected function updateRelatedTourGuide(int $tourGuideId, array $params): void
    {
        $updateParams = [];

        if (Arr::get($params, 'is_completed', false)) {
            $updateParams['is_active'] = true;
        }

        if ($pageName = Arr::get($params, 'page_name')) {
            $updateParams['page_name'] = $pageName;
        }

        if (!empty($updateParams)) {
            $this->tourGuideRepository()->updateTourGuide($tourGuideId, $updateParams);
        }
    }

    public function updateStep(int $id, array $params): Step
    {
        $step = $this->find($id);

        $step->fill($params);

        $step->save();

        return $step;
    }

    public function updateActive(int $id, int $isActive): bool
    {
        $resource = $this->find($id);

        return $resource->update(['is_active' => $isActive]);
    }

    public function orderSteps(array $orderIds): bool
    {
        $steps = $this->getModel()->newQuery()
            ->whereIn('id', $orderIds)
            ->get()
            ->keyBy('id');

        if (!$steps->count()) {
            return true;
        }

        $ordering = 1;

        foreach ($orderIds as $orderId) {
            $step = $steps->get($orderId);

            if (!is_object($step)) {
                continue;
            }

            $step->update(['ordering' => $ordering++]);
        }

        return true;
    }

    protected function getNextOrdering(int $tourGuideId): int
    {
        $lastStep = $this->getModel()
            ->newQuery()
            ->where('tour_guide_id', $tourGuideId)
            ->orderByDesc('ordering')
            ->first();

        if (!$lastStep instanceof Step) {
            return 0;
        }

        return (int) $lastStep->ordering + 1;
    }

    protected function tourGuideRepository(): TourGuideRepositoryInterface
    {
        return resolve(TourGuideRepositoryInterface::class);
    }
}
