<?php

namespace MetaFox\Subscription\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

class CompletedTransaction extends Notification
{
    protected string $type = 'subscription_completed_transaction';

    /**
     * @var bool
     */
    protected bool $isFirstTransaction = true;

    /**
     * @var bool
     */
    protected bool $isYourself = true;

    /**
     * @return bool
     */
    public function getIsFirstTransaction(): bool
    {
        return $this->isFirstTransaction;
    }

    /**
     * @param  bool $value
     * @return void
     */
    public function setIsFirstTransaction(bool $value): void
    {
        $this->isFirstTransaction = $value;
    }

    /**
     * @return bool
     */
    public function getIsYourself(): bool
    {
        return $this->isYourself;
    }

    /**
     * @param  bool $value
     * @return void
     */
    public function setIsYourself(bool $value): void
    {
        $this->isYourself = $value;
    }

    public function toArray(IsNotifiable $notifiable): array
    {
        $invoice = $this->model->invoice;

        $data = array_merge($this->model->toArray(), [
            'is_first_transaction' => $this->getIsFirstTransaction(),
            'is_yourself'          => $this->getIsYourself(),
        ]);

        return [
            'data'      => $data,
            'item_id'   => $this->model->entityId(),
            'item_type' => $this->model->entityType(),
            'user_id'   => $invoice->userId(),
            'user_type' => $invoice->userType(),
        ];
    }

    public function callbackMessage(): ?string
    {
        $invoice = $this->model->invoice;

        if (null === $invoice) {
            return null;
        }

        return $this->getCompletedMessage();
    }

    public function toMail(): ?MailMessage
    {
        $invoice = $this->model->invoice;

        if (null === $invoice) {
            return null;
        }

        $service = new MailMessage();

        $message = $this->getCompletedMessage();

        return $service
            ->locale($this->getLocale())
            ->subject($message)
            ->line($message)
            ->action($this->localize('core::phrase.view_now'), $this->toUrl());
    }

    public function toUrl(): ?string
    {
        $invoice = $this->model->invoice;

        if (null === $invoice) {
            return null;
        }

        return $invoice->toUrl();
    }

    public function toLink(): ?string
    {
        $invoice = $this->model->invoice;

        if (null === $invoice) {
            return null;
        }

        return $invoice->toLink();
    }

    private function getCompletedMessage(): string
    {
        if (!Arr::get($this->data, 'is_yourself')) {
            return $this->localize('subscription::phrase.your_subscription_has_been_activated_by_an_admin');
        }

        if (Arr::get($this->data, 'is_first_transaction', true)) {
            return $this->localize('subscription::phrase.your_subscription_has_been_activated');
        }

        return $this->localize('subscription::phrase.your_recurring_period_of_subscription_has_been_charged');
    }
}
