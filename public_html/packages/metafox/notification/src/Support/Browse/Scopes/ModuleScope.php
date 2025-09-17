<?php

namespace MetaFox\Notification\Support\Browse\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use MetaFox\Authorization\Support\Browse\Scopes\Permission\ModuleScope as BaseScope;

/**
 * Class ModuleScope.
 */
class ModuleScope extends BaseScope
{
    /**
     * @param Builder $builder
     * @param Model   $model
     */
    public function apply(Builder $builder, Model $model)
    {
        $table = $model->getTable();
        if (Schema::hasColumn($table, 'require_module_id')) {
            $builder->where(DB::raw("CASE when $table.require_module_id is not null then $table.require_module_id ELSE $table.module_id end"), '=', $this->getModuleId());
            return;
        }

        $builder->where($this->alias($model->getTable(), 'module_id'), '=', $this->getModuleId());
    }
}
