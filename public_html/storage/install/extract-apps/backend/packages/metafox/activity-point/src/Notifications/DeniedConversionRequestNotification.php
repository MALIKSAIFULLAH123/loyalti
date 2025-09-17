<?php

namespace MetaFox\ActivityPoint\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Notifications\ApproveNotification;
use MetaFox\ActivityPoint\Models\ConversionRequest;

/**
 * @property ConversionRequest $model
 */
class DeniedConversionRequestNotification extends ApproveNotification
{
    protected string $type = 'activitypoint_denied_conversion_request_notification';

    public function callbackMessage(): ?string
    {
        return $this->getMessage();
    }

    public function toMail(): ?MailMessage
    {
        $intro = $this->getMessage();

        $url   = $this->toUrl();

        return (new MailMessage())->line($intro)
            ->locale($this->getLocale())
            ->subject($this->localize('activitypoint::phrase.denied_point_conversion_request'))
            ->action($this->localize('core::phrase.view_now'), $url);
    }

    private function getMessage(): string
    {
        return $this->localize('activitypoint::phrase.your_point_conversion_request_have_been_denied_by_admin');
    }
}
