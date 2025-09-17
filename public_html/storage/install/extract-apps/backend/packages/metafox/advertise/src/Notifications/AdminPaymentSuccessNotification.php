<?php

namespace MetaFox\Advertise\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Notifications\ApproveNotification;

class AdminPaymentSuccessNotification extends ApproveNotification
{
    protected string $type = 'advertise_payment_success_ad_notification';

    /**
     * Get the mail representation of the notification.
     *
     * @param              $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $intro = $this->localize('advertise::phrase.an_admin_has_paid_your_ad_title', [
            'title' => $this->getTitle(),
        ]);

        $url   = $this->model?->toUrl() ?? MetaFoxConstant::EMPTY_STRING;

        return (new MailMessage())
            ->subject($this->localize('advertise::phrase.pay_ad_successfully_notification'))
            ->line($intro)
            ->action($this->localize('core::phrase.view_now'), $url);
    }

    public function callbackMessage(): ?string
    {
        return $this->localize('advertise::phrase.an_admin_has_paid_your_ad_title', [
            'title' => $this->getTitle(),
        ]);
    }

    protected function getTitle(): string
    {
        return $this->model?->toTitle() ?? MetaFoxConstant::EMPTY_STRING;
    }
}
