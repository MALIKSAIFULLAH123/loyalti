<?php

namespace MetaFox\Platform\Support\Browse\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Support\Browse\Browse;

/**
 * Class WhenScope.
 */
class WhenScope extends BaseScope
{
    public const WHEN_DEFAULT = Browse::WHEN_ALL;

    public ?string $fromDate   = null;
    public ?string $toDate     = null;
    public ?string $toColumn   = null;
    public ?string $fromColumn = null;

    public function getToColumn(): ?string
    {
        return $this->toColumn ?? $this->getFromColumn() ?? $this->getWhenColumn();
    }

    public function setToColumn(?string $toColumn): void
    {
        if ($this->toColumn === MetaFoxConstant::EMPTY_STRING) {
            $toColumn = null;
        }

        $this->toColumn = $toColumn;
    }

    public function getFromColumn(): ?string
    {
        return $this->fromColumn ?? $this->getWhenColumn();
    }

    public function setFromColumn(?string $fromColumn): void
    {
        if ($this->fromColumn === MetaFoxConstant::EMPTY_STRING) {
            $fromColumn = null;
        }

        $this->fromColumn = $fromColumn;
    }

    public function getFromDate(): ?string
    {
        return $this->fromDate;
    }

    public function setFromDate(?string $fromDate): void
    {
        if ($fromDate) {
            $fromDate = $this->convertUTCDate($fromDate);
        }

        $this->fromDate = $fromDate;
    }

    public function getToDate(): ?string
    {
        return $this->toDate;
    }

    public function setToDate(?string $toDate): void
    {
        if ($toDate !== null) {
            $toDate = $this->convertUTCDate($toDate);
        }

        $this->toDate = $toDate;
    }

    /**
     * @return string[]
     */
    public static function rules(): array
    {
        return ['sometimes', 'nullable', 'string', 'in:' . implode(',', static::getAllowWhen())];
    }

    /**
     * @return array<int, string>
     */
    public static function getAllowWhen(): array
    {
        return Arr::pluck(self::getWhenOptions(), 'value');
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function getWhenOptions(): array
    {
        return [
            [
                'label' => __p('core::phrase.when.all'),
                'value' => Browse::WHEN_ALL,
            ], [
                'label' => __p('core::phrase.when.this_month'),
                'value' => Browse::WHEN_THIS_MONTH,
            ], [
                'label' => __p('core::phrase.when.this_week'),
                'value' => Browse::WHEN_THIS_WEEK,
            ], [
                'label' => __p('core::phrase.when.today'),
                'value' => Browse::WHEN_TODAY,
            ],
        ];
    }

    /** @var string */
    private string $when;
    private string $whenColumn;

    public function __construct(?string $when = null, string $whenColumn = 'created_at')
    {
        $this->when       = $when ?? self::WHEN_DEFAULT;
        $this->whenColumn = $whenColumn;
    }

    /**
     * @return string
     */
    public function getWhen(): string
    {
        return $this->when;
    }

    /**
     * @param string $when
     *
     * @return self
     */
    public function setWhen(string $when): self
    {
        $this->when = $when;

        return $this;
    }

    /**
     * @param string|null $whenColumn
     *
     * @return self
     */
    public function setWhenColumn(?string $whenColumn): self
    {
        $this->whenColumn = $whenColumn;

        return $this;
    }

    /**
     * @return string
     */
    public function getWhenColumn(): string
    {
        return $this->whenColumn;
    }

    /**
     * Apply when query.
     *
     * @param Builder $builder
     * @param Model   $model
     */
    public function apply(Builder $builder, Model $model)
    {
        $column = sprintf('%s.%s', $model->getTable(), $this->getWhenColumn());
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
            case Browse::WHEN_BETWEEN:
                if ($this->getFromDate()) {
                    $builder->where($this->getFromColumn(), '>=', $this->getFromDate());
                }

                if ($this->getToDate()) {
                    $builder->where($this->getToColumn(), '<=', $this->getToDate());
                }
                break;
        }
    }

    protected function getStartOfWeek(): int
    {
        return Settings::get('core.general.start_of_week', Carbon::MONDAY);
    }

    protected function convertUTCDate(string $date): string
    {
        return Carbon::parse($date)->setTimezone('UTC')->toDateTimeString();
    }
}
