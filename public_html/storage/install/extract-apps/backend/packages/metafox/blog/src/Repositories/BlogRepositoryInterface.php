<?php

namespace MetaFox\Blog\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Blog\Models\Blog;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Repository\Contracts\HasFeature;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsor;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsorInFeed;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * Interface BlogRepositoryInterface.
 * @method Blog find($id, $columns = ['*'])
 * @method Blog getModel()
 *
 * @mixin CollectTotalItemStatTrait
 * @mixin UserMorphTrait
 */
interface BlogRepositoryInterface extends HasSponsor, HasFeature, HasSponsorInFeed
{
    /**
     * @param User                 $context
     * @param User                 $owner
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewBlogs(User $context, User $owner, array $attributes): Paginator;

    /**
     * View a blog.
     *
     * @param User $context
     * @param int  $id
     *
     * @return Blog
     * @throws AuthorizationException
     */
    public function viewBlog(User $context, int $id): Blog;

    /**
     * Create a blog.
     *
     * @param User                 $context
     * @param User                 $owner
     * @param array<string, mixed> $attributes
     *
     * @return Blog
     * @throws AuthorizationException
     * @see StoreBlockLayoutRequest
     */
    public function createBlog(User $context, User $owner, array $attributes): Blog;

    /**
     * Update a blog.
     *
     * @param User                 $context
     * @param int                  $id
     * @param array<string, mixed> $attributes
     *
     * @return Blog
     * @throws AuthorizationException
     */
    public function updateBlog(User $context, int $id, array $attributes): Blog;

    /**
     * Delete a blog.
     *
     * @param User $user
     * @param int  $id
     *
     * @return int
     * @throws AuthorizationException
     */
    public function deleteBlog(User $user, int $id): int;

    /**
     * @param int $limit
     *
     * @return Paginator
     */
    public function findFeature(int $limit = 4): Paginator;

    /**
     * @param int $limit
     *
     * @return Paginator
     */
    public function findSponsor(int $limit = 4): Paginator;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return Content
     * @throws AuthorizationException
     */
    public function approve(User $context, int $id): Content;

    /**
     * @param Content $model
     *
     * @return bool
     */
    public function isPending(Content $model): bool;

    /**
     * @param User $user
     * @param int  $id
     *
     * @return Blog
     * @throws AuthorizationException
     */
    public function publish(User $user, int $id): Blog;
}
