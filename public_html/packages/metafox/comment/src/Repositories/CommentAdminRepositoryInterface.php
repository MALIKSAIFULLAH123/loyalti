<?php

namespace MetaFox\Comment\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Comment\Models\Comment as Model;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Traits\UserMorphTrait;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Comment.
 * @method Model find($id, $columns = ['*'])
 * @method Model getModel()
 *
 * @mixin CollectTotalItemStatTrait
 * @mixin BaseRepository
 * @mixin UserMorphTrait
 */
interface CommentAdminRepositoryInterface
{
    /**
     * View pending comment.
     * @param  array<string, mixed> $attributes
     * @return Paginator
     */
    public function viewPendingComment(array $attributes): Paginator;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return Content
     * @throws AuthorizationException
     */
    public function approve(User $context, int $id): Content;

    /**
     * Decline a comment.
     * @param int $id
     *
     * @return bool
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function decline(int $id): bool;
}
