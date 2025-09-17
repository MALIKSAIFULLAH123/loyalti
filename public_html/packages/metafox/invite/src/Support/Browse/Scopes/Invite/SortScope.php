<?php

namespace MetaFox\Invite\Support\Browse\Scopes\Invite;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\SortScope as PlatformSortScope;

/**
 * Class SortScope.
 */
class SortScope extends PlatformSortScope
{

    public const SORT_SIGNUP_DATE_COLUMN = 'signup_date';
    public const SORT_FULL_NAME_COLUMN   = 'full_name';

    /**
     * @return array<int, string>
     */
    public static function getAllowSort(): array
    {
        return [
            Browse::SORT_LATEST,
            Browse::SORT_RECENT,
            self::SORT_SIGNUP_DATE_COLUMN,
            self::SORT_FULL_NAME_COLUMN,
        ];
    }

    public function apply(Builder $builder, Model $model)
    {
        parent::apply($builder, $model);
        $table    = $model->getTable();
        $sortType = $this->getSortType();

        $aliasJoin = 'sort_user';

        switch ($this->getSort()) {
            case self::SORT_SIGNUP_DATE_COLUMN:
                $builder->join("users as $aliasJoin", function (JoinClause $joinClause) use ($table, $aliasJoin) {
                    $joinClause->on($this->alias($aliasJoin, 'id'), '=', $this->alias($table, 'user_id'));
                });
                $builder->orderBy($this->alias($aliasJoin, 'created_at'), $sortType);
                break;
            case self::SORT_FULL_NAME_COLUMN:
                $builder->join("users as $aliasJoin", function (JoinClause $joinClause) use ($table, $aliasJoin) {
                    $joinClause->on($this->alias($aliasJoin, 'id'), '=', $this->alias($table, 'user_id'));
                });
                $builder->orderByRaw("CASE WHEN ($aliasJoin.full_name = '' OR $aliasJoin.full_name IS NULL) THEN $aliasJoin.user_name ELSE $aliasJoin.full_name END $sortType");
        }
    }
}
