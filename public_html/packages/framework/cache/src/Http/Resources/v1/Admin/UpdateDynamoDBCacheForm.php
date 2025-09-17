<?php

namespace MetaFox\Cache\Http\Resources\v1\Admin;

use Illuminate\Http\Request;
use MetaFox\Form\AbstractForm as Form;
use MetaFox\Form\Builder;
use MetaFox\Yup\Yup;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * @driverType form-cache
 * @driverName file
 */
class UpdateDynamoDBCacheForm extends Form
{
    use TraitValidateCacheConfiguration;

    protected function prepare(): void
    {
        $res    = $this->resource ?? [];
        $value  = $res['value'] ?? [];
        $action = apiUrl('admin.cache.store.update', ['driver' => 'dynamodb', 'name' => $res['name']]);

        $defaults = config('cache.stores.dynamodb');

        foreach ($defaults as $key => $env) {
            if (empty($env)) {
                continue;
            }
            $value[$key] = $env;
        }

        $this->title(__p('cache::phrase.edit_cache_store'))
            ->action($action)
            ->asPut()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::text('key')
                ->required()
                ->label(__p('cache::dynamodb.key_label'))
                ->description(__p('cache::dynamodb.key_desc'))
                ->marginNormal()
                ->yup(Yup::string()->required()),
            Builder::password('secret')
                ->required()
                ->label(__p('cache::dynamodb.secret_label'))
                ->description(__p('cache::dynamodb.secret_desc'))
                ->marginNormal()
                ->yup(Yup::string()->required()),
            Builder::text('region')
                ->required()
                ->label(__p('cache::dynamodb.region_label'))
                ->description(__p('cache::dynamodb.region_desc'))
                ->yup(Yup::string()->required()),
            Builder::text('table')
                ->required()
                ->label(__p('cache::dynamodb.table_label'))
                ->description(__p('cache::dynamodb.table_desc'))
                ->yup(Yup::string()->required()),
            Builder::text('endpoint')
                ->optional()
                ->label(__p('cache::dynamodb.endpoint_label'))
                ->description(__p('cache::dynamodb.endpoint_desc'))
                ->yup(Yup::string()->optional()->nullable()->url())
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
        $config = $request->validate([
            'key'      => 'string|required',
            'secret'   => 'string|required',
            'region'   => 'string|required',
            'table'    => 'string|required',
            'endpoint' => 'sometimes|string|nullable',
        ]);

        $config['driver'] = 'dynamodb';

        $this->validateCacheConfiguration($config);

        return $config;
    }
}
