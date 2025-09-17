<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Socialite\Http\Resources\v1\Invite;

use MetaFox\Platform\Resource\MobileSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class MobileSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('getVerifyInviteForm')
            ->asGet()
            ->apiParams(['hash' => ':hash'])
            ->apiUrl('core/mobile/form/socialite_invite.verify_invite');
    }
}
