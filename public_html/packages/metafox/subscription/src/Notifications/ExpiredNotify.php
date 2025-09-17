<?php

namespace MetaFox\Subscription\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

class ExpiredNotify extends Notification
{
    protected string $type = 'subscription_expired_notify';

    /**
     * @var int
     */
    protected $days;

    /**
     * @param  int  $days
     * @return void
     */
    public function setDays(int $days): void
    {
        $this->days = $days;
    }

    /**
     * @return int
     */
    public function getDays(): ?int
    {
        return $this->days;
    }

    public function toArray(IsNotifiable $notifiable): array
    {
        $data = array_merge($this->model->toArray(), [
            'remain_days' => $this->getDays(),
        ]);

        return [
            'data'      => $data,
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

        return $this->getMessage();
    }

    public function toMail(): ?MailMessage
    {
        if (null === $this->model) {
            return null;
        }

        $service = new MailMessage();

        $subject = $this->getMessage();

        $message = $this->getMessage();

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

        return $this->model->toUrl();
    }

    public function toLink(): ?string
    {
        if (null === $this->model) {
            return null;
        }

        return $this->model->toLink();
    }

    protected function getMessage(): string
    {
        $days = $this->getDays();

        if (null === $days) {
            $days = Arr::get($this->data, 'remain_days');
        }

        if ($days == 1) {
            return $this->localize('subscription::phrase.your_subscription_will_be_expired_tomorrow');
        }

        return $this->localize('subscription::phrase.your_subscription_has_number_day_remaining_to_expire', [
            'number' => $days,
        ]);
    }
}
