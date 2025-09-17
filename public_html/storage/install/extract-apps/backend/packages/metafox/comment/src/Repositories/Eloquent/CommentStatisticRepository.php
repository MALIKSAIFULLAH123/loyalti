<?php

namespace MetaFox\Comment\Repositories\Eloquent;

use MetaFox\Comment\Models\Comment;
use MetaFox\Comment\Repositories\CommentStatisticRepositoryInterface;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasAmounts;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\Contracts\HasTotalCommentWithReply;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * Class CommentStatisticRepository.
 * @method Comment getModel()
 * @method Comment find($id, $columns = ['*'])
 * @SuppressWarnings(PHPMD)
 */
class CommentStatisticRepository extends AbstractRepository implements CommentStatisticRepositoryInterface
{
    public const INCREMENT_AMOUNT = 'incrementAmount';
    public const DECREMENT_AMOUNT = 'decrementAmount';

    public function model(): string
    {
        return Comment::class;
    }

    /**
     * @param  Comment $comment
     * @return void
     */
    public function increaseTotal(Comment $comment): void
    {
        $item = $comment->item;

        if (!$item instanceof HasTotalComment) {
            return;
        }

        $column = $comment->isApproved()
            ? HasTotalComment::TOTAL_COMMENT
            : HasTotalComment::TOTAL_PENDING_COMMENT;

        $this->adjustCommentStatistic($item, self::INCREMENT_AMOUNT, $column);

        //update total_comment of parent.
        if ($comment->parent_id < 1) {
            return;
        }

        $this->adjustParentCommentStatistic($comment, self::INCREMENT_AMOUNT, $column);

        $column = $comment->isApproved()
            ? HasTotalComment::TOTAL_REPLY
            : HasTotalComment::TOTAL_PENDING_REPLY;

        $this->adjustReplyStatistic($item, self::INCREMENT_AMOUNT, $column);
    }

    /**
     * @param  Comment $comment
     * @return void
     */
    public function decreaseTotal(Comment $comment): void
    {
        if (!$comment->isApproved()) {
            return;
        }

        $this->decreaseStatistic($comment);
    }

    public function decreaseTotalPending(Comment $comment): void
    {
        $this->decreaseStatistic($comment, true);
    }

    protected function decreaseStatistic(Comment $comment, bool $isPending = false): void
    {
        /**
         * ! Must query the item to check if it's still existed in DB.
         * ! This method is called in a job which make this handler asynchorous and at the time it is called, item no longer exists.
         * ! Causing any actions on this model call thrown an exceptions.
         */
        $item = $comment->item()->first();

        $commentColumn = $isPending
            ? HasTotalComment::TOTAL_PENDING_COMMENT
            : HasTotalComment::TOTAL_COMMENT;

        if ($item instanceof Entity) {
            $this->adjustCommentStatistic($item, self::DECREMENT_AMOUNT, $commentColumn);
        }

        //update total_comment of parent
        if ($comment->parent_id < 1) {
            return;
        }

        $this->adjustParentCommentStatistic($comment, self::DECREMENT_AMOUNT, $commentColumn);

        $replyColumn = $isPending
            ? HasTotalComment::TOTAL_PENDING_REPLY
            : HasTotalComment::TOTAL_REPLY;

        if ($item instanceof Entity) {
            $this->adjustReplyStatistic($item, self::DECREMENT_AMOUNT, $replyColumn);
        }
    }

    protected function adjustCommentStatistic(Entity $item, string $action, string $column): void
    {
        if (!$item instanceof HasTotalComment) {
            return;
        }

        $this->callActionOnItem($item, $action, $column);
    }

    protected function adjustReplyStatistic(Entity $item, string $action, string $column): void
    {
        if (!$item instanceof HasTotalCommentWithReply) {
            return;
        }

        $this->callActionOnItem($item, $action, $column);
    }

    protected function adjustParentCommentStatistic(Comment $comment, string $action, string $column): void
    {
        $parentComment = $comment->parentComment;
        if (!$parentComment instanceof HasAmounts) {
            return;
        }

        $this->callActionOnItem($parentComment, $action, $column);
    }

    protected function callActionOnItem(mixed $item, string $action, string $param): void
    {
        if (!method_exists($item, $action)) {
            return;
        }

        $item->{$action}($param);
    }
}
