<?php

namespace MetaFox\User\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\User\Models\User;
use MetaFox\User\Repositories\UserAdminRepositoryInterface;

class MassSendInactiveMailingJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected User $context, protected array $userIds = [])
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
        if (!count($this->userIds)) {
            return;
        }

        $users = User::query()
            ->whereIn('id', $this->userIds)
            ->get();

        if (!$users->count()) {
            return;
        }

        foreach ($users as $user) {
            resolve(UserAdminRepositoryInterface::class)->processMailing($this->context, $user);
        }
    }
}
