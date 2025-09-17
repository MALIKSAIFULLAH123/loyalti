<?php

namespace MetaFox\Platform\Support\Browse\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Interface HasTotalMemberSort
 * @package MetaFox\Platform\Support\Browse\Contracts
 */
interface HasTotalMemberSort
{
    public const SORT_MOST_MEMBER = 'most_member';

    public function applyTotalMemberSort(Builder $builder, Model $model): void;

    public function getTotalMemberSortColumn(): string;

    public function getTotalMemberSort(): string;
}
