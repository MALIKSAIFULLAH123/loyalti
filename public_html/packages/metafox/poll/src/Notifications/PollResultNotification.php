<?php

namespace MetaFox\Poll\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\HasTitle;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Contracts\UserEntity;
use MetaFox\Platform\Notifications\Notification;
use MetaFox\Poll\Models\Poll;
use MetaFox\Poll\Models\Result as Model;
use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * stub: packages/notifications/notification.stub.
 */

/**
 * Class PollResultNotification.
 * @property Model $model
 * @ignore
 */
class PollResultNotification extends Notification
{
    protected string $type = 'poll_notification';

    /**
     * Get the mail representation of the notification.
     *
     * @param              $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $user     = $this->model->userEntity;
        $yourName = $user instanceof UserEntity ? $user->name : null;
        $poll     = $this->model->poll;
        $title    = $poll->toTitle();

        $intro = $this->localize('poll::phrase.poll_message_notification', [
            'user'  => $yourName,
            'title' => $title,
        ]);

        if ($poll->isIntegration()) {
            $intro = $this->getIntegrationThreadMessage($poll, $yourName) ?: $intro;
        }

        $url     = $poll->toUrl();
        $subject = $this->localize('poll::phrase.poll_result_notification_type');

        return (new MailMessage())
            ->locale($this->getLocale())
            ->subject($subject)
            ->line($intro)
            ->action($this->localize('poll::phrase.poll'), $url);
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
        $poll     = $this->model->poll;
        $title    = $poll->toTitle();

        $message = $this->localize('poll::phrase.poll_message_notification', [
            'user'  => $yourName,
            'title' => $title,
        ]);

        if ($poll->isIntegration()) {
            $message = $this->getIntegrationThreadMessage($poll, $yourName) ?: $message;
        }

        return $message;
    }

    public function toUrl(): ?string
    {
        if ($this->model->poll instanceof Poll) {
            return $this->model->poll->toUrl();
        }

        return null;
    }

    public function toLink(): ?string
    {
        if ($this->model->poll instanceof Poll) {
            return $this->model->poll->toLink();
        }

        return null;
    }

    public function toRouter(): ?string
    {
        if ($this->model->poll instanceof Poll) {
            return $this->model->poll->toRouter();
        }

        return null;
    }

    protected function getIntegrationThreadMessage(Poll $poll, string $yourName): ?string
    {
        $item = app('events')->dispatch('poll.integration.get_parent', [$poll], true);

        if (!$item instanceof EloquentModel) {
            return null;
        }

        if (!$item instanceof HasTitle) {
            return null;
        }

        return $this->localize('poll::phrase.poll_voted_in_thread_notification', [
            'user'  => $yourName,
            'title' => $item->toTitle(),
        ]);
    }
}
