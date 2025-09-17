<?php
namespace MetaFox\Featured\Services\Contracts;

use Illuminate\Support\Collection;
use MetaFox\Authorization\Models\Role;
use MetaFox\Platform\Contracts\User;

interface SettingServiceInterface
{
    /**
     * @param int $roleId
     * @return array
     */
    public function getSettings(int $roleId): array;

    /**
     * @param User  $context
     * @param Role  $role
     * @param array $settings
     * @return bool
     */
    public function updateSettings(User $context, Role $role, array $settings): bool;

    /**
     * @param array $settings
     * @return Collection
     */
    public function getPermissionsByName(array $settings): Collection;
}
