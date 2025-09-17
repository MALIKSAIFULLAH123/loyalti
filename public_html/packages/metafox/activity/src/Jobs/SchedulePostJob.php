<?php

namespace MetaFox\Activity\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use MetaFox\Activity\Models\ActivitySchedule;
use MetaFox\Activity\Repositories\ActivityScheduleRepositoryInterface;
use MetaFox\Activity\Repositories\SnoozeRepositoryInterface;
use MetaFox\Platform\Jobs\AbstractJob;

/**
 * Class ExpiredSnoozeJob.
 * @ignore
 */
class SchedulePostJob extends AbstractJob implements ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @return string
     * @link https://laravel.com/docs/9.x/queues#unique-jobs
     */
    public function uniqueId(): string
    {
        return __CLASS__;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $response = resolve(ActivityScheduleRepositoryInterface::class)->monitorScheduledPost();
        $isTemp   = Arr::get($response, 'isTemp');

        if (!Arr::get($response, 'schedule') instanceof ActivitySchedule || !$isTemp) {
            return;
        }

        SendScheduledPost::dispatch($isTemp);
    }
}
