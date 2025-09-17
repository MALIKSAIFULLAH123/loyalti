<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Mux\Listeners;

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
    public function getSiteSettings(): array
    {
        return [
            'video.client_id'               => [
                'env_var'   => 'MFOX_MUX_TOKEN_ID',
                'value'     => '',
                'is_public' => false,
            ],
            'video.client_secret'           => [
                'env_var'   => 'MFOX_MUX_TOKEN_SECRET',
                'value'     => '',
                'is_public' => false,
            ],
            'video.webhook_secret'          => [
                'env_var'   => 'MFOX_MUX_WEBHOOK_SECRET',
                'value'     => '',
                'is_public' => false,
            ],
            'livestreaming.client_id'       => [
                'env_var'   => 'MFOX_MUX_LS_TOKEN_ID',
                'value'     => '',
                'is_public' => false,
            ],
            'livestreaming.client_secret'   => [
                'env_var'   => 'MFOX_MUX_LS_TOKEN_SECRET',
                'value'     => '',
                'is_public' => false,
            ],
            'livestreaming.webhook_secret'  => [
                'env_var'   => 'MFOX_MUX_LS_WEBHOOK_SECRET',
                'value'     => '',
                'is_public' => false,
            ],
            'livestreaming.reduced_latency' => [
                'env_var'   => 'MFOX_MUX_LS_REDUCED_LATENCY',
                'value'     => true,
                'is_public' => false,
            ],
            'story.client_id'               => [
                'env_var'   => 'MFOX_MUX_TOKEN_ID',
                'value'     => '',
                'is_public' => false,
            ],
            'story.client_secret'           => [
                'env_var'   => 'MFOX_MUX_TOKEN_SECRET',
                'value'     => '',
                'is_public' => false,
            ],
            'story.webhook_secret'          => [
                'env_var'   => 'MFOX_MUX_STORY_WEBHOOK_SECRET',
                'value'     => '',
                'is_public' => false,
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
