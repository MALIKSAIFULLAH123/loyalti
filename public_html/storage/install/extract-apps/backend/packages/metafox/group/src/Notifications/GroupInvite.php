<?php

namespace MetaFox\Group\Notifications;

use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\Invite as Model;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;
use MetaFox\User\Models\UserEntity;

/**
 * Class GroupInvite.
 *
 * @property Model $model
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 */
class GroupInvite extends Notification
{
    protected string $type = 'group_invite';

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
        $group      = $this->model->group;

        $userFullName = $userEntity instanceof UserEntity ? $userEntity->name : null;

        $subject = $this->localize('group::mail.group_invite_subject', [
            'user' => $userFullName,
        ]);

        $text = $this->localize('group::mail.group_invite_text', [
            'user' => $userFullName,
        ]);

        $url = '';

        if ($group instanceof Group) {
            $url = $group->toUrl() ?? '';
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
        $userEntity   = $this->model->userEntity;
        $group        = $this->model->group;
        $userFullName = null;
        $groupTitle   = null;

        if ($userEntity instanceof UserEntity) {
            $userFullName = $userEntity->name;
        }

        if ($group instanceof Group) {
            $groupTitle = $group->toTitle();
        }

        return $this->localize('group::notification.user_full_name_invited_you_to_the_group_title', [
            'user_full_name' => $userFullName,
            'title'          => $groupTitle,
        ]);
    }
}
