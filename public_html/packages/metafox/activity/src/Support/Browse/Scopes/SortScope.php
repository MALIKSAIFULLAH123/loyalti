<?php

namespace MetaFox\Activity\Support\Browse\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

class SortScope extends BaseScope
{
    public const SORT_DEFAULT         = Browse::SORT_RECENT;
    public const SORT_TYPE_DEFAULT    = Browse::SORT_TYPE_DESC;
    public const SORT_NEWEST_ACTIVITY = 'newest_activity';

    /**
     * @return array<int, string>
     */
    public static function getAllowSort(): array
    {
        return [
            Browse::SORT_RECENT,
            Browse::SORT_MOST_DISCUSSED,
            Browse::SORT_MOST_VIEWED,
            Browse::SORT_MOST_LIKED,
            self::SORT_NEWEST_ACTIVITY,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function getAllowSortType(): array
    {
        return [
            Browse::SORT_TYPE_ASC,
            Browse::SORT_TYPE_DESC,
        ];
    }

    /**
     * @param Builder $builder
     * @param Model   $model
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply(Builder $builder, Model $model)
    {
        // TODO: Implement apply() method.
    }
}
