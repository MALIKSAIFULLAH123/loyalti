<?php

namespace MetaFox\User\Notifications;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;
use MetaFox\User\Models\UserEntity;
use MetaFox\User\Support\Facades\UserValue;

/**
 * @property Content $model
 */
class NewPostTimeline extends Notification
{
    protected string $type = 'new_post_timeline';

    public function toMail(IsNotifiable $notifiable): MailMessage
    {
        $service    = new MailMessage();
        $userEntity = $this->model->userEntity;

        $creatorName = $userEntity instanceof UserEntity ? $userEntity->name : null;

        $itemType = null;
        if ($this->model?->entityType() == 'feed') {
            $itemType = $this->model->itemType();
        }

        $emailSubject = $this->localize('user::mail.new_post_timeline');
        $emailLine    = $this->localize('user::mail.user_name_posted_on_your_timeline' . ($itemType ? "_$itemType" : ''), [
            'username' => $creatorName,
        ]);

        $url = $this->model->toUrl();

        return $service
            ->locale($this->getLocale())
            ->subject($emailSubject)
            ->line($emailLine)
            ->action($this->localize('core::phrase.view_now'), $url ?? '');
    }

    /**
     * @param  IsNotifiable         $notifiable
     * @return array<string, mixed>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray(IsNotifiable $notifiable): array
    {
        $data              = $this->model instanceof Model ? $this->model->toArray() : [];
        $isAllowTaggerPost = (int) UserValue::checkUserValueSettingByName(
            $this->model->owner,
            'user_auto_add_tagger_post'
        );

        Arr::set($data, 'is_review_post', $isAllowTaggerPost);

        return [
            'data'      => $data,
            'item_id'   => $this->model->entityId(),
            'item_type' => $this->model->entityType(),
            'user_id'   => $this->model->userId(),
            'user_type' => $this->model->userType(),
        ];
    }

    public function callbackMessage(): ?string
    {
        $name = $this->model->userEntity?->name ?? '';

        $itemType = 'activity_post';
        if (array_key_exists('item_type', $this->data)) {
            $itemType = $this->data['item_type'];
        }

        $isReview = 0;
        if (array_key_exists('is_review_post', $this->data)) {
            $isReview = $this->data['is_review_post'];
        }

        return $this->localize('user::notification.user_name_posted_on_your_timeline', [
            'username'  => $name,
            'app'       => $this->localize('user::notification.' . $itemType),
            'is_review' => $isReview,
        ]);
    }

    public function toUrl(): ?string
    {
        if (!$this->model instanceof Content) {
            return null;
        }

        return $this->model->toUrl();
    }

    public function toLink(): ?string
    {
        if (!$this->model instanceof Content) {
            return null;
        }

        return $this->model->toLink();
    }

    public function toRouter(): ?string
    {
        if (!$this->model instanceof Content) {
            return null;
        }

        return $this->model->toRouter();
    }
}
