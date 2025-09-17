<?php

namespace MetaFox\Comment\Repositories;

use MetaFox\Comment\Models\Comment;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface  CommentStatisticRepositoryInterface.
 * @mixin BaseRepository
 * @method Comment getModel()
 * @method Comment find($id, $columns = ['*'])
 */
interface CommentStatisticRepositoryInterface
{
    /**
     * @param  Comment $comment
     * @return void
     */
    public function increaseTotal(Comment $comment): void;

    /**
     * @param  Comment $comment
     * @return void
     */
    public function decreaseTotal(Comment $comment): void;

    /**
     * @param  Comment $comment
     * @return void
     */
    public function decreaseTotalPending(Comment $comment): void;
}
