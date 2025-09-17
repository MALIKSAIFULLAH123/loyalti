<?php

namespace MetaFox\Music\Listeners;

use MetaFox\Music\Jobs\DeleteUserDataJob;
use MetaFox\Platform\Contracts\User;

class UserDeletedListener
{
    public function handle(User $user): void
    {
        DeleteUserDataJob::dispatch($user);
    }
}
