<?php

namespace MetaFox\Cache\Http\Resources\v1\Admin;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use MetaFox\Form\AbstractForm as Form;
use MetaFox\Form\Builder;
use MetaFox\Yup\Yup;
use Nette\Schema\ValidationException;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * @driverType form-cache
 * @driverName database
 */
class UpdateMemcachedCacheForm extends Form
{
    protected function prepare(): void
    {
        $res    = $this->resource ?? [];
        $value  = $res['value'] ?? [];
        $action = apiUrl('admin.cache.store.update', ['driver' => 'memcached', 'name' => $res['name']]);

        $this->title(__p('cache::phrase.edit_cache_store'))
            ->action($action)
            ->asPut()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::text('persistent_id')
                ->label(__p('cache::memcached.persistent_id_label'))
                ->description(__p('cache::memcached.persistent_id_desc'))
                ->yup(Yup::string()->optional()->nullable()),
            Builder::text('sasl[0]')
                ->label(__p('cache::memcached.user_label'))
                ->description(__p('cache::memcached.user_desc'))
                ->yup(Yup::string()->optional()->nullable()),
            Builder::text('sasl[1]')
                ->label(__p('cache::memcached.password_label'))
                ->description(__p('cache::memcached.password_desc'))
                ->yup(Yup::string()->optional()->nullable()),
            Builder::text('servers[0].host')
                ->required()
                ->label(__p('cache::memcached.host_label'))
                ->description(__p('cache::memcached.host_desc')),
            Builder::text('servers[0].port')
                ->required()
                ->label(__p('cache::memcached.port_label'))
                ->description(__p('cache::memcached.port_desc')),
            Builder::text('servers[0].weight')
                ->required()
                ->label(__p('cache::memcached.weight_label'))
                ->description(__p('cache::memcached.weight_desc')),
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
        $data = $request->validate([
            'persistent_id'    => 'sometimes|nullable|string',
            'sasl.0'           => 'sometimes|string|nullable',
            'sasl.1'           => 'sometimes|string|nullable',
            'servers.*.host'   => 'sometimes|string',
            'servers.*.port'   => 'sometimes|int|nullable',
            'servers.*.weight' => 'sometimes|int|nullable',
        ]);

        $data['driver'] = 'memcached';

        $this->validateCacheConfiguration($data);

        return $data;
    }

    /**
     * @param  array                                        $config
     * @return void
     * @throws InvalidArgumentException|ValidationException
     */
    public function validateCacheConfiguration(array $config): void
    {
        try {
            config()->set(['cache.stores.memcached' => $config]);
            $store = Cache::store('memcached');

            $key   = __METHOD__;
            $value = Carbon::now();
            $store->set($key, $value);
            $store->get($key);
        } catch (Exception) {
            throw new \InvalidArgumentException('Could not save item to cache store');
        }
    }
}
