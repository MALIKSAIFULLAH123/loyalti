<?php

namespace MetaFox\Advertise\Repositories\Eloquent;

use Carbon\CarbonInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use MetaFox\Advertise\Support\Support;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Advertise\Repositories\ReportRepositoryInterface;
use MetaFox\Advertise\Models\Report;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class ReportRepository.
 */
class ReportRepository extends AbstractRepository implements ReportRepositoryInterface
{
    public function model()
    {
        return Report::class;
    }

    public function createReport(Entity $entity, string $totalType, string $dateType = Support::DATE_TYPE_HOUR): ?Report
    {
        $value = $this->calculateDateValue($dateType);

        if (null === $value) {
            return null;
        }

        $attributes = [
            'item_type'  => $entity->entityType(),
            'item_id'    => $entity->entityId(),
            'date_type'  => $dateType,
            'date_value' => $value,
        ];

        $model = Report::query()
            ->firstOrCreate($attributes);

        if (null === $model) {
            return null;
        }

        $model->incrementAmount($totalType);

        $model->refresh();

        return $model;
    }

    protected function calculateDateValue(string $dateType): ?string
    {
        $now = Carbon::now();

        $format = $this->getModel()->getDateFormat();

        switch ($dateType) {
            case Support::DATE_TYPE_HOUR:
                $value = $now->startOfHour()->format($format);
                break;
            default:
                $value = null;
        }

        return $value;
    }

    public function deleteReports(Entity $entity, string $dateType = Support::DATE_TYPE_HOUR)
    {
        Report::query()
            ->where([
                'item_type' => $entity->entityType(),
                'item_id'   => $entity->entityId(),
                'date_type' => $dateType,
            ])
            ->delete();
    }

    protected function filterReportsByMonth(Entity $entity, string $totalType, ?string $startDate = null, ?string $endDate = null): array
    {
        $now       = Carbon::now();
        $fromMonth = $startDate ? Carbon::parse($startDate)->startOfMonth() : $now->clone();
        $toMonth   = $endDate ? Carbon::parse($endDate)->startOfMonth() : $now->clone();

        if ($toMonth->lessThan($fromMonth)) {
            return [];
        }

        $data = $this->viewReportsByMonth($entity);

        $groups = $this->getFormat(Support::STATISTIC_VIEW_MONTH, $totalType, $fromMonth, $toMonth);

        if (!count($data)) {
            return $groups;
        }

        $field = $totalType == Support::TYPE_IMPRESSION ? 'total_impression' : 'total_click';

        $statistics = [];

        $finalTotal = 0;

        foreach ($data as $date => $report) {
            if ($fromMonth && $fromMonth->greaterThan($date)) {
                continue;
            }

            if ($toMonth && $toMonth->lessThan($date)) {
                continue;
            }

            $total = Arr::get($report, $field, 0);

            $statistics[] = [
                'label'  => null,
                'value'  => $total,
                'format' => Arr::get($report, 'format'),
            ];

            $finalTotal += $total;
        }

        return $this->getFormat(Support::STATISTIC_VIEW_MONTH, $totalType, $fromMonth, $toMonth, $statistics, $finalTotal, false);
    }

    protected function filterReportsByWeek(Entity $entity, string $totalType, ?string $startDate = null, ?string $endDate = null): array
    {
        $now            = Carbon::now();
        $startDayOfWeek = $this->getStartOfWeek();
        $fromWeek       = $startDate ? Carbon::parse($startDate)->startOfWeek($startDayOfWeek) : $now->clone();
        $toWeek         = $endDate ? Carbon::parse($endDate)->startOfWeek($startDayOfWeek) : $now->clone();

        if ($toWeek->lessThan($fromWeek)) {
            return [];
        }

        $data = $this->viewReportsByWeek($entity);

        $groups = $this->getFormat(Support::STATISTIC_VIEW_WEEK, $totalType, $fromWeek, $toWeek);

        if (!count($data)) {
            return $groups;
        }

        $field = $totalType == Support::TYPE_IMPRESSION ? 'total_impression' : 'total_click';

        $count = 1;

        $finalTotal = 0;

        $statistics = [];

        foreach ($data as $date => $report) {
            if ($fromWeek && $fromWeek->greaterThan($date)) {
                continue;
            }

            if ($toWeek && $toWeek->lessThan($date)) {
                continue;
            }

            $total = Arr::get($report, $field, 0);

            $finalTotal += $total;

            $statistics[] = [
                'label'  => __p('advertise::phrase.week_number', ['number' => $count++]),
                'value'  => $total,
                'format' => Arr::get($report, 'format'),
            ];
        }

        return $this->getFormat(Support::STATISTIC_VIEW_WEEK, $totalType, $fromWeek, $toWeek, $statistics, $finalTotal, false);
    }

    protected function filterReportsByDay(Entity $entity, string $totalType, ?string $startDate = null, ?string $endDate = null): array
    {
        $now     = Carbon::now();
        $fromDay = $startDate ? Carbon::parse($startDate)->startOfDay() : $now->clone();
        $toDay   = $endDate ? Carbon::parse($endDate)->startOfDay() : $now->clone();

        if ($toDay->lessThan($fromDay)) {
            return [];
        }

        $data = $this->viewReportsByDay($entity);

        $groups = $this->getFormat(Support::STATISTIC_VIEW_DAY, $totalType, $fromDay, $toDay);

        if (!count($data)) {
            return $groups;
        }

        $field = $totalType == Support::TYPE_IMPRESSION ? 'total_impression' : 'total_click';

        $finalTotal = 0;

        $statistics = [];

        foreach ($data as $date => $report) {
            if ($fromDay && $fromDay->greaterThan($date)) {
                continue;
            }

            if ($toDay && $toDay->lessThan($date)) {
                continue;
            }

            $total = Arr::get($report, $field, 0);

            $statistics[] = [
                'label'  => null,
                'value'  => $total,
                'format' => Arr::get($report, 'format'),
            ];

            $finalTotal += $total;
        }

        return $this->getFormat(Support::STATISTIC_VIEW_DAY, $totalType, $fromDay, $toDay, $statistics, $finalTotal, false);
    }

    public function viewReport(Entity $entity, string $view, string $totalType, ?string $startDate = null, ?string $endDate = null): array
    {
        return match ($view) {
            Support::STATISTIC_VIEW_DAY   => $this->filterReportsByDay($entity, $totalType, $startDate, $endDate),
            Support::STATISTIC_VIEW_WEEK  => $this->filterReportsByWeek($entity, $totalType, $startDate, $endDate),
            Support::STATISTIC_VIEW_MONTH => $this->filterReportsByMonth($entity, $totalType, $startDate, $endDate),
            default                       => [],
        };
    }

    protected function getItemReports(Entity $entity): array
    {
        $cacheId = implode('_', ['advertise', $entity->entityType(), $entity->entityId(), 'reports']);

        return Cache::remember($cacheId, 3600, function () use ($entity) {
            $reports = Report::query()
                ->where([
                    'item_type' => $entity->entityType(),
                    'item_id'   => $entity->entityId(),
                    'date_type' => Support::DATE_TYPE_HOUR,
                ])
                ->orderBy('date_value')
                ->get(['total_impression', 'total_click', 'date_value']);

            if (!$reports->count()) {
                return [];
            }

            $startDayOfWeek = $this->getStartOfWeek();

            return $reports
                ->map(function ($report) use ($startDayOfWeek) {
                    $startOfDay     = Carbon::parse($report->date_value)->startOfDay();
                    $startOfMonth   = Carbon::parse($report->date_value)->startOfMonth();
                    $startOfWeek    = Carbon::parse($report->date_value)->startOfWeek($startDayOfWeek);
                    $dateTimeFormat = $report->getDateFormat();

                    $report->start_month = [
                        'iso'       => $startOfMonth->toISOString(),
                        'date_time' => $startOfMonth->format($dateTimeFormat),
                    ];

                    $report->start_week  = [
                        'iso'       => $startOfWeek->toISOString(),
                        'date_time' => $startOfWeek->format($dateTimeFormat),
                    ];

                    $report->start_day = [
                        'iso'       => $startOfDay->toISOString(),
                        'date_time' => $startOfDay->format($dateTimeFormat),
                    ];

                    return $report;
                })
                ->toArray();
        });
    }

    protected function getStartOfWeek(): int
    {
        return Settings::get('core.general.start_of_week', Carbon::MONDAY);
    }

    protected function viewReportsByPeriod(Entity $entity, string $period): array
    {
        $cacheId = implode('_', ['advertise', $entity->entityType(), $entity->entityId(), 'reports', $period]);

        return Cache::remember($cacheId, 3600, function () use ($entity, $period) {
            $reports = $this->getItemReports($entity);

            if (!count($reports)) {
                return [];
            }

            $groups = [];

            foreach ($reports as $report) {
                $date = Arr::get($report, 'start_' . $period . '.date_time');

                if (null === $date) {
                    continue;
                }

                if (!Arr::has($groups, $date)) {
                    Arr::set($groups, $date, [
                        'total_click'      => 0,
                        'total_impression' => 0,
                        'format'           => Arr::get($report, 'start_' . $period),
                    ]);
                }

                $groups[$date]['total_click'] += $report['total_click'];
                $groups[$date]['total_impression'] += $report['total_impression'];
            }

            return $groups;
        });
    }

    protected function viewReportsByDay(Entity $entity): array
    {
        return $this->viewReportsByPeriod($entity, Support::STATISTIC_VIEW_DAY);
    }

    protected function viewReportsByWeek(Entity $entity): array
    {
        return $this->viewReportsByPeriod($entity, Support::STATISTIC_VIEW_WEEK);
    }

    protected function viewReportsByMonth(Entity $entity): array
    {
        return $this->viewReportsByPeriod($entity, Support::STATISTIC_VIEW_MONTH);
    }

    protected function clearCaches(Entity $entity): void
    {
        $general = implode('_', ['advertise', $entity->entityType(), $entity->entityId(), 'reports']);
        $week    = implode('_', ['advertise', $entity->entityType(), $entity->entityId(), 'reports', Support::STATISTIC_VIEW_WEEK]);
        $month   = implode('_', ['advertise', $entity->entityType(), $entity->entityId(), 'reports', Support::STATISTIC_VIEW_MONTH]);

        Cache::deleteMultiple([$general, $week, $month]);
    }

    protected function getFormat(string $view, string $totalType, CarbonInterface $startDate, CarbonInterface $endDate, array $statistics = [], int $total = 0, bool $isDefault = true): array
    {
        if ($isDefault) {
            $total      = 0;
            $statistics = [];
        }

        switch ($view) {
            case Support::STATISTIC_VIEW_DAY:
                $phraseName = 'total_in_days';
                $value      = $endDate->diffInDays($startDate);
                break;
            case Support::STATISTIC_VIEW_WEEK:
                $phraseName = 'total_in_weeks';
                $value      = $endDate->diffInWeeks($startDate);
                break;
            default:
                $phraseName = 'total_in_months';
                $value      = $endDate->diffInMonths($startDate);
        }

        return [
            'total'     => $total,
            'statistic' => $statistics,
            'view'      => $view,
            'phrase'    => [
                'name'   => $phraseName,
                'params' => [
                    'total' => $total,
                    'type'  => $totalType,
                    'value' => $value ?: 1,
                ],
            ],
        ];
    }
}
