<?php

namespace MetaFox\Group\Notifications;

use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\Request as Model;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

/**
 * Class AcceptRequestNotification.
 * @property Model $model
 * @ignore
 */
class AcceptRequestNotification extends Notification
{
    protected string $type = 'accept_request_member';

    /**
     * @inheritDoc
     */
    public function toArray(IsNotifiable $notifiable): array
    {
        return [
            'data'      => $this->model->toArray(),
            'item_id'   => $this->model->entityId(),
            'item_type' => $this->model->entityType(),
            'user_id'   => $this->user?->entityId() ?? $this->model->reviewerId(),
            'user_type' => $this->user?->entityType() ?? $this->model->reviewerType(),
        ];
    }

    public function toMail(IsNotifiable $notifiable): MailMessage
    {
        $service = new MailMessage();

        $group = $this?->model?->group;

        $subject = $this->localize('group::phrase.group_accept_request_member_notification_type');

        $text = $this->localize('group::mail.welcome_to_group_title', [
            'title' => $group?->toTitle(),
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
        return $this->localize('group::notification.welcome_to_group_title');
    }

    public function toLink(): ?string
    {
        if (null === $this->model) {
            return null;
        }

        $group = $this->model->group;

        return $group instanceof Group ? $group->toLink() : null;
    }

    public function toUrl(): ?string
    {
        if (null === $this->model) {
            return null;
        }

        $group = $this->model->group;

        return $group instanceof Group ? $group->toUrl() : null;
    }

    public function toRouter(): ?string
    {
        $group = $this->model?->group;

        return $group instanceof Group ? $group->toRouter() : null;
    }
}
