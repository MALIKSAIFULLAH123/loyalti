<?php

namespace MetaFox\Cache\Http\Resources\v1\Admin;

use Carbon\Carbon;
use Exception;
use Illuminate\Cache\RedisStore;
use Illuminate\Cache\Repository;
use Illuminate\Http\Request;
use Illuminate\Redis\RedisManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use MetaFox\Form\AbstractForm as Form;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;
use Nette\Schema\ValidationException;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * @driverType form-cache
 * @driverName file
 */
class UpdateRedisCacheForm extends Form
{
    protected function prepare(): void
    {
        $res    = $this->resource ?? [];
        $value  = config('database.redis.cache', []);
        $scheme = Arr::get($value, 'scheme');
        $useTLS = $scheme === 'tls' ? 1 : 0;
        Arr::set($value, 'use_tls', $useTLS);

        $action = apiUrl('admin.cache.store.update', ['driver' => 'redis', 'name' => $res['name']]);

        $this->title(__p('cache::phrase.edit_cache_store'))
            ->action($action)
            ->asPut()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::text('host')
                ->required()
                ->label(__p('cache::redis.host_label')),
            Builder::text('port')
                ->required()
                ->label(__p('cache::redis.port_label')),
            Builder::text('database')
                ->required()
                ->label(__p('cache::redis.database_label')),
            Builder::text('username')
                ->optional()
                ->label(__p('core::phrase.username')),
            Builder::text('password')
                ->optional()
                ->label(__p('cache::redis.password_label')),
            Builder::switch('use_tls')
                ->label(__p('cache::redis.use_tls'))
                ->description(__p('cache::redis.use_tls_desc')),
        );

        $this->addDefaultFooter(true);
    }

    /**
     * @param  Request                  $request
     * @return array
     * @throws InvalidArgumentException
     */
    public function validated(Request $request): array
    {
        $connectionConfig = $request->validate([
            'host'     => 'string|required',
            'port'     => 'string|sometimes|nullable',
            'database' => 'string|sometimes|nullable',
            'username' => 'string|required_with:password|nullable',
            'password' => 'string|sometimes|nullable',
            'use_tls'  => 'numeric|sometimes|nullable',
        ]);

        $useTLS = Arr::get($connectionConfig, 'use_tls', 0);
        Arr::forget($connectionConfig, 'use_tls');
        Arr::set($connectionConfig, 'scheme', $useTLS ? 'tls' : 'tcp');

        config()->set('database.redis.cache', $connectionConfig);

        $this->validateCacheConfiguration($connectionConfig);

        $data['driver']     = 'redis';
        $data['connection'] = 'cache';
        $data['label']      = ucfirst('redis');

        Settings::updateSetting(
            'cache',
            'cache.redis',
            'database.redis.cache',
            null,
            $connectionConfig,
            'array',
            false,
            true
        );

        return $data;
    }

    /**
     * @param  array                                        $config
     * @return void
     * @throws InvalidArgumentException|ValidationException
     */
    public function validateCacheConfiguration(array $config): void
    {
        $isValid = false;
        try {
            $name = 'test';
            Config::set('database.redis.' . $name, $config);

            $store = $this->getStore($name);
            $key   = __METHOD__;
            $value = Carbon::now();
            $store->add($key, $value);
            $store->get($key);
            $isValid = true;
        } catch (Exception $error) {
            Log::error($error->getMessage());
        }

        if (!$isValid) {
            abort(422, 'Could not save item to cache store.');
        }
    }

    protected function getStore(string $name)
    {
        $manager = $this->getRedisManager();

        return Cache::repository(new RedisStore($manager, '', $name));
    }

    protected function getRedisManager(): RedisManager
    {
        $redisConfig = app()->make('config')->get('database.redis', []);

        return new RedisManager(app(), Arr::pull($redisConfig, 'client', 'phpredis'), $redisConfig);
    }
}
