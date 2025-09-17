<?php

namespace MetaFox\Core\Notifications;

use MetaFox\Core\Models\Link as Model;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

/**
 * @property Model $model
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class NewPostLinkToFollowerNotification extends Notification
{
    protected string $type = 'post_link_follower_notification';

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
        $message = __p('core::phrase.post_link_follower_notification_type');

        if (!$this?->model instanceof Content) {
            return $message;
        }

        $content = $this->model->feed_content;
        if (is_string($content)) {
            $attributeParser = [
                'parse_url' => false,
            ];
            app('events')->dispatch('core.parse_content', [$this->model, &$content, $attributeParser]);
        }

        return __p('core::phrase.user_name_create_a_new_post', [
            'title'     => $content ?? '',
            'isTitle'   => (int) !empty($this->model->toTitle()),
            'user_name' => $this->model->user->full_name,
        ]);
    }

    public function toMobileMessage(IsNotifiable $notifiable): array
    {
        $message = __p('core::phrase.post_link_follower_notification_type');

        if (!$this?->model instanceof Content) {
            return [
                'message' => $message,
                'url'     => null,
                'router'  => null,
            ];
        }

        $content = $this->model->feed_content;
        if (is_string($content)) {
            $attributeParser = [
                'parse_url' => false,
            ];
            app('events')->dispatch('core.parse_content', [$this->model, &$content, $attributeParser]);
        }

        $message = __p('core::phrase.user_name_create_a_new_post', [
            'title'     => $content ?? '',
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
