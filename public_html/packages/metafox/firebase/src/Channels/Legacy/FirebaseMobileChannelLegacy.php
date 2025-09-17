<?php

namespace MetaFox\Firebase\Channels\Legacy;

use Illuminate\Support\Arr;
use MetaFox\Authorization\Repositories\DeviceRepositoryInterface;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Notifications\Notification;
use stdClass;

class FirebaseMobileChannelLegacy
{
    /**
     * @deprecated Need to remove in some next version.
     * Send the given notification.
     *
     * @param IsNotifiable $notifiable
     * @param Notification $notification
     *
     * @return void
     */
    public function send(IsNotifiable $notifiable, Notification $notification): void
    {
        if (!$notifiable instanceof User) {
            return;
        }

        if (!method_exists($notification, 'toMobileMessage')) {
            return;
        }

        $data = $notification->setNotifiable($notifiable)->toArray($notifiable);
        $notification->setData(Arr::get($data, 'data', []));
        $message = $notification->toMobileMessage($notifiable);
        $devices = $this->getDeviceRepository()->getUserActiveTokens($notifiable, 'mobile');

        if (empty($devices) || empty($message)) {
            return;
        }

        $url    = Arr::get($message, 'url', '');
        $router = Arr::get($message, 'router', '');
        $body   = Arr::get($message, 'message', '');

        $pushData = [
            'tokens'   => $devices,
            'bodyData' => [
                'notification' => [
                    'title'        => html_entity_decode(Settings::get('core.general.site_name')),
                    'badge'        => (string) $this->getCountData($notifiable),
                    'vibrate'      => true,
                    'body'         => $body,
                    'click_action' => '',
                    'sound'        => 'default',
                ],
                'data' => [
                    'resource_link' => $router,
                    'web_link'      => $url,
                ],
            ],
        ];

        app('firebase.fcm')->sendPushNotification($pushData);
    }

    public function getDeviceRepository(): DeviceRepositoryInterface
    {
        return resolve(DeviceRepositoryInterface::class);
    }

    protected function getCountData(User $notifiable): int
    {
        $data  = new stdClass();
        $badge = 0;

        app('events')->dispatch('core.badge_counter', [$notifiable, $data]);

        $status = get_object_vars($data);
        foreach ($status as $count) {
            if (!is_int($count)) {
                continue;
            }

            $badge += $count;
        }

        return $badge;
    }
}
