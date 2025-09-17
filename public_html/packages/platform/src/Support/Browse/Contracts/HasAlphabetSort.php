<?php

namespace MetaFox\Platform\Support\Browse\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Interface HasAlphabetSort
 * @package MetaFox\Platform\Support\Browse\Contracts
 */
interface HasAlphabetSort
{
    public const SORT_ALPHABETICAL = 'alphabet';

    public function applyAlphabetSort(Builder $builder, Model $model): void;

    public function getAlphabetSortColumn(): string;

    public function getAlphabetSort(): string;
}
