<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Group\Http\Resources\v1\Member;

use MetaFox\Group\Support\Browse\Scopes\GroupMember\ViewScope;
use MetaFox\Group\Support\InviteType;
use MetaFox\Platform\Resource\MobileSetting as Setting;
use MetaFox\Platform\Support\Browse\Browse;

/**
 *--------------------------------------------------------------------------
 * GroupMember Web Resource Setting
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
            ->apiUrl('group-member')
            ->apiParams(['group_id' => ':id'])
            ->apiRules([
                'q'    => ['truthy', 'q'],
                'view' => ['includes', 'view', ['pending', 'all', 'member', 'admin', 'moderator']],
            ]);

        $this->add('addGroupAdmins')
            ->apiUrl('group-member/add-group-admin')
            ->asPost()
            ->apiParams(['group_id' => ':id', 'user_ids' => ':ids']);

        $this->add('addGroupModerators')
            ->apiUrl('group-member/add-group-moderator')
            ->asPost()
            ->apiParams(['group_id' => ':id', 'user_ids' => ':ids']);

        $this->add('changeToModerator')
            ->apiUrl('group-member/change-to-moderator')
            ->asPut()
            ->apiParams(['group_id' => ':group_id', 'user_id' => ':user_id']);

        $this->add('removeGroupAdmin')
            ->apiUrl('group-member/remove-group-admin')
            ->asDelete()
            ->apiParams(['group_id' => ':group_id', 'user_id' => ':user_id', 'is_delete' => 1]);

        $this->add('removeAsAdmin')
            ->apiUrl('group-member/remove-group-admin')
            ->asDelete()
            ->apiParams(['group_id' => ':group_id', 'user_id' => ':user_id', 'is_delete' => 0]);

        $this->add('reassignOwner')
            ->apiUrl('group-member/reassign-owner')
            ->asPut()
            ->apiParams(['group_id' => ':group_id', 'user_id' => ':user_id']);

        $this->add('reassignOwnerForm')
            ->apiUrl('core/mobile/form/group.group_member.reassign_owner/:id')
            ->asGet();

        $this->add('removeGroupModerator')
            ->apiUrl('group-member/remove-group-moderator')
            ->asDelete()
            ->apiParams(['group_id' => ':group_id', 'user_id' => ':user_id', 'is_delete' => 1]);

        $this->add('removeAsModerator')
            ->apiUrl('group-member/remove-group-moderator')
            ->asDelete()
            ->apiParams(['group_id' => ':group_id', 'user_id' => ':user_id', 'is_delete' => 0]);

        $this->add('removeMember')
            ->apiUrl('core/mobile/form/group.group_member.remove_member')
            ->asGet()
            ->apiParams(['group_id' => ':group_id', 'user_id' => ':user_id']);

        $this->add('cancelAdminInvite')
            ->apiUrl('group-member/cancel-invite')
            ->asPatch()
            ->apiParams([
                'group_id'    => ':group_id', 'user_id' => ':user_id',
                'invite_type' => InviteType::INVITED_ADMIN_GROUP,
            ]);

        $this->add('cancelModeratorInvite')
            ->apiUrl('group-member/cancel-invite')
            ->asPatch()
            ->apiParams([
                'group_id'    => ':group_id', 'user_id' => ':user_id',
                'invite_type' => InviteType::INVITED_MODERATOR_GROUP,
            ]);

        $this->add('blockFromGroup')
            ->apiUrl('core/mobile/form/group.group_block.block_member')
            ->asPost()
            ->apiParams(['group_id' => ':group_id', 'user_id' => ':user_id']);

        $this->add('viewMembers')
            ->apiUrl('group-member')
            ->apiParams([
                'group_id' => ':id',
                'view'     => Browse::VIEW_ALL,
            ]);
        $this->add('viewAdmins')
            ->apiUrl('group-member')
            ->apiParams([
                'group_id' => ':id',
                'view'     => ViewScope::VIEW_ADMIN,
            ]);
        $this->add('viewModerators')
            ->apiUrl('group-member')
            ->apiParams([
                'group_id' => ':id',
                'view'     => ViewScope::VIEW_MODERATOR,
            ]);

        $this->add('selectAdmins')
            ->apiUrl('group-member')
            ->apiParams([
                'group_id'        => ':id',
                'view'            => ViewScope::VIEW_MEMBER,
                'not_invite_role' => 1,
                'q'               => ':q',
            ]);
        $this->add('searchGlobalUser')
            ->apiUrl(apiUrl('search.index'))
            ->apiParams([
                'view'       => 'user',
                'q'          => ':q',
                'is_hashtag' => ':is_hashtag',
            ]);
    }
}
