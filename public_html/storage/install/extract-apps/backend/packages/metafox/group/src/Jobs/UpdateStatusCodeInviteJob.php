<?php

namespace MetaFox\Group\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use MetaFox\Group\Models\GroupInviteCode;
use MetaFox\Group\Repositories\GroupInviteCodeRepositoryInterface;
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
    public function handle()
    {
        /** @var GroupInviteCodeRepositoryInterface $codeRepository */
        $codeRepository = resolve(GroupInviteCodeRepositoryInterface::class);
        $inviteCode     = $codeRepository
            ->getModel()
            ->newModelQuery()
            ->where('status', GroupInviteCode::STATUS_ACTIVE)
            ->where(function (Builder $builder) {
                $builder->where('expired_at', '<=', Carbon::now()->toDateTimeString());
            });
        $inviteCode->update(['status' => GroupInviteCode::STATUS_INACTIVE]);
    }
}
