<?php

namespace MetaFox\ChatPlus\Listeners;

use MetaFox\ChatPlus\Support\Traits\ChatplusTrait;
use MetaFox\Platform\Contracts\User;

/**
 * @ignore
 * @codeCoverageIgnore
 */
class ChatplusActiveListener
{
    use ChatplusTrait;
    public function handle(?User $context, ?User $user)
    {
        if ($user->entityType() == 'user') {
            return $this->canMessage($context, $user);
        }

        return null;
    }
}
