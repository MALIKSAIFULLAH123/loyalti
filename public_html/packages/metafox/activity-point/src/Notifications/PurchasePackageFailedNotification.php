<?php

namespace MetaFox\ActivityPoint\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use Illuminate\Support\Arr;
use MetaFox\ActivityPoint\Models\PackagePurchase as Model;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

/**
 * @property Model $model
 */
class PurchasePackageFailedNotification extends Notification
{
    protected string $type = 'purchase_package_fail';

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

        $emailLine = $this->localize('activitypoint::mail.purchase_package_fail', ['amount' => $this->model->points]);
        $url       = url_utility()->makeApiFullUrl('/activitypoint/packages');

        return $mailService
            ->locale($this->getLocale())
            ->subject($emailLine)
            ->line($emailLine)
            ->action($this->localize('activitypoint::phrase.purchase_package_fail'), $url);
    }

    /**
     * @param  IsNotifiable         $notifiable
     * @return array<string, mixed>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
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
        $points = Arr::get($this->data, 'points', 0);

        return $this->localize('activitypoint::mail.purchase_package_fail', ['amount' => $points]);
    }

    public function toUrl(): ?string
    {
        return null;
    }

    public function toLink(): ?string
    {
        return null;
    }

    public function toRouter(): ?string
    {
        return null;
    }
}
