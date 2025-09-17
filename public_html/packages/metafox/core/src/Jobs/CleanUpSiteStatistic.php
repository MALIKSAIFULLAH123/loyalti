<?php

namespace MetaFox\Core\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Core\Repositories\StatsContentRepositoryInterface;
use MetaFox\Platform\Jobs\AbstractJob;

class CleanUpSiteStatistic extends AbstractJob implements ShouldBeUnique
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
        $before = $this->getObsoleteTimePoint($this->period);

        $conditions = [
            ['created_at', '<=', $before],
        ];

        $statRepository = resolve(StatsContentRepositoryInterface::class);
        $statRepository->cleanUpStatisticByPeriod($this->period, $conditions);
    }

    protected function getObsoleteTimePoint(?string $period = null): Carbon
    {
        return match ($period) {
            '1d'    => Carbon::now()->subDays(365)->startOfDay(),
            '1w'    => Carbon::now()->subWeeks(52)->startOfWeek(),
            '1M'    => Carbon::now()->startOfMonth()->subMonths(24),
            '1h'    => Carbon::now()->startOfDay()->subYear(),
            default => Carbon::now()->subDays(30)->startOfDay(),
        };
    }
}
