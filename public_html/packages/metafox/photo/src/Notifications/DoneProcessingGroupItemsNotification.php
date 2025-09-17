<?php

namespace MetaFox\Photo\Notifications;

use Illuminate\Support\Arr;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Photo\Models\Photo;
use MetaFox\Photo\Models\PhotoGroup as Model;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Contracts\Media;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Notifications\Notification;

/**
 * @property Model $model
 */
class DoneProcessingGroupItemsNotification extends Notification
{
    protected string $type = 'done_processing_group_items';

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return MailMessage
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toMail($notifiable)
    {
        $mailService = new MailMessage();

        $emailLine = $this->localize('photo::notification.photo_group_items_read_to_view');
        $url       = $this->model->toUrl();

        return $mailService
            ->locale($this->getLocale())
            ->subject($emailLine)
            ->line($emailLine)
            ->action($this->localize('core::phrase.view_now'), $url ?? '');
    }

    /**
     * @param IsNotifiable $notifiable
     *
     * @return array<string, mixed>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray(IsNotifiable $notifiable): array
    {
        $message = $this->localize('photo::notification.photo_group_items_read_to_view');
        if (!$this->model->isApproved()) {
            $message = $this->localize('photo::notification.photo_group_items_ready_but_pending');
        }

        return [
            'data'      => array_merge($this->model->toArray(), ['message' => $message]),
            'item_id'   => $this->model->entityId(),
            'item_type' => $this->model->entityType(),
            'user_id'   => $this->model->userId(),
            'user_type' => $this->model->userType(),
        ];
    }

    public function callbackMessage(): ?string
    {
        return Arr::get($this->data, 'message', $this->localize('photo::notification.photo_group_items_read_to_view'));
    }

    public function toUrl(): ?string
    {
        if (!$this->model instanceof Model) {
            return null;
        }

        $url = $this->model->toUrl();

        if ($this->model->isApproved()) {
            return $url;
        }

        $owner = $this->model->owner;
        if (!$owner instanceof User) {
            return $url;
        }

        if (!$owner->hasPendingMode() || !$owner->isPendingMode()) {
            return url_utility()->makeApiFullUrl(sprintf('%s/my-pending', $this->getVideoEntityType()));
        }

        $user = $this->model->user;
        if ($user?->hasPermissionTo($owner->entityType() . '.moderate') && method_exists($owner, 'toManagePostUrl')) {
            return $owner->toManagePostUrl();
        }

        if (method_exists($owner, 'toMyPendingContentUrl')) {
            return $owner->toMyPendingContentUrl();
        }

        return url_utility()->makeApiResourceFullUrl($owner->entityType(), $owner->entityId()) . '/review_my_content';
    }

    public function toLink(): ?string
    {
        if (!$this->model instanceof Model) {
            return null;
        }

        $link = $this->model->toLink();

        if ($this->model->isApproved()) {
            return $link;
        }

        $owner = $this->model->owner;
        if (!$owner instanceof User) {
            return $link;
        }

        if (!$owner->hasPendingMode() || !$owner->isPendingMode()) {
            return url_utility()->makeApiUrl(sprintf('%s/my-pending', $this->getVideoEntityType()));
        }

        $user = $this->model->user;
        if ($user?->hasPermissionTo($owner->entityType() . '.moderate') && method_exists($owner, 'toManagePostUrl')) {
            return $owner->toManagePostUrl();
        }

        if (method_exists($owner, 'toMyPendingContentLink')) {
            return $owner->toMyPendingContentLink();
        }

        return url_utility()->makeApiResourceUrl($owner->entityType(), $owner->entityId()) . '/review_my_content';
    }

    public function toRouter(): ?string
    {
        if (!$this->model instanceof Model) {
            return null;
        }

        $router = $this->model->toRouter();

        if ($this->model->isApproved()) {
            return $router;
        }

        $owner = $this->model->owner;
        if (!$owner instanceof User) {
            return $router;
        }

        if (!$owner->hasPendingMode() || !$owner->isPendingMode()) {
            $videoEntityType = $this->getVideoEntityType();

            return url_utility()->makeApiMobileUrl(sprintf('%s/%s/viewMyPendingVideos', $videoEntityType, $videoEntityType));
        }

        $user = $this->model->user;
        if ($user?->hasPermissionTo($owner->entityType() . '.moderate') && method_exists($owner, 'toManagePostRouter')) {
            return $owner->toManagePostRouter();
        }

        if (method_exists($owner, 'toMyPendingContentRouter')) {
            return $owner->toMyPendingContentRouter();
        }

        return url_utility()->makeApiMobileResourceUrl($owner->entityType(), $owner->entityId()) . '/review_my_content';
    }

    protected function getVideoEntityType(): string
    {
        $video = $this->model?->items()
            ->whereNot('item_type', Photo::ENTITY_TYPE)
            ->first()?->detail;

        if (!$video instanceof Media) {
            return 'video';
        }

        return $video->entityType();
    }
}
