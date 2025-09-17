<?php

namespace MetaFox\Group\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Group\Models\Request as Model;
use MetaFox\Platform\Notifications\ApproveNotification;

/**
 * stub: packages/notifications/notification.stub.
 */

/**
 * Class DeniedRequestNotification.
 * @property Model $model
 * @ignore
 */
class DeniedRequestNotification extends ApproveNotification
{
    protected string $type = 'group_denied_request';

    /**
     * Get the mail representation of the notification.
     *
     * @param              $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $group = $this->model->group;

        return (new MailMessage())
            ->locale($this->getLocale())
            ->subject($this->localize('group::mail.decline_request_member_subject', ['group_name' => $group?->toTitle()]))
            ->line($this->localize(
                'group::mail.decline_request_member_content',
                ['group_name' => $group?->toTitle(), 'reason' => $this->model->reason]
            ));
    }

    public function callbackMessage(): ?string
    {
        return $this->localize('group::mail.decline_request_member_subject', ['group_name' => $this->model->group?->toTitle()]);
    }
}
