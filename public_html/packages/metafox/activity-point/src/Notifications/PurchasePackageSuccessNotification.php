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
class PurchasePackageSuccessNotification extends Notification
{
    protected string $type = 'purchase_package_success';

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

        $emailLine = $this->localize('activitypoint::mail.purchase_package_success', ['amount' => $this->model->points]);
        $url       = url_utility()->makeApiFullUrl('/activitypoint/transactions-history');

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

        return $this->localize('activitypoint::mail.purchase_package_success', ['amount' => $points]);
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
        return url_utility()->makeApiMobileUrl('/activitypoint/transactions-history');
    }
}
