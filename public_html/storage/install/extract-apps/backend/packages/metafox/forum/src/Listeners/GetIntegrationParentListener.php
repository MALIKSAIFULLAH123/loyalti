<?php

namespace MetaFox\Forum\Listeners;

use MetaFox\Forum\Models\ForumThread;
use MetaFox\Platform\Contracts\Entity;

class GetIntegrationParentListener
{
    public function handle(Entity $poll)
    {
        $thread = ForumThread::query()->where([
            'item_id'   => $poll->entityId(),
            'item_type' => $poll->entityType(),
        ])->first();

        if (!$thread instanceof ForumThread) {
            return null;
        }

        return $thread;
    }
}
