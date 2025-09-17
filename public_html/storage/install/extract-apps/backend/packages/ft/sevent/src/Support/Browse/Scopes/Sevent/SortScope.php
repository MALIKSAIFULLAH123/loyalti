<?php

namespace Foxexpert\Sevent\Support\Browse\Scopes\Sevent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Browse\Scopes\SortScope as BaseScope;
use MetaFox\Platform\Facades\Settings;
use Illuminate\Support\Facades\DB;

/**
 * Class SortScope.
 */
class SortScope extends BaseScope
{
    public const SORT_POPULAR = 'popular';
    public const SORT_START_SOON = 'start_soon';

    /**
     * @return array<int, string>
     */
    public static function getAllowSort(): array
    {
        return array_merge(parent::getAllowSort(), 
            [self::SORT_POPULAR], [self::SORT_START_SOON]);
    }

    public function apply(Builder $builder, Model $model)
    {
        parent::apply($builder, $model);

        $table    = $model->getTable();
        $sort     = $this->getSort();
        $sortType = $this->getSortType();

        if ($sort == self::SORT_POPULAR) {
            $builder->orderBy('sevents.total_attending', 'desc');
            $builder->orderBy($this->alias($table, 'id'), $sortType);
        }

        if ($sort == self::SORT_START_SOON) {
            $builder->orderBy('sevents.start_date', 'asc');
            $builder->orderBy($this->alias($table, 'id'), $sortType);
        }
    }
}
