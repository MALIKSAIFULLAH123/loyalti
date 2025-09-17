<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Http\Resources\v1\UserBlocked;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 *--------------------------------------------------------------------------
 * UserBlocked Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class UserBlockedWebSetting.
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('unblockItem')
            ->apiUrl('account/blocked-user/:id')
            ->asDelete();

        $this->add('blockItem')
            ->apiUrl('account/blocked-user')
            ->asPost()
            ->apiParams(['user_id' => ':id'])
            ->confirm(['title' => __p('core::phrase.are_you_sure'), 'message' => __p('user::phrase.block_user_confirm')]);

        $this->add('viewBlockItem')
            ->apiUrl('account/blocked-user')
            ->asGet()
            ->apiRules([
                'q' => ['truthy', 'q'],
            ]);
    }
}
