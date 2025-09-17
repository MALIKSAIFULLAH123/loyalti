<?php

namespace MetaFox\User\Support\Security;

use Illuminate\Contracts\Cache\Repository as CacheRepository;

class AbstractRateLimiter
{
    /**
     * The cache store implementation.
     *
     * @var CacheRepository
     */
    protected $cache;

    public function __construct(?CacheRepository $cache = null)
    {
        if (null === $cache) {
            $cache = app()->make('cache')->driver(config('cache.limiter', 'throttling'));
        }

        $this->cache = $cache;
    }

    /**
     * @return CacheRepository
     */
    public function cache(): CacheRepository
    {
        return $this->cache;
    }
}
