<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Event\Http\Resources\v1\HostInvite;

use MetaFox\Platform\Resource\WebSetting as Setting;

class WebSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->apiUrl('event-host-invite')
            ->apiParams(['event_id' => ':id']);

        $this->add('acceptInvite')
            ->apiUrl('event-host-invite')
            ->asPut()
            ->apiParams(['event_id' => ':id', 'accept' => 1]);

        $this->add('declineInvite')
            ->apiUrl('event-host-invite')
            ->asPut()
            ->apiParams(['event_id' => ':id', 'accept' => 0]);

        $this->add('cancelInvite')
            ->apiUrl('event-host-invite')
            ->asDelete()
            ->apiParams(['event_id' => ':event_id', 'user_id' => ':user_id']);
    }
}
