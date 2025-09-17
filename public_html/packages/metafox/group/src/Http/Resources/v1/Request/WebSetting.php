<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Group\Http\Resources\v1\Request;

use MetaFox\Group\Support\Browse\Scopes\Group\ViewScope;
use MetaFox\Group\Support\Browse\Scopes\Request\StatusScope;
use MetaFox\Platform\Resource\WebSetting as Setting;

/**
 *--------------------------------------------------------------------------
 * GroupRequest Web Resource Setting
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
            ->apiUrl('group-request')
            ->apiRules([
                'group_id'   => ['truthy', 'group_id'],
                'q'          => ['truthy', 'q'],
                'view'       => ['includes', 'view', ViewScope::getAllowView()],
                'status'     => ['includes', 'status', StatusScope::getAllowStatus()],
                'start_date' => ['truthy', 'start_date'],
                'end_date'   => ['truthy', 'end_date'],
            ])
            ->apiParams([
                'group_id'   => ':id',
                'q'          => ':q',
                'view'       => ':view',
                'status'     => ':status',
                'start_date' => ':start_date',
                'end_date'   => ':end_date',
            ]);

        $this->add('viewPendingRequest')
            ->apiUrl('group-request')
            ->apiParams([
                'group_id' => ':id',
                'status'   => StatusScope::STATUS_PENDING,
            ]);

        $this->add('searchItem')
            ->apiUrl('/group-request')
            ->apiParams([
                'q'          => ':q',
                'start_date' => ':start_date',
                'end_date'   => ':end_date',
                'status'     => ':status',
                'group_id'   => ':owner_id',
            ])
            ->placeholder(__p('group::phrase.search_requests'));

        $this->add('acceptMemberRequest')
            ->apiUrl('group-request/accept-request')
            ->asPut()
            ->apiParams(['group_id' => ':group_id', 'user_id' => ':user_id']);

        $this->add('denyMemberRequest')
            ->apiUrl('group-request/deny-request')
            ->asDelete()
            ->apiParams(['group_id' => ':group_id', 'user_id' => ':user_id']);

        $this->add('getDeclineRequestForm')
            ->apiUrl('core/form/group.group_request.decline/:id')
            ->asGet();

        $this->add('cancelRequest')
            ->apiUrl('group-request/cancel-request/:id')
            ->asDelete()
            ->confirm(['title' => __p('core::phrase.confirm'), 'message' => __p('group::phrase.are_you_sure_you_want_to_cancel_this_request')]);

        $this->add('getGrid')
            ->apiUrl('core/grid/group.group_request');
    }
}
