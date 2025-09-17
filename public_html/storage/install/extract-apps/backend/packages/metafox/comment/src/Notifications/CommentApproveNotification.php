<?php

namespace MetaFox\Comment\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Comment\Models\Comment as Model;
use MetaFox\Platform\Notifications\ApproveNotification;

/**
 * stub: packages/notifications/notification.stub.
 */

/**
 * Class CommentApproveNotification.
 * @property Model $model
 * @ignore
 */
class CommentApproveNotification extends ApproveNotification
{
    protected string $type = 'comment_approve_notification';

    /**
     * Get the mail representation of the notification.
     *
     * @param              $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $intro = $this->localize('comment::phrase.comment_approved_successfully_notification');
        $url   = $this->model->toUrl();

        return (new MailMessage())
            ->locale($this->getLocale())
            ->line($intro)
            ->action($this->localize('core::phrase.view_now'), $url);
    }

    public function callbackMessage(): ?string
    {
        return $this->localize('comment::phrase.comment_approved_successfully_notification');
    }
}
