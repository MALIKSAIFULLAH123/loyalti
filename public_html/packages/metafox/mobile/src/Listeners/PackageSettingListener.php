<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Mobile\Listeners;

use MetaFox\Mobile\Supports\Support;
use MetaFox\Platform\Support\BasePackageSettingListener;

/**
 * Class PackageSettingListener.
 * @SuppressWarnings(PHPMD)
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSettingListener extends BasePackageSettingListener
{
    public function getSiteSettings(): array
    {
        return [
            'admob_banner_uid.android' => [
                'type'  => 'string',
                'value' => '',
            ],
            'admob_banner_uid.ios' => [
                'type'  => 'string',
                'value' => '',
            ],
            'admob_interstitial_uid.android' => [
                'type'  => 'string',
                'value' => '',
            ],
            'admob_interstitial_uid.ios' => [
                'type'  => 'string',
                'value' => '',
            ],
            'admob_rewarded_uid.android' => [
                'type'  => 'string',
                'value' => '',
            ],
            'admob_rewarded_uid.ios' => [
                'type'  => 'string',
                'value' => '',
            ],
            'google_app_id' => [
                'type'  => 'string',
                'value' => '',
            ],
            'apple_app_id' => [
                'type'  => 'string',
                'value' => '',
            ],
            'smart_banner.icon' => [
                'type'      => 'string',
                'value'     => '',
                'is_public' => false,
            ],
            'smart_banner.title' => [
                'type'  => 'string',
                'value' => 'mobile::phrase.smart_banner_config_tile',
            ],
            'smart_banner.button' => [
                'type'  => 'string',
                'value' => 'mobile::phrase.smart_banner_config_button',
            ],
            'smart_banner.store_text.android' => [
                'type'  => 'string',
                'value' => 'mobile::phrase.smart_banner_config_store_text_android',
            ],
            'smart_banner.store_text.ios' => [
                'type'  => 'string',
                'value' => 'mobile::phrase.smart_banner_config_store_text_ios',
            ],
            'smart_banner.price.android' => [
                'type'  => 'string',
                'value' => 'mobile::phrase.smart_banner_config_price_android',
            ],
            'smart_banner.price.ios' => [
                'type'  => 'string',
                'value' => 'mobile::phrase.smart_banner_config_price_ios',
            ],
            'smart_banner.position' => [
                'type'  => 'string',
                'value' => Support::SMART_BANNER_POSITION_TOP,
            ],
        ];
    }

    public function getEvents(): array
    {
        return [
            'packages.installed' => [
                PackageInstalledListener::class,
            ],
        ];
    }
}
