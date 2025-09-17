<?php

namespace MetaFox\Platform\Support\Browse\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Browse\Contracts\HasAlphabetSort;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;

/**
 *
 * Trait AlphabetSortTrait
 * @package MetaFox\Platform\Support\Browse\Traits
 * @mixin HasAlphabetSort
 * @mixin SortScope
 */
trait AlphabetSortTrait
{
    public function applyAlphabetSort(Builder $builder, Model $model): void
    {
        $table = $model->getTable();
        $sortType = $this->getSortType();
        $sortColumn = $this->getAlphabetSortColumn();

        $builder->orderBy($this->alias($table, $sortColumn), $sortType);
        $builder->orderBy($this->alias($table, 'id'), $sortType);
    }

    public function getAlphabetSort(): string
    {
        return self::SORT_ALPHABETICAL;
    }
}
