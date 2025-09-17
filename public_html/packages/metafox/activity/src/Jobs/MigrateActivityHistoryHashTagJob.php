<?php

namespace MetaFox\Activity\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Activity\Models\ActivityHistory;
use MetaFox\Platform\Jobs\AbstractJob;

/**
 * stub: packages/jobs/job-queued.stub.
 */
class MigrateActivityHistoryHashTagJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach (ActivityHistory::query()->cursor() as $activityHistory) {
            app('events')->dispatch(
                'hashtag.create_hashtag',
                [$activityHistory->user, $activityHistory, $activityHistory->content],
                true
            );
        }
    }
}
