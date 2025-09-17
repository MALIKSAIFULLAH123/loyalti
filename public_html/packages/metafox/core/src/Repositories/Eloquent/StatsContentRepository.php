<?php

namespace MetaFox\Core\Repositories\Eloquent;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use MetaFox\Core\Models\StatsContent;
use MetaFox\Core\Models\StatsContent as Model;
use MetaFox\Core\Repositories\StatsContentRepositoryInterface;
use MetaFox\Core\Repositories\StatsContentTypeAdminRepositoryInterface;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\PackageManager;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\PackageScope;

/**
 * @method StatsContent getModel()
 */
class StatsContentRepository extends AbstractRepository implements StatsContentRepositoryInterface
{
    public function model()
    {
        return StatsContent::class;
    }

    public function findStatContent(string $entityType, ?string $period = null): StatsContent
    {
        /** @var StatsContent $stat */
        $stat = $this->getModel()->newModelQuery()
            ->orderBy('created_at', 'desc')
            ->firstOrCreate([
                'name'   => $entityType,
                'period' => $period,
            ], ['created_at' => Carbon::now()]);

        return $stat->refresh();
    }

    public function collectStats(?string $period, ?Carbon $after, ?Carbon $before = null, ?string $group = null): array
    {
        $allData = app('events')->dispatch('core.collect_total_items_stat', [$after, $before, $group]);
        $now     = $before ?? Carbon::now();
        $urls    = Settings::get('core.general.site_stat_urls', []);

        if (!is_array($allData)) { // reach ?
            return [];
        }

        $rows = [];

        foreach ($allData as $subData) {
            if (empty($subData)) {
                continue;
            }

            foreach ($subData as $data) {
                if (empty($data)) {
                    continue;
                }

                $url   = Arr::get($data, 'url');
                $data  = Arr::add($data, 'group', '*');
                $value = Arr::get($data, 'value', 0);
                $name  = Arr::get($data, 'name');

                Arr::add($data, 'url', '');

                if ($value) {
                    if (null == $url) {
                        $link = Arr::get($urls, $name);

                        $url = $link ? url_utility()->makeApiFullUrl($link) : '';
                    }

                    Arr::set($data, 'url', $url);
                }

                $rows[] = array_merge($data, [
                    'period'     => $period,
                    'created_at' => $now,
                ]);
            }
        }

        return $rows;
    }

    public function logStat(?string $period = '5m'): void
    {
        $after = $this->parsePeriod($period);

        $rows = $this->collectStats($period, $after);

        $fillables = (new StatsContent())->getFillable();

        $rows = Arr::map($rows, function (array $row) use ($fillables) {
            return Arr::only($row, $fillables);
        });

        $this->getModel()->insert($rows);
    }

    /**
     * @inheritDoc
     */
    public function getNowStats(?string $period, ?string $group = null): array
    {
        $after = $this->parseNowPeriod($period);

        $cacheKey = 'today_stat_data_' . $period . '_' . ($group ?? 'all');

        return Cache::remember($cacheKey, 3000, function () use ($period, $after, $group) {
            $data = $this->collectStats($period, $after, null, $group);

            return Arr::sortDesc($data, 'value');
        });
    }

    /**
     * @inheritDoc
     */
    public function getDeepStatistic(): array
    {
        $todayData    = $this->getNowStats(StatsContent::STAT_PERIOD_ONE_DAY);
        $pendingData  = $this->getNowStats(StatsContent::STAT_PERIOD_ONE_HOUR, 'pending');
        $siteStatData = $this->getNowStats(StatsContent::STAT_PERIOD_ONE_HOUR, 'site_stat');

        $today = collect($todayData)
            ->map(function ($item) {
                return [
                    'label' => __p(Arr::get($item, 'label', '')),
                    'value' => Arr::get($item, 'value') ?? 0,
                    'url'   => Arr::get($item, 'url') ?? '',
                ];
            })
            ->values()
            ->toArray();

        $pending = collect($pendingData)
            ->map(function ($item) {
                $typeData = $this->getStatsTypeData($item['name']);
                $value    = Arr::get($item, 'value');
                $link     = $value ? Arr::get($typeData, 'to') : null;

                return [
                    'label' => __p(Arr::get($item, 'label', '')),
                    'value' => Arr::get($item, 'value') ?? 0,
                    'url'   => $link ?? '',
                ];
            })
            ->values()
            ->toArray();

        $siteStatData = collect($siteStatData)
            ->map(function ($item) {
                $typeData = $this->getStatsTypeData($item['name']);
                $value    = Arr::get($item, 'value');
                $link     = $value ? Arr::get($typeData, 'to') : null;

                return [
                    'label' => __p(Arr::get($item, 'label', '')),
                    'value' => $value ?? 0,
                    'url'   => $link ?? '',
                ];
            })
            ->values()
            ->toArray();

        return [
            'site_statistic' => [
                'title' => __p('core::phrase.site_statistics'),
                'items' => $siteStatData,
            ],
            'app_statistic'  => [
                'title' => __p('core::phrase.app_statistics'),
                'tabs'  => [
                    'pending' => [
                        'title' => __p('core::phrase.pending'),
                        'items' => $pending,
                    ],
                    'all'     => [
                        'title' => __p('core::phrase.today'),
                        'items' => $today,
                    ],
                ],
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function getItemStatistic(): Collection
    {
        return $this->getModel()
            ->newModelQuery()
            ->with(['type'])
            ->whereNull('period')
            ->where('created_at', '>', Carbon::now()->subDay())
            ->orderBy('created_at', 'desc')
            ->get()
            ->collect()
            ->groupBy('name')
            ->map(function (Collection $stat) {
                return $stat->first();
            })
            ->values()
            ->filter(function (StatsContent $stat) {
                return $stat->group == '*';
            })
            ->sortBy(function (Model $stat) {
                return $stat->type?->ordering ?? 1;
            });
    }

    public function getSiteStatistic(): Collection
    {
        $keys = [
            'online_user'    => [
                'name'       => 'online_user',
                'label'      => 'user::phrase.online_user_stat_label',
                'value'      => 0,
                'period'     => null,
                'group'      => 'site_stat',
                'created_at' => Carbon::now(),
            ],
            'pending_user'   => [
                'name'       => 'pending_user',
                'label'      => 'user::phrase.pending_user_stat_label',
                'value'      => 0,
                'period'     => null,
                'group'      => 'site_stat',
                'created_at' => Carbon::now(),
            ],
            'pending_report' => [
                'name'       => 'pending_report',
                'label'      => 'report::phrase.pending_report_stat_label',
                'value'      => 0,
                'period'     => null,
                'group'      => 'site_stat',
                'created_at' => Carbon::now(),
            ],
        ];

        $query = $this->getModel()->newModelQuery()
            ->whereNull('period')
            ->whereIn('name', array_keys($keys))
            ->where('created_at', '>', Carbon::now()->subHour()->startOfHour())
            ->orderBy('created_at', 'desc');

        $data = $query->get()
            ->collect()
            ->groupBy('name')
            ->map(function (Collection $stat) {
                return $stat->first();
            })->values()
            ->sortByDesc('value');

        if (!$data->isEmpty()) {
            return $data;
        }

        $data = [];
        foreach ($keys as $key => $defaultData) {
            $model = new StatsContent();
            $model->fill($defaultData);

            $data[] = $model;
        }

        return collect($data);
    }

    /**
     * Parsing period in human readable form into integer (in minutes).
     *
     * @param string|null $period
     *
     * @return Carbon|null
     */
    protected function parsePeriod(?string $period): ?Carbon
    {
        return match ($period) {
            '5m'    => Carbon::now()->subMinutes(5),
            '1h'    => Carbon::now()->subHour(),
            '1d'    => Carbon::now()->subDay(),
            '1w'    => Carbon::now()->subWeek(),
            '1M'    => Carbon::now()->subMonth(),
            default => null,
        };
    }

    /**
     * Parsing period in human readable form into integer (in minutes).
     *
     * @param string|null $period
     *
     * @return Carbon|null
     */
    protected function parseNowPeriod(?string $period): ?Carbon
    {
        return match ($period) {
            '1d'    => Carbon::now()->startOfDay(),
            '1w'    => Carbon::now()->startOfWeek(),
            '1M'    => Carbon::now()->startOfMonth(),
            default => null,
        };
    }

    public function getChartDataWithContextualUser(array $attributes = []): array
    {
        $name = Arr::get($attributes, 'name');

        $period = Arr::get($attributes, 'period');

        try {
            $clientCarbon = Carbon::parse(MetaFox::clientDate());
        } catch (\Throwable $th) {
            $clientCarbon = Carbon::now();
        }

        $start = match ($period) {
            StatsContent::STAT_PERIOD_ONE_MONTH => $clientCarbon->clone()->startOfDay()->subMonths(12)->utc(),
            StatsContent::STAT_PERIOD_ONE_WEEK  => $clientCarbon->clone()->startOfDay()->subWeeks(15)->utc(),
            default                             => $clientCarbon->clone()->startOfDay()->subDays(30)->utc(),
        };

        /**
         * Cover old data that aggregated by day and week
         */
        $periods = [StatsContent::STAT_PERIOD_ONE_HOUR, $period];

        $query = DB::table('core_stats_contents')
            ->selectRaw('created_at as date, value as data')
            ->where([
                'name'  => $name,
                'group' => '*',
            ])
            ->where('value', '>', 0)
            ->whereIn('period', $periods)
            ->where('created_at', '>=', $start);

        return $query->get()
            ->map(function ($stat) {
                if (!is_array($stat)) {
                    $stat = (array) $stat;
                }

                return array_merge($stat, [
                    'date' => Carbon::parse($stat['date'])->toISOString(),
                ]);
            })
            ->toArray();
    }

    /**
     * @deprecated
     * @inheritDoc
     */
    public function getChartData(array $attributes = []): Collection
    {
        extract($attributes);

        $start = match ($period) {
            StatsContent::STAT_PERIOD_ONE_DAY   => Carbon::now()->subDays(30),
            StatsContent::STAT_PERIOD_ONE_WEEK  => Carbon::now()->subWeeks(15),
            StatsContent::STAT_PERIOD_ONE_MONTH => Carbon::now()->subMonths(12),
            default                             => Carbon::now()->startOfYear(),
        };

        $uniqueFilter = [];

        return $this->getModel()
            ->newModelQuery()
            ->where('name', '=', $name)
            ->where('period', '=', $period)
            ->where('created_at', '>=', $start)
            ->orderBy('created_at')
            ->get()
            ->collect()
            ->filter(function ($stat) use (&$uniqueFilter) {
                if (!$stat instanceof StatsContent) {
                    return false;
                }

                $key = Carbon::parse($stat->created_at)->toDateString();
                if (isset($uniqueFilter[$key])) {
                    return false;
                }

                $uniqueFilter[$key] = true;

                return true;
            })
            ->values();
    }

    /**
     * @inheritDoc
     */
    public function getEmptyChartData(?string $period = null): array
    {
        return match ($period) {
            StatsContent::STAT_PERIOD_ONE_WEEK  => $this->getWeekChartData(),
            StatsContent::STAT_PERIOD_ONE_MONTH => $this->getMonthChartData(),
            default                             => $this->getDayChartdata(),
        };
    }

    /**
     * @inheritDoc
     */
    public function getStatTypes(array $excludes = []): array
    {
        $table = $this->getModel()->getTable();
        $query = $this->getModel()
            ->newModelQuery()
            ->select(["$table.name as value", 'label', 'st.operation'])
            ->leftJoin('stats_content_types as st', 'st.name', '=', "$table.name")
            ->whereNotIn("$table.name", $excludes)
            ->whereNotNull('period')
            ->groupBy(["$table.name", 'label', 'st.operation'])
            ->addScope(new PackageScope($table));

        $types = $query->get(['value', 'label', 'operation'])
            ->collect()
            ->sortBy('label')
            ->values()
            ->toArray();

        $period = [
            [
                'label' => __p('core::phrase.daily'),
                'value' => StatsContent::STAT_PERIOD_ONE_DAY,
            ],
            [
                'label' => __p('core::phrase.weekly'),
                'value' => StatsContent::STAT_PERIOD_ONE_WEEK,
            ],
            [
                'label' => __p('core::phrase.monthly'),
                'value' => StatsContent::STAT_PERIOD_ONE_MONTH,
            ],
        ];

        return [
            'types'  => $types,
            'period' => $period,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getDayChartData(): array
    {
        $data      = [];
        $daysLimit = 30;
        $today     = Carbon::now();
        $period    = CarbonPeriod::create(Carbon::now()->subDays($daysLimit), $today);
        foreach ($period->toArray() as $day) {
            $key        = $day->startOfDay()->toIso8601String();
            $data[$key] = [
                'data' => 0,
                'date' => $key,
            ];
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function getWeekChartData(): array
    {
        $displayedWeekLimit = 15;
        $data               = [];

        while ($displayedWeekLimit >= 0) {
            $date = Carbon::now()->subWeeks($displayedWeekLimit)->startOfWeek(Carbon::SUNDAY)->startOfDay();

            $data[$date->toIso8601String()] = [
                'data' => 0,
                'date' => $date,
            ];

            $displayedWeekLimit--;
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     * @todo: need to update rule for month limit before using
     */
    private function getMonthChartData(): array
    {
        $i            = 1;
        $data         = [];
        $currentMonth = Carbon::now()->month;
        while ($i <= $currentMonth) {
            $date        = Carbon::now()->subMonths($currentMonth - $i)->toIso8601String();
            $data[$date] = [
                'data' => 0,
                'date' => $date,
            ];
            $i++;
        }

        return $data;
    }

    public function toDateFormatByPeriod(string $period, Carbon $date): string
    {
        return match ($period) {
            Model::STAT_PERIOD_ONE_WEEK  => __p('core::phrase.week_value', ['value' => Carbon::parse($date)->weekOfYear]),
            Model::STAT_PERIOD_ONE_MONTH => Carbon::parse($date)->month,
            default                      => Carbon::parse($date)->toDateString(),
        };
    }

    /**
     * @inheritDoc
     */
    public function cleanUpStatisticByPeriod(?string $period, array $wheres = []): void
    {
        $this->getModel()->newModelQuery()->where('period', $period)->where($wheres)->delete();
    }

    public function recoverDayStat(): void
    {
        $startOfMonth = Carbon::now()->firstOfMonth();
        $now          = Carbon::now();

        $range = CarbonPeriod::create($startOfMonth, $now)->toArray();

        foreach ($range as $day) {
            // Check if the stat of on each day is recorded.
            $exists = $this->getModel()->newModelQuery()
                ->where('period', '=', StatsContent::STAT_PERIOD_ONE_DAY)
                ->where('created_at', '>=', Carbon::parse($day)->startOfDay())
                ->where('created_at', '<=', Carbon::parse($day)->endOfDay())
                ->exists();
            if ($exists) {
                continue;
            }

            // If is not, then recover it by count it again.
            $rows = $this->collectStats(
                StatsContent::STAT_PERIOD_ONE_DAY,
                Carbon::parse($day)->startOfDay(),
                Carbon::parse($day)->endOfDay()
            );

            $fillables = (new StatsContent())->getFillable();

            $rows = Arr::map($rows, function (array $row) use ($fillables) {
                return Arr::only($row, $fillables);
            });

            $this->getModel()->insert($rows);
            $now = $now->subDay();
        }
    }

    public function recoverWeekStat(): void {}

    public function recoverMonthStat(): void {}

    public function recoverYearStat(): void {}

    public function recoverHourStat(?string $group = null, ?CarbonInterface $carbon = null, int $days = 30): void
    {
        if (null === $carbon) {
            $carbon = Carbon::now()->startOfHour();
        }

        $mileStone = $carbon->clone()->subHour();

        while ($carbon->diffInDays($mileStone) <= $days) {
            $start = $mileStone->clone();

            $end = $mileStone->clone()->endOfHour();

            $records = $this->collectStats(StatsContent::STAT_PERIOD_ONE_HOUR, $start, $end, $group);

            if (!count($records)) {
                break;
            }

            foreach ($records as $record) {
                $this->updateOrInsert($record);
            }

            $mileStone = $mileStone->subHour();
        }
    }

    public function logHourStat(?string $group = null, bool $recover = true): void
    {
        $start = Carbon::now()->startOfHour();

        $end = Carbon::now()->endOfHour();

        $rows = $this->collectStats(StatsContent::STAT_PERIOD_ONE_HOUR, $start, $end);

        $fields = $this->getModel()->newInstance()->getFillable();

        $rows = Arr::map($rows, function (array $row) use ($fields) {
            return Arr::only($row, $fields);
        });

        foreach ($rows as $row) {
            $this->updateOrInsert($row);
        }

        if (!$recover) {
            return;
        }

        $this->recoverHourStat($group, $start);
    }

    protected function updateOrInsert(array $row): void
    {
        $keyLabel  = Arr::get($row, 'label');
        $namespace = Arr::get($row, 'module_id');
        $packageId = Arr::get($row, 'package_id');
        if (!$namespace) {
            [$namespace, $group, $name] = app('translator')->parseKey($keyLabel ?? "");
        }

        $packageId = $packageId ?? PackageManager::getByAlias($namespace);

        $this->getModel()->updateOrInsert([
            'name'       => Arr::get($row, 'name'),
            'period'     => StatsContent::STAT_PERIOD_ONE_HOUR,
            'group'      => Arr::get($row, 'group'),
            'created_at' => Arr::get($row, 'created_at'),
            'package_id' => $packageId,
            'module_id'  => $namespace,
        ], [
            'value' => Arr::get($row, 'value'),
            'label' => $keyLabel,
        ]);
    }

    protected function getStatsTypeData(string $type): array
    {
        $allTypes = resolve(StatsContentTypeAdminRepositoryInterface::class)->getAllKeyByName();

        return Arr::get($allTypes, $type, []);
    }
}
