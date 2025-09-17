<?php

namespace MetaFox\Advertise\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Advertise\Models\Advertise as Model;
use MetaFox\Platform\Notifications\ApproveNotification;

/**
 * stub: packages/notifications/notification.stub.
 */

/**
 * Class AdvertiseDeniedNotification.
 * @property Model $model
 * @ignore
 */
class AdvertiseDeniedNotification extends ApproveNotification
{
    protected string $type = 'advertise_denied_notification';

    /**
     * Get the mail representation of the notification.
     *
     * @param              $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $intro = $this->localize('advertise::phrase.advertise_denied_successfully_notification');

        $url   = $this->model->toUrl();

        return (new MailMessage())->line($intro)
            ->subject($this->localize('advertise::phrase.deny_ad_notification'))
            ->action($this->localize('core::phrase.view_now'), $url);
    }

    public function callbackMessage(): ?string
    {
        return $this->localize('advertise::phrase.advertise_denied_successfully_notification');
    }
}
