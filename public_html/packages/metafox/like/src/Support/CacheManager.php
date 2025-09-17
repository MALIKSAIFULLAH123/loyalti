<?php

namespace MetaFox\Like\Support;

/**
 * Class CacheManager.
 * @ignore
 * @codeCoverageIgnore
 */
class CacheManager
{
    /**
     * 1: item_id
     * 2: item_type.
     */
    public const IS_LIKED_CACHE      = 'is_liked_%s_%s_%s';
    public const IS_LIKED_CACHE_TIME = 3000;

    /**
     * 1: item_id
     * 2: item_type.
     */
    public const USER_REACTED_CACHE      = 'user_reacted_%s_%s_%s';
    public const USER_REACTED_CACHE_TIME = 3000;
}
