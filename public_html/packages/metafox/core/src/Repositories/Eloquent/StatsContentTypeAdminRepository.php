<?php

namespace MetaFox\Core\Repositories\Eloquent;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Core\Repositories\StatsContentTypeAdminRepositoryInterface;
use MetaFox\Core\Models\StatsContentType as Model;
use MetaFox\Core\Support\CacheManager;
use MetaFox\Platform\Contracts\User;

/**
 * Class StatsContentTypeAdminRepository.
 */
class StatsContentTypeAdminRepository extends AbstractRepository implements StatsContentTypeAdminRepositoryInterface
{
    public function model()
    {
        return Model::class;
    }

    /**
     * @inheritDoc
     */
    public function getAllKeyByName(): array
    {
        return Cache::rememberForever(
            CacheManager::CORE_STATS_CONTENT_TYPE_CACHE,
            function () {
                $data = $this->getModel()->newQuery()->get()->keyBy('name')->toArray();

                if (empty($data)) {
                    return [];
                }

                return $data;
            }
        );
    }

    /**
     * @inheritDoc
     */
    public function viewTypes(User $context, array $attributes = []): Collection
    {
        $query = $this->getModel()->newQuery();
        $query->whereHas('latestStatistic');
        $query->orderBy('ordering')->orderBy('id');

        return $query->get()->collect();
    }

    /**
     * @inheritDoc
     */
    public function updateType(User $context, int $id, array $attributes = []): Model
    {
        $resource = $this->find($id);

        $resource->fill(Arr::only($attributes, 'icon'));
        $resource->save();

        return $resource->refresh();
    }

    /**
     * @inheritDoc
     */
    public function orderTypes(array $ids): bool
    {
        $mappedOrders = [];

        foreach ($ids as $order => $id) {
            $mappedOrders[$id] = $order + 1;
        }

        $outofOrder      = $this->getModel()->newQuery()->count() + 1;
        $queryWhereIn    = $this->getModel()->newQuery()->whereIn('id', $ids);
        $queryWhereNotIn = $this->getModel()->newQuery()->whereNotIn('id', $ids);

        foreach ($queryWhereIn->cursor() as $type) {
            if (!$type instanceof Model) {
                continue;
            }

            $type->update(['ordering' => Arr::get($mappedOrders, $type->entityId(), $outofOrder)]);
        }

        foreach ($queryWhereNotIn->cursor() as $type) {
            if (!$type instanceof Model) {
                continue;
            }

            $type->update(['ordering' => $outofOrder]);
        }

        return true;
    }
}
