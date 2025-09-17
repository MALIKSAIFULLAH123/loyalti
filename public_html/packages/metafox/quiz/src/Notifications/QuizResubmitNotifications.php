<?php

namespace MetaFox\Quiz\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;
use MetaFox\Quiz\Models\Quiz;

/**
 * Class QuizResubmitNotifications.
 *
 * @property Quiz $model
 */
class QuizResubmitNotifications extends Notification
{
    protected string $type = 'quiz_resubmit_notification';

    public function toArray(IsNotifiable $notifiable): array
    {
        return [
            'data'      => $this->model->toArray(),
            'item_id'   => $this->model->entityId(),
            'item_type' => $this->model->entityType(),
            'user_id'   => $this->model->userId(),
            'user_type' => $this->model->userType(),
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param              $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $url = $this->model->toUrl();

        $subject = $this->localize(
            'quiz::mail.quiz_title_has_changed_please_resubmit_your_answers_subject',
            ['title' => $this->model->toTitle()]
        );
        $intro = $this->localize(
            'quiz::mail.quiz_title_has_changed_please_resubmit_your_answers',
            ['title' => $this->model->toTitle()]
        );

        return (new MailMessage())
            ->locale($this->getLocale())
            ->subject($subject)
            ->line($intro)
            ->action($this->localize('quiz::phrase.quiz'), $url);
    }

    public function callbackMessage(): ?string
    {
        return $this->localize(
            'quiz::notification.quiz_title_has_changed_please_resubmit_your_answers',
            ['title' => $this->model->toTitle()]
        );
    }
}
