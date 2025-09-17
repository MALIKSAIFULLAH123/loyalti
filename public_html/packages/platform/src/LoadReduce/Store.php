<?php

namespace MetaFox\Platform\LoadReduce;

use Illuminate\Contracts\Cache\Store as CacheStore;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class Store implements CacheStore
{
    private array $store = [];

    public function get($key)
    {
        return array_key_exists($key, $this->store) ? $this->store[$key] : null;
    }

    public function has($key): bool
    {
        return array_key_exists($key, $this->store);
    }

    public function many(array $keys)
    {
        return array_reduce($keys, function ($carry, $key) {
            $carry[$key] = $this->get($key);

            return $carry;
        }, []);
    }

    public function put($key, $value, $seconds = null)
    {
        $this->store[$key] = $value;
    }

    public function putMany(array $values, $seconds = null)
    {
        foreach ($values as $key => $value) {
            $this->store[$key] = $value;
        }
    }

    public function increment($key, $value = 1)
    {
        // do nothing
    }

    public function decrement($key, $value = 1)
    {
        // do nothing
    }

    public function forever($key, $value)
    {
        $this->put($key, $value);
    }

    public function forget($key)
    {
        unset($this->store[$key]);
    }

    public function flush()
    {
        $this->store = [];
    }

    public function getPrefix()
    {
        return '';
    }

    public function all()
    {
        return $this->store;
    }
}
