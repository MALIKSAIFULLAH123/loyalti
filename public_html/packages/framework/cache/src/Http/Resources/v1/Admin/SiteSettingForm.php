<?php

namespace MetaFox\Cache\Http\Resources\v1\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Yup\Yup;

/**
 * | --------------------------------------------------------------------------
 * | Form Configuration
 * | --------------------------------------------------------------------------
 * | stub: src/Http/Resources/v1/Admin/SiteSettingForm.stub.
 */

/**
 * Class SiteSettingForm.
 * @codeCoverageIgnore
 * @ignore
 */
class SiteSettingForm extends AbstractForm
{
    private array $variables = [];

    private bool $disabled = false;

    /**
     * @return array
     */
    public function getCacheOptions(): array
    {
        $cacheOptions = [];

        $stores = config('cache.stores', []);

        foreach ($stores as $key => $value) {
            if ($value['selectable'] ?? false) {
                $cacheOptions[] = [
                    'label' => $value['label'] ?? ucfirst($key),
                    'value' => $key,
                ];
            }
        }

        return $cacheOptions;
    }

    protected function prepare(): void
    {
        $vars = [
            'cache.default',
            'cache.prefix',
            'cache.stores.throttling',
        ];

        $values = [];

        foreach ($vars as $var) {
            Arr::set($values, $var, Settings::get($var));
        }

        $this->title(__p('core::phrase.settings'))
            ->action('admincp/setting/cache')
            ->asPost()
            ->setValue($values);
    }

    protected function initialize(): void
    {
        $cacheOptions = $this->getCacheOptions();

        $this->addBasic()
            ->addFields(
                Builder::choice('cache.default')
                    ->label(__p('cache::phrase.default_cache_label'))
                    ->description(__p('cache::phrase.default_cache_desc'))
                    ->required()
                    ->options($cacheOptions),
                Builder::text('cache.prefix')
                    ->label(__p('cache::phrase.cache_key_prefix_label'))
                    ->description(__p('cache::phrase.cache_key_prefix_desc'))
                    ->yup(Yup::string()->nullable()->matches('\w+')),
                Builder::choice('cache.stores.throttling.driver')
                    ->required()
                    ->disableClearable()
                    ->label(__p('cache::phrase.cache_throttling_driver_label'))
                    ->description(__p('cache::phrase.cache_throttling_driver_desc'))
                    ->options($this->getThrottlingCacheDriverOptions()),
            );

        $this->addDefaultFooter(true);
    }

    public function validated(Request $request): array
    {
        $rules = [
            'cache.default'                  => ['required', 'string'],
            'cache.prefix'                   => ['sometimes', 'string', 'nullable'],
            'cache.stores.throttling.driver' => ['required', 'string', 'nullable'],
        ];

        $data      = $request->all();
        $validator = Validator::make($data, $rules);
        $data      = $validator->validated();
        $driver    = Arr::pull($data, 'cache.stores.throttling.driver', 'file');

        $throttlingCache = config('cache.stores.throttling', []);

        Arr::set($throttlingCache, 'driver', $driver);
        Arr::set($data, 'cache.stores.throttling', $throttlingCache);

        return $data;
    }

    protected function getThrottlingCacheDriverOptions(): array
    {
        $options = [
            [
                'label' => 'Filesystem',
                'value' => 'file',
            ],
        ];

        $redisConfig = Settings::get('cache.redis.throttling', []);
        $host        = Arr::get($redisConfig, 'host');
        $port        = Arr::get($redisConfig, 'port');
        $database    = Arr::get($redisConfig, 'database');

        if ($host && $port && $database) {
            $options[] = [
                'label' => ucfirst('redis'),
                'value' => 'redis',
            ];
        }

        return $options;
    }
}
