<?php

namespace MetaFox\Event\Support\Browse\Scopes\Event;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope as PlatformWhenScope;

/**
 * Class SortScope.
 */
class WhenScope extends PlatformWhenScope
{
    public const WHEN_UPCOMING = 'upcoming';
    public const WHEN_ONGOING  = 'ongoing';
    public const WHEN_PAST     = 'past';

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
            self::WHEN_UPCOMING,
            self::WHEN_ONGOING,
            self::WHEN_PAST,
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
        $startColumn = sprintf('%s.%s', $model->getTable(), 'start_time');
        $endColumn   = sprintf('%s.%s', $model->getTable(), 'end_time');
        $date        = Carbon::now();
        $when        = $this->getWhen();

        switch ($when) {
            case self::WHEN_PAST:
                $builder->where($endColumn, '<=', $date);
                break;
            case self::WHEN_UPCOMING:
                $builder->where($startColumn, '>=', $date);
                break;
            case self::WHEN_ONGOING:
                $builder->where($startColumn, '<=', $date);
                $builder->where($endColumn, '>=', $date);
                break;
            default:
                $this->setWhenColumn('start_time');
                parent::apply($builder, $model);
        }
    }
}
