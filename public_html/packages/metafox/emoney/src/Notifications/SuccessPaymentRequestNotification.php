<?php

namespace MetaFox\EMoney\Notifications;

use MetaFox\EMoney\Models\WithdrawRequest;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

/**
 * @property WithdrawRequest $model
 */
class SuccessPaymentRequestNotification extends Notification
{
    protected string $type = 'ewallet_success_payment_withdraw_request';

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
        return $this->getMessage();
    }

    private function getMessage(): string
    {
        return __p('ewallet::phrase.your_withdrawal_request_has_been_processed');
    }

    public function toMail(): ?MailMessage
    {
        $intro = $this->getMessage();

        $url   = $this->toUrl();

        return (new MailMessage())->line($intro)
            ->subject($intro)
            ->action($this->localize('core::phrase.view_now'), $url);
    }
}
