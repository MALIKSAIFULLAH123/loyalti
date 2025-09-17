<?php

namespace MetaFox\Authorization\Support\Browse\Scopes\Permission;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Browse\Scopes\SortScope as PlatformSort;

/**
 * @todo wait fot admincp feature
 */
class SortScope extends PlatformSort
{
    public static function getAllowSort(): array
    {
        return [];
    }

    public function apply(Builder $builder, Model $model)
    {
        $table = $model->getTable();

        $sortType = $this->getSortType();
        $builder->orderBy($this->alias($table, 'updated_at'), $sortType);
        $builder->orderBy($this->alias($table, 'id'), $sortType);
    }
}
