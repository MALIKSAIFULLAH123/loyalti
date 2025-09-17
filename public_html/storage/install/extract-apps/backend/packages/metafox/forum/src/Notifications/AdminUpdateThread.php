<?php

namespace MetaFox\Forum\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

class AdminUpdateThread extends Notification
{
    protected string $type = 'admin_update_thread';

    /**
     * Get the mail representation of the notification.
     *
     * @return MailMessage
     */
    public function toMail(): MailMessage
    {
        $intro   = $this->localize('forum::phrase.your_thread_has_been_updated_by_an_admin');
        $subject = $this->localize('forum::phrase.your_thread_has_been_updated_by_an_admin_subject', [
            'title' => $this->model->toTitle(),
        ]);
        $url = $this->model->toUrl();

        return (new MailMessage())
            ->locale($this->getLocale())
            ->subject($subject)
            ->line($intro)
            ->action($this->localize('forum::phrase.forum'), $url);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  IsNotifiable $notifiable
     * @return array
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
        return $this->localize('forum::notification.your_thread_has_been_updated_by_an_admin');
    }

    public function toRouter(): ?string
    {
        $model = $this->model;

        if (null === $model) {
            return null;
        }

        return $this->model->toRouter();
    }
}
