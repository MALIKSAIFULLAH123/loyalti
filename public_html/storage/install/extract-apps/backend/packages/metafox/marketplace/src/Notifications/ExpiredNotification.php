<?php

namespace MetaFox\Marketplace\Notifications;

use Carbon\CarbonInterval;
use Illuminate\Support\Arr;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

class ExpiredNotification extends Notification
{
    /**
     * @var string
     */
    protected string $type = 'listing_expired_notification';

    /**
     * @var float
     */
    protected float $expiredDays = 0;

    /**
     * @param  float $days
     * @return $this
     */
    public function setExpiredDays(float $days): self
    {
        $this->expiredDays = $days;

        return $this;
    }

    public function toArray(IsNotifiable $notifiable): array
    {
        return [
            'data' => array_merge($this->model->toArray(), [
                'expired_days' => $this->expiredDays,
                'title'        => $this->model->toTitle(),
            ]),
            'item_id'   => $this->model->entityId(),
            'item_type' => $this->model->entityType(),
            'user_id'   => $this->model->userId(),
            'user_type' => $this->model->userType(),
        ];
    }

    public function callbackMessage(): ?string
    {
        $data        = $this->data;
        $expiredDays = Arr::get($data, 'expired_days', 0);
        $title       = Arr::get($data, 'title', '');
        $parseDay    = CarbonInterval::make($expiredDays . 'd');
        $message     = $this->localize('marketplace::notification.expired_listing_message', [
            'title' => $title,
        ]);

        if ($parseDay) {
            $numberHours = CarbonInterval::make($expiredDays . 'd')->totalHours;
            $numberDays  = CarbonInterval::make($expiredDays . 'd')->totalDays;

            $days = match ($expiredDays < 1) {
                true  => CarbonInterval::hours($numberHours),
                false => CarbonInterval::days($numberDays)
            };

            $message = $this->localize('marketplace::notification.expired_listing_message_in_day', [
                'days'  => $days,
                'title' => $title,
            ]);
        }

        return $message;
    }

    public function toMail(): ?MailMessage
    {
        $service     = new MailMessage();
        $expiredDays = $this->expiredDays;

        if (null === $this->model) {
            return null;
        }

        if (0 == $this->expiredDays) {
            return null;
        }

        $parseDay = CarbonInterval::make($expiredDays . 'd');
        $message  = $subject = $this->localize('marketplace::phrase.expired_email_subject', [
            'title' => $this->model->toTitle(),
        ]);

        if ($parseDay) {
            $numberHours = CarbonInterval::make($expiredDays . 'd')->totalHours;
            $numberDays  = CarbonInterval::make($expiredDays . 'd')->totalDays;
            $days        = match ($expiredDays < 1) {
                true  => CarbonInterval::hours($numberHours),
                false => CarbonInterval::days($numberDays)
            };

            $message = $this->localize('marketplace::phrase.expired_email_message', [
                'title' => $this->model->toTitle(),
                'days'  => $days,
            ]);
        }

        return $service
            ->locale($this->getLocale())
            ->subject($subject)
            ->line($message)
            ->action($this->localize('core::phrase.view_now'), $this->model->toUrl());
    }
}
