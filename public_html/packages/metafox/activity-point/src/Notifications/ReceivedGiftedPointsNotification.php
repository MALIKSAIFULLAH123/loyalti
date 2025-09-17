<?php

namespace MetaFox\ActivityPoint\Notifications;

use Illuminate\Support\Arr;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Notifications\Notification;

/**
 * @property ?User $model
 */
class ReceivedGiftedPointsNotification extends Notification
{
    protected string $type = 'received_gifted_points';

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

        $fullName = $this->model instanceof \MetaFox\User\Models\User ? $this->model->full_name : '';
        $points   = Arr::get($this->data, 'points') ?? 0;

        $emailLine = $this->localize('activitypoint::mail.received_gifted_points_subject', [
            'user'   => $fullName,
            'points' => $points,
        ]);
        $url = url_utility()->makeApiFullUrl('/activitypoint');

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
        $fullName = $this->model instanceof \MetaFox\User\Models\User ? $this->model->full_name : '';

        return [
            'data'      => array_merge($this->data, ['full_name' => $fullName]),
            'item_id'   => $this->model->entityId(),
            'item_type' => $this->model->entityType(),
            'user_id'   => $this->model->userId(),
            'user_type' => $this->model->userType(),
        ];
    }

    public function callbackMessage(): ?string
    {
        $fullName = $this->model instanceof \MetaFox\User\Models\User ? $this->model->full_name : '';
        $points   = Arr::get($this->data, 'points', 0);

        return $this->localize('activitypoint::mail.received_gifted_points', [
            'points' => $points,
            'user'   => $fullName,
        ]);
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl('/activitypoint');
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
