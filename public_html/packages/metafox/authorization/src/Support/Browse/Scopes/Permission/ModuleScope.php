<?php

namespace MetaFox\Authorization\Support\Browse\Scopes\Permission;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

/**
 * Class RoleScope.
 */
class ModuleScope extends BaseScope
{
    /**
     * @var string
     */
    protected string $moduleId;

    /**
     * @param Builder $builder
     * @param Model   $model
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder->where($this->alias($model->getTable(), 'module_id'), '=', $this->getModuleId());
    }

    /**
     * @return string
     */
    public function getModuleId(): string
    {
        return $this->moduleId;
    }

    /**
     * @param string $module
     */
    public function setModuleId(string $module): void
    {
        $this->moduleId = $module;
    }
}
