<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Http\Resources\v1\UserVerify;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class MobileSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('resend')
            ->apiUrl('user/verify/resend')
            ->asPost()
            ->apiParams([
                'action'       => ':action',
                'email'        => ':email',
                'user_id'      => ':user_id',
                'phone_number' => ':phone_number',
            ]);

        $this->add('getVerifyForm')
            ->apiUrl('user/verify/form')
            ->asGet()
            ->apiParams([
                'action'       => ':action',
                'email'        => ':email',
                'user_id'      => ':user_id',
                'phone_number' => ':phone_number',
                'resolution'   => 'mobile',
            ]);
    }
}
