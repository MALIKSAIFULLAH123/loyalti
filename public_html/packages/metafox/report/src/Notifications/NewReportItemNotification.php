<?php

namespace MetaFox\Report\Notifications;

use Illuminate\Bus\Queueable;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\HasTitle;
use MetaFox\Platform\Contracts\HasUrl;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;
use MetaFox\Report\Models\ReportItemAggregate;

/**
 * Class ProcessReportItemNotification.
 * @property ReportItemAggregate $model
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class NewReportItemNotification extends Notification
{
    use Queueable;

    protected string $type = 'new_report_item';

    /**
     * Get the mail representation of the notification.
     *
     * @param IsNotifiable $notifiable
     * @return MailMessage
     */
    public function toMail(IsNotifiable $notifiable): MailMessage
    {
        $item         = $this->model->item;
        $title        = $item instanceof HasTitle ? $item?->toTitle() : $item?->entityType();
        $emailSubject = $this->localize('report::mail.new_report_subject');
        $emailLine    = $this->localize('report::mail.new_report_line', ['item_title' => $title ?? $this->model->itemType()]);

        $url = url_utility()->makeApiFullUrl('admincp/report/items/browse');;

        return (new MailMessage())
            ->subject($emailSubject)
            ->locale($this->getLocale())
            ->line($emailLine)
            ->action(__p('core::phrase.view_now'), $url ?? '');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        $data = $this->model->toArray();

        return [
            'data'      => $data,
            'item_id'   => $this->model->entityId(),
            'item_type' => $this->model->entityType(),
            'user_id'   => $this->model->lastUser->entityId(),
            'user_type' => $this->model->lastUser->entityType(),
        ];
    }

    public function callbackMessage(): ?string
    {
        $item  = $this->model->item;
        $title = $item instanceof HasTitle ? $item?->toTitle() : $item?->entityType();
        return $this->localize('report::mail.new_report_notification', ['item_title' => $title ?? $this->model->itemType()]);
    }

    public function toUrl(): string
    {
        $item = $this->model->item;

        return $item instanceof HasUrl ? $item?->toUrl() : $item?->entityType();
    }

    public function toLink(): string
    {
        $item = $this->model->item;

        return $item instanceof HasUrl ? $item?->toLink() : $item?->entityType();
    }
}
