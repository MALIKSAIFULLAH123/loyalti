<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Activity\Http\Resources\v1\Snooze;

use MetaFox\Platform\Resource\MobileSetting as Setting;

/**
 * Class MobileSetting.
 */
class MobileSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('searchItem')
            ->apiUrl('feed/snooze')
            ->apiParams([
                'q'    => ':q',
                'type' => ':type',
            ])
            ->apiRules([
                'q'    => ['truthy', 'q'],
                'type' => ['truthy', 'type'],
            ]);

        $this->add('snooze')
            ->asPost()
            ->apiUrl('feed/snooze')
            ->apiParams(['user_id' => ':id']);

        $this->add('snoozeForever')
            ->asPost()
            ->apiUrl('feed/snooze/forever')
            ->apiParams(['user_id' => ':id']);

        $this->add('unSnooze')
            ->asDelete()
            ->apiUrl('feed/snooze')
            ->apiParams(['user_id' => ':id']);
    }
}
