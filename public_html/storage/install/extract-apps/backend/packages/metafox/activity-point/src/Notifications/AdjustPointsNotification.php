<?php

namespace MetaFox\ActivityPoint\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use Illuminate\Support\Arr;
use MetaFox\ActivityPoint\Support\ActivityPoint;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Notifications\Notification;

/**
 * @property User $model
 */
class AdjustPointsNotification extends Notification
{
    protected string $type = 'adjust_points';

    /**
     * Get the mail representation of the notification.
     *
     * @param  IsNotifiable $notifiable
     * @return MailMessage
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toMail(IsNotifiable $notifiable): MailMessage
    {
        $mailService = new MailMessage();
        $type   = Arr::get($this->data, 'type');
        $points = Arr::get($this->data, 'points');

        $emailLine = match ($type) {
            ActivityPoint::TYPE_RETRIEVED => $this->localize('activitypoint::mail.points_revoked', ['points' => $points]),
            ActivityPoint::TYPE_RECEIVED => $this->localize('activitypoint::mail.points_added', ['points' => $points]),
            default                      => '',
        };

        $url = $this->toUrl();

        return $mailService
            ->locale($this->getLocale())
            ->subject($emailLine)
            ->line($emailLine)
            ->action($this->localize('core::phrase.view_now'), $url);
    }

    /**
     * @param  IsNotifiable         $notifiable
     * @return array<string, mixed>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray(IsNotifiable $notifiable): array
    {
        $extraData = [
            'points' => Arr::get($this->data, 'points'),
            'type'   => Arr::get($this->data, 'type'),
        ];

        return [
            'data'      => array_merge($this->model->toArray(), $extraData),
            'item_id'   => $this->model->entityId(),
            'item_type' => $this->model->entityType(),
            'user_id'   => $this->model->userId(),
            'user_type' => $this->model->userType(),
        ];
    }

    public function callbackMessage(): ?string
    {
        $type   = Arr::get($this->data, 'type');
        $points = Arr::get($this->data, 'points');

        return match ($type) {
            ActivityPoint::TYPE_SENT     => $this->localize('activitypoint::mail.points_revoked', ['points' => $points]), // Backup for old data
            ActivityPoint::TYPE_RETRIEVED => $this->localize('activitypoint::mail.points_revoked', ['points' => $points]),
            ActivityPoint::TYPE_RECEIVED => $this->localize('activitypoint::mail.points_added', ['points' => $points]),
            default                      => '',
        };
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl('/activitypoint/transactions-history');
    }

    public function toLink(): ?string
    {
        return url_utility()->makeApiUrl('/activitypoint/transactions-history');
    }

    public function toRouter(): ?string
    {
        return url_utility()->makeApiMobileUrl('/viewTransactionHistory');
    }
}
