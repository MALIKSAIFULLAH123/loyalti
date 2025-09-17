<?php

namespace MetaFox\Chat\Notifications;

use MetaFox\Chat\Models\Message as Model;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;

/**
 * stub: packages/notifications/notification.stub.
 */

/**
 * Class BlogApproveNotification.
 * @property Model $model
 * @ignore
 */
class NewMessageNotification extends Notification
{
    protected string $type = 'new_message';

    public function callbackMessage(): ?string
    {
        $model = $this->model;
        if (null === $model) {
            return '';
        }
        $userName          = $model->user?->full_name;
        $attachmentMessage = $this->getAttachmentMessage($model);

        return $userName . ': ' . ($attachmentMessage ?: $model->message);
    }

    public function toArray(IsNotifiable $notifiable): array
    {
        $model = $this->model;

        if (null === $model) {
            return [];
        }

        return [
            'data'      => $model->toArray(),
            'item_id'   => $model->entityId(),
            'item_type' => $model->entityType(),
            'user_id'   => $model->userId(),
            'user_type' => $model->userType(),
        ];
    }

    public function toUrl(): ?string
    {
        $model = $this->model;

        if (null === $model) {
            return null;
        }

        return url_utility()->makeApiResourceFullUrl('messages', $model->room_id);
    }

    public function toRouter(): ?string
    {
        $model = $this->model;

        if (null === $model) {
            return null;
        }

        return url_utility()->makeApiMobileResourceUrl('messages', $model->room_id) . '?id=' . $model->room_id;
    }

    private function getAttachmentMessage($model): string
    {
        $attachments = $model->attachments;
        if ($attachments->isEmpty()) {
            return '';
        }
        if ($attachments->count() === 1) {
            return $this->localize('chat::web.sent_a_photo');
        }

        return $this->localize('chat::web.sent_multiple_photos');
    }
}
