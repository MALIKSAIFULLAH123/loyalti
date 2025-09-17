<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Friend\Http\Resources\v1\FriendRequest;

use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Resource\MobileSetting as Setting;

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
class MobileSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('acceptItem')
            ->apiUrl('friend/request/:id')
            ->asPut();

        $this->add('denyItem')
            ->apiUrl('friend/request/:id')
            ->asPut();

        $this->add('deleteItem')
            ->apiUrl('friend/request/:id')
            ->asDelete()
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('friend::phrase.delete_confirm_friend_request'),
                ]
            );

        $this->add('viewAll')
            ->apiUrl('friend/request')
            ->apiParams([
                'view'  => 'pending',
                'limit' => MetaFoxConstant::DEFAULT_LIMIT_FRIEND_REQUEST,
            ]);

        $this->add('markAllAsRead')
            ->apiUrl('friend/request/markAllAsRead')
            ->asPost();

        $this->add('viewSentRequests')
            ->apiUrl('friend/request')
            ->apiParams([
                'view'  => 'send',
                'limit' => MetaFoxConstant::DEFAULT_LIMIT_FRIEND_REQUEST,
            ]);
    }
}
