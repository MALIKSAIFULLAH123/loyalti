<?php

namespace MetaFox\Photo\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Photo\Models\Photo as Model;
use MetaFox\Platform\Notifications\ApproveNotification;

/**
 * stub: packages/notifications/notification.stub.
 */

/**
 * Class PhotoApproveNotification.
 * @property Model $model
 * @ignore
 */
class PhotoApproveNotification extends ApproveNotification
{
    protected string $type = 'photo_approve_notification';

    /**
     * Get the mail representation of the notification.
     *
     * @param $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $intro = $this->localize('photo::notification.photo_approved_successfully_notification');
        $url   = $this->model->toUrl();

        return (new MailMessage())
            ->locale($this->getLocale())
            ->line($intro)
            ->action($this->localize('core::phrase.view_now'), $url);
    }

    public function callbackMessage(): ?string
    {
        return $this->localize('photo::notification.photo_approved_successfully_notification');
    }
}
