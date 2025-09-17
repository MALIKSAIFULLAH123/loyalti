<?php

namespace MetaFox\Forum\Notifications;

use Illuminate\Support\Arr;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

class MergeThread extends Notification
{
    protected string $type = 'merge_thread';

    /**
     * @var string
     */
    protected string $oldTitle;

    /**
     * @param  string $title
     * @return void
     */
    public function setOldTitle(string $title): void
    {
        $this->oldTitle = $title;
    }

    /**
     * @return string|null
     */
    public function getOldTitle(): ?string
    {
        return $this->oldTitle;
    }

    public function toArray(IsNotifiable $notifiable): array
    {
        $this->model->old_merged_title = $this->getOldTitle();

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
        if (null == $this->model) {
            return null;
        }

        $oldTitle = Arr::get($this->data, 'old_merged_title');

        /*
         * old_title The title of the thread is kept
         * title The title of the thread is merged and deleted
         */
        return $this->localize('forum::notification.thread_title_you_subscribed_to_has_been_merged_with_thread_title', [
            'old_title' => $this->model->toTitle(),
            'title'     => $oldTitle,
        ]);
    }

    public function toMail(): ?MailMessage
    {
        $oldTitle = Arr::get($this->data, 'old_merged_title');

        /**
         * old_title The title of the thread is kept
         * title The title of the thread is merged and deleted.
         */
        $subject = $this->localize('forum::phrase.thread_title_you_subscribed_to_has_been_merged_with_thread_subject', [
            'old_title' => $this->model->toTitle(),
            'title'     => $oldTitle,
        ]);

        $content = $this->localize('forum::notification.thread_title_you_subscribed_to_has_been_merged_with_thread_title', [
            'old_title' => $this->model->toTitle(),
            'title'     => $oldTitle,
        ]);

        $service = new MailMessage();

        return $service
            ->locale($this->getLocale())
            ->subject($subject)
            ->line($content)
            ->action($this->localize('core::phrase.view_now'), $this->toUrl());
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
