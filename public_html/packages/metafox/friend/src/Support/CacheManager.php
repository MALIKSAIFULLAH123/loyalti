<?php

namespace MetaFox\Friend\Support;

/**
 * Class CacheManager.
 */
class CacheManager
{
    /**
     * 1: user_id
     * 2: owner_id.
     */
    public const IS_FRIEND_CACHE = 'friend.is_friend_%s_%s';
    public const IS_FRIEND_CACHE_TIME = 3000;
    public const FRIEND_LIST_IDS = 'friends_of_user_%s';
}
