<?php

namespace MetaFox\ChatPlus\Listeners;

use MetaFox\ChatPlus\Repositories\ChatServerInterface;

/**
 * @ignore
 * @codeCoverageIgnore
 */
class RoleCreatedListener
{
    public function handle($role)
    {
        /* ChatServerInterface */
        resolve(ChatServerInterface::class)->createRole($role);
    }
}
