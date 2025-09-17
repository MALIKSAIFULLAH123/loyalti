<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Notification\Http\Resources\v1\NotificationSetting;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class MobileSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('getMailNotificationSettingsForm')
            ->apiUrl('notification/setting/form')
            ->asGet()
            ->apiParams([
                'channel' => 'mail',
            ]);

        $this->add('getSMSNotificationSettingsForm')
            ->apiUrl('notification/setting/form')
            ->asGet()
            ->apiParams([
                'channel' => 'sms',
            ]);

        $this->add('getInAppNotificationSettingsForm')
            ->apiUrl('notification/setting/form')
            ->asGet()
            ->apiParams([
                'channel' => 'database',
            ]);

        $this->add('notificationSettingsByChannel')
            ->pageUrl('settings/notifications/:channel')
            ->apiUrl('core/mobile/form/notification.notification_setting.channel')
            ->asGet()
            ->apiParams([
                'channel' => ':channel',
            ]);
    }
}
