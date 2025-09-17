<?php

namespace MetaFox\Forum\Repositories\Eloquent;

use MetaFox\Forum\Models\Forum;
use MetaFox\Forum\Support\Facades\Forum as ForumFacade;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Forum\Repositories\UserRolePermissionRepositoryInterface;
use MetaFox\Forum\Models\UserRolePermission;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class UserRolePermissionRepository.
 */
class UserRolePermissionRepository extends AbstractRepository implements UserRolePermissionRepositoryInterface
{
    public function model()
    {
        return UserRolePermission::class;
    }

    public function getPermissionOptions(): array
    {
        $permissions = [
            [
                'name'   => 'can_start_thread',
                'phrase' => __p('forum::permission.can_start_new_discussion_label'),
                'value'  => true,
            ],
            [
                'name'   => 'can_view_forum',
                'phrase' => __p('forum::phrase.can_view_this_forum'),
                'value'  => true,
            ],
            [
                'name'   => 'can_view_thread_content',
                'phrase' => __p('forum::permission.can_view_thread_content_label'),
                'value'  => true,
            ],
        ];

        return $permissions;
    }

    public function getAllPermissionByForumId(int $forumId): array
    {
        return $this->model->where('forum_id', $forumId)->get()->toArray();
    }

    public function updateRolePermission(User $context, Forum $forum, array $data): bool
    {
        $forumId = $forum->id;
        $mainRoleId = $data['role_id'];
        unset($data['role_id']);
        foreach ($data as $key => $value) {
            $permission = explode('__', $key);
            $permissionName = $permission[0];
            $roleId = $permission[1];

            if ($roleId == $mainRoleId) {
                $permission = $this->model->where('forum_id', $forumId)
                    ->where('role_id', $roleId)
                    ->where('permission_name', $permissionName)
                    ->first();

                if ($permission) {
                    $permission->update(['permission_value' => $value]);
                    $cacheKeyMobile = ForumFacade::getViewMobileCacheId() . "_{$roleId}";
                    $cacheKey = ForumFacade::getViewCacheId() . "_{$roleId}";
                    localCacheStore()->forget($cacheKeyMobile);
                    localCacheStore()->forget($cacheKey);
                } else {
                    $this->model->create([
                        'forum_id' => $forumId,
                        'role_id' => $roleId,
                        'permission_name' => $permissionName,
                        'permission_value' => $value,
                    ]);
                }
            }
        }

        return true;
    }

    public function hasAccess(int $userRoleId, int|null $forumId, string $permission): bool
    {
        if (empty($forumId) || empty($userRoleId)) {
            return false;
        }

        return LoadReduce::remember(sprintf('forum_user_role_permission_%s_%s_%s', $userRoleId, $forumId, $permission), function () use ($forumId, $userRoleId, $permission) {
            $permission = $this->model->where('forum_id', $forumId)
                ->where('role_id', $userRoleId)
                ->where('permission_name', $permission)
                ->first();

            if ($permission) {
                return (bool) $permission->permission_value;
            }

            return true;
        });
    }
}
