<?php

namespace MetaFox\Notification\Channels;

use Exception;
use MetaFox\Notification\Support\TypeManager;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;
use MetaFox\Sms\Contracts\ManagerInterface;
use MetaFox\Sms\Support\Message;
use MetaFox\User\Models\User;
use MetaFox\User\Support\Facades\User as UserFacade;

class SmsChannel
{
    /**
     * Create a new sms channel instance.
     *
     * @param ManagerInterface $manager
     *
     * @return void
     */
    public function __construct(protected ManagerInterface $manager) {}

    /**
     * Send the given notification.
     *
     * @param IsNotifiable $notifiable
     * @param Notification $notification
     *
     * @return void
     */
    public function send(IsNotifiable $notifiable, Notification $notification)
    {
        if (!$this->canSend($notifiable, $notification)) {
            return;
        }

        try {
            $message = $notification->toTextMessage($notifiable);
            if (!$message instanceof Message) {
                return;
            }

            $recipients = $notifiable->routeNotificationFor('sms', $notification);
            if (empty($recipients)) {
                return;
            }

            $message->setRecipients($recipients);

            $this->manager->service()->send($message);
        } catch (Exception) {
            // silent
        }
    }

    public function configMethodsCallbackMessage(): array
    {
        return [
            'toTextMessage',
            'callbackMessage',
        ];
    }

    public function validateConfiguration(): bool
    {
        return (bool) app('sms')->validateConfiguration();
    }

    public function canSend(IsNotifiable $notifiable, Notification $notification): bool
    {
        $systemTypes = resolve(TypeManager::class)->getSystemTypes();
        if (!$notifiable instanceof User) {
            return false;
        }

        if (UserFacade::isBan($notifiable->entityId()) && !in_array($notification->getType(), $systemTypes)) {
            return false;
        }

        if (!$notifiable->hasVerified()) {
            return false;
        }

        if (!$notifiable->hasVerifiedPhoneNumber()) {
            return false;
        }

        return true;
    }
}
