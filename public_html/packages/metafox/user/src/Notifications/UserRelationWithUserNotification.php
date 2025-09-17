<?php

namespace MetaFox\User\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\ActivityFeedSource;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;
use MetaFox\User\Models\UserEntity;
use MetaFox\User\Models\UserRelationHistory as Model;

/**
 * @property Model $model
 */
class UserRelationWithUserNotification extends Notification
{
    protected string $type = 'user_relation_with_user';

    public function toMail(IsNotifiable $notifiable): MailMessage
    {
        $service    = new MailMessage();
        $userEntity = $this->model->userEntity;

        $creatorName = $userEntity instanceof UserEntity ? $userEntity->name : null;

        $emailSubject = $this->localize('user::mail.user_relation_with_user_notification_type');
        $emailLine    = $this->localize('user::notification.user_name_listed_that_you_two_are_relationship_name', [
            'username'     => $creatorName,
            'relationship' => __p($this->model->relationship?->phrase_var),
        ]);

        $url = $this->model->toUrl();

        return $service
            ->locale($this->getLocale())
            ->subject($emailSubject)
            ->line($emailLine)
            ->action($this->localize('core::phrase.view_now'), $url ?? '');
    }

    /**
     * @param IsNotifiable $notifiable
     *
     * @return array<string, mixed>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray(IsNotifiable $notifiable): array
    {
        $data = $this->model instanceof Model ? $this->model->toArray() : [];

        return [
            'data'      => $data,
            'item_id'   => $this->model->entityId(),
            'item_type' => $this->model->entityType(),
            'user_id'   => $this->model->userId(),
            'user_type' => $this->model->userType(),
        ];
    }

    public function callbackMessage(): ?string
    {
        $name = $this->model->userEntity?->name ?? '';

        return $this->localize('user::notification.user_name_listed_that_you_two_are_relationship_name', [
            'username'     => $name,
            'relationship' => __p($this->model->relationship?->phrase_var),
        ]);
    }

    public function toUrl(): ?string
    {
        if (!$this->model instanceof ActivityFeedSource) {
            return null;
        }

        return $this->model->activity_feed->toUrl();
    }

    public function toLink(): ?string
    {
        if (!$this->model instanceof ActivityFeedSource) {
            return null;
        }

        return $this->model->activity_feed->toLink();
    }

    public function toRouter(): ?string
    {
        if (!$this->model instanceof ActivityFeedSource) {
            return null;
        }

        return $this->model->activity_feed->toRouter();
    }
}
