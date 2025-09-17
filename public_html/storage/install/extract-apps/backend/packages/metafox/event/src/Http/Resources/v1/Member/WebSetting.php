<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Event\Http\Resources\v1\Member;

use MetaFox\Event\Support\Browse\Scopes\Member\ViewScope;
use MetaFox\Platform\Resource\WebSetting as Setting;

/**
 *--------------------------------------------------------------------------
 * Member Web Resource Setting
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
            ->apiUrl('event-member')
            ->apiParams(['event_id' => ':id'])
            ->apiRules(['q' => ['truthy', 'q'], 'view' => ['includes', 'view', [ViewScope::VIEW_INTERESTED, ViewScope::VIEW_JOINED, ViewScope::VIEW_HOST]]]);

        $this->add('viewJoined')
            ->apiUrl('event-member')
            ->apiParams(['event_id' => ':id', 'view' => ViewScope::VIEW_JOINED]);

        $this->add('viewInterested')
            ->apiUrl('event-member')
            ->apiParams(['event_id' => ':id', 'view' => ViewScope::VIEW_INTERESTED]);

        $this->add('viewHosts')
            ->apiUrl('event-member')
            ->apiParams(['event_id' => ':id', 'view' => ViewScope::VIEW_HOST]);

        $this->add('removeMember')
            ->apiUrl('event-member/member')
            ->asDelete()
            ->apiParams(['event_id' => ':event_id', 'user_id' => ':user_id']);

        $this->add('removeHost')
            ->apiUrl('event-member/host')
            ->asDelete()
            ->apiParams(['event_id' => ':event_id', 'user_id' => ':user_id']);
    }
}
