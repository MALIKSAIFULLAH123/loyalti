<?php

namespace MetaFox\Comment\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Comment\Models\CommentHistory;
use MetaFox\Platform\Jobs\AbstractJob;

/**
 * stub: packages/jobs/job-queued.stub.
 */
class MigrateCommentHistoryHashTagJob extends AbstractJob
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
        foreach (CommentHistory::query()->cursor() as $commentHistory) {
            app('events')->dispatch(
                'hashtag.create_hashtag',
                [$commentHistory->user, $commentHistory, $commentHistory->content],
                true
            );
        }
    }
}
