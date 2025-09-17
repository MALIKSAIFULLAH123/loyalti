<?php

namespace MetaFox\Marketplace\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

class PaymentSuccessNotification extends Notification
{
    protected string $type = 'payment_success_notification';

    public function toArray(IsNotifiable $notifiable): array
    {
        $userId = $userType = null;

        if (null !== $this->model->invoice) {
            $userId = $this->model->invoice->userId();

            $userType = $this->model->invoice->userType();
        }

        return [
            'data'      => $this->model->toArray(),
            'item_id'   => $this->model->entityId(),
            'item_type' => $this->model->entityType(),
            'user_id'   => $userId,
            'user_type' => $userType,
        ];
    }

    public function callbackMessage(): ?string
    {
        return $this->localize('marketplace::phrase.payment_success_message');
    }

    public function toMail(): ?MailMessage
    {
        $service = new MailMessage();

        $subject = $this->localize('marketplace::phrase.payment_success_email_subject');

        $message = $this->localize('marketplace::phrase.payment_success_email_message');

        $service = $service
            ->locale($this->getLocale())
            ->subject($subject)
            ->line($message);

        if (null === $this->model) {
            return $service;
        }

        if (null === $this->model->invoice) {
            return $service;
        }

        $service->action($this->localize('core::phrase.view_now'), $this->toUrl());

        return $service;
    }

    public function toUrl(): ?string
    {
        if (null === $this->model) {
            return null;
        }

        if (null === $this->model->invoice) {
            return null;
        }

        return $this->model->invoice->toUrl();
    }

    public function toLink(): ?string
    {
        if (null === $this->model) {
            return null;
        }

        if (null === $this->model->invoice) {
            return null;
        }

        return $this->model->invoice->toLink();
    }

    public function toRouter(): ?string
    {
        if (null === $this->model) {
            return null;
        }

        if (null === $this->model->invoice) {
            return null;
        }

        return $this->model->invoice->toRouter();
    }
}
