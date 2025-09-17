<?php

namespace MetaFox\Cache\Http\Resources\v1\Admin;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Nette\Schema\ValidationException;
use Psr\SimpleCache\InvalidArgumentException;

trait TraitValidateCacheConfiguration
{
    /**
     * @param  array                                        $config
     * @return void
     * @throws InvalidArgumentException|ValidationException
     */
    public function validateCacheConfiguration(array $config): void
    {
        try {
            config([
                'cache.stores.verify_cache_store_config' => $config,
            ]);

            $store = Cache::store('verify_cache_store_config');

            $key   = __METHOD__;
            $value = Carbon::now();
            $store->set($key, $value);
            $store->get($key);
        } catch (Exception) {
            throw new \InvalidArgumentException('Could not save item to cache store');
        }
    }
}
