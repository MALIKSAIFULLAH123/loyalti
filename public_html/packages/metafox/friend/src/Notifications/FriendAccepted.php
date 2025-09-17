<?php

namespace MetaFox\Friend\Notifications;

use MetaFox\Friend\Models\FriendRequest;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;
use MetaFox\User\Models\UserEntity;

/**
 * Class FriendAccepted.
 *
 * @property FriendRequest $model
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 */
class FriendAccepted extends Notification
{
    protected string $type = 'friend_accepted';

    public function callbackMessage(): ?string
    {
        $userEntity = $this->model->userEntity;

        $friendFullName = $userEntity instanceof UserEntity ? $userEntity->name : null;

        return $this->localize('friend::phrase.username_accepted_your_friend_request', ['username' => $friendFullName]);
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param IsNotifiable $notifiable
     *
     * @return MailMessage
     */
    public function toMail(IsNotifiable $notifiable): MailMessage
    {
        $service        = new MailMessage();
        $userEntity     = $this->model->userEntity;
        $friendFullName = null;
        $url            = null;

        if ($userEntity instanceof UserEntity) {
            $friendFullName = $userEntity->name;
            $url            = $userEntity->toUrl();
        }

        $emailTitle   = $this->localize('friend::mail.username_accepted_your_friend_request_subject', ['username' => $friendFullName]);
        $emailContent = $this->localize(
            'friend::mail.username_accepted_your_friend_request_content',
            ['username' => $friendFullName, 'url' => $url]
        );

        return $service
            ->locale($this->getLocale())
            ->subject($emailTitle)
            ->line($emailContent);
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

    public function toUrl(): ?string
    {
        $user = $this->model->user;

        return $user->toUrl();
    }

    public function toLink(): ?string
    {
        $user = $this->model->user;

        return $user->toLink();
    }
}
