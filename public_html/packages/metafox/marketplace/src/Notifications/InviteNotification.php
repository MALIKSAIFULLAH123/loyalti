<?php

namespace MetaFox\Marketplace\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

class InviteNotification extends Notification
{
    protected string $type = 'invite_notification';

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

        if (null === $this->model->listing) {
            return null;
        }

        return $this->localize('marketplace::phrase.listing_invite_message', [
            'full_name' => $this->model->user->toTitle(),
            'title'     => $this->model->listing->toTitle(),
        ]);
    }

    public function toMail(IsNotifiable $notifiable): ?MailMessage
    {
        $service = new MailMessage();

        $listing      = $this->model->listing;

        $user = $this->model->user;

        if (null === $listing) {
            return null;
        }

        if (null === $user) {
            return null;
        }

        $subject = $this->localize('marketplace::phrase.listing_invite_email_subject');

        $message = $this->localize('marketplace::phrase.listing_invite_email_message', [
            'full_name' => $user->toTitle(),
            'title'     => $listing->toTitle(),
        ]);

        return $service
            ->locale($this->getLocale())
            ->subject($subject)
            ->line($message)
            ->action($this->localize('core::phrase.view_now'), $listing->toUrl());
    }

    public function toUrl(): ?string
    {
        if (null === $this->model) {
            return null;
        }

        if (null === $this->model->listing) {
            return null;
        }

        return $this->model->listing->toUrl();
    }

    public function toLink(): ?string
    {
        if (null === $this->model) {
            return null;
        }

        if (null === $this->model->listing) {
            return null;
        }

        return $this->model->listing->toLink();
    }

    public function toRouter(): ?string
    {
        if (null === $this->model) {
            return null;
        }

        if (null === $this->model->listing) {
            return null;
        }

        return $this->model->listing->toRouter();
    }
}
