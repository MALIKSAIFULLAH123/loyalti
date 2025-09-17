<?php

namespace MetaFox\EMoney\Notifications;

use MetaFox\EMoney\Models\WithdrawRequest;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

/**
 * @property WithdrawRequest $model
 */
class PendingWithdrawRequestNotification extends Notification
{
    protected string $type = 'ewallet_pending_withdraw_request';
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
        $fullName = $this->model->user?->toTitle();

        if (null === $fullName) {
            return __p('ewallet::phrase.someone_created_withdrawal_request');
        }

        return __p('ewallet::phrase.pending_withdraw_request_callback_message', [
            'full_name' => $fullName,
        ]);
    }

    public function toMail(): ?MailMessage
    {
        $intro = $this->getMessage();

        $url   = $this->toUrl();

        return (new MailMessage())->line($intro)
            ->subject(__p('ewallet::phrase.pending_withdrawal_request'))
            ->action($this->localize('core::phrase.view_now'), $url);
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl('admincp/ewallet/request/browse?id=' . $this->model->entityId());
    }

    public function toLink(): ?string
    {
        return url_utility()->makeApiUrl('admincp/ewallet/request/browse?id=' . $this->model->entityId());
    }

    public function toRouter(): ?string
    {
        return $this->toUrl();
    }
}
