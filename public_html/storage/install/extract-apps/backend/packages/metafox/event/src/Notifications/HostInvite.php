<?php

namespace MetaFox\Event\Notifications;

use MetaFox\Event\Models\Event;
use MetaFox\Event\Models\HostInvite as Model;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;
use MetaFox\User\Models\UserEntity;

/**
 * Class Invite.
 *
 * @property Model $model
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class HostInvite extends Notification
{
    protected string $type = 'event_host_invite';

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
        $event      = $this->model->event;

        $userFullName = $userEntity instanceof UserEntity ? $userEntity->name : null;

        $subject = $this->localize('event::mail.event_host_invite_subject', [
            'user' => $userFullName,
        ]);

        $text = $this->localize('event::mail.event_host_invite_text', [
            'user' => $userFullName,
        ]);

        $url = '';

        if ($event instanceof Event) {
            $url = $event->toUrl() ?? '';
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
        $event        = $this->model->event;
        $userFullName = null;
        $eventTitle   = null;

        if ($userEntity instanceof UserEntity) {
            $userFullName = $userEntity->name;
        }

        if ($event instanceof Event) {
            $eventTitle = $event->toTitle();
        }

        return $this->localize('event::notification.user_full_name_invited_you_to_the_event_host_title', [
            'user_full_name' => $userFullName,
            'title'          => $eventTitle,
        ]);
    }
}
