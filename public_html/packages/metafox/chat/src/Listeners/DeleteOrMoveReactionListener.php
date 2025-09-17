<?php

namespace MetaFox\Chat\Listeners;

use MetaFox\Chat\Repositories\MessageRepositoryInterface;
use MetaFox\Platform\Contracts\Entity;

/**
 * Class ModelDeletingListener.
 * @ignore
 * @codeCoverageIgnore
 */
class DeleteOrMoveReactionListener
{
    public function handle(?Entity $reaction, ?int $newReactionId = null)
    {
        resolve(MessageRepositoryInterface::class)->deleteOrMoveReaction($reaction->id, $newReactionId);
    }
}
