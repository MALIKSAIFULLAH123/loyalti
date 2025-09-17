<?php

namespace MetaFox\EMoney\Notifications;

use MetaFox\EMoney\Models\Transaction;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

/**
 * @property Transaction $model
 */
class ApprovedTransactionNotification extends Notification
{
    protected string $type = 'ewallet_approved_transaction_notification';

    public function toArray(IsNotifiable $notifiable): array
    {
        return [
            'data'      => $this->model->toArray(),
            'item_id'   => $this->model->entityId(),
            'item_type' => $this->model->entityType(),
            'user_id'   => $this->model->ownerId(),
            'user_type' => $this->model->ownerType(),
        ];
    }

    public function callbackMessage(): ?string
    {
        return $this->localize('ewallet::phrase.your_balance_transaction_has_been_approved');
    }

    public function toMail(IsNotifiable $notifiable): MailMessage
    {
        $intro = $this->localize('ewallet::phrase.your_balance_transaction_has_been_approved');

        $url   = $this->model->toUrl();

        return (new MailMessage())
            ->subject($intro)
            ->line($intro)
            ->action($this->localize('core::phrase.view_now'), $url);
    }
}
