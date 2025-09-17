<?php

namespace MetaFox\Poll\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Notifications\ApproveNotification;
use MetaFox\Poll\Models\Poll as Model;

/**
 * stub: packages/notifications/notification.stub.
 */

/**
 * Class PollApproveNotification.
 * @property Model $model
 * @ignore
 */
class PollApproveNotification extends ApproveNotification
{
    protected string $type = 'poll_approve_notification';

    /**
     * Get the mail representation of the notification.
     *
     * @param              $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $subject = $this->localize('poll::phrase.poll_approve_notification_type');
        $content = $this->localize('poll::mail.admin_approved_your_poll', [
            'title' => $this->model->toTitle(),
        ]);
        $url = $this->model->toUrl();

        return (new MailMessage())
            ->locale($this->getLocale())
            ->subject($subject)
            ->line($content)
            ->action($this->localize('core::phrase.view_now'), $url);
    }

    public function callbackMessage(): ?string
    {
        $model = $this->model;

        if (null === $model) {
            return null;
        }

        return $this->localize('poll::notification.admin_approved_your_poll', [
            'title' => $this->model->toTitle(),
        ]);
    }
}
