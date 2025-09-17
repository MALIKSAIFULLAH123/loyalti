<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Authorization\Http\Resources\v1\Device;

use MetaFox\Authorization\Models\UserDevice;
use MetaFox\Platform\Resource\WebSetting as ResourceSetting;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('addItem')
            ->apiUrl(apiUrl('authorization.device.store'))
            ->apiMethod(MetaFoxForm::METHOD_POST)
            ->apiParams([
                'device_token' => ':device_token',
                'device_id'    => ':device_token',
                'token_source' => ':token_source',
                'platform'     => UserDevice::DEVICE_WEB_PLATFORM,
            ]);
    }
}
