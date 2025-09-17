<?php

namespace MetaFox\Photo\Support\Browse\Scopes\Album;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SortScope.
 */
class SortScope extends \MetaFox\Platform\Support\Browse\Scopes\SortScope
{
    public const SORT_MOST_PHOTO = 'most_photo';

    /**
     * @return array<int, string>
     */
    public static function getAllowSort(): array
    {
        return array_merge(parent::getAllowSort(), [self::SORT_MOST_PHOTO]);
    }

    /**
     * @inheritdoc
     */
    public function apply(Builder $builder, Model $model)
    {
        parent::apply($builder, $model);

        $table    = $model->getTable();
        $sort     = $this->getSort();
        $sortType = $this->getSortType();

        if ($sort == self::SORT_MOST_PHOTO) {
            $builder->orderBy($this->alias($table, 'total_photo'), $sortType);
            $builder->orderBy($this->alias($table, 'id'), $sortType);
        }
    }
}
