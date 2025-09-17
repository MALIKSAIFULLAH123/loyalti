<?php

namespace Foxexpert\Sevent\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Foxexpert\Sevent\Models\Sevent;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Repository\Contracts\HasFeature;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsor;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsorInFeed;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * Interface SeventRepositoryInterface.
 * @method Sevent find($id, $columns = ['*'])
 * @method Sevent getModel()
 *
 * @mixin CollectTotalItemStatTrait
 * @mixin UserMorphTrait
 */
interface SeventRepositoryInterface extends HasSponsor, HasFeature, HasSponsorInFeed
{
    /**
     * @param User                 $context
     * @param User                 $owner
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewSevents(User $context, User $owner, array $attributes): Paginator;
    public function getYouMayLikeSevents(int $id, int $limit) : Paginator;
    public function massEmail($context, $id, $params);
    /**
     * View a sevent.
     *
     * @param User $context
     * @param int  $id
     *
     * @return Sevent
     * @throws AuthorizationException
     */
    public function viewSevent(User $context, int $id): Sevent;
    /**
     * Create a sevent.
     *
     * @param User                 $context
     * @param User                 $owner
     * @param array<string, mixed> $attributes
     *
     * @return Sevent
     * @throws AuthorizationException
     * @see StoreBlockLayoutRequest
     */
    public function createSevent(User $context, User $owner, array $attributes): Sevent;
    
    /**
     * Update a sevent.
     *
     * @param User                 $context
     * @param int                  $id
     * @param array<string, mixed> $attributes
     *
     * @return Sevent
     * @throws AuthorizationException
     */
    public function updateSevent(User $context, int $id, array $attributes): Sevent;

    /**
     * Delete a sevent.
     *
     * @param User $user
     * @param int  $id
     *
     * @return int
     * @throws AuthorizationException
     */
    public function deleteSevent(User $user, int $id): int;

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
     * @return Sevent
     * @throws AuthorizationException
     */
    public function publish(User $user, int $id): Sevent;

}
