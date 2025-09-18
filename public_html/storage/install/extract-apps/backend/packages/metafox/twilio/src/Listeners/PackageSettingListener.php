<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Twilio\Listeners;

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
            'services.twilio' => [
                'config_name' => 'sms.services.twilio',
                'module_id'   => 'sms',
                'is_public'   => 0,
                'value'       => [
                    'is_core'    => false,
                    'service'    => 'twilio',
                    'sid'        => '',
                    'auth_token' => '',
                    'number'     => '',
                ],
            ],
        ];
    }
}
