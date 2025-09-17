<?php
namespace MetaFox\ActivityPoint\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Notifications\ApproveNotification;

class ApprovedConversionRequestNotification extends ApproveNotification
{
    protected string $type = 'activitypoint_approved_conversion_request_notification';

    public function callbackMessage(): ?string
    {
        return $this->getMessage();
    }

    public function toMail(): ?MailMessage
    {
        $intro = $this->getMessage();

        $url   = $this->toUrl();

        return (new MailMessage())
            ->line($intro)
            ->locale($this->getLocale())
            ->subject($this->localize('activitypoint::phrase.approved_point_conversion_request'))
            ->action($this->localize('core::phrase.view_now'), $url);
    }

    private function getMessage(): string
    {
        return $this->localize('activitypoint::phrase.your_point_conversion_request_have_been_approved_by_admin');
    }
}
