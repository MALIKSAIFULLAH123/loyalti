<?php

namespace MetaFox\Poll\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Repository\Contracts\HasFeature;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsor;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsorInFeed;
use MetaFox\Poll\Models\Poll;
use MetaFox\User\Traits\UserMorphTrait;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Poll.
 * @mixin BaseRepository
 * @method Poll getModel()
 * @method Poll find($id, $columns = ['*'])
 *
 * @mixin CollectTotalItemStatTrait
 * @mixin UserMorphTrait
 */
interface PollRepositoryInterface extends HasSponsor, HasFeature, HasSponsorInFeed
{
    /**
     * @param User                 $context
     * @param User                 $owner
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewPolls(User $context, User $owner, array $attributes): Paginator;

    /**
     * View a poll.
     *
     * @param User $context
     * @param int  $id
     *
     * @return Poll
     * @throws AuthorizationException
     */
    public function viewPoll(User $context, int $id): Poll;

    /**
     * Create a poll.
     *
     * @param User                 $context
     * @param User                 $owner
     * @param array<string, mixed> $attributes
     *
     * @return Poll
     * @throws AuthorizationException
     * @see StoreBlockLayoutRequest
     */
    public function createPoll(User $context, User $owner, array $attributes): Poll;

    /**
     * Update a poll.
     *
     * @param User                 $context
     * @param int                  $id
     * @param array<string, mixed> $attributes
     *
     * @return Poll
     * @throws AuthorizationException
     */
    public function updatePoll(User $context, int $id, array $attributes): Poll;

    /**
     * Delete a poll.
     *
     * @param User $context
     * @param int  $id
     *
     * @return int
     */
    public function deletePoll(User $context, int $id): int;

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
     * @param User $context
     * @param int  $id
     *
     * @return Poll
     */
    public function getPollByAnswerId(User $context, int $id): Poll;

    /**
     * @param  User $context
     * @param  int  $id
     * @return bool
     */
    public function isUserVoted(User $context, int $id): bool;

    /**
     * @param  User        $context
     * @param  int         $id
     * @param  string|null $attachedPermissionName
     * @return array
     */
    public function getDataForEditIntegration(User $context, int $id, ?string $attachedPermissionName = null): array;

    /**
     * @param  User                      $context
     * @param  int                       $id
     * @return array<string, mixed>|null
     */
    public function copy(User $context, int $id): ?array;

    /**
     * @param  array $attributes
     * @return array
     */
    public function prepareDataForFeed(array $attributes): array;
}
