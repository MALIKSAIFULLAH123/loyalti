<?php

namespace MetaFox\Quiz\Support\Browse\Scopes\Quiz;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Contracts\HasAlphabetSort;
use MetaFox\Platform\Support\Browse\Scopes\SortScope as PlatformSortScope;
use MetaFox\Platform\Support\Browse\Traits\AlphabetSortTrait;

class SortScope extends PlatformSortScope implements HasAlphabetSort
{
    use AlphabetSortTrait;

    public const SORT_MOST_PLAYED         = 'most_played';
    public const SORT_ALPHABETICAL_COLUMN = 'title';
    public const SORT_DEFAULT             = Browse::SORT_RECENT;
    public const SORT_TYPE_DEFAULT        = Browse::SORT_TYPE_DESC;

    public static function getAllowSort(): array
    {
        return [
            Browse::SORT_RECENT,
            Browse::SORT_LATEST,
            Browse::SORT_MOST_DISCUSSED,
            Browse::SORT_MOST_VIEWED,
            Browse::SORT_MOST_LIKED,
            self::SORT_ALPHABETICAL,
            self::SORT_MOST_PLAYED,
            Browse::SORT_LATEST,
        ];
    }

    public function apply(Builder $builder, Model $model)
    {
        $table = $model->getTable();

        $sort     = $this->getSort();
        $sortType = $this->getSortType();

        // @todo HasTotalView, HasTotalLike v.v... interfaces.

        // Apply parent sort
        parent::apply($builder, $model);

        if (self::SORT_MOST_PLAYED == $sort) {
            $builder->orderBy($this->alias($table, 'total_play'), $sortType);
            $builder->orderBy($this->alias($table, 'id'), $sortType);
        }
    }

    /**
     * @return string
     */
    public function getAlphabetSortColumn(): string
    {
        return self::SORT_ALPHABETICAL_COLUMN;
    }
}
