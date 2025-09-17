<?php

namespace MetaFox\Platform\Support\Browse\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Browse\Contracts\HasFeatureSort;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;

/**
 * Trait FeatureSortTrait.
 * @mixin HasFeatureSort
 * @mixin SortScope
 */
trait FeatureSortTrait
{
    public function applyFeatureSort(Builder $builder, Model $model): void
    {
        $table = $model->getTable();
        $sortType = $this->getSortType();
        $sortColumn = $this->getFeatureSortColumn();

        $builder->orderBy($this->alias($table, $sortColumn), $sortType);
        $builder->orderBy($this->alias($table, 'id'), $sortType);
    }

    public function getFeatureSort(): string
    {
        return self::SORT_FEATURE;
    }
}
