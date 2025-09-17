<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Broadcast\Listeners;

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
            'default_connection' => [
                'type'        => 'string',
                'config_name' => 'broadcasting.default',
                'value'       => config('broadcasting.default'),
            ],
            'connections.ably' => [
                'type'        => 'array',
                'config_name' => 'broadcasting.connections.ably',
                'value'       => config('broadcasting.connections.ably'),
            ],
            'connections.redis' => [
                'type'        => 'array',
                'config_name' => 'broadcasting.connections.redis',
                'value'       => config('broadcasting.connections.redis'),
            ],
            'connections.log' => [
                'type'        => 'array',
                'config_name' => 'broadcasting.connections.log',
                'value'       => config('broadcasting.connections.log'),
            ],
            'connections.null' => [
                'type'        => 'array',
                'config_name' => 'broadcasting.connections.null',
                'value'       => config('broadcasting.connections.null'),
            ],
            'connections.pusher' => [
                'type'        => 'array',
                'config_name' => 'broadcasting.connections.pusher',
                'value'       => config('broadcasting.connections.pusher'),
                'is_public'   => 0,
            ],
        ];
    }
}
