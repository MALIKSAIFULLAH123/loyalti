<?php

namespace MetaFox\Event\Support\Browse\Scopes\Event;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use MetaFox\Event\Support\Browse\Contracts\HasTotalInterestedSort;
use MetaFox\Event\Support\Browse\Traits\TotalInterestedSortTrait;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Contracts\HasTotalMemberSort;
use MetaFox\Platform\Support\Browse\Scopes\SortScope as PlatformSortScope;
use MetaFox\Platform\Support\Browse\Traits\TotalMemberSortTrait;

/**
 * Class SortScope.
 */
class SortScope extends PlatformSortScope implements HasTotalMemberSort, HasTotalInterestedSort
{
    use TotalMemberSortTrait;
    use TotalInterestedSortTrait;

    public const SORT_TOTAL_MEMBER_COLUMN     = 'total_member';
    public const SORT_TOTAL_INTERESTED_COLUMN = 'total_interested';
    public const SORT_END_TIME                = 'end_time';
    public const SORT_UPCOMING                = 'upcoming';
    public const SORT_RANDOM                  = 'random';

    /**
     * @return array<int, string>
     */
    public static function getAllowSort(): array
    {
        return [
            Browse::SORT_MOST_VIEWED,
            Browse::SORT_LATEST,
            Browse::SORT_RECENT,
            Browse::SORT_MOST_LIKED,
            Browse::SORT_MOST_DISCUSSED,
            self::SORT_MOST_INTERESTED,
            self::SORT_MOST_MEMBER,
            self::SORT_END_TIME,
            self::SORT_RANDOM,
            self::SORT_UPCOMING,
        ];
    }

    public function getTotalMemberSortColumn(): string
    {
        return self::SORT_TOTAL_MEMBER_COLUMN;
    }

    public function getTotalInterestedSortColumn(): string
    {
        return self::SORT_TOTAL_INTERESTED_COLUMN;
    }

    /**
     * @param Builder $builder
     * @param Model   $model
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function apply(Builder $builder, Model $model)
    {
        $table = $model->getTable();

        $sort     = $this->getSort();
        $sortType = $this->getSortType();

        $date = Carbon::now();
        $builder->addSelect(DB::raw("CASE WHEN $table.end_time >= '$date' THEN 0 ELSE 1 END as is_ended"));
        $builder->orderBy('is_ended');

        switch ($sort) {
            case self::SORT_MOST_INTERESTED:
                $builder->orderBy($this->alias($table, 'total_interested'), $sortType);
                $builder->orderBy($this->alias($table, 'id'), $sortType);
                break;
            case self::SORT_END_TIME:
                $builder->orderBy($this->alias($table, 'end_time'), $sortType);
                $builder->orderBy($this->alias($table, 'id'), $sortType);
                break;
            case self::SORT_UPCOMING:
                $builder->orderBy($this->alias($table, 'start_time'), $sortType);
                break;
            case self::SORT_RANDOM:
                $builder->inRandomOrder();
                break;
            case Browse::SORT_MOST_DISCUSSED:
                $builder->orderBy($this->alias($table, 'total_feed'), $sortType);
                $builder->orderBy($this->alias($table, 'id'), $sortType);
                break;
            case Browse::SORT_RECENT:
            case Browse::SORT_LATEST:
                $builder->addSelect(DB::raw("CASE WHEN $table.end_time >= '$date' THEN $table.start_time ELSE '$date' END as order_by_start"))
                    ->addSelect(DB::raw("CASE WHEN $table.end_time <= '$date' THEN $table.end_time ELSE '$date' END as order_by_end"));

                $builder->orderByRaw('order_by_start ASC, order_by_end DESC');
                break;
            default:
                parent::apply($builder, $model);
        }
    }
}
