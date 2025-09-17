<?php

namespace MetaFox\Advertise\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

class OwnerPaymentSuccessNotification extends Notification
{
    protected string $type = 'advertise_payment_success_owner_notification';

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
        if (null === $this->model) {
            return null;
        }

        $invoice = $this->model->invoice;

        if (null === $invoice) {
            return null;
        }

        $payer = $invoice->user;

        if (null === $payer) {
            return null;
        }

        if (null === $invoice->item) {
            return $this->localize('advertise::phrase.owner_payment_success_message_without_title', [
                'type' => $invoice->item->entityType(),
            ]);
        }

        return $this->localize('advertise::phrase.owner_payment_success_message', [
            'title' => $invoice->item->toTitle(),
            'type'  => $invoice->item->entityType(),
        ]);
    }

    public function toMail(): ?MailMessage
    {
        $service = new MailMessage();

        if (null === $this->model) {
            return null;
        }

        $invoice = $this->model->invoice;

        if (null === $invoice) {
            return null;
        }

        $payer = $invoice->user;

        if (null === $payer) {
            return null;
        }

        if (null === $invoice->item) {
            return null;
        }

        $subject = $this->localize('advertise::phrase.owner_payment_success_email_subject');

        $message = $this->localize('advertise::phrase.owner_payment_success_email_message', [
            'type'  => $invoice->item->entityType(),
            'title' => $invoice->item->toTitle(),
        ]);

        return $service
            ->locale($this->getLocale())
            ->subject($subject)
            ->line($message)
            ->action($this->localize('core::phrase.view_now'), $this->toUrl());
    }

    public function toUrl(): ?string
    {
        return $this->model?->invoice?->item?->toUrl();
    }

    public function toLink(): ?string
    {
        return $this->model?->invoice?->item?->toLink();
    }

    public function toRouter(): ?string
    {
        return $this->model?->invoice?->item?->toRouter();
    }
}
