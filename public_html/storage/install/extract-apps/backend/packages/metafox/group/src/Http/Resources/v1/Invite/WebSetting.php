<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Group\Http\Resources\v1\Invite;

use MetaFox\Group\Support\Browse\Scopes\Invite\StatusScope;
use MetaFox\Group\Support\Browse\Scopes\Invite\ViewScope;
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
 *
 * @ignore
 * @codeCoverageIgnore
 */
class WebSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->apiUrl('group-invite')
            ->apiParams(['group_id' => ':id']);

        $this->add('viewInviteMemberPending')
            ->apiUrl('group-invite')
            ->apiParams([
                'group_id' => ':id',
                'view'     => ViewScope::VIEW_MEMBERS,
                'status'   => StatusScope::STATUS_PENDING,
            ]);

        $this->add('cancelInvite')
            ->apiUrl('group-invite/cancel')
            ->asPatch()
            ->apiParams(['group_id' => ':group_id', 'user_id' => ':user_id'])
            ->confirm([
                'title'        => __p('group::phrase.confirm_cancel_invite_title'),
                'message'      => 'confirm_cancel_invite_desc',
                'phraseParams' => [
                    'userName' => ':user.full_name',
                ],
            ]);

        $this->add('getGrid')
            ->apiUrl('core/grid/group.group_invite');

        $this->add('searchForm')
            ->apiUrl('core/form/group.group_invite.search_form');
    }
}
