<?php

namespace MetaFox\Event\Support\Browse\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Interfaces.
 */
interface HasTotalInterestedSort
{
    public const SORT_MOST_INTERESTED = 'most_interested';

    public function applyTotalInterestedSort(Builder $builder, Model $model): void;

    public function getTotalInterestedSortColumn(): string;

    public function getTotalInterestedSort(): string;
}
