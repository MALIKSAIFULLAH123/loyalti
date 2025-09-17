<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Cache\Listeners;

use Illuminate\Support\Str;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Support\BasePackageSettingListener;

/**
 * --------------------------------------------------------------------------
 * Code Generator
 * --------------------------------------------------------------------------
 * stub: src/Listeners/PackageSettingListener.stub.
 */

/**
 * Class PackageSettingListener.
 * @SuppressWarnings(PHPMD)
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSettingListener extends BasePackageSettingListener
{
    private function injectCacheStoreConfig(array &$settings): void
    {
        $settings['default'] = [
            'config_name' => 'cache.default',
            'env_var'     => 'MFOX_CACHE_DRIVER',
            'value'       => 'file',
            'type'        => 'string',
            'is_public'   => 0,
        ];

        $settings['prefix'] = [
            'config_name' => 'cache.prefix',
            'env_var'     => 'CACHE_PREFIX',
            'value'       => Str::slug(Settings::get('core.general.site_name', 'laravel'), '_') . '_cache',
            'type'        => 'string',
            'is_public'   => 0,
        ];
        $settings['stores.throttling'] = [
            'config_name' => 'cache.stores.throttling',
            'value'       => config('cache.stores.throttling'),
            'type'        => 'array',
            'is_public'   => 0,
        ];
    }

    public function getSiteSettings(): array
    {
        $settings = [];

        $this->injectCacheStoreConfig($settings);

        return $settings;
    }
}
