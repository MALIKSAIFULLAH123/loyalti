<?php

namespace MetaFox\TourGuide\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\TourGuide\Repositories\TourGuideAdminRepositoryInterface;
use MetaFox\TourGuide\Models\TourGuide;
use MetaFox\User\Support\Browse\Scopes\User\SearchScope as UserSearchScope;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class TourGuideAdminRepository.
 */
class TourGuideAdminRepository extends AbstractRepository implements TourGuideAdminRepositoryInterface
{
    public function model(): string
    {
        return TourGuide::class;
    }

    public function viewTourGuides(array $params): Paginator
    {
        $limit = Arr::get($params, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        return $this->buildQueryViewTourGuides($params)
            ->orderByDesc('created_at')
            ->paginate($limit, ['tour_guides.*']);
    }

    public function updateTourGuide(int $id, array $params): TourGuide
    {
        $resource = $this->find($id);

        $resource->update($params);

        return $resource->refresh();
    }

    public function updateActive(int $id, int $isActive): bool
    {
        $resource = $this->find($id);

        return $resource->update(['is_active' => $isActive]);
    }

    protected function buildQueryViewTourGuides(array $params): Builder
    {
        $query = $this->getModel()->newQuery();

        $name = Arr::get($params, 'q');

        if ($name) {
            $query = $query->addScope(new SearchScope($name, ['name']));
        }

        $userName = Arr::get($params, 'user_name');

        if ($userName) {
            $searchScope = new UserSearchScope();
            $searchScope->setAliasJoinedTable('user');
            $searchScope->setSearchText($userName);
            $searchScope->setFieldJoined('user_id');

            $query->addScope($searchScope);
        }

        $url = Arr::get($params, 'url');

        if ($url) {
            $query = $query->addScope(new SearchScope($url, ['url']));
        }

        $isActive = Arr::get($params, 'is_active');

        if (is_numeric($isActive)) {
            $query->where('is_active', (bool) $isActive);
        }

        $isAuto = Arr::get($params, 'is_auto');

        if (is_numeric($isAuto)) {
            $query->where('is_auto', (bool) $isAuto);
        }

        return $query;
    }

    public function batchDelete(array $ids): void
    {
        $this->getModel()
            ->newQuery()
            ->whereIn('id', $ids)
            ->get()
            ->each(fn (TourGuide $model) => $model->delete());
    }
}
