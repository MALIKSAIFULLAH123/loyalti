<?php

namespace MetaFox\Group\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use MetaFox\Group\Models\Invite;
use MetaFox\Group\Repositories\GroupInviteCodeRepositoryInterface;
use MetaFox\Group\Repositories\InviteRepositoryInterface;
use MetaFox\Platform\Jobs\AbstractJob;

/**
 * stub: packages/jobs/job-queued.stub.
 */
class UpdateStatusInviteJob extends AbstractJob implements ShouldBeUnique
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
        /** @var GroupInviteCodeRepositoryInterface $repository */
        $repository = resolve(InviteRepositoryInterface::class);
        $query      = $repository
            ->getModel()
            ->newModelQuery()
            ->where('status_id', Invite::STATUS_PENDING)
            ->where(function (Builder $builder) {
                $builder->where('expired_at', '<=', Carbon::now()->toDateTimeString());
            });

        $query->update(['status_id' => Invite::STATUS_EXPIRED]);
    }
}
