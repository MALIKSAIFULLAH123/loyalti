<?php

namespace MetaFox\Forum\Observers;

use MetaFox\Forum\Models\Forum;
use MetaFox\Forum\Models\ForumThread;
use MetaFox\Forum\Repositories\ForumPostRepositoryInterface;
use MetaFox\Forum\Repositories\ForumRepositoryInterface;
use MetaFox\Forum\Support\Facades\Forum as ForumFacade;

/**
 * Class ForumThreadObserver.
 */
class ForumThreadObserver
{
    public function deleted(ForumThread $thread)
    {
        $posts = $thread->posts;

        if (null !== $posts) {
            foreach ($posts as $post) {
                resolve(ForumPostRepositoryInterface::class)->deletePostInBackground($post);
            }
        }

        $thread->tagData()->sync([]);

        $forum = $thread->forum;

        if ($forum instanceof Forum) {
            if ($thread->isApproved()) {
                resolve(ForumRepositoryInterface::class)->decreaseTotal($forum->entityId(), 'total_thread');
                if ($posts->count()) {
                    resolve(ForumRepositoryInterface::class)->decreaseTotal($forum->entityId(), 'total_comment', $posts->count());
                }
            }

            ForumFacade::clearCaches($forum->entityId());
        }

        $thread->lastReads()->delete();

        app('events')->dispatch('notification.delete_mass_notification_by_item', [$thread], true);
    }

    public function created(ForumThread $thread)
    {
        $forum = $thread->forum;

        if ($forum instanceof Forum) {
            if ($thread->isApproved()) {
                resolve(ForumRepositoryInterface::class)->increaseTotal($forum->entityId(), 'total_thread');
            }
            ForumFacade::clearCaches($forum->entityId());
        }
    }
}
