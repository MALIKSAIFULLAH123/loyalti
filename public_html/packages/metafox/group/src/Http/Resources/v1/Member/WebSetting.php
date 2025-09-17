<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Group\Http\Resources\v1\Member;

use MetaFox\Group\Support\Browse\Scopes\GroupMember\SortScope;
use MetaFox\Group\Support\Browse\Scopes\GroupMember\ViewScope;
use MetaFox\Group\Support\Browse\Scopes\SearchMember\ViewScope as SearchMember;
use MetaFox\Group\Support\InviteType;
use MetaFox\Platform\Resource\WebSetting as Setting;

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
class WebSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->apiUrl('group-member')
            ->apiParams([
                'group_id'  => ':id',
                'view'      => SearchMember::VIEW_ALL,
                'sort'      => ':sort',
                'sort_type' => ':sort_type',
            ])
            ->apiRules([
                'q'         => ['truthy', 'q'],
                'view'      => ['includes', 'view', ['pending', 'all', 'member', 'admin', 'moderator']],
                'sort'      => ['includes', 'sort', SortScope::getAllowSort()],
                'sort_type' => ['includes', 'sort_type', SortScope::getAllowSortType()],
                'group_id'  => ['truthy', 'group_id'],
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

        $this->add('removeGroupModerator')
            ->apiUrl('group-member/remove-group-moderator')
            ->asDelete()
            ->apiParams(['group_id' => ':group_id', 'user_id' => ':user_id', 'is_delete' => 1]);

        $this->add('removeAsModerator')
            ->apiUrl('group-member/remove-group-moderator')
            ->asDelete()
            ->apiParams(['group_id' => ':group_id', 'user_id' => ':user_id', 'is_delete' => 0]);

        $this->add('removeMember')
            ->apiUrl('core/form/group.group_member.remove_member')
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
            ->apiUrl('core/form/group.group_block.block_member')
            ->asGet()
            ->apiParams(['group_id' => ':group_id', 'user_id' => ':user_id']);

        $this->add('inviteGroupAdmin')
            ->apiUrl('group-member')
            ->asGet()
            ->apiParams([
                'group_id'        => ':group_id',
                'not_invite_role' => 1,
                'view'            => ViewScope::VIEW_INVITE_ADMIN,
            ]);

        $this->add('inviteGroupModerator')
            ->apiUrl('group-member')
            ->asGet()
            ->apiParams([
                'group_id'        => ':group_id',
                'not_invite_role' => 1,
                'view'            => ViewScope::VIEW_INVITE_MODERATOR,
            ]);
    }
}
