<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Socialite\Http\Resources\v1\Invite;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('getVerifyInviteForm')
            ->asGet()
            ->apiUrl('core/form/socialite_invite.verify_invite');
    }
}
