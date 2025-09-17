<?php

namespace MetaFox\Event\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

/**
 * Class NewEventDiscussion.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class NewEventDiscussion extends Notification
{
    protected string $type = 'new_event_discussion';

    public function toMail(): MailMessage
    {
        $service = new MailMessage();

        $name       = $this->model->user->full_name;
        $eventTitle = $this->model->owner->toTitle();

        $subject = $this->localize('event::mail.event_start_discussion_subject', [
            'user'  => $name,
            'event' => $eventTitle,
        ]);

        $text = $this->localize('event::mail.event_start_discussion_text', [
            'user'  => $name,
            'event' => $eventTitle,
        ]);

        $url = $this->model->toUrl();

        return $service
            ->locale($this->getLocale())
            ->subject($subject)
            ->line($text)
            ->action($this->localize('core::phrase.view_now'), $url);
    }

    /**
     * @param  IsNotifiable         $notifiable
     * @return array<string, mixed>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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
        $name       = $this->model->user->full_name;
        $eventTitle = $this->model->owner->toTitle();

        return $this->localize('event::notification.user_name_posted_in_event_title', [
            'username'    => $name,
            'event_title' => $eventTitle,
        ]);
    }
}
