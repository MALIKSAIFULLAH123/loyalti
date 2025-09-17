<?php

namespace MetaFox\User\Notifications;

use Illuminate\Bus\Queueable;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;
use MetaFox\User\Models\ExportProcess;

/**
 * stub: packages/notifications/notification.stub.
 */

/**
 * Class UserPendingApprovalNotification.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @property ExportProcess $model
 * @ignore
 */
class DoneExportProcessNotification extends Notification
{
    use Queueable;

    protected string $type = 'done_export_process_user';

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->locale($this->getLocale())
            ->subject($this->localize('user::mail.done_export_process_user_notification_type'))
            ->line($this->getMessage())
            ->action($this->localize('core::phrase.view_now'), $this->toLink());
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
        return $this->localize('user::mail.export_processing_the_users');
    }

    public function toLink(): ?string
    {
        return url_utility()->makeApiFullUrl('admincp/user/export-process/browse?process_id=' . $this->model->entityId());
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl('admincp/user/export-process/browse?process_id=' . $this->model->entityId());
    }
}
