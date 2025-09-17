<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Captcha\Http\Resources\v1\Captcha;

use MetaFox\Form\Constants;
use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('getVerifyForm')
            ->apiMethod(Constants::METHOD_GET)
            ->apiUrl('core/form/captcha.verify')
            ->apiParams([
                'action_name' => ':action_name',
                'auto_focus'  => ':auto_focus',
            ]);
    }
}
