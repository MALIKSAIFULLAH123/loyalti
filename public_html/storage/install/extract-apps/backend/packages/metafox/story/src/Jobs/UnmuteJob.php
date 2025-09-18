<?php

namespace MetaFox\Story\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\Story\Repositories\MuteRepositoryInterface;


/**
 * stub: packages/jobs/job-queued.stub
 */
class UnmuteJob extends AbstractJob implements ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $nowTimestamp = Carbon::now()->subMinute()->timestamp;

        $this->repository()->getModel()->newQuery()
            ->where('expired_at', '<=', $nowTimestamp)
            ->whereNotNull('expired_at')
            ->delete();
    }

    protected function repository(): MuteRepositoryInterface
    {
        return resolve(MuteRepositoryInterface::class);
    }
}
