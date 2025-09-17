<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Friend\Http\Resources\v1\FriendSuggestion;

use MetaFox\Platform\Resource\WebSetting as Setting;

/**
 *--------------------------------------------------------------------------
 * Friend Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class WebSetting.
 * @ignore
 * @codeCoverageIgnore
 */
class WebSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('hideUserSuggestion')
            ->asPost()
            ->apiUrl('friend/suggestion/hide')
            ->apiParams([
                'user_id' => ':user_id',
            ]);

        $this->add('suggestItem')
            ->apiUrl('friend/suggestion');

        $this->add('sendRequest')
            ->asPost()
            ->apiUrl('friend/request?friend_user_id=:id');

        $this->add('cancelRequest')
            ->asDelete()
            ->apiUrl('friend/request/:id');

        $this->add('acceptFriendRequest')
            ->asPut()
            ->apiUrl('friend/request/:id');

        $this->add('denyFriendRequest')
            ->asPut()
            ->apiUrl('friend/request/:id');

        $this->add('blockItem')
            ->apiUrl('account/blocked-user')
            ->asPost()
            ->apiParams(['user_id' => ':id'])
            ->confirm([
                'title'   => __p('core::phrase.are_you_sure'),
                'message' => __p('user::phrase.block_user_confirm'),
            ]);

    }
}
