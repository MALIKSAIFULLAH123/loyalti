<?php

namespace MetaFox\Forum\Observers;

use MetaFox\Forum\Models\Forum;
use MetaFox\Forum\Models\Moderator;
use MetaFox\Forum\Models\ModeratorAccess;
use MetaFox\Forum\Models\PermissionConfig;

/**
 * Class ForumObserver.
 */
class ForumObserver
{
    public function deleted(Forum $forum): void
    {
        Moderator::query()
            ->where([
                'forum_id' => $forum->entityId()
            ])
            ->each(function (Moderator $moderator) {
                $moderator->delete();
            });

        PermissionConfig::query()
            ->where([
                'forum_id' => $forum->entityId()
            ])
            ->delete();

        ModeratorAccess::query()
            ->where([
                'forum_id' => $forum->entityId(),
            ])
            ->delete();
    }
}

// end stub
