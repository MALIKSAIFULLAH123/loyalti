<?php

namespace MetaFox\Activity\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Activity\Models\Feed;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\User\Models\User;

/**
 * Interface FeedAdminRepositoryInterface.
 * @mixin AbstractRepository
 * @method Feed find($id, $columns = ['*'])
 * @method Feed getModel()
 */
interface FeedAdminRepositoryInterface
{
    /**
     * @param  User      $context
     * @param  array     $params
     * @return Paginator
     */
    public function viewFeeds(User $context, array $params): Paginator;

    /**
     * Delete a feed.
     *
     * @param User $user
     * @param int  $id
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function deleteFeed(User $user, int $id): bool;

    /**
     * Delete a feed with all related items.
     *
     * @param User $user
     * @param int  $id
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function deleteFeedWithItems(User $user, int $id): bool;
}
