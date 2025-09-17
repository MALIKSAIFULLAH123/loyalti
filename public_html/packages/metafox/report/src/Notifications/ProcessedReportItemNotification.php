<?php

namespace MetaFox\Report\Notifications;

use Illuminate\Bus\Queueable;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\Content;

/**
 * Class ProcessReportItemNotification.
 * @property Content $model
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ProcessedReportItemNotification extends Notification
{
    use Queueable;

    protected string $type = 'processed_report_item';

    /**
     * Get the mail representation of the notification.
     *
     * @param  IsNotifiable $notifiable
     * @return MailMessage
     */
    public function toMail(IsNotifiable $notifiable): MailMessage
    {
        $emailSubject = $this->localize('report::mail.report_processed_subject');
        $emailLine    = $this->localize('report::mail.report_for_item_is_processed', ['item' => $this->model->toTitle()]);

        $url = $this->model->toUrl();

        return (new MailMessage())
                    ->subject($emailSubject)
                    ->locale($this->getLocale())
                    ->line($emailLine)
                    ->action(__p('core::phrase.view_now'), $url ?? '');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed                $notifiable
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        $data = $this->model->toArray();

        return [
            'data'      => $data,
            'item_id'   => $this->model->entityId(),
            'item_type' => $this->model->entityType(),
            'user_id'   => $this->model->userId(),
            'user_type' => $this->model->userType(),
        ];
    }

    public function callbackMessage(): ?string
    {
        return $this->localize('report::mail.report_for_item_is_processed', ['item' => $this->model->toTitle()]);
    }
}
