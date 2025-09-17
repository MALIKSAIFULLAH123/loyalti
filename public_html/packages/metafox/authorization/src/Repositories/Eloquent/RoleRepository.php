<?php

namespace MetaFox\Authorization\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use MetaFox\Authorization\Jobs\DeleteUsersByRoleJob;
use MetaFox\Authorization\Models\Role;
use MetaFox\Authorization\Policies\RolePolicy;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Authorization\Support\Support;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\UserRole;
use MetaFox\User\Models\User as UserModel;
use MetaFox\User\Support\CacheManager;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class RoleRepository.
 * @property Role $model
 * @method   Role getModel()
 * @method   Role find($id, $columns = ['*'])()
 */
class RoleRepository extends AbstractRepository implements RoleRepositoryInterface
{
    public function model(): string
    {
        return Role::class;
    }

    public function viewRoles(User $context, array $attributes): Paginator
    {
        policy_authorize(RolePolicy::class, 'viewAny', $context);

        $limit = Arr::get($attributes, 'limit');

        $search = Arr::get($attributes, 'q');

        $query = $this->getModel()->newModelQuery()
            ->whereNotIn('id', $this->getSpecialRoles());

        if ($search) {
            $query = $query->addScope(new SearchScope($search, ['name']));
        }

        return $query->withCount('users')
            ->orderBy('id')
            ->paginate($limit);
    }

    public function viewRole(User $context, int $id): Role
    {
        $role = $this->find($id);
        policy_authorize(RolePolicy::class, 'view', $context, $role);

        return $role;
    }

    /**
     * NOTE: Special roles are only created for the first initial installation of the site.
     * Other roles created after that shall be considered non-special role.
     *
     * @param User                 $context
     * @param array<string, mixed> $attributes
     * @return Role
     * @throws AuthorizationException|ValidatorException
     */
    public function createRole(User $context, array $attributes): Role
    {
        policy_authorize(RolePolicy::class, 'create', $context);

        $attributes['name'] = $this->cleanTitle(Arr::get($attributes, 'name'));
        $parentId           = Arr::get($attributes, 'inherited_role');
        $parentRole         = $this->with(['permissions'])->find($parentId);

        policy_authorize(RolePolicy::class, 'inheritFromParent', $context, $parentRole);

        $role = parent::create(array_merge($attributes, ['is_special' => 0]));

        $role->refresh();

        $role->givePermissionTo($parentRole->permissions);

        $this->updateRootParentId($role);

        app('events')->dispatch('user.role.created', [$role]);

        return $role;
    }

    public function updateRole(User $context, int $id, array $attributes): Role
    {
        $role = $this->find($id);
        policy_authorize(RolePolicy::class, 'update', $context, $role);

        $attributes['name'] = $this->cleanTitle($attributes['name']);
        $role->fill($attributes)->save();
        $role->refresh();
        $role->loadCount('users');

        app('events')->dispatch('user.role.updated', [$role]);

        return $role;
    }

    public function deleteRole(User $context, int $id, int $alternativeId, string $deleteOption = Support::DELETE_OPTION_MIGRATION): bool
    {
        $role = $this->find($id);

        $alternativeRole = $this->find($alternativeId);

        policy_authorize(RolePolicy::class, 'delete', $context, $role);

        /*
         * TODO: Update this place when implementing deactive user accounts. We can deactive accounts first, then dispatch jobs to delete later
         */
        if ($deleteOption == Support::DELETE_OPTION_PERMANENTLY) {
            $this->deleteUserPermanently($context, $role);
        }

        $roleTable = config('permission.table_names.model_has_roles');

        if (is_string($roleTable) && '' !== $roleTable) {
            DB::table($roleTable)
                ->where(['role_id' => $role->entityId()])
                ->update(['role_id' => $alternativeRole->entityId()]);
        }

        app('events')->dispatch('user.role.deleted', [$role, $alternativeId]);

        return (bool) $this->delete($role->entityId());
    }

    public function updateRegisteredRoleSetting(int $deleteRoleId): void
    {
        $registeredRoleSetting = (int) Settings::get('user.on_register_user_group');
        if ($deleteRoleId != $registeredRoleSetting) {
            return;
        }

        Settings::save(['user.on_register_user_group' => UserRole::NORMAL_USER]);
    }

    protected function deleteUserPermanently(User $context, Role $role): void
    {
        $roleTable = config('permission.table_names.model_has_roles');

        if (!is_string($roleTable)) {
            return;
        }

        $userIds = DB::table('users')
            ->join($roleTable, function (JoinClause $joinClause) use ($roleTable, $role) {
                $joinClause->on($roleTable . '.model_id', '=', 'users.id')
                    ->where($roleTable . '.model_type', '=', UserModel::ENTITY_TYPE)
                    ->where($roleTable . '.role_id', '=', $role->entityId());
            })
            ->get(['users.id'])
            ->pluck('id')
            ->toArray();

        if (!count($userIds)) {
            return;
        }

        $userIds = array_unique($userIds);

        $chunks = array_chunk($userIds, 5);

        foreach ($chunks as $chunk) {
            DeleteUsersByRoleJob::dispatch($context, $chunk);
        }
    }

    /**
     * @param User          $context
     * @param int           $id
     * @param array<string> $permissions
     *
     * @return Role
     * @throws AuthorizationException
     */
    public function assignRolePermission(User $context, int $id, array $permissions): Role
    {
        $role = $this->find($id);
        policy_authorize(RolePolicy::class, 'update', $context, $role);

        if (!empty($permissions)) {
            $role->givePermissionTo($permissions);
        }

        return $role;
    }

    /**
     * @param User          $context
     * @param int           $id
     * @param array<string> $permissions
     *
     * @return Role
     * @throws AuthorizationException
     */
    public function removeRolePermission(User $context, int $id, array $permissions): Role
    {
        $role = $this->find($id);
        policy_authorize(RolePolicy::class, 'update', $context, $role);

        if (!empty($permissions)) {
            $role->revokePermissionTo($permissions);
        }

        return $role;
    }

    /**
     * @inheritdoc
     */
    public function getRoleOptions(): array
    {
        return Cache::rememberForever(CacheManager::AUTH_ROLE_OPTIONS_CACHE, function () {
            $result = [];
            foreach ($this->getUsableRoles() as $role) {
                $result[] = [
                    'value' => $role->id,
                    'label' => $role->name,
                ];
            }

            return $result;
        });
    }

    public function getRoleOptionsWithout(array $exclude): array
    {
        $roles = $this->getRoleOptions();

        return array_values(array_filter($roles, function ($role) use ($exclude) {
            return !in_array($role['value'], $exclude);
        }));
    }

    public function roleOf(?User $user): Role
    {
        /** @var ?Role $role */
        $role = LoadReduce::remember(
            sprintf('user::roles.first(user:%s)', $user?->id),
            fn () => $user?->roles->first()
        );
        if (!$role) {
            $role = $this->find(UserRole::GUEST_USER_ID);
        }

        return $role;
    }

    /**
     * @param User $context
     * @return array<int, mixed>
     */
    public function getRoleOptionsWithContextRole(User $context): array
    {
        return Cache::rememberForever(CacheManager::AUTH_ROLE_OPTIONS_CACHE, function () use ($context) {
            $roles = $this->getModel()
                ->newModelQuery()
                ->where(function ($q) use ($context) {
                    $q->where('is_special', '=', 0)
                        ->orWhere('id', '>=', $context->getSmallestRoleId());
                })
                ->whereNotIn('id', $this->getSpecialRoles())
                ->get()
                ->collect();

            return $roles->map(function (Role $role) {
                return [
                    'value' => $role->entityId(),
                    'label' => $role->name,
                ];
            })->values()->toArray();
        });
    }

    public function getUsableRoles(): Collection
    {
        return $this->getModel()->newModelQuery()
            ->whereNotIn('id', $this->getSpecialRoles())
            ->get();
    }

    protected function getSpecialRoles(): array
    {
        return [
            UserRole::PAGE_USER,
            UserRole::BANNED_USER,
        ];
    }

    public function getDeleteOptions(): array
    {
        return [
            [
                'label' => __p('authorization::phrase.relocate_users_who_belong_to_it_to_another_role'),
                'value' => Support::DELETE_OPTION_MIGRATION,
            ],
            [
                'label' => __p('authorization::phrase.delete_completely'),
                'value' => Support::DELETE_OPTION_PERMANENTLY,
            ],
        ];
    }

    public function updateRootParentId(Role $role): bool
    {
        if (!$role->parent_id) {
            return false;
        }

        if (!$role->parentRole instanceof Role) {
            return false;
        }

        $rootParentId = $role->parentRole->root_parent_id;

        if (0 == $role->parentRole->parent_id) {
            $rootParentId = $role->parentRole->entityId();
        }

        $role->update(['root_parent_id' => $rootParentId]);

        return true;
    }

    public function getCustomRoleIdsWithRootParentId(?array $rootParentIds = null): array
    {
        $groupBy = LoadReduce::remember('authorization::getCustomRolesWithRootParentId', function () {
            $items = $this->getModel()->newQuery()
                ->where('parent_id', '>', 0)
                ->get(['id', 'root_parent_id'])
                ->toArray();

            if (!count($items)) {
                return [];
            }

            $transform = [];

            foreach ($items as $item) {
                $transform[Arr::get($item, 'root_parent_id')][] = Arr::get($item, 'id');
            }

            return $transform;
        });

        if (!count($groupBy) || !is_array($rootParentIds)) {
            return $groupBy;
        }

        $collects = [];

        foreach ($rootParentIds as $rootParentId) {
            if (!Arr::has($groupBy, $rootParentId)) {
                continue;
            }

            Arr::set($collects, $rootParentId, Arr::get($groupBy, $rootParentId));
        }

        return $collects;
    }
}
