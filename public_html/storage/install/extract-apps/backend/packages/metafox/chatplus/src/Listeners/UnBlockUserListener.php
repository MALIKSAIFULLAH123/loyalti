<?php

namespace MetaFox\ChatPlus\Listeners;

use MetaFox\ChatPlus\Repositories\ChatServerInterface;
use MetaFox\Platform\Contracts\User as ContractUser;

/**
 * @ignore
 * @codeCoverageIgnore
 */
class UnBlockUserListener
{
    public function handle(ContractUser $user, ContractUser $owner)
    {
        if ($user->entityType() == 'user') {
            /* ChatServerInterface */
            resolve(ChatServerInterface::class)->unBlockUser($user->entityId(), $owner->entityId());
        }
    }
}
