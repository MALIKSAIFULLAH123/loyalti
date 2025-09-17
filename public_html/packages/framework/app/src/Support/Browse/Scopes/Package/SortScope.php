<?php

namespace MetaFox\App\Support\Browse\Scopes\Package;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Browse\Scopes\SortScope as BaseScope;

/**
 * Class SortScope.
 */
class SortScope extends BaseScope
{
    const SORT_TITLE_COLUMN     = 'title';
    const SORT_TYPE_COLUMN      = 'type';
    const SORT_IS_CORE_COLUMN   = 'is_core';
    const SORT_IS_ACTIVE_COLUMN = 'is_active';
    const SORT_AUTHOR_COLUMN    = 'author';
    const SORT_UPDATE_AVAILABLE = 'update_available';

    public function apply(Builder $builder, Model $model)
    {
        $table    = $model->getTable();
        $sort     = $this->getSort();
        $sortType = $this->getSortType();

        // Apply parent sort
        switch ($sort) {
            case self::SORT_IS_ACTIVE_COLUMN:
            case self::SORT_IS_CORE_COLUMN:
            case self::SORT_TITLE_COLUMN:
            case self::SORT_TYPE_COLUMN:
            case self::SORT_AUTHOR_COLUMN:
                $builder->orderBy($this->alias($table, $sort), $sortType);
                $builder->orderBy($this->alias($table, 'id'), $sortType);
                break;
            case self::SORT_UPDATE_AVAILABLE:
                $builder->orderByRaw("CASE WHEN latest_version > version THEN 1 ELSE 0 END $sortType");
                $builder->orderBy($this->alias($table, 'id'), $sortType);
                break;
        }
    }

}
