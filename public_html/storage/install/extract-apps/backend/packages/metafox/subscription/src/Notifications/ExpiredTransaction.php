<?php

namespace MetaFox\Subscription\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

class ExpiredTransaction extends Notification
{
    protected string $type = 'subscription_expired_transaction';

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
        if (null === $this->model) {
            return null;
        }

        return $this->localize('subscription::phrase.your_subscription_has_been_expired');
    }

    public function toMail(): ?MailMessage
    {
        if (null === $this->model) {
            return null;
        }

        $service = new MailMessage();

        $subject = $this->localize('subscription::phrase.your_subscription_has_been_expired');

        $message = $this->localize('subscription::phrase.your_subscription_has_been_expired');

        return $service
            ->locale($this->getLocale())
            ->subject($subject)
            ->line($message)
            ->action($this->localize('core::phrase.view_now'), $this->toUrl());
    }

    public function toUrl(): ?string
    {
        if (null === $this->model) {
            return null;
        }

        return $this->model->toUrl();
    }

    public function toLink(): ?string
    {
        if (null === $this->model) {
            return null;
        }

        return $this->model->toLink();
    }
}
