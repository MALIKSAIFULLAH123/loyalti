<?php

namespace MetaFox\Quiz\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Contracts\UserEntity;
use MetaFox\Platform\Notifications\Notification;
use MetaFox\Quiz\Models\Quiz;
use MetaFox\Quiz\Models\Result as Model;

/**
 * Class SubmitResultNotifications.
 * @property Model $model
 * @ignore
 */
class SubmitResultNotifications extends Notification
{
    protected string $type = 'quiz_result_submitted_notification';

    /**
     * Get the mail representation of the notification.
     *
     * @param              $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $url      = $this->model->quiz->toUrl();
        $user     = $this->model->userEntity;
        $yourName = $user instanceof UserEntity ? $user->name : null;
        $title    = $this->model->quiz->toTitle();

        $subject = $this->localize('quiz::phrase.quiz_result_submitted_notification_type');
        $intro   = $this->localize('quiz::notification.user_play_on_quiz_notification', [
            'user'  => $yourName,
            'title' => $title,
        ]);

        return (new MailMessage())
            ->locale($this->getLocale())
            ->subject($subject)
            ->line($intro)
            ->action($this->localize('quiz::phrase.quiz'), $url);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  IsNotifiable $notifiable
     * @return array
     */
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

    public function callbackMessage(): ?string
    {
        $user     = $this->model->userEntity;
        $yourName = $user instanceof UserEntity ? $user->name : null;
        $title    = $this->model->quiz->toTitle();

        return $this->localize('quiz::notification.user_play_on_quiz_notification', [
            'user'  => $yourName,
            'title' => $title,
        ]);
    }

    public function toUrl(): ?string
    {
        if ($this->model->quiz instanceof Quiz) {
            return $this->model->quiz->toUrl();
        }

        return null;
    }

    public function toLink(): ?string
    {
        if ($this->model->quiz instanceof Quiz) {
            return $this->model->quiz->toLink();
        }

        return null;
    }

    public function toRouter(): ?string
    {
        if ($this->model->quiz instanceof Quiz) {
            return $this->model->quiz->toRouter();
        }

        return null;
    }
}
