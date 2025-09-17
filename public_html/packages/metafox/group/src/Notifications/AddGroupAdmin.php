<?php

namespace MetaFox\Group\Notifications;

use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\Invite;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;
use MetaFox\User\Models\UserEntity;

class AddGroupAdmin extends Notification
{
    protected string $type = 'add_group_admin';

    /**
     * @inheritDoc
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

    public function toMail(IsNotifiable $notifiable): MailMessage
    {
        $service      = new MailMessage();
        $userEntity   = $this->model->userEntity;
        $group        = $this->model->group;
        $userFullName = $userEntity instanceof UserEntity ? $userEntity->name : null;

        $subject = $this->localize('group::phrase.group_add_admin_notification_type');

        $text = $this->localize('group::mail.user_full_name_invited_you_to_became_an_admin_for_the_group_title', [
            'user_full_name' => $userFullName,
            'title'          => $group->toTitle(),
        ]);

        $url = $group?->toUrl() ?? '';

        return $service
            ->locale($this->getLocale())
            ->subject($subject)
            ->line($text)
            ->action($this->localize('core::phrase.view_now'), $url);
    }

    public function callbackMessage(): ?string
    {
        $model = $this->model;
        if (!$model instanceof Invite) {
            return null;
        }

        $userEntity   = $this->model->userEntity;
        $userFullName = $userEntity->name;

        return $this->localize('group::notification.user_full_name_invited_you_to_became_an_admin_for_the_group_title', [
            'user_full_name' => $userFullName,
            'title'          => $model->group->toTitle(),
        ]);
    }
}
