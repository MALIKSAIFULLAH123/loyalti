<?php

namespace MetaFox\Core\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Core\Models\StatsContent;
use MetaFox\Core\Repositories\StatsContentRepositoryInterface;
use MetaFox\Platform\Jobs\AbstractJob;

class UpdateSiteStatistic extends AbstractJob implements ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private ?string $period;

    public function uniqueId(): string
    {
        return __CLASS__ . (null !== $this->period ? $this->period : 'null');
    }

    public function __construct(?string $period = null)
    {
        parent::__construct();
        $this->period = $period;
    }

    public function handle(): void
    {
        /**
         * @var StatsContentRepositoryInterface $statRepository
         */
        $statRepository = resolve(StatsContentRepositoryInterface::class);

        match ($this->period) {
            StatsContent::STAT_PERIOD_ONE_HOUR => $statRepository->logHourStat(null, true),
            default                            => $statRepository->logStat($this->period),
        };

        if (StatsContent::STAT_PERIOD_ONE_HOUR === $this->period) {
            return;
        }

        match ($this->period) {
            StatsContent::STAT_PERIOD_ONE_DAY   => $statRepository->recoverDayStat(),
            StatsContent::STAT_PERIOD_ONE_WEEK  => $statRepository->recoverWeekStat(),
            StatsContent::STAT_PERIOD_ONE_MONTH => $statRepository->recoverMonthStat(),
            StatsContent::STAT_PERIOD_ONE_YEAR  => $statRepository->recoverYearStat(),
            default                             => null,
        };
    }
}
