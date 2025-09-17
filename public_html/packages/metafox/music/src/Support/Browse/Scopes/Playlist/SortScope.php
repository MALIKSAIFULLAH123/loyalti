<?php

namespace MetaFox\Music\Support\Browse\Scopes\Playlist;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Browse\Scopes\SortScope as BaseScope;

/**
 * Class SortScope.
 */
class SortScope extends BaseScope
{
    public const SORT_MOST_SONG = 'most_song';

    /**
     * @return array<int, string>
     */
    public static function getAllowSort(): array
    {
        return array_merge(parent::getAllowSort(), [self::SORT_MOST_SONG]);
    }

    public function apply(Builder $builder, Model $model)
    {
        parent::apply($builder, $model);

        $table    = $model->getTable();
        $sort     = $this->getSort();
        $sortType = $this->getSortType();

        if ($sort == static::SORT_MOST_SONG) {
            $builder->orderBy($this->alias($table, 'total_track'), $sortType);
            $builder->orderBy($this->alias($table, 'id'), $sortType);
        }
    }
}
