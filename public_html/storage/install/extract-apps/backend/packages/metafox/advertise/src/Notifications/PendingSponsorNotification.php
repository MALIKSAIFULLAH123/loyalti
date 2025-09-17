<?php

namespace MetaFox\Advertise\Notifications;

use MetaFox\Advertise\Models\Sponsor;
use MetaFox\Advertise\Support\Support;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\ApproveNotification;

/**
 * stub: packages/notifications/notification.stub.
 */

/**
 * Class SponsorApprovedNotification.
 * @property Sponsor $model
 * @ignore
 */
class PendingSponsorNotification extends ApproveNotification
{
    protected string $type = 'advertise_sponsor_pending_notification';

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

    /**
     * Get the mail representation of the notification.
     *
     * @param              $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $intro = match ($this->model->sponsor_type) {
            Support::SPONSOR_TYPE_FEED => $this->localize('advertise::phrase.sponsor_in_feed_successfully_paid_please_waiting_for_approval'),
            default                    => $this->localize('advertise::phrase.sponsor_successfully_paid_please_waiting_for_approval'),
        };

        $url   = $this->model->toUrl();

        return (new MailMessage())->line($intro)
            ->subject($this->localize('advertise::phrase.pending_sponsor_notification'))
            ->action($this->localize('core::phrase.view_now'), $url);
    }

    public function callbackMessage(): ?string
    {
        if (null === $this->model) {
            return $this->localize('advertise::phrase.sponsor_successfully_paid_please_waiting_for_approval');
        }

        return match ($this->model->sponsor_type) {
            Support::SPONSOR_TYPE_FEED => $this->localize('advertise::phrase.sponsor_in_feed_successfully_paid_please_waiting_for_approval'),
            default                    => $this->localize('advertise::phrase.sponsor_successfully_paid_please_waiting_for_approval'),
        };
    }
}
