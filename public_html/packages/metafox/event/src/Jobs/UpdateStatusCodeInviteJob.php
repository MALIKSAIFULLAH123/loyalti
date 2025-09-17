<?php

namespace MetaFox\Event\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Event\Models\InviteCode;
use MetaFox\Event\Repositories\InviteCodeRepositoryInterface;
use MetaFox\Platform\Jobs\AbstractJob;

/**
 * stub: packages/jobs/job-queued.stub.
 */
class UpdateStatusCodeInviteJob extends AbstractJob
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
    public function handle(InviteCodeRepositoryInterface $codeRepository)
    {
        $inviteCode = $codeRepository
            ->getModel()
            ->newModelQuery()
            ->where('status', InviteCode::STATUS_ACTIVE)
            ->where('expired_at', '<=', Carbon::now()->toDateTimeString());
        $inviteCode->update(['status' => InviteCode::STATUS_INACTIVE]);
    }
}
