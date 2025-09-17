<?php

namespace MetaFox\Forum\Notifications;

use MetaFox\Notification\Messages\MailMessage;
use Illuminate\Support\Arr;
use MetaFox\Forum\Support\ForumSupport;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

class SubscribedThread extends Notification
{
    protected string $type = 'subscribed_thread';

    /**
     * @var string
     */
    protected $actionType;

    /**
     * @var array
     */
    protected $actionValue;

    /**
     * @param  string $action
     * @return void
     */
    public function setActionType(string $action): self
    {
        $this->actionType = $action;

        return $this;
    }

    /**
     * @param  string $value
     * @return void
     */
    public function setActionValue(array $value): self
    {
        $this->actionValue = $value;

        return $this;
    }

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

        $data = $model->toArray();

        if (null !== $this->actionType) {
            Arr::set($data, 'action_detail', [
                'type'  => $this->actionType,
                'value' => $this->actionValue,
            ]);
        }

        $response = [
            'data'      => $data,
            'item_id'   => $model->entityId(),
            'item_type' => $model->entityType(),
            'user_id'   => $model->userId(),
            'user_type' => $model->userType(),
        ];

        return $response;
    }

    public function toMail(): ?MailMessage
    {
        $mailMessage = $this->getMailMessage([
            'type'  => $this->actionType,
            'value' => $this->actionValue,
        ]);

        if (null === $mailMessage) {
            return null;
        }

        [$subject, $content] = $mailMessage;

        $service = new MailMessage();

        return $service
            ->locale($this->getLocale())
            ->subject($subject)
            ->line($content)
            ->action($this->localize('core::phrase.view_now'), $this->toUrl());
    }

    public function callbackMessage(): ?string
    {
        return $this->getCallbackMessage();
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

    /**
     * @param  array<string, mixed> $action
     * @return string[]|null
     */
    protected function getMailMessage(array $action): ?array
    {
        $actionType = Arr::get($action, 'type');

        $actionValue = Arr::get($action, 'value');

        $thread = $this->model;

        if (null === $thread) {
            return null;
        }

        $currentTitle = null;

        $subject = $message = null;

        if (is_array($actionValue) && Arr::has($actionValue, 'current_title')) {
            $currentTitle = Arr::get($actionValue, 'current_title');
        }

        switch ($actionType) {
            case ForumSupport::MOVE_ACTION:
                $subject = $this->localize('forum::phrase.move_action_notification_subject', [
                    'thread_title' => $currentTitle,
                    'forum_title'  => Arr::get($actionValue, 'current_forum_title'),
                ]);
                $message = $this->localize('forum::phrase.move_action_notification_message', [
                    'thread_title' => $currentTitle,
                    'forum_title'  => Arr::get($actionValue, 'current_forum_title'),
                ]);
                break;
            case ForumSupport::CLOSE_ACTION:
                $subject = $this->localize('forum::phrase.close_action_notification_subject', [
                    'title' => $currentTitle,
                ]);
                $message = $this->localize('forum::phrase.close_action_notification_message', [
                    'title' => $currentTitle,
                ]);
                break;
            case ForumSupport::REOPEN_ACTION:
                $subject = $this->localize('forum::phrase.reopen_action_notification_subject', [
                    'title' => $currentTitle,
                ]);
                $message = $this->localize('forum::phrase.reopen_action_notification_message', [
                    'title' => $currentTitle,
                ]);
                break;
            case ForumSupport::UPDATE_TITLE_ACTION:
                $subject = $this->localize('forum::phrase.update_title_action_notification_subject', [
                    'current_title' => $currentTitle,
                    'new_title'     => Arr::get($actionValue, 'new_title'),
                ]);
                $message = $this->localize('forum::phrase.update_title_action_notification_message', [
                    'current_title' => $currentTitle,
                    'new_title'     => Arr::get($actionValue, 'new_title'),
                ]);
                break;
            case ForumSupport::UPDATE_DESCRIPTION_ACTION:
                $subject = $this->localize('forum::phrase.update_description_action_notification_subject', [
                    'title' => $currentTitle,
                ]);
                $message = $this->localize('forum::phrase.update_description_action_notification_message', [
                    'title' => $currentTitle,
                ]);
                break;
            case ForumSupport::UPDATE_INFO_ACTION:
                $subject = $this->localize('forum::phrase.update_info_action_notification_subject', [
                    'title' => $currentTitle,
                ]);
                $message = $this->localize('forum::phrase.update_info_action_notification_message', [
                    'title' => $currentTitle,
                ]);
                break;
            case ForumSupport::STICK_ACTION:
                $subject = $this->localize('forum::phrase.stick_action_notification_subject', [
                    'title' => $currentTitle,
                ]);
                $message = $this->localize('forum::notification.thread_stick_subscribed_notification', [
                    'title' => $currentTitle,
                ]);
                break;
        }

        if (null === $message || null === $subject) {
            return null;
        }

        return [$subject, $message];
    }

    protected function getCallbackMessage(): ?string
    {
        $action = Arr::get($this->data, 'action_detail', []);
        $data   = $this->getMailMessage($action);
        if (null == $data) {
            return null;
        }

        [, $message] = $data;

        return $message;
    }
}
