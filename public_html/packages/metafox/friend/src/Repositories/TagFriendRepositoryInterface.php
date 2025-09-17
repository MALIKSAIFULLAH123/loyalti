<?php

namespace MetaFox\Friend\Repositories;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as MainCollection;
use MetaFox\Friend\Models\TagFriend;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasTaggedFriend;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Traits\UserMorphTrait;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface TagFriend.
 *
 * @mixin BaseRepository
 * @method TagFriend find($id, $columns = ['*'])
 * @method TagFriend getModel()
 * @mixin UserMorphTrait
 */
interface TagFriendRepositoryInterface
{
    /**
     * @param HasTaggedFriend $item
     * @param int             $limit
     * @param array           $excludedIds
     *
     * @return Builder
     */
    public function getTagFriends(HasTaggedFriend $item, int $limit, array $excludedIds = []): Builder;

    /**
     * @param HasTaggedFriend $item
     * @param User            $owner
     *
     * @return TagFriend|null
     */
    public function getTagFriend(HasTaggedFriend $item, User $owner): ?TagFriend;

    /**
     * @param Entity $item
     *
     * @return Collection
     */
    public function getAllTaggedFriends(Entity $item): Collection;

    /**
     * @param HasTaggedFriend $item
     * @param array|null      $friendIds
     *
     * @return Collection
     */
    public function getItemTagFriends(HasTaggedFriend $item, ?array $friendIds = null): Collection;

    /**
     * $tagFriends = [
     *      [
     *          'friend_id' => 1,
     *          'px' => 1,
     *          'py' => 1,
     *      ],
     *      [ 'friend_id' => 1],
     *      ['friend_id' => 1, 'is_mention' => true, 'content' => 'user test ahihi'],
     * ];.
     *
     * @param User                     $context
     * @param HasTaggedFriend          $item
     * @param array<string|int, mixed> $tagFriends
     *
     * @return bool
     */
    public function createTagFriend(User $context, HasTaggedFriend $item, array $tagFriends, bool $putToTagStream = true): bool;

    /**
     * $tagFriends = [
     *      [
     *          'friend_id' => 1,
     *          'px' => 1,
     *          'py' => 1,
     *      ],
     *      [ 'friend_id' => 1],
     *      ['friend_id' => 1, 'is_mention' => true, 'content' => 'user test ahihi'],
     * ];.
     *
     * @param User            $context
     * @param HasTaggedFriend $item
     * @param int[]           $tagFriends
     *
     * @return bool
     */
    public function updateTagFriend(User $context, HasTaggedFriend $item, array $tagFriends): bool;

    /**
     * @param int $id
     *
     * @return bool
     */
    public function deleteTagFriend(int $id): bool;

    /**
     * @param Entity    $item
     * @param User|null $context
     *
     * @return void
     */
    public function deleteMentionAndTagFriend(Entity $item, ?User $context): void;

    /**
     * @param HasTaggedFriend $item
     * @param array|null      $friendIds
     *
     * @return void
     */
    public function deleteItemTagFriends(HasTaggedFriend $item, ?array $friendIds = null): void;

    /**
     * @return array
     */
    public function getTaggedUserIdsByItem(Entity $item): array;

    /**
     * @param User            $context
     * @param HasTaggedFriend $item
     * @param MainCollection  $users
     * @param array           $attributes
     *
     * @return void
     */
    public function putMultipleToTagStream(User $context, HasTaggedFriend $item, MainCollection $users, array $attributes = []): void;

    /**
     * @param Entity $item
     * @param array  $ownerIds
     * @param string $ownerType
     *
     * @return MainCollection
     */
    public function getUsersForMention(Entity $item, array $ownerIds, string $ownerType): MainCollection;
}
