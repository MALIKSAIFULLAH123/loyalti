<?php

namespace MetaFox\LiveStreaming\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\LiveStreaming\Models\LiveVideo as Model;
use MetaFox\Platform\Notifications\ApproveNotification;

/**
 * stub: packages/notifications/notification.stub.
 */

/**
 * Class BlogApproveNotification.
 * @property Model $model
 * @ignore
 */
class LiveVideoApproveNotification extends ApproveNotification
{
    protected string $type = 'livestreaming_approve_notification';

    /**
     * Get the mail representation of the notification.
     *
     * @param              $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $intro = $this->localize('livestreaming::phrase.live_video_approved_successfully_notification');
        $url   = $this->model->toUrl();

        return (new MailMessage())
            ->locale($this->getLocale())
            ->line($intro)
            ->action($this->localize('core::phrase.view_now'), $url);
    }

    public function callbackMessage(): ?string
    {
        return $this->localize('livestreaming::phrase.live_video_approved_successfully_notification');
    }
}
