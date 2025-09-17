<?php

namespace MetaFox\Friend\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\User\Traits\UserMorphTrait;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Interface FriendRepositoryInterface.
 * @mixin UserMorphTrait
 */
interface FriendRepositoryInterface
{
    /**
     * @param  User      $context
     * @param  User      $owner
     * @param  array     $attributes
     * @return Paginator
     */
    public function viewProfileFriends(User $context, User $owner, array $attributes): Paginator;

    /**
     * @param User                 $context
     * @param User                 $owner
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewFriends(User $context, User $owner, array $attributes): Paginator;

    /**
     * @param User $user
     * @param User $owner
     * @param bool $hasCheckIsFriend
     *
     * @return bool
     * @throws ValidatorException
     * @throws AuthorizationException
     */
    public function addFriend(User $user, User $owner, bool $hasCheckIsFriend): bool;

    /**
     * @param int|null $userId
     * @param int|null $friendId
     *
     * @return bool
     */
    public function isFriend(?int $userId, ?int $friendId): bool;

    /**
     * @param int $userId
     * @param int $friendId
     *
     * @return bool
     */
    public function unFriend(int $userId, int $friendId): bool;

    /**
     * @param int $contextId
     * @param int $userId
     * @param int $limit
     *
     * @return Collection
     */
    public function getMutualFriends(int $contextId, int $userId, int $limit = Pagination::DEFAULT_ITEM_PER_PAGE): Collection;

    /**
     * @param int $contextId
     * @param int $userId
     *
     * @return int
     */
    public function countMutualFriends(int $contextId, int $userId): int;

    public function countTotalFriends(int $userId): int;

    /**
     * @param User  $context
     * @param array $params
     * @return array
     */
    public function getAvailableAddingFriendSuggestion(User $context, array $params = []): array;

    /**
     * @param int $userId
     *
     * @return array<mixed>
     */
    public function getFriendIds(int $userId): array;

    /**
     * @param User                 $context
     * @param array<string, mixed> $params
     *
     * @return array<mixed>
     */
    public function getFriendSuggestion(User $context, array $params): array;

    /**
     * @param  User       $context
     * @param  array      $params
     * @return Collection
     */
    public function getSuggestion(User $context, array $params = []): Collection;

    /**
     * Gets all of your friends' mutual friends.
     * If the "Check Location for Friend Suggestions" setting is enabled, it will additionally check if their location is the same as yours.
     * @param  User    $context
     * @param  Builder $builder
     * @param  array   $attributes
     * @return Builder
     */
    public function getBuilderUserSuggestion(User $context, Builder $builder, array $attributes = []): Builder;

    /**
     * Gets all of your friends' mutual friends and people in the same location as you.
     * @param  User    $context
     * @param  Builder $builder
     * @param  array   $attributes
     * @return Builder
     */
    public function getBuilderUserRecommend(User $context, Builder $builder, array $attributes = []): Builder;

    /**
     * @param  User      $context
     * @param  array     $attributes
     * @return Paginator
     */
    public function getTagSuggestions(User $context, array $attributes): Paginator;

    /**
     * @param User $context
     * @param User $user
     */
    public function hideUserSuggestion(User $context, User $user): bool;

    /**
     * @param User                 $user
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     */
    public function getFriendBirthdays(User $user, array $attributes): Paginator;

    /**
     * @param  User           $context
     * @param  array          $attributes
     * @return BaseCollection
     */
    public function inviteFriendsToItem(User $context, array $attributes): BaseCollection;

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return array<int, mixed>
     */
    public function inviteFriendToOwner(User $context, array $attributes): array;

    /**
     * @param  User           $context
     * @param  User           $user
     * @param  User           $owner
     * @param  array          $attributes
     * @return Paginator|null
     */
    public function viewMembers(User $context, User $user, User $owner, array $attributes): ?Paginator;

    /**
     * @param  User           $context
     * @param  array          $attributes
     * @return Paginator|null
     */
    public function getMentions(User $context, array $attributes): ?Paginator;

    /**
     * @param  int  $userId
     * @return void
     */
    public function deleteUserSuggestionIgnoreData(int $userId): void;

    /**
     * @param  int  $userId
     * @param  int  $ownerId
     * @return void
     */
    public function clearFriendSuggestionCache(int $userId, int $ownerId): void;
}
