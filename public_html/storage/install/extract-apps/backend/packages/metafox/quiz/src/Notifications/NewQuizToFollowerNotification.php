<?php

namespace MetaFox\Quiz\Notifications;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

/**
 * @property Model $model
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class NewQuizToFollowerNotification extends Notification
{
    protected string $type = 'quiz_follower_notification';

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
        $message = __p('quiz::phrase.quiz_follower_notification_type');

        if (!$this?->model instanceof Content) {
            return $message;
        }

        return __p('quiz::notification.user_name_created_a_new_quiz_title', [
            'title'     => $this->model->toTitle(),
            'user_name' => $this->model?->user?->full_name,
        ]);
    }

    public function toMobileMessage(IsNotifiable $notifiable): array
    {
        $message = __p('quiz::phrase.quiz_follower_notification_type');

        if (!$this?->model instanceof Content) {
            return [
                'message' => $message,
                'url'     => null,
                'router'  => null,
            ];
        }

        $message = __p('quiz::notification.user_name_created_a_new_quiz_title', [
            'title'     => $this->model->toTitle(),
            'user_name' => $this->model?->user?->full_name,
        ]);

        return [
            'message' => strip_tag_content($message),
            'url'     => $this->model->toUrl(),
            'router'  => $this->model->toRouter(),
        ];
    }
}
