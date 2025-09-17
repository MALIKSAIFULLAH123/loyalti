<?php

namespace MetaFox\Friend\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Friend\Models\FriendRequest;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Traits\UserMorphTrait;
use Prettus\Validator\Exceptions\ValidatorException;
use stdClass;

/**
 * @mixin UserMorphTrait
 */
interface FriendRequestRepositoryInterface
{
    /**
     * @param  User                   $context
     * @param  stdClass               $data
     * @return void
     * @throws AuthorizationException
     */
    public function countFriendRequest(User $context, StdClass $data): void;

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewRequests(User $context, array $attributes): Paginator;

    /**
     * @param User $user
     * @param User $owner
     *
     * @return array<int,             mixed>
     * @throws AuthorizationException
     * @throws ValidatorException
     */
    public function sendRequest(User $user, User $owner): array;

    /**
     * @param User   $user
     * @param User   $owner
     * @param string $action
     *
     * @return array<int,             mixed>
     * @throws AuthorizationException
     * @throws ValidatorException
     */
    public function updateRequest(User $user, User $owner, string $action): array;

    /**
     * @param int $userId
     * @param int $ownerId
     *
     * @return bool
     */
    public function isRequested(int $userId, int $ownerId): bool;

    /**
     * @param User $owner
     *
     * @return void
     */
    public function markAllAsRead(User $owner): void;

    /**
     * @param int $userId
     * @param int $ownerId
     *
     * @return Model|FriendRequest|object|null
     */
    public function getRequest(int $userId, int $ownerId);

    /**
     * @param User $context
     * @param int  $id
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function deleteRequestById(User $context, int $id): bool;

    /**
     * @param User $context
     * @param int  $ownerId
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function deleteRequestByUserIdAndOwnerId(User $context, int $ownerId): bool;

    /**
     * @param int $userId
     * @param int $ownerId
     *
     * @return bool
     */
    public function deleteAllRequestByUserIdAndOwnerId(int $userId, int $ownerId): bool;

    /**
     * @param  User                   $context
     * @return int
     * @throws AuthorizationException
     */
    public function countTotalFriendRequest(User $context): int;
}
