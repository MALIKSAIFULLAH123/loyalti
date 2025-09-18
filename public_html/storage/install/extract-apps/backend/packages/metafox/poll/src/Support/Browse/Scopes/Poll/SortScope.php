<?php

namespace MetaFox\Poll\Support\Browse\Scopes\Poll;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Contracts\HasAlphabetSort;
use MetaFox\Platform\Support\Browse\Scopes\SortScope as BaseScope;
use MetaFox\Platform\Support\Browse\Traits\AlphabetSortTrait;

/**
 * Class SortScope.
 */
class SortScope extends BaseScope implements HasAlphabetSort
{
    use AlphabetSortTrait;

    public const SORT_ALPHABETICAL_COLUMN = 'question';
    public const SORT_MOST_VOTED          = 'most_voted';
    public const SORT_DEFAULT             = Browse::SORT_RECENT;
    public const SORT_TYPE_DEFAULT        = Browse::SORT_TYPE_DESC;

    /**
     * @return array<int, string>
     */
    public static function getAllowSort(): array
    {
        return [
            Browse::SORT_RECENT,
            Browse::SORT_LATEST,
            Browse::SORT_MOST_VIEWED,
            Browse::SORT_MOST_DISCUSSED,
            Browse::SORT_MOST_LIKED,
            self::SORT_MOST_VOTED,
            self::SORT_ALPHABETICAL,
        ];
    }

    /**
     * @param Builder $builder
     * @param Model   $model
     */
    public function apply(Builder $builder, Model $model)
    {
        $table = $model->getTable();

        $sort     = $this->getSort();
        $sortType = $this->getSortType();

        // @todo HasTotalView, HasTotalLike v.v... interfaces.

        // Apply parent sort
        parent::apply($builder, $model);

        if (self::SORT_MOST_VOTED == $sort) {
            $builder->orderBy($this->alias($table, 'total_vote'), $sortType);
            $builder->orderBy($this->alias($table, 'id'), $sortType);
        }
    }

    public function getAlphabetSortColumn(): string
    {
        return self::SORT_ALPHABETICAL_COLUMN;
    }
}
