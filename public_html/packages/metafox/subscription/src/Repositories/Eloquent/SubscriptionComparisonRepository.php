<?php

namespace MetaFox\Subscription\Repositories\Eloquent;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Subscription\Models\SubscriptionComparison;
use MetaFox\Subscription\Models\SubscriptionComparisonData;
use MetaFox\Subscription\Repositories\SubscriptionComparisonRepositoryInterface;
use MetaFox\Subscription\Support\Helper;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class SubscriptionComparisonRepository.
 */
class SubscriptionComparisonRepository extends AbstractRepository implements SubscriptionComparisonRepositoryInterface
{
    public function model()
    {
        return SubscriptionComparison::class;
    }

    public function createComparison(User $context, array $attributes): SubscriptionComparison
    {
        Arr::set($attributes, 'ordering', $this->getLastOrdering());

        $comparison = $this->getModel()->newInstance($attributes);

        $success = $comparison->save();

        $cacheIds = [];

        if ($success) {
            $packages = Arr::get($attributes, 'packages');

            if (is_array($packages)) {
                $this->handleComparisonData($comparison->entityId(), $packages);
            }
            $submittedPackageIds = array_keys($packages);

            $cacheIds = array_map(function ($packageId) {
                return Helper::getFeaturePackageCacheId($packageId);
            }, $submittedPackageIds);
        }

        $this->clearCaches($cacheIds);

        $comparison->refresh();

        return $comparison;
    }

    protected function handleComparisonData(int $comparisonId, array $packages = []): bool
    {
        $comparison = $this->find($comparisonId);

        if (!count($packages)) {
            return $comparison->packages()->delete();
        }

        $currentPackageIds = [];

        if (null !== $comparison->packages) {
            $currentPackageIds = $comparison->packages->pluck('pivot.package_id')->toArray();
        }

        $submittedPackageIds = array_keys($packages);

        $insertedPackageIds = array_diff($submittedPackageIds, $currentPackageIds);

        $deletedPackageIds = array_diff($currentPackageIds, $submittedPackageIds);

        $updatedPackageIds = array_intersect($submittedPackageIds, $currentPackageIds);

        if (count($insertedPackageIds)) {
            foreach ($insertedPackageIds as $insertedPackageId) {
                $type = Arr::get($packages, $insertedPackageId . '.type');

                $model = new SubscriptionComparisonData([
                    'comparison_id' => $comparisonId,
                    'package_id'    => $insertedPackageId,
                    'type'          => $type,
                    'value'         => $type == Helper::COMPARISON_TYPE_TEXT ? Arr::get($packages, $insertedPackageId . '.text') : null,
                ]);

                $model->save();
            }
        }

        if (count($deletedPackageIds)) {
            $query = (new SubscriptionComparisonData())->newModelQuery();

            $query->where(['comparison_id' => $comparisonId])
                ->whereIn('package_id', $deletedPackageIds)
                ->delete();
        }

        if (count($updatedPackageIds)) {
            foreach ($updatedPackageIds as $updatedPackageId) {
                $type = Arr::get($packages, $updatedPackageId . '.type');

                $query = (new SubscriptionComparisonData())->newModelQuery();

                $query
                    ->where([
                        'package_id'    => $updatedPackageId,
                        'comparison_id' => $comparisonId,
                    ])
                    ->update([
                        'type'  => $type,
                        'value' => $type === Helper::COMPARISON_TYPE_TEXT ? Arr::get($packages, $updatedPackageId . '.text') : null,
                    ]);
            }
        }

        return true;
    }

    public function updateComparison(User $context, int $id, array $attributes): SubscriptionComparison
    {
        $comparison = $this->find($id);
        $cacheIds   = [];
        $comparison->fill($attributes);

        $comparison->save();

        $packages = Arr::get($attributes, 'packages', []);

        if (is_array($packages)) {
            $this->handleComparisonData($id, $packages);
            $submittedPackageIds = array_keys($packages);

            $cacheIds = array_map(function ($packageId) {
                return Helper::getFeaturePackageCacheId($packageId);
            }, $submittedPackageIds);
        }

        $this->clearCaches($cacheIds);

        $comparison->refresh();

        return $comparison;
    }

    public function deleteComparison(User $context, int $id): bool
    {
        $comparison = $this
            ->with(['packages'])
            ->find($id);

        $comparison->delete();

        return true;
    }

    public function clearCaches(array $cacheIds = []): void
    {
        $cacheIds = array_merge($cacheIds, [
            Helper::COMPARISON_CACHE_ID,
            Helper::COMPARISON_CACHE_ID . '_' . Helper::VIEW_ADMINCP,
            Helper::COMPARISON_CACHE_ID . '_' . Helper::VIEW_FILTER,
        ]);

        Cache::deleteMultiple($cacheIds);
    }

    public function viewComparisons(User $context, array $attributes = []): Collection
    {
        $isAdminCP = Arr::get($attributes, 'view', Helper::VIEW_FILTER) === Helper::VIEW_ADMINCP;

        $comparisons = $this->getModel()->newModelQuery()
            ->with(['packages'])
            ->orderBy('ordering')
            ->get();

        $items = [];

        if ($comparisons->count()) {
            $request = resolve(Request::class);

            foreach ($comparisons as $comparison) {
                $resource = match ($isAdminCP) {
                    true  => ResourceGate::asResource($comparison, 'item_admincp', null),
                    false => ResourceGate::asResource($comparison, 'item', null),
                };

                if (null !== $resource) {
                    $items[] = $resource->toArray($request);
                }
            }
        }

        return collect($items);
    }

    public function hasComparisons(User $context): bool
    {
        $count = $this->getModel()->newModelQuery()
            ->count();

        return $count > 0;
    }

    /**
     * @inheritDoc
     */
    public function ordering(User $context, array $orders): void
    {
        foreach ($orders as $order => $id) {
            $this->getModel()->newQuery()
                ->where('id', $id)->update(['ordering' => $order]);
        }
    }

    protected function getLastOrdering(): int
    {
        return $this->getModel()->newQuery()->max('ordering') + 1;
    }
}
