<?php

namespace MetaFox\Group\Notifications;

use MetaFox\Group\Models\Group;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

/**
 * Class PendingRequestNotification.
 * @ignore
 */
class PendingRequestNotification extends Notification
{
    protected string $type = 'group_pending_request';

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

    public function toMail(): ?MailMessage
    {
        $group = $this->model->group;

        if ($group instanceof Group) {
            $service = new MailMessage();

            $url = $group->toPendingRequestTabUrl();

            $subject = $this->localize('group::mail.group_joining_pending_request_subject');

            $message = $this->localize('group::mail.group_joining_pending_request_content', [
                'title' => $group->toTitle(),
            ]);

            return $service
                ->locale($this->getLocale())
                ->subject($subject)
                ->line($message)
                ->action($this->localize('core::phrase.view_now'), $url);
        }

        return null;
    }

    public function callbackMessage(): ?string
    {
        if (null === $this->model) {
            return null;
        }
        $group        = $this->model->group;
        $userEntity   = $this->model->userEntity;
        $userFullName = $userEntity->name;

        if (!$group instanceof Group) {
            return null;
        }

        return match ($group->isPublicPrivacy()) {
            true => $this->localize(
                'group::notification.your_group_title_received_a_joining_pending_request',
                [
                    'title' => $group->toTitle(),
                ]
            ),
            default => $this->localize('group::notification.user_name_requested_to_join_group_title', [
                'user_full_name' => $userFullName,
                'title'          => $group->toTitle(),
            ]),
        };
    }

    public function toLink(): ?string
    {
        if (null === $this->model) {
            return null;
        }

        $group = $this->model->group;

        if (!$group instanceof Group) {
            return null;
        }

        if ($group->isPublicPrivacy()) {
            return $group->toAllMembersTabLink();
        }

        return $group->toPendingRequestTabLink();
    }
    public function toUrl(): ?string
    {
        if (null === $this->model) {
            return null;
        }

        $group = $this->model->group;

        if (!$group instanceof Group) {
            return null;
        }

        if ($group->isPublicPrivacy()) {
            return $group->toAllMembersTabUrl();
        }

        return $group->toPendingRequestTabUrl();
    }

    public function toRouter(): ?string
    {
        if (null === $this->model) {
            return null;
        }

        $group = $this->model->group;

        if (!$group instanceof Group) {
            return null;
        }

        if ($group->isPublicPrivacy()) {
            return $group->toRouter();
        }

        return $group->toPendingRequestTabRoute();
    }
}
