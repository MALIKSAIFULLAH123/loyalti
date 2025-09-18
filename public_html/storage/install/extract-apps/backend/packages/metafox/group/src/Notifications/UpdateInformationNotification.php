<?php

namespace MetaFox\Group\Notifications;

use Illuminate\Support\Arr;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\GroupHistory as Model;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

/**
 * Class UpdateInformationNotification.
 *
 * @property Model $model
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 */
class UpdateInformationNotification extends Notification
{
    protected string $type = 'group_update_info';

    /**
     * Get the mail representation of the notification.
     *
     * @param IsNotifiable $notifiable
     *
     * @return MailMessage
     */
    public function toMail(IsNotifiable $notifiable): MailMessage
    {
        $service = new MailMessage();
        $group = $this->model->group;
        $extra = json_decode($this->model->extra, true);
        $url = '';

        $subject = $this->localize('group::mail.an_admin_changed_the_name_of_the_group_subject');
        $text = $this->localize('group::notification.an_admin_changed_the_name_of_the_group', [
            'current_name' => Arr::get($extra, 'old'),
            'new_name'     => Arr::get($extra, 'new'),
        ]);


        if ($group instanceof Group) {
            $url = $group->toUrl() ?? '';
        }

        return $service
            ->locale($this->getLocale())
            ->subject($subject)
            ->line($text)
            ->action($this->localize('core::phrase.view_now'), $url);
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
        $extra = json_decode($this->model->extra, true);

        return $this->localize('group::notification.an_admin_changed_the_name_of_the_group', [
            'current_name' => Arr::get($extra, 'old'),
            'new_name'     => Arr::get($extra, 'new'),
        ]);
    }
}
