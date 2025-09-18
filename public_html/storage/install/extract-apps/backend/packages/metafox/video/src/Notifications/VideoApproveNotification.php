<?php

namespace MetaFox\Video\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Notifications\ApproveNotification;
use MetaFox\Video\Models\Video as Model;

/**
 * stub: packages/notifications/notification.stub.
 */

/**
 * Class VideoApproveNotification.
 * @property Model $model
 * @ignore
 */
class VideoApproveNotification extends ApproveNotification
{
    protected string $type = 'video_approve_notification';

    /**
     * /**
     * Get the mail representation of the notification.
     *
     * @param $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $intro = $this->localize('video::phrase.video_approved_successfully_notification');
        $url   = $this->model->toUrl();

        return (new MailMessage())
            ->locale($this->getLocale())
            ->line($intro)
            ->action($this->localize('core::phrase.view_now'), $url);
    }

    public function callbackMessage(): ?string
    {
        return $this->localize('video::phrase.video_approved_successfully_notification');
    }
}
