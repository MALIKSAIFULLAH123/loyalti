<?php

namespace MetaFox\Forum\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

class CreatePost extends Notification
{
    protected string $type = 'create_post';

    /**
     * @param  IsNotifiable  $notifiable
     * @return array|mixed[]
     */
    public function toArray(IsNotifiable $notifiable): array
    {
        $model = $this->model;

        if (null === $model) {
            return [];
        }

        return [
            'data'      => $model->toArray(),
            'item_id'   => $model->entityId(),
            'item_type' => $model->entityType(),
            'user_id'   => $model->userId(),
            'user_type' => $model->userType(),
        ];
    }

    public function toMail(): ?MailMessage
    {
        $model = $this->model;

        if (null === $model) {
            return null;
        }

        $user = $model->user;

        $thread = $model->thread;

        $subject = $this->localize('forum::phrase.create_post_subject', [
            'full_name' => $user->full_name,
            'title'     => $thread->toTitle(),
        ]);

        $content = $this->localize('forum::phrase.create_post_content', [
            'full_name' => $user->full_name,
            'title'     => $thread->toTitle(),
        ]);

        $service = new MailMessage();

        return $service
            ->locale($this->getLocale())
            ->subject($subject)
            ->line($content)
            ->action($this->localize('core::phrase.view_now'), $this->toUrl());
    }

    public function callbackMessage(): ?string
    {
        $model = $this->model;

        if (null === $model) {
            return null;
        }

        $fullName = $title = null;

        if (null !== $model->user) {
            $fullName = $model->user->full_name;
        }

        if (null !== $model->thread) {
            $title = $model->thread->toTitle();
        }

        return $this->localize('forum::notification.full_name_replied_on_thread_title', [
            'full_name' => $fullName,
            'title'     => $title,
        ]);
    }

    public function toUrl(): ?string
    {
        return $this->model->toUrl();
    }

    public function toLink(): ?string
    {
        return $this->model->toLink();
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
