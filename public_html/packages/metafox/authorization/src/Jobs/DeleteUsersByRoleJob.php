<?php

namespace MetaFox\Authorization\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;

class DeleteUsersByRoleJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected User $context, protected array $userIds)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $repository = resolve(UserRepositoryInterface::class);

        foreach ($this->userIds as $userId) {
            $repository->deleteUser($this->context, $userId);
        }
    }
}
