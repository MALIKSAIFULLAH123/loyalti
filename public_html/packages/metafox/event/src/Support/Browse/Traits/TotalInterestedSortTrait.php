<?php

namespace MetaFox\Event\Support\Browse\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Event\Support\Browse\Contracts\HasTotalInterestedSort;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;

/**
 * @mixin HasTotalInterestedSort
 * @mixin SortScope
 */
trait TotalInterestedSortTrait
{
    public function applyTotalInterestedSort(Builder $builder, Model $model): void
    {
        $table      = $model->getTable();
        $sortType   = $this->getSortType();
        $sortColumn = $this->getTotalInterestedSortColumn();

        $builder->orderBy($this->alias($table, $sortColumn), $sortType);
        $builder->orderBy($this->alias($table, 'id'), $sortType);
    }

    public function getTotalInterestedSort(): string
    {
        return self::SORT_MOST_INTERESTED;
    }
}
