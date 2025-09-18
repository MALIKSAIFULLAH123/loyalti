<?php

namespace MetaFox\Group\Support\Browse\Scopes\GroupSimilar;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Carbon;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope as PlatformWhenScope;

/**
 * Class WhenScope.
 */
class WhenScope extends PlatformWhenScope
{
    /** @var string */
    private string $table;

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @param string $table
     *
     * @return self
     */
    public function setTable(string $table): self
    {
        $this->table = $table;

        return $this;
    }
    public function apply(Builder $builder, Model $model)
    {
        if ($this->getWhen() == self::WHEN_DEFAULT) {
            return;
        }

        $builder->leftJoin('group_members', function (JoinClause $join) {
            $join->on('group_members.group_id', '=', 'groups.id');
            $this->applyJoinBuilder($join);
        })->whereNotNull('group_members.group_id');
    }
    public function applyJoinBuilder(JoinClause $builder)
    {
        $column = sprintf('%s.%s', $this->getTable(), $this->getWhenColumn());
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
