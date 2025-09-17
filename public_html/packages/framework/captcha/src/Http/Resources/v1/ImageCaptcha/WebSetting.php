<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Captcha\Http\Resources\v1\ImageCaptcha;

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
        $this->add('refreshCaptcha')
            ->apiMethod(Constants::METHOD_POST)
            ->apiUrl('image-captcha/refresh')
            ->apiParams([
                'action_name' => ':action_name',
            ]);
    }
}
