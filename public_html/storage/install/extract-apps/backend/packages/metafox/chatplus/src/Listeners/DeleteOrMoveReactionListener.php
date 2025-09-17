<?php

namespace MetaFox\ChatPlus\Listeners;

use MetaFox\ChatPlus\Repositories\ChatServerInterface;
use MetaFox\Platform\Contracts\Entity;

/**
 * @ignore
 * @codeCoverageIgnore
 */
class DeleteOrMoveReactionListener
{
    public function handle(?Entity $reaction, ?int $newReactionId = null)
    {
        /* ChatServerInterface */
        resolve(ChatServerInterface::class)->deleteOrMoveReaction($reaction->entityId(), $newReactionId);
    }
}
