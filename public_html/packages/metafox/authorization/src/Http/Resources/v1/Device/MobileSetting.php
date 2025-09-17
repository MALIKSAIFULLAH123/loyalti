<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Authorization\Http\Resources\v1\Device;

use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class MobileSetting extends ResourceSetting
{
    public function initialize(): void
    {
        $this->add('addItem')
            ->apiUrl(apiUrl('authorization.device.store'))
            ->apiMethod(MetaFoxForm::METHOD_POST);
    }
}
