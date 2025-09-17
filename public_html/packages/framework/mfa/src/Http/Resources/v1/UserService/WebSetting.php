<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Mfa\Http\Resources\v1\UserService;

use MetaFox\Form\Constants;
use MetaFox\Mfa\Support\Facades\Mfa;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('setup')
            ->apiMethod(Constants::METHOD_GET)
            ->apiUrl('mfa/user/service/setup')
            ->apiParams([
                'resolution' => MetaFoxConstant::RESOLUTION_WEB,
                'service'    => ':service',
            ])
            ->apiRules([
                'service' => ['includes', 'sort', Mfa::getAllowedServices()],
            ]);

        $this->add('remove')
            ->apiMethod(Constants::METHOD_GET)
            ->apiUrl('core/form/mfa.user_service.deactivate_authenticator_form')
            ->apiParams([
                'service' => ':service',
            ])
            ->apiRules([
                'service' => ['includes', 'sort', Mfa::getAllowedServices()],
            ]);

        $this->add('resendVerificationSetup')
            ->asPost()
            ->apiUrl('mfa/user/service/setup/resend')
            ->apiParams([
                'service' => ':service',
                'action'  => ':action',
            ]);
    }
}
