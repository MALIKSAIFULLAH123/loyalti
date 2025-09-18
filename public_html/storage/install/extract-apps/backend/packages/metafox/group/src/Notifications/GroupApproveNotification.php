<?php

namespace MetaFox\Group\Notifications;

use MetaFox\Group\Models\Group as Model;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Notifications\ApproveNotification;

/**
 * stub: packages/notifications/notification.stub.
 */

/**
 * Class GroupApproveNotification.
 * @property Model $model
 * @ignore
 */
class GroupApproveNotification extends ApproveNotification
{
    protected string $type = 'group_approve_notification';

    /**
     * Get the mail representation of the notification.
     *
     * @param              $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $intro = $this->localize('group::notification.group_approved_successfully_notification');
        $url   = $this->model->toUrl();

        return (new MailMessage())
            ->locale($this->getLocale())
            ->line($intro)
            ->subject($this->localize('group::phrase.approved_group_notification'))
            ->action($this->localize('core::phrase.view_now'), $url);
    }

    public function callbackMessage(): ?string
    {
        return $this->localize('group::notification.group_approved_successfully_notification');
    }
}
