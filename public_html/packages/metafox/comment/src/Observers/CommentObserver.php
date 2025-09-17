<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Comment\Observers;

use MetaFox\Comment\Models\Comment;
use MetaFox\Comment\Models\CommentHistory;
use MetaFox\Comment\Repositories\CommentRepositoryInterface;
use MetaFox\Comment\Repositories\CommentStatisticRepositoryInterface;
use MetaFox\Platform\Contracts\Entity;

class CommentObserver
{
    /**
     * @param Comment $model
     */
    public function created(Comment $model): void
    {
        $this->getCommentStatisticService()->increaseTotal($model);
        $this->redundantFeed($model->item);
    }

    /**
     * @param Comment $model
     */
    public function deleted(Comment $model): void
    {
        $item = $model->item;

        $model->tagData()->sync([]);

        $this->handleDecreaseTotal($model);

        //delete hide comment
        $model->commentHides()->delete();

        //delete comment attachment
        $commentAttachment = $model->commentAttachment;

        if (null != $commentAttachment) {
            // todo check to rollDown attachment
            $commentAttachment->delete();
        }

        //delete children
        $this->getCommentService()->deleteCommentByParentId($model->entityId());

        //delete history
        $model->commentHistory()->each(function (CommentHistory $commentHistory) {
            $commentHistory->delete();
        });

        $this->redundantFeed($item);
    }

    protected function handleDecreaseTotal(Comment $model): void
    {
        if ($model->isApproved()) {
            $this->getCommentStatisticService()->decreaseTotal($model);

            return;
        }

        $this->getCommentStatisticService()->decreaseTotalPending($model);
    }

    private function redundantFeed(?Entity $item): void
    {
        app('events')->dispatch('activity.redundant', [$item], true);
    }

    private function getCommentService(): CommentRepositoryInterface
    {
        return resolve(CommentRepositoryInterface::class);
    }

    protected function getCommentStatisticService(): CommentStatisticRepositoryInterface
    {
        return resolve(CommentStatisticRepositoryInterface::class);
    }
}
