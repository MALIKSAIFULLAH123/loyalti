<?php

namespace MetaFox\Marketplace\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

class OwnerPaymentSuccessNotification extends Notification
{
    protected string $type = 'owner_payment_success_notification';

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

        if (null === $invoice->listing) {
            return $this->localize('marketplace::phrase.owner_payment_success_message_without_title', [
                'full_name' => $payer->toTitle(),
            ]);
        }

        return $this->localize('marketplace::phrase.owner_payment_success_message', [
            'title'     => $invoice->listing->toTitle(),
            'full_name' => $payer->toTitle(),
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

        if (null === $invoice->listing) {
            return null;
        }

        $subject = $this->localize('marketplace::phrase.owner_payment_success_email_subject');

        $message = $this->localize('marketplace::phrase.owner_payment_success_email_message', [
            'title'     => $invoice->listing->toTitle(),
            'full_name' => $payer->toTitle(),
        ]);

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

        if (null === $this->model->invoice) {
            return null;
        }

        if (null === $this->model->invoice->listing) {
            return null;
        }

        return $this->model->invoice->listing->toUrl();
    }

    public function toLink(): ?string
    {
        if (null === $this->model) {
            return null;
        }

        if (null === $this->model->invoice) {
            return null;
        }

        if (null === $this->model->invoice->listing) {
            return null;
        }

        return $this->model->invoice->listing->toLink();
    }

    public function toRouter(): ?string
    {
        if (null === $this->model) {
            return null;
        }

        if (null === $this->model->invoice) {
            return null;
        }

        if (null === $this->model->invoice->listing) {
            return null;
        }

        return $this->model->invoice->listing->toRouter();
    }
}
