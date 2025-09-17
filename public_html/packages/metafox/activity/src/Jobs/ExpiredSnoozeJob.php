<?php

namespace MetaFox\Activity\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Activity\Repositories\SnoozeRepositoryInterface;
use MetaFox\Platform\Jobs\AbstractJob;

/**
 * Class ExpiredSnoozeJob.
 * @ignore
 */
class ExpiredSnoozeJob extends AbstractJob implements ShouldBeUnique
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
    public function handle()
    {
        $service = resolve(SnoozeRepositoryInterface::class);
        // Delete expired snoozes not having subscription.
        $service->deleteExpiredSnoozesNotHavingSubscription();

        // If snooze has subscription, then use eloquent delete to use observer.
        $service->deleteExpiredSnoozesHavingSubscription();
    }
}
