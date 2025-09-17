<?php

namespace MetaFox\Forum\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Notifications\ApproveNotification;

class ApprovedPost extends ApproveNotification
{
    protected string $type = 'approved_post';

    public function toMail(): ?MailMessage
    {
        $model = $this->model;

        if (null === $model) {
            return null;
        }

        $thread = $this->model->thread;

        if (null === $thread) {
            return null;
        }

        $subject = $this->localize('forum::phrase.your_pending_post_in_thread_has_been_approved_subject', [
            'title' => $thread->toTitle(),
        ]);

        $content = $this->localize('forum::phrase.your_pending_post_in_thread_has_been_approved_content', [
            'title' => $thread->toTitle(),
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

        $thread = $this->model->thread;

        if (null === $thread) {
            return null;
        }

        return $this->localize('forum::notification.your_pending_post_in_thread_has_been_approved', [
            'title' => $thread->toTitle(),
        ]);
    }

    public function toUrl(): ?string
    {
        $model = $this->model;

        if (null === $model) {
            return null;
        }

        return $model->toUrl();
    }

    public function toLink(): ?string
    {
        $model = $this->model;

        if (null === $model) {
            return null;
        }

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
