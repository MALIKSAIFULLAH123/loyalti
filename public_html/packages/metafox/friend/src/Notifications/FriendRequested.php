<?php

namespace MetaFox\Friend\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Friend\Models\FriendRequest as Model;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;
use MetaFox\User\Models\UserEntity;

/**
 * Class FriendRequested.
 *
 * @property Model $model
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 */
class FriendRequested extends Notification
{
    protected string $type = 'friend_requested';

    /**
     * Get the mail representation of the notification.
     *
     * @param IsNotifiable $notifiable
     *
     * @return MailMessage
     */
    public function toMail(IsNotifiable $notifiable): MailMessage
    {
        $service = new MailMessage();

        $userEntity = $this->model->userEntity;

        $friendFullName = $userEntity instanceof UserEntity ? $userEntity->name : null;

        $yourName = $notifiable->notificationFullName();

        $url = $this->model->toUrl();

        $emailTitle = $this->localize('friend::phrase.you_have_a_friend_request_from_username_subject', ['username' => $friendFullName]);

        $emailLine = $this->localize('friend::phrase.hi_your_name_you_have_a_friend_request_from_username', [
            'username'  => $friendFullName,
            'your_name' => $yourName,
        ]);

        return $service
            ->locale($this->getLocale())
            ->subject($emailTitle)
            ->line($emailLine)
            ->action($this->localize('friend::phrase.view_friend_request'), $url);
    }

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
        $userEntity = $this->model->userEntity;

        $friendFullName = $userEntity instanceof UserEntity ? $userEntity->name : null;

        return $this->localize('friend::phrase.you_have_a_friend_request_from_username', ['username' => $friendFullName]);
    }
}
