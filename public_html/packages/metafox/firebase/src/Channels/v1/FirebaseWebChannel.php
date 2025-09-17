<?php

namespace MetaFox\Firebase\Channels\v1;

use Illuminate\Support\Arr;
use MetaFox\Firebase\Channels\Legacy\FirebaseWebChannelLegacy;
use MetaFox\Firebase\Contracts\FirebaseChannelInterface;
use MetaFox\Firebase\Support\Traits\FirebaseChannel;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Notifications\Notification;
use MetaFox\User\Support\Facades\User as UserFacade;

class FirebaseWebChannel implements FirebaseChannelInterface
{
    use FirebaseChannel;

    /**
     * Send the given notification.
     *
     * @param IsNotifiable $notifiable
     * @param Notification $notification
     *
     * @return void
     */
    public function send(IsNotifiable $notifiable, Notification $notification): void
    {
        if (!$this->validateConfiguration()) {
            /*
             * @deprecated Need to remove in some next version.
             */
            (new FirebaseWebChannelLegacy())->send($notifiable, $notification);

            return;
        }

        if (!$notifiable instanceof User) {
            return;
        }

        if (!method_exists($notification, 'toMobileMessage')) {
            return;
        }

        $systemTypes = resolve(\MetaFox\Notification\Support\TypeManager::class)->getSystemTypes();
        if (UserFacade::isBan($notifiable->entityId()) && !in_array($notification->getType(), $systemTypes)) {
            return;
        }

        $data = $notification->setNotifiable($notifiable)->toArray($notifiable);
        $notification->setData(Arr::get($data, 'data', []));

        $message = $notification->toMobileMessage($notifiable);
        $devices = $this->getDeviceRepository()->getUserActiveTokens($notifiable, 'web');

        if (empty($devices) || empty($message)) {
            return;
        }

        $pushData = $this->prepareNotificationData($devices, $message);

        app('firebase.fcm')->sendPushNotification($pushData);
    }

    private function prepareNotificationData(array $devices, array $message): array
    {
        $url    = Arr::get($message, 'url', '');
        $router = Arr::get($message, 'router', '');
        $body   = Arr::get($message, 'message', '');

        return [
            'tokens'   => $devices,
            'bodyData' => [
                'message' => [
                    'data'         => [
                        'resource_link' => $router,
                        'web_link'      => $url,
                        'title'         => html_entity_decode(Settings::get('core.general.site_name')),
                        'vibrate'       => '',
                        'body'          => $body,
                        'click_action'  => '',
                        'sound'         => 'default',
                    ],
                ],
            ],
        ];
    }
}
