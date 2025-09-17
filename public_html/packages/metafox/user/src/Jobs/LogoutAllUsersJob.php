<?php

namespace MetaFox\User\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Authorization\Repositories\DeviceRepositoryInterface;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\User\Models\User;

class LogoutAllUsersJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected array $userIds = [])
    {
        parent::__construct();
    }

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

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

        /**@var User[] $users */
        foreach ($users as $user) {
            /**@var $deviceRepository DeviceRepositoryInterface */
            $deviceRepository = resolve(DeviceRepositoryInterface::class);

            $deviceRepository->logoutAllByUser($user, null);
        }
    }
}
