<?php

namespace MetaFox\Event\Notifications;

use MetaFox\Event\Models\Event as Model;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Notifications\ApproveNotification;

/**
 * stub: packages/notifications/notification.stub.
 */

/**
 * Class EventApproveNotifications.
 * @property Model $model
 * @ignore
 */
class EventApproveNotifications extends ApproveNotification
{
    protected string $type = 'event_approve_notification';

    /**
     * Get the mail representation of the notification.
     *
     * @param $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $intro = $this->localize('event::notification.event_approved_successfully_notification');
        $url   = $this->model->toUrl();

        return (new MailMessage())
            ->locale($this->getLocale())
            ->line($intro)
            ->action($this->localize('core::phrase.view_now'), $url);
    }

    public function callbackMessage(): ?string
    {
        return $this->localize('event::notification.event_approved_successfully_notification');
    }
}
