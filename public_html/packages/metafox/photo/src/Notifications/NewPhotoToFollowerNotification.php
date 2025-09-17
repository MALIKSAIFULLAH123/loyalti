<?php

namespace MetaFox\Photo\Notifications;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Photo\Models\Album;
use MetaFox\Photo\Models\Photo;
use MetaFox\Photo\Models\PhotoGroup;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;


/**
 * @property Model $model
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class NewPhotoToFollowerNotification extends Notification
{
    protected string $type = 'photo_follower_notification';

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
        $message = __p('photo::phrase.photo_follower_notification_type');

        if (!$this?->model instanceof Content) {
            return $message;
        }

        if ($this->model instanceof PhotoGroup) {
            return __p('photo::notification.user_name_posted_a_post', [
                'user_name' => $this->model?->userEntity?->name,
            ]);
        }

        return __p('photo::notification.user_name_updated_their_cover_or_profile_picture', [
            'gender'    => $this->model->userEntity->possessive_gender,
            'user_name' => $this->model->userEntity->name,
            'type'      => $this->model->is_cover_photo ? Album::COVER_ALBUM : Album::PROFILE_ALBUM,
        ]);
    }

    public function toMobileMessage(IsNotifiable $notifiable): array
    {
        $message = __p('photo::phrase.photo_follower_notification_type');

        if (!$this?->model instanceof Content) {
            return [
                'message' => $message,
                'url'     => null,
                'router'  => null,
            ];
        }

        if ($this->model instanceof PhotoGroup) {
            $message = __p('photo::notification.user_name_posted_a_post', [
                'user_name' => $this->model?->user?->full_name,
            ]);
        }

        if ($this->model instanceof Photo) {
            $message = __p('photo::notification.user_name_updated_their_cover_or_profile_picture', [
                'gender'    => $this->model->userEntity->possessive_gender,
                'user_name' => $this->model->userEntity->name,
                'type'      => $this->model->is_cover_photo ? Album::COVER_ALBUM : Album::PROFILE_ALBUM,
            ]);
        }


        return [
            'message' => strip_tag_content($message),
            'url'     => $this->model->toUrl(),
            'router'  => $this->model->toRouter(),
        ];
    }
}
