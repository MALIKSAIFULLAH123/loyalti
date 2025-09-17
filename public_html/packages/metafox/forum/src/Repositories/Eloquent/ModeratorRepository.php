<?php

namespace MetaFox\Forum\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use MetaFox\Forum\Models\Forum;
use MetaFox\Forum\Models\ModeratorAccess;
use MetaFox\Forum\Models\Permission;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Forum\Repositories\ModeratorRepositoryInterface;
use MetaFox\Forum\Models\Moderator;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\User\Support\Browse\Scopes\User\BlockedScope;
use MetaFox\User\Support\Browse\Scopes\User\SortScope;
use MetaFox\User\Support\Browse\Scopes\User\ViewScope;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class ModeratorRepository.
 */
class ModeratorRepository extends AbstractRepository implements ModeratorRepositoryInterface
{
    public function model()
    {
        return Moderator::class;
    }

    /**
     * @param int      $userId
     * @param int|null $forumId
     * @param string   $permission
     * @return bool
     */
    public function hasAccess(int $userId, int|null $forumId, string $permission): bool
    {
        if (empty($forumId) || empty($userId)) {
            return false;
        }

        return LoadReduce::remember(sprintf('forum_group_permission_%s_%s_%s', $userId, $forumId, $permission), function () use ($forumId, $userId, $permission) {
            return ModeratorAccess::query()
                ->where([
                    'forum_id' => $forumId,
                    'user_id' => $userId,
                    'permission_name' => $permission
                ])
                ->exists();
        });
    }

    /**
     * @param int  $forumId
     * @param bool $toArray
     * @return array|Collection
     */
    public function getForumModerators(int $forumId, bool $toArray = false): array|Collection
    {
        $aModerators = $this->getModel()->newModelQuery()
            ->with(['user'])
            ->where('forum_id', $forumId)
            ->get();

        if ($toArray) {
            return $aModerators->toArray();
        }

        return $aModerators;
    }

    /**
     * @return array
     */
    public function getPerms(): array
    {
        $permissions = localCacheStore()->rememberForever(__METHOD__, function () {
            return Permission::query()
                ->get()
                ->map(function (Permission $permission) {
                    return [
                        'name'     => $permission->name,
                        'var_name' => $permission->var_name,
                    ];
                })
                ->toArray();
        });

        return array_map(function ($permission) {
            return array_merge($permission, [
                'name' => __p(Arr::get($permission, 'name')),
            ]);
        }, $permissions);
    }

    protected function handleModerators(Forum $forum, array $submittedModeratorIds): array
    {
        $forumId = $forum->entityId();

        $currentModeratorIds = $this->getModel()->newModelQuery()
            ->where('forum_id', $forumId)
            ->get()
            ->pluck('user_id')
            ->toArray();

        $insertedModeratorIds = array_diff($submittedModeratorIds, $currentModeratorIds);
        $updatedModeratorIds = array_intersect($submittedModeratorIds, $currentModeratorIds);
        $deletedModeratorIds = array_diff($currentModeratorIds, $submittedModeratorIds);

        if (count($deletedModeratorIds)) {
            $this->getModel()->newQuery()
                ->where([
                    'forum_id' => $forumId,
                ])
                ->whereIn('user_id', $deletedModeratorIds)
                ->each(function (Moderator $moderator) {
                    $moderator->delete();
                });
        }

        if (count($insertedModeratorIds)) {
            foreach ($insertedModeratorIds as $moderatorId) {
                $moderator = $this->getModel()->newInstance([
                    'user_id'   => $moderatorId,
                    'user_type' => \MetaFox\User\Models\User::ENTITY_TYPE,
                    'forum_id'  => $forumId,
                ]);

                $moderator->save();
            }
        }

        return [
            'inserted' => $insertedModeratorIds,
            'kept'     => $updatedModeratorIds,
            'deleted'   => $deletedModeratorIds,
        ];
    }

    /**
     * @param User  $context
     * @param Forum $forum
     * @param array $moderatorIds
     * @param array $configParams
     * @return bool
     */
    public function setupModerators(User $context, Forum $forum, array $moderatorIds, array $configParams): bool
    {
        $data = $this->handleModerators($forum, $moderatorIds);

        $this->handleModeratorHasAccess($forum, $data, $configParams);

        return true;
    }

    protected function handleModeratorHasAccess(Forum $forum, array $data, array $configParams): void
    {
        $inserted = Arr::get($data, 'inserted');
        $kept     = Arr::get($data, 'kept');
        $deleted = Arr::get($data, 'deleted');
        $currentModeratorIds = array_unique(array_merge($inserted, $kept));

        $insertedConfigs = Arr::get($configParams, 'inserted');
        $keptConfigs     = Arr::get($configParams, 'kept');
        $deletedConfigs  = Arr::get($configParams, 'deleted');
        $currentConfigs  = array_unique(array_merge($insertedConfigs, $keptConfigs));

        if (count($deleted)) {
            ModeratorAccess::query()
                ->where('forum_id', '=', $forum->entityId())
                ->whereIn('user_id', $deleted)
                ->delete();
        }

        if (count($deletedConfigs)) {
            ModeratorAccess::query()
                ->where('forum_id', '=', $forum->entityId())
                ->whereIn('permission_name', $deletedConfigs)
                ->delete();
        }

        if (!count($inserted) && !count($insertedConfigs)) {
            return;
        }

        if (count($inserted) && count($insertedConfigs)) {
            ModeratorAccess::query()
                ->where('forum_id', '=', $forum->entityId())
                ->delete();

            foreach ($currentModeratorIds as $userId) {
                $maps = array_map(function ($config) use ($userId, $forum) {
                    return [
                        'forum_id' => $forum->entityId(),
                        'user_id'  => $userId,
                        'user_type' => \MetaFox\User\Models\User::ENTITY_TYPE,
                        'permission_name' => $config,
                    ];
                }, $currentConfigs);

                if (!count($maps)) {
                    continue;
                }

                ModeratorAccess::query()
                    ->upsert($maps, ['forum_id', 'user_id', 'permission_name']);
            }

            return;
        }

        if (count($inserted) && !count($insertedConfigs)) {
            foreach ($inserted as $userId) {
                $maps = array_map(function ($config) use ($userId, $forum) {
                    return [
                        'forum_id' => $forum->entityId(),
                        'user_id'  => $userId,
                        'user_type' => \MetaFox\User\Models\User::ENTITY_TYPE,
                        'permission_name' => $config,
                    ];
                }, $currentConfigs);

                if (!count($maps)) {
                    continue;
                }

                ModeratorAccess::query()
                    ->upsert($maps, ['forum_id', 'user_id', 'permission_name']);
            }

            return;
        }

        $mapping = [];

        foreach ($currentModeratorIds as $userId) {
            $maps = array_map(function ($config) use ($userId, $forum) {
                return [
                    'forum_id' => $forum->entityId(),
                    'user_id'  => $userId,
                    'user_type' => \MetaFox\User\Models\User::ENTITY_TYPE,
                    'permission_name' => $config,
                ];
            }, $insertedConfigs);

            if (!count($maps)) {
                continue;
            }

            $mapping = array_merge($mapping, $maps);
        }

        if (!count($mapping)) {
            return;
        }

        ModeratorAccess::query()
            ->upsert($mapping, ['forum_id', 'user_id', 'permission_name']);
    }

    public function searchModerators(User $user, array $data): Collection
    {
        $builder = $this->getBuilder($user, Arr::get($data, 'q'));

        $limit = Arr::get($data, 'limit', 20);

        if (is_array($excludedIds = Arr::get($data, 'excluded_ids'))) {
            $builder->whereNotIn('users.id', $excludedIds);
        }

        return $builder
            ->limit($limit)
            ->get(['users.*']);
    }

    protected function getBuilder(User $context, ?string $search = null, string $view = Browse::VIEW_ALL, string $sort = 'full_name', string $sortType = MetaFoxConstant::SORT_ASC): Builder
    {
        $query = \MetaFox\User\Models\User::query();

        $sortScope = (new SortScope())
            ->setSort($sort)
            ->setSortType($sortType);

        $viewScope = (new ViewScope())
            ->setView($view);

        $blockedScope = (new BlockedScope())
            ->setContextId($context->entityId());

        if (is_string($view) && MetaFoxConstant::EMPTY_STRING !== $search) {
            $query->addScope(new SearchScope($search, ['search_name']));
        }

        return $query
            ->where('approve_status', MetaFoxConstant::STATUS_APPROVED)
            ->whereNotNull('verified_at')
            ->addScope($sortScope)
            ->addScope($viewScope)
            ->addScope($blockedScope);
    }
}
