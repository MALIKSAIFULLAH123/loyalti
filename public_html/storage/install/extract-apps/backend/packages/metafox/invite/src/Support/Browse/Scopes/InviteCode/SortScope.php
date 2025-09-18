<?php

namespace MetaFox\Invite\Support\Browse\Scopes\InviteCode;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\SortScope as BaseScope;

class SortScope extends BaseScope
{
    public const SORT_DEFAULT      = self::SORT_FULL_NAME;
    public const SORT_TYPE_DEFAULT = Browse::SORT_TYPE_DESC;

    public const SORT_FULL_NAME  = 'full_name';
    public const SORT_UPDATED_AT = 'updated_at';

    /**
     * @inheritDoc
     */
    public function apply(Builder $builder, Model $model)
    {
        $table = $model->getTable();

        $sort     = $this->getSort();
        $sortType = $this->getSortType();

        switch ($sort) {
            case self::SORT_FULL_NAME:
                $builder->leftJoin('users', function (JoinClause $joinClause) use ($table) {
                    $joinClause->on('users.id', '=', $this->alias($table, 'user_id'));
                });
                $builder->orderByRaw("CASE WHEN (users.full_name = '' OR users.full_name IS NULL) THEN users.user_name ELSE users.full_name END $sortType");
                break;
            case self::SORT_UPDATED_AT:
                $builder->orderBy($this->alias($table, 'updated_at'), $sortType);
                break;
        }
    }
}
