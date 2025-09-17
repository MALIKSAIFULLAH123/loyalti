<?php

namespace MetaFox\Forum\Notifications;

use Illuminate\Support\Arr;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

class DisplayWiki extends Notification
{
    protected string $type = 'display_wiki';

    /**
     * @var bool
     */
    protected bool $isWiki;

    /**
     * @var int
     */
    protected int $subscribedUserId;

    /**
     * @param  bool $value
     * @return void
     */
    public function setIsWiki(bool $value): void
    {
        $this->isWiki = $value;
    }

    /**
     * @return bool
     */
    public function getIsWiki(): bool
    {
        return $this->isWiki;
    }

    /**
     * @param  int  $id
     * @return void
     */
    public function setSubscribedUserId(int $id): void
    {
        $this->subscribedUserId = $id;
    }

    /**
     * @return int
     */
    public function getSubscribedUserId(): int
    {
        return $this->subscribedUserId;
    }

    public function toArray(IsNotifiable $notifiable): array
    {
        $this->model->is_changed_to_wiki = $this->getIsWiki();
        $this->model->subscribed_user_id = $this->getSubscribedUserId();

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

        return $this->getMessage();
    }

    protected function getMessage(): ?string
    {
        if (null == $this->model) {
            return null;
        }

        $thread           = $this->model;
        $isWiki           = Arr::get($this->data, 'is_changed_to_wiki', $thread?->is_wiki);
        $subscribedUserId = Arr::get($this->data, 'subscribed_user_id');

        if ($isWiki) {
            if ($subscribedUserId != $thread->userId()) {
                return $this->localize('forum::notification.thread_wiki_subscribed_notification', [
                    'title' => $this->model->toTitle(),
                ]);
            }

            return $this->localize('forum::notification.admin_updated_your_thread_title_as_a_wiki', [
                'title' => $this->model->toTitle(),
            ]);
        }

        if ($subscribedUserId != $thread->userId()) {
            return $this->localize('forum::notification.thread_remove_as_wiki_subscribed_notification', [
                'title' => $this->model->toTitle(),
            ]);
        }

        return $this->localize('forum::notification.admin_removed_your_thread_title_as_a_wiki', [
            'title' => $this->model->toTitle(),
        ]);
    }

    public function toMail(): ?MailMessage
    {
        $subject = $this->getMailSubject();

        $content = $this->getMessage();

        $service = new MailMessage();

        return $service
            ->locale($this->getLocale())
            ->subject($subject)
            ->line($content)
            ->action($this->localize('core::phrase.view_now'), $this->toUrl());
    }

    public function toUrl(): ?string
    {
        if (null === $this->model) {
            return null;
        }

        return $this->model->toUrl();
    }

    public function toLink(): ?string
    {
        if (null === $this->model) {
            return null;
        }

        return $this->model->toLink();
    }

    protected function getMailSubject(): string
    {
        $thread           = $this->model;
        $isWiki           = Arr::get($this->data, 'is_changed_to_wiki', $thread?->is_wiki);
        $subscribedUserId = Arr::get($this->data, 'subscribed_user_id');

        if ($isWiki) {
            if ($subscribedUserId != $thread->userId()) {
                return $this->localize('forum::phrase.admin_updated_subscribed_thread_title_as_a_wiki_subject', [
                    'title' => $thread?->toTitle(),
                ]);
            }

            return $this->localize('forum::phrase.admin_updated_your_thread_title_as_a_wiki_subject', [
                'title' => $thread?->toTitle(),
            ]);
        }

        if ($subscribedUserId != $thread->userId()) {
            return $this->localize('forum::phrase.admin_removed_subscribed_thread_title_as_a_wiki_subject', [
                'title' => $thread?->toTitle(),
            ]);
        }

        return $this->localize('forum::phrase.admin_removed_your_thread_title_as_a_wiki_subject', [
            'title' => $thread?->toTitle(),
        ]);
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
