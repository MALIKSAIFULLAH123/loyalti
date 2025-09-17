<?php

namespace MetaFox\User\Notifications;

use Illuminate\Bus\Queueable;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

/**
 * stub: packages/notifications/notification.stub.
 */

/**
 * Class UserPendingApprovalNotification.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 */
class UserPendingApprovalNotification extends Notification
{
    use Queueable;

    protected string $type = 'user_pending_approval_notification';

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed                                          $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->locale($this->getLocale())
            ->subject($this->localize('user::mail.user_pending_approval_notification_type'))
            ->line($this->getMessage());
    }

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
        return $this->getMessage();
    }

    private function getMessage(): string
    {
        return $this->localize(
            'user::mail.user_has_just_registered_and_is_awaiting_your_approval',
            ['user' => $this->model->display_name, 'url' => $this->model->toUrl()]
        );
    }
}
