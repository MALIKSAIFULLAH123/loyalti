<?php

namespace MetaFox\Featured\Notifications;

use MetaFox\Featured\Models\Invoice;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

/**
 * @property Invoice $model
 */
class SuccessPaymentNotification extends Notification
{
    /**
     * @var string
     */
    protected string $type = 'featured_payment_success';

    public function toArray(IsNotifiable $notifiable): array
    {
        return [
            'data'      => $this->model->toArray(),
            'item_id'   => $this->model->entityId(),
            'item_type' => $this->model->entityType(),
            'user_id'   => $this->model->user_id,
            'user_type' => $this->model->user_type,
        ];
    }

    public function callbackMessage(): ?string
    {
        return __p('featured::phrase.your_invoice_has_been_paid_successfully');
    }

    public function toMail(IsNotifiable $notifiable): MailMessage
    {
        $intro = __p('featured::phrase.your_invoice_has_been_paid_successfully');

        $url = $this->model->toUrl();

        return (new MailMessage())
            ->locale($this->getLocale())
            ->line($intro)
            ->action($this->localize('core::phrase.view_now'), $url);
    }
}
