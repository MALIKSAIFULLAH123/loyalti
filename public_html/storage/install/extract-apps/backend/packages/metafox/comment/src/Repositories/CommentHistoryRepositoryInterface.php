<?php

namespace MetaFox\Comment\Repositories;

use Illuminate\Database\Eloquent\Collection;
use MetaFox\Comment\Models\Comment;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface CommentHistory
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface CommentHistoryRepositoryInterface
{
    /**
     * @param  User         $context
     * @param  Comment      $comment
     * @param  string|null  $phrase
     * @return void
     */
    public function createHistory(User $context, Comment $comment, string $phrase = null): void;

    /**
     * @param  Comment  $comment
     * @return Collection
     */
    public function viewHistory(Comment $comment): Collection;

    /**
     * @param  Comment  $comment
     * @return bool
     */
    public function checkExists(Comment $comment): bool;
}
