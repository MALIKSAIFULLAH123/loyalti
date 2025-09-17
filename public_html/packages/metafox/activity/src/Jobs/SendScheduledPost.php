<?php

namespace MetaFox\Activity\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use MetaFox\Activity\Repositories\ActivityScheduleRepositoryInterface;
use MetaFox\Platform\Jobs\AbstractJob;

/**
 * Class ExpiredSnoozeJob.
 * @ignore
 */
class SendScheduledPost extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(protected int $isTemp = 0)
    {
        parent::__construct();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        resolve(ActivityScheduleRepositoryInterface::class)->sendScheduledPost($this->isTemp);
    }

    public function fail($exception = null): void
    {
        Log::channel('daily')->error(sprintf('%s:%s', __METHOD__, $exception?->getMessage()));
    }
}
