<?php

namespace MetaFox\Saved\Notifications;

use MetaFox\Saved\Models\SavedListMember as Model;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;
use MetaFox\Saved\Models\SavedList;
use MetaFox\User\Models\UserEntity;

/**
 * Class AddFriendToListNotification.
 *
 * @property Model $model
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class AddFriendToListNotification extends Notification
{
    protected string $type = 'saved_add_friend_to_list';

    /**
     * Get the mail representation of the notification.
     *
     * @param IsNotifiable $notifiable
     *
     * @return MailMessage
     */
    public function toMail(IsNotifiable $notifiable): ?MailMessage
    {
        $service = new MailMessage();

        $userEntity = $this->model?->collection?->userEntity;
        $collection = $this->model?->collection;

        $userFullName = $userEntity instanceof UserEntity ? $userEntity->name : null;

        $subject = $this->localize('saved::mail.saved_add_to_collection_subject', [
            'user' => $userFullName,
        ]);

        $text = $this->localize('saved::mail.saved_add_to_collection_text', [
            'user' => $userFullName,
        ]);

        $url = '';
        if ($collection instanceof SavedList) {
            $url = $collection->toUrl() ?? '';
        }

        return $service
            ->locale($this->getLocale())
            ->subject($subject)
            ->line($text)
            ->action($this->localize('core::phrase.view_now'), $url);
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
        $userEntity = $this->model?->collection?->userEntity;

        $collection = $this->model?->collection;

        $userFullName = null;

        $savedListTitle = null;

        if ($userEntity instanceof UserEntity) {
            $userFullName = $userEntity->name;
        }

        if ($collection instanceof SavedList) {
            $savedListTitle = $collection->name;
        }

        return $this->localize('saved::notification.user_full_name_invited_you_to_the_collection_title', [
            'user_full_name' => $userFullName,
            'title'          => $savedListTitle,
        ]);
    }

    public function toUrl(): ?string
    {
        return $this->model?->collection?->toUrl() ?? null;
    }

    public function toLink(): ?string
    {
        return $this->model?->collection?->toLink() ?? null;
    }

    public function toRouter(): ?string
    {
        return $this->model?->collection?->toRouter() ?? null;
    }
}
