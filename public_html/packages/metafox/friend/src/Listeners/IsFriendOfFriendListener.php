<?php

namespace MetaFox\Friend\Listeners;

use Illuminate\Support\Facades\Cache;

/**
 * Class IsFriendOfFriendListener.
 * @ignore
 * @codeCoverageIgnore
 */
class IsFriendOfFriendListener
{
    public function handle(int $userId, int $ownerId): bool
    {
        $cacheName = sprintf('is_friends_of_friend_%s_%s', $userId, $ownerId);

        return Cache::remember($cacheName, 300, function () use ($userId, $ownerId) {
            if (app_active('metafox/friend')) {
                $isFriend = app('events')->dispatch('friend.is_friend', [$userId, $ownerId], true);
                if (!$isFriend) {
                    $friendsOfContext = app('events')->dispatch('friend.friend_ids', [$userId], true);

                    $friendsOfTarget = app('events')->dispatch('friend.friend_ids', [$ownerId], true);

                    if (!empty($friendsOfContext) && !empty($friendsOfTarget)) {
                        foreach ($friendsOfContext as $friendContext) {
                            foreach ($friendsOfTarget as $friendTarget) {
                                if ($friendContext == $friendTarget) {
                                    return true;
                                }
                            }
                        }
                    }
                }
            }

            return false;
        });
    }
}
