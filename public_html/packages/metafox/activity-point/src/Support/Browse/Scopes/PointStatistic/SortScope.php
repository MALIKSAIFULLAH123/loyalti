<?php

namespace MetaFox\ActivityPoint\Support\Browse\Scopes\PointStatistic;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\SortScope as BaseScope;

class SortScope extends BaseScope
{
    public const SORT_DEFAULT      = self::SORT_FULL_NAME;
    public const SORT_TYPE_DEFAULT = Browse::SORT_TYPE_DESC;

    public const SORT_FULL_NAME     = 'full_name';
    public const SORT_CURRENT_POINT = 'current_points';

    /**
     * @return array<int, string>
     */
    public static function getAllowSort(): array
    {
        return Arr::pluck(self::getSortOptions(), 'value');
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function getSortOptions(): array
    {
        return [
            [
                'label' => __p('core::phrase.name'),
                'value' => self::SORT_FULL_NAME,
            ],
            [
                'label' => __p('activitypoint::phrase.sort_current_point'),
                'value' => self::SORT_CURRENT_POINT,
            ],
        ];
    }

    /**
     * @var string
     */
    private string $sort = self::SORT_DEFAULT;

    /**
     * @return string
     */
    public function getSort(): string
    {
        return $this->sort;
    }

    /**
     * @param string $sort
     *
     * @return self
     */
    public function setSort(string $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @param Builder $builder
     * @param Model   $model
     */
    public function apply(Builder $builder, Model $model): void
    {
        $table = $model->getTable();

        $sort     = $this->getSort();
        $sortType = $this->getSortType();

        switch ($sort) {
            case self::SORT_FULL_NAME:
                $builder->join('users', function (JoinClause $joinClause) use ($table) {
                    $joinClause->on('users.id', '=', $this->alias($table, 'id'));
                })->orderBy('users.full_name', $sortType);
                $builder->orderBy($this->alias($table, 'id'), $sortType);
                break;
            case self::SORT_CURRENT_POINT:
                $builder->orderBy($this->alias($table, 'current_points'), $sortType);
                $builder->orderBy($this->alias($table, 'id'), $sortType);
                break;
        }
    }
}
