<?php

namespace MetaFox\Platform\Traits\Helpers;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasTaggedFriend;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;

/**
 * Trait IsFriendTrait.
 */
trait IsFriendTrait
{
    public function isFriend(User $context, ?User $user = null): bool
    {
        return LoadReduce::remember(
            sprintf('friend::exists(user:%s,user:%s)', $context->id, $user?->id),
            fn () => $user instanceof User
                && app('events')->dispatch('friend.is_friend', [$context->id, $user?->id], true)
        );
    }

    public function canAddFriend(User $context, ?User $user = null): bool
    {
        if (!app_active('metafox/friend')) {
            return false;
        }

        if (!$user instanceof User) {
            return false;
        }

        return LoadReduce::remember(
            sprintf('friend::can_add_friend(user:%s,owner:%s)', $context->id, $user->id),
            fn () => (bool) app('events')->dispatch('friend.can_add_friend', [$context, $user], true)
        );
    }

    public function getTaggedFriends(?Entity $item, int $limit = 10, array $excludedIds = []): ?Builder
    {
        if ($item === null) {
            return null;
        }

        if (!app_active('metafox/friend')) {
            return null;
        }

        if (!$item instanceof HasTaggedFriend) {
            return null;
        }

        if ($item->total_tag_friend < 1) {
            return null;
        }

        /** @var Builder|null $tagFriends */
        $tagFriends = app('events')->dispatch('friend.get_tag_friends', [$item, $limit, $excludedIds], true);

        if (!$tagFriends instanceof Builder) {
            return null;
        }

        return $tagFriends;
    }

    public function getTaggedFriend(?Entity $item, User $friend)
    {
        if ($item === null) {
            return null;
        }

        if (!app_active('metafox/friend')) {
            return null;
        }

        if (!$item instanceof HasTaggedFriend) {
            return null;
        }

        return app('events')->dispatch('friend.get_tag_friend', [$item, $friend], true);
    }

    public function countTotalFriend(int $userId): int
    {
        if (!app_active('metafox/friend')) {
            return 0;
        }

        return LoadReduce::remember(
            sprintf('countTotalFriend-%s', $userId),
            function () use ($userId) {
                /** @var int $totalFriend */
                $totalFriend = app('events')->dispatch('friend.count_total_friend', [$userId], true);

                return !empty($totalFriend) ? $totalFriend : 0;
            }
        );
    }

    public function countTotalMutualFriend(int $contextId, int $userId): int
    {
        if (!app_active('metafox/friend')) {
            return 0;
        }

        return LoadReduce::remember(
            sprintf('countTotalMutualFriend-%s-%s', $userId, $contextId),
            function () use ($contextId, $userId) {
                /** @var int $totalMutualFriend */
                $totalMutualFriend = app('events')
                    ->dispatch('friend.count_total_mutual_friend', [$contextId, $userId], true);

                return !empty($totalMutualFriend) ? $totalMutualFriend : 0;
            }
        );
    }

    public function countTotalFriendRequest(User $user): int
    {
        if (!app_active('metafox/friend')) {
            return 0;
        }

        return LoadReduce::remember(
            sprintf('countTotalFriendRequest-%s', $user->id),
            function () use ($user) {
                /** @var int $totalFriendRequest */
                $totalFriendRequest = app('events')
                    ->dispatch('friend.count_total_friend_request', [$user], true);

                return !empty($totalFriendRequest) ? $totalFriendRequest : 0;
            }
        );
    }

    /**
     * @param HasTaggedFriend $item
     * @param int             $limit
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getTaggedFriendItems(HasTaggedFriend $item, int $limit = 10): array
    {
        if ($item->total_tag_friend < 1) {
            return [];
        }

        $taggedFriendsQuery = $this->getTaggedFriends($item, $limit);

        if (!$taggedFriendsQuery instanceof Builder) {
            return [];
        }

        $taggedFriendsData = $taggedFriendsQuery->paginate($limit, ['user_entities.*'], 'tag_friend_page');

        if (!$taggedFriendsData->count()) {
            return [];
        }

        return $taggedFriendsData->items();
    }
}
