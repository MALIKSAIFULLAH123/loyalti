<?php

namespace MetaFox\ChatPlus\Listeners;

use MetaFox\ChatPlus\Repositories\ChatServerInterface;
use MetaFox\Platform\Contracts\User;

/**
 * @ignore
 * @codeCoverageIgnore
 */
class UserRoleDowngrade
{
    public function handle(User $context, User $user): void
    {
        resolve(ChatServerInterface::class)->updateUser($user->userId());
    }
}
