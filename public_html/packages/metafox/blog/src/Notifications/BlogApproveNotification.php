<?php

namespace MetaFox\Blog\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Blog\Models\Blog as Model;
use MetaFox\Platform\Notifications\ApproveNotification;

/**
 * stub: packages/notifications/notification.stub.
 */

/**
 * Class BlogApproveNotification.
 * @property Model $model
 * @ignore
 */
class BlogApproveNotification extends ApproveNotification
{
    protected string $type = 'blog_approve_notification';

    /**
     * Get the mail representation of the notification.
     *
     * @param $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $intro = $this->localize('blog::phrase.blog_approved_successfully_notification');
        $url   = $this->model->toUrl();

        return (new MailMessage())
            ->locale($this->getLocale())
            ->line($intro)
            ->action($this->localize('core::phrase.view_now'), $url);
    }

    public function callbackMessage(): ?string
    {
        return $this->localize('blog::phrase.blog_approved_successfully_notification');
    }
}
