<?php

namespace MetaFox\Authorization\Support\Browse\Scopes\Permission;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

/**
 * Class FilterByRoleScope.
 */
class FilterByRoleScope extends BaseScope
{
    protected int $roleId;

    /**
     * @param Builder $builder
     * @param Model   $model
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder->whereHas('roles', function ($q) {
            $q->where('id', '=', $this->getRoleId());
        });
    }

    public function getRoleId(): int
    {
        return $this->roleId;
    }

    /**
     * @param  int               $roleId
     * @return FilterByRoleScope
     */
    public function setRoleId(int $roleId): self
    {
        $this->roleId = $roleId;

        return $this;
    }
}
