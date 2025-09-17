<?php

namespace MetaFox\Forum\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Forum\Models\ForumPost;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * Interface ForumPost.
 * @mixin BaseRepository
 * @mixin CollectTotalItemStatTrait
 * @mixin UserMorphTrait
 */
interface ForumPostRepositoryInterface
{
    /**
     * @param  User      $context
     * @param  User      $owner
     * @param  array     $attributes
     * @return Paginator
     */
    public function viewPosts(User $context, User $owner, array $attributes): Paginator;

    /**
     * @param  User      $context
     * @param  int       $id
     * @return ForumPost
     */
    public function viewPost(User $context, int $id): ForumPost;

    /**
     * @param  User      $context
     * @param  User      $owner
     * @param  array     $attributes
     * @return ForumPost
     */
    public function createPost(User $context, User $owner, array $attributes): ForumPost;

    /**
     * @param  User      $context
     * @param  int       $id
     * @param  array     $attributes
     * @return ForumPost
     */
    public function updatePost(User $context, int $id, array $attributes): ForumPost;

    /**
     * @param  ForumPost $post
     * @return bool
     */
    public function deletePostInBackground(ForumPost $post): bool;

    /**
     * @param  User $context
     * @param  int  $id
     * @return bool
     */
    public function deletePost(User $context, int $id): bool;

    /**
     * @param  User    $context
     * @param  int     $id
     * @return Content
     */
    public function approve(User $context, int $id): Content;

    /**
     * @param  User      $context
     * @param  array     $attributes
     * @return ForumPost
     */
    public function quotePost(User $context, array $attributes): ?ForumPost;

    /**
     * @param  User    $context
     * @param  User    $owner
     * @param  array   $attributes
     * @return Builder
     */
    public function buildQueryForListing(User $context, User $owner, array $attributes): Builder;

    /**
     * @param  int  $threadId
     * @param  int  $postId
     * @return void
     */
    public function sendNotificationForThreadSubscription(int $threadId, int $postId): void;

    /**
     * @param  User       $context
     * @param  int        $threadId
     * @param  array      $params
     * @return Collection
     */
    public function viewPosters(User $context, int $threadId, array $params = []): Collection;
}
