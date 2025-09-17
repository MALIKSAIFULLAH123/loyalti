<?php

namespace MetaFox\Activity\Notifications;

use MetaFox\Activity\Models\Share as Model;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

/**
 * @property Model $model
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class NewShareToFollowerNotification extends Notification
{
    protected string $type = 'share_follower_notification';

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
        $message = __p('activity::phrase.share_follower_notification_type');

        if (!$this?->model instanceof Content) {
            return $message;
        }

        $title = $this->model->toTitle();
        app('events')->dispatch('core.parse_content', [$this->model, &$title]);

        $title = strip_tags($title);

        return __p('activity::notification.user_name_share_a_post', [
            'title'     => $title,
            'isTitle'   => (int) !empty($this->model->toTitle()),
            'user_name' => $this->model->user->full_name,
        ]);
    }

    public function toMobileMessage(IsNotifiable $notifiable): array
    {
        $message = __p('activity::phrase.share_follower_notification_type');

        if (!$this?->model instanceof Content) {
            return [
                'message' => $message,
                'url'     => null,
                'router'  => null,
            ];
        }

        $title = $this->model->toTitle();
        app('events')->dispatch('core.parse_content', [$this->model, &$title]);

        $title = strip_tags($title);

        $message = __p('activity::notification.user_name_share_a_post', [
            'title'     => $title,
            'isTitle'   => (int) !empty($this->model->toTitle()),
            'user_name' => $this->model->user->full_name,
        ]);

        return [
            'message' => strip_tag_content($message),
            'url'     => $this->model->toUrl(),
            'router'  => $this->model->toRouter(),
        ];
    }
}
