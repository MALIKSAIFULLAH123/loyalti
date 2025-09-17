<?php

namespace MetaFox\Forum\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Forum\Jobs\DeleteForum;
use MetaFox\Forum\Models\Forum;
use MetaFox\Forum\Policies\ForumPolicy;
use MetaFox\Forum\Repositories\ForumAdminRepositoryInterface;
use MetaFox\Forum\Repositories\ForumRepositoryInterface;
use MetaFox\Forum\Support\Facades\Forum as ForumFacade;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * Class ForumAdminRepository.
 * @method Forum getModel()
 * @method Forum find($id, $columns = ['*'])()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 *
 * @inore
 */
class ForumAdminRepository extends AbstractRepository implements ForumAdminRepositoryInterface
{
    public function model()
    {
        return Forum::class;
    }

    public function viewForumsInAdminCP(User $context, array $attributes = []): Collection
    {
        policy_authorize(ForumPolicy::class, 'viewAdminCP', $context);

        $parentId = Arr::get($attributes, 'parent_id', 0);
        $search   = Arr::get($attributes, 'q', '');
        $table    = $this->model->getTable();

        $query = $this->getModel()->newQuery()
            ->select("$table.*")
            ->with(['parentForums'])
            ->where([
                'parent_id' => $parentId,
            ]);

        if ($search) {
            $defaultLocale = Language::getDefaultLocaleId();

            $query->leftJoin('phrases as ps', function (JoinClause $join) use ($table) {
                $join->on('ps.key', '=', "$table.title");
            });

            $query->where(function (Builder $builder) use ($table, $search, $defaultLocale) {
                $builder->where(DB::raw("CASE when ps.name is null then $table.title else ps.text end"), $this->likeOperator(), '%' . $search . '%');
                $builder->whereRaw("CASE when ps.name is null then ps.locale is null else ps.locale = '$defaultLocale' end");
            });
        }

        return $query->orderBy('parent_id')
            ->orderBy('ordering')
            ->get();
    }

    public function createForum(User $context, array $attributes): Forum
    {
        policy_authorize(ForumPolicy::class, 'create', $context);

        $parentId = (int) Arr::get($attributes, 'parent_id', 0);

        $ordering = $this->getNextOrdering($parentId);

        $level = $this->getNextLevel($parentId);

        $attributes = array_merge($attributes, [
            'parent_id' => $parentId,
            'ordering'  => $ordering,
            'level'     => $level,
        ]);

        $forum = $this->getModel()->newModelInstance($attributes);

        $forum->save();

        $forum->refresh();

        if ($parentId) {
            $this->increaseTotal($parentId, 'total_sub');
        }

        $this->clearCaches();

        return $forum;
    }

    protected function getNextOrdering(int $parentId): int
    {
        $last = Forum::query()
            ->where('parent_id', '=', $parentId)
            ->orderByDesc('ordering')
            ->first();

        if (null === $last) {
            return 1;
        }

        return $last->ordering + 1;
    }

    protected function getNextLevel(int $parentId): int
    {
        if (0 === $parentId) {
            return 1;
        }

        $parent = Forum::query()
            ->where('id', '=', $parentId)
            ->first();

        if (null === $parent) {
            return 1;
        }

        return $parent->level + 1;
    }

    public function updateForum(User $context, int $id, array $attributes): Forum
    {
        $forum = $this->find($id);

        policy_authorize(ForumPolicy::class, 'update', $context, $forum);

        $oldParentId = $forum->parent_id;

        $newParentId = Arr::get($attributes, 'parent_id');

        $forum->fill($attributes);

        $forum->save();

        $forum->refresh();

        if ($oldParentId != $newParentId) {
            $subs = $forum->total_sub + 1;
            $this->increaseTotal($newParentId, 'total_sub', $subs);
            $this->increaseTotal($newParentId, 'total_comment', $forum->total_comment);
            $this->increaseTotal($newParentId, 'total_thread', $forum->total_thread);
            $this->decreaseTotal($oldParentId, 'total_sub', $subs);
            $this->decreaseTotal($oldParentId, 'total_comment', $forum->total_comment);
            $this->decreaseTotal($oldParentId, 'total_thread', $forum->total_thread);

            $newLevel = 1;

            if (is_numeric($newParentId) && $newParentId > 0) {
                $parentLevel = $this->getLevelByForumId($newParentId);

                if (null != $parentLevel) {
                    $newLevel = $parentLevel + 1;
                }
            }

            if ($newLevel != $forum->level) {
                $this->updateForumLevel($forum->entityId(), $newLevel);
            }
        }

        $this->clearCaches();

        return $forum;
    }

    public function getLevelByForumId(int $forumId): ?int
    {
        $forum = $this->getModel()
            ->newQuery()
            ->where('id', $forumId)
            ->first();

        if (!$forum instanceof Forum) {
            return null;
        }

        return $forum->level;
    }

    public function updateForumLevel(int $forumId, int $level): void
    {
        $forum = $this->find($forumId);

        $forum->update(['level' => $level]);

        if (!$forum->subForums->count()) {
            return;
        }

        $forum->subForums->each(function ($sub) use ($level) {
            $this->updateForumLevel($sub->entityId(), $level + 1);
        });
    }

    public function deleteForum(User $context, int $id, string $deleteOption, ?int $alternativeId = null): bool
    {
        $forum = $this->find($id);

        policy_authorize(ForumPolicy::class, 'delete', $context, $forum);

        $forum->delete();

        $this->clearCaches();

        DeleteForum::dispatch($id, $deleteOption, $alternativeId);

        return true;
    }

    public function getForumsForDeleteOption(Forum $forum): array
    {
        return $this->getModel()->newQuery()
            ->select('id as value', 'title as label', 'parent_id', 'is_closed', 'level', 'ordering')
            ->orderBy('ordering')
            ->where('level', '<=', $forum->level)
            ->where('id', '<>', $forum->entityId())
            ->get()
            ->map(function ($forum) {
                $forum->is_active = !$forum->is_closed;
                $forum->label     = __p($forum->label);

                return $forum;
            })
            ->toArray();
    }

    public function getUpdateForumsForForm(Forum $forum): array
    {
        $exceptForumIds = $this->getDescendantIds($forum->entityId()) ?: [$forum->entityId()];

        $forums = $this->getModel()->newQuery()
            ->select('id as value', 'title as label', 'parent_id', 'is_closed', 'level', 'ordering')
            ->orderBy('ordering')
            ->whereNotIn('id', $exceptForumIds)
            ->get()
            ->toArray();

        if (!count($forums)) {
            return [];
        }

        return array_map(function ($forum) {
            return array_merge($forum, [
                'is_active' => !$forum['is_closed'],
                'label'     => __p($forum['label']),
            ]);
        }, $forums);
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

    public function order(User $context, array $orderIds): bool
    {
        policy_authorize(ForumPolicy::class, 'update', $context);

        $ordering = 1;

        foreach ($orderIds as $id) {
            Forum::query()
                ->where('id', '=', $id)
                ->update(['ordering' => $ordering++]);
        }

        $this->clearCaches();

        return true;
    }

    public function close(User $context, int $id, bool $closed): ?Forum
    {
        $forum = $this->find($id);

        policy_authorize(ForumPolicy::class, 'update', $context, $forum);

        $forum->fill(['is_closed' => $closed]);

        $forum->save();

        $this->clearCaches();

        return $forum;
    }

    public function getAscendantIds(int $forumId, bool $includeSelf = true): array
    {
        return resolve(ForumRepositoryInterface::class)->getAscendantIds($forumId, $includeSelf);
    }

    public function getDescendantIds(int $forumId): array
    {
        return resolve(ForumRepositoryInterface::class)->getDescendantIds($forumId);
    }

    public function increaseTotal(int $forumId, string $column, int $total = 1): void
    {
        resolve(ForumRepositoryInterface::class)->increaseTotal($forumId, $column, $total);
    }

    public function decreaseTotal(int $forumId, string $column, int $total = 1): void
    {
        resolve(ForumRepositoryInterface::class)->decreaseTotal($forumId, $column, $total);
    }

    public function countActiveForumByLevel(int $level): int
    {
        return $this->getModel()
            ->newQuery()
            ->where([
                'level'     => $level,
                'is_closed' => 0,
            ])->count();
    }
}
