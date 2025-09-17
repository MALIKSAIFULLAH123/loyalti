<?php

namespace MetaFox\User\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\Platform\UserRole;
use MetaFox\User\Models\User;
use MetaFox\User\Notifications\UserPendingApprovalNotification;
use MetaFox\User\Policies\UserPolicy;

/**
 * Class UserPendingApprovalJob.
 * @ignore
 * @codeCoverageIgnore
 */
class UserPendingApprovalJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(protected int $userId)
    {
        parent::__construct();
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        $pendingApprovalUser = User::query()->where('id', $this->userId)->first();

        if (!$pendingApprovalUser instanceof User) {
            return;
        }

        $users = User::query()
            ->whereHas('roles', function (Builder $q) {
                $q->whereIn('role_id', [UserRole::SUPER_ADMIN_USER, UserRole::ADMIN_USER]);
            })->cursor();

        foreach ($users as $user) {
            if (!policy_check(UserPolicy::class, 'approve', $user, $pendingApprovalUser)) {
                continue;
            }

            Notification::send($user, new UserPendingApprovalNotification($pendingApprovalUser));
        }
    }
}
