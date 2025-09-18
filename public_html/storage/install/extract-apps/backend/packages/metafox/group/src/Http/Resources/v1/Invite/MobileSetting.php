<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Group\Http\Resources\v1\Invite;

use MetaFox\Group\Support\Browse\Scopes\Invite\StatusScope;
use MetaFox\Group\Support\Browse\Scopes\Invite\ViewScope;
use MetaFox\Platform\Resource\MobileSetting as Setting;

/**
 *--------------------------------------------------------------------------
 * GroupMember Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class MobileSetting.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class MobileSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->apiUrl('group-invite')
            ->apiRules([
                'group_id'     => ['truthy', 'group_id'],
                'q'            => ['truthy', 'q'],
                'view'         => ['includes', 'view', ViewScope::getAllowView()],
                'status'       => ['includes', 'status', StatusScope::getAllowStatus()],
                'sort'         => ['truthy', 'sort'],
                'sort_type'    => ['truthy', 'sort_type'],
                'created_from' => ['truthy', 'created_from'],
                'created_to'   => ['truthy', 'created_to'],
            ])
            ->apiParams([
                'group_id'     => ':id',
                'q'            => ':q',
                'view'         => ':view',
                'status'       => ':status',
                'sort'         => ':sort',
                'sort_type'    => ':sort_type',
                'created_from' => ':created_from',
                'created_to'   => ':created_to',
            ]);

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

        $this->add('addItem')
            ->apiUrl('core/mobile/form/group.invite.store/:id');

        $this->add('searchItem')
            ->apiUrl('/group-invite')
            ->apiParams([
                'group_id'     => ':owner_id',
                'q'            => ':q',
                'view'         => ':view',
                'status'       => ':status',
                'sort'         => ':sort',
                'sort_type'    => ':sort_type',
                'created_from' => ':created_from',
                'created_to'   => ':created_to',
            ])
            ->placeholder(__p('group::phrase.search_group_invite_placeholder'));
    }
}
