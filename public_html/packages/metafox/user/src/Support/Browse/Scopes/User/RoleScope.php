<?php

namespace MetaFox\User\Support\Browse\Scopes\User;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

class RoleScope extends BaseScope
{
    /**
     * @return array<string>
     */
    public static function getAllowRoles(): array
    {
        return collect(resolve(RoleRepositoryInterface::class)
            ->getRoleOptions())
            ->pluck('value')
            ->toArray();
    }

    /**
     * @return array<string>
     */
    public static function getAllowFilterRoles(): array
    {
        $excludeLists = Settings::get('user.user_role_filter_exclude') ?: [];

        return collect(resolve(RoleRepositoryInterface::class)
            ->getRoleOptions())
            ->whereNotIn('value', $excludeLists)
            ->pluck('value')
            ->toArray();
    }

    /**
     * @var array
     */
    private array $roles        = [];
    private array $excludeRoles = [];

    public function getExcludeRoles(): array
    {
        return $this->excludeRoles;
    }

    public function setExcludeRoles(array $excludeRoles): void
    {
        $this->excludeRoles = $excludeRoles;
    }

    /**
     * @param ?array $roles
     *
     * @return RoleScope
     */
    public function setRoles(?array $roles = null): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @param ?string $role
     *
     * @return RoleScope
     */
    public function setRole(?string $role = null): self
    {
        if (!empty($role)) {
            $this->roles = [$role];
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param Builder $builder
     * @param Model   $model
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply(Builder $builder, Model $model)
    {
        $excludeLists = $this->getExcludeRoles();
        if (!empty($excludeLists)) {
            $builder->whereHas('roles', function (Builder $q) use ($excludeLists) {
                $q->whereNotIn('role_id', $excludeLists);
            });
        }

        $roles = $this->getRoles();

        if (empty($roles)) {
            return;
        }

        $builder->whereHas('roles', function (Builder $q) use ($roles) {
            $q->whereIn('role_id', $roles);
        });
    }
}
