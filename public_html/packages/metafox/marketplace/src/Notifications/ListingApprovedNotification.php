<?php

namespace MetaFox\Marketplace\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Marketplace\Models\Listing as Model;
use MetaFox\Platform\Notifications\ApproveNotification;

/**
 * stub: packages/notifications/notification.stub.
 */

/**
 * Class ListingApprovedNotification.
 * @property Model $model
 * @ignore
 */
class ListingApprovedNotification extends ApproveNotification
{
    protected string $type = 'listing_approved_notification';

    /**
     * Get the mail representation of the notification.
     *
     * @param $notifiable
     * @return MailMessage|null
     */
    public function toMail($notifiable): ?MailMessage
    {
        if (null === $this->model) {
            return null;
        }

        $intro = $this->localize('marketplace::phrase.listing_approved_successfully_notification');

        $url = $this->model->toUrl();

        $message = new MailMessage();

        if (null !== $url) {
            $message->action($this->localize('core::phrase.view_now'), $url);
        }

        return $message
            ->locale($this->getLocale())
            ->line($intro);
    }

    public function callbackMessage(): ?string
    {
        return $this->localize('marketplace::phrase.listing_approved_successfully_notification');
    }
}
