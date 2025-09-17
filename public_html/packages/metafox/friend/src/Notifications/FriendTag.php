<?php

namespace MetaFox\Friend\Notifications;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFox\Friend\Models\TagFriend as Model;
use MetaFox\Notification\Messages\MailMessage;
use MetaFox\Platform\Contracts\HasTaggedFriend;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Notifications\Notification;
use MetaFox\User\Models\UserEntity;
use MetaFox\User\Support\Facades\UserValue;

/**
 * Class FriendTag.
 *
 * @property Model $model
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 */
class FriendTag extends Notification
{
    protected string $type = 'friend_tag';

    /**
     * Get the mail representation of the notification.
     *
     * @param IsNotifiable $notifiable
     *
     * @return MailMessage
     */
    public function toMail(IsNotifiable $notifiable): MailMessage
    {
        $service     = new MailMessage();
        $item        = $this->model->item;
        $ownerEntity = $this->model->ownerEntity;
        $userEntity  = $this->model->userEntity;
        $isMention   = (bool) $this->model->is_mention;

        if ($item instanceof HasTaggedFriend) {
            return $item->toMail($service, $userEntity, $ownerEntity, $isMention);
        }

        /**
         * Default case when item does not implement HasTaggedFriend interface.
         */
        $friendFullName = $ownerEntity instanceof UserEntity ? $ownerEntity->name : null;
        $yourName       = $userEntity instanceof UserEntity ? $userEntity->name : null;
        $itemType       = $this->model->itemType();

        $emailTitle = $this->localize($isMention ? 'core::phrase.username_mentioned_you_in_a_post_subject' : 'core::phrase.username_tagged_you_in_a_post_subject', [
            'username' => $yourName,
        ]);

        $emailLine = $this->localize($isMention ? 'core::phrase.hi_friend_username_mentioned_you_in_a_post' : 'core::phrase.hi_friend_username_tagged_you_in_a_post', [
            'friend'   => $friendFullName,
            'username' => $yourName,
            'item'     => Str::replace('-', ' ', $itemType),
        ]);

        $url = url_utility()->makeApiResourceFullUrl($this->model->itemType(), $this->model->itemId());

        return $service
            ->locale($this->getLocale())
            ->subject($emailTitle)
            ->line($emailLine)
            ->action($this->localize('core::phrase.view_now'), $url);
    }

    public function toArray(IsNotifiable $notifiable): array
    {
        $data              = $this->model->toArray();
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
        $item = $this->model->item;

        $userEntity = $this->model->userEntity;

        $ownerEntity = $this->model->ownerEntity;

        if (!$userEntity instanceof UserEntity) {
            return null;
        }

        if (!$ownerEntity instanceof UserEntity) {
            return null;
        }

        $owner = $ownerEntity->detail;

        $user = $userEntity->detail;

        if (!$owner instanceof User) {
            return null;
        }

        if (!$user instanceof User) {
            return null;
        }

        $isReview  = 0;
        $isMention = (bool) $this->model->is_mention;

        if (array_key_exists('is_review_post', $this->data)) {
            $isReview = $this->data['is_review_post'];
        }

        if ($item instanceof HasTaggedFriend) {
            $item->setIsReview($isReview);
            if (method_exists($item, 'setTagFriendNotifiable') && $this->notifiable instanceof IsNotifiable) {
                $item->setTagFriendNotifiable($this->notifiable);
            }

            return $item->toCallbackMessage($userEntity, $ownerEntity, $isMention);
        }

        /*
         * Default case when item does not implement HasTaggedFriend interface.
         */
        return $this->localize($isMention ? 'core::phrase.username_mentioned_you_in_a_post_review_now' : 'core::phrase.username_tagged_you_in_a_post_review_now', [
            'username'  => $userEntity->name,
            'is_review' => $isReview,
        ]);
    }

    public function toUrl(): ?string
    {
        $item = $this->model->item;

        if ($item instanceof HasTaggedFriend) {
            return $item->toTagFriendUrl();
        }

        return null;
    }

    public function toLink(): ?string
    {
        $item = $this->model->item;

        if ($item instanceof HasTaggedFriend) {
            return $item->toTagFriendLink();
        }

        return null;
    }

    public function toRouter(): ?string
    {
        $item = $this->model->item;

        if ($item instanceof HasTaggedFriend) {
            return $item->toTagFriendRouter();
        }

        return null;
    }
}
