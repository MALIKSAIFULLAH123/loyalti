<?php

namespace MetaFox\Music\Notifications;

use MetaFox\Music\Models\Song as Model;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Notifications\ApproveNotification;

/**
 * stub: packages/notifications/notification.stub.
 */

/**
 * Class SongApproveNotification.
 * @property Model $model
 * @ignore
 */
class SongApproveNotification extends ApproveNotification
{
    protected string $type = 'song_approve_notification';

    /**
     * Get the mail representation of the notification.
     *
     * @param $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $intro = __p('music::phrase.song_approved_successfully_notification');

        $url   = $this->model->toUrl();

        return (new MailMessage())->line($intro)
            ->action(__p('core::phrase.view_now'), $url);
    }

    public function callbackMessage(): ?string
    {
        return __p('music::phrase.song_approved_successfully_notification');
    }
}
