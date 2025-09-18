<?php

namespace MetaFox\Subscription\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

class ManualSubscriptionCancellation extends Notification
{
    protected string $type = 'subscription_manual_cancellation';

    /**
     * @var bool
     */
    protected $isYourself = true;

    /**
     * @param  bool $value
     * @return void
     */
    public function setIsYourself(bool $value): void
    {
        $this->isYourself = $value;
    }

    /**
     * @return bool
     */
    public function getIsYourself(): bool
    {
        return $this->isYourself;
    }

    public function toArray(IsNotifiable $notifiable): array
    {
        $invoice = $this->model->invoice;

        $this->model->is_yourself = $this->isYourself;

        return [
            'data'      => $this->model->toArray(),
            'item_id'   => $this->model->entityId(),
            'item_type' => $this->model->entityType(),
            'user_id'   => $invoice->userId(),
            'user_type' => $invoice->userType(),
        ];
    }

    public function callbackMessage(): ?string
    {
        $isYourself = Arr::get($this->data, 'is_yourself');

        if ($isYourself) {
            return $this->localize('subscription::phrase.your_subscription_has_been_cancelled');
        }

        return $this->localize('subscription::phrase.your_subscription_has_been_cancelled_by_admin');
    }

    public function toMail(): ?MailMessage
    {
        $service = new MailMessage();

        switch ($this->isYourself) {
            case true:
                $subject = $this->localize('subscription::phrase.your_subscription_has_been_cancelled');
                $content = $this->localize('subscription::phrase.your_subscription_has_been_cancelled');
                break;
            default:
                $subject = $this->localize('subscription::phrase.your_subscription_has_been_cancelled_by_admin');
                $content = $this->localize('subscription::phrase.your_subscription_has_been_cancelled_by_admin');
                break;
        }

        return $service
            ->locale($this->getLocale())
            ->subject($subject)
            ->line($content)
            ->action($this->localize('core::phrase.view_now'), $this->toUrl());
    }

    public function toUrl(): ?string
    {
        if (null === $this->model) {
            return null;
        }

        $invoice = $this->model->invoice;

        if (null === $invoice) {
            return null;
        }

        return $invoice->toUrl();
    }

    public function toLink(): ?string
    {
        if (null === $this->model) {
            return null;
        }

        $invoice = $this->model->invoice;

        if (null === $invoice) {
            return null;
        }

        return $invoice->toLink();
    }
}
