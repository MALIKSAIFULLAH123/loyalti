<?php

namespace MetaFox\Follow\Notifications;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;
use MetaFox\Platform\Contracts\User;

/**
 * @property Model $model
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class NewPostNotification extends Notification
{
    protected string $type = 'follower';

    public function toArray(IsNotifiable $notifiable): array
    {
        if (!$this?->model instanceof Content) {
            return [];
        }

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
        $message = __p('follow::phrase.someone_created_a_new_post');

        if (!$this?->model instanceof Content) {
            return $message;
        }

        if ($this->model->owner instanceof User) {
            $message = __p('follow::phrase.user_create_new_post_title', [
                'user_name' => $this->model->owner->toTitle(),
                'title'     => $this->model->toTitle(),
            ]);
        }

        if (method_exists($this->model, 'toFollowerNotification')) {
            $message = Arr::get($this->model->toFollowerNotification(), 'message');
        }

        return $message;
    }

    public function toMobileMessage(IsNotifiable $notifiable): array
    {
        $message = __p('follow::phrase.someone_created_a_new_post');

        if (!$this?->model instanceof Content) {
            return [
                'message' => $message,
                'url'     => null,
                'router'  => null,
            ];
        }

        if ($this->model->owner instanceof User) {
            $message = __p('follow::phrase.user_create_new_post_title', [
                'user_name' => $this->model->owner->toTitle(),
                'title'     => $this->model->toTitle(),
            ]);
        }

        if (method_exists($this->model, 'toFollowerNotification')) {
            $message = Arr::get($this->model->toFollowerNotification(), 'message', $message);
        }

        return [
            'message' => strip_tag_content($message),
            'url'     => $this->model->toUrl(),
            'router'  => $this->model->toRouter(),
        ];
    }
}
