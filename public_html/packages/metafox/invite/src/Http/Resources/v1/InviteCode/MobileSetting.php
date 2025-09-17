<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Invite\Http\Resources\v1\InviteCode;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class MobileSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('refresh')
            ->asPatch()
            ->apiUrl('invite-code/refresh')
            ->confirm([
                'title'   => __p('core::phrase.confirm'),
                'message' => 'refresh_invite_code_confirm',
            ]);
    }
}
