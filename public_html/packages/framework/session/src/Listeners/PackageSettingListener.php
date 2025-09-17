<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Session\Listeners;

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
            'driver' => [
                'config_name' => 'session.driver',
                'env_var'     => 'SESSION_DRIVER',
                'value'       => 'array',
                'type'        => 'string',
                'is_public'   => 0,
            ],
            'lifetime' => [
                'env_var'     => 'SESSION_LIFETIME',
                'config_name' => 'session.lifetime',
                'type'        => 'integer',
                'value'       => 120,
                'is_public'   => 0,
            ],
            'expire_on_close' => [
                'config_name' => 'session.expire_on_close',
                'value'       => false,
                'type'        => 'boolean',
                'is_public'   => 0,
            ],
            'encrypt' => [
                'config_name' => 'session.encrypt',
                'value'       => false,
                'type'        => 'boolean',
                'is_public'   => 0,
            ],
            'path' => [
                'config_name' => 'session.path',
                'value'       => '/',
                'is_public'   => 0,
            ],
            'domain' => [
                'config_name' => 'session.domain',
                'value'       => '',
                'type'        => 'string',
                'is_public'   => 0,
            ],
            'secure' => [
                'config_name' => 'session.secure',
                'env_var'     => 'SESSION_SECURE_COOKIE',
                'value'       => false,
                'type'        => 'boolean',
                'is_public'   => 0,
            ],
            'http_only' => [
                'config_name' => 'session.http_only',
                'value'       => true,
                'type'        => 'boolean',
                'is_public'   => 0,
            ],
            'same_site' => [
                'config_name' => 'session.same_site',
                'value'       => 'lax',
                'is_public'   => 0,
            ],
            'table' => [
                'config_name' => 'session.table',
                'value'       => 'sessions',
                'is_public'   => 0,
            ],
            'connection' => [
                'config_name' => 'session.connection',
                'env_var'     => 'SESSION_CONNECTION',
                'value'       => '',
                'is_public'   => 0,
            ],
            'store' => [
                'config_name' => 'session.store',
                'env_var'     => 'SESSION_STORE',
                'type'        => 'string',
                'is_public'   => 0,
            ],
            'cookie' => [
                'is_public'   => 0,
                'config_name' => 'session.cookie',
                'env_var'     => 'SESSION_COOKIE',
                'value'       => 'mfox_session',
            ],
        ];
    }
}
