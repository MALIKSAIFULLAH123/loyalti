<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Friend\Http\Resources\v1\FriendList;

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
        // Not used yet
        $this->add('viewAll')
            ->apiUrl('friend/list');

        $this->add('addList')
            ->apiUrl('core/form/friend.friend_list.store');

        $this->add('editList')
            ->apiUrl('core/form/friend.friend_list.update/:id');

        $this->add('deleteItem')
            ->apiUrl('friend/list/:id')
            ->asDelete()
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('friend::phrase.delete_confirm_friend_list'),
                ]
            );

        // Not used yet
        $this->add('assignItem')
            ->apiUrl('friend/list/assign/:id')
            ->asPost();

        $this->add('addItems')
            ->apiUrl('core/form/friend.friend_list.add_items/:id');
    }
}
//end
