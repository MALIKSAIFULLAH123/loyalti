<?php

namespace MetaFox\Poll\Listeners;

use MetaFox\Platform\Contracts\User;
use MetaFox\Poll\Jobs\CleanUpOnDeleteUser;

class UserDeletedListener
{
    public function handle(User $user): void
    {
        CleanUpOnDeleteUser::dispatch($user->entityId(), $user->entityType());
    }
}
