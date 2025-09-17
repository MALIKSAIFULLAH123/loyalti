<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Event\Http\Resources\v1\InviteCode;

use MetaFox\Platform\Resource\MobileSetting as Setting;

/**
 * Class WebSetting.
 */
class MobileSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('getCode')
            ->apiUrl('event-code')
            ->asPost()
            ->apiParams(['event_id' => ':id', 'refresh' => 0]);

        $this->add('refreshCode')
            ->apiUrl('event-code')
            ->asPost()
            ->apiParams(['event_id' => ':id', 'refresh' => 1]);

        $this->add('verifyCode')
            ->apiUrl('event-code/verify/:code')
            ->asGet();

        $this->add('acceptCode')
            ->apiUrl('event-code/accept/:code')
            ->asPost();
    }
}
