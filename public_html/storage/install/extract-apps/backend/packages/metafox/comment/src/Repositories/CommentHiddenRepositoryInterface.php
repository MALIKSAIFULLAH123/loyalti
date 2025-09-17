<?php

namespace MetaFox\Comment\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Comment\Models\CommentHide;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Comment.
 * @mixin BaseRepository
 * @method CommentHide getModel()
 * @method CommentHide find($id, $columns = ['*'])
 */
interface CommentHiddenRepositoryInterface
{
    /**
     * @param  User $context
     * @param  int  $id
     * @param  bool $isHidden
     * @return bool
     */
    public function hideComment(User $context, int $id, bool $isHidden): bool;

    /**
     * @param  User $context
     * @param  int  $id
     * @param  bool $isHidden
     * @return bool
     */
    public function hideCommentGlobal(User $context, int $id, bool $isHidden): bool;
}
