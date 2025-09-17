<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Mfa\Http\Resources\v1\UserAuth;

use MetaFox\Form\Constants;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class MobileSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('authForm')
            ->apiMethod(Constants::METHOD_GET)
            ->apiUrl('mfa/user/auth/form')
            ->apiParams([
                'resolution' => MetaFoxConstant::RESOLUTION_MOBILE,
            ]);

        $this->add('resendVerificationAuth')
            ->asPost()
            ->apiUrl('mfa/user/auth/resend')
            ->apiParams([
                'password' => ':password',
                'service'  => ':service',
                'action'   => ':action',
            ]);
    }
}
