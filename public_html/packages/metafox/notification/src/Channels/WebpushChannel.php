<?php

namespace MetaFox\Notification\Channels;

use Illuminate\Support\Facades\Log;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

class WebpushChannel
{
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
        Log::channel('push')->info('This message has been logged out due to no channel handler registered!');
        Log::channel('push')->info('Notification:');

        $data = json_encode($notification->toArray($notifiable));
        Log::channel('push')->info(is_string($data) ? $data : 'No data.');
    }

    public function configMethodsCallbackMessage(): array
    {
        return [
            'toMobileMessage',
        ];
    }

    public function validateConfiguration(): bool
    {
        return false;
    }
}
