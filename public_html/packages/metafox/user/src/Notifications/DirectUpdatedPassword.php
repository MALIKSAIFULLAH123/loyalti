<?php

namespace MetaFox\User\Notifications;

use MetaFox\User\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

/**
 * Class DirectUpdatedPassword.
 * @property User $model
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class DirectUpdatedPassword extends Notification
{
    protected string $type = 'new_password_updated';

    public function toMail(): MailMessage
    {
        $service = new MailMessage();

        $dateTime = Carbon::now()->toDayDateTimeString();

        //todo: need format email template
        return $service
            ->locale($this->getLocale())
            ->line($this->localize('user::mail.site_name_password_change_subject', [
                'site_name' => config('app.name'),
            ]))
            ->line($this->localize('user::mail.your_site_name_password_was_changed_on_date_time', [
                'site_name' => config('app.name'),
                'date_time' => $dateTime,
            ]));
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
        return $this->localize('user::notification.your_site_name_password_was_changed_on', [
            'site_name' => config('app.name'),
        ]);
    }

    public function toLink(): ?string
    {
        return null;
    }

    public function toUrl(): ?string
    {
        return null;
    }

    public function toRouter(): ?string
    {
        return null;
    }
}
