<?php

namespace MetaFox\Friend\Support\Browse\Scopes\Friend;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope as PlatformWhenScope;

/**
 * Class SortScope.
 */
class WhenScope extends PlatformWhenScope
{
    /**
     * @return array<int, string>
     */
    public static function getAllowWhen(): array
    {
        return [
            Browse::WHEN_ALL,
            Browse::WHEN_THIS_MONTH,
            Browse::WHEN_THIS_WEEK,
            Browse::WHEN_TODAY,
        ];
    }

    /**
     * Apply when query.
     *
     * @param Builder $builder
     * @param Model   $model
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function apply(Builder $builder, Model $model)
    {
        $column = sprintf('%s.%s', 'friends', $this->getWhenColumn());
        $date   = Carbon::now();
        $when   = $this->getWhen();

        switch ($when) {
            case Browse::WHEN_THIS_MONTH:
                $builder->whereYear($column, '=', $date->year)
                    ->whereMonth($column, '=', $date->month);
                break;
            case Browse::WHEN_THIS_WEEK:
                $startDayOfWeek = $date->startOfWeek($this->getStartOfWeek());

                $endDayOfWeek = $startDayOfWeek->clone()->addDays(6);

                $builder->whereDate($column, '>=', $startDayOfWeek->toDateString())
                    ->whereDate($column, '<=', $endDayOfWeek->toDateString());
                break;
            case Browse::WHEN_TODAY:
                $builder->whereDate($column, '=', $date->toDateString());
                break;
        }
    }
}
