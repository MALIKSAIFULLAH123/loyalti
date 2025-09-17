<?php

namespace MetaFox\Quiz\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Notifications\ApproveNotification;
use MetaFox\Quiz\Models\Quiz as Model;

/**
 * stub: packages/notifications/notification.stub.
 */

/**
 * Class QuizApproveNotifications.
 * @property Model $model
 * @ignore
 */
class QuizApproveNotifications extends ApproveNotification
{
    protected string $type = 'quiz_approve_notification';

    /**
     * Get the mail representation of the notification.
     *
     * @param              $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $subject = $this->localize('quiz::phrase.quiz_approve_notification_type');
        $intro   = $this->localize('quiz::notification.quiz_approved_successfully_notification');
        $url     = $this->model->toUrl();

        return (new MailMessage())
            ->locale($this->getLocale())
            ->subject($subject)
            ->line($intro)
            ->action($this->localize('core::phrase.view_now'), $url);
    }

    public function callbackMessage(): ?string
    {
        return $this->localize('quiz::notification.quiz_approved_successfully_notification');
    }
}
