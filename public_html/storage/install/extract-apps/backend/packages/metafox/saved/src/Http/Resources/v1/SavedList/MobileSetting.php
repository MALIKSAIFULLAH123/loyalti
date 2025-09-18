<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Saved\Http\Resources\v1\SavedList;

use MetaFox\Platform\Resource\MobileSetting as Setting;

/**
 *--------------------------------------------------------------------------
 * SavedList Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class WebSetting.
 */
class MobileSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->asGet()
            ->apiUrl('saveditems-collection')
            ->apiRules([
                'saved_id' => ['truthy', 'saved_id'],
            ])
            ->apiParams([
                'saved_id' => ':saved_id',
            ]);

        $this->add('viewMyCollections')
            ->asGet()
            ->apiUrl('saveditems-collection');

        $this->add('addItem')
            ->apiUrl('core/mobile/form/saved.saved_list.store');

        $this->add('editItem')
            ->apiUrl('core/mobile/form/saved.saved_list.update/:id');

        $this->add('deleteItem')
            ->apiUrl('saveditems-collection/:id')
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('saved::phrase.delete_confirm_saved_list'),
                ]
            );

        $this->add('addItemToNewCollection')
            ->asPost()
            ->apiUrl('saveditems-collection');

        $this->add('addFriend')
            ->asPost()
            ->apiUrl('saveditems-collection/add-friend/:id')
            ->apiParams(['users' => ':users']);

        $this->add('viewFriend')
            ->asGet()
            ->apiUrl('saveditems-collection/view-friend/:id');

        $this->add('leaveCollection')
            ->asDelete()
            ->apiUrl('saveditems-collection/leave-collection/:id')
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('saved::phrase.leave_confirm_saved_list', ),
                ]
            );

        $this->add('viewItem')
            ->asGet()
            ->apiUrl('saveditems-collection/:id');

        $this->add('viewDetailItem')
            ->asGet()
            ->apiUrl('saveditems-collection/item/:id')
            ->apiParams([
                'type' => ':type',
            ]);

        $this->add('getFriendsToInvite')
            ->asGet()
            ->apiUrl('friend/invite-to-item')
            ->apiParams([
                'owner_id'  => ':user_id',
                'item_type' => 'saved_list',
                'item_id'   => ':id',
            ]);
    }
}
