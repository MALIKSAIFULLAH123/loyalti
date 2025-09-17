<?php

namespace MetaFox\Advertise\Notifications;

use MetaFox\Advertise\Models\Advertise;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\ApproveNotification;

/**
 * stub: packages/notifications/notification.stub.
 */

/**
 * Class PendingAdvertiseNotification.
 * @property Advertise $model
 * @ignore
 */
class PendingAdvertiseNotification extends ApproveNotification
{
    protected string $type = 'advertise_pending_notification';

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
        $intro = $this->localize('advertise::phrase.advertise_successfully_paid_please_waiting_for_approval');

        $url   = $this->model->toUrl();

        return (new MailMessage())->line($intro)
            ->subject($this->localize('advertise::phrase.pending_advertise_notification'))
            ->action($this->localize('core::phrase.view_now'), $url);
    }

    public function callbackMessage(): ?string
    {
        if (null === $this->model) {
            return $this->localize('advertise::phrase.advertise_successfully_paid_please_waiting_for_approval');
        }

        return $this->localize('advertise::phrase.advertise_successfully_paid_please_waiting_for_approval');
    }
}
