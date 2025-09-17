<?php

namespace MetaFox\Event\Notifications;

use MetaFox\Event\Models\Event;
use MetaFox\Event\Models\Member as Model;
use MetaFox\Event\Support\Facades\EventMembership;
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
class EventRsvpNotification extends Notification
{
    protected string $type = 'event_member';

    /**
     * Get the mail representation of the notification.
     *
     * @param IsNotifiable $notifiable
     *
     * @return MailMessage|null
     */
    public function toMail(IsNotifiable $notifiable): ?MailMessage
    {
        $service      = new MailMessage();
        $userEntity   = $this->model->userEntity;
        $event        = $this->model->event;
        $userFullName = $eventTitle = null;

        if ($userEntity instanceof UserEntity) {
            $userFullName = $userEntity->name;
        }

        if ($event instanceof Event) {
            $eventTitle = $event->toTitle();
        }

        $memberShip = EventMembership::parseRsvp();
        $actionType = $memberShip[$this->model->rsvp_id];

        $subject = $this->localize('event::mail.event_rsvp_subject', [
            'user_name'   => $userFullName,
            'action_type' => $actionType,
            'title'       => $eventTitle,
        ]);

        $text = $this->localize('event::mail.event_rsvp_text', [
            'user_name'   => $userFullName,
            'action_type' => $actionType,
            'title'       => $eventTitle,
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
        $userEntity = $this->model->userEntity;

        $event = $this->model->event;

        $userFullName = null;

        $eventTitle = null;

        if ($userEntity instanceof UserEntity) {
            $userFullName = $userEntity->name;
        }

        if ($event instanceof Event) {
            $eventTitle = $event->toTitle();
        }

        $memberShip = EventMembership::parseRsvp();

        $actionType = $memberShip[$this->model->rsvp_id];

        return $this->localize('event::notification.event_rsvp_notification', [
            'user_name'   => $userFullName,
            'action_type' => $actionType,
            'title'       => $eventTitle,
        ]);
    }

    public function toLink(): ?string
    {
        $event = $this->model->event;

        return $event?->toLink();
    }

    public function toUrl(): ?string
    {
        $event = $this->model->event;

        return $event?->toUrl();
    }

    public function toRouter(): ?string
    {
        $event = $this->model->event;

        return $event?->toRouter();
    }
}
