<?php

namespace MetaFox\Event\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use MetaFox\Event\Models\HostInvite;
use MetaFox\Event\Repositories\HostInviteRepositoryInterface;
use MetaFox\Platform\Jobs\AbstractJob;

/**
 * stub: packages/jobs/job-queued.stub.
 */
class DeleteNotificationHostInviteExpireJob extends AbstractJob
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
    public function handle(): void
    {
        /** @var HostInviteRepositoryInterface $repository */
        $repository = resolve(HostInviteRepositoryInterface::class);

        $invites = $repository
            ->getModel()
            ->newModelQuery()
            ->where('status_id', HostInvite::STATUS_PENDING)
            ->where(function (Builder $builder) {
                $builder->where('expired_at', '<=', Carbon::now()->toDateTimeString());
            });

        foreach ($invites->get() as $invite) {
            if (!$invite instanceof HostInvite) {
                continue;
            }

            $repository->massDeleteNotification($invite);
        }

        $invites->update(['status_id' => HostInvite::STATUS_DECLINED]);
    }
}
