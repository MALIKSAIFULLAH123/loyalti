<?php

namespace MetaFox\Mfa\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

class BruteForceMfaNotification extends Notification
{
    protected string $type = 'brute_force_notification';

    public function toArray(IsNotifiable $notifiable): array
    {
        return [
            'data'      => $this->model->toArray(),
            'item_id'   => $this->model->entityId(),
            'item_type' => $this->model->entityType(),
            'user_id'   => $this->model->entityId(),
            'user_type' => $this->model->entityType(),
        ];
    }

    public function callbackMessage(): ?string
    {
        if (null === $this->model) {
            return null;
        }

        return $this->localize('mfa::phrase.mfa_attempts_warning_message');
    }

    public function toMail(IsNotifiable $notifiable): ?MailMessage
    {
        $service = new MailMessage();

        $subject = $this->localize('mfa::phrase.mfa_attempts_warning_email_subject');
        $message = $this->localize('mfa::phrase.mfa_attempts_warning_message');

        return $service
            ->locale($this->getLocale())
            ->subject($subject)
            ->line($message);
    }

    public function toUrl(): ?string
    {
        return null;
    }

    public function toLink(): ?string
    {
        return null;
    }

    public function toRouter(): ?string
    {
        return null;
    }
}
