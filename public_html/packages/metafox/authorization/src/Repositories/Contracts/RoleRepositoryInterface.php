<?php

namespace MetaFox\Authorization\Repositories\Contracts;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use MetaFox\Authorization\Models\Role;
use MetaFox\Authorization\Support\Support;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * Interface Role.
 * @mixin AbstractRepository
 * @method Role getModel()
 * @method Role find($id, $columns = ['*'])()
 */
interface RoleRepositoryInterface
{
    /**
     * View the role list.
     *
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewRoles(User $context, array $attributes): Paginator;

    /**
     * View a specific role data.
     *
     * @param User $context
     * @param int  $id
     *
     * @return Role
     * @throws AuthorizationException
     */
    public function viewRole(User $context, int $id): Role;

    /**
     * Create a new role.
     *
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Role
     * @throws AuthorizationException
     */
    public function createRole(User $context, array $attributes): Role;

    /**
     * Update an existed role.
     *
     * @param User                 $context
     * @param int                  $id
     * @param array<string, mixed> $attributes
     *
     * @return Role
     * @throws AuthorizationException
     */
    public function updateRole(User $context, int $id, array $attributes): Role;

    /**
     * Delete an existed role.
     * @param  User   $context
     * @param  int    $id
     * @param  int    $alternativeId
     * @param  string $deleteOption
     * @return bool
     */
    public function deleteRole(User $context, int $id, int $alternativeId, string $deleteOption = Support::DELETE_OPTION_MIGRATION): bool;

    /**
     * Assign permission list to an existed role.
     *
     * @param User          $context
     * @param int           $id
     * @param array<string> $permissions
     *
     * @return Role
     * @throws AuthorizationException
     */
    public function assignRolePermission(User $context, int $id, array $permissions): Role;

    /**
     * Remove permission list from an existed role.
     *
     * @param User          $context
     * @param int           $id
     * @param array<string> $permissions
     *
     * @return Role
     * @throws AuthorizationException
     */
    public function removeRolePermission(User $context, int $id, array $permissions): Role;

    /**
     * @return array<int,mixed>
     */
    public function getRoleOptions(): array;

    /**
     * @return array<int,mixed>
     */
    public function getRoleOptionsWithout(array $exclude): array;

    /**
     * @param User|null $user
     *
     * @return Role
     */
    public function roleOf(?User $user): Role;

    /**
     * @return array<int,mixed>
     */
    public function getRoleOptionsWithContextRole(User $context): array;

    /**
     * @return Collection
     */
    public function getUsableRoles(): Collection;

    /**
     * @return array
     */
    public function getDeleteOptions(): array;

    /**
     * @param  int  $deleteRoleId
     * @return void
     */
    public function updateRegisteredRoleSetting(int $deleteRoleId): void;

    /**
     * @param Role $role
     * @return bool
     */
    public function updateRootParentId(Role $role): bool;

    /**
     * @param array|null $rootParentIds
     * @return array
     */
    public function getCustomRoleIdsWithRootParentId(?array $rootParentIds = null): array;
}
