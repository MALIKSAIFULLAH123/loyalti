<?php

namespace MetaFox\ChatPlus\Listeners;

use MetaFox\ChatPlus\Repositories\ChatServerInterface;

/**
 * @ignore
 * @codeCoverageIgnore
 */
class AddDeviceTokensListener
{
    public function handle(int $userId, array $devices, array $tokens, string $platform): void
    {
        if (empty($tokens)) {
            return;
        }

        /* ChatServerInterface */
        resolve(ChatServerInterface::class)->addDeviceTokens($userId, $devices, $tokens, $platform);
    }
}
