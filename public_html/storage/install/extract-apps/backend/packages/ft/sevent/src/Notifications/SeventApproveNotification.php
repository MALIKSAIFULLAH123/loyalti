<?php

namespace Foxexpert\Sevent\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use Foxexpert\Sevent\Models\Sevent as Model;
use MetaFox\Platform\Notifications\ApproveNotification;

/**
 * stub: packages/notifications/notification.stub.
 */

/**
 * Class SeventApproveNotification.
 * @property Model $model
 * @ignore
 */
class SeventApproveNotification extends ApproveNotification
{
    protected string $type = 'sevent_approve_notification';

    /**
     * Get the mail representation of the notification.
     *
     * @param $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $intro = $this->localize('sevent::phrase.sevent_approved_successfully_notification');
        $url   = $this->model->toUrl();

        return (new MailMessage())
            ->locale($this->getLocale())
            ->line($intro)
            ->action($this->localize('core::phrase.view_now'), $url);
    }

    public function callbackMessage(): ?string
    {
        return $this->localize('sevent::phrase.sevent_approved_successfully_notification');
    }
}
