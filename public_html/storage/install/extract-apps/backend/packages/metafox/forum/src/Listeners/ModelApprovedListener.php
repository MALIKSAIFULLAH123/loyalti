<?php

namespace MetaFox\Forum\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Forum\Models\Forum;
use MetaFox\Forum\Models\ForumPost;
use MetaFox\Forum\Models\ForumThread;
use MetaFox\Forum\Repositories\ForumPostRepositoryInterface;
use MetaFox\Forum\Repositories\ForumRepositoryInterface;
use MetaFox\Forum\Repositories\ForumThreadRepositoryInterface;
use MetaFox\Forum\Support\Facades\Forum as ForumFacade;
use MetaFox\Platform\Contracts\User;

class ModelApprovedListener
{
    /**
     * @param  User|null $context
     * @param  Model     $model
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(?User $context, Model $model): void
    {
        if ($model->entityType() == ForumPost::ENTITY_TYPE) {
            $this->handleForumPost($model);

            return;
        }

        if ($model->entityType() == ForumThread::ENTITY_TYPE) {
            $this->handleForumThread($model);
            $this->handleApproveIntegratedItem($model);
        }

        $this->handlePendingMode($context, $model);
    }

    protected function handleForumThread(ForumThread $thread): void
    {
        $forum = $thread->forum;

        if (!$forum instanceof Forum) {
            return;
        }

        resolve(ForumRepositoryInterface::class)->increaseTotal($forum->entityId(), 'total_thread');

        ForumFacade::clearCaches($forum->entityId());
    }

    protected function handleApproveIntegratedItem(ForumThread $thread): void
    {
        if (!$thread->item_type || !$thread->item_id) {
            return;
        }

        app('events')->dispatch(
            'forum.thread.integrated_item.approve',
            [$thread->item_type, $thread->item_id],
            true
        );
    }

    protected function handleForumPost(ForumPost $model): void
    {
        $thread = $model->thread;

        if (null !== $thread) {
            if ($model->is_approved) {
                $thread->incrementAmount('total_comment');

                if (null !== $thread->forum) {
                    resolve(ForumRepositoryInterface::class)->increaseTotal($thread->forum->entityId(), 'total_comment');
                }

                resolve(ForumThreadRepositoryInterface::class)->updatePostId($thread);
            }

            resolve(ForumPostRepositoryInterface::class)->sendNotificationForThreadSubscription($thread->entityId(), $model->entityId());
        }
    }

    protected function handlePendingMode(User $context, Model $model): void
    {
        $item = null;
        if ($model instanceof ForumPost) {
            $item = $model->item ?? null;
        }

        if (!$item instanceof ForumPost) {
            return;
        }

        $item->refresh();

        if (!$item->owner?->hasPendingMode()) {
            return;
        }

        if (!$item->isApproved()) {
            return;
        }

        app('events')->dispatch('models.notify.approved', [$context, $item], true);
    }
}
