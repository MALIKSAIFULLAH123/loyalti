<?php

namespace MetaFox\Forum\Listeners;

use MetaFox\Forum\Models\ForumThread;
use MetaFox\Forum\Repositories\ForumThreadSubscribeRepositoryInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;

class LikeOwnerNotificationListener
{
    public function handle(?User $owner, ?Content $resource): ?bool
    {
        if (!$owner) {
            return null;
        }

        if (!$resource instanceof ForumThread) {
            return null;
        }

        $subscribed = resolve(ForumThreadSubscribeRepositoryInterface::class)->getSubscribed(
            $owner,
            $resource->entityId()
        );

        if (null === $subscribed) {
            return false;
        }

        return true;
    }
}
