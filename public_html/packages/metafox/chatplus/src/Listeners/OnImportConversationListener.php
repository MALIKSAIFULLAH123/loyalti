<?php

namespace MetaFox\ChatPlus\Listeners;

use MetaFox\ChatPlus\Repositories\ChatServerInterface;

/**
 * @ignore
 * @codeCoverageIgnore
 */
class OnImportConversationListener
{
    public function handle(array $data = [])
    {
        if (empty($data)) {
            return;
        }

        /* ChatServerInterface */
        resolve(ChatServerInterface::class)->importConversations($data);
    }
}
