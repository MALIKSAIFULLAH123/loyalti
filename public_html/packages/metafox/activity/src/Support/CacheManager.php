<?php

namespace MetaFox\Activity\Support;

class CacheManager
{
    /**
     * 1: user_id.
     */
    public const ACTIVITY_SNOOZE_CACHE = 'activity_snooze_%s';
    public const ACTIVITY_SNOOZE_CACHE_TIME = 6000;

    /**
     * 1: user_id.
     */
    public const ACTIVITY_HIDDEN_CACHE = 'activity_hidden_%s';
    public const ACTIVITY_HIDDEN_CACHE_TIME = 6000;

    /**
     * 1: user_id.
     */
    public const ACTIVITY_PINS_CACHE = 'activity_pins_%s';
    public const ACTIVITY_PINS_CACHE_TIME = 6000;
}
