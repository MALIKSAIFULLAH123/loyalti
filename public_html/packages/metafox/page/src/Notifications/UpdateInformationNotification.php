<?php

namespace MetaFox\Page\Notifications;

use Illuminate\Support\Arr;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Page\Models\PageHistory as Model;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

/**
 * Class UpdateInformationNotification.
 * @property Model $model
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class UpdateInformationNotification extends Notification
{
    protected string $type = 'page_update_info';

    /**
     * @inheritDoc
     */
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
        $extra = json_decode($this->model->extra, true);

        return $this->localize('page::notification.an_admin_changed_the_name_of_the_page', [
            'current_name' => Arr::get($extra, 'old'),
            'new_name'     => Arr::get($extra, 'new'),
        ]);
    }

    public function toMail(IsNotifiable $notifiable): MailMessage
    {
        $service = new MailMessage();
        $subject = $this->localize('page::mail.the_page_name_is_changed_subject');
        $url     = $this->model->page->toUrl() ?? '';
        $extra   = json_decode($this->model->extra, true);

        $text = $this->localize('page::notification.an_admin_changed_the_name_of_the_page', [
            'current_name' => ban_word()->clean(Arr::get($extra, 'old')),
            'new_name'     => ban_word()->clean(Arr::get($extra, 'new')),
        ]);

        return $service
            ->locale($this->getLocale())
            ->subject($subject)
            ->line($text)
            ->action($this->localize('core::phrase.view_now'), $url);
    }
}
