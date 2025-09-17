<?php

namespace MetaFox\Core\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use MetaFox\Core\Models\StatsContent;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface StatsContentRepositoryInterface.
 *
 * @mixin BaseRepository
 */
interface StatsContentRepositoryInterface
{
    /**
     * @param  string       $entityType
     * @param  string|null  $period
     * @return StatsContent
     */
    public function findStatContent(string $entityType, ?string $period = null): StatsContent;

    /**
     * @param  string|null $period
     * @return void
     */
    public function logStat(?string $period = '5m'): void;

    /**
     * @return array<string, mixed>
     */
    public function getDeepStatistic(): array;

    /**
     * @return Collection
     */
    public function getItemStatistic(): Collection;

    /**
     * @return Collection
     */
    public function getSiteStatistic(): Collection;

    /**
     * @param  array<string, mixed> $attributes
     * @return Collection
     */
    public function getChartData(array $attributes = []): Collection;

    /**
     * @param array $attributes
     * @return array
     */
    public function getChartDataWithContextualUser(array $attributes = []): array;

    /**
     * @param  string|null          $period
     * @return array<string, mixed>
     */
    public function getEmptyChartData(?string $period = null): array;

    /**
     * @param  array<string>        $excludes
     * @return array<string, mixed>
     */
    public function getStatTypes(array $excludes = []): array;

    /**
     * @param  string $period
     * @param  Carbon $date
     * @return string
     */
    public function toDateFormatByPeriod(string $period, Carbon $date): string;

    /**
     * @param  string|null       $period
     * @param  string|null       $group
     * @return array<int, mixed>
     */
    public function getNowStats(?string $period, ?string $group = null): array;

    /**
     * Use to clean up obsolete site statistics by period.
     *
     * @param  string|null          $period
     * @param  array<string, mixed> $wheres
     * @return void
     */
    public function cleanUpStatisticByPeriod(?string $period, array $wheres = []): void;

    /**
     * @return void
     */
    public function recoverHourStat(): void;

    /**
     * @return void
     */
    public function logHourStat(?string $group = null): void;
}
