<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Group\Http\Resources\v1\GroupInviteCode;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('getCode')
            ->apiUrl('group/invite-code')
            ->asPost()
            ->apiParams(['group_id' => ':id', 'refresh' => 0]);

        $this->add('refreshCode')
            ->apiUrl('group/invite-code')
            ->asPost()
            ->apiParams(['group_id' => ':id', 'refresh' => 1]);

        $this->add('verifyCode')
            ->apiUrl('group/invite-code/verify/:code')
            ->asGet();

        $this->add('acceptCode')
            ->apiUrl('group/invite-code/accept/:code')
            ->asPost();
    }
}
