<?php

namespace MetaFox\Quiz\Listeners;

use MetaFox\Platform\Contracts\User;
use MetaFox\Quiz\Jobs\CleanUpOnDeleteUser;

class UserDeletedListener
{
    public function handle(User $user): void
    {
        CleanUpOnDeleteUser::dispatch($user->entityId(), $user->entityType());
    }
}
