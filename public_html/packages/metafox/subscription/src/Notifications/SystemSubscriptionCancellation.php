<?php

namespace MetaFox\Subscription\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

class SystemSubscriptionCancellation extends Notification
{
    protected string $type = 'subscription_system_cancellation';

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
        $gateway = $this->model->gateway;

        if (null === $gateway) {
            return $this->localize('your_subscription_has_been_cancelled_from_payment_gateway_the_subscription_will_be_continued_until_expiration');
        }

        return $this->localize('subscription::phrase.your_subscription_has_been_cancelled_from_gateway_the_subscription_will_be_continued_until_expiration', [
            'gateway' => $gateway->title,
        ]);
    }

    public function toMail(): ?MailMessage
    {
        $service = new MailMessage();

        $gateway = $this->model->gateway;

        $subject = $this->localize('subscription::phrase.your_subscription_has_been_cancelled_from_payment_gateway');

        $content = $this->localize('subscription::phrase.your_subscription_has_been_cancelled_from_gateway_the_subscription_will_be_continued_until_expiration', [
            'gateway' => $gateway->title,
        ]);

        return $service
            ->locale($this->getLocale())
            ->subject($subject)
            ->line($content)
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
