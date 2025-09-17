<?php

namespace MetaFox\Photo\Listeners;

use MetaFox\Photo\Jobs\DeleteUserDataJob;
use MetaFox\Photo\Repositories\AlbumRepositoryInterface;
use MetaFox\Photo\Repositories\PhotoGroupRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class UserDeletedListener
{
    public function handle(User $user): void
    {
        DeleteUserDataJob::dispatch($user);
    }
}
