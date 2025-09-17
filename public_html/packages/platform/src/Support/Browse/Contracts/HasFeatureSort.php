<?php

namespace MetaFox\Platform\Support\Browse\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Interface HasFeatureSort
 * @package MetaFox\Platform\Support\Browse\Contracts
 */
interface HasFeatureSort
{
    public const SORT_FEATURE = 'feature';

    public function applyFeatureSort(Builder $builder, Model $model): void;

    public function getFeatureSortColumn(): string;

    public function getFeatureSort(): string;
}
