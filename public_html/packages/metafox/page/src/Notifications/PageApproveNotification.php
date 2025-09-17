<?php

namespace MetaFox\Page\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Page\Models\Page as Model;
use MetaFox\Platform\Notifications\ApproveNotification;

/**
 * stub: packages/notifications/notification.stub.
 */

/**
 * Class PageApproveNotification.
 * @property Model $model
 * @ignore
 */
class PageApproveNotification extends ApproveNotification
{
    protected string $type = 'page_approve_notification';

    /**
     * Get the mail representation of the notification.
     *
     * @param              $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $intro = $this->localize('page::notification.page_approved_successfully_notification');
        $url   = $this->model->toUrl();

        return (new MailMessage())
            ->locale($this->getLocale())
            ->subject($this->localize('page::phrase.approve_page_notification_type'))
            ->line($intro)
            ->action($this->localize('core::phrase.view_now'), $url);
    }

    public function callbackMessage(): ?string
    {
        return $this->localize('page::notification.page_approved_successfully_notification');
    }
}
