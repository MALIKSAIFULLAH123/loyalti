<?php

namespace MetaFox\Group\Support\Browse\Scopes\GroupMember;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Contracts\HasAlphabetSort;
use MetaFox\Platform\Support\Browse\Scopes\SortScope as PlatformSortScope;
use MetaFox\Platform\Support\Browse\Traits\AlphabetSortTrait;

/**
 * Class SortScope.
 */
class SortScope extends PlatformSortScope implements HasAlphabetSort
{
    use AlphabetSortTrait;

    const SORT_NAME = 'name';

    /**
     * @return array<int, string>
     */
    public static function getAllowSort(): array
    {
        return [
            Browse::SORT_RECENT,
            self::SORT_NAME,
        ];
    }

    public function getAlphabetSortColumn(): string
    {
        return "us.full_name";
    }

    public function apply(Builder $builder, Model $model)
    {
        $table    = $model->getTable();
        $sort     = $this->getSort();
        $sortType = $this->getSortType();

        switch ($sort) {
            case Browse::SORT_RECENT;
                $builder->orderBy($table . '.created_at', $sortType);
                break;
            case self::SORT_NAME:
                $builder->join('users as us', 'us.id', '=', $table . '.user_id');
                $builder->orderBy($this->getAlphabetSortColumn(), $sortType);
                break;
        }
    }
}
