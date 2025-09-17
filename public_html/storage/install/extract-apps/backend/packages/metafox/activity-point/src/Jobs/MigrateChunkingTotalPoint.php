<?php

namespace MetaFox\ActivityPoint\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\ActivityPoint\Support\Facade\ActivityPoint as ActivityPointFacade;
use MetaFox\ActivityPoint\Models\PointStatistic;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\User\Models\User;

class MigrateChunkingTotalPoint extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected array $statisticIds = [])
    {
        parent::__construct();
    }

    public function handle(): void
    {
        if (!count($this->statisticIds)) {
            return;
        }

        $statistics = PointStatistic::query()
            ->select(['id', 'current_points'])
            ->whereIn('id', $this->statisticIds)
            ->get();

        if (!$statistics->count()) {
            return;
        }

        foreach ($statistics as $statistic) {
            $user = User::query()->where('id', $statistic->id)->first();

            if (!$user instanceof User) {
                continue;
            }

            ActivityPointFacade::updateActivityPoints($user, $statistic->current_points);
        }
    }
}
