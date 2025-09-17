<?php

namespace MetaFox\ChatPlus\Listeners;

use MetaFox\ChatPlus\Repositories\ChatServerInterface;

/**
 * @ignore
 * @codeCoverageIgnore
 */
class RemoveDeviceTokensListener
{
    public function handle(int $userId, array $tokens): void
    {
        if (empty($tokens)) {
            return;
        }

        /* ChatServerInterface */
        resolve(ChatServerInterface::class)->removeDeviceTokens($userId, $tokens);
    }
}
