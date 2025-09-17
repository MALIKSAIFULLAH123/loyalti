<?php

namespace MetaFox\Comment\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use MetaFox\Comment\Models\Comment;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;

/**
 * Interface Comment.
 * @mixin BaseRepository
 * @mixin CollectTotalItemStatTrait
 *
 * @method Comment getModel()
 * @method Comment find($id, $columns = ['*'])
 */
interface CommentRepositoryInterface
{
    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Comment
     * @throws AuthorizationException
     * @see StoreRequest
     */
    public function createComment(User $context, array $attributes): Comment;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return Comment
     * @throws AuthorizationException
     */
    public function viewComment(User $context, int $id): Comment;

    /**
     * @param User                 $context
     * @param int                  $id
     * @param array<string, mixed> $attributes
     *
     * @return Comment
     * @throws AuthorizationException|ValidationException
     *
     * @see UpdateRequest
     */
    public function updateComment(User $context, int $id, array $attributes): Comment;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return array<string,          mixed>
     * @throws AuthorizationException
     */
    public function deleteCommentById(User $context, int $id): array;

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Collection
     * @throws AuthorizationException
     * @see IndexRequest
     */
    public function viewComments(User $context, array $attributes): Collection;

    /**
     * @param int $parentId
     *
     * @return bool
     */
    public function deleteCommentByParentId(int $parentId): bool;

    /**
     * @param Builder $builder
     * @param User    $context
     * @param array   $attributes
     * @return Builder
     */
    public function getReducerSortingBuilder(Builder $builder, User $context, array $attributes = []): Builder;

    /**
     * @param  User       $context
     * @param  string     $itemType
     * @param  int        $itemId
     * @param  array      $attributes
     * @return Collection
     */
    public function getRelatedCommentsByType(
        User $context,
        string $itemType,
        int $itemId,
        array $attributes = []
    ): Collection;

    /**
     * @param User            $context
     * @param HasTotalComment $content
     *
     * @return Collection
     */
    public function getRelatedComments(User $context, HasTotalComment $content): Collection;

    /**
     * @param User   $context
     * @param string $itemType
     * @param int    $itemId
     * @param array  $attributes
     * @return Builder
     */
    public function getRelatedCommentsByTypeQuery(
        User $context,
        string $itemType,
        int $itemId,
        array $attributes = []
    ): Builder;

    /**
     * @param  User    $context
     * @param  mixed   $itemType
     * @param  mixed   $itemId
     * @param  array   $attributes
     * @return Builder
     */
    public function getRelatedCommentForItemDetailQuery(
        User $context,
        mixed $itemType,
        mixed $itemId,
        array $attributes = []
    ): Builder;

    /**
     * @param User            $context
     * @param HasTotalComment $content
     * @param int             $limit
     * @param array           $attributes
     *
     * @return Collection
     */
    public function getRelatedCommentsForItemDetail(
        User $context,
        HasTotalComment $content,
        int $limit = 6,
        array $attributes = []
    ): Collection;

    /**
     * @param  User                 $context
     * @param  array<string, mixed> $attributes
     * @return array<int, mixed>
     */
    public function getUsersCommentByItem(User $context, array $attributes): array;

    /**
     * @param  User            $context
     * @param  int             $id
     * @param  Entity|null     $content
     * @param  bool            $isFeed
     * @return Collection|null
     */
    public function getRelevantCommentsById(
        User $context,
        int $id,
        ?Entity $content = null,
        bool $isFeed = true
    ): ?Collection;

    /**
     * @param  User            $context
     * @param  HasTotalComment $item
     * @return int
     */
    public function getTotalHidden(User $context, HasTotalComment $item): int;

    /**
     * @param  Comment $comment
     * @return bool
     */
    public function removeLinkPreview(Comment $comment): bool;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return Content
     * @throws AuthorizationException
     */
    public function approve(User $context, int $id): Content;

    /**
     * @param  Comment    $comment
     * @param  User       $context
     * @param  mixed      $limitChildren
     * @return Collection
     */
    public function getReplies(Comment $comment, User $context, mixed $limitChildren): Collection;

    /**
     * @param Comment $comment
     * @return Collection
     */
    public function viewReplyDetail(Comment $comment): Collection;

    /**
     * @param  Comment $comment
     * @param  User    $context
     * @param  array   $attributes
     * @return array|null
     */
    public function translateComment(Comment $comment, User $context, array $attributes): array|null;
}
