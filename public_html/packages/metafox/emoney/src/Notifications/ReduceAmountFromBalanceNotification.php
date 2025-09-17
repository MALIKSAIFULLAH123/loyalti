<?php
namespace MetaFox\EMoney\Notifications;

use MetaFox\EMoney\Models\BalanceAdjustment;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

/**
 * @property BalanceAdjustment $model
 */
class ReduceAmountFromBalanceNotification extends Notification
{
    protected string $type = 'ewallet_reduce_amount_from_balance';

    public function toArray(IsNotifiable $notifiable): array
    {
        return [
            'data'      => $this->model->toArray(),
            'item_id'   => $this->model->entityId(),
            'item_type' => $this->model->entityType(),
            'user_id'   => $this->model->user_id,
            'user_type' => $this->model->user_type,
        ];
    }

    public function callbackMessage(): ?string
    {
        return __p('ewallet::notification.admin_reduced_number_from_your_ewallet', [
            'number' => app('currency')->getPriceFormatByCurrencyId($this->model->currency, $this->model->amount),
        ]);
    }

    public function toMail(IsNotifiable $notifiable): ?MailMessage
    {
        return (new MailMessage())
            ->subject($this->localize('ewallet::notification.balance_adjustment'))
            ->line($this->localize('ewallet::notification.admin_reduced_number_from_your_ewallet', [
                'number' => app('currency')->getPriceFormatByCurrencyId($this->model->currency, $this->model->amount),
            ]))
            ->action($this->localize('core::phrase.view_now'), $this->toUrl());
    }
}
