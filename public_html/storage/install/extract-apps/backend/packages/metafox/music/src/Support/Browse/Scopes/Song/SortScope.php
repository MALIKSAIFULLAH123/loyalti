<?php

namespace MetaFox\Music\Support\Browse\Scopes\Song;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Browse\Scopes\SortScope as BaseScope;

/**
 * Class SortScope.
 */
class SortScope extends BaseScope
{
    public const SORT_MOST_PLAYED = 'most_played';

    /**
     * @return array<int, string>
     */
    public static function getAllowSort(): array
    {
        return array_merge(parent::getAllowSort(), [self::SORT_MOST_PLAYED]);
    }

    public function apply(Builder $builder, Model $model)
    {
        parent::apply($builder, $model);

        $table    = $model->getTable();
        $sort     = $this->getSort();
        $sortType = $this->getSortType();

        switch ($sort) {
            case static::SORT_MOST_PLAYED:
                $builder->orderBy($this->alias($table, 'total_play'), $sortType)
                    ->orderBy($this->alias($table, 'id'), $sortType);
                break;
        }
    }
}
