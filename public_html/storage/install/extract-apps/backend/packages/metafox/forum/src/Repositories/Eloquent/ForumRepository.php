<?php

namespace MetaFox\Forum\Repositories\Eloquent;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Forum\Http\Resources\v1\Forum\ForumSideBlockItemCollection;
use MetaFox\Forum\Models\Forum;
use MetaFox\Forum\Models\ForumThread;
use MetaFox\Forum\Policies\ForumPolicy;
use MetaFox\Forum\Policies\ForumThreadPolicy;
use MetaFox\Forum\Repositories\ForumPostRepositoryInterface;
use MetaFox\Forum\Repositories\ForumRepositoryInterface;
use MetaFox\Forum\Repositories\ForumThreadRepositoryInterface;
use MetaFox\Forum\Support\Browse\Scopes\ForumTranslatableTextSearchScope;
use MetaFox\Forum\Support\Facades\Forum as ForumFacade;
use MetaFox\Forum\Support\ForumSupport;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * Class GroupRepository.
 * @method Forum getModel()
 * @method Forum find($id, $columns = ['*'])()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 *
 * @inore
 */
class ForumRepository extends AbstractRepository implements ForumRepositoryInterface
{
    public function model()
    {
        return Forum::class;
    }

    /**
     * @throws AuthorizationException
     */
    public function viewForums(User $context, array $attributes): array
    {
        policy_authorize(ForumPolicy::class, 'viewAny', $context);

        $items = $this->getForumsForView($context, $attributes);

        return $items;
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function getForumsForNavigation(User $context, array $attributes): ?EloquentCollection
    {
        policy_authorize(ForumPolicy::class, 'viewAny', $context);

        return localCacheStore()->rememberForever(ForumFacade::getNavigationCacheId(), function () use ($attributes) {
            return ForumFacade::buildForumsForNavigation(0, $attributes);
        });
    }

    public function viewForum(User $context, int $id): Forum
    {
        // TODO: Implement viewForum() method.
    }

    /**
     * @param User  $context
     * @param array $attributes
     *
     * @return array
     */
    public function getForumsForView(User $context, array $attributes = []): array
    {
        if (!policy_check(ForumPolicy::class, 'viewAny', $context)) {
            return [];
        }

        $roleId = $context->roleId();
        $cacheKeyMobile = ForumFacade::getViewMobileCacheId() . "_{$roleId}";
        $cacheKey = ForumFacade::getViewCacheId() . "_{$roleId}";

        $search =  Arr::get($attributes, 'q');

        if (null !== $search) {
            $defaultLocale = App::getLocale() ?? Language::getDefaultLocaleId();
            $searchScope   = new ForumTranslatableTextSearchScope($search);
            $searchScope->setLocale($defaultLocale);

            $items = Forum::query()
                ->addScope($searchScope)
                ->orderBy('ordering')
                ->get('forums.*');

            $request = resolve(Request::class);

            $collection = new ForumSideBlockItemCollection($items);

            return $collection->toArray($request);
        }

        if (MetaFox::isMobile()) {
            return localCacheStore()->rememberForever($cacheKeyMobile, function () {
                return ForumFacade::buildForumsForViewMobile();
            });
        }

        return localCacheStore()->rememberForever($cacheKey, function () {
            return ForumFacade::buildForumsForView();
        });
    }

    /**
     * @throws AuthorizationException
     */
    public function getForumsForForm(User $context, ?Forum $forum = null, bool $filterClosed = true): array
    {
        $isEdit = $forum instanceof Forum;

        if ($isEdit && $forum->subForums()->exists() && !$forum->parentForums()->exists()) {
            return [];
        }

        $query = $this->getModel()->newQuery()
            ->select('id as value', 'title as label', 'parent_id', 'is_closed', 'level', 'ordering')
            ->orderBy('ordering');

        if ($filterClosed) {
            $query->where('is_closed', MetaFoxConstant::IS_INACTIVE);
        }

        switch ($isEdit) {
            case true:
                $forums = $query->where('level', '<=', $forum->level)
                    ->where('id', '<>', $forum->entityId())
                    ->get()
                    ->toArray();
                break;
            default:
                $forums = localCacheStore()->rememberForever(ForumFacade::getFormCacheId(), function () use ($query) {
                    return $query
                        ->get()
                        ->toArray();
                });
        }

        if (!count($forums)) {
            return [];
        }

        return array_map(function ($forum) {
            $forum = array_merge($forum, [
                'is_active' => !$forum['is_closed'],
            ]);
            $forum['label'] = __p($forum['label']);

            return $forum;
        }, $forums);
    }

    public function getForumOptions(?Forum $forum = null): array
    {
        if (!$forum instanceof Forum) {
            return $this->getForumsForStoreForm();
        }

        return $this->getForumsForUpdateForm(collect([$forum]));
    }

    protected function getForumsForStoreForm(): array
    {
        $forums = $this->fetchSortedForums($this->getActiveForumIds());

        return $this->setActiveStatusForForm($forums);
    }

    protected function getForumsForUpdateForm(Collection $selectedForums): array
    {
        $forums = $this->fetchSortedForums(array_merge(
            $this->getActiveForumIds(),
            $selectedForums->pluck('id')->toArray(),
            $this->getRelatedParentIds($selectedForums)
        ));

        return $this->setActiveStatusForForm($forums);
    }

    protected function getRelatedParentIds(Collection $selectedForums): array
    {
        return $selectedForums
            ->filter(fn ($selectedForum) => $selectedForum->parent_id)
            ->flatMap(function ($selectedForum) {
                return $this->fetchParentRelations($selectedForum->entityId());
            })
            ->unique()
            ->toArray();
    }

    protected function fetchParentRelations(int $forumId, array &$parentIds = []): array
    {
        $forums = $this->getForums(false);

        $parentIds[] = $forumId;

        $parentForum = $forums->where('value', $forumId)->first();

        if (!$parentForum?->parent_id) {
            return $parentIds;
        }

        return $this->fetchParentRelations($parentForum->parent_id, $parentIds);
    }

    protected function setActiveStatusForForm(array $options): array
    {
        $activeForumIds = $this->getActiveForumIds();

        return array_map(function ($option) use ($activeForumIds) {
            $option['is_active'] = (int) in_array($option['value'], $activeForumIds);

            return $option;
        }, $options);
    }

    protected function fetchSortedForums(array $forumIds): array
    {
        return $this->getForums(false)
            ->whereIn('value', $forumIds)
            ->sortBy('ordering')
            ->values()
            ->toArray();
    }

    public function getActiveForumIds(): array
    {
        return localCacheStore()->rememberForever(ForumFacade::getActiveForumIdsCacheId(), function () {
            return $this->collectActiveIds();
        });
    }

    protected function collectActiveIds(array $parentIds = [0]): array
    {
        $result = array_filter($parentIds);

        foreach ($parentIds as $parentId) {
            $childrenIds = $this->getForums()
                ->where('parent_id', '=', $parentId)
                ->pluck('value')
                ->toArray();

            if (empty($childrenIds)) {
                continue;
            }

            $result = array_merge($result, $this->collectActiveIds($childrenIds));
        }

        return $result;
    }

    public function getForums(bool $activeOnly = true): Collection
    {
        return LoadReduce::remember(sprintf('forum::getForums(%s)', $activeOnly), function () use ($activeOnly) {
            $query = $this->getModel()->newQuery()
                ->select('id as value', 'title as label', 'parent_id', 'is_closed', 'level', 'ordering')
                ->orderBy('ordering');

            if ($activeOnly) {
                $query->where('is_closed', false);
            }

            return $query->get();
        });
    }

    /**
     * @throws AuthorizationException
     */
    public function getSubForums(User $context, int $parentId, int $limit = 4): ?Paginator
    {
        $parent = $this->find($parentId);

        policy_authorize(ForumPolicy::class, 'view', $context, $parent);

        $query = $this->getModel()->newQuery();

        $items = $query
            ->where([
                'parent_id' => $parentId,
            ])
            ->orderBy('ordering');

        return $items->simplePaginate($limit);
    }

    protected function addMoreAttributes(array $attributes): array
    {
        if (!Arr::has($attributes, 'thread_id')) {
            Arr::set($attributes, 'thread_id', 0);
        }
        if (!Arr::has($attributes, 'post_id')) {
            Arr::set($attributes, 'post_id', 0);
        }

        return $attributes;
    }

    public function getSearchItems(User $context, array $attributes): Paginator
    {
        $owner = $context;

        switch ($attributes['item_type']) {
            case ForumSupport::SEARCH_BY_POST:
                $repository = resolve(ForumPostRepositoryInterface::class);
                $attributes = $this->addMoreAttributes($attributes);
                $items      = $repository->viewPosts($context, $owner, $attributes);
                break;
            default:
                policy_authorize(ForumThreadPolicy::class, 'viewAny', $context, $owner);
                $repository = resolve(ForumThreadRepositoryInterface::class);
                $items      = $repository->viewThreads($context, $owner, $attributes);
                break;
        }

        return $items;
    }

    protected function clearCaches(): void
    {
        localCacheStore()->deleteMultiple([
            ForumFacade::getNavigationCacheId(),
            ForumFacade::getViewCacheId(),
            ForumFacade::getFormCacheId(),
            ForumFacade::getViewMobileCacheId(),
            ForumFacade::getActiveForumIdsCacheId(),
        ]);
    }

    public function getAscendantIds(int $forumId, bool $includeSelf = true): array
    {
        $forum = Forum::query()
            ->withTrashed()
            ->where('id', '=', $forumId)
            ->first();

        if (null === $forum) {
            return [];
        }

        $results = [];

        if ($includeSelf) {
            $results[] = $forumId;
        }

        if (0 == $forum->parent_id) {
            return $results;
        }

        $forums = Forum::query()
            ->withTrashed()
            ->get()
            ->pluck('parent_id', 'id')
            ->toArray();

        $parentId = $forum->parent_id;

        do {
            $results[] = $parentId;
            $parentId  = Arr::get($forums, $parentId);
        } while ($parentId);

        return $results;
    }

    public function getDescendantIds(int $forumId): array
    {
        return array_unique(ForumFacade::buildForumIdsForSearch($forumId));
    }

    public function getBreadcrumbs(int $forumId): array
    {
        $ids = $this->getAscendantIds($forumId, false);

        if (!count($ids)) {
            return [];
        }

        $ids = array_reverse($ids);

        $forums = $this->getModel()->newQuery()
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        if (!$forums->count()) {
            return [];
        }

        $breadcrumbs = [];

        foreach ($ids as $id) {
            $forum = $forums->get($id);

            if (!$forum instanceof Forum) {
                continue;
            }

            $breadcrumbs[] = [
                'label' => $forum->toTitle(),
                'to'    => $forum->toLink(),
            ];
        }

        return $breadcrumbs;
    }

    public function increaseTotal(int $forumId, string $column, int $total = 1): void
    {
        $forumIds = $this->getAscendantIds($forumId);

        if (!count($forumIds)) {
            return;
        }

        Forum::query()
            ->whereIn('id', $forumIds)
            ->get()
            ->each(function ($forum) use ($column, $total) {
                $forum->incrementAmount($column, $total);
            });

        $this->clearCaches();
    }

    public function decreaseTotal(int $forumId, string $column, int $total = 1): void
    {
        $forumIds = $this->getAscendantIds($forumId);

        if (!count($forumIds)) {
            return;
        }

        Forum::query()
            ->whereIn('id', $forumIds)
            ->get()
            ->each(function ($forum) use ($column, $total) {
                $forum->decrementAmount($column, $total);
            });

        $this->clearCaches();
    }

    public function migrateStatistics(int $level): void
    {
        $forums = Forum::query()
            ->with(['subForums'])
            ->withTrashed()
            ->where([
                'level' => $level,
            ])
            ->get();

        if (!$forums->count()) {
            return;
        }

        foreach ($forums as $forum) {
            $threads = ForumThread::query()
                ->withCount([
                    'posts' => function ($builder) {
                        $builder->where('is_approved', '=', 1);
                    },
                ])
                ->where('forum_id', '=', $forum->entityId())
                ->where('is_approved', '=', 1)
                ->get();

            $totalThreads = $totalComments = 0;

            $totalSubs = $forum->subForums->count();

            if ($threads->count()) {
                $totalThreads = $threads->count();

                foreach ($threads as $thread) {
                    $totalComments += $thread->posts_count;
                }
            }

            if ($totalSubs) {
                foreach ($forum->subForums as $subForum) {
                    $totalThreads += $subForum->total_thread;
                    $totalComments += $subForum->total_comment;
                    $totalSubs += $subForum->total_sub;
                }
            }

            $forum->update([
                'total_thread'  => $totalThreads,
                'total_comment' => $totalComments,
                'total_sub'     => $totalSubs,
            ]);
        }

        $this->migrateStatistics($level - 1);
    }

    public function migrateForumLevel(int $level = 1): void
    {
        try {
            $condition = [
                'level' => $level,
            ];

            if ($level === 1) {
                $condition['parent_id'] = 0;
            }

            $forums = Forum::query()->with(['subForums'])
                ->withTrashed()
                ->where($condition)
                ->cursor();

            $batch = [];

            if ($forums->isEmpty() || $level == 999) {
                return;
            }

            foreach ($forums as $forum) {
                foreach ($forum->subForums as $subForum) {
                    $batch[] = [
                        'id'    => $subForum->id,
                        'level' => $level + 1,
                        'title' => $subForum->title,
                    ];
                }
            }
            Forum::query()->upsert($batch, ['id']);

            $this->migrateForumLevel($level + 1);
        } catch (Exception $e) {
            return;
        }
    }

    public function paginateForums(array $attributes = []): Paginator
    {
        $limit = Arr::get($attributes, 'limit', 3);

        return $this->builderQueryForums($attributes)
            ->orderBy('ordering')
            ->paginate($limit, ['forums.*']);
    }

    public function isClosed(int $id): bool
    {
        /**
         * @var Forum $forum
         */
        $forum = $this->find($id);

        return $forum->is_closed;
    }

    public function builderQueryForums(array $attributes): Builder
    {
        $query    = $this->getModel()->newQuery();
        $isClosed = Arr::get($attributes, 'is_closed', MetaFoxConstant::IS_INACTIVE);

        $query->where('is_closed', $isClosed);

        if (Arr::has($attributes, 'parent_id')) {
            $query->where('parent_id', $attributes['parent_id']);
        }

        if (Arr::has($attributes, 'level')) {
            $query->where('level', $attributes['level']);
        }

        return $query;
    }
}
