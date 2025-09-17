<?php

namespace MetaFox\Forum\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\Notifications\Notification;

class AddModerator extends Notification
{
    protected string $type = 'add_moderator';

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

    public function toMail(): MailMessage
    {
        $subject = __p('forum::notification.you_have_been_granted_moderator_forum_title', [
            'forum_title' => $this->model->forum->toTitle(),
        ]);

        $content = $this->getMessage();

        $service = new MailMessage();

        return $service
            ->locale($this->getLocale())
            ->subject($subject)
            ->line($content)
            ->action($this->localize('core::phrase.view_now'), $this->toUrl());
    }

    public function callbackMessage(): ?string
    {
        if (null == $this->model) {
            return null;
        }

        return $this->getMessage();
    }

    protected function getMessage(): string
    {
        return $this->localize('forum::notification.you_have_been_granted_moderator_forum_title', [
            'forum_title' => $this->model->forum->toTitle(),
        ]);
    }

    public function toRouter(): ?string
    {
        if (MetaFox::isMobile()) {
            return url_utility()->makeApiMobileUrl('forum');
        }

        $model = $this->model;

        if (null === $model) {
            return null;
        }

        return $this->model->forum->toRouter();
    }

    public function toLink(): ?string
    {
        if (MetaFox::isMobile()) {
            return url_utility()->makeApiUrl('forum');
        }

        $model = $this->model;

        if (null === $model) {
            return null;
        }

        return $this->model->forum->toLink();
    }

    public function toUrl(): ?string
    {
        if (MetaFox::isMobile()) {
            return url_utility()->makeApiFullUrl('forum');
        }

        $model = $this->model;

        if (null === $model) {
            return null;
        }

        return $this->model->forum->toUrl();
    }
}
