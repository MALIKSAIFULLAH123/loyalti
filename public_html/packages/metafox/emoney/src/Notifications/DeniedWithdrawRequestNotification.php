<?php

namespace MetaFox\EMoney\Notifications;

use MetaFox\EMoney\Models\WithdrawRequest;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;
use MetaFox\Platform\Contracts\User;

/**
 * @property WithdrawRequest $model
 */
class DeniedWithdrawRequestNotification extends Notification
{
    protected string $type = 'ewallet_denied_withdraw_request';

    /**
     * @var User|null
     */
    protected ?User $context = null;

    /**
     * @param  User  $user
     * @return $this
     */
    public function setContext(User $user): self
    {
        $this->context = $user;

        return $this;
    }

    public function toArray(IsNotifiable $notifiable): array
    {
        try {
            $user = $this->context;

            if (null === $user) {
                $user = $this->model->user;
            }

            return [
                'data'      => $this->model->toArray(),
                'item_id'   => $this->model->entityId(),
                'item_type' => $this->model->entityType(),
                'user_type' => $user->entityType(),
                'user_id'   => $user->entityId(),
            ];
        } catch (\Throwable $throwable) {
            return [];
        }
    }

    public function callbackMessage(): ?string
    {
        return $this->getMessage();
    }

    public function toMail(): ?MailMessage
    {
        $intro = $this->getMessage();

        $url   = $this->toUrl();

        return (new MailMessage())->line($intro)
            ->subject(__p('ewallet::phrase.denied_withdrawal_request'))
            ->action($this->localize('core::phrase.view_now'), $url);
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl('ewallet/request?id=' . $this->model->entityId());
    }

    public function toLink(): ?string
    {
        return url_utility()->makeApiUrl('ewallet/request?id=' . $this->model->entityId());
    }

    private function getMessage(): string
    {
        return __p('ewallet::phrase.your_withdrawal_request_has_been_denied');
    }
}
