<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Event\Http\Resources\v1\Invite;

use MetaFox\Platform\Resource\MobileSetting as Setting;

/**
 * Class WebSetting.
 */
class MobileSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->apiUrl('event-invite')
            ->apiParams(['event_id' => ':id']);

        $this->add('acceptInvite')
            ->apiUrl('event-invite')
            ->asPut()
            ->apiParams(['event_id' => ':id', 'accept' => 1]);

        $this->add('declineInvite')
            ->apiUrl('event-invite')
            ->asPut()
            ->apiParams(['event_id' => ':id', 'accept' => 0]);

        $this->add('cancelInvite')
            ->apiUrl('event-invite')
            ->asDelete()
            ->apiParams(['event_id' => ':event_id', 'user_id' => ':user_id']);

        $this->add('addItem')
            ->apiUrl('core/mobile/form/event.invite.store/:id');
    }
}
