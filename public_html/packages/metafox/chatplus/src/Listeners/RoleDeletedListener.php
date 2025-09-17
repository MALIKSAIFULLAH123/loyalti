<?php

namespace MetaFox\ChatPlus\Listeners;

use MetaFox\ChatPlus\Repositories\ChatServerInterface;

/**
 * @ignore
 * @codeCoverageIgnore
 */
class RoleDeletedListener
{
    public function handle($role, $alternativeId)
    {
        /* ChatServerInterface */
        resolve(ChatServerInterface::class)->deleteRole($role, $alternativeId);
    }
}
